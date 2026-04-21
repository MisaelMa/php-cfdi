<?php

namespace Cfdi\Retenciones;

use InvalidArgumentException;

final class Retencion20Builder
{
    private const P = 'retenciones';

    /**
     * Namespace URI for CFDI de Retenciones e información de pagos 1.0.
     *
     * @see http://www.sat.gob.mx/esquemas/retencionpago/1
     */
    public const RETENCION_PAGO_NAMESPACE_V1 = 'http://www.sat.gob.mx/esquemas/retencionpago/1';

    /**
     * Namespace URI for CFDI de Retenciones e información de pagos 2.0.
     *
     * @see http://www.sat.gob.mx/esquemas/retencionpago/2
     */
    public const RETENCION_PAGO_NAMESPACE_V2 = 'http://www.sat.gob.mx/esquemas/retencionpago/2';

    /**
     * Genera el XML del comprobante de Retenciones 2.0 con el namespace oficial del SAT.
     *
     * @param array<string, mixed> $data Estructura alineada con {@see Retencion20} (claves como en el esquema SAT / API Node).
     */
    public static function build(array $data): string
    {
        return self::buildXml(self::hydrate($data));
    }

    private static function buildXml(Retencion20 $doc): string
    {
        $p = self::P;
        $t = $doc->totales;
        $per = $doc->periodo;

        $rootAttrs =
            ' xmlns:' . $p . '="' . self::RETENCION_PAGO_NAMESPACE_V2 . '"' .
            ' Version="' . self::escapeXmlAttr($doc->Version) . '"' .
            ' CveRetenc="' . self::escapeXmlAttr($doc->CveRetenc) . '"' .
            self::optAttr('DescRetenc', $doc->DescRetenc) .
            ' FechaExp="' . self::escapeXmlAttr($doc->FechaExp) . '"' .
            ' LugarExpRet="' . self::escapeXmlAttr($doc->LugarExpRet) . '"' .
            self::optAttr('NumCert', $doc->NumCert) .
            self::optAttr('FolioInt', $doc->FolioInt);

        $body =
            self::buildEmisorXml($doc->emisor) .
            self::buildReceptorXml($doc->receptor) .
            '<' . $p . ':Periodo MesIni="' . self::escapeXmlAttr($per->MesIni) . '" MesFin="' . self::escapeXmlAttr($per->MesFin) . '" Ejerc="' . self::escapeXmlAttr($per->Ejerc) . '"/>' .
            '<' . $p . ':Totales' .
            ' montoTotOperacion="' . self::escapeXmlAttr($t->montoTotOperacion) . '"' .
            ' montoTotGrav="' . self::escapeXmlAttr($t->montoTotGrav) . '"' .
            ' montoTotExent="' . self::escapeXmlAttr($t->montoTotExent) . '"' .
            ' montoTotRet="' . self::escapeXmlAttr($t->montoTotRet) . '"' .
            '/>' .
            self::buildComplementoXml($doc->complemento);

        return '<?xml version="1.0" encoding="UTF-8"?><' . $p . ':Retenciones' . $rootAttrs . '>' . $body . '</' . $p . ':Retenciones>';
    }

    private static function buildEmisorXml(EmisorRetencion $emisor): string
    {
        $p = self::P;

        return
            '<' . $p . ':Emisor' .
            ' Rfc="' . self::escapeXmlAttr($emisor->Rfc) . '"' .
            self::optAttr('NomDenRazSocE', $emisor->NomDenRazSocE) .
            ' RegimenFiscalE="' . self::escapeXmlAttr($emisor->RegimenFiscalE) . '"' .
            self::optAttr('CURPE', $emisor->CurpE) .
            '/>';
    }

    private static function buildReceptorXml(ReceptorRetencion $receptor): string
    {
        $p = self::P;
        $nat = $receptor->NacionalidadR->value;
        $nac = $receptor->NacionalidadR === NacionalidadReceptor::Nacional ? $receptor->nacional : null;
        $ext = $receptor->NacionalidadR === NacionalidadReceptor::Extranjero ? $receptor->extranjero : null;

        $inner = '';
        if ($nac !== null) {
            $inner .=
                '<' . $p . ':Nacional' .
                ' RFCRecep="' . self::escapeXmlAttr($nac->RfcRecep) . '"' .
                self::optAttr('NomDenRazSocR', $nac->NomDenRazSocR) .
                self::optAttr('CURPR', $nac->CurpR) .
                '/>';
        }
        if ($ext !== null) {
            $inner .=
                '<' . $p . ':Extranjero' .
                self::optAttr('NumRegIdTrib', $ext->NumRegIdTrib) .
                ' NomDenRazSocR="' . self::escapeXmlAttr($ext->NomDenRazSocR) . '"' .
                '/>';
        }

        return '<' . $p . ':Receptor Nacionalidad="' . self::escapeXmlAttr($nat) . '">' . $inner . '</' . $p . ':Receptor>';
    }

    /**
     * @param list<ComplementoRetencion>|null $complemento
     */
    private static function buildComplementoXml(?array $complemento): string
    {
        if ($complemento === null || $complemento === []) {
            return '';
        }
        $p = self::P;
        $body = '';
        foreach ($complemento as $c) {
            $body .= $c->innerXml;
        }

        return '<' . $p . ':Complemento>' . $body . '</' . $p . ':Complemento>';
    }

    private static function escapeXmlAttr(string $value): string
    {
        return str_replace(['&', '<', '>', '"'], ['&amp;', '&lt;', '&gt;', '&quot;'], $value);
    }

