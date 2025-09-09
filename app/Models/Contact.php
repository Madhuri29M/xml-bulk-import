<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Builder;

class Contact extends Model
{
    use SoftDeletes, MassPrunable;

    protected $fillable = ['name', 'phone'];

    public function prunable(): Builder
    {
        // Permanently remove records soft-deleted at least 30 days ago
        return static::onlyTrashed()
                     ->where('deleted_at', '<=', now()->subDays(30));
    }
}
