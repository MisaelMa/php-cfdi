<?php

use Cfdi\Cleaner\Cleaners;
use Cfdi\Cleaner\CfdiCleaner;

$EXAMPLES_DIR = dirname(__DIR__, 4) . '/../cfdi-node/packages/files/xml/examples/cfdi40';

$XML_CLEAN = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd" Version="4.0" Sello="ABC123" NoCertificado="00000000000000000001" Certificado="CERT">
  <cfdi:Emisor Rfc="EKU9003173C9" Nombre="EMISOR TEST" RegimenFiscal="601"/>
  <cfdi:Receptor Rfc="URE180429TM6" Nombre="RECEPTOR TEST" DomicilioFiscalReceptor="65000" RegimenFiscalReceptor="601" UsoCFDI="G01"/>
  <cfdi:Conceptos>
    <cfdi:Concepto ClaveProdServ="01010101" Cantidad="1" ClaveUnidad="H87" Descripcion="Servicio" ValorUnitario="100.00" Importe="100.00" ObjetoImp="01"/>
  </cfdi:Conceptos>
  <cfdi:Complemento>
    <tfd:TimbreFiscalDigital xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital" xsi:schemaLocation="http://www.sat.gob.mx/TimbreFiscalDigital http://www.sat.gob.mx/sitio_internet/cfd/TimbreFiscalDigital/TimbreFiscalDigitalv11.xsd" Version="1.1" UUID="A1B2C3D4-E5F6-7890-ABCD-EF1234567890" FechaTimbrado="2024-01-01T00:00:00" RfcProvCertif="SAT970701NN3" SelloCFD="ABC" NoCertificadoSAT="00001000000504465028" SelloSAT="XYZ"/>
  </cfdi:Complemento>
</cfdi:Comprobante>';

$XML_WITH_ADDENDA = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Version="4.0" Sello="ABC123">
  <cfdi:Emisor Rfc="EKU9003173C9" Nombre="EMISOR TEST" RegimenFiscal="601"/>
  <cfdi:Complemento>
    <tfd:TimbreFiscalDigital xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital" Version="1.1" UUID="A1B2C3D4-0000-0000-0000-000000000001" FechaTimbrado="2024-01-01T00:00:00" RfcProvCertif="SAT970701NN3" SelloCFD="ABC" NoCertificadoSAT="00001000000504465028" SelloSAT="XYZ"/>
  </cfdi:Complemento>
  <cfdi:Addenda>
    <vendor:DatosProveedor xmlns:vendor="http://www.proveedor.com/schema">
      <vendor:PedidoInterno>PO-2024-001</vendor:PedidoInterno>
      <vendor:CentroCostos>CC-100</vendor:CentroCostos>
    </vendor:DatosProveedor>
  </cfdi:Addenda>
</cfdi:Comprobante>';

$XML_WITH_ADDENDA_MULTILINE = '<?xml version="1.0"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4" Version="4.0" Sello="SEL">
  <cfdi:Complemento>
    <tfd:TimbreFiscalDigital xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital" Version="1.1" UUID="UUID-001" RfcProvCertif="SAT970701NN3" SelloCFD="S" SelloSAT="T"/>
  </cfdi:Complemento>
  <cfdi:Addenda>
    <extra:Info xmlns:extra="http://extra.com">
      <extra:Linea1>dato 1</extra:Linea1>
      <extra:Linea2>dato 2</extra:Linea2>
    </extra:Info>
  </cfdi:Addenda>
</cfdi:Comprobante>';

$XML_WITH_NON_SAT_NS = '<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:vendor="http://www.proveedor.com/ns" xmlns:erp="http://erp.empresa.com/v1" Version="4.0" Sello="ABC">
  <cfdi:Emisor Rfc="EKU9003173C9" Nombre="TEST" RegimenFiscal="601"/>
</cfdi:Comprobante>';

$XML_WITH_NON_SAT_SCHEMA = '<?xml version="1.0"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:vendor="http://proveedor.com/ns" xsi:schemaLocation="http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd http://proveedor.com/ns http://proveedor.com/schema/vendor.xsd" Version="4.0" Sello="ABC">
  <cfdi:Emisor Rfc="EKU9003173C9" Nombre="TEST" RegimenFiscal="601"/>
