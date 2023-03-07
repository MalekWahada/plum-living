import {isEmpty} from "../../utils";
import {
  ProjectItemOptionCode,
  ProjectItemOptionsAvailable,
  ProjectItemOptionsInterface,
  ProjectItemOptionsLocked,
  ProjectItemOptionTypes
} from "../types/ProjectItemOption";

const Classes = {
  HIDDEN_CLASS: 'u-hidden'
}

export const Selectors = {
  INPUT_CLASS: '.ps-project-item__%OPTION_CODE%',
  FIELD_CLASS: '.ps-project-item__%OPTION_CODE%-field',
  OPTIONS_CONTAINER_CLASS: '.ps-project-item__options-container',
  COLOR_BASED_SELECT_OPTION_CLASS: '.color-select-field__options .color-select-field__option'
}

export default class ProjectItemOptions  {
  private elmt: HTMLElement;
  protected _selects: ProjectItemOptionsInterface = {};

  get selects() {
    return this._selects;
  }

  get isLoading() {
    return Object.keys(this._selects).some(key => this._selects[key as keyof ProjectItemOptionsInterface].isLoading);
  }

  get isFilled() {
    return Object.keys(this._selects).every(key => this._selects[key as keyof ProjectItemOptionsInterface].exist ? !isEmpty(this._selects[key as keyof ProjectItemOptionsInterface].value) : true);
  }

  get isComplete() {
    return Object.keys(this._selects).every(key => this._selects[key as keyof ProjectItemOptionsInterface].exist ? !this._selects[key as keyof ProjectItemOptionsInterface].inputDisabled : true);
  }

  get hasOptions() { return Object.keys(this._selects).some(key => this._selects[key as keyof ProjectItemOptionsInterface].exist) }
  get hasOptionsWithMultipleSelectValues() { return Object.keys(this._selects).some(key => this._selects[key as keyof ProjectItemOptionsInterface].exist && this._selects[key as keyof ProjectItemOptionsInterface].options.length > 1) }

  get hasGlobalOptions() {
    // @ts-ignore
    return Object.keys(this._selects).some(key => this._selects[key as keyof ProjectItemOptionsInterface].exist && [ProjectItemOptionCode.DESIGN, ProjectItemOptionCode.FINISH, ProjectItemOptionCode.COLOR].includes(key));
  }

  get containerElmt() { return this.elmt.querySelector(Selectors.OPTIONS_CONTAINER_CLASS); }

  constructor(elmt: HTMLElement) {
    this.elmt = elmt;

    this.createOptionsSelects();
  }

  on(event: string, callback: (...args: any[]) => void) {
    Object.keys(this._selects).forEach(key => this._selects[key as keyof ProjectItemOptionsInterface].on(event, callback));
  }

  removeNotAvailableOptions(options: ProjectItemOptionsAvailable) {
    let optionsKeys = Object.keys(options);
    for (const optionKey in optionsKeys) {
      let optionCode = <ProjectItemOptionCode>optionsKeys[optionKey];
      if (Object.keys(this.selects).includes(optionCode) && !options[optionCode]) {
        this.selects[optionCode].remove(true);
      }
    }
  }

  disableLockedOptions(options: ProjectItemOptionsLocked) {
    let optionsKeys = Object.keys(options);
    for (const optionKey in optionsKeys) {
      let optionCode = <ProjectItemOptionCode>optionsKeys[optionKey];
      if (Object.keys(this.selects).includes(optionCode)) {
        this.selects[optionCode].disabled = options[optionCode];
      }
    }
  }

  updateWarnings(): void {
    Object.keys(this.selects).forEach(key => {
      let select = this.selects[key as keyof ProjectItemOptionsInterface];
      select.setWarning(!select.isValid);
    });
  }

  clearWarnings(): void {
    Object.keys(this.selects).forEach(key => {
      let select = this.selects[key as keyof ProjectItemOptionsInterface];
      select.clearWarning();
    });
  }

  reset(): void {
    Object.keys(this._selects).forEach(key => this._selects[key as keyof ProjectItemOptionsInterface].reset());
  }

  showContainer(): void {
    this.containerElmt.classList.remove(Classes.HIDDEN_CLASS);;
  }

  hideContainer(): void {
    this.containerElmt.classList.add(Classes.HIDDEN_CLASS);
  }

  /**
   * Create options selects dynamically from the options enum type
   * @private
   */
  private createOptionsSelects(): void {
    let options = Object.values(ProjectItemOptionCode);
    for (const optionKey in options) {
      let option = options[optionKey];
      if (Object.keys(ProjectItemOptionTypes).includes(option)) {
        this.selects[option] = new ProjectItemOptionTypes[option](option, this.elmt.querySelector(Selectors.FIELD_CLASS.replace('%OPTION_CODE%', option)), this.elmt.querySelector(Selectors.INPUT_CLASS.replace('%OPTION_CODE%', option)));
      }
    }
  }
}
