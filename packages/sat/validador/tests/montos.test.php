<?php

use Cfdi\Validador\Validador;

function buildXml(array $overrides = []): string
{
    $subtotal = $overrides['subtotal'] ?? '1000.00';
    $total = $overrides['total'] ?? '1160.00';
    $descuento = $overrides['descuento'] ?? null;
    $moneda = $overrides['moneda'] ?? 'MXN';
    $tipoCambioAttr = $overrides['tipoCambioAttr'] ?? '';
    $totalTrasladados = $overrides['totalTrasladados'] ?? '160.00';
    $totalRetenidos = $overrides['totalRetenidos'] ?? null;
    $tipoComprobante = $overrides['tipoComprobante'] ?? 'I';
    $version = $overrides['version'] ?? '4.0';
    $emisorNombre = $overrides['emisorNombre'] ?? 'Nombre="TEST"';
    $receptorExtra = $overrides['receptorExtra'] ?? 'DomicilioFiscalReceptor="06600" RegimenFiscalReceptor="601"';
    $impuestosAttr = $overrides['impuestosAttr'] ?? null;
    $impuestosContent = $overrides['impuestosContent'] ?? null;
    $conceptoImporte = $overrides['conceptoImporte'] ?? '1000.00';
    $conceptoValorUnitario = $overrides['conceptoValorUnitario'] ?? '1000.00';

    if ($impuestosAttr === null) {
        $parts = [];
        if ($totalTrasladados !== null) $parts[] = "TotalImpuestosTrasladados=\"{$totalTrasladados}\"";
        if ($totalRetenidos !== null) $parts[] = "TotalImpuestosRetenidos=\"{$totalRetenidos}\"";
        $impAttr = implode(' ', $parts);
    } else {
        $impAttr = $impuestosAttr;
    }

    $trasladoImporte = $totalTrasladados ?? '160.00';
    if ($impuestosContent === null) {
        $impContent = "<cfdi:Traslados>
      <cfdi:Traslado Base=\"{$conceptoImporte}\" Importe=\"{$trasladoImporte}\" Impuesto=\"002\" TasaOCuota=\"0.160000\" TipoFactor=\"Tasa\"/>
    </cfdi:Traslados>";
    } else {
        $impContent = $impuestosContent;
    }

    $descAttr = $descuento !== null ? "Descuento=\"{$descuento}\"" : '';
    $ns = $version === '4.0' ? 'http://www.sat.gob.mx/cfd/4' : 'http://www.sat.gob.mx/cfd/3';

    return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<cfdi:Comprobante xmlns:cfdi=\"{$ns}\"
  Version=\"{$version}\" TipoDeComprobante=\"{$tipoComprobante}\"
  Fecha=\"2024-01-01T00:00:00\" LugarExpedicion=\"06600\"
  SubTotal=\"{$subtotal}\" Total=\"{$total}\" Moneda=\"{$moneda}\"
  {$descAttr} {$tipoCambioAttr}
  Exportacion=\"01\" NoCertificado=\"\" Sello=\"\" Certificado=\"\">
  <cfdi:Emisor Rfc=\"EKU9003173C9\" {$emisorNombre} RegimenFiscal=\"601\"/>
  <cfdi:Receptor Rfc=\"URE180429TM6\" Nombre=\"TEST\" {$receptorExtra} UsoCFDI=\"G03\"/>
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ=\"84111506\" Cantidad=\"1\" ClaveUnidad=\"E48\"
      Descripcion=\"Test\" ValorUnitario=\"{$conceptoValorUnitario}\"
      Importe=\"{$conceptoImporte}\" ObjetoImp=\"02\">
      <cfdi:Impuestos>
        <cfdi:Traslados>
          <cfdi:Traslado Base=\"{$conceptoImporte}\" Importe=\"{$trasladoImporte}\" Impuesto=\"002\" TasaOCuota=\"0.160000\" TipoFactor=\"Tasa\"/>
        </cfdi:Traslados>
      </cfdi:Impuestos>
    </cfdi:Concepto>
  </cfdi:Conceptos>
  <cfdi:Impuestos {$impAttr}>
    {$impContent}
  </cfdi:Impuestos>
</cfdi:Comprobante>";
}

function hasErrorCode(array $errors, string $code): bool
{
    foreach ($errors as $e) {
        if ($e->code === $code) return true;
    }
    return false;
}

describe('Reglas de montos - SubTotal', function () {
    $validador = new Validador();

    test('acepta SubTotal valido', function () use ($validador) {
        $result = $validador->validate(buildXml());
        $subtotalErrors = array_filter($result->errors, fn($e) => str_contains($e->field ?? '', 'SubTotal'));
        expect($subtotalErrors)->toHaveCount(0);
    });

    test('rechaza SubTotal negativo', function () use ($validador) {
        $xml = buildXml(['subtotal' => '-100.00', 'total' => '-100.00']);
        $result = $validador->validate($xml);
        expect(hasErrorCode($result->errors, 'CFDI203'))->toBeTrue();
    });
});

describe('Reglas de montos - Total', function () {
    $validador = new Validador();

    test('rechaza Total negativo', function () use ($validador) {
        $xml = buildXml(['total' => '-500.00']);
        $result = $validador->validate($xml);
        expect(hasErrorCode($result->errors, 'CFDI205'))->toBeTrue();
    });

    test('rechaza cuando Total no coincide con la formula', function () use ($validador) {
        $xml = buildXml(['total' => '1000.00']);
        $result = $validador->validate($xml);
        expect(hasErrorCode($result->errors, 'CFDI208'))->toBeTrue();
    });

    test('acepta Total con tolerancia de 1 centavo', function () use ($validador) {
        $xml = buildXml(['total' => '1160.005']);
        $result = $validador->validate($xml);
        $totalErrors = array_filter($result->errors, fn($e) => $e->code === 'CFDI208');
        expect($totalErrors)->toHaveCount(0);
    });
});

