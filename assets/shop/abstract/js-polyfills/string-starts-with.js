// Source: Mozilla Developer Network
// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/startsWith

// See also:
// https://github.com/mathiasbynens/String.prototype.startsWith

// See also:
// https://github.com/uxitten/polyfill/blob/master/string.polyfill.js

if (!String.prototype.startsWith) {
  Object.defineProperty(String.prototype, 'startsWith', {
    value: function(search, pos) {
      pos = !pos || pos < 0 ? 0 : +pos;
      return this.substring(pos, pos + search.length) === search;
    }
  });
}