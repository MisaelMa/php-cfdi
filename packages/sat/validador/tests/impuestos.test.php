<?php

use Cfdi\Validador\Validador;

$filesDir = dirname(__DIR__, 4) . '/../cfdi-node/packages/files/xml/examples';

$BASE_XML_40 = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
  Version="4.0" TipoDeComprobante="I"
  Fecha="2024-01-01T00:00:00" LugarExpedicion="06600"
  SubTotal="1000.00" Total="1160.00" Moneda="MXN"
  Exportacion="01" NoCertificado="" Sello="" Certificado="">
  <cfdi:Emisor Rfc="EKU9003173C9" Nombre="TEST" RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="URE180429TM6" Nombre="TEST" DomicilioFiscalReceptor="06600" RegimenFiscalReceptor="601" UsoCFDI="G03"/>
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ="84111506" Cantidad="1" ClaveUnidad="E48"
      Descripcion="Test" ValorUnitario="1000.00" Importe="1000.00" ObjetoImp="02">
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

function impuestosHasErrorCode(array $errors, string $code): bool
{
    foreach ($errors as $e) {
        if ($e->code === $code) return true;
    }
    return false;
}

describe('Reglas de impuestos - suma de traslados', function () use ($BASE_XML_40) {
    $validador = new Validador();

    test('acepta cuando TotalImpuestosTrasladados coincide con la suma', function () use ($validador, $BASE_XML_40) {
        $result = $validador->validate($BASE_XML_40);
        expect(impuestosHasErrorCode($result->errors, 'CFDI605'))->toBeFalse();
    });

    test('rechaza cuando TotalImpuestosTrasladados no coincide', function () use ($validador) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
  Version="4.0" TipoDeComprobante="I"
  Fecha="2024-01-01T00:00:00" LugarExpedicion="06600"
  SubTotal="1000.00" Total="1360.00" Moneda="MXN"
  Exportacion="01" NoCertificado="" Sello="" Certificado="">
  <cfdi:Emisor Rfc="EKU9003173C9" Nombre="TEST" RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="URE180429TM6" Nombre="TEST" DomicilioFiscalReceptor="06600" RegimenFiscalReceptor="601" UsoCFDI="G03"/>
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ="84111506" Cantidad="1" ClaveUnidad="E48"
      Descripcion="Test" ValorUnitario="1000.00" Importe="1000.00" ObjetoImp="02">
      <cfdi:Impuestos>
        <cfdi:Traslados>
          <cfdi:Traslado Base="1000.00" Importe="160.00" Impuesto="002" TasaOCuota="0.160000" TipoFactor="Tasa"/>
        </cfdi:Traslados>
      </cfdi:Impuestos>
    </cfdi:Concepto>
  </cfdi:Conceptos>
  <cfdi:Impuestos TotalImpuestosTrasladados="360.00">
    <cfdi:Traslados>
      <cfdi:Traslado Base="1000.00" Importe="360.00" Impuesto="002" TasaOCuota="0.160000" TipoFactor="Tasa"/>
    </cfdi:Traslados>
  </cfdi:Impuestos>
</cfdi:Comprobante>';
        $result = $validador->validate($xml);
        expect(impuestosHasErrorCode($result->errors, 'CFDI605'))->toBeTrue();
    });
});

