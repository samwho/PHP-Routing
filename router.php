<?php
// Include the Router class and a sample Controller to use.
require_once dirname(__FILE__) . '/lib/model/class.Router.php';
require_once dirname(__FILE__) . '/lib/controller/class.FooController.php';
require_once dirname(__FILE__) . '/lib/controller/class.NotFoundController.php';

// Get the singleton instance of the Router class.
$router = Router::getInstance();

/*
 * If your web site is not located on the web root, you will
 * need to make sure the router knows what directory it is in.
 *
 * If you don't, routes won't match unless you explicitly define
 * the subdirectory in the mappings.
 *
 * You will probably have to change this to match your current
 * environment. This is set up to work with my development
 * machine. If your site is located on the web root you can
 * just get rid of this line altogether.
 */
$router->setSubDir('/projects/PHPRouting/');

try {
    // Create some URL mappings.
    //
    // This will map the URL /foo to the default action of the
    // FooController. The default action is "index" but you can
    // chance this with a call to $router->setDefaultAction($action).
    $router->map('/foo', 'FooController');

    // This will map to the default controller defined in the source
    // code of class.Router.php. You can change the default controller
    // either in the source code or by a call to
    // $router->setDefaultController($controller).
    $router->setDefaultController('FooController');
    $router->map('/default');

    // This is a parametised mapping. Any part of a URL mapping that starts
    // with a colon : will be treated as a parameter and any valid URL
    // characters will be matched. The things that are matched in the URL
    // will be put into the $_GET array.
    //
    // Example: /just/testing will make the $_GET array look like this:
    //
    // array('parameter1' => 'just', 'parameter2' => 'testing');
    $router->map('/:parameter1/:parameter2', 'FooController', 'parameters');

    // This is a mapping that has an optional parameter. If you want to send
    // some extra things in the $_GET array you can pass an array as the fourth
    // argument.
    //
    // Example: /just/testing will make the $_GET array look like this:
    //
    // array('parameter1' => 'just', 'parameter2' => 'testing',
    //     'optional' => 'param');
    $router->map('/:parameter1/:parameter2/extraparam', 'FooController', 'parameters',
        array('optional' => 'param'));

    // Finally, there is a fifth parameter that you can pass that will create
    // custom, regular expression mappings for specific parameters.
    //
    // This example is telling the mapping to only match numbers for the :id
    // parameter.
    $router->map('/user/:id', 'FooController', 'parameters', array(),
        array('id'=>'[0-9]+'));






    // Attempt to find a Route match and execute the correct
    // controller and action. If no route is matched, this method will
    // execute the default action in the default 404 controller. This is,
    // by default, NotFoundController but can be changed with a call to
    // $router->set404Controller().
    $router->execute();
}
catch (Exception $e) {
    // If the controller or action can't be found or if two of your routes
    // are the same, an exception will be thrown.
    echo $e->getMessage();
}
?>

