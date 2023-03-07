
export class ApiRequestError extends Error {
  get inner(): Error {
    return this._inner;
  }
  private readonly _inner: Error;

  constructor(error: Error) {
    super(`API request failed`);
    this.name = 'APIRequestError';
    this._inner = error;
  }
}
