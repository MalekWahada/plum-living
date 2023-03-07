import {ProjectItemPayload} from "./ProjectItemPayload";
import {ProjectGlobalOptionsData} from "./ProjectGlobalOptionsData";

export type ProjectSavePayload = {
  globalOptions: ProjectGlobalOptionsData
  newItems?: ProjectItemPayload[],
  updatedItems?: ProjectItemPayload[]
}

export type ProjectSaveNamePayload = {
  name: string,
  comment: string
}

export type ProjectSaveRemovePayload = {
  removedItems: string[]
}

export type ProjectSaveResponse = {
  message: string,
  newItems: {
    [key: string]: string
  },
  updatedItems: {
    [key: string]: string
  },
  removedItems: {
    [key: string]: string
  },
}