describe('Reglas de impuestos - suma de retenciones', function () {
    $validador = new Validador();

    test('acepta cuando TotalImpuestosRetenidos coincide con la suma', function () use ($validador) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
  Version="4.0" TipoDeComprobante="I"
  Fecha="2024-08-01T12:00:00" LugarExpedicion="06600"
  SubTotal="25000.00" Total="26333.33" Moneda="MXN"
  Exportacion="01" MetodoPago="PPD" FormaPago="99" NoCertificado="" Sello="" Certificado="">
  <cfdi:Emisor Rfc="CACX7605101P8" Nombre="TEST" RegimenFiscal="612"/>
  <cfdi:Receptor Rfc="EKU9003173C9" Nombre="TEST" DomicilioFiscalReceptor="26015" RegimenFiscalReceptor="601" UsoCFDI="G03"/>
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ="80101500" Cantidad="1" ClaveUnidad="E48"
      Descripcion="Honorarios" ValorUnitario="25000.00" Importe="25000.00" ObjetoImp="02">
      <cfdi:Impuestos>
        <cfdi:Traslados>
          <cfdi:Traslado Base="25000.00" Importe="4000.00" Impuesto="002" TasaOCuota="0.160000" TipoFactor="Tasa"/>
        </cfdi:Traslados>
        <cfdi:Retenciones>
          <cfdi:Retencion Base="25000.00" Importe="2500.00" Impuesto="001" TasaOCuota="0.100000" TipoFactor="Tasa"/>
          <cfdi:Retencion Base="25000.00" Importe="166.67" Impuesto="002" TasaOCuota="0.006667" TipoFactor="Tasa"/>
        </cfdi:Retenciones>
      </cfdi:Impuestos>
    </cfdi:Concepto>
  </cfdi:Conceptos>
  <cfdi:Impuestos TotalImpuestosTrasladados="4000.00" TotalImpuestosRetenidos="2666.67">
    <cfdi:Retenciones>
      <cfdi:Retencion Importe="2500.00" Impuesto="001"/>
      <cfdi:Retencion Importe="166.67" Impuesto="002"/>
    </cfdi:Retenciones>
    <cfdi:Traslados>
      <cfdi:Traslado Base="25000.00" Importe="4000.00" Impuesto="002" TasaOCuota="0.160000" TipoFactor="Tasa"/>
    </cfdi:Traslados>
  </cfdi:Impuestos>
</cfdi:Comprobante>';
        $result = $validador->validate($xml);
        expect(impuestosHasErrorCode($result->errors, 'CFDI606'))->toBeFalse();
        expect(impuestosHasErrorCode($result->errors, 'CFDI605'))->toBeFalse();
    });

    test('rechaza cuando TotalImpuestosRetenidos no coincide', function () use ($validador) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
  Version="4.0" TipoDeComprobante="I"
  Fecha="2024-01-01T00:00:00" LugarExpedicion="06600"
  SubTotal="1000.00" Total="1060.00" Moneda="MXN"
  Exportacion="01" NoCertificado="" Sello="" Certificado="">
  <cfdi:Emisor Rfc="EKU9003173C9" Nombre="TEST" RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="URE180429TM6" Nombre="TEST" DomicilioFiscalReceptor="06600" RegimenFiscalReceptor="601" UsoCFDI="G03"/>
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ="84111506" Cantidad="1" ClaveUnidad="E48"
      Descripcion="Test" ValorUnitario="1000.00" Importe="1000.00" ObjetoImp="02">
      <cfdi:Impuestos>
        <cfdi:Traslados>
          <cfdi:Traslado Base="1000.00" Importe="160.00" Impuesto="002" TasaOCuota="0.160000" TipoFactor="Tasa"/>
        </cfdi:Traslados>
        <cfdi:Retenciones>
          <cfdi:Retencion Base="1000.00" Importe="100.00" Impuesto="001" TasaOCuota="0.100000" TipoFactor="Tasa"/>
        </cfdi:Retenciones>
      </cfdi:Impuestos>
    </cfdi:Concepto>
  </cfdi:Conceptos>
  <cfdi:Impuestos TotalImpuestosTrasladados="160.00" TotalImpuestosRetenidos="999.00">
    <cfdi:Retenciones>
      <cfdi:Retencion Importe="100.00" Impuesto="001"/>
    </cfdi:Retenciones>
    <cfdi:Traslados>
      <cfdi:Traslado Base="1000.00" Importe="160.00" Impuesto="002" TasaOCuota="0.160000" TipoFactor="Tasa"/>
    </cfdi:Traslados>
  </cfdi:Impuestos>
</cfdi:Comprobante>';
        $result = $validador->validate($xml);
        expect(impuestosHasErrorCode($result->errors, 'CFDI606'))->toBeTrue();
    });
});