</cfdi:Comprobante>';

$XML_WITH_NON_SAT_COMPLEMENT_NODE = '<?xml version="1.0"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital" xmlns:vendor="http://www.proveedor.com/complemento" Version="4.0" Sello="ABC">
  <cfdi:Complemento>
    <tfd:TimbreFiscalDigital Version="1.1" UUID="UUID-002" RfcProvCertif="SAT970701NN3" SelloCFD="S" SelloSAT="T"/>
    <vendor:DatosExtra Foo="bar"/>
  </cfdi:Complemento>
</cfdi:Comprobante>';

$XML_WITH_STYLESHEET = '<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="cfdi.xsl"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4" Version="4.0" Sello="ABC">
  <cfdi:Emisor Rfc="EKU9003173C9" Nombre="TEST" RegimenFiscal="601"/>
</cfdi:Comprobante>';

// ---------------------------------------------------------------------------
// Tests unitarios por cleaner
// ---------------------------------------------------------------------------

describe('removeAddenda()', function () use ($XML_WITH_ADDENDA, $XML_WITH_ADDENDA_MULTILINE, $XML_CLEAN) {

    test('elimina bloque cfdi:Addenda completo', function () use ($XML_WITH_ADDENDA) {
        $result = Cleaners::removeAddenda($XML_WITH_ADDENDA);
        expect($result)->not->toContain('<cfdi:Addenda');
        expect($result)->not->toContain('PedidoInterno');
        expect($result)->not->toContain('</cfdi:Addenda>');
    });

    test('elimina addenda multilinea con contenido anidado', function () use ($XML_WITH_ADDENDA_MULTILINE) {
        $result = Cleaners::removeAddenda($XML_WITH_ADDENDA_MULTILINE);
        expect($result)->not->toContain('cfdi:Addenda');
        expect($result)->not->toContain('extra:Info');
        expect($result)->not->toContain('dato 1');
    });

    test('preserva el Sello del Comprobante', function () use ($XML_WITH_ADDENDA) {
        $result = Cleaners::removeAddenda($XML_WITH_ADDENDA);
        expect($result)->toContain('Sello="ABC123"');
    });

    test('preserva el UUID del TimbreFiscalDigital', function () use ($XML_WITH_ADDENDA) {
        $result = Cleaners::removeAddenda($XML_WITH_ADDENDA);
        expect($result)->toContain('UUID="A1B2C3D4-0000-0000-0000-000000000001"');
    });

    test('no modifica un XML sin addenda', function () use ($XML_CLEAN) {
        $result = Cleaners::removeAddenda($XML_CLEAN);
        expect($result)->toBe($XML_CLEAN);
    });
});

describe('removeNonSatNamespaces()', function () use ($XML_WITH_NON_SAT_NS, $XML_CLEAN) {

    test('elimina declaraciones xmlns no-SAT del root', function () use ($XML_WITH_NON_SAT_NS) {
        $result = Cleaners::removeNonSatNamespaces($XML_WITH_NON_SAT_NS);
        expect($result)->not->toContain('xmlns:vendor="http://www.proveedor.com/ns"');
        expect($result)->not->toContain('xmlns:erp="http://erp.empresa.com/v1"');
    });

    test('preserva namespaces oficiales del SAT', function () use ($XML_WITH_NON_SAT_NS) {
        $result = Cleaners::removeNonSatNamespaces($XML_WITH_NON_SAT_NS);
        expect($result)->toContain('xmlns:cfdi="http://www.sat.gob.mx/cfd/4"');
        expect($result)->toContain('xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"');
    });

    test('no modifica un XML sin namespaces no-SAT', function () use ($XML_CLEAN) {
        $result = Cleaners::removeNonSatNamespaces($XML_CLEAN);
        expect($result)->toBe($XML_CLEAN);
    });

    test('preserva el namespace tfd en el Complemento declarado en nodo hijo', function () use ($XML_WITH_NON_SAT_NS) {
        $result = Cleaners::removeNonSatNamespaces($XML_WITH_NON_SAT_NS);
        expect($result)->toContain('xmlns:cfdi="http://www.sat.gob.mx/cfd/4"');
    });
});

