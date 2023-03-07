// @ts-ignore
import getSymbolFromCurrency from "currency-symbol-map";
// @ts-ignore
import ProjectGlobalOptions from "./fields/ProjectGlobalOptions";
import ProjectItem, {Events as ProjectItemEvents, Selectors as ProjectItemSelectors} from "./ProjectItem";
import {ProjectManager} from "../ProjectManager";
import {getRoute} from "../../../abstract/js-toolbox/router";
import {ProjectItemPropertyChangeEvent, PropertyType} from "./events/ProjectItemPropertyChangeEvent";
import {ProjectItemRemoveEvent} from "./events/ProjectItemRemoveEvent";
import moneyFormat, {CURRENCY_DIVIDER} from "../money-format";
import UIComponent from "../../../abstract/js-toolbox/ui-component";
import GridSystem, {breakpointMatches} from "../../../abstract/css-grid/css-grid";
import {getComponent as getModal} from "../../modal/modal";
import {
  ProjectSaveNamePayload,
  ProjectSavePayload,
  ProjectSaveRemovePayload,
  ProjectSaveResponse
} from "./types/ProjectSave";
import {isEmpty} from "../utils";
import UIEvent from "./events/UIEvent";
import {Events as FieldEvents} from "./fields/Field";
import {SelectOptionFieldChangeEvent} from "./events/FieldChangeEvent";
import {EventType, ProjectItemUpdatedDetailsEvent} from "./events/ProjectItemUpdatedDetailsEvent";
import {ProjectItemOptionCode} from "./types/ProjectItemOption";
import {AddProjectItemTaxon, Taxon} from "./types/Taxon";

const Classes = {
  HIDDEN_CLASS: 'u-hidden',
  FLEX_CLASS: 'u-flex',
  NO_EVENTS_CLASS: 'no-events'
}

const Selectors = {
  MAIN_COLUMN_CLASS: '.ps-main-column',
  ASIDE_COLUMN_CLASS: '.ps-aside-column',
  TOTAL_PANEL_CLASS: '.ps-total-panel',
  PROJECT_ITEM_CLASS: '.ps-project-item',
  PROJECT_ITEM_TEMPLATE_ID: 'project-item-template',
  PROJECT_NAME_INPUT_ID: '#app_project_name',
  PROJECT_COMMENT_INPUT_ID: '#app_project_comment',
  PROJECT_COMMENT_BUTTON_ID: '#app_project_comment_button',
  PROJECT_COMMENT_FIELD_ID: '#app_project_comment_field',
  PRODUCT_LIST_CLASS: '.ps-project__product-list',
  TOTAL_PRICE_ID: '#project-total-price',
  ERROR_MESSAGE_CLASS: '.ps-project__error-message',
  ADD_ITEM_BUTTON_CLASS: '.ps-project__add-item-button',
  ADD_TO_CART_BUTTON_CLASS: '.ps-project__add-to-cart-button',
  PHONE_APPOINTMENT_BUTTON_CLASS: '.ps-project__phone-appointment-button',
  SHARE_PROJECT_BUTTON_CLASS: '.ps-project__share-project-button',
  DOWNLOAD_QUOTE_FILE_BUTTON_CLASS: '.ps-project__download-quote-file-button',
  PROJECT_TOKEN_ID: 'project-token',
  CHANNEL_CODE_ID: 'channel-code',
  CURRENCY_CODE_ID: 'currency-code',
  ITEM_TYPE_FIELD_CLASS: '.ps-project-item__type-field',
};

export const Events = {
  ITEM_PROPERTY_CHANGE: 'itemPropertyChange',
  ITEM_QUANTITY_CHANGE: 'itemQuantityChange',
  ITEM_OPTION_CHANGE: 'itemOptionChange',
  ITEM_REMOVED: 'itemRemoved',
};

export default class Project extends UIComponent {
  public elmt: HTMLElement; // Change extended type
  private manager: ProjectManager;

  private _saveTimeout: number;
  private _loadingIndicatorTimeout: number;
  private _loadings: { [key: string]: boolean } = {}
  private readonly _items: ProjectItem[] = [];
  private readonly _globalOptions: ProjectGlobalOptions;
  private _lastItemIndex: number;

