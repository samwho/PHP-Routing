<?php
require_once dirname(__FILE__) . '/class.Route.php';

/**
 * The Router Class
 *
 * This class is intended to mimick the various styles of routing that
 * are present in Ruby / Rails. If you are familiar with Ruby and Rails
 * then hopefully some of the syntax will be familiar to you.
 *
 * This code was originally adapted from the code found at
 * http://blog.sosedoff.com/2009/09/20/rails-like-php-url-router/ but I've
 * changed it so much that there isn't a lot of resemblance left. Many thanks
 * to the original author and I sincerely hope he does not mind that I have
 * taken inspiration from his code.
 *
 * @author Sam Rose <samwho@lbak.co.uk>
 * @author http://blog.sosedoff.com/2009/09/20/rails-like-php-url-router/
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Sam Rose
 */
class Router {
    /**
     * The default controller to use for page requests.
     * @var string
     */
    private $default_controller = 'WelcomeController';
    /**
     * The controller to fall back to if no routes match
     * the current request URI.
     *
     * @var string
     */
    private $_404_controller = 'NotFoundController';

    /**
     * The default action to perform on the controller.
     *
     * @var string
     */
    private $default_action = 'index';

    /**
     * When a route has been matched to the current request
     * URI, it is stored in here.
     *
     * @var Route
     */
    private $matched_route;

    /**
     * If the location of this site is not the web root, specify
     * the sub directory it is in in this variable.
     *
     * e.g. if the site is at www.example.com/blog/
     *
     * You would put $sub_dir = '/blog/' in here.
     */
    private $sub_dir = '';

    /**
     * The singleton instance of this class.
     * @var Router
     */
    private static $instance = null;

