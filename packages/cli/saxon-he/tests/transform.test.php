<?php

use Cli\SaxonHe\Transform;

test('transform uses default binary', function () {
    $transform = new Transform();
    expect($transform->commandline)->toBe('transform');
});

test('transform accepts custom binary', function () {
    $transform = new Transform(['binary' => '/usr/local/bin/saxon']);
    expect($transform->commandline)->toBe('/usr/local/bin/saxon');
});

test('transform builds a option', function () {
    $transform = new Transform();
    $transform->a('on');
    expect($transform->commandline)->toContain('-a:on');
});

test('transform a rejects invalid option', function () {
    $transform = new Transform();
    $transform->a('invalid');
})->throws(\InvalidArgumentException::class);

test('transform builds ea option', function () {
    $transform = new Transform();
    $transform->ea('off');
    expect($transform->commandline)->toContain('-ea:off');
});

test('transform builds explain option', function () {
    $transform = new Transform();
    $transform->explain('output.txt');
    expect($transform->commandline)->toContain('-explain:output.txt');
});

test('transform builds export option', function () {
    $transform = new Transform();
    $transform->exportFile('export.sef');
    expect($transform->commandline)->toContain('-export:export.sef');
});

test('transform builds im option', function () {
    $transform = new Transform();
    $transform->im('mymode');
    expect($transform->commandline)->toContain('-im:mymode');
});

test('transform builds it option', function () {
    $transform = new Transform();
    $transform->it('main');
    expect($transform->commandline)->toContain('-it:main');
});

test('transform builds jit option', function () {
    $transform = new Transform();
    $transform->jit('on');
    expect($transform->commandline)->toContain('-jit:on');
});

test('transform jit rejects invalid option', function () {
    $transform = new Transform();
    $transform->jit('maybe');
})->throws(\InvalidArgumentException::class);

test('transform builds lib option', function () {
    $transform = new Transform();
    $transform->lib('/path/to/lib');
    expect($transform->commandline)->toContain('-lib:/path/to/lib');
});

test('transform builds license option', function () {
    $transform = new Transform();
    $transform->license('on');
    expect($transform->commandline)->toContain('-license:on');
});

test('transform builds m option', function () {
    $transform = new Transform();
    $transform->m('MyClass');
    expect($transform->commandline)->toContain('-m:MyClass');
});

test('transform builds nogo option', function () {
    $transform = new Transform();
    $transform->nogo();
    expect($transform->commandline)->toContain('-nogo');
});

test('transform builds ns option', function () {
    $transform = new Transform();
    $transform->ns('uri');
    expect($transform->commandline)->toContain('-ns:uri');
});

test('transform ns rejects invalid option', function () {
    $transform = new Transform();
    $transform->ns('invalid');
})->throws(\InvalidArgumentException::class);

test('transform builds or option', function () {
    $transform = new Transform();
    $transform->orOutput('MyOutputResolver');
    expect($transform->commandline)->toContain('-or:MyOutputResolver');
});

test('transform builds relocate option', function () {
    $transform = new Transform();
    $transform->relocate('on');
    expect($transform->commandline)->toContain('-relocate:on');
});

test('transform builds target option', function () {
    $transform = new Transform();
    $transform->target('HE');
    expect($transform->commandline)->toContain('-target:HE');
});

test('transform target rejects invalid option', function () {
    $transform = new Transform();
    $transform->target('INVALID');
})->throws(\InvalidArgumentException::class);

test('transform builds threads option', function () {
    $transform = new Transform();
    $transform->threads(4);
    expect($transform->commandline)->toContain('-threads:4');
});

test('transform builds warnings option', function () {
    $transform = new Transform();
    $transform->warnings('silent');
    expect($transform->commandline)->toContain('-warnings:silent');
});

test('transform warnings rejects invalid option', function () {
    $transform = new Transform();
    $transform->warnings('invalid');
})->throws(\InvalidArgumentException::class);

test('transform builds y option', function () {
    $transform = new Transform();
    $transform->y('output.xml');
    expect($transform->commandline)->toContain('-y:output.xml');
});

test('transform xsl throws on missing file', function () {
    $transform = new Transform();
    $transform->xsl('/nonexistent/file.xsl');
})->throws(\RuntimeException::class);

test('transform chains multiple options', function () {
    $transform = new Transform(['binary' => 'saxon-transform']);
    $transform->a('on')
        ->target('HE')
        ->threads(2)
        ->warnings('silent');

    expect($transform->commandline)->toContain('saxon-transform');
    expect($transform->commandline)->toContain('-a:on');
    expect($transform->commandline)->toContain('-target:HE');
    expect($transform->commandline)->toContain('-threads:2');
    expect($transform->commandline)->toContain('-warnings:silent');
});

test('transform commandlineArray tracks all options', function () {
    $transform = new Transform();
    $transform->a('on')->nogo()->target('HE');

    expect($transform->commandlineArray)->toContain('-a:on');
    expect($transform->commandlineArray)->toContain('-nogo');
    expect($transform->commandlineArray)->toContain('-target:HE');
    expect($transform->commandlineArray)->toHaveCount(3);
});
