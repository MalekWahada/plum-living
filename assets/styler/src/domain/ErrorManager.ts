import {action, computed, makeAutoObservable, observable} from "mobx";

export class ErrorManager {
  @observable private _errors: Set<Error> = new Set();

  constructor() {
    makeAutoObservable(this);
  }

  @computed
  get hasErrors(): boolean {
    return this._errors.size > 0;
  }

  @action
  addError(error: Error): void {
    this._errors.add(error);
    console.error(`New error: ${error.message}`, error);
  }

  @action
  consumeError(): Error | undefined {
    const error = this._errors.values().next().value;
    if (error) {
      this._errors.delete(error);
    }
    return error;
  }

  @action
  clearErrors(): void {
    this._errors.clear();
  }
}
