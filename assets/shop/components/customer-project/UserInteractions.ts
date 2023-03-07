import Project from "./model/Project";
import {AddProjectItemTaxon, Taxon} from "./model/types/Taxon";
import ProjectItem from "./model/ProjectItem";
import {ProductOptionValue} from "./model/types/ProductOptionValue";

const Classes = {
  HIDDEN_CLASS: 'u-hidden'
}

const Selectors = {
  POPIN_HANDLES_CLASS: '.popin__handles',
  POPIN_HANDLES_TEXT_CLASS: '.popin__handles__text',
  POPIN_HANDLES_BUTTON_CLASS: '.popin__handles__button',
  POPIN_HANDLES_TEXT_TEMPLATE_ID: 'popin__handles__text_template',
  POPIN_HANDLES_BUTTON_TEMPLATE_ID: 'popin__handles__button_template',
};

export default class UserInteractions {
  private _project: Project;
  private _popinHandlesTextTemplate: string;
  private _popinHandlesButtonTemplate: string;
  private _handlesToAdd: number = 0;

  private get _popinHandlesElmt() { return this._project.elmt.querySelector(Selectors.POPIN_HANDLES_CLASS); }
  private get _popinHandlesTextElmt() { return this._project.elmt.querySelector(Selectors.POPIN_HANDLES_TEXT_CLASS); }
  private get _popinHandlesButtonElmt() { return this._project.elmt.querySelector(Selectors.POPIN_HANDLES_BUTTON_CLASS); }

  constructor(project: Project) {
    this._project = project;

    this._popinHandlesTextTemplate = (<HTMLInputElement>document.getElementById(Selectors.POPIN_HANDLES_TEXT_TEMPLATE_ID)).value;
    this._popinHandlesButtonTemplate = (<HTMLInputElement>document.getElementById(Selectors.POPIN_HANDLES_BUTTON_TEMPLATE_ID)).value;
    this._popinHandlesButtonElmt.addEventListener('click', this.onClickPopinHandlesButton.bind(this));

    this.updatePopinHandles(); // Initial update onLoad
  }

  updatePopinHandles() {
    let doorsCount = this._project.countItemsTaxons([Taxon.FACADE_METOD_DOOR, Taxon.FACADE_PAX_DOOR], true, UserInteractions.doorDiscriminator);
    let drawersCount = this._project.countItemsTaxons(Taxon.FACADE_METOD_DRAWER, true, UserInteractions.doorDiscriminator);
    let handlesCount = this._project.countItemsTaxons(Taxon.ACCESSORY_HANDLE, true);
    this._handlesToAdd = (doorsCount + drawersCount) - handlesCount;

    if (this._handlesToAdd > 0) {
      this._popinHandlesTextElmt.innerHTML = this._popinHandlesTextTemplate
        .replace('%doors_count%', doorsCount.toString())
        .replace('%drawers_count%', drawersCount.toString());
      this._popinHandlesButtonElmt.innerHTML = this._popinHandlesButtonTemplate.replace('%handles_count%', this._handlesToAdd.toString());

      this._popinHandlesElmt.classList.remove(Classes.HIDDEN_CLASS);
    } else {
      this._popinHandlesElmt.classList.add(Classes.HIDDEN_CLASS);
    }
  }

  private static doorDiscriminator(item: ProjectItem) {
    return !item.options.selects.design.hasValue(ProductOptionValue.DESIGN_U_SHAPE_CODE);
  }

  private onClickPopinHandlesButton(event: Event) {
    event.preventDefault();
    if (this._handlesToAdd <= 0) {
      return;
    }
    this._project.addItem(AddProjectItemTaxon.ACCESSORY_HANDLE, this._handlesToAdd);
  }
}
