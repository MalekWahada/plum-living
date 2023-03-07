import './style.scss'
import {Button} from "react-bootstrap";
import React from "react";
import {SceneTab, SelectionViewTab} from "../../types/domain";
import {observer} from "mobx-react-lite";

export interface TabProps {
  tabViews: SelectionViewTab[],
  selectedTabView?: SelectionViewTab,
  handleTabSelect: (tab: SceneTab) => void,
  disabled?: boolean
}

export const TabsList = observer(({tabViews, selectedTabView, handleTabSelect, disabled}: TabProps) => {
  return (
    <>
      {tabViews.map((tabView) => (
        <div key={tabView.tab.code} className='styler__tabs-container'>
          <Button active={tabView === selectedTabView} disabled={!!disabled} onClick={handleTabSelect.bind(this, tabView.tab)} value={tabView.tab.code}
                  className='styler__tabs'>
            {tabView.tab.name}
          </Button>
        </div>
      ))}
    </>
  )
})
