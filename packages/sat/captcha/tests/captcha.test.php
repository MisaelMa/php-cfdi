<?php

declare(strict_types=1);

use Sat\Captcha\CaptchaChallenge;
use Sat\Captcha\CaptchaResult;
use Sat\Captcha\Solvers\ManualCaptchaSolver;
use Sat\Captcha\Solvers\TwoCaptchaSolver;

describe('CaptchaChallenge', function () {
    test('expone image y type por defecto base64', function () {
        $c = new CaptchaChallenge(image: 'aGVsbG8=');
        expect($c->image)->toBe('aGVsbG8=');
        expect($c->type)->toBe('base64');
    });

    test('acepta type personalizado', function () {
        $c = new CaptchaChallenge(image: 'x', type: 'base64');
        expect($c->type)->toBe('base64');
    });
});

describe('CaptchaResult', function () {
    test('texto e id opcional', function () {
        $r = new CaptchaResult(text: 'abc', id: 'task-1');
        expect($r->text)->toBe('abc');
        expect($r->id)->toBe('task-1');
    });

    test('id puede ser null', function () {
        $r = new CaptchaResult(text: 'ok');
        expect($r->id)->toBeNull();
    });
});

describe('ManualCaptchaSolver', function () {
    test('devuelve CaptchaResult con el texto del closure', function () {
        $solver = new ManualCaptchaSolver(fn (CaptchaChallenge $c) => 'hola');
        $out = $solver->solve(new CaptchaChallenge(image: 'img'));
        expect($out)->toBeInstanceOf(CaptchaResult::class);
        expect($out->text)->toBe('hola');
        expect($out->id)->toBeNull();
    });

    test('lanza si la respuesta está vacía', function () {
        $solver = new ManualCaptchaSolver(fn () => '');
        $solver->solve(new CaptchaChallenge(image: 'x'));
    })->throws(RuntimeException::class, 'No se proporciono respuesta al captcha');
});

describe('TwoCaptchaSolver', function () {
    test('lanza si la imagen base64 está vacía', function () {
        $solver = new TwoCaptchaSolver('api-key', pollIntervalMs: 0);
        $solver->solve(new CaptchaChallenge(image: ''));
    })->throws(InvalidArgumentException::class);

    test('lanza para type userrecaptcha', function () {
        $solver = new TwoCaptchaSolver('api-key', pollIntervalMs: 0);
        $solver->solve(new CaptchaChallenge(image: 'x', type: 'userrecaptcha'));
    })->throws(InvalidArgumentException::class);

    test('envía base64, espera CAPCHA_NOT_READY y devuelve texto con id', function () {
        $getCalls = 0;
        $post = static function (string $url, array $params): string {
            expect($url)->toContain('2captcha.com/in.php');
            expect($params['method'] ?? '')->toBe('base64');
            expect($params['body'] ?? '')->toBe('Ym9keQ==');
            expect($params['json'] ?? '')->toBe('1');

            return '{"status":1,"request":"99"}';
        };
        $get = static function (string $url) use (&$getCalls): string {
            expect($url)->toContain('2captcha.com/res.php');
            expect($url)->toContain('action=get');
            expect($url)->toContain('id=99');
            ++$getCalls;
            if ($getCalls === 1) {
                return '{"status":0,"request":"CAPCHA_NOT_READY"}';
            }

            return '{"status":1,"request":"SOLVED"}';
        };

        $solver = new TwoCaptchaSolver(
            'test-key',
            timeoutMs: 60_000,
            postForm: \Closure::fromCallable($post),
            getUrl: \Closure::fromCallable($get),
            pollIntervalMs: 0,
        );

        $result = $solver->solve(new CaptchaChallenge(image: 'Ym9keQ=='));
        expect($result->text)->toBe('SOLVED');
        expect($result->id)->toBe('99');
        expect($getCalls)->toBe(2);
    });

    test('reportgood y reportbad llaman a res.php', function () {
        $urls = [];
        $get = static function (string $url) use (&$urls): string {
            $urls[] = $url;

            return 'OK';
        };
        $solver = new TwoCaptchaSolver('k', getUrl: \Closure::fromCallable($get));

        $solver->report('tid', true);
        $solver->report('tid2', false);

        expect($urls)->toHaveCount(2);
        expect($urls[0])->toContain('action=reportgood');
        expect($urls[0])->toContain('id=tid');
        expect($urls[1])->toContain('action=reportbad');
        expect($urls[1])->toContain('id=tid2');
    });
});
