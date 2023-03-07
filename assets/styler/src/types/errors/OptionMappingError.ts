
export class OptionMappingError extends Error {
  constructor(code: string) {
    super(`Mapping not found for option code ${code}`);
    this.name = 'OptionMappingException';
  }
}
