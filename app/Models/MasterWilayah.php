<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['idsubsls', 'nmsls', 'nama_ketua', 'nmkec', 'kdkec', 'nmdesa', 'kddesa', 'kdsls', 'kdsubsls'])]
class MasterWilayah extends Model
{
    protected $table = 'master_wilayah';
}
