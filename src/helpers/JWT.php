<?php

namespace Src\Helper;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Parser;
use InvalidArgumentException;
use DateTime;

class JWT {

    private static $ISSUER = 'http://example.com';
    private static $AUDIENCE = 'http://example.org';
    // this should be done differently, for this purpose we will leave it as it is
    private static $SIGNATURE_KEY = 'ch4ng3m3';

    /**
     * @param array $data
     *
     * @return string
     */
    public static function getToken(array $data): string {
        $signer = new Sha256();
        $jti = substr(bin2hex(random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES)), 0, 11);

        $token = (new Builder())->setIssuer(self::$ISSUER)
                        ->setAudience(self::$AUDIENCE)
                        ->setId($jti, true)
                        ->setIssuedAt(time())
                        ->setNotBefore(time() + 60)
                        ->setExpiration(time() + 3600)
                        ->set('user_id', $data['user_id'])
                        ->set('user_roles', $data['user_roles'])
                        ->sign($signer, self::$SIGNATURE_KEY)
                        ->getToken();
        return $token;
    }

    /**
     * @param string $jwt
     * @return boolean
     *
     * @throws \InvalidArgumentException
     */
    public static function verify(string $jwt): bool {
        $token = (new Parser())->parse($jwt);
        $signer = new Sha256();
        return $token->verify($signer, self::$SIGNATURE_KEY);
    }

    /**
     * @param string $jwt
     *
     * @return boolean
     */
    public static function isExpired(string $jwt): bool {
        $token = (new Parser())->parse($jwt);
        return $token->isExpired(new DateTime());
    }

    /**
     * @param string $jwt
     *
     * @return array
     */
    public static function getUserRoles(string $jwt): array {
        $token = (new Parser())->parse($jwt);
        return $token->getClaim('user_roles');
    }

    /**
     * @param string $jwt
     *
     * @return integer
     */
    public static function getUserId(string $jwt): int {
        $token = (new Parser())->parse($jwt);
        return intval($token->getClaim('user_id'));
    }
}
