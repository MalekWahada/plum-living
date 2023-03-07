import AbstractEventEmitter from './abstractEventEmitter.js';

//
// - Private variables
// ---------- ---------- ---------- ---------- ----------

var _hasTouchScreen = 'ontouchstart' in window || navigator.maxTouchPoints;

var _width;
var _height;
var _scrollX;
var _scrollY;
var _scrollMaxX;
var _scrollMaxY;
var _lastScrollY;
var _scrollingDown;
// localX is always 0 on the viewport
// localY is always 0 on the viewport
// globalX is always 0 on the viewport
// globalY is always 0 on the viewport
// entersAtScrollX is always 0 on the viewport
var _leavesAtScrollX = 0;
// entersAtScrollY is always 0 on the viewport
var _leavesAtScrollY = 0;
var _scrollXWhenBlocked = 0;
var _scrollYWhenBlocked = 0;





//
// - Public API
// ---------- ---------- ---------- ---------- ----------

class UIViewport extends AbstractEventEmitter {

  get hasTouchScreen () { return _hasTouchScreen }
  get width () { return _width }
  get height () { return _height }
  get scrollX () { return _scrollX; }
  get scrollY () { return _scrollY; }
  get scrollMaxX () { return _scrollMaxX; }
  get scrollMaxY () { return _scrollMaxY; }
  get scrollingDown () { return _scrollingDown; }
  get localX () { return 0; }
  get localY () { return 0; }
  get globalX () { return 0; }
  get globalY () { return 0; }
  get entersAtScrollX () { return 0; }
  get leavesAtScrollX () { return _leavesAtScrollX; }
  get entersAtScrollY () { return 0; }
  get leavesAtScrollY () { return _leavesAtScrollY; }


  update () {
    _width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
    _height = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
    _scrollX = window.scrollX || window.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft;
    _scrollY = window.scrollY || window.pageYoffset || document.documentElement.scrollTop || document.body.scrollTop;
    _scrollMaxX = 'documentElement' in document ? document.documentElement.scrollWidth - _width : document.body.scrollWidth - _width;
    _scrollMaxY = 'documentElement' in document ? document.documentElement.scrollHeight - _height : document.body.scrollHeight - _height;
    _leavesAtScrollX = _scrollMaxX;
    _leavesAtScrollY = _scrollMaxY;
    return this;
  }


  setScrollX (value) {
    _scrollX = (typeof value == 'number') ? value : _scrollX;
    window.scrollTo(_scrollX, _scrollY);
  }


  setScrollY (value) {
    _scrollY = (typeof value == 'number') ? value : _scrollY;
    window.scrollTo(_scrollX, _scrollY);
  }


  blockScrolling () {
    this.emit('beforeBlockScrolling');

    _scrollXWhenBlocked = _scrollX;
    _scrollYWhenBlocked = _scrollY;

    document.documentElement.style.overflow = 'hidden';
    document.body.style.overflow = 'hidden';

    if(_hasTouchScreen) {
      document.body.style.width = '100%';
      document.body.style.position = 'fixed';
      document.body.style.left = -_scrollXWhenBlocked + 'px';
      document.body.style.top = -_scrollYWhenBlocked + 'px';
    }

    _scrollX = _scrollXWhenBlocked;
    _scrollY = _scrollYWhenBlocked;

    this.emit('blockScrolling');
  }


  releaseScrolling () {
    this.emit('beforeReleaseScrolling');

    document.documentElement.style.overflow = '';
    document.body.style.overflow = '';

    if(_hasTouchScreen) {
      document.body.style.width = '';
      document.body.style.position = '';
      document.body.style.left = '';
      document.body.style.top = '';

      setScrollX(_scrollXWhenBlocked);
      setScrollY(_scrollYWhenBlocked);
    }

    this.emit('releaseScrolling');
  }


  setScrollXWhenBlocked (value) {
    _scrollXWhenBlocked = value;
  }


  setScrollYWhenBlocked (value) {
    _scrollYWhenBlocked = value;
  }


  elementIsInViewport (el) {
    var rect = el.getBoundingClientRect();

    return rect
      && rect.left < _width
      && rect.left + rect.width > 0
      && rect.top < _height
      && rect.top + rect.height > 0;
  }

}





//
// - Maintenance stuff
// ---------- ---------- ---------- ---------- ----------

const _singleton = new UIViewport();

window.addEventListener('resize', () => _singleton.update());

window.addEventListener('scroll', function () {
  _scrollX = window.scrollX || window.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft;
  _scrollY = window.scrollY || window.pageYoffset || document.documentElement.scrollTop || document.body.scrollTop;

  if(!_scrollingDown && _scrollY > _lastScrollY) {
    _scrollingDown = true;
    _singleton.emit('scrolldirectionchange', { down: true, up: false });
  }
  else if(_scrollingDown && _scrollY < _lastScrollY) {
    _scrollingDown = false;
    _singleton.emit('scrolldirectionchange', { down: false, up: true });
  }

  _lastScrollY = _scrollY;
});

_singleton.update();





//
// - Exports
// ---------- ---------- ---------- ---------- ----------

export default _singleton;
