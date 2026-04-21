<?php

namespace Cfdi\Catalogos;

enum FormaPago: string
{
    case EFECTIVO = '01';
    case CHEQUE_NOMINATIVO = '02';
    case TRANSFERENCIA_ELECTRONICA = '03';
    case TARJETA_DE_CREDITO = '04';
    case MONEDERO_ELECTRONICO = '05';
    case DINERO_ELECTRONICO = '06';
    case VALES_DE_DESPENSA = '08';
    case DACION_EN_PAGO = '12';
    case SUBROGACION = '13';
    case CONSIGNACION = '14';
    case CONDONACION = '15';
    case COMPENSACION = '17';
    case NOVACION = '23';
    case CONFUSION = '24';
    case REMISION_DE_DEUDA = '25';
    case PRESCRIPCION_O_CADUCIDAD = '26';
    case A_SATISFACCION_DEL_ACREEDOR = '27';
    case TARJETA_DE_DEBITO = '28';
    case TARJETA_DE_SERVICIOS = '29';
    case POR_DEFINIR = '99';

    public function label(): string
    {
        return match ($this) {
            self::EFECTIVO => 'Efectivo',
            self::CHEQUE_NOMINATIVO => 'Cheque nominativo',
            self::TRANSFERENCIA_ELECTRONICA => 'Transferencia electrónica de fondos',
            self::TARJETA_DE_CREDITO => 'Tarjeta de crédito',
            self::MONEDERO_ELECTRONICO => 'Monedero electrónico',
            self::DINERO_ELECTRONICO => 'Dinero electrónico',
            self::VALES_DE_DESPENSA => 'Vales de despensa',
            self::DACION_EN_PAGO => 'Dación en pago',
            self::SUBROGACION => 'Pago por subrogación',
            self::CONSIGNACION => 'Pago por consignación',
            self::CONDONACION => 'Condonación',
            self::COMPENSACION => 'Compensación',
            self::NOVACION => 'Novación',
            self::CONFUSION => 'Confusión',
            self::REMISION_DE_DEUDA => 'Remisión de deuda',
            self::PRESCRIPCION_O_CADUCIDAD => 'Prescripción o caducidad',
            self::A_SATISFACCION_DEL_ACREEDOR => 'A satisfacción del acreedor',
            self::TARJETA_DE_DEBITO => 'Tarjeta de débito',
            self::TARJETA_DE_SERVICIOS => 'Tarjeta de servicios',
            self::POR_DEFINIR => 'Por definir',
        };
    }
}

enum Exportacion: string
{
    case NO_APLICA = '01';
    case DEFINITIVA = '02';
    case TEMPORAL = '03';

    public function label(): string
    {
        return match ($this) {
            self::NO_APLICA => 'No aplica',
            self::DEFINITIVA => 'Definitiva',
            self::TEMPORAL => 'Temporal',
        };
    }
}
