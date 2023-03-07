function isObject (obj) {
  return !!obj && typeof obj === 'object';
}

function deepMerge(target, source, tryToFuseArrays = false) {
  if(!isObject(target) || !isObject(source)) {
    return source;
  }

  Object.keys(source).forEach(key => {
    const targetValue = target[key];
    const sourceValue = source[key];

    if(Array.isArray(targetValue) && Array.isArray(sourceValue)) {
      if(tryToFuseArrays) {
        sourceValue.forEach((item, i) => {
          if(isObject(targetValue[i]) && isObject(sourceValue[i])) {
            target[key][i] = deepMerge(Object.assign({}, targetValue[i]), sourceValue[i], tryToFuseArrays);
          }
          else {
            target[key].push(sourceValue[i]);
          }
        });
      }
      else {
        target[key] = targetValue.concat(sourceValue);
      }
    }
    else if(isObject(targetValue) && isObject(sourceValue)) {
      target[key] = deepMerge(Object.assign({}, targetValue), sourceValue, tryToFuseArrays);
    }
    else {
      target[key] = sourceValue;
    }
  });

  return target;
}

export default deepMerge;
