import {plainToInstance} from "class-transformer";
import ApiService from "./ApiService";
import axios from "axios";
import {ApiRequestError, ApiValidationError} from "../../types/errors";
import {validateArray} from "../../helpers/validator";
import {OptionValue, OptionValues} from "../../types/domain";

export class OptionsApiService extends ApiService {
  public async getOptionValues(url: string): Promise<OptionValues> {
    let options: OptionValues = [];

    try {
      const response = await axios.get(url);
      options = plainToInstance(OptionValue, response.data as [], { excludeExtraneousValues: true }); // Instantiate all blocks
    }
    catch (e: any) {
      throw new ApiRequestError(e);
    }

    let errors = await validateArray<OptionValue>(options);
    if (errors.length > 0) {
      throw new ApiValidationError(errors);
    }
    return options;
  }
}
