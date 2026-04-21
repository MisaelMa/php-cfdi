<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sat\Cfdi\Complementos\Iedu;

class ComplementoTest extends TestCase
{
    public function test_iedu_is_instance_of_complemento(): void
    {
        $iedu = new Iedu([
            'version' => '1.0',
            'nombreAlumno' => 'Juan Perez',
            'CURP' => 'PEPJ800101HDFRRL09',
            'nivelEducativo' => 'Licenciatura',
            'autRVOE' => 'RVOE-2020-001',
            'rfcPago' => 'XAXX010101000',
        ]);

        $this->assertInstanceOf(\Sat\Cfdi\Complementos\Complemento::class, $iedu);
    }

    public function test_iedu_get_complement_returns_correct_structure(): void
    {
        $iedu = new Iedu([
            'version' => '1.0',
            'nombreAlumno' => 'Juan Perez',
            'CURP' => 'PEPJ800101HDFRRL09',
            'nivelEducativo' => 'Licenciatura',
            'autRVOE' => 'RVOE-2020-001',
            'rfcPago' => 'XAXX010101000',
        ]);

        $result = $iedu->getComplement();

        $this->assertArrayHasKey('complement', $result);
        $this->assertArrayHasKey('key', $result);
        $this->assertArrayHasKey('schemaLocation', $result);
        $this->assertArrayHasKey('xmlns', $result);
        $this->assertArrayHasKey('xmlnskey', $result);
    }

    public function test_iedu_key_is_correct(): void
    {
        $iedu = new Iedu([
            'version' => '1.0',
            'nombreAlumno' => 'Juan Perez',
            'CURP' => 'PEPJ800101HDFRRL09',
            'nivelEducativo' => 'Licenciatura',
            'autRVOE' => 'RVOE-2020-001',
            'rfcPago' => 'XAXX010101000',
        ]);

        $result = $iedu->getComplement();

        $this->assertEquals('iedu:instEducativas', $result['key']);
    }

    public function test_iedu_xmlns_is_correct(): void
    {
        $iedu = new Iedu([
            'version' => '1.0',
            'nombreAlumno' => 'Test',
            'CURP' => 'TEST800101HDFRRL09',
            'nivelEducativo' => 'Primaria',
            'autRVOE' => 'RVOE-001',
            'rfcPago' => 'XAXX010101000',
        ]);

        $result = $iedu->getComplement();

        $this->assertEquals('http://www.sat.gob.mx/iedu', $result['xmlns']);
    }

    public function test_iedu_xmlnskey_is_derived_from_key(): void
    {
        $iedu = new Iedu([
            'version' => '1.0',
            'nombreAlumno' => 'Test',
            'CURP' => 'TEST800101HDFRRL09',
            'nivelEducativo' => 'Primaria',
            'autRVOE' => 'RVOE-001',
            'rfcPago' => 'XAXX010101000',
        ]);

        $result = $iedu->getComplement();

        $this->assertEquals('iedu', $result['xmlnskey']);
    }

    public function test_iedu_schema_location_contains_xmlns_and_xsd(): void
    {
        $iedu = new Iedu([
            'version' => '1.0',
            'nombreAlumno' => 'Test',
            'CURP' => 'TEST800101HDFRRL09',
            'nivelEducativo' => 'Primaria',
            'autRVOE' => 'RVOE-001',
            'rfcPago' => 'XAXX010101000',
        ]);

        $result = $iedu->getComplement();

        $this->assertCount(2, $result['schemaLocation']);
        $this->assertContains('http://www.sat.gob.mx/iedu', $result['schemaLocation']);
        $this->assertContains('http://www.sat.gob.mx/sitio_internet/cfd/iedu/iedu.xsd', $result['schemaLocation']);
    }

    public function test_iedu_complement_contains_attributes(): void
    {
        $attributes = [
            'version' => '1.0',
            'nombreAlumno' => 'Maria Garcia',
            'CURP' => 'GARM900202MDFRRL01',
            'nivelEducativo' => 'Maestria',
            'autRVOE' => 'RVOE-2021-123',
            'rfcPago' => 'GARM900202AAA',
        ];

        $iedu = new Iedu($attributes);
        $result = $iedu->getComplement();

        $this->assertEquals($attributes, $result['complement']['_attributes']);
    }
}
