<?php

require_once dirname(__FILE__) . '/test.init.php';

$all_tests = & new TestSuite('All tests');
$all_tests->add(new TestOfRoute());
$all_tests->add(new TestOfRouter());

$all_tests->run(new TextReporter());
?>
