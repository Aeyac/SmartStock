<?php

function validateSupplierInput($mydb, $userId, $data, $currentSupplierId = null)
{
    $name = trim($data['name'] ?? '');
    $contact = trim($data['contact_number'] ?? '');
    $email = trim($data['email'] ?? '');
    $status = (int) ($data['status'] ?? 1);

    $errors = [];

    // Name validation
    if ($name === '') {
        $errors['name'] = 'Supplier name is required.';
    }

    // Contact number validation
    if ($contact === '') {
        $errors['contact_number'] = 'Contact number is required.';
    } elseif (strlen($contact) > 15) {
        $errors['contact_number'] = 'Maximum of 15 digits.';
    } elseif (!preg_match('/^[0-9]+$/', $contact)) {
        $errors['contact_number'] = 'Contact number must contain digits only.';
    }

    // Email validation & uniqueness check
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email address.';
    } else {
        $mydb->select('suppliers', '*', [
            'email' => $email,
            'user_id' => $userId
        ]);

        $existing = $mydb->res ? $mydb->res->fetch_assoc() : null;

        // On CREATE: check if existing record found
        // On UPDATE: check if existing record found AND belongs to a DIFFERENT supplier
        if ($existing && (int) $existing['id'] !== (int) $currentSupplierId) {
            $errors['email'] = 'Supplier already exists.';
        }
    }

    return [
        'isValid' => empty($errors),
        'errors' => $errors,
        'data' => [
            'name' => $name,
            'contact_number' => $contact,
            'email' => $email,
            'status' => $status
        ]
    ];
}


function validateItemInput($mydb, $userId, $data, $currentItemId = null)
{
    $name = trim($data['name'] ?? '');
    $categoryId = (int) ($data['category_id'] ?? 0);
    $safetyStock = $data['safety_stock'] ?? null;
    $sellingPrice = $data['selling_price'] ?? null;

    $errors = [];

    // 1. Name validation & uniqueness per user
    if ($name === '') {
        $errors['name'] = 'Item name is required.';
    } else {
        $mydb->select('items', '*', [
            'name' => $name,
            'user_id' => $userId
        ]);
        $existing = $mydb->res ? $mydb->res->fetch_assoc() : null;

        if ($existing && (int) $existing['id'] !== (int) $currentItemId) {
            $errors['name'] = 'An item with this name already exists.';
        }
    }

    // 2. Category validation
    if ($categoryId <= 0) {
        $errors['category_id'] = 'Please select a valid category.';
    } else {
        $mydb->select('categories', 'id', [
            'id' => $categoryId,
            'user_id' => $userId
        ]);
        if (!$mydb->res || !$mydb->res->fetch_assoc()) {
            $errors['category_id'] = 'Selected category does not exist.';
        }
    }

    // 3. Safety Stock validation
    if ($safetyStock === null || $safetyStock === '' || !is_numeric($safetyStock) || (int) $safetyStock < 0) {
        $errors['safety_stock'] = 'Safety stock must be a non-negative number.';
    }

    // 4. Selling Price validation
    if ($sellingPrice === null || $sellingPrice === '' || !is_numeric($sellingPrice) || (float) $sellingPrice < 0) {
        $errors['selling_price'] = 'Selling price must be a non-negative number.';
    }

    return [
        'isValid' => empty($errors),
        'errors' => $errors,
        'data' => [
            'name' => $name,
            'category_id' => $categoryId,
            'safety_stock' => (int) $safetyStock,
            'selling_price' => (float) $sellingPrice
        ]
    ];
}


function findSupplierOwner($mydb, $supplierId, $userId)
{
    $mydb->select('suppliers', 'id', [
        'id' => (int) $supplierId,
        'user_id' => (int) $userId
    ]);
    return $mydb->res && $mydb->res->fetch_assoc();
}