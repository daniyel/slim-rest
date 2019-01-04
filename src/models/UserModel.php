<?php

namespace Src\Model;

use Exception;

final class UserModel extends BaseModel {

    /**
     * Retrieve user from database
     *
     * @param integer $id
     * @return array
     *
     * @throws \Exception
     */
    public function getById(int $id): array {
        try {
            $sql = 'SELECT id, username FROM users WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $user = $stmt->fetch();

            if (empty($user)) {
                throw new Exception('User with id ' . $id . ' does not exist.');
            }

            return $user;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Retrieve all roles of an user
     *
     * @param integer $id
     * @return array
     *
     * @throws \Exception
     */
    public function getRoles(int $id): array {
        try {
            $sql = 'SELECT r.id AS id, r.role AS role FROM users u '
                . 'JOIN user_roles ur ON ur.user_id = u.id '
                . 'JOIN roles r ON r.id = ur.role_id WHERE u.id = :id';

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            $stmt->execute();

            $roles = [];

            while($row = $stmt->fetch()) {
                var_dump($row);
                var_dump($id);
                $roles[] = $row['role'];
            }

            return $roles;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Assign role to an user
     *
     * @param integer $roleId
     * @param integer $userId
     *
     * @throws \Exception
     */
    public function assignRoleToUser(int $roleId, int $userId): void {
        try {
            $sql = 'INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':role_id', $roleId, SQLITE3_INTEGER);
            $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
            $stmt->execute();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Delete user from database
     *
     * @param integer $id
     *
     * @throws \Exception
     */
    public function deleteById(int $id): void {
        try {
            $this->db->exec('PRAGMA foreign_keys = ON');
            $this->db->beginTransaction();
            $this->deleteUser($id);
            // we need to "reset" the orders so newly registered
            // users do not get the orders of the old users
            $this->updateUserOrders($id);
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            $this->logger->error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param integer $id
     * @return boolean
     */
    private function deleteUser(int $id): bool {
        $sql = 'DELETE FROM users WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
    }

    /**
     * @param integer $id
     * @return boolean
     */
    private function updateUserOrders(int $id): bool {
        $sql = 'UPDATE orders SET user_id = 0 WHERE user_id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        return $stmt->execute();
    }
}
