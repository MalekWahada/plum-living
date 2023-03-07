import UIComponent from "../../../abstract/js-toolbox/ui-component";
// @ts-ignore
import getSymbolFromCurrency from "currency-symbol-map";
import Project from "./Project";
import {isEmpty, isNothing} from "../utils";
import ProjectItemOptions from "./fields/ProjectItemOptions";
import Field, {Events as FieldEvents} from "./fields/Field";
import {CURRENCY_DIVIDER, moneyFormat} from "../money-format";
import {SelectField} from "./fields/SelectField";
import {ProjectItemPropertyChangeEvent, PropertyType} from "./events/ProjectItemPropertyChangeEvent";
import {ProjectItemRemoveEvent} from "./events/ProjectItemRemoveEvent";
import {FieldChangeEvent, SelectFieldChangeEvent} from "./events/FieldChangeEvent";
import {ProjectItemDetail} from "./types/ProjectItemsDetails";
import {EventType, ProjectItemUpdatedDetailsEvent} from "./events/ProjectItemUpdatedDetailsEvent";
import UIEvent from "./events/UIEvent";
import {ProjectItemPayload} from "./types/ProjectItemPayload";
import {
  ProjectItemOptionCode,
  ProjectItemOptionsAvailable,
  ProjectItemOptionsInterface,
  ProjectItemOptionsLocked
} from "./types/ProjectItemOption";
import ProjectItemIconHelper from "../ProjectItemIconHelper";

const Classes = {
  INVALID_CLASS: 'ps-project-item--invalid',
  LOCKED_CLASS: 'ps-project-item--locked',
  HIDDEN_CLASS: 'u-hidden'
}

export const Selectors = {
  HEADER_CLASS: '.ps-project-item__header',
  NAME_CLASS: '.ps-project-item__name',
  PRICE_CLASS: '.ps-project-item__price',
  UNIT_PRICE_CLASS: '.ps-project-item__unit-price',
  UNIT_PRICE_CONTAINER_CLASS: '.ps-project-item__unit-price-container',
  TOTAL_PRICE_CLASS: '.ps-project-item__total-price',
  TOTAL_PRICE_CONTAINER_CLASS: '.ps-project-item__total-price-container',
  ID_INPUT_CLASS: '.ps-project-item__id',
  GROUP_ID_INPUT_CLASS: '.ps-project-item__group_id',
  QUANTITY_INPUT_CLASS: '.ps-project-item__quantity',
  QUANTITY_FIELD_CLASS: '.ps-project-item__quantity-field',
  TYPE_INPUT_CLASS: '.ps-project-item__type',
  TYPE_FIELD_CLASS: '.ps-project-item__type-field',
  COMMENT_CONTAINER_CLASS: '.ps-project-item__comment-container',
  COMMENT_INPUT_CLASS: '.ps-project-item__comment',
  COMMENT_FIELD_CLASS: '.ps-project-item__comment-field',
  COMMENT_BUTTON_CLASS: '.ps-project-item__comment-button',
  REMOVE_BUTTON_CLASS: '.ps-project-item__remove-button',
  INPUTS_NAME_PREFIX: 'app_project[items]',
  DIVS_ID_PREFIX: 'app_project_items'
};

export const Events = {
  BEFORE_PROPERTY_CHANGE: 'beforePropertyChange',
  PROPERTY_CHANGE: 'propertyChange',
  RESET: 'reset',
  REQUEST_REMOVAL: 'requestRemoval',
  UPDATE_DETAILS: 'updateDetails',
  UPDATE_TOTAL: 'updateTotal'
};

type FieldsLockType = {
  type?: boolean,
  quantity?: boolean,
  comment?: boolean
};

export default class ProjectItem extends UIComponent {
  private readonly _project: Project;
  private _itemIndex: number;
  readonly channelCode = (<HTMLInputElement>document.getElementById('channel-code')).value;
  readonly currencyCode = (<HTMLInputElement>document.getElementById('currency-code')).value;

