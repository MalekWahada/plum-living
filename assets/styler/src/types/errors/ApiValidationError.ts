import {ValidationError} from "class-validator";

export class ApiValidationError extends Error {
  get errors(): ValidationError[] {
    return this._errors;
  }
  private readonly _errors: ValidationError[];

  constructor(errors: ValidationError[]) {
    super(`API validation failed`);
    this.name = 'APIValidationError';
    this._errors = errors;
  }
}
