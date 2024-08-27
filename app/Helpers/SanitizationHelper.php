<?php

namespace App\Helpers;

class SanitizationHelper
{
    public function sanitizeZipCode($zipCode)
    {
        return preg_replace('/\D/', '', $zipCode);
    }

    public function sanitizePhoneNumber($phoneNumber)
    {
        return preg_replace('/\D/', '', $phoneNumber);
    }
}

