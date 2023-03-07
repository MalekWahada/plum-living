import {IsNotEmpty, IsOptional, IsString} from "class-validator";
import {Expose} from "class-transformer";
import {action, computed, makeAutoObservable, observable} from "mobx";

export class SceneViews extends Array<SceneView> {}

export class SceneView {
  @Expose({name: "view_code"})
  @IsString()
  @IsNotEmpty()
  code: string;

  @Expose()
  @IsString()
  @IsOptional()
  @observable
  uri?: string;

  @observable
  isOutdated: boolean = false;

  constructor(code: string) {
    this.code = code;
    makeAutoObservable(this);
  }

  @computed
  get isLoaded(): boolean {
    return typeof this.uri !== 'undefined';
  }

  @action
  updateUri(uri: string): void {
    this.uri = uri;
  }

  @action
  setOutdated(): void {
    this.isOutdated = true;
  }

  @action
  resetOutdated(): void {
    this.isOutdated = false;
  }
}
