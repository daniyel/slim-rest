<?php

namespace Tests\Functional;

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Environment;
use Exception;

/**
 * This is an example class that shows how you could set up a method that
 * runs the application. Note that it doesn't cover all use-cases and is
 * tuned to the specifics of this skeleton app, so if your needs are
 * different, you'll need to change it.
 */
class BaseTestCase extends \PHPUnit_Framework_TestCase {
    protected $ADMIN_USERNAME = 'test@example.org';
    protected $ADMIN_PASSWORD = '246172676f6e32696424763d3139246d3d3236323134342c743d332c703d312457516a6870635179354655456b31466c494a686a4a67243347564e4349595a67696872744f437a6a4e416b6c7a6f4265776d4f4d3168435a49467464344647655067';
    /**
     * Use middleware when running application?
     *
     * @var bool
     */
    protected $withMiddleware = true;

    protected $container;

    /**
     * Process the application given a request method and URI
     *
     * @param string $requestMethod the request method (e.g. GET, POST, etc.)
     * @param string $requestUri the request URI
     * @param array $requestHeaders headers used in the request
     * @param array|object|null $requestData the request data
     * @param array $requestParams params used in the request
     * @return \Slim\Http\Response
     */
    public function runApp(
        string $requestMethod,
        string $requestUri,
        array $requestHeaders = null,
        array $requestData = null,
        array $requestParams = null
    ) {
        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => $requestMethod,
                'REQUEST_URI' => $requestUri
            ]
        );

        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);

        // Add request data, if it exists
        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }

        // Add query params, if it exists
        if (isset($requestParams)) {
            $request = $request->withQueryParams($requestParams);
        }

        // Add headers, if it exists
        if (isset($requestHeaders)) {
            foreach ($requestHeaders as $name => $value) {
                $request = $request->withHeader($name, $value);
            }
        }

        // Set up a response object
        $response = new Response();

        // Use the application settings
        $settings = require __DIR__ . '/../../src/settings.test.php';

        // Instantiate the application
        $app = new App($settings);

        // Set up dependencies
        require __DIR__ . '/../../src/dependencies.php';

        // Register middleware
        if ($this->withMiddleware) {
            require __DIR__ . '/../../src/middleware.php';
        }

        // Register routes
        require __DIR__ . '/../../src/routes.php';

        // Init DB
        $this->container = $app->getContainer();
        $this->initDb();

        $this->seedProducts();

        // Process the application
        $response = $app->process($request, $response);

        return $response;
    }

    public function closeConnection() {
        $db = $this->container->get('db');
        $db = null;
    }

    private function initDb() {
        $db = $this->container->get('db');
        $sqlFile = file_get_contents(__DIR__ . '/../../init.sql');
        $db->exec($sqlFile);

        try {
            $this->addAdminUser();
            $this->addAdminRole();
            $this->assignAdminRole();
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function addAdminUser() {
        $db = $this->container->get('db');
        $sql = 'INSERT INTO users (username, password) VALUES (:username, :password)';
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':username', $this->ADMIN_USERNAME, SQLITE3_TEXT);
        $stmt->bindValue(':password', $this->ADMIN_PASSWORD, SQLITE3_TEXT);
        $stmt->execute();
    }

    private function addAdminRole() {
        $db = $this->container->get('db');
        $sql = 'INSERT INTO roles (role) VALUES (:role)';
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':role', 'admin', SQLITE3_TEXT);
        $stmt->execute();
    }

    private function assignAdminRole() {
        $db = $this->container->get('db');
        $sql = 'INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)';
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', 1, SQLITE3_INTEGER);
        $stmt->bindValue(':role_id', 1, SQLITE3_INTEGER);
        $stmt->execute();
    }

    private function seedProducts() {
        $products = [
            [
                'name' => 'Product1',
                'price' => 10000,
                'discount' => 1000,
                'discount_type' => 'fixed'
            ],
            [
                'name' => 'Product2',
                'price' => 20000,
                'discount' => 3000,
                'discount_type' => 'fixed'
            ],
            [
                'name' => 'Product3',
                'price' => 15000,
                'discount' => 1500,
                'discount_type' => 'variable'
            ]
        ];
        $db = $this->container->get('db');
        $sql = 'INSERT INTO products (name, price, discount, discount_type) VALUES (:name, :price, :discount, :discount_type)';
        $stmt = $db->prepare($sql);

        foreach ($products as $key => $product) {

            $stmt->bindValue(':name', $product['name'], SQLITE3_TEXT);
            $stmt->bindValue(':price', $product['price'], SQLITE3_INTEGER);
            $stmt->bindValue(':discount', $product['discount'], SQLITE3_INTEGER);
            $stmt->bindValue(':discount_type', $product['discount_type'], SQLITE3_TEXT);
            $stmt->execute();
        }
    }

    protected function tearDown() {
        $this->closeConnection();
    }
}
