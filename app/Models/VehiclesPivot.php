<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehiclesPivot extends Model
{
    use HasFactory;

    protected $table = 'vehicles_pivots';

    protected $fillable = [
        'tipo',
        'placa',
        'codemisor',
        'mtc',
        'data_sends_id',
        'active'
    ];

    public function data_sends(){
        return $this->belongsTo(DataSend::class,'data_sends_id');
    }


}
