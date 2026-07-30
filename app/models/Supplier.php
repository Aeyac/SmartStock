<?php
require_once __DIR__ . '/../db.php';


class Supplier
{
    private $db;

    public function __construct()
    {
        $this->db = new myDB();
    }

    public function getSuppliers(): ?array
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

        return (bool) $this->db->delete('suppliers', $conditions);
    }

    public function create(array $data): void
    {
        $this->db->insert('suppliers', [
            'user_id' => $_SESSION['user_id'],
            'name' => $data['name'],
            'contact_number' => $data['contact_number'],
            'email' => $data['email'],
        ]);
    }

    public function findByEmail(string $email): ?array
    {
        $conditions = [
            'user_id' => $_SESSION['user_id'],
            'email' => $email
        ];

        $result = $this->db->select('suppliers', '*', $conditions);
        return $result ? $result->fetch_assoc() : null;
    }

    public function validateSupplier(array $data): array
    {
        $errors = [];

        // Safely pull values without warnings
        $name = trim($data['name'] ?? '');
        $contact = trim($data['contact_number'] ?? '');
        $email = trim($data['email'] ?? '');

        if (empty($name)) {
            $errors['name'] = 'Supplier name is required.';
        }

        if (empty($contact)) {
            $errors['contact_number'] = 'Contact number is required.';
        } elseif (strlen($contact) > 15) {
            $errors['contact_number'] = 'Contact number must not exceed 15 digits.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        } elseif ($this->findByEmail($email)) {
            $errors['email'] = 'This supplier already exists.';
        }

        return $errors;
    }
}