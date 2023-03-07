import {Expose, Type} from "class-transformer";
import {IsArray, IsNotEmpty, IsOptional, IsString, ValidateNested} from "class-validator";
import {Option, OptionTypeHierarchy} from "./Option";
import {VariablesOptionValuesTree} from "./VariablesOptionValuesTree";
import {OptionValue} from "./OptionValue";

export class Category {
  @Expose()
  @IsString()
  @IsNotEmpty()
  name: string;

  @Expose()
  @IsString()
  @IsOptional()
  icon?: string;

  @Type(() => Option)
  @Expose()
  @ValidateNested()
  options: Option[] = [];

  @Expose()
  @IsArray()
  variables: string[] = [];

  variablesTree: VariablesOptionValuesTree = new VariablesOptionValuesTree();

  get code(): string {
    return this.name.toLowerCase().replace(/\s/g, '_');
  }

  get optionsSorted(): Option[] {
    return this.options.sort((a, b) => OptionTypeHierarchy.indexOf(a.type) - OptionTypeHierarchy.indexOf(b.type));
  }

  getVariablesToUpdate(optionValue: OptionValue): string[] {
    let variables: string[] = [];
    for (const variable of this.variables) {
      if (optionValue.appliesToVariables.has(variable)) {
        variables.push(variable);
      }
    }
    return variables;
  }
}
