<?php

namespace Src\Controller;

use Slim\Http\Request;
use Slim\Http\Response;
use Exception;

final class ProductController extends BaseController {
    public function create(Request $request, Response $response, array $args) {

        // Sample log message
        $this->logger->info('POST /products route');

        $body = $request->getParsedBody();

        try {
            $productId = $this->productRepository->save($body);
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
            ->withHeader('Location', '/products/' . $productId);
    }

    public function read(Request $request, Response $response, array $args) {

        // Sample log message
        $this->logger->info('GET /products route');

        $id = $request->getAttribute('route')->getArgument('id');

        try {
            $product = $this->productRepository->getById($id);
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
