import {OptionType} from "./Option";
import {OptionValue} from "./OptionValue";
import {OptionValuesTreeNode, VariablesOptionValuesTree} from "./VariablesOptionValuesTree";
import {action, computed, makeAutoObservable, observable} from "mobx";

export interface VariablesSelectionsValue { [key: string]: Map<OptionType, OptionValue | undefined> }
// export type ExtractVariableSelectionsValue = { [key in OptionType]?: OptionValue | undefined }

export class VariablesSelections {
  @observable.deep private readonly _values: VariablesSelectionsValue = {};
  @observable private _isInitialized: boolean = false;
  @observable private _updateVersion: number = 0;

  constructor() {
    makeAutoObservable(this);
  }

  @computed
  get isInitialized(): boolean {
    return this._isInitialized;
  }

  @computed
  get variables(): string[] {
    return Object.keys(this._values);
  }

  @computed
  get update(): number {
    return this._updateVersion;
  }

  @computed
  extractVariablesOptionValues(): VariablesSelectionsValue {
    let result: VariablesSelectionsValue = {};
    for (const variable of Object.keys(this._values)) {
      result[variable] = new Map(this._values[variable]);
    }
    return result;
  }

  @computed
  getVariableOptionValue(variable: string, optionType: OptionType): OptionValue | undefined {
    if (!this._values.hasOwnProperty(variable)) {
      return undefined;
    }
    return this._values[variable].get(optionType);
  }

  @action
  selectOptionValue(variable: string, optionType: OptionType, value: OptionValue | undefined): void {
    if (!this._values.hasOwnProperty(variable)) {
      this._values[variable] = new Map<OptionType, OptionValue>();
    }
    this._values[variable].set(optionType, value);
    this._updateVersion++;
  }

  @action
  selectVariablesOptionValue(variables: string[], optionType: OptionType, value: OptionValue | undefined): void {
    for (const variable of variables) {
      this.selectOptionValue(variable, optionType, value);
    }
  }

  @action
  resetOptionValue(variable: string, optionType: OptionType): void {
    if (!this._values.hasOwnProperty(variable)) {
      return;
    }
    this._values[variable].delete(optionType);
  }

  @action
  resetVariablesOptionValue(variables: string[], optionType: OptionType): void {
    for (const variable of variables) {
      this.resetOptionValue(variable, optionType);
    }
  }

  @action
  initWithTree(tree: VariablesOptionValuesTree): void {
    for (const variable of tree.keys) {
      if (!this._values.hasOwnProperty(variable)) {
        this._values[variable] = new Map<OptionType, OptionValue>();
      }

      let node = tree.getRootNode(variable);
      while (typeof node !== "undefined") {
        if (typeof node.optionType !== "undefined" && typeof (node as OptionValuesTreeNode).optionValue !== "undefined") {
          this._values[variable].set(node.optionType, (node as OptionValuesTreeNode).optionValue);
        }
        node = node.children[0] ?? undefined; // Select first child
      }
    }
    this._isInitialized = true;
    this._updateVersion++; // Initial update
  }
}
