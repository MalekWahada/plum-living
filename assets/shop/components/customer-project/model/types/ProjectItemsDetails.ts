import {ProjectItemPayload} from "./ProjectItemPayload";
import {ProjectItemOptionCode, ProjectItemOptionsAvailable} from "./ProjectItemOption";

export type ProjectItemDetailOption = {
  value: any
}

export type ProjectItemDetail = {
  combinationImagePaths: any,
  options: ProjectItemOptionCode[],
  validOptions: ProjectItemOptionsAvailable,
  designs?: ProjectItemDetailOption[],
  finishes?: ProjectItemDetailOption[],
  colors?: ProjectItemDetailOption[],
  handleFinishes?: ProjectItemDetailOption[],
  tapFinishes?: ProjectItemDetailOption[],
  variants?: ProjectItemDetailOption[],
  unitPrice: string,
  error?: string
}

export const PROJECT_ITEM_DETAIL_NOT_FOUND = 'NOT_FOUND';

export type ProjectItemsDetailsRequest = {
  channelCode: string,
  itemsData: ProjectItemPayload[]
}

export type ProjectItemsDetailsResponse = {
  [key: string]: ProjectItemDetail
}
