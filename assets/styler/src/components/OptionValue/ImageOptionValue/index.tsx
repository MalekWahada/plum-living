import '../style.scss'
import {OptionValueProps} from "../OptionValue";
import {Button, Col} from "react-bootstrap";
import testImg from "../../../assets/img/no-image.png";
import React from "react";

export const ImageOptionValue = ({optionValue, isSelected, disabled, handleClick}: OptionValueProps) => {
  return (
    <Col key={optionValue.code} xs={3}>
      <Button active={isSelected} disabled={!!disabled}
              onClick={handleClick.bind(this)} value={optionValue.code} variant="outline-dark"
              className='styler__btn-design w-100'>

        <img src={optionValue.image || testImg} alt="Select option" width="70"/>

        <span>{optionValue.name || 'unname'}</span>
      </Button>
    </Col>
  )
}