describe('Reglas de impuestos - impuesto invalido', function () {
    $validador = new Validador();

    test('rechaza impuesto con codigo no valido', function () use ($validador) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
  Version="4.0" TipoDeComprobante="I"
  Fecha="2024-01-01T00:00:00" LugarExpedicion="06600"
  SubTotal="1000.00" Total="1160.00" Moneda="MXN"
  Exportacion="01" NoCertificado="" Sello="" Certificado="">
  <cfdi:Emisor Rfc="EKU9003173C9" Nombre="TEST" RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="URE180429TM6" Nombre="TEST" DomicilioFiscalReceptor="06600" RegimenFiscalReceptor="601" UsoCFDI="G03"/>
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ="84111506" Cantidad="1" ClaveUnidad="E48"
      Descripcion="Test" ValorUnitario="1000.00" Importe="1000.00" ObjetoImp="02">
      <cfdi:Impuestos>
        <cfdi:Traslados>
          <cfdi:Traslado Base="1000.00" Importe="160.00" Impuesto="999" TasaOCuota="0.160000" TipoFactor="Tasa"/>
        </cfdi:Traslados>
      </cfdi:Impuestos>
    </cfdi:Concepto>
  </cfdi:Conceptos>
  <cfdi:Impuestos TotalImpuestosTrasladados="160.00">
    <cfdi:Traslados>
      <cfdi:Traslado Base="1000.00" Importe="160.00" Impuesto="999" TasaOCuota="0.160000" TipoFactor="Tasa"/>
    </cfdi:Traslados>
  </cfdi:Impuestos>
</cfdi:Comprobante>';
        $result = $validador->validate($xml);
        expect(impuestosHasErrorCode($result->errors, 'CFDI601'))->toBeTrue();
    });
});

describe('Reglas de impuestos - TipoFactor Exento', function () use ($filesDir) {
    $validador = new Validador();

    test('rechaza TasaOCuota cuando TipoFactor=Exento', function () use ($validador) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
  Version="4.0" TipoDeComprobante="I"
  Fecha="2024-01-01T00:00:00" LugarExpedicion="06600"
  SubTotal="1000.00" Total="1000.00" Moneda="MXN"
  Exportacion="01" NoCertificado="" Sello="" Certificado="">
  <cfdi:Emisor Rfc="EKU9003173C9" Nombre="TEST" RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="URE180429TM6" Nombre="TEST" DomicilioFiscalReceptor="06600" RegimenFiscalReceptor="601" UsoCFDI="G03"/>
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ="86121700" Cantidad="1" ClaveUnidad="E48"
      Descripcion="Test exento" ValorUnitario="1000.00" Importe="1000.00" ObjetoImp="02">
      <cfdi:Impuestos>
        <cfdi:Traslados>
          <cfdi:Traslado Base="1000.00" Impuesto="002" TipoFactor="Exento" TasaOCuota="0.160000"/>
        </cfdi:Traslados>
      </cfdi:Impuestos>
    </cfdi:Concepto>
  </cfdi:Conceptos>
  <cfdi:Impuestos>
    <cfdi:Traslados>
      <cfdi:Traslado Base="1000.00" Impuesto="002" TipoFactor="Exento" TasaOCuota="0.160000"/>
    </cfdi:Traslados>
  </cfdi:Impuestos>
</cfdi:Comprobante>';
        $result = $validador->validate($xml);
        expect(impuestosHasErrorCode($result->errors, 'CFDI603'))->toBeTrue();
    });

    test('acepta traslado Exento sin TasaOCuota ni Importe', function () use ($validador, $filesDir) {
        $result = $validador->validateFile("{$filesDir}/test-cfdi40/ingreso-exento.xml");
        expect(impuestosHasErrorCode($result->errors, 'CFDI603'))->toBeFalse();
        expect(impuestosHasErrorCode($result->errors, 'CFDI604'))->toBeFalse();
    });
});

