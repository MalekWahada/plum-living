import viewport from './viewport.js';





//
// - Private event handlers
// ---------- ---------- ---------- ---------- ----------

function _onResize (ev) {
  this.update();
}


function _onScroll (ev) {
  this._scrollX = this._el.scrollLeft;
  this._scrollY = this._el.scrollTop;
}





//
// - Public API
// ---------- ---------- ---------- ---------- ----------

class UIMetricsObject {

  constructor (el, args) {
    if(args === undefined) { args = {}; }
    if(args.autoUpdate === undefined) { args.autoUpdate = true; }

    this._el = el;

    if(args.onBeforeUpdate) { this.onBeforeUpdate = args.onBeforeUpdate; }
    if(args.onUpdate) { this.onUpdate = args.onUpdate; }

    this.update();

    if(args.autoUpdate) {
      this.onResize = _onResize.bind(this);
      this.onScroll = _onScroll.bind(this);
      window.addEventListener('resize', this.onResize);
      this._el.addEventListener('scroll', this.onScroll);
    }
  }


  get el () { return this._el; }
  get width () { return this._width; }
  get height () { return this._height; }
  get scrollX () { return this._scrollX; }
  get scrollY () { return this._scrollY; }
  get scrollMaxX () { return this._scrollMaxX; }
  get scrollMaxY () { return this._scrollMaxY; }
  get localX () { return this._localX; }
  get localY () { return this._localY; }
  get globalX () { return this._globalX; }
  get globalY () { return this._globalY; }
  get entersAtScrollX () { return this._entersAtScrollX; }
  get leavesAtScrollX () { return this._leavesAtScrollX; }
  get entersAtScrollY () { return this._entersAtScrollY; }
  get leavesAtScrollY () { return this._leavesAtScrollY; }

  get elementIsInViewport () {
    var vx = this._globalX - viewport.scrollX,
      vy = this._globalY - viewport.scrollY;

    return vx < viewport.width
      && vx + this._width > 0
      && vy < viewport.height
      && vy + this._height > 0;
  }


  update () {
    this.onBeforeUpdate && this.onBeforeUpdate();

    this._width = this._el.offsetWidth;
    this._height = this._el.offsetHeight;
    this._scrollX = this._el.scrollLeft;
    this._scrollY = this._el.scrollTop;
    this._scrollMaxX = this._el.scrollWidth - this._width;
    this._scrollMaxY = this._el.scrollHeight - this._height;
    this._localX = this._el.offsetLeft;
    this._localY = this._el.offsetTop;
    this._globalX = getGlobalXof(this._el);
    this._globalY = getGlobalYof(this._el);
    this._entersAtScrollX = this._globalX - Math.min(this._globalX, viewport.width);
    this._leavesAtScrollX = this._globalX + this._width;
    this._entersAtScrollY = this._globalY - Math.min(this._globalY, viewport.height);
    this._leavesAtScrollY = this._globalY + this._height;

    this.onUpdate && this.onUpdate();

    return this;
  }


  tearDown () {
    if(this.onResize) {
      window.removeEventListener('resize', this.onResize);
    }

    if(this.onScroll) {
      this._el.removeEventListener('scroll', this.onScroll);
    }

    this._el = this._width = this._height = this._scrollX = this._scrollY = this._scrollMaxX = this._scrollMaxY = this._localX = this._localY = this._globalX = this._globalY = null;
    this._entersAtScrollX = this._leavesAtScrollX = this._entersAtScrollY = this._leavesAtScrollY = null;
    this.onBeforeUpdate = this.onUpdate = null;
  }

}



function getGlobalXof (el) {
  return el
    ? el.getBoundingClientRect().left + viewport.scrollX
    : 0;
}

function getGlobalYof (el) {
  return el
    ? el.getBoundingClientRect().top + viewport.scrollY
    : 0;
}


function getObject (el, args) {
  if(el == window) {
    return viewport;
  }

  return new UIMetricsObject(el, args);
}





//
// - Exports
// ---------- ---------- ---------- ---------- ----------

export default getObject;

export {
  getObject,
  getGlobalXof,
  getGlobalYof
};
