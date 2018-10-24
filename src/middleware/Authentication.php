<?php

namespace Src\Middleware;

use Slim\Http\Request;
use Slim\Http\Response;
use Exception;

class Authentication {

    private $EXCLUDE_PATHS = [
        'POST ^\/auth\/register$',
        'POST ^\/auth\/login$',
        'POST ^\/users\/\d+\/roles\/\d+$'
    ];

    public function __invoke(Request $request, Response $response, callable $next) {

        if ($this->checkPath($request->getMethod(), $request->getUri()->getPath())) {
            return $next($request, $response);
        } else if ($request->hasHeader('Authorization')) {
            $authHeader = $request->getHeader('Authorization');
            $authHeaderParts = explode(' ', $authHeader[0]);

            if (empty($authHeaderParts[0]) || $authHeaderParts[0] !== 'Bearer' || empty($authHeaderParts[1])) {
                return $response->withJson(
                    array(
                        'status' => 'error',
                        'message' => 'Invalid Authorization header.'
                    ),
                    400
                );
            }

            return $next($request, $response);
        }

        return $response->withStatus(401)
            ->write('Unauthenticated');
    }

    /**
     * @param string $method
     * @param string $path
     * @return boolean
     */
    private function checkPath(string $method, string $path): bool {
        foreach ($this->EXCLUDE_PATHS as $excludedPath) {
            $rule = explode(' ', $excludedPath);
            preg_match('/' . $rule[1] . '/', $path, $matches);

            if ($rule[0] === $method && !empty($matches)) {
                return true;
            }
        }
        return false;
    }
}