  public readonly token: string;
  public readonly channelCode: string;
  public readonly currencyCode: string;
  public readonly currencySymbol: string;
  private _lastAutosaveIndex: number;

  get items(): ProjectItem[] { return this._items }
  get globalOptions(): ProjectGlobalOptions { return this._globalOptions }

  get addToCartButton(): HTMLButtonElement { return this.elmt.querySelector(Selectors.ADD_TO_CART_BUTTON_CLASS); }
  get shareProjectButton(): HTMLButtonElement { return this.elmt.querySelector(Selectors.SHARE_PROJECT_BUTTON_CLASS); }
  get downloadQuoteFileButton(): HTMLButtonElement { return this.elmt.querySelector(Selectors.DOWNLOAD_QUOTE_FILE_BUTTON_CLASS); }
  get productListElmt() { return this.elmt.querySelector(Selectors.PRODUCT_LIST_CLASS); }
  get errorMessageElmt() { return this.elmt.querySelector(Selectors.ERROR_MESSAGE_CLASS); }
  get totalPriceElmt() { return this.elmt.querySelector(Selectors.TOTAL_PRICE_ID); }
  get nameInputElmt(): HTMLInputElement { return this.elmt.querySelector(Selectors.PROJECT_NAME_INPUT_ID); }
  get commentInputElmt(): HTMLInputElement { return this.elmt.querySelector(Selectors.PROJECT_COMMENT_INPUT_ID); }
  get commentButtonElmt(): HTMLInputElement { return this.elmt.querySelector(Selectors.PROJECT_COMMENT_BUTTON_ID); }

  get isComplete() { return this._items.every(x => x.isComplete); }
  get isValid() { return this._items.every(x => x.isValid); }
  get totalPrice() {
    return this.isComplete
      ? this._items.reduce((accumulator, product) => accumulator + product.totalPrice, 0)
      : 0;
  }
  get formattedTotalPrice() { return moneyFormat.format(this.totalPrice / CURRENCY_DIVIDER).replace('.', ','); }

  get isLoading() {
    return this._items.some(x => x.isLoading) || Object.values(this._loadings).some((value) => value)
  }

  constructor(elem: HTMLElement, manager: ProjectManager) {
    super(elem);
    this.manager = manager;

    this.token = (<HTMLInputElement>document.getElementById(Selectors.PROJECT_TOKEN_ID)).value;
    this.channelCode = (<HTMLInputElement>document.getElementById(Selectors.CHANNEL_CODE_ID)).value;
    this.currencyCode = (<HTMLInputElement>document.getElementById(Selectors.CURRENCY_CODE_ID)).value;
    this.currencySymbol = getSymbolFromCurrency(this.currencyCode);

    this._globalOptions = new ProjectGlobalOptions(elem);
    this._items = Array.from(elem.querySelectorAll(Selectors.PROJECT_ITEM_CLASS)).map((elem: HTMLElement, index ) => {
      let item = new ProjectItem(elem, this, index);
      this.bindItem(item);
      return item;
    });
    this._lastItemIndex = this._items.length - 1;
    this._lastAutosaveIndex = this._lastItemIndex;

    GridSystem.on('enterlarge', onEnterLargeBreakpoint.bind(this));
    GridSystem.on('leavelarge', onLeaveLargeBreakpoint.bind(this));
    breakpointMatches('large')
      ? onEnterLargeBreakpoint.call(this)
      : onLeaveLargeBreakpoint.call(this);

    //Binding
    this.bindData();
    this.bindActions();
  }

  /**
   * Count all items that match the given taxons
   * An item can be evinced using discriminator callback
   * Returns the quantity or the number of items depending on countQuantity flag
   * @param taxons
   * @param countQuantity
   * @param discriminator
   */
  countItemsTaxons(taxons: Taxon|Taxon[], countQuantity: boolean = true, discriminator: (x: ProjectItem) => boolean = () => true): number {
    if (!Array.isArray(taxons)) {
      taxons = [taxons];
    }

    return this._items.filter(x => x.taxons.some((t: Taxon) => taxons.includes(t)) && discriminator(x)).reduce((accumulator, item) => {
      return countQuantity ? accumulator + parseInt(item.quantity.value) : accumulator + 1;
    }, 0);
  }

