<?php

namespace Src\Model;

use Src\Helper\Money;
use Exception;

final class BundleModel extends BaseModel {

    /**
     * Save bundle into database
     *
     * @param array $body
     * @return integer
     *
     * @throws \Exception
     */
    public function save(array $body): int {
        try {
            $this->db->exec('PRAGMA foreign_keys = ON');
            $this->db->beginTransaction();
            $bundleId = $this->insertBundle($body);

            // associate product with the bundle
            $productIds = $body['products'];
            foreach ($productIds as $productId) {
                $this->assignProductToBundle($productId, $bundleId);
            }

            $this->db->commit();
            return $bundleId;
        } catch (\Exception $e) {
            $this->db->rollback();
            $this->logger->error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Insert bundle into database
     *
     * @param array $body
     * @return integer
     */
    private function insertBundle(array $body): int {
        $sql = 'INSERT INTO bundles (name, price) VALUES (:name, :price)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':price', Money::prepareForDb($body['price']), SQLITE3_INTEGER);
        $stmt->bindValue(':name', $body['name'], SQLITE3_TEXT);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Assign product to the bundle
     *
     * @param integer $productId
     * @param integer $bundleId
     */
    private function assignProductToBundle(int $productId, int $bundleId): array {
        $sql = 'INSERT INTO product_bundles (product_id, bundle_id) VALUES (:product_id, :bundle_id)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':product_id', $productId, SQLITE3_INTEGER);
        $stmt->bindValue(':bundle_id', $bundleId, SQLITE3_INTEGER);
        $stmt->execute();
    }

    /**
     * Retrieve single bundle from database
     *
     * @param integer $id
     * @return array
     *
     * @throws \Exception
     */
    public function getById(int $id): array {
        try {
            $sql = 'SELECT b.id AS bundle_id, b.name AS bundle_name, b.price AS bundle_price,'
                . ' p.id AS product_id, p.name AS product_name FROM bundles b '
                . 'JOIN product_bundles pb ON pb.bundle_id = b.id '
                . 'JOIN products p ON p.id = pb.product_id WHERE b.id = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $bundle = [];
            $products = [];

            while($row = $stmt->fetch()) {
                if (empty($bundle)) {
                    $bundle = [
                        'id' => $row['bundle_id'],
                        'name' => $row['bundle_name'],
                        'price' => Money::prepareForPresentation($row['bundle_price']),
                    ];
                }

                $products[] = [
                    'id' => $row['product_id'],
                    'name' => $row['product_name']
                ];
            }

            $bundle['products'] = $products;

            if (empty($bundle)) {
                throw new Exception('Bundle with id ' . $id . ' does not exist.');
            }

            return $bundle;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
}
