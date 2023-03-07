import {ConfigService} from "../services/ConfigService";
import {OptionsApiService, StylerApiService} from "../services/api";
import {StylerApiVariables} from "../types/api";
import {AppConfig, OptionType, OptionValue, OptionValues, SceneConfig} from "../types/domain";
import {VariablesOptionValuesTree} from "../types/domain/VariablesOptionValuesTree";
import {action, computed, makeAutoObservable, observable} from "mobx";
import {SceneView, SceneViews} from "../types/domain/SceneView";

export class ConfigManager {
  private _configService: ConfigService = new ConfigService();
  private _stylerApiService: StylerApiService;
  private _optionsApiService: OptionsApiService = new OptionsApiService();
  private readonly _appConfig: AppConfig;

  private _optionValues: OptionValues;

  @observable private _sceneConfig?: SceneConfig;
  private _sceneVariables?: StylerApiVariables;
  private _variablesTree: VariablesOptionValuesTree = new VariablesOptionValuesTree();
  @observable private _sceneViews: SceneViews = [];
  @observable private _currentSceneView: SceneView | undefined;

  get sceneVariables(): StylerApiVariables | undefined {
    return this._sceneVariables;
  }

  @computed
  get sceneConfig(): SceneConfig | undefined {
    return this._sceneConfig;
  }

  @computed
  get sceneViews(): SceneViews {
    return this._sceneViews;
  }

  @computed
  get currentSceneView(): SceneView | undefined {
    return this._currentSceneView;
  }

  @action
  selectSceneView(sceneView: SceneView) {
    this._currentSceneView = sceneView;
  }

  @action
  setSceneViewsOutdated() {
    this._sceneViews.forEach(view => view.setOutdated());
  }

  get variablesTree(): VariablesOptionValuesTree {
    return this._variablesTree;
  }

  constructor(appConfig: AppConfig, stylerApiService: StylerApiService) {
    this._appConfig = appConfig;
    this._stylerApiService = stylerApiService;
    makeAutoObservable(this);
  }

  async init() {
    this._optionValues = await this._optionsApiService.getOptionValues(this._appConfig.optionsApiUrl);
    this._sceneConfig = await this._configService.getSceneConfig(this._appConfig.sceneId);
    const variablesPayload = await this._stylerApiService.getVariables(this._appConfig.sceneId);
    this._sceneVariables = variablesPayload.variables;
    this.buildSceneViews(variablesPayload.views);
    this.selectSceneView(this._sceneViews[0] ?? undefined);

    this.buildVariablesTree();
  }

  private buildSceneViews(views: string[]): void {
    views.map(view => this._sceneViews.push(new SceneView(view)));
  }

  private buildVariablesTree() {
    this._sceneVariables?.forEach(variable => {
      let variableNode = this._variablesTree.getRootNode(variable.code);
      variable.shapes.forEach(shape => {
        let shapeNode = variableNode?.addChild(OptionType.SHAPE);
        if (shape.isValidOption && typeof shape.syliusCode !== 'undefined') {
          let optionValue = this.findOptionValue(shape.syliusCode);
          optionValue.appliesToVariables.add(variable.code);
          shapeNode.optionValue = optionValue;
        }

        shape.surfaces.forEach(surface => {
          let surfaceNode = shapeNode?.addChild(OptionType.SURFACE);

          if (surface.isValidOption && typeof surface.syliusCode !== 'undefined') {
            let optionValue = this.findOptionValue(surface.syliusCode);
            optionValue.appliesToVariables.add(variable.code);
            surfaceNode.optionValue = optionValue;
          }

          surface.colors.forEach(color => {
            let colorNode = surfaceNode?.addChild(OptionType.COLOR);

            if (color.isValidOption && typeof color.syliusCode !== 'undefined') {
              let optionValue = this.findOptionValue(color.syliusCode);
              optionValue.appliesToVariables.add(variable.code);
              colorNode.optionValue = optionValue;
            }
          })
        })
      })
    })

    // Store variables tree in each categories
    this._sceneConfig?.tabs.forEach(tab => {
      tab.categories.forEach(category => {
        category.variablesTree.mergeValues(this._variablesTree.getRootNodes(category.variables));
      })
    })
  }

  findOptionValue(code: string): OptionValue {
    if (typeof this._optionValues === 'undefined') {
      throw new Error('Option values are not initialized');
    }

    let option = this._optionValues.find(option => option.code === code);
    if (typeof option === 'undefined') {
      throw new Error(`Option value with code ${code} is not found`);
    }

    return option;
  }
}
