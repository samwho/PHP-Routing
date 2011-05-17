<?php

require_once dirname(__FILE__) . '/test.init.php';

class TestOfRouter extends UnitTestCase {
    /**
     * This is the Router used for testing. It is destroyed and
     * recreated for every test function.
     *
     * @var Router
     */
    private $router;

    /**
     * Get the Router instance.
     */
    function setUp() {
        // the $_SERVER['REQUEST_URI'] needs to be set or you get a PHP warning
        $_SERVER['REQUEST_URI'] = 'unset';
        $this->router = Router::getInstance();
    }

    /**
     * Reset the Router ready for the next test.
     */
    function tearDown() {
        Router::reset();
    }

    function testSimpleRouting() {
        $this->router->setRequestUri('/foo');

        $this->router->map('/foo', 'FooController', 'index');

        $this->assertEqual($this->router->execute(true), 'Hello from foo!');
    }

    function testParametisedRouting() {
        $this->router->setRequestUri('/foo/bar');

        $this->router->map('/:controller/:action', 'FooController', 'parameters');

        $output = $this->router->execute(true);

        $this->assertPattern('/Hello from foo with parameters!/', $output);
        $this->assertPattern('/controller: foo/', $output);
        $this->assertPattern('/action: bar/', $output);

    }

    function testCustomParameterRules() {
        $this->router->setRequestUri('/user/1');

        $this->router->map('/user/:id', 'FooController', 'parameters', array(),
            array('id' => '[0-9]+'));

        $output = $this->router->execute(true);
        $this->assertPattern('/id: 1/', $output);

        $this->router->setRequestUri('/user/hello');
        $this->router->map('/user/:sometext', 'FooController', 'parameters');

        $output = $this->router->execute(true);
        $this->assertPattern('/sometext: hello/', $output);
    }

    function testSendingCustomParameters() {
        $this->router->setRequestUri('/foo/bar');

        $this->router->map('/:controller/:action', 'FooController', 'parameters', array('An extra parameter, you say?' => true));

        $output = $this->router->execute(true);

        $this->assertPattern('/An extra parameter, you say\?: 1/', $output);
        $this->assertPattern('/controller: foo/', $output);
        $this->assertPattern('/action: bar/', $output);
    }

    function testComplexRouting() {
        $this->router->map('/user/:username/new/post', 'FooController', 'parameters');
        $this->router->map('/user/:username/add/friend', 'FooController', 'parameters');
        $this->router->map('/user/:username/:action', 'FooController', 'parameters', array('extra param' => 'just for fun'));
        $this->router->map('/user/:username/', 'FooController', 'parameters');

        $this->router->setRequestUri('/user/samwhoo/new/post');
        $this->assertPattern('/username: samwhoo/', $this->router->execute(true));

        $this->router->setRequestUri('/user/samwhoo/new/unrecognised');
        $this->assertPattern('/This is not the page you are looking for./', $this->router->execute(true));

        $this->router->setRequestUri('/user/samwhoo/new');
        $this->assertPattern('/username: samwhoo/', $this->router->execute(true));
        $this->assertPattern('/action: new/', $this->router->execute(true));
        $this->assertPattern('/extra param: just for fun/', $this->router->execute(true));
    }
}

?>
