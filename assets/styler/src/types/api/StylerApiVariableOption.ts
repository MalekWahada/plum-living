import {Expose, Type} from "class-transformer";
import {IsArray, IsNotEmpty, IsString, ValidateNested} from "class-validator";
import syliusMapping from "../../assets/sylius_mapping.json";
import {OptionMappingError} from "../errors";
import {OptionType} from "../domain";

const NO_OPTION_CODE = 'no_option';

abstract class StylerApiVariableOption {
  abstract get optionType(): OptionType;

  @Expose()
  @IsString()
  @IsNotEmpty()
  code: string;

  get isValidOption(): boolean {
    return this.code !== NO_OPTION_CODE
  }

  get syliusCode(): string | undefined {
    if (!this.isValidOption) {
      return undefined;
    }

    if (!syliusMapping.hasOwnProperty(this.code)) {
      throw new OptionMappingError(this.code);
    }

    return syliusMapping[this.code];
  }
}

export class ShapeStylerApiVariableOption extends StylerApiVariableOption {
  @Type(() => SurfaceStylerApiVariableOption)
  @Expose({ name: 'surface_options' })
  @ValidateNested()
  @IsArray()
  surfaces: SurfaceStylerApiVariableOption[] = [];

  get optionType(): OptionType {
    return OptionType.SHAPE;
  }
}

export class SurfaceStylerApiVariableOption extends StylerApiVariableOption {
  @Type(() => ColorStylerApiVariableOption)
  @Expose({ name: 'color_options' })
  @ValidateNested()
  @IsArray()
  colors: ColorStylerApiVariableOption[] = [];

  get optionType(): OptionType {
    return OptionType.SURFACE;
  }
}

export class ColorStylerApiVariableOption extends StylerApiVariableOption {
  get optionType(): OptionType {
    return OptionType.COLOR;
  }
}


