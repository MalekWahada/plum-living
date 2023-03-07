import Project, {Events} from "./model/Project";
import {getComponent as getModal} from "../modal/modal";
import {isEmpty} from "./utils";
import ProjectItem from "./model/ProjectItem";
import {ProjectItemsDetailsRequest, ProjectItemsDetailsResponse} from "./model/types/ProjectItemsDetails";
import {ProjectSaveResponse} from "./model/types/ProjectSave";
import axios, {AxiosError, AxiosInstance, AxiosResponse, CancelToken, CancelTokenSource} from "axios";
import UserInteractions from "./UserInteractions";
import UIEvent from "./model/events/UIEvent";
import {ProjectItemPropertyChangeEvent} from "./model/events/ProjectItemPropertyChangeEvent";

type SaveToken = {
    source: CancelTokenSource,
    requestType: string,
    requestItems: string[],
}

export class ProjectManager {
  private readonly _project: Project;
  private readonly _fetchBulkDetailsUrl: string;
  private readonly _autoSaveUrl: string;
  private readonly _scheduleCallUrl: string;
  private readonly _downloadQuoteFileUrl: string;
  private _http: AxiosInstance;
  private _userInteractions: UserInteractions;
  private _saveTokens: SaveToken[] = [];
  private _downloadFileToken: CancelTokenSource;

  constructor(elem: HTMLElement) {
    this._fetchBulkDetailsUrl = (<HTMLInputElement>document.getElementById('get-bulk-project-item-details-url')).value;
    this._autoSaveUrl = (<HTMLInputElement>document.getElementById('auto-save-url')).value;
    this._downloadQuoteFileUrl = (<HTMLInputElement>document.getElementById('download-quote-file-url')).value;
    this._scheduleCallUrl = (<HTMLInputElement>document.getElementById('schedule-call-url')).value;

    this._project = new Project(elem, this);
    this._userInteractions = new UserInteractions(this._project);

    this._project.on(Events.ITEM_PROPERTY_CHANGE, this.onProjectItemPropertyChange.bind(this));
    this._project.on(Events.ITEM_REMOVED, this.onProjectItemPropertyChange.bind(this));

    this._http = axios.create({
      baseURL: window.location.origin,
    })
    this._http.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'; // Set default header for PHP
  }

  /**
   * Save the project. Payload will be determined depending on the save type
   * @param type
   * @param items
   */
  async saveProject(type: string, items: ProjectItem[] = this._project.items) {
    let itemsIds = items.map(item => item.id);
    this._saveTokens // Cancel any previous save request for this type and items list
      .filter(token => token.requestType === type && JSON.stringify(token.requestItems) === JSON.stringify(itemsIds))
      .forEach(token => token.source.cancel());
    let currentSaveToken = {
      source: axios.CancelToken.source(),
      requestType: type,
      requestItems: itemsIds,
    };
    this._saveTokens.push(currentSaveToken);
    let payload = null;

    switch (type) {
      case 'name':
        payload = this._project.getProjectNameSavePayload();
        break;
      case 'item':
        payload = this._project.getItemsSavePayload(items, false);
        break;
      case 'batch':
        payload = this._project.getItemsSavePayload(items, true);
        break;
      case 'removal':
        payload = this._project.getItemsRemovedSavePayload(items);
        break;
    }

    if (payload === null || Object.keys(payload).length === 0) { // Payload is empty
      return;
    }

    this._project.elmt.classList.add('ps-project--saving');
    this._project.showLoadingIndicator(false);

    try {
      let res = await this._http.post<ProjectSaveResponse>(this._autoSaveUrl, payload, {
        cancelToken: currentSaveToken.source.token,
      });

      await this._project.finishSuccessfulSaveRequest(type, items, res.data);
    } catch (e: any) {
      if (e instanceof axios.Cancel) { // Request was cancelled by new one
        return;
      }
      console.error(`Save error`, e);
      await this._project.finishUnsuccessfulSaveRequest(type, items, <AxiosError>e);
    } finally {
      delete this._saveTokens[this._saveTokens.indexOf(currentSaveToken)];
    }
  }

  openShareProjectModal() {
    const modal = getModal(document.querySelector('.ps-share-project-modal'));
    modal.show();
  }

