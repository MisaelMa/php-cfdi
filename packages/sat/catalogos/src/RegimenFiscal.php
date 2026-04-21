<?php

namespace Cfdi\Catalogos;

class RegimenFiscal
{
    public const LIST = [
        ['value' => 601, 'descripcion' => 'General de Ley Personas Morales', 'fisica' => false, 'moral' => true],
        ['value' => 603, 'descripcion' => 'Personas Morales con Fines no Lucrativos', 'fisica' => false, 'moral' => true],
        ['value' => 605, 'descripcion' => 'Sueldos y Salarios e Ingresos Asimilados a Salarios', 'fisica' => true, 'moral' => false],
        ['value' => 606, 'descripcion' => 'Arrendamiento', 'fisica' => true, 'moral' => false],
        ['value' => 607, 'descripcion' => 'Régimen de Enajenación o Adquisición de Bienes', 'fisica' => false, 'moral' => true],
        ['value' => 608, 'descripcion' => 'Demás ingresos', 'fisica' => true, 'moral' => false],
        ['value' => 609, 'descripcion' => 'Consolidación', 'fisica' => false, 'moral' => true],
        ['value' => 610, 'descripcion' => 'Residentes en el Extranjero sin Establecimiento Permanente en México', 'fisica' => true, 'moral' => true],
        ['value' => 611, 'descripcion' => 'Ingresos por Dividendos (socios y accionistas)', 'fisica' => true, 'moral' => false],
        ['value' => 612, 'descripcion' => 'Personas Físicas con Actividades Empresariales y Profesionales', 'fisica' => true, 'moral' => false],
        ['value' => 614, 'descripcion' => 'Ingresos por intereses', 'fisica' => true, 'moral' => false],
        ['value' => 615, 'descripcion' => 'Régimen de los ingresos por obtención de premios', 'fisica' => true, 'moral' => false],
        ['value' => 616, 'descripcion' => 'Sin obligaciones fiscales', 'fisica' => true, 'moral' => false],
        ['value' => 620, 'descripcion' => 'Sociedades Cooperativas de Producción que optan por diferir sus ingresos', 'fisica' => false, 'moral' => true],
        ['value' => 621, 'descripcion' => 'Incorporación Fiscal', 'fisica' => true, 'moral' => false],
        ['value' => 622, 'descripcion' => 'Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras', 'fisica' => true, 'moral' => true],
        ['value' => 623, 'descripcion' => 'Opcional para Grupos de Sociedades', 'fisica' => false, 'moral' => true],
        ['value' => 624, 'descripcion' => 'Coordinados', 'fisica' => false, 'moral' => true],
        ['value' => 625, 'descripcion' => 'Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas', 'fisica' => true, 'moral' => false],
        ['value' => 628, 'descripcion' => 'Hidrocarburos', 'fisica' => false, 'moral' => true],
        ['value' => 629, 'descripcion' => 'De los Regímenes Fiscales Preferentes y de las Empresas Multinacionales', 'fisica' => true, 'moral' => false],
        ['value' => 630, 'descripcion' => 'Enajenación de acciones en bolsa de valores', 'fisica' => true, 'moral' => false],
    ];

    public static function find(int $value): ?array
    {
        foreach (self::LIST as $item) {
            if ($item['value'] === $value) {
                return $item;
            }
        }
        return null;
    }

    public static function forFisica(): array
    {
        return array_filter(self::LIST, fn(array $item) => $item['fisica']);
    }

    public static function forMoral(): array
    {
        return array_values(array_filter(self::LIST, fn(array $item) => $item['moral']));
    }
}