describe('removeNonSatSchemaLocations()', function () use ($XML_WITH_NON_SAT_SCHEMA, $XML_CLEAN) {

    test('elimina pares URI+XSD no-SAT del schemaLocation', function () use ($XML_WITH_NON_SAT_SCHEMA) {
        $result = Cleaners::removeNonSatSchemaLocations($XML_WITH_NON_SAT_SCHEMA);
        expect($result)->not->toContain('http://proveedor.com/schema/vendor.xsd');
        preg_match('/xsi:schemaLocation="([^"]*)"/', $result, $schemaMatch);
        expect($schemaMatch)->not->toBeEmpty();
        expect($schemaMatch[1])->not->toContain('http://proveedor.com/ns');
    });

    test('preserva pares URI+XSD del SAT en schemaLocation', function () use ($XML_WITH_NON_SAT_SCHEMA) {
        $result = Cleaners::removeNonSatSchemaLocations($XML_WITH_NON_SAT_SCHEMA);
        expect($result)->toContain('http://www.sat.gob.mx/cfd/4');
        expect($result)->toContain('http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd');
    });

    test('mantiene el atributo xsi:schemaLocation aunque quede vacio de SAT', function () {
        $xml = '<root xsi:schemaLocation="http://no-sat.com/ns http://no-sat.com/schema.xsd"/>';
        $result = Cleaners::removeNonSatSchemaLocations($xml);
        expect($result)->toContain('xsi:schemaLocation=""');
    });

    test('no modifica un XML sin schemaLocation de terceros', function () use ($XML_CLEAN) {
        $result = Cleaners::removeNonSatSchemaLocations($XML_CLEAN);
        expect($result)->toBe($XML_CLEAN);
    });
});

describe('removeNonSatNodes()', function () use ($XML_WITH_NON_SAT_COMPLEMENT_NODE) {

    test('elimina nodos con namespace no-SAT dentro de cfdi:Complemento', function () use ($XML_WITH_NON_SAT_COMPLEMENT_NODE) {
        $result = Cleaners::removeNonSatNodes($XML_WITH_NON_SAT_COMPLEMENT_NODE);
        expect($result)->not->toContain('vendor:DatosExtra');
        expect($result)->not->toContain('Foo="bar"');
    });

    test('preserva TimbreFiscalDigital namespace SAT en Complemento', function () use ($XML_WITH_NON_SAT_COMPLEMENT_NODE) {
        $result = Cleaners::removeNonSatNodes($XML_WITH_NON_SAT_COMPLEMENT_NODE);
        expect($result)->toContain('tfd:TimbreFiscalDigital');
        expect($result)->toContain('UUID="UUID-002"');
    });

    test('no modifica nodos fuera de cfdi:Complemento', function () use ($XML_WITH_NON_SAT_COMPLEMENT_NODE) {
        $result = Cleaners::removeNonSatNodes($XML_WITH_NON_SAT_COMPLEMENT_NODE);
        expect($result)->toContain('<cfdi:Comprobante');
    });

    test('no modifica un XML sin Complemento', function () {
        $xmlSinComplemento = '<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4" Version="4.0" Sello="S"/>';
        $result = Cleaners::removeNonSatNodes($xmlSinComplemento);
        expect($result)->toBe($xmlSinComplemento);
    });
});

