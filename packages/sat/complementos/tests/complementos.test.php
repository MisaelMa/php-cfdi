<?php

use Sat\Cfdi\Complementos\Aerolineas;
use Sat\Cfdi\Complementos\CartaPorte20;
use Sat\Cfdi\Complementos\Cce11;
use Sat\Cfdi\Complementos\Complemento;
use Sat\Cfdi\Complementos\ConsumoDeCombustibles;
use Sat\Cfdi\Complementos\Destruccion;
use Sat\Cfdi\Complementos\Divisas;
use Sat\Cfdi\Complementos\Donat;
use Sat\Cfdi\Complementos\Implocal;
use Sat\Cfdi\Complementos\Ine;
use Sat\Cfdi\Complementos\LeyendasFiscales;
use Sat\Cfdi\Complementos\Nomina12;
use Sat\Cfdi\Complementos\ObrasArte;
use Sat\Cfdi\Complementos\PagoEnEspecie;
use Sat\Cfdi\Complementos\Pagos20;
use Sat\Cfdi\Complementos\Pfic;
use Sat\Cfdi\Complementos\RegistroFiscal;
use Sat\Cfdi\Complementos\ServicioParcial;
use Sat\Cfdi\Complementos\Tfd;
use Sat\Cfdi\Complementos\Tpe;
use Sat\Cfdi\Complementos\ValesDeDespensa;
use Sat\Cfdi\Complementos\VehiculoUsado;

