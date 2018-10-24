<?php

namespace Src\Model;

use Exception;

final class RoleModel extends BaseModel {

    /**
     * Save role into database
     *
     * @param array $body
     * @return integer
     *
     * @throws \Exception
     */
    public function save(array $body): int {
        try {
            $sql = 'INSERT INTO roles (role) VALUES (:role)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':role', $body['role'], SQLITE3_TEXT);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Retrieve role from database
     *
     * @param integer $id
     * @return array
     *
     * @throws \Exception
     */
    public function getById(int $id): array {
        try {
            $sql = 'SELECT * FROM roles WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $role = $stmt->fetch();

            if (empty($role)) {
                throw new Exception('Role with id ' . $id . ' does not exist.');
            }

            return $role;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
}
