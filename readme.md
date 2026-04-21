# cfdi-php — Monorepo PHP para facturación electrónica SAT

Réplica en PHP de los **34 paquetes** de [cfdi-node](../cfdi-node). Este repositorio es un **monorepo** gestionado con **Composer** y **symplify/monorepo-builder**: un solo `composer.json` raíz con autoload PSR-4 que une **32 paquetes** bajo `packages/` (el resto de piezas de Node pueden estar fusionadas o pendientes según el estado del port).

## Requisitos

- **PHP 8.3** o superior (`^8.3` en el proyecto)
- **Composer 2.x**

**Instalar PHP**

- macOS: `brew install php`
- Otras plataformas: [Descargas oficiales de PHP](https://www.php.net/downloads)

**Instalar Composer**

- macOS: `brew install composer`
- O: [getcomposer.org/download](https://getcomposer.org/download/)

**Comprobar**

```bash
php -v && composer --version
```

## Inicio rápido

```bash
cd cfdi-php
composer install
composer test
```

`composer test` ejecuta **Pest** sobre el monorepo (`./vendor/bin/pest`).

## Estructura del proyecto

Cada paquete vive en `packages/<grupo>/<nombre>/` (por ejemplo `packages/cfdi/cleaner/`, `packages/sat/rfc/`). El **`composer.json` raíz** declara el autoload PSR-4 (y `classmap` donde aplica) para todos los namespaces; al trabajar **dentro del monorepo** no necesitas `composer require` por paquete: con `composer install` en la raíz quedan disponibles todas las clases.

```
cfdi-php/
├── composer.json          ← monorepo, autoload y scripts
├── packages/
│   ├── cfdi/              ← lógica CFDI (XML, esquemas, utilidades, etc.)
│   ├── sat/               ← SAT (auth, catálogos, CSD, validación, etc.)
│   ├── cli/               ← integraciones CLI (OpenSSL, Saxon-HE)
│   └── renapo/            ← CURP (RENAPO)
└── VERSIONAMIENTO.md      ← versionado con monorepo-builder
```

## Paquetes (32)

### `cfdi/`

| Paquete | Descripción breve |
| --- | --- |
| `cleaner` | Limpieza y normalización de XML CFDI |
| `designs` | Recursos de diseño / presentación relacionados con CFDI |
| `descarga` | Descarga masiva y flujos asociados al SAT |
| `expresiones` | Expresiones de identificación de CFDI |
| `pdf` | Generación o apoyo a representación impresa (PDF) |
| `retenciones` | Retenciones e ISR (XML y reglas) |
| `schema` | Definición y uso de esquemas CFDI |
| `transform` | Transformaciones de XML (p. ej. cadena original) |
| `types` | Contratos / tipos alineados con estructuras del comprobante |
| `utils` | Utilidades compartidas del dominio CFDI |
| `xml2json` | Conversión entre XML CFDI y JSON |
| `xsd` | XSD del SAT y validación estructural |

Además, el código en **`cfdi/csf/`** (constancia de situación fiscal, namespace `Cfdi\Csf`) se carga desde el autoload raíz aunque esa carpeta no declare un `composer.json` propio.

### `sat/`

| Paquete | Descripción breve |
| --- | --- |
| `auth` | Autenticación y firma frente a servicios del SAT |
| `banxico` | Integración con tipos de cambio (Banxico) |
| `cancelacion` | Cancelación de CFDI |
| `captcha` | Resolución o manejo de captcha en flujos SAT |
| `catalogos` | Catálogos del SAT (códigos y metadatos) |
| `cfdi` | Construcción del comprobante CFDI 4.0 y XML base |
| `complementos` | Complementos fiscales en XML |
| `contabilidad` | Contabilidad electrónica (p. ej. Anexo 24 RMF) |
| `csd` | Certificado de Sello Digital (CER/KEY, NoCertificado) |
| `diot` | Declaración informativa de operaciones con terceros |
| `estado` | Consulta de estatus de CFDI en el SAT |
| `opinion` | Opinión de cumplimiento |
| `pacs` | Proveedores autorizados de certificación (PAC) |
| `recursos` | Descarga y uso de recursos públicos del SAT |
| `rfc` | Validación y valor tipado de RFC |
| `scraper` | Utilidades de extracción desde portales SAT |
| `validador` | Validación semántica y reglas de negocio |

### `cli/`

| Paquete | Descripción breve |
| --- | --- |
| `openssl` | Invocación de OpenSSL para firma y criptografía |
| `saxon-he` | Saxon-HE para XSLT (p. ej. cadena original) |

### `renapo/`

| Paquete | Descripción breve |
| --- | --- |
| `curp` | Validación y utilidades de CURP |

## Cómo usar un paquete

En el monorepo, **todo está autoloaded** desde la raíz. Ejemplos mínimos:

**RFC (`Cfdi\Rfc`)**

```php
use Cfdi\Rfc\Rfc;
use Cfdi\Rfc\InvalidRfcError;

$rfc = Rfc::of('GODE561231GR8');
echo $rfc->toString();

if (Rfc::isValid('XAXX010101000')) {
    // RFC genérico aceptado por reglas SAT
}

// Sin excepciones:
$parsed = Rfc::parse('no-es-rfc'); // null
```

**Comprobante CFDI (`Sat\Cfdi\Comprobante`)**

```php
use Sat\Cfdi\Comprobante;

$cfdi = new Comprobante();
$cfdi->comprobante([
    'Fecha' => '2023-01-01T00:00:00',
    'FormaPago' => '01',
    'SubTotal' => '1000.00',
    'Moneda' => 'MXN',
    'Total' => '1160.00',
    'TipoDeComprobante' => 'I',
    'Exportacion' => '01',
    'MetodoPago' => 'PUE',
    'LugarExpedicion' => '86991',
]);

$xml = $cfdi->toXml();
```

Para firmar y cadena original existe la clase `Sat\CFDI` en el mismo paquete `sat/cfdi`, con métodos como `certificar()` y `generarCadenaOriginal()` según la configuración del proyecto.

## Tests

- **Todo el monorepo:** `composer test` o `./vendor/bin/pest`
- **Un paquete (carpeta de tests):** `./vendor/bin/pest packages/cfdi/retenciones/`

## Versionamiento (bump)

Las versiones se alinean con **symplify/monorepo-builder** (alias `composer symplify` en el `composer.json` raíz). Para **ajustar restricciones de dependencia entre paquetes** del monorepo:

```bash
./vendor/bin/monorepo-builder bump-interdependency "^X.Y.Z"
```

Sustituye `X.Y.Z` por la versión semver que quieras imponer entre paquetes internos. Los comandos de **release** (`release patch|minor|major`, versiones explícitas, pre-releases, `validate`, `propagate`, etc.) están documentados en **[VERSIONAMIENTO.md](./VERSIONAMIENTO.md)**.

## Diferencias respecto a Node.js / TypeScript

- **Enums:** PHP 8.3 usa **enums** (incluidos *backed enums*) en lugar de enums de TypeScript.
- **DTOs:** Clases y propiedades **`readonly`** donde aplica, en lugar de objetos mutables por defecto.
- **Contratos:** **Interfaces** PHP para contratos (las interfaces no llevan propiedades; se usan métodos o clases de datos).
- **Módulos:** **Composer + PSR-4** en lugar de `package.json` y resolución de Node.
- **Tests:** **Pest**, con estilo cercano a **Vitest/Jest** (expect, describe, etc.).

---

Si vienes de [cfdi-elixir](../cfdi-elixir), la idea es la misma: muchos paquetes por dominio, un proyecto raíz que los ensambla; aquí el pegamento es Composer y un solo autoload en la raíz.
