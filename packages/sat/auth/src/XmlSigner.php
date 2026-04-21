<?php

declare(strict_types=1);

namespace Sat\Auth;

final class XmlSigner
{
    public static function canonicalize(string $xmlFragment): string
    {
        $result = preg_replace('/<\?xml[^?]*\?>\s*/', '', $xmlFragment) ?? $xmlFragment;
        $result = str_replace(["\r\n", "\r"], "\n", $result);

        $result = preg_replace_callback(
            '/<([a-zA-Z][^\s\/>]*)((?:\s+[^>]*)?)(\/?)>/',
            static function (array $match): string {
                $tagName = $match[1];
                $attrsStr = $match[2];
                $selfClose = $match[3];
                if ($attrsStr === '' || trim($attrsStr) === '') {
                    return '<' . $tagName . $selfClose . '>';
                }
                $attrs = self::parseAttributes($attrsStr);
                ksort($attrs);
                $parts = [];
                foreach ($attrs as $name => $value) {
                    $parts[] = $name . '="' . $value . '"';
                }

                return '<' . $tagName . ' ' . implode(' ', $parts) . $selfClose . '>';
            },
            $result
        );

        return $result ?? '';
    }

    /**
     * @return array<string, string>
     */
    private static function parseAttributes(string $attrsStr): array
    {
        $attrs = [];
        if (preg_match_all('/([a-zA-Z_:][\w:.-]*)=["\']([^"\']*)["\']/', $attrsStr, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $attrs[$m[1]] = $m[2];
            }
        }

        return $attrs;
    }

    public static function sha256Digest(string $data): string
    {
        return base64_encode(hash('sha256', $data, true));
    }

    /**
     * @param \OpenSSLAsymmetricKey|string $privateKey
     */
    public static function signRsaSha256(string $data, $privateKey): string
    {
        if (is_string($privateKey)) {
            $privateKey = openssl_pkey_get_private($privateKey);
            if ($privateKey === false) {
                throw new \InvalidArgumentException('Invalid PEM private key');
            }
        }

        $signature = '';
        if (!openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('openssl_sign failed');
        }

        return base64_encode($signature);
    }
}
