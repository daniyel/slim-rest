<?php

namespace Src\Model;

use Slim\Container;
use Src\Model\UserModel;
use Src\Helper\Crypto;
use Src\Helper\JWT;
use Exception;

final class AuthModel extends BaseModel {

    public function __construct(Container $c, UserModel $userRepository) {
        parent::__construct($c);
        $this->userRepository = $userRepository;
    }

    /**
     * Create user in database
     *
     * @param array $body
     * @return integer
     *
     * @throws \Exception
     */
    public function createUser(array $body): int {
        try {
            $sql = 'INSERT INTO users (username, password) VALUES (:username, :password)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':username', $body['username'], SQLITE3_TEXT);
            $stmt->bindValue(':password', Crypto::hash($body['password']), SQLITE3_TEXT);
            $stmt->execute();
            return $this->db->lastInsertId();
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
     * Assign role to the user
     *
     * @param integer $roleId
     * @param integer $userId
     */
    private function assignRoleToUser(int $roleId, int $userId): void {
        $sql = 'INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':role_id', $roleId, SQLITE3_INTEGER);
        $stmt->execute();
    }

    /**
     * Generate JWT token from user data
     *
     * @param array $body
     * @return string
     *
     * @throws \Exception
     */
    public function login(array $body): string {
        try {
            $sql = 'SELECT * FROM users WHERE username = :username';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':username', $body['username'], SQLITE3_TEXT);
            $stmt->execute();

            $user = $stmt->fetch();

            if (empty($user)) {
                throw new Exception('User with username ' . $body['username'] . ' does not exist.');
            }

            if (!Crypto::compare($user['password'], $body['password'])) {
                throw new Exception('Wrong credentials.');
            }

            $roles = $this->userRepository->getRoles($user['id']);

            if (empty($roles)) {
                throw new Exception('User with username ' . $body['username'] . ' does not have any roles assigned.');
            }

            $token = JWT::getToken([
                'user_id' => $user['id'],
                'user_roles' => $roles
            ]);

            return $token;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
}
