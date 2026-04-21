<?php

namespace Cfdi\Catalogos;

enum UsoCFDI: string
{
    case ADQUISICION_MERCANCIAS = 'G01';
    case DEVOLUCIONES_DESCUENTOS_BONIFICACIONES = 'G02';
    case GASTOS_EN_GENERAL = 'G03';
    case CONSTRUCCIONES = 'I01';
    case MOBILIARIO_Y_EQUIPO_DE_OFICINA = 'I02';
    case EQUIPO_DE_TRANSPORTE = 'I03';
    case EQUIPO_DE_COMPUTO = 'I04';
    case DADOS_TROQUELES_HERRAMENTAL = 'I05';
    case COMUNICACIONES_TELEFONICAS = 'I06';
    case COMUNICACIONES_SATELITALES = 'I07';
    case OTRA_MAQUINARIA = 'I08';
    case HONORARIOS_MEDICOS = 'D01';
    case GASTOS_MEDICOS_POR_INCAPACIDAD = 'D02';
    case GASTOS_FUNERALES = 'D03';
    case DONATIVOS = 'D04';
    case INTERESES_POR_CREDITOS_HIPOTECARIOS = 'D05';
    case APORTACIONES_VOLUNTARIAS_SAR = 'D06';
    case PRIMA_SEGUROS_GASTOS_MEDICOS = 'D07';
    case GASTOS_TRANSPORTACION_ESCOLAR = 'D08';
    case CUENTAS_AHORRO_PENSIONES = 'D09';
    case SERVICIOS_EDUCATIVOS = 'D10';
    case POR_DEFINIR = 'P01';
    case SIN_EFECTOS_FISCALES = 'S01';
    case PAGOS = 'CP01';
    case NOMINA = 'CN01';

    public function label(): string
    {
        return match ($this) {
            self::ADQUISICION_MERCANCIAS => 'Adquisición de mercancias',
            self::DEVOLUCIONES_DESCUENTOS_BONIFICACIONES => 'Devoluciones, descuentos o bonificaciones',
            self::GASTOS_EN_GENERAL => 'Gastos en general',
            self::CONSTRUCCIONES => 'Construcciones',
            self::MOBILIARIO_Y_EQUIPO_DE_OFICINA => 'Mobilario y equipo de oficina por inversiones',
            self::EQUIPO_DE_TRANSPORTE => 'Equipo de transporte',
            self::EQUIPO_DE_COMPUTO => 'Equipo de computo y accesorios',
            self::DADOS_TROQUELES_HERRAMENTAL => 'Dados, troqueles, moldes, matrices y herramental',
            self::COMUNICACIONES_TELEFONICAS => 'Comunicaciones telefónicas',
            self::COMUNICACIONES_SATELITALES => 'Comunicaciones satelitales',
            self::OTRA_MAQUINARIA => 'Otra maquinaria y equipo',
            self::HONORARIOS_MEDICOS => 'Honorarios médicos, dentales y gastos hospitalarios.',
            self::GASTOS_MEDICOS_POR_INCAPACIDAD => 'Gastos médicos por incapacidad o discapacidad',
            self::GASTOS_FUNERALES => 'Gastos funerales.',
            self::DONATIVOS => 'Donativos.',
            self::INTERESES_POR_CREDITOS_HIPOTECARIOS => 'Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación).',
            self::APORTACIONES_VOLUNTARIAS_SAR => 'Aportaciones voluntarias al SAR.',
            self::PRIMA_SEGUROS_GASTOS_MEDICOS => 'Primas por seguros de gastos médicos.',
            self::GASTOS_TRANSPORTACION_ESCOLAR => 'Gastos de transportación escolar obligatoria.',
            self::CUENTAS_AHORRO_PENSIONES => 'Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones.',
            self::SERVICIOS_EDUCATIVOS => 'Pagos por servicios educativos (colegiaturas)',
            self::POR_DEFINIR => 'Por definir',
            self::SIN_EFECTOS_FISCALES => 'Sin efectos fiscales',
            self::PAGOS => 'Pagos',
            self::NOMINA => 'Nómina',
        };
    }
}