  /**
   * Set loading flag for parameter type
   * Used for requests
   * @param type
   * @param state
   */
  setLoading(type: string, state: boolean) {
    this._loadings[type] = state;
  }

  /**
   * Lock or unlock the project
   * @param lock
   */
  lockUnlock(lock: boolean) {
    this._globalOptions.disabled = lock;
    this.addToCartButton.disabled = lock;
  }

  /**
   * Show errors and disable add to cart
   */
  lockFormSubmission() {
    this.errorMessageElmt.classList.remove(Classes.HIDDEN_CLASS);
    this.addToCartButton.disabled = true;
    this.shareProjectButton.disabled = true;
    this.downloadQuoteFileButton.disabled = true;
    this.updateTotal(0);
  }

  /**
   * Hide errors and release add to cart
   */
  unlockFormSubmission() {
    this.errorMessageElmt.classList.add(Classes.HIDDEN_CLASS);
    this.addToCartButton.disabled = false;
    this.shareProjectButton.disabled = false;
    this.downloadQuoteFileButton.disabled = false;
    this.updateTotal();
  }

  /**
   * Update total value
   * @param value
   */
  updateTotal(value: number|undefined = undefined) {
    let val = isEmpty(value)
      ? this.formattedTotalPrice
      : moneyFormat.format(value / CURRENCY_DIVIDER).replace('.', ',');

    this.totalPriceElmt.innerHTML = `<span>${val} ${this.currencySymbol}</span>`;
  }

  /**
   * Show the project as loading
   * Auto resolve after 1sec if autoHide is true
   * @param autoHide
   */
  showLoadingIndicator(autoHide = true) {
    this._loadingIndicatorTimeout && clearTimeout(this._loadingIndicatorTimeout);

    const loadingIndicatorElmt = document.querySelector('.ps-loading-indicator');

    loadingIndicatorElmt.classList.remove('ps-loading-indicator--error');
    loadingIndicatorElmt.classList.add('ps-loading-indicator--visible');

    if (autoHide) {
      this._loadingIndicatorTimeout = window.setTimeout(() => {
        this.maybeHideLoadingIndicator();
      }, 1000);
    }
  }

  /**
   * Recursive method used to determine if the loading indicator should change
   */
  maybeHideLoadingIndicator() {
    if (!this.isLoading) {
      this.hideLoadingIndicator();
    } else {
      this._loadingIndicatorTimeout = window.setTimeout(() => {
        this.maybeHideLoadingIndicator.call(this);
      }, 1000);
    }
  }

  /**
   * Hide project loading indicator
   * @param error
   */
  hideLoadingIndicator(error = false) {
    this._loadingIndicatorTimeout && clearTimeout(this._loadingIndicatorTimeout);
    this._loadingIndicatorTimeout = null;

    const loadingIndicatorElmt = document.querySelector('.ps-loading-indicator');

    error && loadingIndicatorElmt.classList.add('ps-loading-indicator--error');
    loadingIndicatorElmt.classList.remove('ps-loading-indicator--visible');
  }

  /**
   * On save success. Show a retry modal if any item has FAILED during save
   * @param type
   * @param items
   * @param response
   */
  async finishSuccessfulSaveRequest(type: string, items: ProjectItem[], response: ProjectSaveResponse) {
    this.hideLoadingIndicator();
    this.elmt.classList.remove('ps-project--saving');

    let allGood = true;

    if ('newItems' in response && !Array.isArray(response.newItems)) {
      const keys = Object.keys(response.newItems);
      keys.forEach((groupId) => {
        const item = this.getProjectItemById(groupId);
        item && item.validateAsSavedItem(response.newItems[groupId], ++this._lastAutosaveIndex);
      });
    }

    if ('removedItems' in response && !Array.isArray(response.removedItems)) {
      const keys = Object.keys(response.removedItems);
      keys.forEach((itemId) => {
        if (response.removedItems[itemId] === 'FAILED') {
          allGood = false;
        } else {
          // Reorder items indexes to be consistency with backend
          let newIndex = 0;
          this._items.forEach((item) => {
            item.itemIndex = newIndex++;
          });
          this._lastItemIndex = this._lastAutosaveIndex = newIndex - 1;
        }
      });
    }

    if ('updatedItems' in response && !Array.isArray(response.updatedItems)) {
      const keys = Object.keys(response.updatedItems);
      keys.forEach((itemId) => {
        if (response.updatedItems[itemId] === 'FAILED') {
          allGood = false;
        }
      });
    }

    if (!allGood) {
      await this.openRetryableSaveModal(type, items);
    }
  }

