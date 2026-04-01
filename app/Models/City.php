<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $table = 'cities';     // Make sure this matches your Supabase table name
    protected $guarded = [];

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    // Optional: Access country through state
    public function country()
    {
        return $this->hasOneThrough(Country::class, State::class);
    }
}