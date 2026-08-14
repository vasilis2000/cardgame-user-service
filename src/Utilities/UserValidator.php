<?php

declare(strict_types=1);

namespace App\Utilities;

class UserValidator
{
    public static function validateUser(array $data): array
    {
        $errors = [];

        $username = is_string($data['username'] ?? '') ? trim($data['username']) : '';
        $password = is_string($data['password'] ?? '') ? trim($data['password']) : '';

        if ($username === '') {
            $errors[] = 'Username is required.';
        } else {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                $errors[] = 'Username may only contain letters, numbers, and underscores.';
            }
            if (strlen($username) < 6) {
                $errors[] = 'Username must be at least 6 characters.';
            }
            if (strlen($username) > 32) {
                $errors[] = 'Username must be at most 32 characters.';
            }
        }

        if ($password === '') {
            $errors[] = 'Password is required.';
        } else {
            if (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters.';
            }
            if (strlen($password) > 72) {
                $errors[] = 'Password must be at most 72 characters.';
            }
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }
}
