<?php

namespace Src\Controller;

use Slim\Http\Request;
use Slim\Http\Response;
use Exception;

final class OrderController extends BaseController {
    public function create(Request $request, Response $response, array $args) {

        // Sample log message
        $this->logger->info('POST /orders route');

        $body = $request->getParsedBody();
        $body['userId'] = $request->getAttribute('user_id');

        try {
            $productId = $this->orderRepository->save($body);
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
            ->withHeader('Location', '/orders/' . $productId);
    }

    public function read(Request $request, Response $response, array $args) {

        // Sample log message
        $this->logger->info('GET /orders route');

        $id = $request->getAttribute('route')->getArgument('id');

        try {
            $product = $this->orderRepository->getById($id);
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

    public function readProducts(Request $request, Response $response, array $args) {

        // Sample log message
        $id = $request->getAttribute('route')->getArgument('id');
        $this->logger->info('GET /orders/' . $id . '/products route');

        try {
            $products = $this->orderRepository->getProductsById($id);
        } catch (\Exception $e) {
            return $response->withJson(
                array(
                    'status' => 'error',
                    'message' => $e->getMessage()
                ),
                400
            );
        }
        return $response->withJson($products);
    }

    public function readBundles(Request $request, Response $response, array $args) {

        $id = $request->getAttribute('route')->getArgument('id');
        $this->logger->info('GET /orders/' . $id . '/bundles route');

        try {
            $bundles = $this->orderRepository->getBundlesById($id);
        } catch (\Exception $e) {
            return $response->withJson(
                array(
                    'status' => 'error',
                    'message' => $e->getMessage()
                ),
                400
            );
        }
        return $response->withJson($bundles);
    }
}
