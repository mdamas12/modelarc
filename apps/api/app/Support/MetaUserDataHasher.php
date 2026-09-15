<?php

namespace App\Support;

/**
 * Normalizes and SHA-256 hashes user identifiers for Meta Conversions API.
 *
 * Hashing does NOT make personal data anonymous — it only meets Meta's
 * transport requirement for matched fields.
 */
final class MetaUserDataHasher
{
    /**
     * Common country names used by the public contact form → ISO 3166-1 alpha-2.
     *
     * @var array<string, string>
     */
    private const COUNTRY_MAP = [
        'venezuela' => 've',
        'españa' => 'es',
        'spain' => 'es',
        'colombia' => 'co',
        'méxico' => 'mx',
        'mexico' => 'mx',
        'estados unidos' => 'us',
        'united states' => 'us',
        'usa' => 'us',
        'argentina' => 'ar',
        'chile' => 'cl',
        'perú' => 'pe',
        'peru' => 'pe',
        'brasil' => 'br',
        'brazil' => 'br',
        'ecuador' => 'ec',
        'panama' => 'pa',
        'panamá' => 'pa',
        'canada' => 'ca',
        'canadá' => 'ca',
    ];

    public static function hashEmail(?string $email): ?string
    {
        $normalized = self::normalizeEmail($email);

        return $normalized === null ? null : self::sha256($normalized);
    }

    public static function hashPhone(?string $phone): ?string
    {
        $normalized = self::normalizePhone($phone);

        return $normalized === null ? null : self::sha256($normalized);
    }

    public static function hashNamePart(?string $value): ?string
    {
        $normalized = self::normalizeNamePart($value);

        return $normalized === null ? null : self::sha256($normalized);
    }

    public static function hashCity(?string $city): ?string
    {
        $normalized = self::normalizeCity($city);

        return $normalized === null ? null : self::sha256($normalized);
    }

    public static function hashState(?string $state): ?string
    {
        $normalized = self::normalizeState($state);

        return $normalized === null ? null : self::sha256($normalized);
    }

    public static function hashCountry(?string $country): ?string
    {
        $normalized = self::normalizeCountry($country);

        return $normalized === null ? null : self::sha256($normalized);
    }

    public static function normalizeEmail(?string $email): ?string
    {
        if ($email === null) {
            return null;
        }

        $normalized = strtolower(trim($email));

        return $normalized === '' ? null : $normalized;
    }

    public static function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        return $digits === '' ? null : $digits;
    }

    public static function normalizeNamePart(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtolower(trim($value));
        $normalized = preg_replace('/[^a-zà-ÿ\s]/u', '', $normalized) ?? '';
        $normalized = preg_replace('/\s+/', '', $normalized) ?? '';

        return $normalized === '' ? null : $normalized;
    }

    public static function normalizeCity(?string $city): ?string
    {
        if ($city === null) {
            return null;
        }

        $normalized = strtolower(trim($city));
        $normalized = preg_replace('/[^a-zà-ÿ0-9]/u', '', $normalized) ?? '';

        return $normalized === '' ? null : $normalized;
    }

    public static function normalizeState(?string $state): ?string
    {
        if ($state === null) {
            return null;
        }

        $trimmed = trim($state);
        if ($trimmed === '') {
            return null;
        }

        if (preg_match('/^[A-Za-z]{2}$/', $trimmed) === 1) {
            return strtolower($trimmed);
        }

        $normalized = strtolower($trimmed);
        $normalized = preg_replace('/[^a-zà-ÿ0-9]/u', '', $normalized) ?? '';

        return $normalized === '' ? null : $normalized;
    }

    public static function normalizeCountry(?string $country): ?string
    {
        if ($country === null) {
            return null;
        }

        $trimmed = trim($country);
        if ($trimmed === '') {
            return null;
        }

        if (preg_match('/^[A-Za-z]{2}$/', $trimmed) === 1) {
            return strtolower($trimmed);
        }

        $key = mb_strtolower($trimmed);

        return self::COUNTRY_MAP[$key] ?? null;
    }

    /**
     * Split a full name into first / last for Meta fn / ln.
     *
     * @return array{0: ?string, 1: ?string}
     */
    public static function splitName(?string $fullName): array
    {
        if ($fullName === null) {
            return [null, null];
        }

        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        $parts = array_values(array_filter($parts, fn ($p) => $p !== ''));

        if ($parts === []) {
            return [null, null];
        }

        $first = $parts[0];
        $last = count($parts) > 1 ? $parts[count($parts) - 1] : null;

        return [$first, $last];
    }

    private static function sha256(string $value): string
    {
        return hash('sha256', $value);
    }
}