describe('Reglas de montos - Descuento', function () {
    $validador = new Validador();

    test('acepta descuento menor al subtotal', function () use ($validador) {
        $xml = buildXml([
            'subtotal' => '1000.00',
            'total' => '1044.00',
            'descuento' => '100.00',
            'conceptoImporte' => '1000.00',
            'totalTrasladados' => '144.00',
        ]);
        $result = $validador->validate($xml);
        $descErrors = array_filter($result->errors, fn($e) => $e->code === 'CFDI207');
        expect($descErrors)->toHaveCount(0);
    });

    test('rechaza descuento mayor al subtotal', function () use ($validador) {
        $xml = buildXml([
            'subtotal' => '100.00',
            'total' => '116.00',
            'descuento' => '200.00',
        ]);
        $result = $validador->validate($xml);
        expect(hasErrorCode($result->errors, 'CFDI207'))->toBeTrue();
    });
});

describe('Reglas de montos - Moneda y TipoCambio', function () {
    $validador = new Validador();

    test('rechaza TipoCambio cuando Moneda=XXX', function () use ($validador) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
  Version="4.0" TipoDeComprobante="T"
  Fecha="2024-01-01T00:00:00" LugarExpedicion="06600"
  SubTotal="0" Total="0" Moneda="XXX" TipoCambio="17.00"
  Exportacion="01" NoCertificado="" Sello="" Certificado="">
  <cfdi:Emisor Rfc="EKU9003173C9" Nombre="TEST" RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="URE180429TM6" Nombre="TEST" DomicilioFiscalReceptor="06600" RegimenFiscalReceptor="601" UsoCFDI="S01"/>
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ="01010101" Cantidad="1" ClaveUnidad="E48" Descripcion="Test" ValorUnitario="0" Importe="0" ObjetoImp="01"/>
  </cfdi:Conceptos>
</cfdi:Comprobante>';
        $result = $validador->validate($xml);
        expect(hasErrorCode($result->errors, 'CFDI006'))->toBeTrue();
    });

    test('requiere TipoCambio cuando Moneda es distinta de MXN y XXX', function () use ($validador) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
  Version="4.0" TipoDeComprobante="I"
  Fecha="2024-01-01T00:00:00" LugarExpedicion="06600"
  SubTotal="1000.00" Total="1160.00" Moneda="USD"
  Exportacion="01" NoCertificado="" Sello="" Certificado="">
  <cfdi:Emisor Rfc="EKU9003173C9" Nombre="TEST" RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="URE180429TM6" Nombre="TEST" DomicilioFiscalReceptor="06600" RegimenFiscalReceptor="601" UsoCFDI="G03"/>
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ="84111506" Cantidad="1" ClaveUnidad="E48" Descripcion="Test" ValorUnitario="1000.00" Importe="1000.00" ObjetoImp="02">
      <cfdi:Impuestos>
        <cfdi:Traslados>
          <cfdi:Traslado Base="1000.00" Importe="160.00" Impuesto="002" TasaOCuota="0.160000" TipoFactor="Tasa"/>
        </cfdi:Traslados>
      </cfdi:Impuestos>
    </cfdi:Concepto>
  </cfdi:Conceptos>
  <cfdi:Impuestos TotalImpuestosTrasladados="160.00">
    <cfdi:Traslados>
      <cfdi:Traslado Base="1000.00" Importe="160.00" Impuesto="002" TasaOCuota="0.160000" TipoFactor="Tasa"/>
    </cfdi:Traslados>
  </cfdi:Impuestos>
</cfdi:Comprobante>';
        $result = $validador->validate($xml);
        expect(hasErrorCode($result->errors, 'CFDI007'))->toBeTrue();
    });

    test('acepta Moneda=MXN sin TipoCambio', function () use ($validador) {
        $result = $validador->validate(buildXml());
        expect(hasErrorCode($result->errors, 'CFDI006'))->toBeFalse();
        expect(hasErrorCode($result->errors, 'CFDI007'))->toBeFalse();
    });
});

describe('Reglas de montos - TipoDeComprobante Traslado', function () {
    $validador = new Validador();

    test('rechaza SubTotal != 0 en comprobante Traslado', function () use ($validador) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
  Version="4.0" TipoDeComprobante="T"
  Fecha="2024-01-01T00:00:00" LugarExpedicion="06600"
  SubTotal="500.00" Total="0" Moneda="XXX"
  Exportacion="01" NoCertificado="" Sello="" Certificado="">
  <cfdi:Emisor Rfc="EKU9003173C9" Nombre="TEST" RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="URE180429TM6" Nombre="TEST" DomicilioFiscalReceptor="06600" RegimenFiscalReceptor="601" UsoCFDI="S01"/>
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ="01010101" Cantidad="1" ClaveUnidad="E48" Descripcion="Test" ValorUnitario="0" Importe="0" ObjetoImp="01"/>
  </cfdi:Conceptos>
</cfdi:Comprobante>';
        $result = $validador->validate($xml);
        expect(hasErrorCode($result->errors, 'CFDI004'))->toBeTrue();
    });
});
