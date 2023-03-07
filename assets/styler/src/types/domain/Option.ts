import {Expose} from "class-transformer";
import {IsEnum, IsNotEmpty, IsOptional, IsString} from "class-validator";

export enum OptionSelectorType {
  IMAGE = "image",
  COLOR = "color",
}

export enum OptionType {
  SHAPE = "shape",
  SURFACE = "surface",
  COLOR = "color",
}

export const OptionTypeHierarchy = [
  OptionType.SHAPE,
  OptionType.SURFACE,
  OptionType.COLOR,
]

export class Options extends Array<Option> {}

export class Option {
  @Expose()
  @IsString()
  @IsNotEmpty()
  name: string;

  @Expose()
  @IsEnum(OptionType)
  @IsNotEmpty()
  type: OptionType;

  @Expose()
  @IsEnum(OptionSelectorType)
  @IsOptional()
  selector?: OptionSelectorType;
}