  /**
   * On save error. Show a retry modal
   * @param type
   * @param items
   * @param error
   */
  async finishUnsuccessfulSaveRequest(type: string, items: ProjectItem[], error: any) {
    this.hideLoadingIndicator(true);
    this.elmt.classList.remove('ps-project--saving');

    if (error.statusText !== 'abort') {
      await this.openRetryableSaveModal(type, items);
    }
  }

  /**
   * Get request payload for updated / added items
   * @param items
   * @param includeGlobalOptions
   */
  getItemsSavePayload(items: ProjectItem[], includeGlobalOptions: boolean) {
    const payload = <ProjectSavePayload>{ };

    if (includeGlobalOptions) {
      payload.globalOptions = {
        design: this.globalOptions.design.value,
        finish: this.globalOptions.finish.value,
        color: this.globalOptions.color.value,
      }
    }

    items.forEach((item) => {
      const itemData = item.getData(false, true);

      if (item.isNewItem && item.isComplete) {
        if (!('newItems' in payload)) {
          payload.newItems = [];
        }

        payload.newItems.push(itemData);
      } else if (item.isComplete) {
        if (!('updatedItems' in payload)) {
          payload.updatedItems = [];
        }

        payload.updatedItems.push(itemData);
      }
    });

    return payload; // Payload should be an empty array if no items are inserting / updating
  }

  /**
   * Get request payload for removed items
   * @param removedItems
   */
  getItemsRemovedSavePayload(removedItems: ProjectItem[]) {
    const payload = <ProjectSaveRemovePayload>{ removedItems: [] };
    removedItems.forEach((item) => {
      !isEmpty(item.id) && payload.removedItems.push(item.id);
    })

    return payload.removedItems.length > 0 ? payload : null;
  }

  /**
   * Get request payload for changed project name
   */
  getProjectNameSavePayload() {
    return <ProjectSaveNamePayload>{
      name: this.nameInputElmt.value,
      comment: this.commentInputElmt.value
    };
  }

  /**
   * Clear warnings for all global options and items
   */
  clearWarnings() {
    this._globalOptions.clearWarnings();
    this._items.forEach(x => x.clearWarnings());
  }

  /**
   * Update warnings for all global options and items
   */
  updateWarnings() {
    this._globalOptions.updateWarnings();
    this._items.forEach(x => x.updateWarnings());
  }

  /**
   * Add a project item
   * @private
   */
  addItem(type: AddProjectItemTaxon, quantity: number = 1): ProjectItem {
    if (!Object.values(AddProjectItemTaxon).includes(type)) {
      throw new Error('Invalid item type');
    }

    if (isNaN(quantity) || quantity < 1) {
      quantity = 1;
    }

    const commonRoute = getRoute('common');
    const index: number = ++this._lastItemIndex;
    const id = `${ProjectItemSelectors.DIVS_ID_PREFIX}_${index}`;

    const productHtml = document.getElementById(Selectors.PROJECT_ITEM_TEMPLATE_ID).innerHTML
      .replace(/%ID%/gm, id)
      .replace(/__name__/gm, String(index));

    this.productListElmt.insertAdjacentHTML('beforeend', productHtml);
    let newItemElmt = <HTMLElement>this.productListElmt.querySelector(`#${id}`);

    // Remove SELECT choices that are not the desired type
    newItemElmt.querySelectorAll(Selectors.ITEM_TYPE_FIELD_CLASS).forEach((elem: HTMLElement) => {
      if (elem.dataset.type !== <string><unknown>type) {
        elem.remove();
      }
    });

    commonRoute.initWidgets(newItemElmt, true); // Also init hidden form fields (options)
    const item = new ProjectItem(newItemElmt, this, index);
    item.quantity.value = quantity;
    this._items.push(item);
    this.bindItem(item);
    return item;
  }

  /**
   * Delete a project item
   * @param item
   */
  private removeProjectItem(item: ProjectItem) {
    const index = this.getItemIndexFromElmt(item.elmt);
    if (index === -1) {
      return;
    }
    const [removedItem] = this._items.splice(index, 1);
    removedItem && removedItem.elmt.remove();
  }

