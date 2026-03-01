<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tulajdonos extends Model
{
    protected $table = 'tulajdonos';

    protected $fillable = ['nev', 'email', 'telefon'];

    public function alberletek()
    {
        return $this->hasMany(Alberlet::class, 'tulajdonos_id');
    }
}
