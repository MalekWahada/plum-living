import ColorBasedSelectOptionField from "../fields/ColorBasedSelectOptionField";
import SelectOptionField from "../fields/SelectOptionField";

export type ProjectItemOptionFieldTypes = typeof SelectOptionField | typeof ColorBasedSelectOptionField;
export type ProjectItemOptionField = SelectOptionField | ColorBasedSelectOptionField;

export enum ProjectItemOptionCode {
  DESIGN = 'design',
  FINISH = 'finish',
  COLOR = 'color',
  FINISH_HANDLE = 'finish_handle',
  FINISH_TAP = 'finish_tap',
  VARIANT = 'variant',
}

export const ProjectItemOptionTypes: { [key in ProjectItemOptionCode]: ProjectItemOptionFieldTypes } = {
  [ProjectItemOptionCode.DESIGN]: SelectOptionField,
  [ProjectItemOptionCode.FINISH]: ColorBasedSelectOptionField,
  [ProjectItemOptionCode.COLOR]: ColorBasedSelectOptionField,
  [ProjectItemOptionCode.FINISH_HANDLE]: SelectOptionField,
  [ProjectItemOptionCode.FINISH_TAP]: SelectOptionField,
  [ProjectItemOptionCode.VARIANT]: SelectOptionField
};

export type ProjectItemOptionsAvailable = {
  [key in ProjectItemOptionCode]?: boolean;
};

export type ProjectItemOptionsLocked = {
  [key in ProjectItemOptionCode]?: boolean;
};

export type ProjectItemOptionsInterface = {
  [key in ProjectItemOptionCode]?: ProjectItemOptionField;
};
