import {ConfigService} from "./ConfigService";
import {SceneNotFoundError} from "../types/errors";

const service = new ConfigService();

test('should scene config contains tabs, categories and options', async () => {
  let config = await service.getSceneConfig(19);
  expect(config.tabs).toHaveLength(3);
  expect(config.tabs[0].categories.length).toBeGreaterThanOrEqual(1);
  expect(config.tabs[0].categories[0].options.length).toBeGreaterThanOrEqual(1);
});

it('should return scene not found', async () => {
  await expect( () => service.getSceneConfig(-1))
    .rejects
    .toThrow(SceneNotFoundError);
})
