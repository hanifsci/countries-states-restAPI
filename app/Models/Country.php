<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends Model
{
    use HasFactory;

    protected $table = 'countries';
    protected $guarded = [];   // Allow all fields for now (safe since it's seed data)

    public function states()
    {
        return $this->hasMany(State::class);
    }
}