    /**
     * Gets the singleton instance of the Router object. This object handles
     * the routing of URLs to their appropriate pages. If you want to specify a new URL
     * page rule, you will do it through this object.
     *
     * @return Router The Router object.
     */
    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new Router();
        }

        return self::$instance;
    }

    /**
     * The request URI of the page request as found in $_SERVER['REQUEST_URI']
     * @var string
     */
    private $request_uri;

    /**
     * The array of Route objects that will be used to match a URL to a controller.
     * @var array
     */
    private $routes = array();

    /**
     * A string containing the class name of the controller to use for this page load.
     * This variable will be null if none of the Routes matches the request URL.
     * @var string
     */
    private $controller;

    /**
     * An array of parameters for the matched route. This array will be null if a Route
     * does not match the current URL.
     * @var array
     */
    private $params;

    private function __construct() {
        $this->setRequestUri();
    }

    /**
     * Unsets the currently available version of the Router so that a call
     * to Router::getInstance() wil create a fresh Router.
     *
     * This function also resets the $_GET array back to an empty array
     * unless an optional parameter is set.
     *
     * This is for testing purposes only.
     *
     * @param bool $reset_get If set to false, this leaves the $_GET array
     * in tact. Defaults to true.
     */
    public static function reset($reset_get = true) {
        self::$instance = null;
        if ($reset_get) {
            $_GET = array();
        }
    }

    /**
     * Looks at the current request URI and subdirectory settings and pulls
     * the usable request URI based on them.
     *
     * If you pass it the optional parameter, it will use that as the request
     * URI instead of $_SERVER['REQUEST_URI'].
     *
     * This is meant for internal and testing use only, though if you find a
     * good use for it then by all means knock yourself out.
     *
     * @param string $request_uri An optional request URI parameter. If this is
     * set, the method will use its contents instead of $_SERVER['REQUEST_URI']
     */
    public function setRequestUri($request_uri = null) {
        $request = is_null($request_uri) ? $_SERVER['REQUEST_URI'] : $request_uri;
        $pos = strpos($request, '?');
        if ($pos)
            $request = substr($request, 0, $pos);

        // If the current site is located in a subdirectory, this bit of
        // code will remove the subdirectory from the request URI.
        $this->request_uri = str_replace($this->sub_dir, '', $request);

        // ensure that the request uri has a leading forward slash
        if (strpos($this->request_uri, '/') !== 0) {
            $this->request_uri = '/' . $this->request_uri;
        }
    }

    /**
     * This function lets you set a new rule for mapping URLs. Note that URL mapping
     * happens very early on in the life cycle of a page request. After a URL has been
     * mapped to a controller, this function does nothing.
     *
     * Some example uses:
     *
     * $router = Router::getInstance();
     * $router->map('/:user');
     *
     * Any section of a mapping rule that starts with a colon is a variabe. The default matching pattern
     * for variables is "([a-zA-Z0-9_\+\-%]+)". So if a URL matches that regex and nothing else, e.g.
     *
     * http://example.com/samwhoo
     *
     * That URL will map to the default controller for page requests with $_GET['user']
     * set to the string "samwhoo".
     *
     * If you want to specify your own mapping rules or controller you can do so by specifying extra parameters of the
     * function:
     *
     * $router->map('/post/:id', 'PostController', 'index', array(), array('id' => '[0-9]+'));
     *
     * This will match the /post part literally and the /:id part with the regex "[0-9]+". The following URL
     * would match:
     *
     * http://example.com/post/123456789
     *
     * That will send a request to the PostController class's "index" method with $_GET['id'] set to the string "123456789".
     *
     * If you try and overwrite an already mapped rule, an exception will be thrown. The way this works is that Route objects
     * generate a regular expression based on their rule strings. This regex is checked when you try and add a new rule and if
     * it turns out to be the same as any other currently existing Routes, an exception is thrown.
     *
     * @param string $rule The rule for this new URL mapping.
     * @param array $params The parameters to send this mapping. Optional.
     * @param array $conditions The regex conditions for sections of the URL. Optional.
     */
    public function map($rule, $controller = null, $action = null, $params = array(), $conditions = array()) {
        if ($controller == null) $controller = $this->default_controller;
        if ($action == null) $action = $this->default_action;

        $new_route = new Route($rule, $this->request_uri, $controller, $action, $params, $conditions);

        // Make sure that the new route does not match any of the current matching rules.
        foreach ($this->routes as $route) {
            if ($new_route->getRegex() == $route->getRegex()) {
                throw new Exception(
                    "Tried to overwrite an existing URL mapping rule: '" . $new_route->getRouteString() .
                    "' has the same matching regex as '" . $route->getRouteString() . "'."
                );
            }
        }

        $this->routes[$rule] = $new_route;
    }

    /**
     * Searches all of the routes in the Router and returns the one
     * that matches the current request URI.
     *
     * If no routes match, this method will return null.
     *
     * @return Route The matched route.
     */
    public function matchRoute() {
        foreach ($this->routes as $route) {
            if ($route->match()) {
                $this->matched_route = $route;
                return $route;
                break;
            }
        }

        // if the method gets to this point, no route was found
        return null;
    }

    /**
     * If your web site is not on the web root but instead in a
     * sub directory, you will need to tell the Router this. The
     * $_SERVER['REQUEST_URI'] will include the full root after the
     * domain to the current page. By setting a sub-directory, the
     * Router class will trim it from the request URI.
     *
     * If your website is not located at a subdirectory, you do not
     * need to bother with this method.
     *
     * @param $new_sub_dir The sub-directory to your site.
     */
    public function setSubDir($new_sub_dir) {
        $this->sub_dir = $new_sub_dir;
        $this->setRequestUri();
    }

    /**
     * If a route is unmatched, the fallback controller is the 404Controller.
     *
     * You can use this controller to display a nice looking 404 page.
     * The default for this if you haven't modified the source code is
     * "NotFoundController".
     *
     * @param string $new_404_controller The new 404 controller.
     */
    public function set404Controller($new_404_controller) {
        $this->_404_controller = $new_404_controller;
    }

    /**
     * If no controller is specified in the map() method, the default
     * controller is what's used. Use this method to set what the default
     * controller should be.
     *
     * If you have not modified the source code, this is set to "WelcomeController"
     * by default.
     *
     * @param string $new_default_controller The new defalt controller.
     */
    public function setDefaultController($new_default_controller) {
        $this->default_controller = $new_default_controller;
    }

    /**
     * If no action is specified in the map() method, the default action
     * is what's used. Use this method to set what the default action should
     * be.
     *
     * If you have no modified the source code, this is set to "index" by
     * default.
     *
     * @param string $new_default_action The new default action.
     */
    public function setDefaultAction($new_default_action) {
        $this->default_action = $new_default_action;
    }

    /**
     * Search through all the currently mapped rules until a match on the current
     * request URI is found.
     *
     * When a match is found, the controller associated with that route is
     * instantiated and the associated action is called on it. For example,
     * if you set the controller to WelcomeController and the action to "main",
     * the WelcomeController is instantiated and then the "main" method in
     * that controller is called and the return value printed to the screen.
     *
     * You can force this method to return whatever controller action you
     * told it to execute instead of printing it out to the screen by
     * calling it like so:
     *
     * $router->execute(true);
     *
     * If the controller class that you specified in the matched route does
     * not exist, this method will throw an exception. This also goes for if
     * the action is not found inside the controller.
     *
     * @param $return Whether or not to return the value retrieve from the
     * controller action. Defaults to false, which means a call to $router->execute()
     * will, by default, print the results to the screen.
     * @return Route The first matched Route object.
     */
    public function execute($return = false) {
        $route = $this->matchRoute();

        if ($route == null) {
            $controller = $this->_404_controller;
            $action = $this->default_action;
        } else {
            $controller = $route->getController();
            $action = $route->getAction();
        }

        if (!class_exists($controller)) {
            throw new Exception("Class '$controller' does not exist.");
        }

        $controller_instance = new $controller();
        if (!method_exists($controller_instance, $action)) {
            throw new Exception("Method '$action' not found in class '$controller'.");
        }

        if ($route != null) {
            // merge the Route parameters with the $_GET superglobal.
            $_GET = array_merge($_GET, $route->getParams());
        }

        if ($return) {
            return $controller_instance->$action();
        } else {
            echo $controller_instance->$action();
        }
    }
}
