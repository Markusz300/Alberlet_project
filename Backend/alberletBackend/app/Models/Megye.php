<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Megye extends Model
{
    protected $table = 'megye';

    protected $fillable = ['nev'];

    public function varosok()
    {
        return $this->hasMany(Varos::class, 'megye_id');
    }
}
