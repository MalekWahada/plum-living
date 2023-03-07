import {AppConfig, SceneConfig} from "../types/domain";
import {ConfigManager} from "./ConfigManager";
import {ViewManager} from "./ViewManager";
import {StylerApiService} from "../services/api";
import {action, computed, makeAutoObservable, observable, reaction} from "mobx";
import {StylerApiBuildViewsResult} from "../types/api/StylerApiBuildViewsResult";
import {ErrorManager} from "./ErrorManager";

export class AppManager {
  private readonly _appConfig: AppConfig;
  private readonly _configManager: ConfigManager;
  private readonly _viewManager: ViewManager;
  private readonly _stylerApiService: StylerApiService;
  private readonly _errorManager: ErrorManager;
  @observable private _isLoadingView: boolean = false;

  @computed
  get isLoadingView(): boolean {
    return this._isLoadingView;
  }

  @action
  private setLoadingView(value: boolean) {
    this._isLoadingView = value;
  }

  get errorManager(): ErrorManager {
    return this._errorManager;
  }

  get viewManager(): ViewManager {
    return this._viewManager;
  }

  get sceneConfig(): SceneConfig | undefined {
    return this._configManager.sceneConfig;
  }

  get configManager(): ConfigManager {
    return this._configManager;
  }

  async init() {
    await this._configManager.init();

    if (typeof this.sceneConfig === 'undefined') {
      throw new Error('Scene config is undefined');
    }
    this._viewManager.initView(); // Initial fill of view
  }

  constructor(appConfig: AppConfig) {
    this._appConfig = appConfig;
    this._stylerApiService = new StylerApiService();
    this._configManager = new ConfigManager(this._appConfig, this._stylerApiService);
    this._viewManager = new ViewManager(this._configManager);
    this._errorManager = new ErrorManager();

    makeAutoObservable(this);

    // Handle variables selections changes
    reaction(() => this._viewManager.variablesSelections.update,
      async (variable) => {
        if (typeof this._configManager.currentSceneView === 'undefined') {
          return;
        }
        this.configManager.setSceneViewsOutdated();
        await this.buildView(this.configManager.currentSceneView);
    })

    // Handle carousel view change
    reaction(
      () => this.configManager.currentSceneView,
      async (sceneView) => {
      if (sceneView?.isOutdated || !sceneView?.isLoaded) {
        await this.buildView(sceneView);
      }
    });
  }

  private async buildView(sceneView) {
    if (typeof sceneView !== 'undefined' && this._viewManager.variablesSelections.isInitialized) {
      try {
        this.setLoadingView(true);
        console.log(`Build view ${sceneView.code}`);
        let result = await this._stylerApiService.buildViews(this._appConfig.sceneId, this._appConfig.userConfig ?? {}, this._viewManager.variablesSelections.extractVariablesOptionValues(), [sceneView]);
        this.processBuildViewsResult(result);
      }
      catch (error) {
        this._errorManager.addError(error as Error);
      } finally {
        this.setLoadingView(false);
      }
    }
  }

  private processBuildViewsResult(result: StylerApiBuildViewsResult) {
    result.views.forEach(viewResult => {
      let view = this._configManager.sceneViews.find(x => x.code === viewResult.code);
      if (typeof view !== 'undefined') {
        view.updateUri(viewResult.uri);
        view.resetOutdated();
        console.log(`View ${view.code} updated: ${view.uri}`);
      }
    })
  }
}
