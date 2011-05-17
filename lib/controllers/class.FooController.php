<?php
class FooController {
    public function index() {
        return "Hello from foo!";
    }

    public function parameters() {
        $return = "Hello from foo with parameters!\n";
        foreach ($_GET as $key => $value) {
            $return .= $key . ': ' . $value . "\n";
        }

        return $return;
    }
}

