<?php

use Sat\Cfdi\BaseImpuestos;

describe('base_impuetos', function () {

    it('debería crear una instancia de BaseImpuestos con los atributos dados', function () {

        $totalImpuestos = [
            'TotalImpuestosTrasladados' => '100.00',
        ];

        $baseImpuestos = new BaseImpuestos($totalImpuestos);

        expect($baseImpuestos->getTotalImpuestos())->toBe($totalImpuestos);
    });


    it('debería agregar un traslado', function () {
        $totalImpuestos = [
            'TotalImpuestosTrasladados' => '100.00',
        ];
        $baseImpuestos = new BaseImpuestos($totalImpuestos);

        $trasladoPayload = [
            'Base' => '1000',
            'Impuesto' => '002',
            'TipoFactor' => 'Tasa',
            'TasaOCuota' => '0.160000',
            'Importe' => '160.00',
        ];

        $baseImpuestos->setTraslado($trasladoPayload);
        expect($baseImpuestos->getTraslados())->toContainEqual([
            '_attributes' => $trasladoPayload,
        ]);
    });

    it('debería agregar una retención',  function () {
        $totalImpuestos = [
            'TotalImpuestosTrasladados' => '100.00',
        ];

        $baseImpuestos = new BaseImpuestos($totalImpuestos);
        $retencionPayload = [
            'Impuesto' => '001',
            'Importe' => '50.00',
        ];
        $baseImpuestos->setRetencion($retencionPayload);
        expect($baseImpuestos->getRetenciones())->toContainEqual([
            '_attributes' => $retencionPayload,
        ]);
    });

    it('debería retornar los impuestos totales', function () {
        $totalImpuestos = [
            'TotalImpuestosTrasladados' => '100.00'
        ];
        $baseImpuestos = new BaseImpuestos($totalImpuestos);
        expect($baseImpuestos->getTotalImpuestos())->toEqual([
            'TotalImpuestosTrasladados' => '100.00'
        ]);
    });

    it('debería retornar las retenciones', function () {
        $totalImpuestos = [
            'TotalImpuestosTrasladados' => '100.00',
        ];
        $baseImpuestos = new BaseImpuestos($totalImpuestos);
        $retencionPayload = [
            'Impuesto' => '001',
            'Importe' => '50.00',
        ];

        $baseImpuestos->setRetencion($retencionPayload);
        expect($baseImpuestos->getRetenciones())->toContainEqual([
            '_attributes' => $retencionPayload,
        ]);
    });

    it('debería retornar los traslados', function () {
        $totalImpuestos = [
            'TotalImpuestosTrasladados' => '100.00',
        ];
        $baseImpuestos = new BaseImpuestos($totalImpuestos);
        $trasladoPayload = [
            'Base' => '1000',
            'Impuesto' => '002',
            'TipoFactor' => 'Tasa',
            'TasaOCuota' => '0.160000',
            'Importe' => '160.00',
        ];

        $baseImpuestos->setTraslado($trasladoPayload);

        expect($baseImpuestos->getTraslados())->toContainEqual([
            '_attributes' => $trasladoPayload,
        ]);
    });
});
