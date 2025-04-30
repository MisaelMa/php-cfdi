<?php

use Sat\Cfdi;
use Sat\Cfdi\Emisor;
use Sat\Cfdi\Receptor;
use Sat\Cfdi\Concepto;
use Sat\Cfdi\Impuestos;

test('cfdi', function () {
    $cfdi = new CFDI();

    $emisor = new Emisor([
        'Rfc' => 'EKU9003173C9',
        'Nombre' => 'ESCUELA KEMPER URGATE',
        'RegimenFiscal' => '601',
    ]);

    $receptor = new Receptor([
        'Rfc' => "URE180429TM6",
        'Nombre' => "UNIVERSIDAD ROBOTICA ESPANOLA",
        'DomicilioFiscalReceptor' => "86991",
        'RegimenFiscalReceptor' => "601",
        'UsoCFDI' => "G01"
    ]);

    $cfdi->informacionGlobal([
        'Periodicidad' => "01",
        'Meses' => "01",
        'Año' => "2023"
    ]);

    $cfdi->emisor($emisor);

    $cfdi->receptor($receptor);

    $conceptos = $conceptos = [
        [
            'ClaveProdServ'     => '01010101',
            'NoIdentificacion'  => 'UT421511',
            'Cantidad'          => 1,
            'ClaveUnidad'       => 'ACT',
            'Descripcion'       => 'Venta',
            'ValorUnitario'     => 130,
            'Importe'           => 130,
            'Descuento'         => 0,
            'ObjetoImp'         => '02',
            'Impuestos' => [
                'Traslados' => [
                    [
                        'Base'        => 130,
                        'Impuesto'    => '002',
                        'TipoFactor'  => 'Tasa',
                        'TasaOCuota'  => '0.160000',
                        'Importe'     => 20.80,
                    ],
                ],
            ],
        ],
        [
            'ClaveProdServ'     => '01010101',
            'NoIdentificacion'  => 'UT421512',
            'Cantidad'          => 1,
            'ClaveUnidad'       => 'ACT',
            'Descripcion'       => 'Venta',
            'ValorUnitario'     => 359.98,
            'Importe'           => 359.98,
            'Descuento'         => 0,
            'ObjetoImp'         => '02',
            'Impuestos' => [
                'Traslados' => [
                    [
                        'Base'        => 359.98,
                        'Impuesto'    => '002',
                        'TipoFactor'  => 'Tasa',
                        'TasaOCuota'  => '0.160000',
                        'Importe'     => 57.60,
                    ],
                ],
            ],
        ],
        [
            'ClaveProdServ'     => '01010101',
            'NoIdentificacion'  => 'UT421513',
            'Cantidad'          => 1,
            'ClaveUnidad'       => 'ACT',
            'Descripcion'       => 'Venta',
            'ValorUnitario'     => 355.00,
            'Importe'           => 355.00,
            'Descuento'         => 0,
            'ObjetoImp'         => '02',
            'Impuestos' => [
                'Traslados' => [
                    [
                        'Base'        => 355.00,
                        'Impuesto'    => '002',
                        'TipoFactor'  => 'Tasa',
                        'TasaOCuota'  => '0.160000',
                        'Importe'     => 56.80,
                    ],
                ],
            ],
        ],
    ];
    foreach ($conceptos as $item) {
        $impuestos = $item['Impuestos'];
        unset($item['Impuestos']);
        $concepto = new Concepto($item);
        $concepto->traslado($impuestos['Traslados'][0]);
        $cfdi->concepto($concepto);
    }

    $impuestos = new Impuestos([
        'TotalImpuestosTrasladados' => '135.20'
    ]);
    $impuestos->traslados([
        'Base' => "844.98",
        'Impuesto' => "002",
        'TipoFactor' => "Tasa",
        'TasaOCuota' => "0.160000",
        'Importe' => "135.20"
    ]);
    $cfdi->impuesto($impuestos);
    $xml = $cfdi->getXmlCdfi();

    $expected_xml = <<<XML
<?xml version="1.0"?>
<cfdi:Comprobante xsi:schemaLocation="" Version="4.0">
  <cfdi:InformacionGlobal Periodicidad="01" Meses="01" Año="2023"/>
  <cfdi:Emisor Rfc="EKU9003173C9" Nombre="ESCUELA KEMPER URGATE" RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="URE180429TM6" Nombre="UNIVERSIDAD ROBOTICA ESPANOLA" DomicilioFiscalReceptor="86991" RegimenFiscalReceptor="601" UsoCFDI="G01"/>
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ="01010101" NoIdentificacion="UT421511" Cantidad="1" ClaveUnidad="ACT" Descripcion="Venta" ValorUnitario="130" Importe="130" Descuento="0" ObjetoImp="02">
      <cfdi:Impuestos>
        <cfdi:Traslados>
          <cfdi:Traslado Base="130" Impuesto="002" TipoFactor="Tasa" TasaOCuota="0.160000" Importe="20.8"/>
        </cfdi:Traslados>
      </cfdi:Impuestos>
    </cfdi:Concepto>
    <cfdi:Concepto ClaveProdServ="01010101" NoIdentificacion="UT421512" Cantidad="1" ClaveUnidad="ACT" Descripcion="Venta" ValorUnitario="359.98" Importe="359.98" Descuento="0" ObjetoImp="02">
      <cfdi:Impuestos>
        <cfdi:Traslados>
          <cfdi:Traslado Base="359.98" Impuesto="002" TipoFactor="Tasa" TasaOCuota="0.160000" Importe="57.6"/>
        </cfdi:Traslados>
      </cfdi:Impuestos>
    </cfdi:Concepto>
    <cfdi:Concepto ClaveProdServ="01010101" NoIdentificacion="UT421513" Cantidad="1" ClaveUnidad="ACT" Descripcion="Venta" ValorUnitario="355" Importe="355" Descuento="0" ObjetoImp="02">
      <cfdi:Impuestos>
        <cfdi:Traslados>
          <cfdi:Traslado Base="355" Impuesto="002" TipoFactor="Tasa" TasaOCuota="0.160000" Importe="56.8"/>
        </cfdi:Traslados>
      </cfdi:Impuestos>
    </cfdi:Concepto>
  </cfdi:Conceptos>
  <cfdi:Impuestos TotalImpuestosTrasladados="135.20">
    <cfdi:Traslados>
      <cfdi:Traslado Base="844.98" Impuesto="002" TipoFactor="Tasa" TasaOCuota="0.160000" Importe="135.20"/>
    </cfdi:Traslados>
  </cfdi:Impuestos>
</cfdi:Comprobante>

XML;
    expect($xml)->toBe($expected_xml);
});
