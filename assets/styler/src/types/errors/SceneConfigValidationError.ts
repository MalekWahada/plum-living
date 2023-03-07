import {ValidationError} from "class-validator";

export class SceneConfigValidationError extends Error {
  get sceneId(): string | number {
    return this._sceneId;
  }
  get errors(): ValidationError[] {
    return this._errors;
  }
  private readonly _errors: ValidationError[];
  private readonly _sceneId: string | number;

  constructor(sceneId: string | number, errors: ValidationError[]) {
    super(`Scene config validation failed for scene ${sceneId}`);
    this._sceneId = sceneId;
    this._errors = errors;
    this.name = 'SceneConfigValidationError';
  }
}
