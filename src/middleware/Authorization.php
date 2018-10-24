<?php

namespace Src\Middleware;

use Slim\Http\Request;
use Slim\Http\Response;
use Src\Helper\JWT;
use InvalidArgumentException;

class Authorization {

    /**
     * @var array
     */
    private $roles;

    public function __construct() {

    }

    /**
     * @param Slim\Http\Request $request
     * @param Slim\Http\Response $response
     * @param callable $next
     * @return Slim\Http\Response
     *
     */
    public function __invoke(Request $request, Response $response, callable $next) {
        $authHeader = $request->getHeader('Authorization');

        if (empty($authHeader)) {
            return $response->withStatus(403)
                ->write('Forbidden');
        }

        $authHeaderParts = explode(' ', $authHeader[0]);
        $jwt = $authHeaderParts[1] ?? null;

        try  {
            if (!empty($jwt) && JWT::verify($jwt) && !JWT::isExpired($jwt) && $this->checkRoles($jwt)) {
                $request = $request->withAttribute('user_id', $this->getUserId($jwt));
                return $next($request, $response);
            }
        } catch (\InvalidArgumentException $e) {
            return $response->withJson(
                array(
                    'status' => 'error',
                    'message' => $e->getMessage()
                ),
                400
            );
        }
        return $response->withStatus(403)
            ->write('Forbidden');
    }

    /**
     * @param array $roles
     *
     * @return Src\Middleware\Authorization
     */
    public function withRequiredRole(array $roles): Authorization {
        $clone = clone $this;
        $clone->roles = $roles;
        return $clone;
    }

    /**
     * @param string $jwt
     *
     * @return boolean
     */
    private function checkRoles(string $jwt): bool {
        $userRoles = JWT::getUserRoles($jwt);

        foreach ($userRoles as $userRole) {
            if (in_array($userRole, $this->roles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $jwt
     *
     * @return integer
     */
    private function getUserId(string $jwt): int {
        return JWT::getUserId($jwt);
    }
}
