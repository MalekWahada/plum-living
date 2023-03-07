import '../style.scss'
import {OptionValueProps} from "../OptionValue";
import {Button} from "react-bootstrap";
import React from "react";
import ReactTooltip from "react-tooltip";

export const ColorOptionValue = ({optionValue, isSelected, handleClick, disabled}: OptionValueProps) => {
  return (
    <div key={optionValue.code} className='styler__color-container'>
      <label data-tip data-for={`colorSurface${optionValue.code}`}>
        <Button disabled={!!disabled} active={isSelected}
                onClick={handleClick} value={optionValue.code} variant="outline-dark"
                className='surfaceOpt styler__btn-color' data-color={optionValue.colorHex}
                style={{background: optionValue.colorHex}}>
          <span>{optionValue.name || 'unname'}</span>
        </Button>
      </label>
      <ReactTooltip id={`colorSurface${optionValue.code}`} place="bottom" type="dark" effect="float">
        {optionValue.name}
      </ReactTooltip>
    </div>
  )
}