  readonly currencySymbol = getSymbolFromCurrency(this.currencyCode);
  private readonly _options: ProjectItemOptions;
  get options(): ProjectItemOptions { return this._options }
  private readonly _type: SelectField;
  get type() { return this._type; }
  private readonly _comment: Field;
  get comment() { return this._comment; }
  private readonly _quantity: Field;
  get quantity() { return this._quantity; }
  private _isLoadingVariantDetails: boolean;
  private _isLoadingProductDetails: boolean;

  get headerElmt() { return this.elmt.querySelector(Selectors.HEADER_CLASS); }
  get nameElmt() { return this.elmt.querySelector(Selectors.NAME_CLASS); }
  get priceElmt() { return this.elmt.querySelector(Selectors.PRICE_CLASS); }
  get unitPriceElmt() { return this.elmt.querySelector(Selectors.UNIT_PRICE_CLASS); }
  get unitPriceContainerElmt() { return this.elmt.querySelector(Selectors.UNIT_PRICE_CONTAINER_CLASS); }
  get totalPriceElmt() { return this.elmt.querySelector(Selectors.TOTAL_PRICE_CLASS); }
  get totalPriceContainerElmt() { return this.elmt.querySelector(Selectors.TOTAL_PRICE_CONTAINER_CLASS); }
  get commentContainerElmt() { return this.elmt.querySelector(Selectors.COMMENT_CONTAINER_CLASS); }
  get idInputElmt() { return this.elmt.querySelector(Selectors.ID_INPUT_CLASS); }
  get groupIdInputElmt() { return this.elmt.querySelector(Selectors.GROUP_ID_INPUT_CLASS); } // Used for new items

  get taxons() { return !isEmpty(this.elmt.dataset.taxons) ? this.elmt.dataset.taxons.split(',') : []; }
  set taxons(value) { this.elmt.dataset.taxons = Array.isArray(value) ? value.join(',') : String(value); }
  get unitPrice() { return parseInt(this.totalPriceElmt.dataset.unitPrice); }
  set unitPrice(value) {
    this.totalPriceElmt.dataset.unitPrice = value;
    this.unitPriceElmt.innerHTML = `${this.formattedUnitPrice} ${this.currencySymbol}`;
    this.unitPriceContainerElmt.classList[value > 0 ? 'remove' : 'add']('c-grey-light');
  }
  get totalPrice() { return parseInt(this._quantity.value) * this.unitPrice; }
  get formattedUnitPrice() { return moneyFormat.format(this.unitPrice / CURRENCY_DIVIDER).replace('.', ','); }
  get formattedTotalPrice() { return moneyFormat.format(this.totalPrice / CURRENCY_DIVIDER).replace('.', ','); }

  get id() {
    return this.isNewItem ? this._type.value : this.idInputElmt.value;
  }
  get isNewItem() { return 'newItem' in this.elmt.dataset; }
  get itemIndex(): number { return this._itemIndex; }
  set itemIndex(value: number) {
    this.changeDivsIndex(value)
    this._itemIndex = value;
  }

  get isValid() { return this.elmt.querySelectorAll('.field--warning').length === 0; }
  get isLoading() {
    return this._options.isLoading
      || this._isLoadingVariantDetails
      || this._isLoadingProductDetails;
  }
  get isFilled() { return !isEmpty(this.id) && this._options.isFilled; }
  get isComplete() { return this.isFilled && this._options.isComplete; }

