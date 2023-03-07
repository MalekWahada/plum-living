import {AppManager} from "./AppManager";
import {instanceToPlain} from "class-transformer";

const app = new AppManager({
  optionsApiUrl: 'http://127.0.0.1:8000/fr/styler/options',
  sceneId: 19
});
let res = undefined;
it('should init', async () => {
  let promise = app.init();

  await expect(promise).resolves.not.toThrow(Error);
});

