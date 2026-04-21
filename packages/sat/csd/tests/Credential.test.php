<?php

use Sat\Csd\Credential;

$CERTS_DIR = realpath(__DIR__ . '/../../../../../cfdi-node/packages/files/certificados');
$CSD_CER = $CERTS_DIR . '/LAN7008173R5.cer';
$CSD_KEY = $CERTS_DIR . '/LAN7008173R5.key';
$CSD_CER_PEM = $CERTS_DIR . '/LAN7008173R5.cer.pem';
$CSD_KEY_PEM = $CERTS_DIR . '/LAN7008173R5.key.pem';
$KEY_PASSWORD = '12345678a';
$RFC_ESPERADO = 'LAN7008173R5';

test('crea un Credential desde archivos .cer y .key', function () use ($CSD_CER, $CSD_KEY, $KEY_PASSWORD) {
    $cred = Credential::create($CSD_CER, $CSD_KEY, $KEY_PASSWORD);
    expect($cred)->toBeInstanceOf(Credential::class);
});

test('crea un Credential desde archivos PEM', function () use ($CSD_CER_PEM, $CSD_KEY_PEM, $KEY_PASSWORD) {
    $cred = Credential::create($CSD_CER_PEM, $CSD_KEY_PEM, $KEY_PASSWORD);
    expect($cred)->toBeInstanceOf(Credential::class);
});

test('lanza error con contrasena incorrecta', function () use ($CSD_CER, $CSD_KEY) {
    Credential::create($CSD_CER, $CSD_KEY, 'mal_password');
})->throws(RuntimeException::class);

test('crea un Credential desde strings PEM', function () use ($CSD_CER_PEM, $CSD_KEY_PEM) {
    $cerPem = file_get_contents($CSD_CER_PEM);
    $keyPem = file_get_contents($CSD_KEY_PEM);
    $cred = Credential::fromPem($cerPem, $keyPem);
    expect($cred)->toBeInstanceOf(Credential::class);
});

test('el Credential con CSD de prueba es CSD', function () use ($CSD_CER, $CSD_KEY, $KEY_PASSWORD) {
    $cred = Credential::create($CSD_CER, $CSD_KEY, $KEY_PASSWORD);
    expect($cred->isCsd())->toBe(true);
});

test('isFiel es false para un CSD', function () use ($CSD_CER, $CSD_KEY, $KEY_PASSWORD) {
    $cred = Credential::create($CSD_CER, $CSD_KEY, $KEY_PASSWORD);
    expect($cred->isFiel())->toBe(false);
});

test('retorna el RFC del titular', function () use ($CSD_CER, $CSD_KEY, $KEY_PASSWORD, $RFC_ESPERADO) {
    $cred = Credential::create($CSD_CER, $CSD_KEY, $KEY_PASSWORD);
    expect($cred->rfc())->toBe($RFC_ESPERADO);
});

test('retorna el nombre legal del titular', function () use ($CSD_CER, $CSD_KEY, $KEY_PASSWORD) {
    $cred = Credential::create($CSD_CER, $CSD_KEY, $KEY_PASSWORD);
    $name = $cred->legalName();
    expect($name)->toBeString();
    expect(strtoupper($name))->toContain('CINDEMEX');
});

test('serialNumber retorna hex', function () use ($CSD_CER, $CSD_KEY, $KEY_PASSWORD) {
    $cred = Credential::create($CSD_CER, $CSD_KEY, $KEY_PASSWORD);
    expect($cred->serialNumber())->toBeString();
    expect(strlen($cred->serialNumber()))->toBeGreaterThan(0);
});

test('noCertificado retorna 20 digitos', function () use ($CSD_CER, $CSD_KEY, $KEY_PASSWORD) {
    $cred = Credential::create($CSD_CER, $CSD_KEY, $KEY_PASSWORD);
    expect($cred->noCertificado())->toMatch('/^\d{20}$/');
});

test('firma datos y verifica la firma', function () use ($CSD_CER, $CSD_KEY, $KEY_PASSWORD) {
    $cred = Credential::create($CSD_CER, $CSD_KEY, $KEY_PASSWORD);
    $data = '||cadena|original|de|prueba||';
    $signature = $cred->sign($data);

    expect($signature)->toBeString();
    expect(strlen($signature))->toBeGreaterThan(0);
    expect($cred->verify($data, $signature))->toBe(true);
});

