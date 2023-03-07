import {OptionValue} from "../../types/domain";

export interface OptionValueProps {
  optionValue: OptionValue;
  isSelected: boolean;
  disabled?: boolean;
  handleClick: (e:any) => void;
}
