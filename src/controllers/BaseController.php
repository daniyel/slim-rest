<?php

namespace Src\Controller;

use Slim\Container;

class BaseController {
    protected $logger;
    protected $productRepository;
    protected $bundleRepository;
    protected $orderRepository;
    protected $authRepository;
    protected $userRepository;
    protected $roleRepository;

    public function __construct(Container $c) {
        $this->logger = $c->get('logger');
        $this->productRepository = $c->get('Src\Model\ProductModel');
        $this->bundleRepository = $c->get('Src\Model\BundleModel');
        $this->orderRepository = $c->get('Src\Model\OrderModel');
        $this->authRepository = $c->get('Src\Model\AuthModel');
        $this->userRepository = $c->get('Src\Model\UserModel');
        $this->roleRepository = $c->get('Src\Model\RoleModel');
    }
}
