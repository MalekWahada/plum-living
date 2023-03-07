import {Expose, Type} from "class-transformer";
import {IsArray, IsNotEmpty, IsNumber, IsOptional, ValidateNested} from "class-validator";

export class StylerApiBuildViewsPayload {
  @Type(() => StylerApiBuildViewsPayloadVariable)
  @Expose()
  @ValidateNested()
  variables: StylerApiBuildViewsPayloadVariable[] = [];

  @Expose({name: "view_codes"})
  @IsArray()
  @IsOptional()
  views?: string[] = [];

  @Expose({name: "scene_id"})
  @IsNumber()
  @IsNotEmpty()
  sceneId: number;

  constructor(sceneId: number) {
    this.sceneId = sceneId;
  }
}

export class StylerApiBuildViewsPayloadVariable {
  @Expose({name: "variable_code"})
  code: string;

  @Expose({name: "design_code"})
  shapeCode?: string;

  @Expose({name: "surface_code"})
  surfaceCode?: string;

  @Expose({name: "color_code"})
  colorCode?: string;

  constructor(code: string) {
    this.code = code;
  }
}
