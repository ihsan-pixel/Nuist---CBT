<?php

namespace App\Support;

use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class SebConfig
{
    public static function settings(Request $request, Exam $exam): array
    {
        return [
            'startURL' => self::startUrl(),
            'allowQuit' => true,
            'allowReloadInExam' => false,
            'showReloadButton' => false,
            'showTaskBar' => false,
            'showNavigationBar' => false,
            'openLinksInNewWindow' => false,
            'showHomeButton' => false,
            'showBackButton' => false,
            'showForwardButton' => false,
            'showMenuBar' => false,
            'allowSpellCheck' => false,
            'allowPreferences' => false,
            'allowQuitApp' => true,
            'allowQuitAfterExam' => false,
            'mainWindowEnableClose' => true,
            'mainWindowFullScreen' => true,
            'browserWindowWebViewMode' => 3,
            'sendBrowserExamKey' => true,
            'showTime' => true,
        ];
    }

    public static function startUrl(): string
    {
        $baseUrl = rtrim((string) config('app.url'), '/');
        $path = route('exam.seb', [], false);

        if ($baseUrl === '') {
            return url($path);
        }

        return Str::startsWith($baseUrl, 'https://')
            ? $baseUrl.$path
            : preg_replace('/^http:\/\//i', 'https://', $baseUrl).$path;
    }

    public static function configKey(array $settings): string
    {
        $canonical = self::canonicalJson($settings);

        return hash('sha256', $canonical);
    }

    public static function requestKeyHash(Request $request, string $configKey): string
    {
        $absoluteUrl = rtrim($request->fullUrl(), '#');

        return hash('sha256', $absoluteUrl.$configKey);
    }

    public static function plistXml(array $settings): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;

        $plist = $dom->appendChild($dom->createElement('plist'));
        $plist->setAttribute('version', '1.0');
        $dict = $plist->appendChild($dom->createElement('dict'));

        foreach ($settings as $key => $value) {
            $dict->appendChild($dom->createElement('key', (string) $key));

            if (is_bool($value)) {
                $dict->appendChild($dom->createElement($value ? 'true' : 'false'));

                continue;
            }

            if (is_int($value) || is_float($value) || ctype_digit((string) $value)) {
                $dict->appendChild($dom->createElement('integer', (string) $value));

                continue;
            }

            $node = $dom->createElement('string');
            $node->appendChild($dom->createTextNode((string) $value));
            $dict->appendChild($node);
        }

        $xml = $dom->saveXML();

        return $xml === false ? '' : $xml;
    }

    private static function canonicalJson(array $settings): string
    {
        ksort($settings);

        $normalized = [];

        foreach ($settings as $key => $value) {
            $normalized[$key] = $value;
        }

        return json_encode($normalized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '';
    }

    public static function sebBinary(string $plistXml): string
    {
        $compressedPlist = gzencode($plistXml, 9);

        if ($compressedPlist === false) {
            throw new RuntimeException('Gagal mengompresi XML konfigurasi SEB.');
        }

        $sebContent = gzencode('plnd'.$compressedPlist, 9);

        if ($sebContent === false) {
            throw new RuntimeException('Gagal membuat file konfigurasi SEB.');
        }

        return $sebContent;
    }
}
