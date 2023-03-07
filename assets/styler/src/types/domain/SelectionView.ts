import {SceneTab} from "./SceneTab";
import {Category} from "./Category";
import {OptionType, OptionTypeHierarchy} from "./Option";
import {OptionValue, OptionValues} from "./OptionValue";
import {action, computed, makeAutoObservable, observable} from "mobx";
import {OptionValuesTreeNode, OptionValuesTreeRootNode} from "./VariablesOptionValuesTree";
import {ExportViewVariables} from "./ExportViewVariables";

export class SelectionView {
  @observable private readonly _tabViews: Map<SceneTab, SelectionViewTab> = new Map();
  @observable private _selectedTab?: SceneTab;

  constructor() {
    makeAutoObservable(this);
  }

  @computed
  get tabViews(): SelectionViewTab[] {
    return Array.from(this._tabViews.values());
  }

  @computed
  get selectedTab(): SceneTab | undefined {
    return this._selectedTab;
  }
  @computed
  get selectedTabView(): SelectionViewTab | undefined {
    return typeof this._selectedTab !== "undefined" ? this._tabViews.get(this._selectedTab) : undefined;
  }

  @computed
  get tabIsSelected(): boolean {
    return typeof this._selectedTab !== "undefined";
  }

  @computed
  get isSelectionCompleted(): boolean {
    if (typeof this._selectedTab !== "undefined" && this._tabViews.has(this._selectedTab)) {
      return !!this.selectedTabView?.isSelectionCompleted;
    }
    return false;
  }

  @computed
  get isDisabled(): boolean {
    return !this.tabIsSelected;
  }

  @action
  selectTab(tab: SceneTab): void {
    if (!this._tabViews.has(tab)) {
      this._tabViews.set(tab, new SelectionViewTab(tab));
    }
    this._selectedTab = tab;
  }

  @action
  selectCategory(category: Category): void {
    if (typeof this._selectedTab !== "undefined" && this._tabViews.has(this._selectedTab)) {
      this._tabViews.get(this._selectedTab)?.selectCategory(category);
    }
  }

  @action
  selectOptionValue(optionType: OptionType, value: OptionValue | undefined): void {
    if (typeof this._selectedTab !== "undefined" && this._tabViews.has(this._selectedTab)) {
      this._tabViews.get(this._selectedTab)?.selectOptionValue(optionType, value);
    }
  }

  @action
  resetAllOptionValues(): void {
    if (typeof this._selectedTab !== "undefined" && this._tabViews.has(this._selectedTab)) {
      this._tabViews.get(this._selectedTab)?.resetAllOptionValues();
    }
  }

  @action
  exportCurrentView(): ExportViewVariables | undefined {
    if (typeof this._selectedTab !== "undefined" && this._tabViews.has(this._selectedTab)) {
      return this._tabViews.get(this._selectedTab)?.exportCurrentView();
    }
    return undefined;
  }

  @action
  initTabs(tabs: SceneTab[]): void {
    for (const tab of tabs) {
      if (!this._tabViews.has(tab)) {
        this._tabViews.set(tab, new SelectionViewTab(tab));
      }
    }
  }
}

export class SelectionViewTab {
  private readonly _tab: SceneTab;
  @observable _selectedCategory?: Category;
  @observable readonly _categoryViews: Map<Category, SelectionViewCategory> = new Map();

  constructor(tab: SceneTab) {
    this._tab = tab;
    this._tab.categories.forEach(category => {
      this._categoryViews.set(category, new SelectionViewCategory(category));
    })
    makeAutoObservable(this);
  }

  @computed
  get categoryViews(): SelectionViewCategory[] {
    return Array.from(this._categoryViews.values());
  }

  @computed
  get tab(): SceneTab {
    return this._tab;
  }

  @computed
  get categoryIsSelected(): boolean {
    return typeof this._selectedCategory !== "undefined";
  }

  @computed
  get selectedCategory(): Category | undefined {
    return this._selectedCategory;
  }

  @computed
  get selectedCategoryView(): SelectionViewCategory | undefined {
    return typeof this._selectedCategory !== "undefined" ? this._categoryViews.get(this._selectedCategory) : undefined;
  }

  @computed
  get isSelectionCompleted(): boolean {
    if (typeof this._selectedCategory !== "undefined" && this._categoryViews.has(this._selectedCategory)) {
      return !!this.selectedCategoryView?.isSelectionCompleted;
    }
    return false;
  }

  @computed
  get isDisabled(): boolean {
    return !this.categoryIsSelected;
  }

  @action
  selectCategory(category: Category): void {
    if (!this._categoryViews.has(category)) {
      this._categoryViews.set(category, new SelectionViewCategory(category));
    }
    this._selectedCategory = category;
  }

  @action
  selectOptionValue(optionType: OptionType, value: OptionValue | undefined): void {
    if (typeof this._selectedCategory !== "undefined" && this._categoryViews.has(this._selectedCategory)) {
      this._categoryViews.get(this._selectedCategory)?.selectOptionValue(optionType, value);
    }
  }

  @action
  resetAllOptionValues(): void {
    if (typeof this._selectedCategory !== "undefined" && this._categoryViews.has(this._selectedCategory)) {
      this._categoryViews.get(this._selectedCategory)?.resetAllOptionValues();
    }
  }

