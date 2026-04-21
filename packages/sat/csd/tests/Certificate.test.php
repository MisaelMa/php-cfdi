<?php

use Sat\Csd\Certificate;

$CERTS_DIR = realpath(__DIR__ . '/../../../../../cfdi-node/packages/files/certificados');
$CSD_CER = $CERTS_DIR . '/LAN7008173R5.cer';
$CSD_CER_PEM = $CERTS_DIR . '/LAN7008173R5.cer.pem';

test('carga el certificado desde archivo .cer (DER)', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    expect($cert)->toBeInstanceOf(Certificate::class);
});

test('carga el certificado desde archivo .pem', function () use ($CSD_CER_PEM) {
    $cert = Certificate::fromFile($CSD_CER_PEM);
    expect($cert)->toBeInstanceOf(Certificate::class);
});

test('carga el certificado desde PEM string', function () use ($CSD_CER_PEM) {
    $pem = file_get_contents($CSD_CER_PEM);
    $cert = Certificate::fromPem($pem);
    expect($cert)->toBeInstanceOf(Certificate::class);
});

test('carga el certificado desde buffer DER', function () use ($CSD_CER) {
    $der = file_get_contents($CSD_CER);
    $cert = Certificate::fromDer($der);
    expect($cert)->toBeInstanceOf(Certificate::class);
});

test('serialNumber retorna hex string', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    $serial = $cert->serialNumber();
    expect($serial)->toBeString();
    expect(strlen($serial))->toBeGreaterThan(0);
});

test('noCertificado retorna 20 digitos', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    $noCer = $cert->noCertificado();
    expect($noCer)->toMatch('/^\d{20}$/');
});

test('noCertificado coincide entre DER y PEM', function () use ($CSD_CER, $CSD_CER_PEM) {
    $certDer = Certificate::fromFile($CSD_CER);
    $certPem = Certificate::fromFile($CSD_CER_PEM);
    expect($certDer->noCertificado())->toBe($certPem->noCertificado());
});

test('rfc extrae el RFC del subject', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    $rfc = $cert->rfc();
    expect(strlen($rfc))->toBeGreaterThanOrEqual(12);
    expect(strlen($rfc))->toBeLessThanOrEqual(13);
});

test('rfc del CSD es LAN7008173R5', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    expect($cert->rfc())->toBe('LAN7008173R5');
});

test('legalName extrae el nombre legal del subject', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    $name = $cert->legalName();
    expect($name)->toBeString();
    expect(strlen($name))->toBeGreaterThan(0);
});

test('legalName contiene CINDEMEX', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    expect(strtoupper($cert->legalName()))->toContain('CINDEMEX');
});

test('subject retorna array', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    $sub = $cert->subject();
    expect($sub)->toBeArray();
    expect(count($sub))->toBeGreaterThan(0);
});

test('issuer retorna array', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    $iss = $cert->issuer();
    expect($iss)->toBeArray();
    expect(count($iss))->toBeGreaterThan(0);
});

test('validFrom retorna DateTimeImmutable', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    expect($cert->validFrom())->toBeInstanceOf(\DateTimeImmutable::class);
});

test('validTo retorna DateTimeImmutable', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    expect($cert->validTo())->toBeInstanceOf(\DateTimeImmutable::class);
});

test('validTo es posterior a validFrom', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    expect($cert->validTo()->getTimestamp())->toBeGreaterThan($cert->validFrom()->getTimestamp());
});

test('el certificado de prueba del SAT esta vencido', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    expect($cert->isExpired())->toBe(true);
});

test('fingerprint retorna formato XX:XX:XX', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    $fp = $cert->fingerprint();
    expect($fp)->toMatch('/^[0-9A-F]{2}(:[0-9A-F]{2})+$/');
    expect(strlen($fp))->toBe(59);
});

test('fingerprint es consistente entre llamadas', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    expect($cert->fingerprint())->toBe($cert->fingerprint());
});

test('publicKey retorna PEM', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    $pk = $cert->publicKey();
    expect($pk)->toContain('-----BEGIN PUBLIC KEY-----');
});

test('el certificado LAN7008173R5 es CSD', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    expect($cert->isCsd())->toBe(true);
});

test('isFiel retorna false para un CSD', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    expect($cert->isFiel())->toBe(false);
});

test('toPem retorna PEM valido', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    $pem = $cert->toPem();
    expect($pem)->toContain('-----BEGIN CERTIFICATE-----');
    expect($pem)->toContain('-----END CERTIFICATE-----');
});

test('toDer retorna contenido binario', function () use ($CSD_CER) {
    $cert = Certificate::fromFile($CSD_CER);
    $der = $cert->toDer();
    expect(strlen($der))->toBeGreaterThan(0);
});

test('round-trip DER -> Certificate -> DER mantiene noCertificado', function () use ($CSD_CER) {
    $cert1 = Certificate::fromFile($CSD_CER);
    $der1 = $cert1->toDer();
    $cert2 = Certificate::fromDer($der1);
    expect($cert2->noCertificado())->toBe($cert1->noCertificado());
});
