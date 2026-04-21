<?php

namespace Cfdi\Utils;

class NumeroALetras
{
    private function unidades(int $num): string
    {
        return match ($num) {
            1 => 'UN',
            2 => 'DOS',
            3 => 'TRES',
            4 => 'CUATRO',
            5 => 'CINCO',
            6 => 'SEIS',
            7 => 'SIETE',
            8 => 'OCHO',
            9 => 'NUEVE',
            default => '',
        };
    }

    private function decenas(int $num): string
    {
        $decena = intdiv($num, 10);
        $unidad = $num - ($decena * 10);

        return match ($decena) {
            1 => match ($unidad) {
                0 => 'DIEZ',
                1 => 'ONCE',
                2 => 'DOCE',
                3 => 'TRECE',
                4 => 'CATORCE',
                5 => 'QUINCE',
                default => 'DIECI' . $this->unidades($unidad),
            },
            2 => $unidad === 0 ? 'VEINTE' : 'VEINTI' . $this->unidades($unidad),
            3 => $this->decenasY('TREINTA', $unidad),
            4 => $this->decenasY('CUARENTA', $unidad),
            5 => $this->decenasY('CINCUENTA', $unidad),
            6 => $this->decenasY('SESENTA', $unidad),
            7 => $this->decenasY('SETENTA', $unidad),
            8 => $this->decenasY('OCHENTA', $unidad),
            9 => $this->decenasY('NOVENTA', $unidad),
            0 => $this->unidades($unidad),
            default => '',
        };
    }

    private function decenasY(string $strSin, int $numUnidades): string
    {
        if ($numUnidades > 0) {
            return $strSin . ' Y ' . $this->unidades($numUnidades);
        }
        return $strSin;
    }

    private function centenas(int $num): string
    {
        $centenas = intdiv($num, 100);
        $decenas = $num - ($centenas * 100);

        return match ($centenas) {
            1 => $decenas > 0 ? 'CIENTO ' . $this->decenas($decenas) : 'CIEN',
            2 => 'DOSCIENTOS ' . $this->decenas($decenas),
            3 => 'TRESCIENTOS ' . $this->decenas($decenas),
            4 => 'CUATROCIENTOS ' . $this->decenas($decenas),
            5 => 'QUINIENTOS ' . $this->decenas($decenas),
            6 => 'SEISCIENTOS ' . $this->decenas($decenas),
            7 => 'SETECIENTOS ' . $this->decenas($decenas),
            8 => 'OCHOCIENTOS ' . $this->decenas($decenas),
            9 => 'NOVECIENTOS ' . $this->decenas($decenas),
            default => $this->decenas($decenas),
        };
    }

    private function seccion(int $num, int $divisor, string $strSingular, string $strPlural): string
    {
        $cientos = intdiv($num, $divisor);
        $letras = '';

        if ($cientos > 0) {
            $letras = $cientos > 1
                ? $this->centenas($cientos) . ' ' . $strPlural
                : $strSingular;
        }

        return $letras;
    }

    private function miles(int $num): string
    {
        $divisor = 1000;
        $resto = $num - (intdiv($num, $divisor) * $divisor);

        $strMiles = $this->seccion($num, $divisor, 'UN MIL', 'MIL');
        $strCentenas = $this->centenas($resto);

        if ($strMiles === '') {
            return $strCentenas;
        }

        return $strMiles . ' ' . $strCentenas;
    }

    private function millones(int $num): string
    {
        $divisor = 1000000;
        $resto = $num - (intdiv($num, $divisor) * $divisor);

        $strMillones = $this->seccion($num, $divisor, 'UN MILLON DE', 'MILLONES DE');
        $strMiles = $this->miles($resto);

        if ($strMillones === '') {
            return $strMiles;
        }

        return $strMillones . ' ' . $strMiles;
    }

    public function convertir(
        float $num,
        string $plural = 'PESOS',
        string $singular = 'PESO',
    ): string {
        $enteros = (int) floor($num);
        $centavos = (int) round(($num * 100) - ($enteros * 100));

        if ($centavos > 0) {
            $letrasCentavos = $centavos < 10
                ? "0{$centavos}/100 M.N"
                : "{$centavos}/100 M.N";
        } else {
            $letrasCentavos = '00/100';
        }

        if ($enteros === 0) {
            return "CERO {$plural} {$letrasCentavos}";
        }

        if ($enteros === 1) {
            return $this->millones($enteros) . " {$singular} {$letrasCentavos}";
        }

        return $this->millones($enteros) . " {$plural} {$letrasCentavos}";
    }
}
