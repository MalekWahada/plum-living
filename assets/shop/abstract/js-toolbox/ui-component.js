const instances = new WeakMap();



class UIComponent {
  constructor (elmt) {
    this.elmt = elmt;
    this._bindedEventHandlers = {};
    instances.set(this.elmt, this);
  }

  remove () {
    instances.delete(this.elmt);
    this.elmt.remove();
  }

  on (type, handler) {
    const bindedHandler = handler.bind(this);
    
    if(!(type in this._bindedEventHandlers)) { this._bindedEventHandlers[type] = new WeakMap(); }
    this._bindedEventHandlers[type].set(handler, bindedHandler);

    this.elmt.addEventListener(type, bindedHandler);
  }

  off (type, handler) {
    if((type in this._bindedEventHandlers) && this._bindedEventHandlers[type].has(handler)) {
      const bindedHandler = this._bindedEventHandlers[type].get(handler);
      this._bindedEventHandlers[type].delete(handler);
      this.elmt.removeEventListener(type, bindedHandler);
    }
  }

  emit (type, data) {
    this.elmt.dispatchEvent(new CustomEvent(type, { detail: data }));
  }
}



UIComponent.get = function (elmt) {
  return instances.get(elmt);
}



export default UIComponent;
