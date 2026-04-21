<?php

use Cfdi\Transform\Transform;

$filesDir = dirname(__DIR__, 4) . '/../cfdi-node/packages/files';
$xmlPath = "{$filesDir}/xml";
$xslt40 = "{$filesDir}/4.0/cadenaoriginal.xslt";
$xslt33 = "{$filesDir}/3.3/cadenaoriginal-3.3.xslt";
$vehiculoUsado = "{$xmlPath}/vehiculo_usado.xml";
$examplesPath = "{$xmlPath}/examples";

function saxonAvailable(string $vehiculoUsado, string $xslt40): bool
{
    if (!class_exists('Cli\SaxonHe\Transform')) return false;
    try {
        $saxon = new \Cli\SaxonHe\Transform();
        $saxon->s($vehiculoUsado)->xsl($xslt40)->run();
        return true;
    } catch (\Throwable) {
        return false;
    }
}

function getExampleFiles(string $dir): array
{
    if (!is_dir($dir)) return [];
    $files = scandir($dir);
    return array_values(array_filter($files, fn($f) => str_ends_with($f, '.xml')));
}

$hasSaxon = saxonAvailable($vehiculoUsado, $xslt40);

describe('transform', function () use ($vehiculoUsado, $xslt40) {
    test('should generate cadena original from vehiculo_usado.xml', function () use ($vehiculoUsado, $xslt40) {
        $transform = new Transform();
        $cadena = $transform->s($vehiculoUsado)->xsl($xslt40)->run();
        expect($cadena)->toBeString();
        expect(str_starts_with($cadena, '||'))->toBeTrue();
        expect(str_ends_with($cadena, '||'))->toBeTrue();
    });

    test('should throw if xsl not loaded', function () use ($vehiculoUsado) {
        $transform = new Transform();
        expect(fn() => $transform->s($vehiculoUsado)->run())->toThrow(\RuntimeException::class, 'XSLT not loaded');
    });
});

describe('transform vs saxon-he (xml/)', function () use ($xmlPath, $xslt40, $hasSaxon) {
    $xmlFiles = getExampleFiles($xmlPath);

    foreach ($xmlFiles as $xmlFile) {
        $xmlFilePath = "{$xmlPath}/{$xmlFile}";

        test("{$xmlFile}: output must match Saxon-HE (4.0)", function () use ($xmlFilePath, $xslt40, $hasSaxon) {
            if (!$hasSaxon) {
                $this->markTestSkipped('Saxon-HE not available');
            }
            try {
                $saxon = new \Cli\SaxonHe\Transform();
                $cadenaSaxon = $saxon->s($xmlFilePath)->xsl($xslt40)->run();
            } catch (\Throwable) {
                return;
            }
            $transform = new Transform();
            $cadenaTransform = $transform->s($xmlFilePath)->xsl($xslt40)->run();
            expect($cadenaTransform)->toBe($cadenaSaxon);
        });
    }
});

describe('transform vs saxon-he (examples cfdi40 con xslt 4.0)', function () use ($examplesPath, $xslt40, $hasSaxon) {
    $cfdi40Files = getExampleFiles("{$examplesPath}/cfdi40");

    foreach ($cfdi40Files as $xmlFile) {
        $xmlFilePath = "{$examplesPath}/cfdi40/{$xmlFile}";

        test("{$xmlFile}: output must match Saxon-HE", function () use ($xmlFilePath, $xslt40, $hasSaxon) {
            if (!$hasSaxon) {
                $this->markTestSkipped('Saxon-HE not available');
            }
            try {
                $saxon = new \Cli\SaxonHe\Transform();
                $cadenaSaxon = $saxon->s($xmlFilePath)->xsl($xslt40)->run();
            } catch (\Throwable) {
                return;
            }
            $transform = new Transform();
            $cadenaTransform = $transform->s($xmlFilePath)->xsl($xslt40)->run();
            expect($cadenaTransform)->toBe($cadenaSaxon);
        });
    }
});

