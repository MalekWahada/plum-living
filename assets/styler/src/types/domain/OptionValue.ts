import {Expose} from "class-transformer";
import {IsHexColor, IsOptional, IsString} from "class-validator";
import syliusMapping from "../../assets/sylius_mapping.json";

export class OptionValues extends Array<OptionValue> {}

export class OptionValue {
  @Expose()
  @IsString()
  code: string;

  get apiCode(): string {
    const code = Object.keys(syliusMapping).find(key => syliusMapping[key] === this.code);
    if (typeof code === 'undefined') {
      throw new Error(`No API code found for OptionValue ${this.code}`);
    }
    return code;
  }

  @Expose()
  @IsString()
  name: string;

  @Expose()
  @IsHexColor()
  @IsOptional()
  colorHex?: string;

  @Expose()
  @IsString()
  @IsOptional()
  image?: string;

  appliesToVariables: Set<string> = new Set();
}