  constructor(elem: HTMLElement, project: Project, itemIndex: number) {
    super(elem);
    this._project = project;
    this._itemIndex = itemIndex;

    this._options = new ProjectItemOptions(elem);
    this._type = new SelectField(elem.querySelector(Selectors.TYPE_FIELD_CLASS), elem.querySelector(Selectors.TYPE_INPUT_CLASS))
    this._comment = new Field(elem.querySelector(Selectors.COMMENT_FIELD_CLASS), elem.querySelector(Selectors.COMMENT_INPUT_CLASS), true)
    this._quantity = new Field(elem.querySelector(Selectors.QUANTITY_FIELD_CLASS), elem.querySelector(Selectors.QUANTITY_INPUT_CLASS))

    // Handlers
    this._type.on(FieldEvents.ON_CHANGE, this.onTypeChange.bind(this));
    this._comment.on(FieldEvents.ON_CHANGE, this.onCommentChange.bind(this));
    this._quantity.on(FieldEvents.ON_CHANGE, this.onQuantityChange.bind(this));
    this._options.on(FieldEvents.ON_CHANGE, this.onOptionChange.bind(this))
    this.elmt.querySelector(Selectors.COMMENT_BUTTON_CLASS).addEventListener('click', this.onClickCommentButton.bind(this));
    this.elmt.querySelector(Selectors.REMOVE_BUTTON_CLASS).addEventListener('click', this.onClickRemoveButton.bind(this));
  }

  hasTaxon(taxon: string) {
    return this.taxons.includes(taxon);
  }

  updateTotal(): void {
    this.totalPriceElmt.innerHTML = `${this.formattedTotalPrice} ${this.currencySymbol}`;

    if (parseInt(this._quantity.value) > 1 && this.unitPrice !== 0) {
      this.totalPriceContainerElmt.classList.remove(Classes.HIDDEN_CLASS);
    } else {
      this.totalPriceContainerElmt.classList.add(Classes.HIDDEN_CLASS);
    }

    this.emit(Events.UPDATE_TOTAL, { item: this });
  }

  /**
   * Set the new itemId after a successful autosave
   * @param newId
   * @param newIndex
   */
  validateAsSavedItem(newId: string, newIndex: number): void {
    if (this.isNewItem) {
      this.idInputElmt.value = newId;
      delete this.elmt.dataset.newItem;
      // When posting the form (POST), the item index is used to match with the database collection.
      // After an autosave, we must update the form index to be consistent with the backend collection.
      this.itemIndex = newIndex;
    }
  }

  /**
   * Export payload data of the item
   * @param useGlobalOptionIfMissing
   * @param getSaveData
   */
  getData(useGlobalOptionIfMissing: boolean = false, getSaveData = false): ProjectItemPayload {
    let data: ProjectItemPayload = {
      design: this._options.selects.design.value || (useGlobalOptionIfMissing ? this._project.globalOptions.design.value : null),
      finish: this._options.selects.finish.value || (useGlobalOptionIfMissing ? this._project.globalOptions.finish.value : null),
      color: this._options.selects.color.value || (useGlobalOptionIfMissing ? this._project.globalOptions.color.value : null),
      finish_handle: this._options.selects.finish_handle.value,
      finish_tap: this._options.selects.finish_tap.value,
      variant: this._options.selects.variant.value
    }
    data[this.isNewItem ? 'groupId' : 'itemId'] = this.id;
    if (getSaveData) {
      data.quantity = parseInt(this._quantity.value);
      if (!isEmpty(this._comment.value)) {
        data.comment = this._comment.value;
      }
    }
    return data;
  }

  /**
   * Lock or unlock the item
   * Can lock fields and options
   * @param lock
   * @param flags
   */
  lockUnlock(lock: boolean, flags: FieldsLockType & ProjectItemOptionsLocked = undefined): void {
    let availableOptions = Object.values(ProjectItemOptionCode);
    let availableFlags: ProjectItemOptionsLocked = {};
    for (let optionKey in availableOptions) {
      availableFlags[<ProjectItemOptionCode>availableOptions[optionKey]] = lock;
    }

    let defaultFlags = Object.assign({
      type: lock,
      quantity: lock,
      comment: lock,
    }, availableFlags);

    flags = flags === undefined ? defaultFlags : Object.assign(defaultFlags, flags);

    this._type.disabled = flags.type;
    this._quantity.disabled = flags.quantity;
    this._comment.disabled = flags.comment;
    this._options.disableLockedOptions(flags);

    if (lock)
      this.elmt.classList.add(Classes.LOCKED_CLASS);
    else
      this.elmt.classList.remove(Classes.LOCKED_CLASS);
  }

