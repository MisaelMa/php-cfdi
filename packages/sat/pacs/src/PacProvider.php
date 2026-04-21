<?php

declare(strict_types=1);

namespace Sat\Pacs;

/** Contrato mínimo para un proveedor PAC. */
interface PacProvider
{
    public function timbrar(TimbradoRequest $request): TimbradoResult;

    public function cancelar(
        string $uuid,
        string $rfcEmisor,
        string $motivo,
        ?string $folioSustitucion = null,
    ): CancelacionPacResult;

    public function consultarEstatus(string $uuid): ConsultaEstatusResult;
}
