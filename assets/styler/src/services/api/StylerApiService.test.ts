import axiosDep from 'axios';
import {ApiRequestError, ApiValidationError, SceneNotFoundError} from "../../types/errors";
import {StylerApiService} from "./StylerApiService";
import {StylerApiVariable} from "../../types/api";

jest.mock('axios');
const axios = axiosDep as jest.Mocked<typeof axiosDep>;
const service = new StylerApiService();

const variables = [
  {
    "name": "MIT",
    "design-options": [
      {
        "code": "shape_tap_loop",
        "surface-options": [
          {
            "code": "surface_chromium",
            "color-options": []
          },
          {
            "code": "surface_brass",
            "color-options": []
          },
          {
            "code": "surface_matte_black",
            "color-options": []
          }
        ]
      },
      {
        "code": "shape_tap_regular",
        "surface-options": [
          {
            "code": "surface_brass",
            "color-options": []
          }
        ]
      },
      {
        "code": "shape_tap_line",
        "surface-options": [
          {
            "code": "surface_matte_black",
            "color-options": []
          },
          {
            "code": "surface_brass",
            "color-options": []
          },
          {
            "code": "surface_chromium",
            "color-options": []
          }
        ]
      }
    ]
  },
  {
    "name": "PLT",
    "design-options": [
      {
        "code": "no_option",
        "surface-options": [
          {
            "code": "surface_worktop_marble",
            "color-options": []
          },
          {
            "code": "surface_worktop_granite",
            "color-options": []
          },
          {
            "code": "surface_worktop_other",
            "color-options": []
          }
        ]
      }
    ]
  },
];

const invalidVariables = [
  {
    "name": "",
    "design-options": [
      {
        "code": ""
      }
    ]
  }
];

it('should return not found', async () => {
  await expect(() => service.getVariables(99)).rejects.toThrow(SceneNotFoundError);
  expect(axios.get).toBeCalledTimes(1);
});

it('should return varaibles', async () => {
  axios.get.mockImplementation((url: string) => Promise.resolve({
      data: variables
    })
  );

  let promise = service.getVariables(1);
  await expect(promise).resolves.not.toThrow(ApiRequestError);
  await expect(promise).resolves.toHaveLength(2);
  for(let item of (await promise).variables) {
    expect(item).toBeInstanceOf(StylerApiVariable);
  }

  expect(axios.get).toBeCalledTimes(1);
  axios.get.mockReset();
});

it('should return validation error', async () => {
  axios.get.mockImplementation((url: string) => Promise.resolve({
      data: invalidVariables
    })
  );

  let promise = service.getVariables(1);
  await expect(promise).rejects.toThrow(ApiValidationError);

  expect(axios.get).toBeCalledTimes(1);
  axios.get.mockReset();
});
