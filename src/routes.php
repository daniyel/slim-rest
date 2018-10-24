<?php

use Src\Middleware\Authorization;

$authMiddleware = new Authorization();

// Routes
$app->post('/products', 'Src\Controller\ProductController:create')
    ->add($authMiddleware->withRequiredRole(['admin']));
$app->get('/products/{id}', 'Src\Controller\ProductController:read');

$app->post('/bundles', 'Src\Controller\BundleController:create')
    ->add($authMiddleware->withRequiredRole(['admin']));
$app->get('/bundles/{id}', 'Src\Controller\BundleController:read')
    ->add($authMiddleware->withRequiredRole(['admin', 'customer']));
$app->get('/bundles/{id}/products', 'Src\Controller\BundleController:readProducts')
    ->add($authMiddleware->withRequiredRole(['admin', 'customer']));

$app->post('/orders', 'Src\Controller\OrderController:create')
    ->add($authMiddleware->withRequiredRole(['admin', 'customer']));
$app->get('/orders/{id}', 'Src\Controller\OrderController:read')
    ->add($authMiddleware->withRequiredRole(['admin', 'customer']));
$app->get('/orders/{id}/products', 'Src\Controller\OrderController:readProducts')
    ->add($authMiddleware->withRequiredRole(['admin', 'customer']));
$app->get('/orders/{id}/bundles', 'Src\Controller\OrderController:readBundles')
    ->add($authMiddleware->withRequiredRole(['admin', 'customer']));

$app->post('/roles', 'Src\Controller\RoleController:create')
    ->add($authMiddleware->withRequiredRole(['admin']));

$app->get('/users', 'Src\Controller\UserController:read')
    ->add($authMiddleware->withRequiredRole(['admin']));
$app->get('/users/{id}/roles', 'Src\Controller\UserController:readRoles')
    ->add($authMiddleware->withRequiredRole(['admin']));
$app->post('/users/{id}/roles/{role_id}', 'Src\Controller\UserController:attachRole')
    ->add($authMiddleware->withRequiredRole(['admin']));
$app->delete('/users/{id}', 'Src\Controller\UserController:delete')
    ->add($authMiddleware->withRequiredRole(['admin']));

$app->post('/auth/register', 'Src\Controller\AuthController:register');
$app->post('/auth/login', 'Src\Controller\AuthController:login');
