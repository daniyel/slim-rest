<?php

namespace Src\Model;

use Src\Helper\Money;
use Exception;

final class OrderModel extends BaseModel {

    /**
     * Save order into database
     *
     * @param array $body
     * @return integer
     *
     * @throws \Exception
     */
    public function save($body) {
        try {
            $this->db->exec('PRAGMA foreign_keys = ON');
            $this->db->beginTransaction();
            $orderId = $this->insertOrder($body);

            // associate product with the order
            $productIds = [];
            if (array_key_exists('products', $body)) {
                $productIds = $body['products'];
                foreach ($productIds as $productId) {
                    $this->assignProductToOrder($productId, $orderId);
                }
            }

            // associate bundles with the order
            $bundleIds = [];
            if (array_key_exists('bundles', $body)) {
                $bundleIds = $body['bundles'];
                foreach ($bundleIds as $bundleId) {
                    $this->assignBundleToOrder($bundleId, $orderId);
                }
            }

            // update order total price
            $totalPrice = $this->calculateProductsTotalPrice($productIds);
            $totalPrice += $this->calculateBundlesTotalPrice($bundleIds);
            $this->updateTotalPrice($orderId, $totalPrice);

            $this->db->commit();
            return $orderId;
        } catch (\Exception $e) {
            $this->db->rollback();
            $this->logger->error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    private function updateTotalPrice($id, $price) {
        $sql = 'UPDATE orders SET total_price = :total_price WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':total_price', $price, SQLITE3_INTEGER);
        return $stmt->execute();
    }

    /**
     * Insert order into database
     *
     * @param array $body
     * @return integer
     */
    private function insertOrder(array $body): int {
        $sql = 'INSERT INTO orders (total_price, user_id) VALUES (:total_price, :user_id)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':total_price', 0, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $body['userId'], SQLITE3_INTEGER);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Calculate total price of products in the order
     *
     * @param array $ids
     * @return integer
     */
    private function calculateProductsTotalPrice(array $ids): int {
        $totalPrice = 0;

        if (empty($ids)) {
            return $totalPrice;
        }

        $in  = str_repeat('?,', count($ids) - 1) . '?';
        $sql = 'SELECT * FROM products WHERE id IN (' . $in . ')';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($ids);
        $products = $stmt->fetchAll();

        foreach ($products as $product) {
            if ($product['discount_type'] === 'fixed') {
                $totalPrice += ($product['price'] - $product['discount']);
            } else {
                $totalPrice += ($product['price'] * (1 - $product['discount'] / 10000));
            }
        }
        return $totalPrice;
    }

    /**
     * Calculate total price of bundles in the order
     *
     * @param array $ids
     * @return integer
     */
    private function calculateBundlesTotalPrice(array $ids): int {
        $totalPrice = 0;

        if (empty($ids)) {
            return $totalPrice;
        }

        $in  = str_repeat('?,', count($ids) - 1) . '?';
        $sql = 'SELECT * FROM bundles WHERE id IN (' . $in . ')';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($ids);
        $bundles = $stmt->fetchAll();

        foreach ($bundles as $bundle) {
            $totalPrice += $bundle['price'];
        }
        return $totalPrice;
    }

    /**
     * Assign product to the order
     *
     * @param integer $productId
     * @param integer $orderId
     */
    private function assignProductToOrder(int $productId, int $orderId) {
        $sql = 'INSERT INTO product_orders (product_id, order_id) VALUES (:product_id, :order_id)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':product_id', $productId, SQLITE3_INTEGER);
        $stmt->bindValue(':order_id', $orderId, SQLITE3_INTEGER);
        $stmt->execute();
    }

    /**
     * Assign bundle to the order
     *
     * @param integer $bundleId
     * @param integer $orderId
     */
    private function assignBundleToOrder(int $bundleId, int $orderId) {
        $sql = 'INSERT INTO bundle_orders (bundle_id, order_id) VALUES (:bundle_id, :order_id)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':bundle_id', $bundleId, SQLITE3_INTEGER);
        $stmt->bindValue(':order_id', $orderId, SQLITE3_INTEGER);
        $stmt->execute();
    }

    /**
     * Retrieve order from database
     *
     * @param integer $id
     * @return array
     *
     * @throws \Exception
     */
    public function getById(int $id): array {
        try {
            $sql = 'SELECT * FROM orders WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $order = $stmt->fetch();

            if (empty($order)) {
                throw new Exception('Order with id ' . $id . ' does not exist.');
            }


            $order['totalPrice'] = Money::prepareForPresentation($order['total_price']);
            unset($order['total_price']);

            return $order;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Retrieve products from order
     *
     * @param integer $id
     * @return array
     *
     * @throws \Exception
     */
    public function getProductsById(int $id): array {
        try {
            $sql = 'SELECT p.id AS product_id, p.name AS product_name FROM orders o '
                . 'JOIN product_orders po ON po.order_id = o.id '
                . 'JOIN products p ON p.id = po.product_id WHERE o.id = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $products = [];

            while($row = $stmt->fetch()) {
                $products[] = [
                    'id' => $row['product_id'],
                    'name' => $row['product_name']
                ];
            }

            if (empty($products)) {
                throw new Exception('Order with id ' . $id . ' does not exist or contain any products.');
            }

            return $products;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Retrieve bundles from order
     *
     * @param integer $id
     * @return array
     *
     * @throws \Exception
     */
    public function getBundlesById(int $id): array {
        try {
            $sql = 'SELECT b.id AS bundle_id, b.name AS bundle_name FROM orders o '
                . 'JOIN bundle_orders bo ON bo.order_id = o.id '
                . 'JOIN bundles b ON b.id = bo.bundle_id WHERE o.id = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $bundles = [];

            while($row = $stmt->fetch()) {
                $bundles[] = [
                    'id' => $row['bundle_id'],
                    'name' => $row['bundle_name']
                ];
            }

            if (empty($bundles)) {
                throw new Exception('Order with id ' . $id . ' does not exist or contain any bundles.');
            }

            return $bundles;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
}
