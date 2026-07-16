<?php

declare(strict_types=1);

namespace PIS\Services;

class EncryptionService
{
    private string $key;
    private string $cipher = 'aes-256-gcm';

    public function __construct()
    {
        $config = require BASE_PATH . '/config/config.php';
        $this->key = hash('sha256', $config['encryption_key'], true);
    }

    public function encrypt(string $data): string
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
        $tag = '';

        $encrypted = openssl_encrypt(
            $data,
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            16
        );

        return base64_encode($iv . $tag . $encrypted);
    }

    public function decrypt(string $encryptedData): string
    {
        $data = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length($this->cipher);

        $iv = substr($data, 0, $ivLength);
        $tag = substr($data, $ivLength, 16);
        $encrypted = substr($data, $ivLength + 16);

        $decrypted = openssl_decrypt(
            $encrypted,
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($decrypted === false) {
            throw new \RuntimeException('Decryption failed');
        }

        return $decrypted;
    }

    public function hashData(string $data): string
    {
        return hash_hmac('sha256', $data, $this->key);
    }
}
