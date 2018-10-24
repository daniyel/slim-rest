<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// PDO database library
$container['db'] = function ($c) {
    $settings = $c->get('settings')['db'];
    $dbname = preg_match('/^:memory:$/', $settings['dbname']) ? $settings['dbname'] : dirname($_SERVER['DOCUMENT_ROOT']) . '/' . $settings['dbname'] . '.sqlite3';
    $pdo = new PDO('sqlite:' . $dbname);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

// Controller factories
// TODO: autoload classes from directory
$container['Src\Controller\ProductController'] = function ($c) {
    return new Src\Controller\ProductController($c);
};
$container['Src\Controller\BundleController'] = function ($c) {
    return new Src\Controller\BundleController($c);
};
$container['Src\Controller\OrderController'] = function ($c) {
    return new Src\Controller\OrderController($c);
};
$container['Src\Controller\AuthController'] = function ($c) {
    return new Src\Controller\AuthController($c);
};
$container['Src\Controller\UserController'] = function ($c) {
    return new Src\Controller\UserController($c);
};
$container['Src\Controller\RoleController'] = function ($c) {
    return new Src\Controller\RoleController($c);
};

// Model factories
// TODO: autoload classes from directory
$container['Src\Model\ProductModel'] = function ($c) {
    return new Src\Model\ProductModel($c);
};
$container['Src\Model\BundleModel'] = function ($c) {
    return new Src\Model\BundleModel($c);
};
$container['Src\Model\OrderModel'] = function ($c) {
    return new Src\Model\OrderModel($c);
};
$container['Src\Model\UserModel'] = function ($c) {
    return new Src\Model\UserModel($c);
};
$container['Src\Model\AuthModel'] = function ($c) {
    return new Src\Model\AuthModel($c, new Src\Model\UserModel($c));
};
$container['Src\Model\RoleModel'] = function ($c) {
    return new Src\Model\RoleModel($c);
};
