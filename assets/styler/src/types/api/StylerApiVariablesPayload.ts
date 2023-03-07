import {StylerApiVariable} from "./StylerApiVariable";
import {IsArray, ValidateNested} from "class-validator";
import {Expose, Type} from "class-transformer";

export class StylerApiVariablesPayload {
  @Expose()
  @Type(() => StylerApiVariable)
  @IsArray()
  @ValidateNested()
  variables: StylerApiVariable[] = [];

  @Expose({name: "view_codes"})
  @IsArray()
  views: string[];
}
