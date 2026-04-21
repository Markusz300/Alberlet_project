<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alberlet extends Model
{
    use HasFactory;

    protected $table = 'alberlet';

    public $timestamps = false;
    protected $fillable = [
        'cim',
        'tipus',           // 0=haz, 1=lakás, 2=szoba
        'ar',
        'meret',
        'szobak_szama',
        'emelet',
        'lift',
        'butorozott',
        'leiras',
        'hirdetes_datuma',
        'aktiv',
        'varos_id',
        'tulajdonos_id',
    ];

    //kenyelem adatatalakitas
    protected $casts = [
        'szobak_szama' => 'decimal:1',
        'hirdetes_datuma' => 'date',
        'aktiv' => 'boolean',
        'lift' => 'boolean',
        'butorozott' => 'boolean',
    ];

    public function varos()
    {
        return $this->belongsTo(Varos::class, 'varos_id');
    }

    public function tulajdonos()
    {
        return $this->belongsTo(Tulajdonos::class, 'tulajdonos_id');
    }

    public function kepek()
    {
        return $this->hasMany(Kep::class, 'alberlet_id');
    }

    public function tipusKapcsolat()   // mert van alberlet_tipus tabla
    {
        return $this->belongsTo(AlberletTipus::class, 'tipus', 'id');
    }
}
