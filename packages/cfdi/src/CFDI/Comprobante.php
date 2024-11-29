<?php

namespace Sat\Cfdi;

class Comprobante
{

    protected $xml = [
        'rootElementName' => 'cfdi:Comprobante',
        '_attributes' => [
            'xsi:schemaLocation' => '',
            'Version' => '4.0',
        ],
    ];

    protected $version = '4.0';
    protected $XMLSchema = 'http://www.w3.org/2001/XMLSchema-instance';
    protected $cfd = 'http://www.sat.gob.mx/cfd/4';
    protected $locations = [
        'http://www.sat.gob.mx/cfd/4',
        'http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd',
    ];

    protected $schema = null;

    public function __construct($options = [])
    {
        $this->schema = $options['schema'] ?? null;
    }

    public function xmlns(): void {}

    public function addXmlns($xmlnsKey, $xmlnsValue): void
    {
        $this->xml['cfdi:Comprobante']['_attributes'][$xmlnsKey] = $xmlnsValue;
    }

    public function addSchemaLocation(array $locations): void
    {
        $SCHEMA_LOCATION = 'xsi:schemaLocation';

        if (!isset($this->xml['cfdi:Comprobante']['_attributes'][$SCHEMA_LOCATION])) {
            $this->xml['cfdi:Comprobante']['_attributes'][$SCHEMA_LOCATION] = '';
        }

        $currentLocations = $this->xml['cfdi:Comprobante']['_attributes'][$SCHEMA_LOCATION] ?? '';

        $listLocations = array_filter(explode(' ', $currentLocations));

        $uniqueLocations = array_unique(array_merge($listLocations, $locations));

        $schemaLocation = implode(' ', $uniqueLocations);

        $this->xml['cfdi:Comprobante']['_attributes'][$SCHEMA_LOCATION] = $schemaLocation;
    }

    public function setAttributesXml($attr = []): void
    {
        //TODO: Validar que los atributos sean correctos 1.0 UTF-8
    }

    public function setAttributes(array $attr = []): void
    {
        $xmlns = $attr['xmlns'] ?? [];
        $schemaLocation = $attr['schemaLocation'] ?? $this->locations;

        $this->xmlns($xmlns);
        $this->addSchemaLocation($schemaLocation);
    }

    public function comprobante(array $attribute): void
    {
        $order = [
            'xsi:schemaLocation',
            'Version',
            'Serie',
            'Folio',
            'Fecha',
            'Sello',
            'FormaPago',
            'NoCertificado',
            'Certificado',
            'CondicionesDePago',
            'SubTotal',
            'Descuento',
            'Moneda',
            'TipoCambio',
            'Total',
            'TipoDeComprobante',
            'Exportacion',
            'MetodoPago',
            'LugarExpedicion',
            'Confirmacion',
            'xmlns:cfdi',
            'xmlns:xsi',
        ];

        $attributes = array_merge(
            $this->xml['cfdi:Comprobante']['_attributes'] ?? [],
            ['Version' => $this->version],
            $attribute,
            [
                'Sello' => '',
                'NoCertificado' => '',
                'Certificado' => '',
                /*  'SubTotal' => $attribute['SubTotal'] ?? null,
                'Descuento' => $attribute['Descuento'] ?? null,
                'Total' => $attribute['Total'] ?? null, */
            ]
        );

        $sortedAttributes = $this->sortObject($attributes, $order);

        $this->xml['cfdi:Comprobante']['_attributes'] = $sortedAttributes;

        // TODO: Validar los atributos del comprobante
        /*  $comprobante = $this->schema['cfdi']['comprobante'];
        $comprobante->validateInit($this->xml['cfdi:Comprobante']['_attributes']); */
    }

    public function informacionGlobal(array $payload): void
    {
        // TODO: Validar los atributos de 'cfdi:InformacionGlobal'
        // $this->schema['cfdi']['informacionGlobal']->validate($payload);

        $this->xml['cfdi:Comprobante'] = array_merge(
            ['cfdi:InformacionGlobal' => ['_attributes' => $payload]],
            $this->xml['cfdi:Comprobante'] ?? []
        );
    }

