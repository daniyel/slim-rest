<?php

namespace Src\Controller;

use Slim\Http\Request;
use Slim\Http\Response;
use Exception;

final class BundleController extends BaseController {
    public function create(Request $request, Response $response, array $args) {

        $this->logger->info('POST /bundles route');

        $body = $request->getParsedBody();

        try {
            $bundleId = $this->bundleRepository->save($body);
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
            ->withHeader('Location', '/bundles/' . $bundleId);
    }

    public function read(Request $request, Response $response, array $args) {

        $this->logger->info('GET /bundles route');

        $id = $request->getAttribute('route')->getArgument('id');

        try {
            $bundle = $this->bundleRepository->getById($id);
        } catch (\Exception $e) {
            return $response->withJson(
                array(
                    'status' => 'error',
                    'message' => $e->getMessage()
                ),
                400
            );
        }
        return $response->withJson($bundle);
    }

    public function readProducts(Request $request, Response $response, array $args) {

        // Sample log message
        $id = $request->getAttribute('route')->getArgument('id');
        $this->logger->info('GET /bundles/' . $id . '/products route');

        try {
            $product = $this->orderRepository->getProductsById($id);
        } catch (\Exception $e) {
            return $response->withJson(
                array(
                    'status' => 'error',
                    'message' => $e->getMessage()
                ),
                400
            );
        }
        return $response->withJson($product);
    }
}