  markAsLoading(): void {
    this._isLoadingVariantDetails = true;
    this._isLoadingProductDetails = true;
    this.priceElmt.setAttribute('data-loading', true);
  }

  unmarkAsLoading(): void {
    this._isLoadingVariantDetails = false;
    this._isLoadingProductDetails = false;
    this.priceElmt.removeAttribute('data-loading');
  }

  /**
   * Clear fields warnings
   */
  clearWarnings(): void {
    this._type.clearWarning();
    this._comment.clearWarning();
    this._quantity.clearWarning();
    this._options.clearWarnings();
    this.headerElmt.classList.remove(Classes.INVALID_CLASS);
  }

  /**
   * Update warnings depending on option's validity
   */
  updateWarnings(): void {
    this._options.updateWarnings();
  }

  /**
   * Update item with API request details
   * Note: Autofill is already made by backend and don't have te be handled here
   * @param details
   */
  updateDetails(details: ProjectItemDetail): void {
    if (typeof details !== 'object' || !details || 'error' in details) { // PROJECT_ITEM_DETAIL_NOT_FOUND
      this.finishIncompleteUpdateDetails(details);
      return;
    }

    const options = details.options;
    const valid = details.validOptions;

    // Options are unlocked (enabled) by default
    const shouldLock: FieldsLockType & ProjectItemOptionsLocked = {};
    Object.values(ProjectItemOptionCode).forEach(x => shouldLock[x] = false); // Generate default object

    // Add invalid if some options are invalid
    if (Object.values(valid).some(v => v === false)) {
      this.headerElmt.classList.add(Classes.INVALID_CLASS);
    }

    // Design
    if (options.includes(ProjectItemOptionCode.DESIGN) && ProjectItemOptionCode.DESIGN in valid) {
      this._options.selects.design.refreshOptions(details.designs);

      if (!valid.design) {
        shouldLock.finish = true;
        shouldLock.color = true;
      }
    } else {
      this._options.selects.design.emptyOptions();
      shouldLock.design = true;
    }

    // Finish
    if (options.includes(ProjectItemOptionCode.FINISH) && ProjectItemOptionCode.FINISH in valid) {
      this._options.selects.finish.refreshOptions(details.finishes);

      if (!valid.finish && !shouldLock.finish) {
        shouldLock.color = true;
      }
    } else {
      this._options.selects.finish.emptyOptions();
      shouldLock.finish = true;
    }

    // Color
    if (options.includes(ProjectItemOptionCode.COLOR) && ProjectItemOptionCode.COLOR in valid) {
      this._options.selects.color.refreshOptions(details.colors);
    } else {
      this._options.selects.color.emptyOptions();
      shouldLock.color = true;
    }

    // Handle finish
    if (options.includes(ProjectItemOptionCode.FINISH_HANDLE) && ProjectItemOptionCode.FINISH_HANDLE in valid) {
      this._options.selects.finish_handle.refreshOptions(details.handleFinishes);
    } else {
      this._options.selects.finish_handle.emptyOptions();
      shouldLock.finish_handle = true;
    }

    // Tap finish
    if (options.includes(ProjectItemOptionCode.FINISH_TAP) && ProjectItemOptionCode.FINISH_TAP in valid) {
      this._options.selects.finish_tap.refreshOptions(details.tapFinishes);
    } else {
      this._options.selects.finish_tap.emptyOptions();
      shouldLock.finish_tap = true;
    }

    // Variant (no options)
    if (isEmpty(options)
      && 'variants' in details) {
      this._options.selects.variant.disabled = true;
      this._options.selects.variant.refreshOptions(details.variants);
    } else {
      this._options.selects.variant.disabled = true;
    }

    // If any options select have more than one value
    if (this._options.hasOptionsWithMultipleSelectValues) {
      this._options.showContainer();
    }

    if (this.isFilled) {
      this.finishValidUpdateDetails(details);
    } else {
      this.finishInvalidUpdateDetails(details, shouldLock);
    }
  }

