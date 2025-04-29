<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Model
{
    use HasFactory;

    protected $fillable = ['title'];

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }
}
