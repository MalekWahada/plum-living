import './style.scss'
import {Form, Row} from "react-bootstrap";
import {TabsList} from "../TabsList";
import {CategoriesList} from "../CategoriesList";
import {SelectionView} from "../../types/domain";
import {observer} from "mobx-react-lite";
import {CategoriesOptionContainer} from "../CategoriesOptionsContainer";

export interface ControlPanelProps {
  view: SelectionView,
  isLoadingView?: boolean,
}

export const ControlPanel = observer(({view, isLoadingView}: ControlPanelProps) => {
  return (
    <Form className='px-4'>
      <div className='caissons pt-4 pb-3'>
        <Row>
          <TabsList tabViews={view.tabViews}
                    selectedTabView={view.selectedTabView}
                    handleTabSelect={(tab) => {view.selectTab(tab)}}
                    disabled={!!isLoadingView} />
        </Row>
      </div>

      <CategoriesList categoryViews={view.selectedTabView?.categoryViews}
                      selectedCategoryView={view.selectedTabView?.selectedCategoryView}
                      handleCategorySelect={(category) => {view.selectedTabView?.selectCategory(category)}}
                      disabled={!!isLoadingView}/>

      <CategoriesOptionContainer categoryView={view.selectedTabView?.selectedCategoryView} disabled={!!isLoadingView}/>
    </Form>
  )
})
