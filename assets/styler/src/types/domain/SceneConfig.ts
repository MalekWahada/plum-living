import {SceneTab} from "./SceneTab";
import {IsNumber, ValidateNested} from "class-validator";
import {Expose, Type} from "class-transformer";
import {SceneConfigError} from "../errors/SceneConfigError";

export class SceneConfig {
  @Expose()
  @IsNumber()
  sceneId: number;

  @Type(() => SceneTab)
  @Expose()
  @ValidateNested()
  tabs: SceneTab[] = [];

  get firstTab(): SceneTab {
    if (typeof this.tabs[0] === "undefined") {
      throw new SceneConfigError("No tabs found in scene config");
    }
    return this.tabs[0];
  }
}


