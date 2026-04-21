<?php

declare(strict_types=1);

use Cfdi\Types\Complemento;
use Cfdi\Types\Comprobante;
use Cfdi\Types\ComprobanteAttributes;
use Cfdi\Types\Concepto;
use Cfdi\Types\Conceptos;
use Cfdi\Types\Config;
use Cfdi\Types\Emisor;
use Cfdi\Types\EmisorAttributes;
use Cfdi\Types\Impuestos;
use Cfdi\Types\ImpuestosTotalesAttributes;
use Cfdi\Types\Receptor;
use Cfdi\Types\ReceptorAttributes;
use Cfdi\Types\Retenciones;
use Cfdi\Types\TrasladoRetencionBaseAttributes;
use Cfdi\Types\Traslados;

test('Emisor and Receptor can be instantiated as anonymous implementations', function () {
    $emisor = new class implements Emisor {
        public function getAttributes(): EmisorAttributes
        {
            return new class implements EmisorAttributes {
                public function getRfc(): string
                {
                    return 'AAA010101AAA';
                }

                public function getNombre(): string
                {
                    return 'ACME';
                }

                public function getRegimenFiscal(): string|int
                {
                    return '601';
                }

                public function getFacAtrAdquirente(): string|int|null
                {
                    return null;
                }
            };
        }
    };

    $receptor = new class implements Receptor {
        public function getAttributes(): ReceptorAttributes
        {
            return new class implements ReceptorAttributes {
                public function getRfc(): string
                {
                    return 'URE180429TM6';
                }

                public function getNombre(): string
                {
                    return 'RECEPTOR';
                }

                public function getUsoCfdi(): string
                {
                    return 'G03';
                }

                public function getDomicilioFiscalReceptor(): string
                {
                    return '42501';
                }

                public function getResidenciaFiscal(): ?string
                {
                    return null;
                }

                public function getNumRegIdTrib(): ?string
                {
                    return null;
                }

                public function getRegimenFiscalReceptor(): string|int
                {
                    return '601';
                }
            };
        }
    };

    expect($emisor)->toBeInstanceOf(Emisor::class);
    expect($emisor->getAttributes()->getRfc())->toBe('AAA010101AAA');
    expect($receptor->getAttributes()->getUsoCfdi())->toBe('G03');
});

test('Impuestos with traslados and retenciones', function () {
    $line = new class implements TrasladoRetencionBaseAttributes {
        public function getBase(): string|int|float|null
        {
            return '100.00';
        }

        public function getImpuesto(): string|int
        {
            return '002';
        }

        public function getTipoFactor(): string
        {
            return 'Tasa';
        }

        public function getTasaOCuota(): string|int|float|null
        {
            return '0.160000';
        }

        public function getImporte(): string|int|float|null
        {
            return '16.00';
        }
    };

    $traslados = new class($line) implements Traslados {
        public function __construct(private TrasladoRetencionBaseAttributes $line) {}

        public function getTraslado(): array
        {
            return [
                new class($this->line) implements \Cfdi\Types\Traslado {
                    public function __construct(private TrasladoRetencionBaseAttributes $a) {}

                    public function getAttributes(): TrasladoRetencionBaseAttributes
                    {
                        return $this->a;
                    }
                },
            ];
        }
    };

    $retenciones = new class implements Retenciones {
        public function getRetencion(): array
        {
            return [];
        }
    };

    $totales = new class implements ImpuestosTotalesAttributes {
        public function getTotalImpuestosRetenidos(): string|int|float|null
        {
            return null;
        }

        public function getTotalImpuestosTrasladados(): string|int|float|null
        {
            return '16.00';
        }
    };

    $impuestos = new class($totales, $traslados, $retenciones) implements Impuestos {
        public function __construct(
            private ImpuestosTotalesAttributes $t,
            private Traslados $tr,
            private Retenciones $re,
        ) {}

        public function getAttributes(): ImpuestosTotalesAttributes
        {
            return $this->t;
        }

        public function getTraslados(): ?Traslados
        {
            return $this->tr;
        }

        public function getRetenciones(): ?Retenciones
        {
            return $this->re;
        }
    };

    expect($impuestos->getTraslados()?->getTraslado()[0]->getAttributes()->getImpuesto())->toBe('002');
    expect($impuestos->getAttributes()->getTotalImpuestosTrasladados())->toBe('16.00');
});

test('Config implements tooling and optional signing fields', function () {
    $config = new class implements Config {
        public function isDebug(): bool
        {
            return false;
        }

        public function isCompact(): bool
        {
            return true;
        }

        public function getCustomTags(): ?array
        {
            return null;
        }

        public function getSchema(): ?\Cfdi\Types\ConfigSchema
        {
            return null;
        }

        public function getSaxon(): ?\Cfdi\Types\ConfigSaxonHe
        {
            return null;
        }

        public function getXslt(): ?\Cfdi\Types\ConfigXsltSheet
        {
            return null;
        }

        public function getNoCertificado(): ?string
        {
            return '00001000000405332712';
        }

        public function getCertificado(): ?string
        {
            return 'MIIC...';
        }

        public function getSello(): ?string
        {
            return 'abc123';
        }

        public function getCertificateData(): ?\Cfdi\Types\CertificateData
        {
            return null;
        }
    };

    expect($config->getSello())->toBe('abc123');
    expect($config->isCompact())->toBeTrue();
});

