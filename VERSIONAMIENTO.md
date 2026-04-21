# Versionamiento - cfdi-php

Monorepo PHP gestionado con **symplify/monorepo-builder**.

## Comandos disponibles

```bash
# Alias definido en composer.json
composer symplify <comando>

# O directamente
./vendor/bin/monorepo-builder <comando>
```

## Release (bump de versión)

```bash
# Incrementar patch: 0.1.0 → 0.1.1
./vendor/bin/monorepo-builder release patch

# Incrementar minor: 0.1.0 → 0.2.0
./vendor/bin/monorepo-builder release minor

# Incrementar major: 0.1.0 → 1.0.0
./vendor/bin/monorepo-builder release major

# Versión explícita
./vendor/bin/monorepo-builder release 2.5.0

# Dry-run (preview sin aplicar cambios)
./vendor/bin/monorepo-builder release patch --dry-run
```

El comando `release` actualiza la versión en el `composer.json` raíz y propaga a todos los paquetes.

## Pre-release / Alpha / Beta / RC

Composer soporta versiones con sufijos de estabilidad. Para manejar alphas y pre-releases:

```bash
# Establecer una versión alpha
./vendor/bin/monorepo-builder release 1.0.0-alpha1

# Siguiente alpha
./vendor/bin/monorepo-builder release 1.0.0-alpha2

# Pasar a beta
./vendor/bin/monorepo-builder release 1.0.0-beta1

# Release candidate
./vendor/bin/monorepo-builder release 1.0.0-RC1

# Release final
./vendor/bin/monorepo-builder release 1.0.0
```

### Sufijos de estabilidad en Composer

Composer reconoce estos sufijos ordenados de menor a mayor estabilidad:

| Sufijo | Ejemplo | Orden |
|---|---|---|
| `alpha` / `a` | `1.0.0-alpha1` | 1 (menos estable) |
| `beta` / `b` | `1.0.0-beta1` | 2 |
| `RC` | `1.0.0-RC1` | 3 |
| (estable) | `1.0.0` | 4 (más estable) |

Un paquete que requiere `^1.0` **no** instalará versiones alpha/beta a menos que se configure `minimum-stability` en el `composer.json` del consumidor:

```json
{
    "minimum-stability": "alpha",
    "prefer-stable": true
}
```

## Otros comandos útiles

```bash
# Sincronizar versiones de todos los paquetes con el root
./vendor/bin/monorepo-builder propagate

# Merge: combinar composer.json de paquetes al root
./vendor/bin/monorepo-builder merge

# Validar que las versiones están sincronizadas
./vendor/bin/monorepo-builder validate

# Actualizar branch alias en todos los paquetes
./vendor/bin/monorepo-builder package-alias

# Bump interdependencias entre paquetes
./vendor/bin/monorepo-builder bump-interdependency
```

## Flujo de release típico

```bash
# 1. Verificar que todo está sincronizado
./vendor/bin/monorepo-builder validate

# 2. Correr tests
composer test

# 3. Preview del release
./vendor/bin/monorepo-builder release 1.0.0-alpha1 --dry-run

# 4. Ejecutar el release
./vendor/bin/monorepo-builder release 1.0.0-alpha1

# 5. Commit y tag
git add -A && git commit -m "release v1.0.0-alpha1"
git tag v1.0.0-alpha1
```
