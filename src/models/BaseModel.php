<?php

namespace Src\Model;

use Slim\Container;

class BaseModel {
    protected $db;
    protected $logger;

    public function __construct(Container $c) {
        $this->db = $c->get('db');
        $this->logger = $c->get('logger');
    }
}