test('Complemento exposes key xmlns and schema location', function () {
    $c = new class implements Complemento {
        public function getComplementPayload(): array
        {
            return ['_attributes' => ['UUID' => 'x']];
        }

        public function getKey(): string
        {
            return 'tfd:TimbreFiscalDigital';
        }

        public function getXmlns(): string
        {
            return 'http://www.sat.gob.mx/TimbreFiscalDigital';
        }

        public function getSchemaLocation(): array
        {
            return [
                'http://www.sat.gob.mx/TimbreFiscalDigital',
                'http://www.sat.gob.mx/sitio_internet/cfd/TimbreFiscalDigital/TimbreFiscalDigitalv11.xsd',
            ];
        }

        public function getXmlnsKey(): string
        {
            return 'tfd';
        }
    };

    expect($c->getXmlnsKey())->toBe('tfd');
    expect($c->getComplementPayload()['_attributes']['UUID'])->toBe('x');
});

test('Comprobante wires core children and attributes', function () {
    $attributes = new class implements ComprobanteAttributes {
        public function getVersion(): ?string
        {
            return '4.0';
        }

        public function getSerie(): ?string
        {
            return 'A';
        }

        public function getFolio(): ?string
        {
            return '1';
        }

        public function getFecha(): string
        {
            return '2024-01-15T12:00:00';
        }

        public function getFormaPago(): null|string|int
        {
            return '03';
        }

        public function getCondicionesDePago(): ?string
        {
            return null;
        }

        public function getSubTotal(): string|int|float
        {
            return '100.00';
        }

        public function getDescuento(): string|int|float|null
        {
            return null;
        }

        public function getMoneda(): string
        {
            return 'MXN';
        }

        public function getTipoCambio(): ?string
        {
            return null;
        }

        public function getTotal(): string|int|float
        {
            return '116.00';
        }

        public function getTipoDeComprobante(): string
        {
            return 'I';
        }

        public function getExportacion(): string
        {
            return '01';
        }

        public function getMetodoPago(): ?string
        {
            return 'PUE';
        }

        public function getLugarExpedicion(): string
        {
            return '42501';
        }

        public function getConfirmacion(): ?string
        {
            return null;
        }

        public function getNoCertificado(): string
        {
            return '00001000000405332712';
        }

        public function getCertificado(): ?string
        {
            return null;
        }

        public function getSello(): ?string
        {
            return null;
        }

        public function getXmlnsXsi(): ?string
        {
            return 'http://www.w3.org/2001/XMLSchema-instance';
        }

        public function getXmlnsXs(): ?string
        {
            return null;
        }

        public function getXsiSchemaLocation(): ?string
        {
            return null;
        }

        public function getSupplementalAttributes(): array
        {
            return ['xmlns:cfdi' => 'http://www.sat.gob.mx/cfd/4'];
        }
    };

    $conceptos = new class implements Conceptos {
        public function getConcepto(): array
        {
            return [new class implements Concepto {
                public function getAttributes(): \Cfdi\Types\ConceptoAttributes
                {
                    return new class implements \Cfdi\Types\ConceptoAttributes {
                        public function getClaveProdServ(): string
                        {
                            return '01010101';
                        }

                        public function getNoIdentificacion(): ?string
                        {
                            return null;
                        }

                        public function getCantidad(): string|int|float
                        {
                            return '1';
                        }

                        public function getClaveUnidad(): string
                        {
                            return 'H87';
                        }

                        public function getUnidad(): ?string
                        {
                            return null;
                        }

                        public function getDescripcion(): string
                        {
                            return 'Producto';
                        }

                        public function getValorUnitario(): string|int|float
                        {
                            return '100.00';
                        }

                        public function getImporte(): string|int|float
                        {
                            return '100.00';
                        }

                        public function getDescuento(): string|int|float|null
                        {
                            return null;
                        }

                        public function getObjetoImp(): string
                        {
                            return '02';
                        }
                    };
                }

                public function getImpuestos(): ?Impuestos
                {
                    return null;
                }

                public function getComplementoConcepto(): ?array
                {
                    return null;
                }

                public function getParte(): ?\Cfdi\Types\ConceptoParte
                {
                    return null;
                }

                public function getInformacionAduanera(): ?array
                {
                    return null;
                }
            }];
        }
    };

    $comprobante = new class($attributes, $conceptos) implements Comprobante {
        public function __construct(
            private ComprobanteAttributes $attributes,
            private Conceptos $conceptos,
        ) {}

        public function getAttributes(): ComprobanteAttributes
        {
            return $this->attributes;
        }

        public function getInformacionGlobal(): ?array
        {
            return null;
        }

        public function getCfdiRelacionados(): ?\Cfdi\Types\CfdiRelacionados
        {
            return null;
        }

        public function getEmisor(): ?Emisor
        {
            return null;
        }

        public function getReceptor(): ?Receptor
        {
            return null;
        }

        public function getConceptos(): Conceptos
        {
            return $this->conceptos;
        }

        public function getImpuestos(): ?Impuestos
        {
            return null;
        }

        public function getComplemento(): ?array
        {
            return null;
        }
    };

    expect($comprobante->getAttributes()->getFecha())->toBe('2024-01-15T12:00:00');
    expect($comprobante->getConceptos()->getConcepto()[0]->getAttributes()->getObjetoImp())->toBe('02');
});