test('verifica firma incorrecta retorna false', function () use ($CSD_CER, $CSD_KEY, $KEY_PASSWORD) {
    $cred = Credential::create($CSD_CER, $CSD_KEY, $KEY_PASSWORD);
    $data = 'datos originales';
    $firmaFalsa = base64_encode('firma_falsa');
    expect($cred->verify($data, $firmaFalsa))->toBe(false);
});

test('firma con datos diferentes no verifica como datos originales', function () use ($CSD_CER, $CSD_KEY, $KEY_PASSWORD) {
    $cred = Credential::create($CSD_CER, $CSD_KEY, $KEY_PASSWORD);
    $firmaOtros = $cred->sign('datos diferentes');
    expect($cred->verify('datos originales', $firmaOtros))->toBe(false);
});

test('firma desde PEM equivale a firma desde DER', function () use ($CSD_CER, $CSD_KEY, $KEY_PASSWORD, $CSD_CER_PEM, $CSD_KEY_PEM) {
    $credDer = Credential::create($CSD_CER, $CSD_KEY, $KEY_PASSWORD);
    $cerPem = file_get_contents($CSD_CER_PEM);
    $keyPem = file_get_contents($CSD_KEY_PEM);
    $credPem = Credential::fromPem($cerPem, $keyPem);

    $data = 'cadena de prueba';
    $sigPem = $credPem->sign($data);
    expect($credDer->verify($data, $sigPem))->toBe(true);
});

test('el certificado de prueba del SAT esta vencido', function () use ($CSD_CER, $CSD_KEY, $KEY_PASSWORD) {
    $cred = Credential::create($CSD_CER, $CSD_KEY, $KEY_PASSWORD);
    expect($cred->isValid())->toBe(false);
});

test('pertenece al RFC correcto', function () use ($CSD_CER, $CSD_KEY, $KEY_PASSWORD, $RFC_ESPERADO) {
    $cred = Credential::create($CSD_CER, $CSD_KEY, $KEY_PASSWORD);
    expect($cred->belongsTo($RFC_ESPERADO))->toBe(true);
});

test('pertenece al RFC en minusculas', function () use ($CSD_CER, $CSD_KEY, $KEY_PASSWORD, $RFC_ESPERADO) {
    $cred = Credential::create($CSD_CER, $CSD_KEY, $KEY_PASSWORD);
    expect($cred->belongsTo(strtolower($RFC_ESPERADO)))->toBe(true);
});

test('no pertenece a un RFC diferente', function () use ($CSD_CER, $CSD_KEY, $KEY_PASSWORD) {
    $cred = Credential::create($CSD_CER, $CSD_KEY, $KEY_PASSWORD);
    expect($cred->belongsTo('XAXX010101000'))->toBe(false);
});

test('keyMatchesCertificate retorna true', function () use ($CSD_CER, $CSD_KEY, $KEY_PASSWORD) {
    $cred = Credential::create($CSD_CER, $CSD_KEY, $KEY_PASSWORD);
    expect($cred->keyMatchesCertificate())->toBe(true);
});

test('expone el certificado', function () use ($CSD_CER, $CSD_KEY, $KEY_PASSWORD, $RFC_ESPERADO) {
    $cred = Credential::create($CSD_CER, $CSD_KEY, $KEY_PASSWORD);
    expect($cred->getCertificate())->toBeInstanceOf(\Sat\Csd\Certificate::class);
    expect($cred->getCertificate()->rfc())->toBe($RFC_ESPERADO);
});

test('expone la llave privada', function () use ($CSD_CER, $CSD_KEY, $KEY_PASSWORD) {
    $cred = Credential::create($CSD_CER, $CSD_KEY, $KEY_PASSWORD);
    expect($cred->getPrivateKey())->toBeInstanceOf(\Sat\Csd\PrivateKey::class);
    expect($cred->getPrivateKey()->toPem())->toContain('-----BEGIN PRIVATE KEY-----');
});
