import './style.scss'
import {Category, SelectionViewCategory} from "../../types/domain";
import {Button, Col, Row} from "react-bootstrap";
import React from "react";
import {observer} from "mobx-react-lite";

export interface CategoriesListProps {
  categoryViews?: SelectionViewCategory[];
  selectedCategoryView?: SelectionViewCategory;
  handleCategorySelect: (category: Category) => void;
  disabled?: boolean;
}

export const CategoriesList = observer(({categoryViews, selectedCategoryView, handleCategorySelect, disabled}: CategoriesListProps) => {
  const importAll = (context: __WebpackModuleApi.RequireContext) => {
    let images: string[] = [];
    context.keys().forEach((item: any, index: any) => {
      images[item.replace('./', '')] = context(item);
    });
    return images;
  }
  const categoriesIcons = importAll(require.context('../../assets/icons', false, /\.(png|jpe?g|svg)$/));

  return (
    <div className='articles'>
      <Row className='g-2'>
          {categoryViews?.map((view: SelectionViewCategory) => (
              <Col key={view.category.code} xs={4}>
                <Button active={view === selectedCategoryView} disabled={!!disabled} onClick={handleCategorySelect.bind(this, view.category)}
                        value={view.category.code} variant="outline-dark" className='styler__btn-item'>
                  { typeof view.category.icon !== 'undefined' &&
                    <img width={24} src={categoriesIcons[view.category.icon]} alt={`Category ${view.category.name}`}/>
                  }
                  {view.category.name}
                </Button>
              </Col>
            )
          )}
      </Row>
    </div>
  )
})
