<?php

namespace App\Services;

/**
 * HmacHashService — Deterministic blind-index hashing for encrypted fields.
 *
 * PURPOSE
 * -------
 * When a field (e.g. email) is AES-encrypted with a random IV, every
 * ciphertext is different even for identical plain-text values.  That means
 * you CANNOT do  WHERE email = ?  against the encrypted column.
 *
 * The HIPAA-approved solution is a "blind index": store an HMAC-SHA256 of
 * the plain-text value alongside the encrypted ciphertext.  The HMAC is:
 *   • Deterministic  — same input + same key → same output, always.
 *   • Non-reversible — knowing the hash tells you nothing about the value.
 *   • Keyed          — an attacker who steals the DB but not the key cannot
 *                      brute-force the hashes.
 *
 * CONFIGURATION
 * -------------
 * Add to .env:
 *   HMAC_HASH_KEY=<random 32+ char secret, DIFFERENT from APP_KEY>
 *
 * USAGE
 * -----
 *   $hashService = app(HmacHashService::class);
 *   $hash = $hashService->hash('user@example.com');       // store this
 *   User::where('email_hash', $hash)->first();            // fast index lookup
 */
class HmacHashService
{
    /**
     * Compute a deterministic HMAC-SHA256 blind index.
     *
     * @param  string  $value  Plain-text value to hash (e.g. an email address)
     * @return string          64-char lowercase hex string
     */
    public function hash(string $value): string
    {
        $key = config('app.hmac_hash_key') ?? env('HMAC_HASH_KEY', config('app.key'));

        return hash_hmac('sha256', mb_strtolower(trim($value)), $key);
    }

    /**
     * Verify that a plain-text value matches a stored hash.
     *
     * @param  string  $value       Plain-text value to verify
     * @param  string  $storedHash  Previously computed hash from hash()
     * @return bool
     */
    public function verify(string $value, string $storedHash): bool
    {
        return hash_equals($storedHash, $this->hash($value));
    }
}
