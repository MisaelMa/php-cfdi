<?php

namespace Cfdi\Retenciones;

/** Documento Retenciones 2.0: atributos del nodo raíz y nodos hijos requeridos. */
final readonly class Retencion20
{
    /**
     * @param list<ComplementoRetencion>|null $complemento
     */
    public function __construct(
        public string $CveRetenc,
        public string $FechaExp,
        public string $LugarExpRet,
        public EmisorRetencion $emisor,
        public ReceptorRetencion $receptor,
        public PeriodoRetencion $periodo,
        public TotalesRetencion $totales,
        public string $Version = '2.0',
        public ?string $DescRetenc = null,
        public ?string $NumCert = null,
        public ?string $FolioInt = null,
        public ?array $complemento = null,
    ) {
    }
}
