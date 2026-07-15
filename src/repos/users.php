<?php
require_once __DIR__ . '/../helpers/Database.php';

class UserRepository
{
    private PDO $pdo;

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
        if ($user && password_verify($password, $user['password'])) {
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