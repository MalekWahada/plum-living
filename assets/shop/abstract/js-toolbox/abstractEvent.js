class abstractEvent extends CustomEvent {

  constructor (type, initObj) {
    super(type, initObj);

    Object.defineProperty(this, 'target', {
      value: initObj.source || this.target,
      writable: true,
      configurable: true
    });

    Object.defineProperty(this, 'currentTarget', {
      value: initObj.source || this.currentTarget,
      writable: true,
      configurable: true
    });

    Object.defineProperty(this, 'explicitOriginalTarget', {
      value: initObj.source || this.explicitOriginalTarget,
      writable: true,
      configurable: true
    });

    Object.defineProperty(this, 'originalTarget', {
      value: initObj.source || this.originalTarget,
      writable: true,
      configurable: true
    });

    Object.defineProperty(this, 'srcElement', {
      value: initObj.source || this.srcElement,
      writable: true,
      configurable: true
    });
  }

}



export default abstractEvent;