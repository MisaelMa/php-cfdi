<?php

use Sat\Utils\Map;

test('sortObject orders keys according to order array', function () {
    $obj = ['b' => 2, 'a' => 1, 'c' => 3];
    $order = ['a', 'b', 'c'];

    $result = Map::sortObject($obj, $order);
    $keys = array_keys($result);

    expect($keys)->toBe(['a', 'b', 'c']);
    expect($result['a'])->toBe(1);
    expect($result['b'])->toBe(2);
    expect($result['c'])->toBe(3);
});

test('sortObject appends unknown keys after ordered ones', function () {
    $obj = ['z' => 26, 'a' => 1, 'b' => 2];
    $order = ['a', 'b'];

    $result = Map::sortObject($obj, $order);
    $keys = array_keys($result);

    expect($keys[0])->toBe('a');
    expect($keys[1])->toBe('b');
    expect($keys[2])->toBe('z');
});

test('sortObject with empty object returns empty', function () {
    $result = Map::sortObject([], ['a', 'b']);
    expect($result)->toBe([]);
});

test('sortObject with empty order returns object as-is', function () {
    $obj = ['b' => 2, 'a' => 1];
    $result = Map::sortObject($obj, []);

    expect($result)->toBe($obj);
});

test('sortObject ignores missing order keys', function () {
    $obj = ['a' => 1, 'b' => 2];
    $order = ['a', 'c', 'b'];

    $result = Map::sortObject($obj, $order);
    $keys = array_keys($result);

    expect($keys)->toBe(['a', 'b']);
});

test('sortObject with single key object', function () {
    $obj = ['a' => 1];
    $order = ['a'];

    $result = Map::sortObject($obj, $order);
    expect($result)->toBe(['a' => 1]);
});

test('sortObject preserves values', function () {
    $obj = [
        'Version' => '4.0',
        'Sello' => '',
        'NoCertificado' => '12345',
        'SubTotal' => '100.00',
    ];
    $order = ['Version', 'NoCertificado', 'Sello', 'SubTotal'];

    $result = Map::sortObject($obj, $order);
    $keys = array_keys($result);

    expect($keys[0])->toBe('Version');
    expect($keys[1])->toBe('NoCertificado');
    expect($keys[2])->toBe('Sello');
    expect($keys[3])->toBe('SubTotal');
    expect($result['Version'])->toBe('4.0');
});
