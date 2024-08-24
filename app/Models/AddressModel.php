<?php

namespace App\Models;

use CodeIgniter\Model;

class AddressModel extends Model
{
    protected $table      = 'address';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'id_contact', 'zip_code', 'country', 'state', 'street_address',
        'address_number', 'city', 'address_line', 'neighborhood'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
