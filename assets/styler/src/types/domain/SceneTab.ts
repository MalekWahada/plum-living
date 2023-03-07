import {Category} from "./Category";
import {Expose, Type} from "class-transformer";
import {IsNotEmpty, IsOptional, IsString, ValidateNested} from "class-validator";
import {SceneConfigError} from "../errors/SceneConfigError";

export class SceneTab {
  @Expose()
  @IsString()
  name: string;

  @Expose()
  @IsString()
  @IsNotEmpty()
  code: string;

  @Expose()
  @IsString()
  @IsOptional()
  icon?: string;

  @Type(() => Category)
  @Expose()
  @ValidateNested()
  categories: Category[];

  get firstCategory(): Category {
    if (typeof this.categories[0] === "undefined") {
      throw new SceneConfigError(`No categories found in scene tab ${this.name}`);
    }
    return this.categories[0] ?? undefined;
  }
}
