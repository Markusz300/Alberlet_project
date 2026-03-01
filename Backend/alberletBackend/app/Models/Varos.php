<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Varos extends Model
{
    protected $table = 'varos';

    protected $fillable = ['nev', 'megye_id'];

    public function megye()
    {
        return $this->belongsTo(Megye::class, 'megye_id');
    }

    public function alberletek()
    {
        return $this->hasMany(Alberlet::class, 'varos_id');
    }
}
