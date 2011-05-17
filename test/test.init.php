<?php

// include the simpletest required file
require_once dirname(__FILE__) . '/../lib/ext/simpletest/autorun.php';

// include all class files in lib/model
foreach (glob(dirname(__FILE__) . '/../lib/models/class.*.php') as $filename)
{
    require_once $filename;
}

// include all class files in lib/controller
foreach (glob(dirname(__FILE__) . '/../lib/controllers/class.*.php') as $filename)
{
    require_once $filename;
}

// include all test class files
foreach (glob(dirname(__FILE__) . '/class.*.php') as $filename)
{
    require_once $filename;
}

?>
