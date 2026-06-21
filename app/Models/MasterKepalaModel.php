<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterKepalaModel extends Model
{
    protected $table            = 'master_kepala';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['unit_kerja', 'nama_kepala', 'nip_kepala', 'status'];
}