describe('removeStylesheetAttributes()', function () use ($XML_WITH_STYLESHEET, $XML_CLEAN) {

    test('elimina processing instruction xml-stylesheet', function () use ($XML_WITH_STYLESHEET) {
        $result = Cleaners::removeStylesheetAttributes($XML_WITH_STYLESHEET);
        expect($result)->not->toContain('<?xml-stylesheet');
        expect($result)->not->toContain('cfdi.xsl');
    });

    test('preserva la declaracion xml normal', function () use ($XML_WITH_STYLESHEET) {
        $result = Cleaners::removeStylesheetAttributes($XML_WITH_STYLESHEET);
        expect($result)->toContain('<?xml version="1.0"');
    });

    test('preserva el contenido del Comprobante', function () use ($XML_WITH_STYLESHEET) {
        $result = Cleaners::removeStylesheetAttributes($XML_WITH_STYLESHEET);
        expect($result)->toContain('<cfdi:Comprobante');
        expect($result)->toContain('Sello="ABC"');
    });

    test('no modifica un XML sin stylesheet PI', function () use ($XML_CLEAN) {
        $result = Cleaners::removeStylesheetAttributes($XML_CLEAN);
        expect($result)->toBe($XML_CLEAN);
    });

    test('elimina multiples PI xml-stylesheet', function () {
        $xml = '<?xml version="1.0"?><?xml-stylesheet type="text/xsl" href="a.xsl"?><?xml-stylesheet type="text/css" href="b.css"?><root/>';
        $result = Cleaners::removeStylesheetAttributes($xml);
        expect($result)->not->toContain('<?xml-stylesheet');
        expect($result)->toContain('<root/>');
    });
});

describe('collapseWhitespace()', function () {

    test('colapsa multiples saltos de linea entre tags a uno solo', function () {
        $xml = "<root>\n\n\n  <child/>\n\n</root>";
        $result = Cleaners::collapseWhitespace($xml);
        expect($result)->not->toMatch('/>\s{2,}</');
    });

    test('no altera el contenido de texto dentro de nodos', function () {
        $xml = '<root><name>Juan  Garcia</name></root>';
        $result = Cleaners::collapseWhitespace($xml);
        expect($result)->toContain('Juan  Garcia');
    });

    test('elimina whitespace al inicio y final del documento', function () {
        $xml = "   <root/>\n   ";
        $result = Cleaners::collapseWhitespace($xml);
        expect($result)->toBe('<root/>');
    });
});

// ---------------------------------------------------------------------------
// Tests de integracion: CfdiCleaner
// ---------------------------------------------------------------------------