  /**
   * Retrieve a project item by his entity id
   * @param pid
   */
  private getProjectItemById(pid: string): ProjectItem | null {
    let found = null;
    this._items.forEach((item) => {
      if (item.id === pid) {
        found = item;
      }
    });
    return found;
  }

  /**
   * Open the save error modal with a retry button
   * @param type original save type
   * @param items original save items
   * @private
   */
  private async openRetryableSaveModal(type: string, items: ProjectItem[]) {
    const modal = getModal(document.querySelector('.ps-save-error-modal'));

    if (await modal.show()) { // Retry
      await this.manager.saveProject(type, items);
    }
  }

  /**
   * Get the index in items list
   * @param elmt
   * @private
   */
  private getItemIndexFromElmt(elmt: HTMLElement): number {
    let found = -1;
    this._items.forEach((item, index) => {
      if (item.elmt === elmt) {
        found = index;
      }
    });
    return found;
  }

  /**
   * Handle project item property change
   * @param event
   */
  private async onProjectItemPropertyChange(event: UIEvent<ProjectItemPropertyChangeEvent>) {
    this.emit(Events.ITEM_PROPERTY_CHANGE, event.detail);
    switch (event.detail.propertyType) {
      case PropertyType.COMMENT:
      case PropertyType.QUANTITY:
        this.emit(Events.ITEM_QUANTITY_CHANGE, event.detail);
        this.updateTotal();
        this._saveTimeout && clearTimeout(this._saveTimeout);
        this._saveTimeout = window.setTimeout(async () => {
          await this.manager.saveProject('item', [event.detail.item]);
        }, event.detail.propertyType === PropertyType.QUANTITY ? 300 : 500);
        break;

      case PropertyType.OPTION:
        this.emit(Events.ITEM_OPTION_CHANGE, event.detail);
        await this.manager.updateProjectItemDetailsFor([event.detail.item])
        break;

      case PropertyType.TYPE:
        await this.manager.updateProjectItemDetailsFor([event.detail.item])

        if (!this.isLoading) {
          this.lockUnlock(false);
          !this.isComplete && this.lockFormSubmission();
        }
        break;
      default:
    }
  }

  /**
   * On global option field change
   * @param event
   * @private
   */
  private async onProjectGlobalOptionFieldChange(event: UIEvent<SelectOptionFieldChangeEvent>) {
    event.detail.field.clearWarning();
    this.showLoadingIndicator();

    if (event.detail.field.optionCode === ProjectItemOptionCode.FINISH) {
      this._globalOptions.colorSelectConditionalDisplay();
    }

    // Update only items with design / finish / color option
    let updateItemsList = this._items.filter(item => {
      return item.options.hasGlobalOptions && item.taxons.includes(Taxon.FACADE)
    });

    updateItemsList.forEach(item => {
      let availableOptions, field;

      switch (event.detail.field.optionCode) {
        case ProjectItemOptionCode.DESIGN:
          availableOptions = item.options.selects.design.optionsContent;
          field = item.options.selects.design;
          break;
        case ProjectItemOptionCode.FINISH:
          availableOptions = item.options.selects.finish.optionsContent;
          field = item.options.selects.finish;
          break;
        default:
          availableOptions = item.options.selects.color.optionsContent;
          field = item.options.selects.color;
          break;
      }

      // Check if desired value is present in options
      if (field.value !== event.detail.value) {
        if (availableOptions.includes(event.detail.value)) {
          field.value = event.detail.value
        } else if (availableOptions.length > 1) {
          field.value = null;
        }
      }
    });
    await this.manager.updateProjectItemDetailsFor(updateItemsList);
  }

  /**
   * Handle project item remove request
   * @param event
   */
  private async onProjectItemRemovalRequest(event: UIEvent<ProjectItemRemoveEvent>) {
    this.removeProjectItem(event.detail.item);
    this.updateWarnings();

    if (this.isComplete && !this.isLoading) {
      this.unlockFormSubmission();
    }

    this.emit(Events.ITEM_REMOVED, event.detail);

    this.updateTotal();

    // Delete only items already saved
    if (!event.detail.item.isNewItem) {
      await this.manager.saveProject('removal', [event.detail.item]);
    }
  }