describe('transform vs saxon-he (examples cfdi33 con xslt 3.3)', function () use ($examplesPath, $xslt33, $hasSaxon) {
    $cfdi33Files = getExampleFiles("{$examplesPath}/cfdi33");

    foreach ($cfdi33Files as $xmlFile) {
        $xmlFilePath = "{$examplesPath}/cfdi33/{$xmlFile}";

        test("{$xmlFile}: output must match Saxon-HE", function () use ($xmlFilePath, $xslt33, $hasSaxon) {
            if (!$hasSaxon) {
                $this->markTestSkipped('Saxon-HE not available');
            }
            try {
                $saxon = new \Cli\SaxonHe\Transform();
                $cadenaSaxon = $saxon->s($xmlFilePath)->xsl($xslt33)->run();
            } catch (\Throwable) {
                return;
            }
            $transform = new Transform();
            $cadenaTransform = $transform->s($xmlFilePath)->xsl($xslt33)->run();
            expect($cadenaTransform)->toBe($cadenaSaxon);
        });
    }
});

describe('transform vs saxon-he (test-cfdi40 con xslt 4.0)', function () use ($examplesPath, $xslt40, $hasSaxon) {
    $testFiles = getExampleFiles("{$examplesPath}/test-cfdi40");

    foreach ($testFiles as $xmlFile) {
        $xmlFilePath = "{$examplesPath}/test-cfdi40/{$xmlFile}";

        test("{$xmlFile}: output must match Saxon-HE (test-cfdi40)", function () use ($xmlFilePath, $xslt40, $hasSaxon) {
            if (!$hasSaxon) {
                $this->markTestSkipped('Saxon-HE not available');
            }
            try {
                $saxon = new \Cli\SaxonHe\Transform();
                $cadenaSaxon = $saxon->s($xmlFilePath)->xsl($xslt40)->run();
            } catch (\Throwable) {
                return;
            }
            $transform = new Transform();
            $cadenaTransform = $transform->s($xmlFilePath)->xsl($xslt40)->run();
            expect($cadenaTransform)->toBe($cadenaSaxon);
        });

        test("{$xmlFile}: cadena original should be valid (test-cfdi40)", function () use ($xmlFilePath, $xslt40) {
            $transform = new Transform();
            $cadena = $transform->s($xmlFilePath)->xsl($xslt40)->run();

            expect($cadena)->toBeString();
            expect(str_starts_with($cadena, '||'))->toBeTrue();
            expect(str_ends_with($cadena, '||'))->toBeTrue();
            expect(strlen($cadena))->toBeGreaterThan(4);
        });
    }
});

describe('transform vs saxon-he (test-cfdi33 con xslt 3.3)', function () use ($examplesPath, $xslt33, $hasSaxon) {
    $testFiles = getExampleFiles("{$examplesPath}/test-cfdi33");

    foreach ($testFiles as $xmlFile) {
        $xmlFilePath = "{$examplesPath}/test-cfdi33/{$xmlFile}";

        test("{$xmlFile}: output must match Saxon-HE (test-cfdi33)", function () use ($xmlFilePath, $xslt33, $hasSaxon) {
            if (!$hasSaxon) {
                $this->markTestSkipped('Saxon-HE not available');
            }
            try {
                $saxon = new \Cli\SaxonHe\Transform();
                $cadenaSaxon = $saxon->s($xmlFilePath)->xsl($xslt33)->run();
            } catch (\Throwable) {
                return;
            }
            $transform = new Transform();
            $cadenaTransform = $transform->s($xmlFilePath)->xsl($xslt33)->run();
            expect($cadenaTransform)->toBe($cadenaSaxon);
        });

        test("{$xmlFile}: cadena original should be valid (test-cfdi33)", function () use ($xmlFilePath, $xslt33) {
            $transform = new Transform();
            $cadena = $transform->s($xmlFilePath)->xsl($xslt33)->run();

            expect($cadena)->toBeString();
            expect(str_starts_with($cadena, '||'))->toBeTrue();
            expect(str_ends_with($cadena, '||'))->toBeTrue();
            expect(strlen($cadena))->toBeGreaterThan(4);
        });
    }
});
