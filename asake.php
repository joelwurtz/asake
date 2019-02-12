<?php

namespace Foo\Bar;

use Asake\Context\Context;

/**
 * @Asake\Annotation\Task(description="Some description")
 */
function baz(Context $context, string $arg1, bool $isNotAllowed, string $arg2 = 'lol') {
    var_dump($arg1);
    var_dump($isNotAllowed);
    var_dump($arg2);
}

/**
 * @Asake\Annotation\Task()
 */
function multi_args(string $arg1, ...$args) {
    var_dump($arg1);
    var_dump($args);
}

function test() {
    echo 'yolo';
}
