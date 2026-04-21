<?php

use Cfdi\Schema\CfdiSchema;
use Cfdi\Schema\CfdiXsd;
use Cfdi\Schema\XsdLoader;
use Cfdi\Schema\XmlUtils;

afterEach(function () {
    XsdLoader::reset();
    CfdiXsd::reset();
});

test('XsdLoader parsea XSD minimo con Comprobante', function () {
    $dir = sys_get_temp_dir() . '/cfdi-schema-test-' . bin2hex(random_bytes(4));
    mkdir($dir, 0777, true);
    $xsd = <<<'XSD'
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">
  <xs:element name="Comprobante">
    <xs:complexType>
      <xs:sequence>
        <xs:element name="Emisor" type="xs:string"/>
        <xs:element name="Receptor" type="xs:string" minOccurs="0"/>
      </xs:sequence>
    </xs:complexType>
  </xs:element>
</xs:schema>
XSD;
    $path = $dir . '/test.xsd';
    file_put_contents($path, $xsd);

    $doc = XsdLoader::getInstance()->loadXsd($path);
    $seq = XmlUtils::comprobanteSequence($doc);
    expect($seq)->toHaveCount(2);
    expect($seq[0]['name'])->toBe('Emisor');
    expect($seq[0]['type'])->toBe('xs:string');
    expect($seq[1]['name'])->toBe('Receptor');
    expect($seq[1]['minOccurs'])->toBe('0');
});

test('CfdiXsd process devuelve jsonLike con properties', function () {
    $dir = sys_get_temp_dir() . '/cfdi-xsd-test-' . bin2hex(random_bytes(4));
    mkdir($dir, 0777, true);
    $path = $dir . '/cfdi.xsd';
    file_put_contents($path, <<<'XSD'
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
  <xs:element name="Comprobante">
    <xs:complexType>
      <xs:sequence>
        <xs:element name="Conceptos" type="xs:string"/>
      </xs:sequence>
    </xs:complexType>
  </xs:element>
</xs:schema>
XSD);

    $out = (new CfdiXsd())->setConfig(['source' => $path])->process();
    expect($out['jsonLike']['properties'])->toHaveKey('Conceptos');
    expect($out['comprobanteSequence'][0]['name'])->toBe('Conceptos');
});

test('CfdiSchema processAll combina cfdi y catalogos', function () {
    $dir = sys_get_temp_dir() . '/cfdi-full-' . bin2hex(random_bytes(4));
    mkdir($dir, 0777, true);
    $cfdi = $dir . '/main.xsd';
    $cat = $dir . '/cat.xsd';
    file_put_contents($cfdi, <<<'XSD'
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
  <xs:element name="Comprobante">
    <xs:complexType>
      <xs:sequence>
        <xs:element name="A" type="xs:string"/>
      </xs:sequence>
    </xs:complexType>
  </xs:element>
</xs:schema>
XSD);
    file_put_contents($cat, <<<'XSD'
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">
  <xs:element name="Comprobante">
    <xs:complexType>
      <xs:sequence>
        <xs:element name="B" type="xs:string"/>
      </xs:sequence>
    </xs:complexType>
  </xs:element>
</xs:schema>
XSD);

    $all = (new CfdiSchema())->setConfig(['cfdi' => $cfdi, 'catalogos' => $cat])->processAll();
    expect($all['cfdi']['comprobanteSequence'][0]['name'])->toBe('A');
    expect($all['catalogos']['comprobanteSequence'][0]['name'])->toBe('B');
});
