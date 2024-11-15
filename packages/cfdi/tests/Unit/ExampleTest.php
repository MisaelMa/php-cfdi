<?php

use Sat\Cfdi\Xml;

test('example', function () {
    echo Xml::cfdi() . PHP_EOL;
    expect(true)->toBeTrue();
});
