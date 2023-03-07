import {Option, OptionSelectorType, OptionType, OptionValue, SelectionViewCategory} from "../../types/domain";
import React, {Fragment} from "react";
import {observer} from "mobx-react-lite";
import {Row} from "react-bootstrap";
import {ColorOptionValue, ImageOptionValue} from "../OptionValue";

export interface CategoriesOptionProps {
  option: Option,
  categoryView: SelectionViewCategory,
  disabled?: boolean
}

export const CategoriesOption = observer(({option, categoryView, disabled}: CategoriesOptionProps) => {
  const optionValues = categoryView.getAvailableOptionValues(option.type);

  if (optionValues.length === 0) {
    return null;
  }

  return (
    <Row className='mt-4 g-2' key={option.type}>
      <h6 className="styler__catTitle text-start">{option.name}</h6>
      {optionValues.map((optionValue: OptionValue) => (
        <Fragment key={optionValue.code}>
          {option.selector === OptionSelectorType.IMAGE &&
            <ImageOptionValue optionValue={optionValue}
                              isSelected={!!categoryView?.isOptionValueSelected(option.type, optionValue)}
                              handleClick={() => {categoryView?.selectOptionValue(option.type, optionValue)}}
                              disabled={!!disabled} />
          }
          {option.selector === OptionSelectorType.COLOR &&
            <ColorOptionValue optionValue={optionValue}
                              isSelected={!!categoryView?.isOptionValueSelected(option.type, optionValue)}
                              handleClick={() => {categoryView?.selectOptionValue(option.type, optionValue)}}
                              disabled={!!disabled} />
          }
        </Fragment>
      ))}
    </Row>
  )
})