  @action
  exportCurrentView(): ExportViewVariables | undefined {
    let view = new ExportViewVariables()

    if (typeof this._selectedCategory !== "undefined" && this._categoryViews.has(this._selectedCategory)) {
      let categoryView = this._categoryViews.get(this._selectedCategory);
      if (typeof categoryView === "undefined") {
        return undefined;
      }

      for (let variable of this._selectedCategory.variables) {
        if (!view.has(variable)) {
          view.set(variable, new Map<OptionType, OptionValue>());
        }

        // for (let optionType of categoryView.optionValues.keys()) {
        //   view.get(variable)?.set(optionType, categoryView.optionValues.get(optionType));
        // }
      }
    }
    return view;
  }

  @action
  initCategories(categories: Category[]): void {
    for (const category of categories) {
      if (!this._categoryViews.has(category)) {
        this._categoryViews.set(category, new SelectionViewCategory(category));
      }
    }
  }
}

export class SelectionViewCategory {
  private readonly _category: Category;
  @observable private readonly _optionValues: Map<OptionType, OptionValue | undefined> = new Map();

  constructor(category: Category) {
    this._category = category;
    makeAutoObservable(this);
  }

  @computed
  get category(): Category {
    return this._category;
  }

  @computed
  get optionTypesToShow(): OptionType[] {
    const optionsTypeToShow: OptionType[] = [];

    for (const option of this._category.options) {
      optionsTypeToShow.push(option.type);
      if (!this.isOptionTypeSelected(option.type)) {
        break;
      }
    }
    return optionsTypeToShow;
  }

  @computed
  getOptionValue(optionType: OptionType): OptionValue | undefined {
    return this._optionValues.get(optionType);
  }

  @computed
  getOptionValues(): OptionValues {
    let types = [];
    for (const option of this._category.options) {
      types[option.type] = this._optionValues.get(option.type);
    }
    return types;
  }

  @computed
  isOptionTypeSelected(optionType: OptionType): boolean {
    return typeof this._optionValues.get(optionType) !== "undefined";
  }

  @computed
  isOptionValueSelected(optionType: OptionType, optionValue: OptionValue): boolean {
    return this._optionValues.get(optionType) === optionValue;
  }

  // @computed
  // getOptionValuesHierarchicallyOrdered(): OptionValues {
  //   let optionValues: OptionValues = [];
  //
  //   for (const type of OptionTypeHierarchy) {
  //     const optionValue = this._optionValues.get(type);
  //     if (typeof optionValue === 'undefined') { // Process only selected values
  //       break;
  //     }
  //
  //     optionValues.push(optionValue);
  //   }
  //   return optionValues;
  // }

  /**
   * TODO: improve this method
   */
  @computed
  get isSelectionCompleted(): boolean {
    let previousOptionsValueCodes: (string|null)[] = [];
    let previousOptionValue: OptionValue | undefined = undefined;

    for (const optionType of OptionTypeHierarchy) {
      let optionValue = this._optionValues.get(optionType);
      if (typeof previousOptionValue !== "undefined" && typeof optionValue === "undefined") { // Skip latest undefined values
        break;
      }
      previousOptionsValueCodes.push(optionValue?.code ?? null); // Keep empty values (case no_option)
      previousOptionValue = optionValue;
    }

    return this.category.variablesTree.getOptionValues(previousOptionsValueCodes).length === 0;
  }

  @computed
  get isDisabled(): boolean {
    return false;
  }

  @computed
  getAvailableOptionValues(requestType: OptionType) {
    let previousOptionsValueCodes: (string|null)[] = [];
    for (let optionType of OptionTypeHierarchy) { // We need previous values for all options even if they are not available in category
      if (optionType === requestType) {
        break;
      }
      previousOptionsValueCodes.push(this.getOptionValue(optionType)?.code ?? null);
    }
    return this.category.variablesTree.getOptionValues(previousOptionsValueCodes);
  }

  @action
  selectOptionValue(optionType: OptionType, value: OptionValue | undefined): void {
    this._optionValues.set(optionType, value);
    this.resetOtherOptionValues(optionType);
  }

  /**
   * Reset all other hierarchical option values above the given optionType
   * @param optionType
   */
  @action
  resetOtherOptionValues(optionType: OptionType): void {
    let reset = false;
    for (const type of OptionTypeHierarchy) {
      if (type === optionType) {
        reset = true;
        continue;
      }
      if (reset) {
        this._optionValues.delete(type);
      }
    }
  }

  /**
   * Clear selection of all option values
   */
  @action
  resetAllOptionValues(): void {
    this._optionValues.clear();
  }

  @action
  resetOptionValue(optionType: OptionType): void {
    this._optionValues.delete(optionType);
  }

  /**
   * Init with first variable tree option values
   */
  @action
  initOptionValues(): void {
    if (!this._category.variablesTree.hasValues) {
      return;
    }

    let node = this._category.variablesTree.first as OptionValuesTreeRootNode;
    while (typeof node !== "undefined") {
      if (typeof node.optionType !== 'undefined' && typeof (node as OptionValuesTreeNode).optionValue !== "undefined") {
        this._optionValues.set(node.optionType, (node as OptionValuesTreeNode).optionValue);
      }
      node = node.children[0] ?? undefined;
    }
  }
}
