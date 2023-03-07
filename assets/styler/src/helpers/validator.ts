import {validate, ValidationError} from "class-validator";

export const validateArray = async <T extends Object>(array: T[]): Promise<ValidationError[]> => {
  let errors: ValidationError[] = [];
  for (let item of array) {
    errors = errors.concat(await validate(item));
  }
  return errors;
}
