import axios, {AxiosError} from "axios";
import {instanceToPlain, plainToInstance} from "class-transformer";
import ApiService from "./ApiService";
import {ApiRequestError, ApiValidationError, SceneNotFoundError} from "../../types/errors";
import {OptionType, UserConfig} from "../../types/domain";
import {
  StylerApiBuildViewsPayload,
  StylerApiBuildViewsPayloadVariable
} from "../../types/api/StylerApiBuildViewsPayload";
import {StylerApiVariablesPayload} from "../../types/api/StylerApiVariablesPayload";
import {validate} from "class-validator";
import {VariablesSelectionsValue} from "../../types/domain/VariablesSelections";
import {StylerApiBuildViewsResult} from "../../types/api/StylerApiBuildViewsResult";
import {SceneViews} from "../../types/domain/SceneView";

const STYLER_API_BASE_URL = "https://styler-api.plum-living.com/api/v2/Styler/";
const STYLER_API_GET_VARIABLES_PATH = "Variables?sceneName=";
const STYLER_API_BUILD_VARIABLES_PATH = "BuildImage";

export class StylerApiService extends ApiService {
  public async getVariables(sceneId: string | number): Promise<StylerApiVariablesPayload> {
    let payload: StylerApiVariablesPayload | undefined;

    try {
      const response = await axios.get(STYLER_API_BASE_URL + STYLER_API_GET_VARIABLES_PATH + sceneId);
      payload = plainToInstance(StylerApiVariablesPayload, response.data, { excludeExtraneousValues: true }); // Instantiate all blocks
    }
    catch (e: any) {
      if (e instanceof AxiosError && e.code === AxiosError.ERR_BAD_REQUEST) {
        throw new SceneNotFoundError(sceneId, e);
      }

      throw new ApiRequestError(e);
    }

    let errors = await validate(payload);
    if (errors.length > 0) {
      throw new ApiValidationError(errors);
    }
    return payload;
  }

  public async buildViews(sceneId: number, userConfig: UserConfig, viewVariables: VariablesSelectionsValue, views: SceneViews): Promise<StylerApiBuildViewsResult> {
    let payload = StylerApiService.generatePayloadObject(sceneId, viewVariables, views);
    let result: StylerApiBuildViewsResult | undefined;

    try {
      const response = await axios.post(STYLER_API_BASE_URL + STYLER_API_BUILD_VARIABLES_PATH, instanceToPlain(payload), {
        params: userConfig,
        timeout: 5000
      });
      result = plainToInstance(StylerApiBuildViewsResult, response.data, { excludeExtraneousValues: true });
    }
    catch (e: any) {
      throw new ApiRequestError(e);
    }

    let errors = await validate(result);
    if (errors.length > 0) {
      throw new ApiValidationError(errors);
    }
    return result;
  }

  private static generatePayloadObject(sceneId: number, viewVariables: VariablesSelectionsValue, views: SceneViews): StylerApiBuildViewsPayload {
    let payload = new StylerApiBuildViewsPayload(sceneId);
    payload.views = views.map(view => view.code);

    for (let [key, value] of Object.entries(viewVariables)) {
      let variablePayload = new StylerApiBuildViewsPayloadVariable(key);
      variablePayload.shapeCode = value.get(OptionType.SHAPE)?.apiCode;
      variablePayload.surfaceCode = value.get(OptionType.SURFACE)?.apiCode;
      variablePayload.colorCode = value.get(OptionType.COLOR)?.apiCode;

      payload.variables.push(variablePayload);
    }
    return payload;
  }
}
