<?php

require_once dirname(__FILE__) . '/test.init.php';

class TestOfRoute extends UnitTestCase {
    function testConstructor() {
        $rule = '/:controller/:action/';
        $request_uri = '/foo/bar/';
        $controller = 'FooController';
        $action = 'index';
        $params = array('won the game?' => false);
        $conditions = array();

        $route = new Route($rule, $request_uri, $controller, $action, $params, $conditions);

        $this->assertEqual($route->getController(), 'FooController');
        $this->assertEqual($route->getAction(), 'index');
        $this->assertEqual($route->getRouteString(), '/:controller/:action/');
        $this->assertEqual($route->getAdditionalParams(), array('won the game?' => false));
    }
}

?>
