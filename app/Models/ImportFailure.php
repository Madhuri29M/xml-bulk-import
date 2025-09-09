<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportFailure extends Model
{
    protected $fillable = ['import_id','row_number','payload','errors'];
    protected $casts = ['payload' => 'array', 'errors' => 'array'];
}