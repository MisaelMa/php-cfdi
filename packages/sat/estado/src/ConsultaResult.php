<?php

namespace Cfdi\Estado;

class ConsultaResult
{
    public readonly bool $activo;
    public readonly bool $cancelado;
    public readonly bool $noEncontrado;

    public function __construct(
        public readonly string $codigoEstatus,
        public readonly string $esCancelable,
        public readonly string $estado,
        public readonly string $estatusCancelacion,
        public readonly string $validacionEFOS,
    ) {
        $this->activo = $this->estado === 'Vigente';
        $this->cancelado = $this->estado === 'Cancelado';
        $this->noEncontrado = $this->estado === 'No Encontrado';
    }
}
