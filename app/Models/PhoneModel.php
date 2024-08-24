<?php

namespace App\Models;

use CodeIgniter\Model;

class PhoneModel extends Model
{
    protected $table      = 'phone';
    protected $primaryKey = 'id';

    protected $allowedFields = ['id_contact', 'phone'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
