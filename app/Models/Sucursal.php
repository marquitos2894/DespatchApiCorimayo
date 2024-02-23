<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursal';

    protected $fillable = [
        'nombreSucursal',
        'ubigueo',
        'direccion',
        'departamento',
        'provincia',
        'distrito',
        'urbanizacion',
        'direccion',
        'codLocal',
        'estado',
        'companie_id',
        'nivel'
    ];

    public function companies(){
        return $this->belongsTo(Company::class);
    }

    public function despatches(){
        return $this->hasMany(Despatch::class);
    }


}
