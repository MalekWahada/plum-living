import ColorBasedSelectField from "./ColorBasedSelectField";
import {ProjectItemOptionCode} from "../types/ProjectItemOption";

export default class ColorBasedSelectOptionField extends ColorBasedSelectField {
  protected readonly _optionCode: ProjectItemOptionCode;

  constructor(optionCode: ProjectItemOptionCode, field: HTMLElement, input: HTMLElement, observeInput: boolean = false) {
    super(field, input, observeInput);
    this._optionCode = optionCode;
  }

  get optionCode(): ProjectItemOptionCode {
    return this._optionCode;
  }
}