  /**
   * Download the quote file asynchronously
   * A link is added temporally to the document once the download is complete
   * Taken from https://github.com/kennethjiang/js-file-download/blob/master/file-download.js
   */
  async downloadQuoteFile() {
    if (typeof this._downloadFileToken !== 'undefined') {
      this._downloadFileToken.cancel();
    }
    this._downloadFileToken = axios.CancelToken.source();

    try {
      let res = await this._http.get(window.location.origin + this._downloadQuoteFileUrl, {
        responseType: 'blob',
        cancelToken: this._downloadFileToken.token,
      });
      const blob = new Blob([res.data], {type: res.headers['content-type']});
      let filename = '';
      const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
      const matches = filenameRegex.exec(res.headers['content-disposition']);
      if (matches != null && matches[1]) {
        filename = decodeURIComponent(matches[1].replace(/['"]/g, ''));
      }

      // @ts-ignore
      if (typeof window.navigator.msSaveBlob !== 'undefined') { // IE workaround
        // @ts-ignore
        window.navigator.msSaveBlob(blob, filename);
      } else {
        const url = (window.URL && window.URL.createObjectURL) ? window.URL.createObjectURL(blob) : window.webkitURL.createObjectURL(blob);
        const link = document.createElement("a");
        link.style.display = 'none';
        link.href = url;
        link.setAttribute('download', filename);
        if (typeof link.download === 'undefined') {
          link.setAttribute('target', '_blank');
        }
        document.body.appendChild(link);
        link.click();
        setTimeout(function() {
          document.body.removeChild(link);
          window.URL.revokeObjectURL(url);
        }, 200)
      }
    }
    catch (e: any) {
      console.error(`Download quote file error`, e);
      throw e;
    }
  }

  async openScheduleCallModal() {
    try {
      await this._http.get(this._scheduleCallUrl);

      const modal = getModal(document.querySelector('.ps-save-warning-modal'));
      modal.show();
    } catch (e: any) {
      console.error(`Schedule call error`, e);
    }
  }

  async updateProjectItemDetailsFor(items: ProjectItem[]) {
    const partialUpdate = items !== this._project.items;
    const requestData = <ProjectItemsDetailsRequest>{
      channelCode: this._project.channelCode,
      itemsData: [],
    };

    items.forEach((item) => {
      if (item.id) {
        requestData.itemsData.push(item.getData(true, false));
      }
    });

    try {
      if (!partialUpdate) {
        this._project.lockUnlock(true);
        this._project.setLoading('projectItemDetails', true);
      }

      items.forEach((item) => {
        item.lockUnlock(true);
        item.markAsLoading();
      });

      let res = await this._http.post<ProjectItemsDetailsResponse>(this._fetchBulkDetailsUrl, requestData);

      items.forEach((item) => {
        if (item.id in res.data) {
          try {
            item.updateDetails(res.data[item.id]);
            item.updateWarnings();
          }
          catch (e: any) {
            console.error("Unable to update details on item", item, e);
          }
        } else {
          item.lockUnlock(false);
        }
      });

      if (!partialUpdate) {
        this._project.setLoading('projectItemDetails', false);
        this._project.lockUnlock(false);
      }

      if (!this._project.isLoading) {
        if (this._project.isValid) {
          this._project.clearWarnings()
        } else {
          this._project.updateWarnings();
        }

        if (this._project.isComplete) {
          this._project.unlockFormSubmission();
        } else if (this._project.items.filter(item => !item.isComplete && isEmpty(item.id)).length > 0) {
          this._project.unlockFormSubmission();
        } else {
          this._project.lockFormSubmission();
        }
      }

      await this.saveProject(items.length > 1 ? 'batch' : 'item', items); // Save one or multiple items.
    } catch (e: any) {
      this._project.setLoading('projectItemDetails', false);
      this._project.lockUnlock(false);
      console.error(`Item-details error`, e);
    }
  }

  private onProjectItemPropertyChange(event: UIEvent<ProjectItemPropertyChangeEvent>) {
    this._userInteractions.updatePopinHandles();
  }
}

function init(elem: HTMLElement) {
  return new ProjectManager(elem);
}

export default init;
