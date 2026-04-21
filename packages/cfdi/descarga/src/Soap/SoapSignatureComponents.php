<?php

namespace Cfdi\Descarga\Soap;

final class SoapSignatureComponents
{
    public function __construct(
        public readonly string $bodyDigest,
        public readonly string $signatureValue,
        public readonly string $x509Certificate,
        public readonly string $bodyId,
    ) {
    }
}
