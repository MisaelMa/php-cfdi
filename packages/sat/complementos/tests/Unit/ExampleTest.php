<?php

namespace Tests\Unit;

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
            'nivelEducativo' => 'Primaria',
            'autRVOE' => 'RVOE-001',
            'rfcPago' => 'XAXX010101000',
        ]);

        $this->assertEquals('Iedu', Iedu::iedu());
        $this->assertInstanceOf(Iedu::class, $iedu);
    }
}
