<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataSend extends Model
{
    use HasFactory;

    protected $table = 'data_sends';
    
    protected $fillable = [
        'codtraslado',
        'modtraslado',
        'fecTraslado',
        'pesoTotal',
        'undPesoTotal',

        'ubigueollegada',
        'direccionLlegada',
        'codLocalLlegada',
        'rucLlegada',

        'ubigueoPartida',
        'direccionPartida',
        'codLocalPartida',
        'rucPartida',

        'tipoDocChofer',
        'nroDocChofer',
        'licenciaChofer',
        'nombresChofer',
        'apellidosChofer',

        'placaVehiculo',

        'tipoDocTransp',
        'numDocTransp',
        'rzSocialTransp',
        'nroMtcTransp',

        'despatch_id',

        'estDelete'
    ];

    public function despatches(){
        return $this->belongsTo(despatch::class);
    }

}
