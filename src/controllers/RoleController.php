<?php

namespace Src\Controller;

use Slim\Http\Request;
use Slim\Http\Response;
use Exception;

final class RoleController extends BaseController {
    public function create(Request $request, Response $response, array $args) {

        $this->logger->info('POST /roles route');

        $body = $request->getParsedBody();

        try {
            $roleId = $this->roleRepository->save($body);
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
            ->withHeader('Location', '/roles/' . $roleId);
    }
}
