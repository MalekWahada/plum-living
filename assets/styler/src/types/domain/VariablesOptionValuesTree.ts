import {OptionType} from "./Option";
import {OptionValue, OptionValues} from "./OptionValue";

export interface VariablesOptionValuesTreeValue {
    [key: string]: OptionValuesTreeRootNode;
}

export class VariablesOptionValuesTree {
  private _values: VariablesOptionValuesTreeValue = {};

  get hasValues(): boolean {
    return Object.keys(this._values).length > 0;
  }

  get first(): OptionValuesTreeRootNode | undefined {
    return this.hasValues ? this._values[Object.keys(this._values)[0]] : undefined;
  }

  get keys(): string[] {
    return Object.keys(this._values);
  }

  getOptionValues(hierarchyCodes: (string|null)[]): OptionValues {
    let values: OptionValues = [];
    for (let value of Object.values(this._values)) {
      values = [...new Set([...values, ...value.getChildValues(hierarchyCodes)])];
    }
    return values;
  }

  getRootNode(key: string): OptionValuesTreeRootNode {
    let root = this._values[key] ?? new OptionValuesTreeRootNode();

    if (!this._values.hasOwnProperty(key)) {
      this._values[key] = root;
    }

    return root;
  }

  getRootNodes(filterKey?: string[]): VariablesOptionValuesTreeValue {
    if (typeof filterKey === 'undefined') {
      return this._values;
    }

    return Object.fromEntries(
      Object
        .entries(this._values)
        .filter(([variable]) => filterKey.includes(variable))
    )
  }

  mergeValues(values: VariablesOptionValuesTreeValue) {
    for (let [variable, node] of Object.entries(values)) {
      this._values[variable] = node;
    }
  }
}

abstract class BaseOptionValuesTreeNode {
  private _values: OptionValuesTreeNode[] = [];
  private readonly _optionType?: OptionType;
  protected _parentsNode: BaseOptionValuesTreeNode[] = [];
  private _optionValue?: OptionValue;

  protected constructor(nodeType?: OptionType) {
    this._optionType = nodeType;
  }

  get optionValue(): OptionValue | undefined {
    return this._optionValue;
  }

  set optionValue(value: OptionValue | undefined) {
    this._optionValue = value;
  }

  get hasOptionValue(): boolean {
    return typeof this._optionValue !== 'undefined';
  }

  get parentsNode(): BaseOptionValuesTreeNode[] {
    return this._parentsNode;
  }

  addChild(optionType: OptionType) {
    const child = new OptionValuesTreeNode(optionType, [...this._parentsNode, this]);
    this._values.push(child);
    return child;
  }

  findOrCreateChild(optionType: OptionType, optionValue: OptionValue): OptionValuesTreeNode {
    let child = this._values.find(node => node.optionType === optionType);

    if (typeof child === 'undefined') {
      child = this.addChild(optionType);
      child.optionValue = optionValue;
    }
    return child;
  }

  get optionType(): OptionType | undefined {
    return this._optionType;
  }

  get childrenValues(): OptionValues {
    return this._values
      .filter(node => typeof node.optionValue !== 'undefined')
      .map(node => node.optionValue) as OptionValue[];
  }

  get children(): OptionValuesTreeNode[] {
    return this._values;
  }

  get hasChildren(): boolean {
    return this._values.length > 0;
  }

  get hasUniqueChild(): boolean {
    return this._values.length === 1;
  }

  getChildValues(hierarchyCodes: (string|null)[]): OptionValues {
    if (hierarchyCodes.length === 0) { // No hierarchy codes, return all children values
      return this.childrenValues; // Return all values for current node
    }

    const [first, ...rest] = hierarchyCodes;
    if (first === null && this.hasChildren) { // No option value selected (case no_option), return first child values
      return this._values[0].getChildValues(rest);
    }

    const child = this._values.find(node => node.optionValue?.code === first);
    if (typeof child === 'undefined') {
      return [];
    }
    return child.getChildValues(rest);
  }
}

export class OptionValuesTreeRootNode extends BaseOptionValuesTreeNode {
  constructor() {
    super(undefined);
  }
}

export class OptionValuesTreeNode extends BaseOptionValuesTreeNode {
  constructor(type: OptionType, parents: BaseOptionValuesTreeNode[]) {
    super(type);
    this._parentsNode = parents;
  }
}