    /**
     * Agregar relacionados
     *
     * @param Relacionado $relationCfdi
     * Relacionado
     */
    public function relacionados(/* Relacionado */array $relationCfdi): void
    {
        $this->xml['cfdi:Comprobante'] = array_merge(
            ['cfdi:CfdiRelacionados' => $relationCfdi->getRelation()],
            $this->xml['cfdi:Comprobante'] ?? []
        );
    }


    /**
     * Agregar emisor
     *
     * @param Emisor $emisor
     * Emisor
     */
    public function emisor(/* Emisor */array $emisor): void
    {
        $this->xml['cfdi:Comprobante']['cfdi:Emisor'] = $emisor->emisor;
    }


    /**
     * Agregar receptor
     *
     * @param Receptor $receptor
     */
    public function receptor(/* Receptor */$receptor): void
    {
        $this->xml['cfdi:Comprobante']['cfdi:Receptor'] = $receptor->receptor;
    }

    /**
     * Agregar concepto
     *
     * @param Concepto $concept
     */
    public function concepto(/* Concepto */$concept): void
    {
        if ($concept->isComplement()) {
            $properties = $concept->getComplementProperties();
            $this->addXmlns($properties['xmlnskey'], $properties['xmlns']);
            $this->addSchemaLocation($properties['schemaLocation']);
        }

        if (!isset($this->xml['cfdi:Comprobante']['cfdi:Conceptos'])) {
            $this->xml['cfdi:Comprobante']['cfdi:Conceptos'] = [
                'cfdi:Concepto' => [],
            ];
        }

        $this->xml['cfdi:Comprobante']['cfdi:Conceptos']['cfdi:Concepto'][] = $concept->getConcept();
    }

    /**
     * Agregar impuesto
     *
     * @param Impuestos $impuesto
     */
    public function impuesto(/* Impuestos */$impuesto): void
    {
        $this->xml['cfdi:Comprobante']['cfdi:Impuestos'] = $impuesto->impuesto;
    }

    /**
     * Agregar complemento
     *
     * @param ComplementType $complements
     */
    public function complemento(/* ComplementType */$complements): void
    {
        if (!isset($this->xml['cfdi:Comprobante']['cfdi:Complemento'])) {
            $this->xml['cfdi:Comprobante']['cfdi:Complemento'] = [];
        }

        $complement = $complements->getComplement();
        $this->addXmlns($complement['xmlnskey'], $complement['xmlns']);
        $this->addSchemaLocation($complement['schemaLocation']);
        $this->xml['cfdi:Comprobante']['cfdi:Complemento'][$complement['key']] = $complement['complement'];
    }

    /**
     * Establecer certificado
     *
     * @param string $certificado
     */
    public function setCertificado(string $certificado): void
    {
        if (!$certificado) {
            return;
        }

        $this->xml['cfdi:Comprobante']['_attributes']['Certificado'] = $certificado;
    }

    /**
     * Establecer número de certificado
     *
     * @param string $noCertificado
     */
    public function setNoCertificado(string $noCertificado): void
    {
        if (!$noCertificado) {
            return;
        }

        $this->xml['cfdi:Comprobante']['_attributes']['NoCertificado'] = $noCertificado;
    }

    /**
     * Establecer sello
     *
     * @param string $sello
     */
    public function setSello(string $sello): void
    {
        if (!$sello) {
            return;
        }

        $this->xml['cfdi:Comprobante']['_attributes']['Sello'] = $sello;
    }

    /**
     * Reiniciar CFDI
     */
    protected function restartCfdi(): void
    {
        $this->xml = [
            '_declaration' => [
                '_attributes' => [
                    'version' => '1.0',
                    'encoding' => 'utf-8',
                ],
            ],
        ];
        $this->xml['cfdi:Comprobante'] = [
            '_attributes' => [],
            'cfdi:Emisor' => [],
            'cfdi:Receptor' => [],
        ];
        $this->setAttributes();
    }



    public function toXml(): array
    {
        return $this->xml;
    }

    public function sortObject(array $obj, array $order): array
    {
        $sortedObj = [];

        // Añadir elementos en el orden especificado
        foreach ($order as $key) {
            if (array_key_exists($key, $obj)) {
                $sortedObj[$key] = $obj[$key];
            }
        }

        // Añadir los elementos restantes que no están en el orden
        foreach ($obj as $key => $value) {
            if (!array_key_exists($key, $sortedObj)) {
                $sortedObj[$key] = $value;
            }
        }

        return $sortedObj;
    }
}
