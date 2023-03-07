import abstractEvent from './abstractEvent.js';



class abstractEventEmitter {

  constructor (properties) {
    this._eeRadio = document.createElement('div');
    this._eeRegistered = {};
    properties && Object.assign(this, properties);
  }


  on (type, callback) {
    const caller = ev => {
      return callback.call(this, ev);
    }

    if(!(type in this._eeRegistered)) {
      this._eeRegistered[type] = new WeakMap();
    }

    this._eeRegistered[type].set(callback, caller);
    this._eeRadio.addEventListener(type, caller);

    return this;
  }


  off (type, callback) {
    if(type in this._eeRegistered) {
      const caller = this._eeRegistered[type].get(callback);

      if(caller) {
        this._eeRadio.removeEventListener(type, caller);
      }
    }

    return this;
  }


  emit (type, payload) {
    const event = new abstractEvent(type, { source: this, detail: payload });
    this._eeRadio.dispatchEvent(event);

    return this;
  }

}



export default abstractEventEmitter;