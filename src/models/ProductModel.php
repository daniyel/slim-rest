<?php

namespace Src\Model;

use Src\Helper\Money;
use Exception;

final class ProductModel extends BaseModel {

    /**
     * Save product into database
     *
     * @param array $body
     * @return integer
     *
     * @throws \Exception
     */
    public function save(array $body): int {
        try {
            $sql = 'INSERT INTO products (name, price, discount, discount_type) VALUES (:name, :price, :discount, :discount_type)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':price', Money::prepareForDb($body['price']), SQLITE3_INTEGER);
            $stmt->bindValue(':name', $body['name'], SQLITE3_TEXT);
            $stmt->bindValue(':discount', Money::prepareForDb($body['discount']), SQLITE3_INTEGER);
            $stmt->bindValue(':discount_type', $body['discountType'], SQLITE3_TEXT);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Retrieve product from database
     *
     * @param integer $id
     * @return array
     *
     * @throws \Exception
     */
    public function getById(int $id): array {
        try {
            $sql = 'SELECT * FROM products WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $product = $stmt->fetch();

            if (empty($product)) {
                throw new Exception('Product with id ' . $id . ' does not exist.');
            }

            $product['price'] = Money::prepareForPresentation($product['price']);
            $product['discount'] = Money::prepareForPresentation($product['discount']);
            $product['discountType'] = $product['discount_type'];
            unset($product['discount_type']);

            return $product;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
}
