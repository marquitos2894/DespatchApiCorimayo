<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class details extends Model
{
    use HasFactory;

    protected $table = 'details';

    protected $fillable = [
        'codigo',
        'descripcion',
        'cantidad',
        '"unidad"',
        'equipo',
        'despatch_id'
    ];

    public function despatches(){
        return $this->belongsTo(despatch::class);
    }

}
