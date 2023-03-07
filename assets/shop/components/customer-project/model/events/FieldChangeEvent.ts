import Field from "../fields/Field";
import {SelectField} from "../fields/SelectField";
import SelectOptionField from "../fields/SelectOptionField";

export type FieldChangeEvent = {
  field: Field,
  value: any
}

export type SelectFieldChangeEvent = {
  field: SelectField,
  value: any
}

export type SelectOptionFieldChangeEvent = {
  field: SelectOptionField,
  value: any
}
