import {OptionsApiService} from "./OptionsApiService";
import axiosDep from 'axios';
import {ApiRequestError, ApiValidationError} from "../../types/errors";
import {OptionValue} from "../../types/domain";

jest.mock('axios');
const axios = axiosDep as jest.Mocked<typeof axiosDep>;
const service = new OptionsApiService();

const optionValues = [
  {
    code: 'shape_1',
    name: 'Shape 1',
    image: 'shape_1.png',
  },
  {
    code: 'color_1',
    name: 'Color 1',
    colorHex: '#ff0000',
  }
]

const invalidOptionValues = [
  {
    code: '',
    name: '',
  },
  {
    code: 'shape_1',
    name: '',
    colorHex: 'blue',
  }
];

it('should return error', async () => {
  await expect(() => service.getOptionValues('invalid')).rejects.toThrow(ApiRequestError);
  expect(axios.get).toBeCalledTimes(1);
});

it('should return option values', async () => {
  axios.get.mockImplementation((url: string) => {
    if (url !== 'http://options_url') {
      return Promise.reject(new Error('Invalid URL'));
    }
    return Promise.resolve({
      data: optionValues,
    });
  });

  let promise = service.getOptionValues('http://options_url');
  await expect(promise).resolves.not.toThrow(ApiRequestError);
  await expect(promise).resolves.toHaveLength(2);
  for(let item of await promise) {
    expect(item).toBeInstanceOf(OptionValue);
  }

  expect(axios.get).toBeCalledTimes(1);
  axios.get.mockReset();
});

it('should return validation error', async () => {
  axios.get.mockImplementation((url: string) => Promise.resolve({
      data: invalidOptionValues
    })
  );

  let promise = service.getOptionValues('http://options_url');
  await expect(promise).rejects.toThrow(ApiValidationError);

  expect(axios.get).toBeCalledTimes(1);
  axios.get.mockReset();
});
