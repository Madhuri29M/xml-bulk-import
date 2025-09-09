<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    protected $fillable = [
        'file_path','status','total','processed','succeeded','failed',
        'batch_id','error_log_path','started_at','finished_at'
    ];

    public function failures() { return $this->hasMany(ImportFailure::class); }
}