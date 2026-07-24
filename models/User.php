<?php
require_once __DIR__ . '/../db.php';

class User
{
    private $db;

    public function __construct()
    {
        $this->db = new myDB();
    }

    //  Find a user by email. Returns an associative array, or null if not found.
    public function findByEmail($email)
    {
        $result = $this->db->select('users', '*', ['email' => $email]);
        return $result ? $result->fetch_assoc() : null;
    }


    // Create a new user. Expects the password to ALREADY be hashed —
    // hashing is a security decision, not a data-storage one, so that
    // stays in Auth (or wherever the raw password is first received).

    public function create($name, $email, $hashedPassword)
    {
        return $this->db->insert('users', [
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword
        ]);
    }

    //Check whether a given plain-text password matches a user's stored hash.
    public function verifyPassword($plainPassword, $hashedPassword)
    {
        return password_verify($plainPassword, $hashedPassword);
    }
}