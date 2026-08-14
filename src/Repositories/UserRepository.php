<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use App\Utilities\Database;

class UserRepository
{
    private PDO $pdo;
    private const DUMMY_HASH = '$2y$10$C6UzMDM.H6dfI/f/IKcEeO9nDp5MYqK7Wl2AZTUEHW3G.uQ4o.j6O';

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function create(string $username, string $password): int
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare('INSERT INTO users (username, password, isadmin) VALUES (?, ?, 0)');
        $stmt->execute([$username, $hash]);
        return (int) $this->pdo->lastInsertId();
    }

    public function findByUsernameAndPassword(string $username, string $password): array|false
    {
        $stmt = $this->pdo->prepare('SELECT id, username, password, isadmin FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user === false) {
            $hashToCheck = self::DUMMY_HASH;
        } else {
            $hashToCheck = $user['password'];
        }

        $passwordOk = password_verify($password, $hashToCheck);
        if ($user && $passwordOk) {
            unset($user['password']);
            return $user;
        }
        return false;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, username, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
