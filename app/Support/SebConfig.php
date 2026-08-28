<?php

namespace App\Support;

use App\Models\Exam;
use Illuminate\Http\Request;

class SebConfig
{
    public static function settings(Request $request, Exam $exam): array
    {
        return [
            'startURL' => route('exam.seb'),
            'allowQuit' => false,
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
            'allowQuitApp' => false,
            'allowQuitAfterExam' => false,
            'mainWindowEnableClose' => false,
            'mainWindowFullScreen' => true,
            'browserWindowWebViewMode' => 3,
            'sendBrowserExamKey' => true,
            'showTime' => true,
        ];
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
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><plist version="1.0"><dict></dict></plist>');
        $dict = $xml->dict;

        foreach ($settings as $key => $value) {
            $dict->addChild('key', htmlspecialchars((string) $key, ENT_XML1 | ENT_COMPAT, 'UTF-8'));

            if (is_bool($value)) {
                $dict->addChild($value ? 'true' : 'false');

                continue;
            }

            if (is_int($value) || is_float($value) || ctype_digit((string) $value)) {
                $dict->addChild('integer', (string) $value);

                continue;
            }

            $node = $dict->addChild('string');
            $node[0] = htmlspecialchars((string) $value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
        }

        return $xml->asXML() ?: '';
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
}
