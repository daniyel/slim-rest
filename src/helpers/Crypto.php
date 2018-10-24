<?php

namespace Src\Helper;


class Crypto {

    public static $OPSLIMIT = SODIUM_CRYPTO_PWHASH_OPSLIMIT_MODERATE;
    public static $MEMLIMIT = SODIUM_CRYPTO_PWHASH_MEMLIMIT_MODERATE;

    /**
     * @param string $password
     *
     * @return string
     */
    public static function hash(string $password): string {
        $hash = sodium_crypto_pwhash_str(
            $password,
            self::$OPSLIMIT,
            self::$MEMLIMIT
        );
        return bin2hex($hash);
    }

    /**
     * @param string $hash
     * @param string $password
     *
     * @return bool
     */
    public static function compare(string $hash, string $password): bool {
        if (sodium_crypto_pwhash_str_verify(hex2bin($hash), $password)) {
            sodium_memzero($password);
            return true;
        } else {
            sodium_memzero($password);
            return false;
        }
    }
}
