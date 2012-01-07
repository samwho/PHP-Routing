<?php
/**
 * @author Sam Rose <samwho@lbak.co.uk>
 * @author http://blog.sosedoff.com/2009/09/20/rails-like-php-url-router/
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011 Sam Rose
 */
class Route {

    private $is_matched = false;
    private $params;
    private $additional_params;
    private $controller;
    private $action;
    private $url;
    private $conditions;
    private $regex;
    private $request_uri;

    /**
     * Construct an instance of the Route class.
     *
     * @param $url The route string. E.g. /:controller/:action
     * @param $request_uri The request URI for the page. If you
     * are unsure, set it to $_SERVER['REQUEST_URI']
     * @param $controller The controller to associate with this route.
     * @param $action The method to call on the associated controller.
     * @param $params Any additional params to send to the controller in
     * the $_GET array.
     * @param $conditions Specify custom matching rules for the URI parameters.
     * E.g. if you have this rule: "/user/:id" you could send the conditions parameter
     * this array: "array('id' => '[0-9]+')" to tell it to only match numbers.
     */
    public function __construct($url, &$request_uri, $controller, $action, $params = array(), $conditions = array()) {
        $this->url = $url;
        $this->additional_params = $params;
        $this->conditions = $conditions;
        $this->controller = $controller;
        $this->action = $action;
        // set the request URI to a reference to when it changes in the Router, is changes here too.
        $this->request_uri = &$request_uri;
        // create one regex for this URL rule
        $url_regex = preg_replace_callback('@:[\w]+@', array($this, 'regexUrl'), $url);
        $url_regex .= '/?';
        // Store the regex used to match this pattern.
        $this->regex = '@^' . $url_regex . '$@';
    }

    /**
     * This method performs all of the matching on the request URI
     * that is required to determine whether or not it is a match.
     *
     * If the route is a valid match to the current request URI, this
     * method returns true. Otherwise, it returns false.
     *
     * This method also handles the assignment to the parameters
     * array (not the additional parameters array) for this object.
     * This means that values matched in the request URI will not
     * be available until this method has been called.
     *
     * @return bool True if the route matches the request URI, false
     * otherwise.
     */
    public function match() {
        //echo "Debug: {$this->url} - {$this->request_uri}\n";
        $this->is_matched = false;
        $p_names = array();
        $p_values = array();
        $this->params = array();

        // match all of the variables (e.g. :id) in the URL.
        preg_match_all('@:([\w]+)@', $this->url, $p_names, PREG_PATTERN_ORDER);
        $p_names = $p_names[0];

        if (preg_match($this->regex, $this->request_uri, $p_values)) {
            array_shift($p_values);

            // add the matched :variable in the URL to the params array of this object
            foreach ($p_names as $index => $value)
                $this->params[substr($value, 1)] = urldecode($p_values[$index]);
            // add the additionally specified params to the params array
            foreach ($this->additional_params as $key => $value)
                $this->params[$key] = $value;

            // set the object to matched
            $this->is_matched = true;
        }

        return $this->is_matched;
    }

    /**
     * Gets the regular expression used by this route to
     * match against the request URI and determine whether
     * or not it's a match for the current route.
     *
     * @return string A regular expression. For example, the
     * router "/user/:username" would return this regex:
     *
     * @^/user/([a-zA-Z0-9_\+\-%]+)$@
     */
    public function getRegex() {
        return $this->regex;
    }

    /**
     * Gets the action to perform on the controller for
     * this route.
     *
     * @return The action to perform on the controller of
     * this route. The action should directly match to a
     * method name in the controller.
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Gets the string that was used to match this route.
     *
     * e.g. /:controller/:action
     *
     * or
     *
     * /user/:username
     *
     * @return string
     */
    public function getRouteString() {
        return $this->url;
    }

    /**
     * Gets the parameters that are associated with this route.
     * This includes the parameters that were matched in the URI
     * and the additional parameters that may have been specified
     * in the $router->map() method call.
     *
     * This array is only available after a call to match() has
     * been made.
     *
     * @return array Parameters associated with this route.
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Gets the additional parameters that are associated with this route.
     * This is the array of parameters you passed in to the constructors
     * $params optional argument.
     *
     * @return array Additional parameters associated with this route.
     */
    public function getAdditionalParams() {
        return $this->additional_params;
    }

    /**
     * Gets the controller for this route.
     *
     * @return string The controller for this route, e.g. WelcomeController
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * Determines whether or not this route matches the current request URI.
     *
     * @return True if this route matches, false if it does not.
     */
    public function isMatched() {
        return $this->is_matched;
    }

    /**
     * Takes matches from a preg_replace_callback function call and decides what regex to use for that
     * section of the Route URL.
     *
     * @param array $matches Passed to the function from preg_replace_callback
     * @return string Regex to use in matching a route pattern.
     */
    private function regexUrl($matches) {
        // trim the colon from the start of the variable
        $key = str_replace(':', '', $matches[0]);

        // if the variable has its own regex condition specified, use that
        if (array_key_exists($key, $this->conditions)) {
            return '(' . $this->conditions[$key] . ')';
        } else {
            // else default to this regex for matching variables
            return '([a-zA-Z0-9_\+\-%]+)';
        }
    }

}