  /**
   * Handle project item updated details
   * @private
   * @param event
   */
  private async onProjectItemUpdateDetails(event: UIEvent<ProjectItemUpdatedDetailsEvent>) {
    if (event.detail.type === EventType.INCOMPLETE) { // Remove a not found item
      this.removeProjectItem(event.detail.item);
    }
  }

  /**
   * Attach handlers to project item events
   * @param item
   * @private
   */
  private bindItem(item: ProjectItem) {
    item.on(ProjectItemEvents.PROPERTY_CHANGE, this.onProjectItemPropertyChange.bind(this));
    item.on(ProjectItemEvents.BEFORE_PROPERTY_CHANGE, (e: UIEvent<ProjectItemPropertyChangeEvent>) => {
      (e.detail.propertyType === PropertyType.TYPE || e.detail.propertyType === PropertyType.OPTION) && this.lockUnlock(true)
    });
    item.on(ProjectItemEvents.RESET, () => { this.updateTotal() });
    item.on(ProjectItemEvents.REQUEST_REMOVAL, this.onProjectItemRemovalRequest.bind(this));
    item.on(ProjectItemEvents.UPDATE_DETAILS, this.onProjectItemUpdateDetails.bind(this));
  }

  /**
   * Attach handlers to project actions
   * @private
   */
  private bindActions() {
    this.elmt.querySelectorAll(Selectors.ADD_ITEM_BUTTON_CLASS).forEach((button: HTMLButtonElement) => {
      button.addEventListener('click', this.addItem.bind(this, button.dataset.type, 1));
    });
    this.elmt.querySelector(Selectors.PHONE_APPOINTMENT_BUTTON_CLASS).addEventListener('click', async (e) => {
      e.preventDefault();
      await this.manager.openScheduleCallModal();
    })
    this.shareProjectButton.addEventListener('click', (e) => {
      e.preventDefault();
      this.manager.openShareProjectModal();
    })
    this.commentButtonElmt.addEventListener('click', (e) => {
      e.preventDefault();
      this.elmt.querySelector(Selectors.PROJECT_COMMENT_FIELD_ID).classList.remove('u-invisible');
      this.elmt.querySelector(Selectors.PROJECT_COMMENT_BUTTON_ID).classList.add(Classes.HIDDEN_CLASS);
    })
    this.downloadQuoteFileButton.addEventListener('click', async (e) => {
      e.preventDefault();
      document.getElementById('loaderPdf').classList.add(Classes.FLEX_CLASS);
      document.getElementById('app_project_downloadQuoteFile').classList.add(Classes.NO_EVENTS_CLASS);
      try {
        await this.manager.downloadQuoteFile();
        document.getElementById('loaderPdf').classList.remove(Classes.FLEX_CLASS);
        document.getElementById('app_project_downloadQuoteFile').classList.remove(Classes.NO_EVENTS_CLASS);
      }
      catch (e) {}
    })
  }

  /**
   * Attach handlers to project data
   * @private
   */
  private bindData() {
    // Subscribe to input & change events for name and comment
    ['input', 'change'].forEach(x => [this.nameInputElmt, this.commentInputElmt].forEach(y => y.addEventListener(x, (e) => {
      clearTimeout(this._saveTimeout);
      this._saveTimeout = window.setTimeout(async () => {
        await this.manager.saveProject('name');
      }, 500);
    })));

    // Bind change on global options
    this._globalOptions.on(FieldEvents.ON_CHANGE, this.onProjectGlobalOptionFieldChange.bind(this))
  }
}

function onEnterLargeBreakpoint() {
  const asideColumn = this.elmt.querySelector(Selectors.ASIDE_COLUMN_CLASS);
  const totalPanel = this.elmt.querySelector(Selectors.TOTAL_PANEL_CLASS);
  asideColumn.insertAdjacentElement('afterbegin', totalPanel);
}

function onLeaveLargeBreakpoint() {
  const mainColumn = this.elmt.querySelector(Selectors.MAIN_COLUMN_CLASS);
  const totalPanel = this.elmt.querySelector(Selectors.TOTAL_PANEL_CLASS);
  mainColumn.insertAdjacentElement('afterbegin', totalPanel);
}
