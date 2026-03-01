<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlberletTipus extends Model
{
    protected $table = 'alberlet_tipus';

    public $timestamps = false;   

    protected $fillable = ['nev'];

    public function alberletek()
    {
        return $this->hasMany(Alberlet::class, 'tipus');
    }
}
