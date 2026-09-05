<?php
declare(strict_types=1);

/**
 * COMMERCE ENV LOADER & CONFIGURATION SERVICE
 * Loads environment variables from .env file securely without leaking to client
 */
class CommerceEnv {
    private static ?array $cache = null;

    public static function load(?string $filePath = null): void {
        if (self::$cache !== null) {
            return;
        }

        self::$cache = [];
        $envPath = $filePath ?? (__DIR__ . '/../../.env');

        if (file_exists($envPath) && is_readable($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                if (str_contains($line, '=')) {
                    [$key, $val] = explode('=', $line, 2);
                    $key = trim($key);
                    $val = trim($val);
                    // Strip quotes if present
                    if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                        (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                        $val = substr($val, 1, -1);
                    }
                    self::$cache[$key] = $val;
                    if (!isset($_ENV[$key])) {
                        $_ENV[$key] = $val;
                    }
                    putenv("{$key}={$val}");
                }
            }
        }
    }

    public static function get(string $key, ?string $default = null): ?string {
        self::load();
        return self::$cache[$key] ?? $_ENV[$key] ?? getenv($key) ?: $default;
    }

    public static function getBool(string $key, bool $default = false): bool {
        $val = strtolower((string)self::get($key));
        if ($val === 'true' || $val === '1' || $val === 'yes') return true;
        if ($val === 'false' || $val === '0' || $val === 'no') return false;
        return $default;
    }

    public static function isLivePayPal(): bool {
        return strtolower(self::get('PAYPAL_MODE', 'live')) === 'live';
    }

    public static function getPayPalBaseUrl(): string {
        return self::isLivePayPal()
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }
}

if (!class_exists('Dependex\\Commerce\\CommerceEnv', false)) {
    class_alias('CommerceEnv', 'Dependex\\Commerce\\CommerceEnv');
}

