export function isEmpty(value: any) {
  return !!(value === undefined
    || value === null
    || value === false
    || value === ''
    || value.length == 0);
}

export function isNothing(value: any) {
  return isEmpty(value) || value === '0';
}

export function stringBool(value: string) {
  return value.toLowerCase() === 'true';
}
