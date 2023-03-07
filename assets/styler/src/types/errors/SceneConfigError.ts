
export class SceneConfigError extends Error {
  constructor(message: string) {
    super(message);
    this.name = 'SceneConfigError';
  }
}
