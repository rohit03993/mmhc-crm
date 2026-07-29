<?php

/**
 * Optional OpenSSL EC diagnostic (not required for setup).
 * Prefer: php artisan webpush:setup
 */

$config = [
    'curve_name' => 'prime256v1',
    'private_key_type' => OPENSSL_KEYTYPE_EC,
];

$key = openssl_pkey_new($config);

echo 'openssl_pkey_new EC: '.($key !== false ? 'OK' : 'FAILED').PHP_EOL;

while ($e = openssl_error_string()) {
    echo $e, PHP_EOL;
}
