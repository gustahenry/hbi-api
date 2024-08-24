<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailModel extends Model
{
    protected $table      = 'email';
    protected $primaryKey = 'id';

    protected $allowedFields = ['id_contact', 'email'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}