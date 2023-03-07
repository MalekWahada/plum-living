import {disableField, enableField} from "../../../form-fields/form-fields";
import UIComponent from "../../../../abstract/js-toolbox/ui-component";
import {FieldChangeEvent} from "../events/FieldChangeEvent";
import {isEmpty} from "../../utils";

const Classes = {
  FIELD_WARNING_CLASS: 'field--warning',
  HIDDEN_CLASS: 'u-hidden',
  FLEX_CLASS: 'u-flex',
  INVISIBLE_CLASS: 'u-invisible'
}

export const Events = {
  ON_CHANGE: 'onChange'
}

export default class Field extends UIComponent {
  protected _field: HTMLElement;
  get field(): HTMLElement { return this._field }
  protected _input: HTMLInputElement;
  get input(): HTMLInputElement { return this._input }
  protected _current: any;
  protected _previous: any;
  private _loading: boolean = false;

  constructor(field: HTMLElement, input: HTMLElement, observeInput: boolean = false) {
    super(field ?? document); // Field can be null so we pass document instead
    this._field = field;
    this._input = <HTMLInputElement>input;

    if (this.exist) {
      this._current = this.value;
      if (observeInput) {
        input.addEventListener('input', this.onOptionChange.bind(this));
      }
      input.addEventListener('change', this.onOptionChange.bind(this));
    }
  }

  get exist(): boolean { return !!this._field && !!this._input }

  get isValid(): boolean { return !isEmpty(this.value) && !this._input.classList.contains(Classes.FIELD_WARNING_CLASS); }

  clearWarning(): void {
    this.exist && this._field.classList.remove(Classes.FIELD_WARNING_CLASS);
  }

  addWarning(): void {
    this.exist && this._field.classList.add(Classes.FIELD_WARNING_CLASS);
  }

  setWarning(state: boolean): void {
    if (state)
      this.addWarning();
    else
      this.clearWarning();
  }

  get inputDisabled(): boolean { return this.exist && this._input.disabled; }

  set disabled(state: boolean) {
    if (state)
      this.exist && disableField(this._field, true);
    else
      this.exist && enableField(this._field, true);
  }

  set hidden(state: boolean) {
    if (state)
      this.exist && this._field.classList.add(Classes.HIDDEN_CLASS);
    else
      this.exist && this._field.classList.remove(Classes.HIDDEN_CLASS);
  }

  set visible(state: boolean) {
    if (state)
      this.exist && this._field.classList.add(Classes.INVISIBLE_CLASS);
    else
      this.exist && this._field.classList.remove(Classes.INVISIBLE_CLASS);
  }

  set loading(state: boolean) {
    if (state) { // @ts-ignore
      disableField(this._field, true, 'loading');
      this._loading = true;
    }
    else {
      enableField(this._field);
      this._loading = false;
    }
  }

  get isLoading(): boolean { return this._loading; }

  set required(state: boolean) {
    this.exist && (this._input.required = state);
  }

  reset(): void {
    this.required = false;
    if (this.exist && this._input instanceof HTMLSelectElement) {
      this._input.selectedIndex = 0;
    }
  }

  get value(): any {
    return this._input ? this._input.value : null;
  }

  set value(value: any) {
    this._input && (this._input.value = value);
  }

  hasValue(value: any): boolean {
    return this.value === value;
  }

  remove(removeParent: Boolean = false): void {
    this.exist && removeParent && this._field.parentElement.remove();
    this.exist && this._field.remove();
    this._input = null;
    this._field = null;
  }

  private async onOptionChange(e: Event) {
    this.emit(Events.ON_CHANGE, <FieldChangeEvent>{ field: this, value: this.value });
  }
}