describe('complementos builders', function () {

    $cases = [
        [
            'Pagos20',
            fn () => new Pagos20([]),
            'pago20:Pagos',
            'pago20',
            'http://www.sat.gob.mx/Pagos20',
            'http://www.sat.gob.mx/sitio_internet/cfd/Pagos/Pagos20.xsd',
        ],
        [
            'CartaPorte20',
            fn () => new CartaPorte20([]),
            'cartaporte20:CartaPorte',
            'cartaporte20',
            'http://www.sat.gob.mx/CartaPorte20',
            'http://www.sat.gob.mx/sitio_internet/cfd/CartaPorte/CartaPorte20.xsd',
        ],
        [
            'Aerolineas',
            fn () => new Aerolineas(['version' => '1.0']),
            'aerolineas:Aerolineas',
            'aerolineas',
            'http://www.sat.gob.mx/aerolineas',
            'http://www.sat.gob.mx/sitio_internet/cfd/aerolineas/aerolineas.xsd',
        ],
        [
            'Ine',
            fn () => new Ine(['TipoProceso' => 'Ordinario', 'TipoComite' => 'Ejecutivo Nacional']),
            'ine:INE',
            'ine',
            'http://www.sat.gob.mx/ine',
            'http://www.sat.gob.mx/sitio_internet/cfd/ine/ine11.xsd',
        ],
        [
            'Nomina12',
            fn () => new Nomina12([
                'Version' => '1.2',
                'TipoNomina' => 'O',
                'FechaPago' => '2024-01-01',
                'FechaInicialPago' => '2024-01-01',
                'FechaFinalPago' => '2024-01-31',
                'NumDiasPagados' => '31',
            ]),
            'nomina12:Nomina',
            'nomina12',
            'http://www.sat.gob.mx/nomina12',
            'http://www.sat.gob.mx/sitio_internet/cfd/nomina/nomina12.xsd',
        ],
        [
            'Tfd',
            fn () => new Tfd([
                'Version' => '1.1',
                'UUID' => '00000000-0000-0000-0000-000000000001',
                'FechaTimbrado' => '2024-01-01T00:00:00',
                'RfcProvCertif' => 'SAT970701NN3',
                'SelloCFD' => 'S',
                'NoCertificadoSAT' => '00001000000504465028',
                'SelloSAT' => 'T',
            ]),
            'tfd:TimbreFiscalDigital',
            'tfd',
            'http://www.sat.gob.mx/TimbreFiscalDigital',
            'http://www.sat.gob.mx/sitio_internet/cfd/TimbreFiscalDigital/TimbreFiscalDigitalv11.xsd',
        ],
        [
            'Implocal',
            fn () => new Implocal(['version' => '1.0']),
            'implocal:ImpuestosLocales',
            'implocal',
            'http://www.sat.gob.mx/implocal',
            'http://www.sat.gob.mx/sitio_internet/cfd/implocal/implocal.xsd',
        ],
        [
            'Donat',
            fn () => new Donat(['version' => '1.1', 'noAutorizacion' => '123']),
            'donat:Donatarias',
            'donat',
            'http://www.sat.gob.mx/donat',
            'http://www.sat.gob.mx/sitio_internet/cfd/donat/donat11.xsd',
        ],
        [
            'Divisas',
            fn () => new Divisas(['version' => '1.0', 'tipoOperacion' => 'compra']),
            'divisas:Divisas',
            'divisas',
            'http://www.sat.gob.mx/divisas',
            'http://www.sat.gob.mx/sitio_internet/cfd/divisas/divisas.xsd',
        ],
        [
            'Cce11',
            fn () => new Cce11(['Version' => '1.1', 'MotivoTraslado' => '01', 'TipoOperacion' => '2']),
            'cce11:ComercioExterior',
            'cce11',
            'http://www.sat.gob.mx/ComercioExterior11',
            'http://www.sat.gob.mx/sitio_internet/cfd/ComercioExterior11/ComercioExterior11.xsd',
        ],
        [
            'VehiculoUsado',
            fn () => new VehiculoUsado(['Version' => '1.0']),
            'vehiculousado:VehiculoUsado',
            'vehiculousado',
            'http://www.sat.gob.mx/vehiculousado',
            'http://www.sat.gob.mx/sitio_internet/cfd/vehiculousado/vehiculousado.xsd',
        ],
        [
            'LeyendasFiscales',
            fn () => new LeyendasFiscales([]),
            'leyendasFisc:LeyendasFiscales',
            'leyendasFisc',
            'http://www.sat.gob.mx/leyendasFiscales',
            'http://www.sat.gob.mx/sitio_internet/cfd/leyendasFiscales/leyendasFisc.xsd',
        ],
        [
            'ServicioParcial',
            fn () => new ServicioParcial(['Version' => '1.0', 'NumPerLicoAut' => '1']),
            'servicioparcial:parcialesconstruccion',
            'servicioparcial',
            'http://www.sat.gob.mx/servicioparcialconstruccion',
            'http://www.sat.gob.mx/sitio_internet/cfd/servicioparcialconstruccion/servicioparcialconstruccion.xsd',
        ],
        [
            'ObrasArte',
            fn () => new ObrasArte(['Version' => '1.0', 'TipoBien' => '01']),
            'obrasarte:obrasarteantiguedades',
            'obrasarte',
            'http://www.sat.gob.mx/arteantiguedades',
            'http://www.sat.gob.mx/sitio_internet/cfd/arteantiguedades/obrasarteantiguedades.xsd',
        ],
        [
            'PagoEnEspecie',
            fn () => new PagoEnEspecie(['Version' => '1.0', 'Operacion' => '01']),
            'pagoenespecie:PagoEnEspecie',
            'pagoenespecie',
            'http://www.sat.gob.mx/pagoenespecie',
            'http://www.sat.gob.mx/sitio_internet/cfd/pagoenespecie/pagoenespecie.xsd',
        ],
        [
            'Pfic',
            fn () => new Pfic(['Version' => '1.0', 'ClaveVehicular' => 'ABC']),
            'pfic:PFintegranteCoordinado',
            'pfic',
            'http://www.sat.gob.mx/pfic',
            'http://www.sat.gob.mx/sitio_internet/cfd/pfic/pfic.xsd',
        ],
        [
            'Tpe',
            fn () => new Tpe(['Version' => '1.0', 'fechadeTransito' => '2024-01-01', 'tipoTransito' => 'Arribo']),
            'tpe:TuristaPasajeroExtranjero',
            'tpe',
            'http://www.sat.gob.mx/TuristaPasajeroExtranjero',
            'http://www.sat.gob.mx/sitio_internet/cfd/TuristaPasajeroExtranjero/TuristaPasajeroExtranjero.xsd',
        ],
        [
            'ValesDeDespensa',
            fn () => new ValesDeDespensa(['Version' => '1.0', 'TipoOperacion' => 'monedero electronico']),
            'valesdedespensa:ValesDeDespensa',
            'valesdedespensa',
            'http://www.sat.gob.mx/valesdedespensa',
            'http://www.sat.gob.mx/sitio_internet/cfd/valesdedespensa/valesdedespensa.xsd',
        ],
        [
            'Destruccion',
            fn () => new Destruccion(['Version' => '1.0']),
            'destruccion:certificadodedestruccion',
            'destruccion',
            'http://www.sat.gob.mx/certificadodestruccion',
            'http://www.sat.gob.mx/sitio_internet/cfd/certificadodestruccion/certificadodedestruccion.xsd',
        ],
        [
            'ConsumoDeCombustibles',
            fn () => new ConsumoDeCombustibles(['Version' => '1.1']),
            'consumodecombustibles11:ConsumoDeCombustibles',
            'consumodecombustibles11',
            'http://www.sat.gob.mx/ConsumoDeCombustibles11',
            'http://www.sat.gob.mx/sitio_internet/cfd/ConsumoDeCombustibles/consumodeCombustibles11.xsd',
        ],
        [
            'RegistroFiscal',
            fn () => new RegistroFiscal([]),
            'registrofiscal:CFDIRegistroFiscal',
            'registrofiscal',
            'http://www.sat.gob.mx/registrofiscal',
            'http://www.sat.gob.mx/sitio_internet/cfd/cfdiregistrofiscal/cfdiregistrofiscal.xsd',
        ],
    ];

    foreach ($cases as [$label, $factory, $key, $xmlnskey, $xmlns, $xsd]) {
        test("{$label} instancia Complemento y getComplement()", function () use ($factory, $key, $xmlnskey, $xmlns, $xsd) {
            $c = $factory();
            expect($c)->toBeInstanceOf(Complemento::class);
            $r = $c->getComplement();
            expect($r)->toHaveKeys(['complement', 'key', 'schemaLocation', 'xmlns', 'xmlnskey']);
            expect($r['key'])->toBe($key);
            expect($r['xmlnskey'])->toBe($xmlnskey);
            expect($r['xmlns'])->toBe($xmlns);
            expect($r['schemaLocation'])->toBe([$xmlns, $xsd]);
        });
    }
});
