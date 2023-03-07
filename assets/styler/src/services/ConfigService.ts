import scenesConfig from '../assets/scenes_config.json';
import {SceneConfig} from "../types/domain/SceneConfig";
import {plainToInstance} from "class-transformer";
import {SceneConfigValidationError, SceneNotFoundError} from "../types/errors";
import {validate} from "class-validator";

export class ConfigService {
  private cachedScenes: SceneConfig[] = [];

  async getSceneConfig(sceneId: string | number): Promise<SceneConfig> {
    if (typeof this.cachedScenes[sceneId] !== 'undefined') {
      return this.cachedScenes[sceneId];
    }

    let scenePlain = scenesConfig.find(scene => scene.sceneId === sceneId);
    if (typeof scenePlain === 'undefined') {
      throw new SceneNotFoundError(sceneId);
    }

    let scene = plainToInstance(SceneConfig, scenePlain, { excludeExtraneousValues: true });
    let errors = await validate(scene);

    if (errors.length > 0) {
      throw new SceneConfigValidationError(sceneId, errors);
    }

    this.cachedScenes[sceneId] = scene;
    return scene;
  }
}
