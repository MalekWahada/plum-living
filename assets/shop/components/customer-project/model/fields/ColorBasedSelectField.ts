import {SelectField} from "./SelectField";
import {changeColorSelectSelectedValue, changeColorSelectValues} from "../../../form-fields/form-fields";
import {Selectors} from "./ProjectItemOptions";

export default class ColorBasedSelectField extends SelectField {
  set value(value: any) {
    this.exist && changeColorSelectSelectedValue(this._field, value, { shouldDispatchChangeEvent: false });
  }

  get value(): any { // Getter must be redefined then the setter is
    return super.value;
  }

  refreshOptions(options: any[]): boolean {
    return this.exist && changeColorSelectValues(
      this._field,
      options,
      {
        shouldDispatchChangeEvent: false,
        missingGoesToBlank: true,
      },
    );
  }


  get options(): HTMLOptionsCollection {
    return this.elmt.querySelectorAll(Selectors.COLOR_BASED_SELECT_OPTION_CLASS);
  }

  get optionsContent(): string[] {
    return Array.from(this.options).map(option => option.dataset.value).filter(value => value.trim().length > 0);
  }
}