    private static function optAttr(string $name, ?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return ' ' . $name . '="' . self::escapeXmlAttr($value) . '"';
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function hydrate(array $data): Retencion20
    {
        $version = isset($data['Version']) ? (string) $data['Version'] : '2.0';
        if ($version !== '2.0') {
            throw new InvalidArgumentException('Solo se admite Version 2.0.');
        }

        $emisor = $data['emisor'] ?? null;
        if (!is_array($emisor)) {
            throw new InvalidArgumentException('emisor debe ser un array.');
        }

        $receptor = $data['receptor'] ?? null;
        if (!is_array($receptor)) {
            throw new InvalidArgumentException('receptor debe ser un array.');
        }

        $periodo = $data['periodo'] ?? null;
        if (!is_array($periodo)) {
            throw new InvalidArgumentException('periodo debe ser un array.');
        }

        $totales = $data['totales'] ?? null;
        if (!is_array($totales)) {
            throw new InvalidArgumentException('totales debe ser un array.');
        }

        return new Retencion20(
            CveRetenc: self::reqString($data, 'CveRetenc'),
            FechaExp: self::reqString($data, 'FechaExp'),
            LugarExpRet: self::reqString($data, 'LugarExpRet'),
            emisor: self::hydrateEmisor($emisor),
            receptor: self::hydrateReceptor($receptor),
            periodo: self::hydratePeriodo($periodo),
            totales: self::hydrateTotales($totales),
            Version: $version,
            DescRetenc: self::optString($data, 'DescRetenc'),
            NumCert: self::optString($data, 'NumCert'),
            FolioInt: self::optString($data, 'FolioInt'),
            complemento: self::hydrateComplemento($data['complemento'] ?? null),
        );
    }

    /**
     * @param array<string, mixed> $e
     */
    private static function hydrateEmisor(array $e): EmisorRetencion
    {
        return new EmisorRetencion(
            Rfc: self::reqString($e, 'Rfc'),
            RegimenFiscalE: self::reqString($e, 'RegimenFiscalE'),
            NomDenRazSocE: self::optString($e, 'NomDenRazSocE'),
            CurpE: self::optString($e, 'CurpE'),
        );
    }

    /**
     * @param array<string, mixed> $r
     */
    private static function hydrateReceptor(array $r): ReceptorRetencion
    {
        $natRaw = self::reqString($r, 'NacionalidadR');
        $nat = NacionalidadReceptor::tryFrom($natRaw)
            ?? throw new InvalidArgumentException('NacionalidadR debe ser Nacional o Extranjero.');

        $nacional = null;
        $extranjero = null;
        if ($nat === NacionalidadReceptor::Nacional) {
            $n = $r['nacional'] ?? null;
            if (!is_array($n)) {
                throw new InvalidArgumentException('receptor.nacional es requerido para NacionalidadR Nacional.');
            }
            $nacional = new ReceptorNacional(
                RfcRecep: self::reqString($n, 'RfcRecep'),
                NomDenRazSocR: self::optString($n, 'NomDenRazSocR'),
                CurpR: self::optString($n, 'CurpR'),
            );
        } else {
            $ex = $r['extranjero'] ?? null;
            if (!is_array($ex)) {
                throw new InvalidArgumentException('receptor.extranjero es requerido para NacionalidadR Extranjero.');
            }
            $extranjero = new ReceptorExtranjero(
                NomDenRazSocR: self::reqString($ex, 'NomDenRazSocR'),
                NumRegIdTrib: self::optString($ex, 'NumRegIdTrib'),
            );
        }

        return new ReceptorRetencion(NacionalidadR: $nat, nacional: $nacional, extranjero: $extranjero);
    }

    /**
     * @param array<string, mixed> $p
     */
    private static function hydratePeriodo(array $p): PeriodoRetencion
    {
        return new PeriodoRetencion(
            MesIni: self::reqString($p, 'MesIni'),
            MesFin: self::reqString($p, 'MesFin'),
            Ejerc: self::reqString($p, 'Ejerc'),
        );
    }

    /**
     * @param array<string, mixed> $t
     */
    private static function hydrateTotales(array $t): TotalesRetencion
    {
        return new TotalesRetencion(
            montoTotOperacion: self::reqString($t, 'montoTotOperacion'),
            montoTotGrav: self::reqString($t, 'montoTotGrav'),
            montoTotExent: self::reqString($t, 'montoTotExent'),
            montoTotRet: self::reqString($t, 'montoTotRet'),
        );
    }

    /**
     * @return list<ComplementoRetencion>|null
     */
    private static function hydrateComplemento(mixed $raw): ?array
    {
        if ($raw === null) {
            return null;
        }
        if (!is_array($raw)) {
            throw new InvalidArgumentException('complemento debe ser un array o null.');
        }
        $list = [];
        foreach ($raw as $item) {
            if (!is_array($item)) {
                throw new InvalidArgumentException('Cada elemento de complemento debe ser un array.');
            }
            $meta = $item['meta'] ?? null;
            if ($meta !== null && !is_array($meta)) {
                throw new InvalidArgumentException('complemento.meta debe ser un array o null.');
            }
            /** @var array<string, mixed>|null $meta */
            $list[] = new ComplementoRetencion(
                innerXml: self::reqString($item, 'innerXml'),
                meta: $meta,
            );
        }

        return $list;
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function reqString(array $data, string $key): string
    {
        if (!array_key_exists($key, $data)) {
            throw new InvalidArgumentException("Campo requerido faltante: {$key}");
        }
        $v = $data[$key];
        if (!is_string($v) || $v === '') {
            throw new InvalidArgumentException("Campo requerido inválido o vacío: {$key}");
        }

        return $v;
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function optString(array $data, string $key): ?string
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }
        $v = $data[$key];
        if (!is_string($v)) {
            throw new InvalidArgumentException("Campo opcional debe ser string o null: {$key}");
        }
        if ($v === '') {
            return null;
        }

        return $v;
    }
}
