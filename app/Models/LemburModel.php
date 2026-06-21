<?php

namespace App\Models;

use CodeIgniter\Model;

class LemburModel extends Model
{
    protected $table      = 'lembur';
    protected $primaryKey = 'id';
    protected $allowedFields = ['tanggal', 'uraian', 'bukti', 'tindak_lanjut', 'target'];
}