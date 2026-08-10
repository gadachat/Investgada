<?php

namespace App\Services;

/**
 * TOTP (Time-based One-Time Password) service.
 * Implements RFC 6238 using PHP's built-in hash_hmac — no external packages required.
 */
class TotpService
{
    private const ALGORITHM = 'sha1';
    private const DIGITS    = 6;
    private const STEP      = 30; // seconds

    /**
     * Generate a random base32-encoded secret.
     */
    public static function generateSecret(int $length = 32): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // Base32 alphabet
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Build the otpauth:// URI for QR code generation.
     */
    public static function getQrCodeUrl(string $secret, string $email, string $issuer = 'Platform'): string
    {
        $label = rawurlencode($issuer . ':' . $email);
        $params = http_build_query([
            'secret'   => $secret,
            'issuer'   => $issuer,
            'algorithm'=> 'SHA1',
            'digits'   => self::DIGITS,
            'period'   => self::STEP,
        ]);

        return "otpauth://totp/{$label}?{$params}";
    }

    /**
     * Generate a Google Chart API QR URL (no JS needed, renders as <img>).
     */
    public static function getQrImageTag(string $secret, string $email, string $issuer = 'Platform'): string
    {
        $url = self::getQrCodeUrl($secret, $email, $issuer);
        // Use api.qrserver.com for QR generation
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=' . rawurlencode($url);
        return '<img src="' . $qrUrl . '" width="240" height="240" alt="2FA QR Code" class="rounded-lg" />';
    }

    /**
     * Verify a TOTP code against the secret.
     * Allows ±1 time step (±30 seconds) for clock drift.
     */
    public static function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\s+/', '', $code);

        if (strlen($code) !== self::DIGITS || !ctype_digit($code)) {
            return false;
        }

        $binarySecret = self::base32Decode($secret);
        $timestamp = time();
        $counter = (int) floor($timestamp / self::STEP);

        // Check current and adjacent windows
        for ($i = -$window; $i <= $window; $i++) {
            $testCounter = $counter + $i;
            $testCode = self::generateTotp($binarySecret, $testCounter);

            if (hash_equals($testCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate the TOTP code for a given counter.
     */
    private static function generateTotp(string $binarySecret, int $counter): string
    {
        $time = pack('N', 0) . pack('N', $counter); // 8-byte big-endian
        $hash = hash_hmac(self::ALGORITHM, $time, $binarySecret, true);

        // Dynamic truncation
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $code = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        );

        $code = $code % (10 ** self::DIGITS);

        return str_pad((string) $code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Decode a base32 string to binary.
     */
    private static function base32Decode(string $b32): string
    {
        $b32 = strtoupper($b32);
        $map = array_flip(array_combine(
            range('A', 'Z'),
            range(0, 25)
        ));

        // Add digits 2-7
        for ($i = 2; $i <= 7; $i++) {
            $map[(string) $i] = 26 + ($i - 2);
        }

        $binary = '';
        foreach (str_split($b32) as $char) {
            if (!isset($map[$char])) continue;
            $binary .= str_pad(decbin($map[$char]), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        $chunks = str_split($binary, 8);
        foreach ($chunks as $chunk) {
            if (strlen($chunk) === 8) {
                $bytes .= chr(bindec($chunk));
            }
        }

        return $bytes;
    }

    /**
     * Generate recovery codes (one-time use).
     */
    public static function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(substr(md5(random_bytes(16)), 0, 4) . '-' . substr(md5(random_bytes(16)), 0, 4));
        }
        return $codes;
    }
}
