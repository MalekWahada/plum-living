
export class SceneNotFoundError extends Error {
  get error(): Error | undefined {
    return this._error;
  }

  private readonly _error: Error | undefined;

  constructor(sceneId: string | number, error?: Error) {
    super(`Scene ${sceneId} not found`);
    this._error = error;
    this.name = 'SceneNotFoundError';
  }
}
