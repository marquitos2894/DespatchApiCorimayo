<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriversPivot extends Model
{
    use HasFactory;

    protected $table = 'drivers_pivots';

    protected $fillable = [
        'tipo',
        'tipoDoc',
        'nroDoc',
        'licencia',
        'nombres',
        'apellidos',
        'data_sends_id',
        'active'
    ];

    public function data_sends(){
        return $this->belongsTo(DataSend::class,'data_sends_id');
    }

    
}