describe('CfdiCleaner', function () use (
    $EXAMPLES_DIR, $XML_WITH_ADDENDA, $XML_CLEAN,
    $XML_WITH_NON_SAT_COMPLEMENT_NODE, $XML_WITH_NON_SAT_NS,
    $XML_WITH_NON_SAT_SCHEMA, $XML_WITH_STYLESHEET
) {
    $cleaner = new CfdiCleaner();

    describe('clean()', function () use (
        $cleaner, $XML_WITH_ADDENDA, $XML_CLEAN,
        $XML_WITH_NON_SAT_COMPLEMENT_NODE, $XML_WITH_NON_SAT_NS,
        $XML_WITH_NON_SAT_SCHEMA, $XML_WITH_STYLESHEET
    ) {

        test('elimina addenda del XML sucio', function () use ($cleaner, $XML_WITH_ADDENDA) {
            $result = $cleaner->clean($XML_WITH_ADDENDA);
            expect($result)->not->toContain('cfdi:Addenda');
            expect($result)->not->toContain('PedidoInterno');
        });

        test('preserva Sello, Certificado y UUID tras limpieza', function () use ($cleaner) {
            $xmlConTodo = '<?xml version="1.0"?>
<?xml-stylesheet type="text/xsl" href="cfdi.xsl"?>
<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:vendor="http://tercero.com/ns" xsi:schemaLocation="http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd http://tercero.com/ns http://tercero.com/schema.xsd" Version="4.0" Sello="SELLO_ORIGINAL" NoCertificado="00000000000000000001" Certificado="CERT_ORIGINAL">
  <cfdi:Complemento>
    <tfd:TimbreFiscalDigital xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital" Version="1.1" UUID="UUID-REAL-123" RfcProvCertif="SAT970701NN3" SelloCFD="SELLO_CFD" SelloSAT="SELLO_SAT"/>
    <vendor:Extra xmlns:vendor="http://tercero.com/ns" Dato="quitar"/>
  </cfdi:Complemento>
  <cfdi:Addenda>
    <info>eliminar</info>
  </cfdi:Addenda>
</cfdi:Comprobante>';

            $result = $cleaner->clean($xmlConTodo);

            expect($result)->toContain('Sello="SELLO_ORIGINAL"');
            expect($result)->toContain('Certificado="CERT_ORIGINAL"');
            expect($result)->toContain('UUID="UUID-REAL-123"');
            expect($result)->toContain('SelloCFD="SELLO_CFD"');
            expect($result)->toContain('SelloSAT="SELLO_SAT"');

            expect($result)->not->toContain('<?xml-stylesheet');
            expect($result)->not->toContain('cfdi:Addenda');
            expect($result)->not->toContain('eliminar');
            expect($result)->not->toContain('xmlns:vendor="http://tercero.com/ns"');
            expect($result)->not->toContain('http://tercero.com/schema.xsd');
            expect($result)->not->toContain('vendor:Extra');
        });

        test('un XML limpio no cambia su contenido esencial tras clean', function () use ($cleaner, $XML_CLEAN) {
            $result = $cleaner->clean($XML_CLEAN);
            expect($result)->toContain('Sello="ABC123"');
            expect($result)->toContain('xmlns:cfdi="http://www.sat.gob.mx/cfd/4"');
            expect($result)->toContain('UUID="A1B2C3D4-E5F6-7890-ABCD-EF1234567890"');
            expect($result)->toContain('SelloCFD="ABC"');
            expect($result)->toContain('SelloSAT="XYZ"');
        });

        test('elimina nodo no-SAT en Complemento preservando tfd', function () use ($cleaner, $XML_WITH_NON_SAT_COMPLEMENT_NODE) {
            $result = $cleaner->clean($XML_WITH_NON_SAT_COMPLEMENT_NODE);
            expect($result)->not->toContain('vendor:DatosExtra');
            expect($result)->toContain('tfd:TimbreFiscalDigital');
        });

        test('elimina namespaces no-SAT del root', function () use ($cleaner, $XML_WITH_NON_SAT_NS) {
            $result = $cleaner->clean($XML_WITH_NON_SAT_NS);
            expect($result)->not->toContain('xmlns:vendor');
            expect($result)->not->toContain('xmlns:erp');
            expect($result)->toContain('xmlns:cfdi="http://www.sat.gob.mx/cfd/4"');
        });

        test('elimina schemaLocation de terceros', function () use ($cleaner, $XML_WITH_NON_SAT_SCHEMA) {
            $result = $cleaner->clean($XML_WITH_NON_SAT_SCHEMA);
            expect($result)->not->toContain('http://proveedor.com');
        });

        test('elimina stylesheet PI', function () use ($cleaner, $XML_WITH_STYLESHEET) {
            $result = $cleaner->clean($XML_WITH_STYLESHEET);
            expect($result)->not->toContain('<?xml-stylesheet');
        });
    });

    describe('cleanFile()', function () use ($cleaner, $EXAMPLES_DIR) {

        test('limpia archivo real de CFDI 4.0 cfdi-validator-cfdi40-real', function () use ($cleaner, $EXAMPLES_DIR) {
            $filePath = "{$EXAMPLES_DIR}/cfdi-validator-cfdi40-real.xml";
            if (!file_exists($filePath)) {
                $this->markTestSkipped("XML file not found: {$filePath}");
            }
            $result = $cleaner->cleanFile($filePath);
            expect($result)->toContain('cfdi:Comprobante');
            expect($result)->toContain('tfd:TimbreFiscalDigital');
            expect($result)->not->toContain('<?xml-stylesheet');
        });

        test('limpia archivo real CfdiUtils-cfdi40-real preservando UUID y sellos', function () use ($cleaner, $EXAMPLES_DIR) {
            $filePath = "{$EXAMPLES_DIR}/CfdiUtils-cfdi40-real.xml";
            if (!file_exists($filePath)) {
                $this->markTestSkipped("XML file not found: {$filePath}");
            }
            $result = $cleaner->cleanFile($filePath);
            expect($result)->toContain('UUID="C2832671-DA6D-11EF-A83D-00155D012007"');
            expect($result)->toContain('WZzQVFmM/0E21+v4Th3K9K3a8yfN8TPwkarBsD28YUb');
        });

        test('lanza error si el archivo no existe', function () use ($cleaner) {
            expect(fn () => $cleaner->cleanFile('/ruta/que/no/existe/factura.xml'))
                ->toThrow(\RuntimeException::class);
        });
    });
});
