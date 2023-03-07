import {ProjectItemEvent} from "./ProjectItemEvent";
import Field from "../fields/Field";

export enum PropertyType {
  QUANTITY,
  COMMENT,
  TYPE,
  OPTION
}

export interface ProjectItemPropertyChangeEvent extends ProjectItemEvent {
  propertyType: PropertyType,
  property: Field
  value: any
}
