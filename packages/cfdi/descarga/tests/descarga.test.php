<?php

use Cfdi\Descarga\EstadoSolicitud;
use Cfdi\Descarga\SolicitudParams;
use Cfdi\Descarga\TipoDescarga;
use Cfdi\Descarga\TipoSolicitud;

describe('EstadoSolicitud', function () {
    test('Aceptada tiene valor 1', function () {
        expect(EstadoSolicitud::Aceptada->value)->toBe(1);
    });

    test('EnProceso tiene valor 2', function () {
        expect(EstadoSolicitud::EnProceso->value)->toBe(2);
    });

    test('Terminada tiene valor 3', function () {
        expect(EstadoSolicitud::Terminada->value)->toBe(3);
    });

    test('Error tiene valor 4', function () {
        expect(EstadoSolicitud::Error->value)->toBe(4);
    });

    test('Rechazada tiene valor 5', function () {
        expect(EstadoSolicitud::Rechazada->value)->toBe(5);
    });

    test('Vencida tiene valor 6', function () {
        expect(EstadoSolicitud::Vencida->value)->toBe(6);
    });

    test('label() retorna texto en español', function () {
        expect(EstadoSolicitud::EnProceso->label())->toBe('En proceso');
        expect(EstadoSolicitud::Terminada->label())->toBe('Terminada');
    });
});

describe('TipoSolicitud y TipoDescarga', function () {
    test('valores string coinciden con el SAT', function () {
        expect(TipoSolicitud::CFDI->value)->toBe('CFDI');
        expect(TipoSolicitud::Metadata->value)->toBe('Metadata');
        expect(TipoDescarga::Emitidos->value)->toBe('RfcEmisor');
        expect(TipoDescarga::Recibidos->value)->toBe('RfcReceptor');
    });
});

describe('SolicitudParams', function () {
    test('se construye con parametros requeridos', function () {
        $p = new SolicitudParams(
            rfcSolicitante: 'AAA010101AAA',
            fechaInicio: '2024-01-01',
            fechaFin: '2024-01-31',
            tipoSolicitud: TipoSolicitud::CFDI,
            tipoDescarga: TipoDescarga::Emitidos,
        );
        expect($p->rfcSolicitante)->toBe('AAA010101AAA');
        expect($p->rfcEmisor)->toBeNull();
        expect($p->rfcReceptor)->toBeNull();
    });

    test('acepta rfcEmisor y rfcReceptor opcionales', function () {
        $p = new SolicitudParams(
            rfcSolicitante: 'AAA010101AAA',
            fechaInicio: '2024-01-01',
            fechaFin: '2024-01-31',
            tipoSolicitud: TipoSolicitud::CFDI,
            tipoDescarga: TipoDescarga::Recibidos,
            rfcReceptor: 'BBB020202BBB',
        );
        expect($p->rfcReceptor)->toBe('BBB020202BBB');
    });
});
