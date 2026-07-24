<?php
require_once '../db.php';

class Supplier
{
    private $db;
    public function __construct()
    {
        $this->db = new myDB();
    }

    public function getSuppliers(): array
    {
        $result = $this->db->select('suppliers', '*', ['user_id' => $_SESSION['user_id']]);
        return $result ? $result->fetch_assoc() : null;
    }

    public function delete(): bool
    {
        $conditions = [
            'user_id' => $_SESSION['user_id'],
            'status' => 'pending',
            'country_id' => 12
        ];

        // Call the updated method (removed the '*' argument)
        $result = $this->db->delete('suppliers', $conditions);

        return $result; // Returns true or false
    }


    public function create($name, $email, $contactPhone): void
    {
        return $this->db->insert('suppliers', [
            'user_id' => $_SESSION['user_id'],
            'name' => $name,
            'contactPhone' => $contactPhone,
            'email' => $email,
        ]);
    }


    public function findByEmail($email): array
    {
        $conditions = [
            'user_id' => $_SESSION['user_id'],
            'email' => $email
        ];

        $result = $this->db->select('suppliers', '*', $conditions);
        return $result ? $result->fetch_assoc() : null;
    }



}