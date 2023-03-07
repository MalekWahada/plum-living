import SelectOptionField from "./SelectOptionField";
import {ProjectItemOptionCode, ProjectItemOptionField} from "../types/ProjectItemOption";
import ColorBasedSelectOptionField from "./ColorBasedSelectOptionField";

export const Selectors = {
  DESIGN_FIELD_CLASS: '.ps-project__design-field',
  DESIGN_INPUT_ID: '#app_project_design',
  FINISH_INPUT_ID: '#app_project_finish',
  FINISH_FIELD_CLASS: '.ps-project__finish-field',
  COLOR_INPUT_ID: '#app_project_color',
  COLOR_FIELD_CLASS: '.ps-project__color-field',
}

export default class ProjectGlobalOptions {
  private lockColorSelectIfFinishIs: number[];

  protected _design: ProjectItemOptionField;
  get design() { return this._design; }
  protected _finish: ProjectItemOptionField;
  get finish() { return this._finish; }
  protected _color: ProjectItemOptionField;
  get color() { return this._color; }

  constructor(elem: HTMLElement) {
    this._design = new SelectOptionField(ProjectItemOptionCode.DESIGN, elem.querySelector(Selectors.DESIGN_FIELD_CLASS), elem.querySelector(Selectors.DESIGN_INPUT_ID));
    this._finish = new ColorBasedSelectOptionField(ProjectItemOptionCode.FINISH, elem.querySelector(Selectors.FINISH_FIELD_CLASS), elem.querySelector(Selectors.FINISH_INPUT_ID));
    this._color = new ColorBasedSelectOptionField(ProjectItemOptionCode.COLOR, elem.querySelector(Selectors.COLOR_FIELD_CLASS), elem.querySelector(Selectors.COLOR_INPUT_ID));

    this.lockColorSelectIfFinishIs = JSON.parse((<HTMLInputElement>document.getElementById('finishes_with_disabled_color')).value);
    this.colorSelectConditionalDisplay();
  }

  clearWarnings(): void {
    this._design.clearWarning();
    this._finish.clearWarning();
    this._color.clearWarning();
  }

  updateWarnings(): void {
    this._design.setWarning(!this._design.isValid);
    this._finish.setWarning(!this._finish.isValid);
    this._color.setWarning(!this._color.isValid);
  }

  set disabled(state: boolean) {
    this._design.disabled = state;
    this._finish.disabled = state;
    if (!state) {
      !this.shouldLockColorField && (this._color.disabled = false);
    }
    else {
      this._color.disabled = state;
    }
  }

  on(event: string, callback: (...args: any[]) => void) {
    this._design.on(event, callback);
    this._finish.on(event, callback);
    this._color.on(event, callback);
  }

  colorSelectConditionalDisplay() {
      this._color.disabled = this.shouldLockColorField;
  }

  private get shouldLockColorField() { return this.lockColorSelectIfFinishIs.indexOf(this._finish.value) !== -1; }
}
