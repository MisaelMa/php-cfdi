<?php

use Cli\SaxonHe\Query;

test('query uses default binary', function () {
    $query = new Query();
    expect($query->commandline)->toBe('query');
});

test('query accepts custom binary', function () {
    $query = new Query(['binary' => '/usr/local/bin/saxon-query']);
    expect($query->commandline)->toBe('/usr/local/bin/saxon-query');
});

test('query builds backup option', function () {
    $query = new Query();
    $query->backup('on');
    expect($query->commandline)->toContain('-a:on');
});

test('query builds config option', function () {
    $query = new Query();
    $query->config('config.xml');
    expect($query->commandline)->toContain('-config:config.xml');
});

test('query builds mr option', function () {
    $query = new Query();
    $query->mr('MyResolver');
    expect($query->commandline)->toContain('-mr:MyResolver');
});

test('query builds projection option', function () {
    $query = new Query();
    $query->projection('on');
    expect($query->commandline)->toContain('-projection:on');
});

test('query builds q option', function () {
    $query = new Query();
    $query->q('query.xq');
    expect($query->commandline)->toContain('-q:query.xq');
});

test('query builds qs option', function () {
    $query = new Query();
    $query->qs('//book/title');
    expect($query->commandline)->toContain('-qs://book/title');
});

test('query builds stream option', function () {
    $query = new Query();
    $query->stream('on');
    expect($query->commandline)->toContain('-stream:on');
});

test('query builds update option', function () {
    $query = new Query();
    $query->update('discard');
    expect($query->commandline)->toContain('-update:discard');
});

test('query builds wrap option', function () {
    $query = new Query();
    $query->wrap();
    expect($query->commandline)->toContain('-wrap');
});

test('query chains multiple options', function () {
    $query = new Query(['binary' => 'saxon-query']);
    $query->q('query.xq')
        ->projection('on')
        ->stream('off');

    expect($query->commandline)->toContain('saxon-query');
    expect($query->commandline)->toContain('-q:query.xq');
    expect($query->commandline)->toContain('-projection:on');
    expect($query->commandline)->toContain('-stream:off');
});

test('query commandlineArray tracks all options', function () {
    $query = new Query();
    $query->q('test.xq')->wrap()->update('on');

    expect($query->commandlineArray)->toContain('-q:test.xq');
    expect($query->commandlineArray)->toContain('-wrap');
    expect($query->commandlineArray)->toContain('-update:on');
    expect($query->commandlineArray)->toHaveCount(3);
});

test('query inherits shared options', function () {
    $query = new Query();
    $query->catalog('catalog.xml')
        ->dtd('off')
        ->expand('on');

    expect($query->commandline)->toContain('-catalog:catalog.xml');
    expect($query->commandline)->toContain('-dtd:off');
    expect($query->commandline)->toContain('-expand:on');
});
