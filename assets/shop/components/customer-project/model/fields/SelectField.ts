import {changeSelectSelectedValue, changeSelectValues} from "../../../form-fields/form-fields";
import Field from "./Field";

export class SelectField extends Field {
 set value(value: any) {
   this.exist && changeSelectSelectedValue(this._field, value, { shouldDispatchChangeEvent: false });
 }

  get value(): any { // Getter must be redefined then the setter is
    return super.value;
  }

  get options(): HTMLOptionsCollection {
   return (<HTMLSelectElement><unknown>this._input).options
 }

 get optionsContent(): string[] {
   return Array.from(this.options).map(option => option.value).filter(value => value.trim().length > 0);
 }

 get selectedIndex(): number {
   return (<HTMLSelectElement><unknown>this._input).selectedIndex
 }

 get selectedDataset(): DOMStringMap {
   return (<HTMLSelectElement><unknown>this._input).options[this.selectedIndex].dataset
 }

 refreshOptions(options: any[]): boolean {
   return this.exist && changeSelectValues(
     this._field,
     options,
     {
       shouldDispatchChangeEvent: false,
       missingGoesToBlank: true,
     },
   );
 }

 emptyOptions() {
   return this.refreshOptions([{ text: '', value: null }]);
 }
}
