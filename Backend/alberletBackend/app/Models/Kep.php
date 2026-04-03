<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kep extends Model
{
    protected $table = 'kep';
    public $timestamps = false;

    protected $fillable = [
        'alberlet_id',
        'kep_url',
    ];

    public function alberlet()
    {
        return $this->belongsTo(Alberlet::class, 'alberlet_id');
    }
}