  /**
   * For new item only
   * Reset the current item and show type selection field
   * when no type is selected
   */
  private resetFormToNoType(): void {
    this._type.hidden = false;

    this.nameElmt.innerHTML = '';
    this.unitPrice = 0;
    this._type.required = false;
    this._quantity.required = true;
    this._quantity.value = 1;
    this.headerElmt.classList.add(Classes.HIDDEN_CLASS);

    this._comment.value = '';
    this.commentContainerElmt.classList.add(Classes.HIDDEN_CLASS);

    if (this._options.hasOptions) {
      this._options.hideContainer();
      this._options.reset();
    }

    this.updateTotal();
    this.emit(Events.RESET);
  }

  /**
   * For new item only
   * Prepare the current item with the selected type field
   * when a type is selected
   */
  private prepareFormForType(availableOptions: ProjectItemOptionsAvailable, taxonsDataset: string): void {
    if (this._options.hasOptions) {
      this._type.hidden = true;
      this.nameElmt.innerHTML = this._type.options[this._type.selectedIndex].innerHTML;
      this._type.required = true;
    }

    this._options.removeNotAvailableOptions(availableOptions);
    this.taxons = taxonsDataset;
    ProjectItemIconHelper.updateProjectIcon(this);

    this._quantity.required = true;
    isNothing(this._quantity.value) && (this._quantity.value = 1);
    this.headerElmt.classList.remove(Classes.HIDDEN_CLASS);
    this.commentContainerElmt.classList.remove(Classes.HIDDEN_CLASS);
    // Options container will be shown if there is any option with valued in updateDetails()

    // Set data from global options
    if (this._options.selects.design.exist) {
      this._options.selects.design.value = this._project.globalOptions.design.value;
    }

    if (this._options.selects.finish.exist) {
      this._options.selects.finish.value = this._project.globalOptions.finish.value;
    }

    if (this._options.selects.color.exist) {
      this._options.selects.color.value = this._project.globalOptions.color.value;
    }

    // Set all existing options required
    Object.keys(this._options.selects).forEach((key: keyof ProjectItemOptionsInterface) => {
      if (this._options.selects[key].exist) {
        this._options.selects[key].required = true;
      }
    });
  }

  /**
   * Called after a VALID details update
   * Unlock the item
   * @param details
   */
  private finishValidUpdateDetails(details: ProjectItemDetail): void {
    this.unitPrice = parseInt(details.unitPrice ?? '0');
    this.updateTotal();
    this.clearWarnings();
    this.lockUnlock(false);
    this.unmarkAsLoading();
    this.emit(Events.UPDATE_DETAILS, <ProjectItemUpdatedDetailsEvent>{
      item: this,
      type: EventType.VALID,
      value: details
    })
  }

  /**
   * Called after an INVALID details update
   * Unlock the item
   * @param details
   * @param shouldLock
   */
  private finishInvalidUpdateDetails(details: ProjectItemDetail, shouldLock: FieldsLockType & ProjectItemOptionsLocked = {}): void {
    this.unitPrice = 0;
    this.updateTotal();
    this.lockUnlock(false, shouldLock);
    this.unmarkAsLoading();
    this.emit(Events.UPDATE_DETAILS, <ProjectItemUpdatedDetailsEvent>{
      item: this,
      type: EventType.INVALID,
      value: details
    })
  }

  /**
   * Called after an INCOMPLETE details update
   * Unlock the item
   * @param details
   */
  private finishIncompleteUpdateDetails(details: ProjectItemDetail): void {
    this.unitPrice = 0;
    this.updateTotal();
    this.lockUnlock(false);
    this.unmarkAsLoading();
    this.emit(Events.UPDATE_DETAILS, <ProjectItemUpdatedDetailsEvent>{
      item: this,
      type: EventType.INCOMPLETE,
      value: details
    })
  }

