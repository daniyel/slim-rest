<?php

namespace Src\Controller;

use Slim\Http\Request;
use Slim\Http\Response;
use Exception;

final class UserController extends BaseController {
    public function read(Request $request, Response $response, array $args) {

        $id = $request->getAttribute('route')->getArgument('id');
        $this->logger->info('GET /users/' . $id . ' route');

        try {
            $user = $this->userRepository->getById($id);
        } catch (\Exception $e) {
            return $response->withJson(
                array(
                    'status' => 'error',
                    'message' => $e->getMessage()
                ),
                400
            );
        }
        return $response->withJson($user);
    }

    public function readRoles(Request $request, Response $response, array $args) {

        $id = $request->getAttribute('route')->getArgument('id');
        $this->logger->info('POST /user/' . $id . '/roles route');

        try {
            $roles = $this->userRepository->getRoles($id);
        } catch (\Exception $e) {
            return $response->withJson(
                array(
                    'status' => 'error',
                    'message' => $e->getMessage()
                ),
                400
            );
        }

        return $response->withJson($roles);
    }

    public function attachRole(Request $request, Response $response, array $args) {

        $userId = $request->getAttribute('route')->getArgument('id');
        $roleId = $request->getAttribute('route')->getArgument('role_id');
        $this->logger->info('POST /users/' . $userId . '/roles/' . $roleId . ' route');

        try {
            $this->userRepository->assignRoleToUser($roleId, $userId);
        } catch (\Exception $e) {
            return $response->withJson(
                array(
                    'status' => 'error',
                    'message' => $e->getMessage()
                ),
                400
            );
        }

        return $response->withStatus(200);
    }

    public function delete(Request $request, Response $response, array $args) {

        $id = $request->getAttribute('route')->getArgument('id');
        $this->logger->info('DELETE /users/' . $id . ' route');

        try {
            $this->userRepository->deleteById($id);
        } catch (\Exception $e) {
            return $response->withJson(
                array(
                    'status' => 'error',
                    'message' => $e->getMessage()
                ),
                400
            );
        }

        return $response->withStatus(200);
    }
}
