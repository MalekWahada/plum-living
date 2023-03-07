import AbstractEventEmitter from '../js-toolbox/abstractEventEmitter.js';



/*
  Do edit the breakpoints
  ---------- ---------- ---------- ---------- ----------
  They should match the breakpoints defined in the
  css-grid-config file, otherwise it's the bordel...
*/

const breakpoints = {
  small: rem(560),
  medium: rem(720),
  large: rem(1000),
  wide: rem(1280)
};

/*
  ---------- ---------- ---------- ---------- ----------
*/



for(const bid in breakpoints) {
  breakpoints[bid] = window.matchMedia(`(min-width: ${breakpoints[bid]})`);
  breakpoints[bid].id = bid;
  breakpoints[bid].onchange = onBreakpointChange;
}



function onBreakpointChange(ev) {
  api.emit('change', ev.target.id);

  if(ev.matches) {
    api.emit('enter', ev.target.id);
    api.emit(`enter${ev.target.id}`);
  }
  else {
    api.emit('leave', ev.target.id);
    api.emit(`leave${ev.target.id}`);
  }
}

function rem (value, base) {
  base = base === undefined ? 16 : base;
  return value / base + 'rem';
}


function breakpointMatches (bid) {
  return breakpoints[bid] && breakpoints[bid].matches;
}


const api = new AbstractEventEmitter({
  breakpointMatches,
  getBreakpoint (bid) {
    return breakpoints[bid];
  }
});



export default api;

export { breakpointMatches };