<?php

use Sat\Csd\PrivateKey;
use Sat\Csd\Certificate;

$CERTS_DIR = realpath(__DIR__ . '/../../../../../cfdi-node/packages/files/certificados');
$CSD_CER = $CERTS_DIR . '/LAN7008173R5.cer';
$CSD_KEY = $CERTS_DIR . '/LAN7008173R5.key';
$CSD_KEY_PEM = $CERTS_DIR . '/LAN7008173R5.key.pem';
$KEY_PASSWORD = '12345678a';

test('carga la llave privada desde archivo .key con contrasena', function () use ($CSD_KEY, $KEY_PASSWORD) {
    $key = PrivateKey::fromFile($CSD_KEY, $KEY_PASSWORD);
    expect($key)->toBeInstanceOf(PrivateKey::class);
});

test('lanza error con contrasena incorrecta', function () use ($CSD_KEY) {
    PrivateKey::fromFile($CSD_KEY, 'contrasena_incorrecta');
})->throws(RuntimeException::class);

test('carga la llave privada desde PEM sin cifrado', function () use ($CSD_KEY_PEM) {
    $pem = file_get_contents($CSD_KEY_PEM);
    $key = PrivateKey::fromPem($pem);
    expect($key)->toBeInstanceOf(PrivateKey::class);
});

test('carga la llave privada desde buffer DER cifrado', function () use ($CSD_KEY, $KEY_PASSWORD) {
    $der = file_get_contents($CSD_KEY);
    $key = PrivateKey::fromDer($der, $KEY_PASSWORD);
    expect($key)->toBeInstanceOf(PrivateKey::class);
});

test('toPem retorna formato PEM valido', function () use ($CSD_KEY, $KEY_PASSWORD) {
    $key = PrivateKey::fromFile($CSD_KEY, $KEY_PASSWORD);
    $pem = $key->toPem();
    expect($pem)->toContain('-----BEGIN PRIVATE KEY-----');
    expect($pem)->toContain('-----END PRIVATE KEY-----');
});

test('firma datos y retorna base64', function () use ($CSD_KEY, $KEY_PASSWORD) {
    $key = PrivateKey::fromFile($CSD_KEY, $KEY_PASSWORD);
    $data = 'cadena original de prueba';
    $signature = $key->sign($data);
    expect($signature)->toBeString();
    expect(strlen($signature))->toBeGreaterThan(0);
    expect(base64_decode($signature, true))->not->toBe(false);
});

test('firmas de la misma cadena son verificables', function () use ($CSD_KEY, $KEY_PASSWORD, $CSD_CER) {
    $key = PrivateKey::fromFile($CSD_KEY, $KEY_PASSWORD);
    $cert = Certificate::fromFile($CSD_CER);
    $data = 'datos a firmar';
    $signature = $key->sign($data);

    $pubKey = openssl_pkey_get_public($cert->publicKey());
    $sigBinary = base64_decode($signature);
    $result = openssl_verify($data, $sigBinary, $pubKey, OPENSSL_ALGO_SHA256);
    expect($result)->toBe(1);
});

test('la firma cambia con datos diferentes', function () use ($CSD_KEY, $KEY_PASSWORD) {
    $key = PrivateKey::fromFile($CSD_KEY, $KEY_PASSWORD);
    $sig1 = $key->sign('datos 1');
    $sig2 = $key->sign('datos 2');
    expect($sig1)->not->toBe($sig2);
});

test('firma con algoritmo alternativo SHA512', function () use ($CSD_KEY, $KEY_PASSWORD) {
    $key = PrivateKey::fromFile($CSD_KEY, $KEY_PASSWORD);
    $sig = $key->sign('datos', 'SHA512');
    expect($sig)->toBeString();
    expect(strlen($sig))->toBeGreaterThan(0);
});

test('la llave pertenece al certificado correspondiente', function () use ($CSD_KEY, $KEY_PASSWORD, $CSD_CER) {
    $key = PrivateKey::fromFile($CSD_KEY, $KEY_PASSWORD);
    $cert = Certificate::fromFile($CSD_CER);
    expect($key->belongsToCertificate($cert))->toBe(true);
});

test('la llave PEM pertenece al certificado correspondiente', function () use ($CSD_KEY_PEM, $CSD_CER) {
    $keyPem = file_get_contents($CSD_KEY_PEM);
    $key = PrivateKey::fromPem($keyPem);
    $certPem = file_get_contents($CSD_CER . '.pem');
    $cert = Certificate::fromPem($certPem);
    expect($key->belongsToCertificate($cert))->toBe(true);
});
