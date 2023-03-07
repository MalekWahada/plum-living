import {Expose, Type} from "class-transformer";
import {IsArray, IsNotEmpty, IsString, ValidateNested} from "class-validator";
import 'reflect-metadata';
import {ShapeStylerApiVariableOption} from "./StylerApiVariableOption";

export class StylerApiVariables extends Array<StylerApiVariable> {}

export class StylerApiVariable {
  @Expose({ name: 'variable_code'})
  @IsString()
  @IsNotEmpty()
  code: string;

  @Type(() => ShapeStylerApiVariableOption)
  @Expose({ name: 'design_options' })
  @ValidateNested()
  @IsArray()
  shapes: ShapeStylerApiVariableOption[] = [];
}


