import {OptionType, SelectionView, SelectionViewCategory} from "../types/domain";
import {ConfigManager} from "./ConfigManager";
import {VariablesSelections} from "../types/domain/VariablesSelections";
import {reaction} from "mobx";

export class ViewManager {
  get selectionView(): SelectionView {
    return this._selectionView;
  }
  get variablesSelections(): VariablesSelections {
    return this._variablesSelections;
  }

  private readonly _selectionView: SelectionView;
  private readonly _configManager: ConfigManager;
  private readonly _variablesSelections: VariablesSelections;

  constructor(configManager: ConfigManager) {
    this._selectionView = new SelectionView();
    this._variablesSelections = new VariablesSelections();
    this._configManager = configManager;
  }

  initView() {
    this._variablesSelections.initWithTree(this._configManager.variablesTree);

    if (typeof this._configManager.sceneConfig !== 'undefined') {
      this._selectionView.initTabs(this._configManager.sceneConfig.tabs);
      this._selectionView.selectTab(this._configManager.sceneConfig.firstTab);
      this._selectionView.tabViews.forEach(tabView => {
        tabView?.initCategories(tabView.tab.categories);
        tabView.selectCategory(tabView.tab.firstCategory);
        tabView.categoryViews.forEach(categoryView => {
          categoryView.initOptionValues();

          // Listen to option value changes
          reaction(() => categoryView.getOptionValues(),
          (optionValues, previousOptionValues) => {
              if (!categoryView.isSelectionCompleted) {
                return;
              }

              // Remove previous selection
              for (const optionType in previousOptionValues) {
                let optionValue = previousOptionValues[optionType];
                if (typeof optionValue === 'undefined') {
                  continue;
                }
                console.log(`Removing option ${optionType} to ${optionValue?.code}`);
                this._variablesSelections.selectVariablesOptionValue(categoryView.category.getVariablesToUpdate(optionValue), optionType as OptionType, optionValue);
              }

              // Update current selection
              for (const optionType in optionValues) {
                let optionValue = optionValues[optionType];
                if (typeof optionValue === 'undefined') {
                  continue;
                }
                console.log(`Setting option ${optionType} to ${optionValue?.code}`);
                this._variablesSelections.selectVariablesOptionValue(categoryView.category.getVariablesToUpdate(optionValue), optionType as OptionType, optionValue);
              }
            }
          );
        })
      })
    }
  }

  export() {
    return this._selectionView.exportCurrentView();
  }
}
