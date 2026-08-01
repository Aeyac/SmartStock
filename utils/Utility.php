<?php


class Utility
{


    static function validateSupplier(array $data): array
    {
        $errors = [];

        $name = trim($data["name"] ?? "");
        $contact = trim($data["contact_number"] ?? "");
        $email = trim($data["email"] ?? "");


        if ($name === "") {
            $errors["name"] = "Name is required.";
        } elseif (strlen($name) < 2) {
            $errors["name"] = "Name must be at least 2 characters.";
        } elseif (strlen($name) > 100) {
            $errors["name"] = "Name must not exceed 100 characters.";
        }

        if ($contact === "") {
            $errors["contact_number"] = "Contact number is required.";
        } elseif (!preg_match('/^(09\d{9}|\+639\d{9})$/', $contact)) {
            $errors["contact_number"] = "Invalid Philippine contact number.";
        }

        if ($email === "") {
            $errors["email"] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors["email"] = "Invalid email address.";
        } elseif (strlen($email) > 255) {
            $errors["email"] = "Email must not exceed 255 characters.";
        }

        return $errors;
    }
}