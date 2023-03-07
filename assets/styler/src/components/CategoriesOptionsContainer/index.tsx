import './style.scss'
import {observer} from "mobx-react-lite";
import {SelectionViewCategory} from "../../types/domain";
import React, {ReactElement} from "react";
import {CategoriesOption} from '../CategoriesOption'

export interface CategoriesOptionsContainerProps {
  categoryView?: SelectionViewCategory,
  disabled?: boolean
}

export const CategoriesOptionContainer = observer(({categoryView, disabled}: CategoriesOptionsContainerProps) => {
  if (typeof categoryView === 'undefined') {
    return null;
  }

  const options = categoryView.category.options;
  let result: ReactElement[] = [];

  for (const option of options) {
    if (categoryView.optionTypesToShow.includes(option.type)) {
      result.push(
        <CategoriesOption key={option.type}
                          option={option}
                          categoryView={categoryView}
                          disabled={!!disabled} />
      );
    }
  }

  return (
    <>
      {result}
    </>
  )
})

