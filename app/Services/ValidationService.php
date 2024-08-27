<?php

namespace App\Services;

class ValidationService
{
    protected $validation;
    protected $errors;

    public function __construct()
    {
        $this->validation = \Config\Services::validation();
    }

    public function validateContact(array $data)
    {
        $this->validation->setRules([
            'name' => 'required|min_length[3]',
            'description' => 'required',
            'address.zip_code' => 'required|exact_length[8]',
            'phone.phone' => 'required|min_length[10]',
            'email.email' => 'required|valid_email'
        ]);

        if (!$this->validation->run($data)) {
            $this->errors = $this->validation->getErrors();
            return false;
        }

        return true;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
