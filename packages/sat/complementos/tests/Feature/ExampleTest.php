<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Sat\Cfdi\Complementos\Iedu;

class ExampleTest extends TestCase
{
    public function test_example(): void
    {
        $iedu = new Iedu([
            'version' => '1.0',
            'nombreAlumno' => 'Test Student',
            'CURP' => 'TEST800101HDFRRL09',
            'nivelEducativo' => 'Licenciatura',
            'autRVOE' => 'RVOE-2023-001',
            'rfcPago' => 'EKU9003173C9',
        ]);

        $complement = $iedu->getComplement();
        $this->assertEquals('iedu:instEducativas', $complement['key']);
        $this->assertEquals('Test Student', $complement['complement']['_attributes']['nombreAlumno']);
    }
}
