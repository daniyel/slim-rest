<?php

namespace Tests\Functional;

class ProductTest extends BaseTestCase {

    protected $withMiddleware = false;

    protected function setUp() {}

    public function testGetProductById() {
        $response = $this->runApp('GET', '/products/1');

        $body = json_decode((string)$response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('name', $body);
        $this->assertArrayHasKey('price', $body);
        $this->assertArrayHasKey('discount', $body);
        $this->assertArrayHasKey('discountType', $body);
    }
}
