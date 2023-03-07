import {ProjectItemEvent} from "./ProjectItemEvent";
import {ProjectItemDetail} from "../types/ProjectItemsDetails";

export enum EventType {
  VALID,
  INVALID,
  INCOMPLETE
}

export interface ProjectItemUpdatedDetailsEvent extends ProjectItemEvent {
  type: EventType,
  value: ProjectItemDetail
}
