<?php
require_once __DIR__ . '/../models/Supplier.php';
require_once __DIR__ . '/../db.php';


class SupplierController
{
    private $supplierModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->supplierModel = new Supplier();
    }

    public function createSupplier(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $errors = $this->supplierModel->validateSupplier($data);

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $errors
            ]);
            return;
        }

        $this->supplierModel->create($data);

        http_response_code(201); // 201 Created
        echo json_encode([
            'status' => 'success',
            'message' => 'Supplier created successfully.'
        ]);
        return;
    }


    public function deleteSupplier($data): void
    {

    }
}