import camelCase from './camelCase';

/**
 * DOM-based Routing
 *
 * Based on {@link http://goo.gl/EUTi53|Markup-based Unobtrusive Comprehensive DOM-ready Execution} by Paul Irish
 *
 * The routing fires all common scripts, followed by the page specific scripts.
 * Add additional events for more control over timing e.g. a finalize event
 */

class Router
{

  /**
   * Create a new Router
   * @param {Object} routes
   */
  constructor(routes)
  {
    this.routes = routes;

    for (const rid in this.routes) {
      this.routes[rid]._router = this;
    }

    singleton = this;
  }

  /**
   * Fire Router events
   * @param {string} route DOM-based route derived from body classes (`<body class="...">`)
   * @param {string} [event] Events on the route. By default, `init` and `finalize` events are called.
   * @param {string} [arg] Any custom argument to be passed to the event.
   */
  fire(route, event = 'init', arg)
  {
    arg = arg === undefined ? {} : arg;
    arg._rid = route;

    const fire = route !== '' && this.routes[route] && typeof this.routes[route][event] === 'function';
    if (fire) {
      document.dispatchEvent(new CustomEvent('routed', {
        bubbles: true,
        detail: {
          route,
          fn: event,
        },
      }));
      this.routes[route][event](arg);
    }
  }

  /**
   * Automatically load and fire Router events
   *
   * Events are fired in the following order:
   *  * common init
   *  * page-specific init
   *  * page-specific finalize
   *  * common finalize
   */
  loadEvents()
  {
    // Fire common init JS
    this.fire('common');

    // Fire page-specific init JS, and then finalize JS
    document.body.className
      .toLowerCase()
      .replace(/-/g, '_')
      .split(/\s+/)
      .map(camelCase)
      .forEach((className) => {
        this.fire(className);
        this.fire(className, 'finalize');
      });

    // Fire common finalize JS
    this.fire('common', 'finalize');
  }
}

let singleton;

function getRouter(routes)
{
  if (!singleton) {
    singleton = new Router(routes);
  }

  return singleton;
}

function getRoute(rid)
{
  return singleton && singleton.routes[rid]
    ? singleton.routes[rid]
    : undefined;
}

export default Router;

export {getRouter, getRoute};