describe('Reglas de impuestos - TipoFactor invalido', function () {
    $validador = new Validador();

    test('rechaza TipoFactor desconocido', function () use ($validador) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
  Version="4.0" TipoDeComprobante="I"
  Fecha="2024-01-01T00:00:00" LugarExpedicion="06600"
  SubTotal="1000.00" Total="1160.00" Moneda="MXN"
  Exportacion="01" NoCertificado="" Sello="" Certificado="">
  <cfdi:Emisor Rfc="EKU9003173C9" Nombre="TEST" RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="URE180429TM6" Nombre="TEST" DomicilioFiscalReceptor="06600" RegimenFiscalReceptor="601" UsoCFDI="G03"/>
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ="84111506" Cantidad="1" ClaveUnidad="E48"
      Descripcion="Test" ValorUnitario="1000.00" Importe="1000.00" ObjetoImp="02">
      <cfdi:Impuestos>
        <cfdi:Traslados>
          <cfdi:Traslado Base="1000.00" Importe="160.00" Impuesto="002" TasaOCuota="0.160000" TipoFactor="Porcentaje"/>
        </cfdi:Traslados>
      </cfdi:Impuestos>
    </cfdi:Concepto>
  </cfdi:Conceptos>
  <cfdi:Impuestos TotalImpuestosTrasladados="160.00">
    <cfdi:Traslados>
      <cfdi:Traslado Base="1000.00" Importe="160.00" Impuesto="002" TasaOCuota="0.160000" TipoFactor="Porcentaje"/>
    </cfdi:Traslados>
  </cfdi:Impuestos>
</cfdi:Comprobante>';
        $result = $validador->validate($xml);
        expect(impuestosHasErrorCode($result->errors, 'CFDI602'))->toBeTrue();
    });
});

describe('Reglas de estructura - campos faltantes', function () {
    $validador = new Validador();

    test('rechaza CFDI 4.0 sin DomicilioFiscalReceptor', function () use ($validador) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
  Version="4.0" TipoDeComprobante="I"
  Fecha="2024-01-01T00:00:00" LugarExpedicion="06600"
  SubTotal="0" Total="0" Moneda="MXN"
  Exportacion="01" NoCertificado="" Sello="" Certificado="">
  <cfdi:Emisor Rfc="EKU9003173C9" Nombre="TEST" RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="URE180429TM6" Nombre="TEST" UsoCFDI="G03"/>
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ="01010101" Cantidad="1" ClaveUnidad="E48" Descripcion="Test" ValorUnitario="0" Importe="0" ObjetoImp="01"/>
  </cfdi:Conceptos>
</cfdi:Comprobante>';
        $result = $validador->validate($xml);
        expect(impuestosHasErrorCode($result->errors, 'CFDI404'))->toBeTrue();
    });

    test('rechaza CFDI 4.0 sin ObjetoImp en concepto', function () use ($validador) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
  Version="4.0" TipoDeComprobante="I"
  Fecha="2024-01-01T00:00:00" LugarExpedicion="06600"
  SubTotal="0" Total="0" Moneda="MXN"
  Exportacion="01" NoCertificado="" Sello="" Certificado="">
  <cfdi:Emisor Rfc="EKU9003173C9" Nombre="TEST" RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="URE180429TM6" Nombre="TEST" DomicilioFiscalReceptor="06600" RegimenFiscalReceptor="601" UsoCFDI="G03"/>
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ="01010101" Cantidad="1" ClaveUnidad="E48" Descripcion="Test" ValorUnitario="0" Importe="0"/>
  </cfdi:Conceptos>
</cfdi:Comprobante>';
        $result = $validador->validate($xml);
        expect(impuestosHasErrorCode($result->errors, 'CFDI508'))->toBeTrue();
    });

    test('rechaza CFDI con RFC del emisor invalido', function () use ($validador) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
  Version="4.0" TipoDeComprobante="I"
  Fecha="2024-01-01T00:00:00" LugarExpedicion="06600"
  SubTotal="0" Total="0" Moneda="MXN"
  Exportacion="01" NoCertificado="" Sello="" Certificado="">
  <cfdi:Emisor Rfc="INVALIDO" Nombre="TEST" RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="URE180429TM6" Nombre="TEST" DomicilioFiscalReceptor="06600" RegimenFiscalReceptor="601" UsoCFDI="G03"/>
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ="01010101" Cantidad="1" ClaveUnidad="E48" Descripcion="Test" ValorUnitario="0" Importe="0" ObjetoImp="01"/>
  </cfdi:Conceptos>
</cfdi:Comprobante>';
        $result = $validador->validate($xml);
        expect(impuestosHasErrorCode($result->errors, 'CFDI302'))->toBeTrue();
    });
});
