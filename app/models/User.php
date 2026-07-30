<?php
require_once __DIR__ . '/../db.php';

class User
{
    private $db;

    public function __construct()
    {
        $this->db = new myDB();
    }

    // Find a user by email.
    public function findByEmail($email)
    {
        $result = $this->db->select('users', '*', $email);
        return $result ? $result->fetch_assoc() : null;
    }

    public function create($name, $email, $hashedPassword)
    {
        return $this->db->insert('users', [
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword
        ]);
    }

    public function verifyPassword($plainPassword, $hashedPassword)
    {
        return password_verify($plainPassword, $hashedPassword);
    }
}