  private onQuantityChange(e: UIEvent<FieldChangeEvent>): void {
    this.updateTotal();

    // Delete item if quantity is 0
    if (e.detail.value === '0') {
      this.emit('requestRemoval', <ProjectItemRemoveEvent>{ item: this });
      return;
    }

    this.emit(Events.PROPERTY_CHANGE, <ProjectItemPropertyChangeEvent>{
      item: this,
      propertyType: PropertyType.QUANTITY,
      property: e.detail.field,
      value: this._quantity.value
    });
  }

  private onCommentChange(e: UIEvent<FieldChangeEvent>): void {
    this.emit(Events.PROPERTY_CHANGE, <ProjectItemPropertyChangeEvent>{
      item: this,
      propertyType: PropertyType.COMMENT,
      property: e.detail.field,
      value: this._comment.value
    });
  }

  private onOptionChange(e: UIEvent<FieldChangeEvent>): void {
    this.emit(Events.PROPERTY_CHANGE, <ProjectItemPropertyChangeEvent>{
      item: this,
      propertyType: PropertyType.OPTION,
      property: e.detail.field,
      value: e.detail.value
    });
  }

  private async onTypeChange(e: UIEvent<SelectFieldChangeEvent>): Promise<void> {
    this.emit(Events.BEFORE_PROPERTY_CHANGE, <ProjectItemPropertyChangeEvent>{
      item: this,
      propertyType: PropertyType.TYPE,
      property: e.detail.field,
      value: this._type.value
    });

    let optionsDataset = e.detail.field.selectedDataset['options'] ?? '';
    let taxonsDataset = e.detail.field.selectedDataset['taxons'] ?? '';
    let availableOptions: ProjectItemOptionsAvailable = {};

    // Parse input data-options dataset into ProjectItemOptionsAvailable type
    if (typeof optionsDataset === 'string') {
      let availableOptionCodes = optionsDataset.split(',');
      let options = Object.values(ProjectItemOptionCode);
      for (let optionKey in options) {
        let optionCode = <ProjectItemOptionCode>options[optionKey];
        availableOptions[optionCode] = availableOptionCodes.includes(optionCode);
      }
    }

    if (isEmpty(this._type.value)) {
      this.resetFormToNoType();
    } else {
      this.groupIdInputElmt.value = this._type.value;
      this.prepareFormForType(availableOptions, taxonsDataset);
    }

    this.emit(Events.PROPERTY_CHANGE, <ProjectItemPropertyChangeEvent>{
      item: this,
      propertyType: PropertyType.TYPE,
      property: e.detail.field,
      value: this._type.value
    });
  }

  private onClickCommentButton(e: Event): void {
    this._comment.visible = false;
    this.elmt.querySelector(Selectors.COMMENT_BUTTON_CLASS).classList.add(Classes.HIDDEN_CLASS);
  }

  private onClickRemoveButton(e: Event): void {
    this.emit('requestRemoval', <ProjectItemRemoveEvent>{ item: this });
  }

  /**
   * Change the input form index and all ids
   * @param value
   * @private
   */
  private changeDivsIndex(value: number): void {
    // Replace inputs
    Array.from(this.elmt.querySelectorAll(`[name^='${Selectors.INPUTS_NAME_PREFIX}'`)).forEach((input: HTMLInputElement) => {
      input.name = input.name.replace(`[${this._itemIndex}]`, `[${value}]`)
    });
    // Replace divs IDs
    Array.from(this.elmt.querySelectorAll(`[id^='${Selectors.DIVS_ID_PREFIX}'`)).forEach((div: HTMLElement) => {
      div.id = div.id.replace(`_${this._itemIndex}`, `_${value}`)
    });
    // Replace item itself ID
    if (!isEmpty(this.elmt.id)) {
      this.elmt.id = this.elmt.id.replace(`_${this._itemIndex}`, `_${value}`)
    }
  }
}
