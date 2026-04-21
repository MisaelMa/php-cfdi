<?php

use Cli\Openssl\Utils;

test('getOsComandBin returns openssl or openssl.exe', function () {
    $bin = Utils::getOsComandBin();

    expect($bin)->toBeIn(['openssl', 'openssl.exe']);
});

test('getOsComandBin returns string', function () {
    $bin = Utils::getOsComandBin();

    expect($bin)->toBeString();
});

test('readFileSync reads file content', function () {
    $tmpFile = tempnam(sys_get_temp_dir(), 'openssl_test_');
    file_put_contents($tmpFile, 'test content');

    $content = Utils::readFileSync($tmpFile);

    expect($content)->toBe('test content');

    unlink($tmpFile);
});
