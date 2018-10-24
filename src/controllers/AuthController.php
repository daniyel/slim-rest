<?php

namespace Src\Controller;

use Slim\Http\Request;
use Slim\Http\Response;
use Exception;

final class AuthController extends BaseController {
    public function register(Request $request, Response $response, array $args) {

        $this->logger->info('POST /auth/register route');

        $body = $request->getParsedBody();

        try {
            $userId = $this->authRepository->createUser($body);
        } catch (\Exception $e) {
            return $response->withJson(
                array(
                    'status' => 'error',
                    'message' => $e->getMessage()
                ),
                400
            );
        }

        return $response->withStatus(201)
            ->withHeader('Location', '/users/' . $userId);
    }

    public function login(Request $request, Response $response, array $args) {

        $this->logger->info('POST /auth/login route');

        $body = $request->getParsedBody();

        try {
            $token = $this->authRepository->login($body);
        } catch (\Exception $e) {
            return $response->withJson(
                array(
                    'status' => 'error',
                    'message' => $e->getMessage()
                ),
                400
            );
        }

        return $response->write($token);
    }
}
