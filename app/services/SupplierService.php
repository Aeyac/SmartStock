<?php
require_once "../models/Supplier.php";
require_once "../db.php";

class SupplierService
{
    private $db;
    private $supplierModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = new myDB();
        $this->supplierModel = new Supplier();
    }

    public function addSupplier($data): bool
    {
        $name = isset($data['name']) ? trim($data['name']) : '';
        $contact_number = isset($data['contact_number']) ? trim($data['contact_number']) : '';
        $email = isset($data['email']) ? $data['email'] : '';

        



        return true;


    }





    
}