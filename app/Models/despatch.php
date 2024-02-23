<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class despatch extends Model
{
    use HasFactory;

    protected $table = 'despatches';

    protected $fillable = [
        'version',
        'tipoDoc',
        'serie',
        'correlativo',
        'fechaEmision',
        'hash',
        'estHash',
        'xml',
        'estXml',
        'cdrZip',
        'estcdrZip',
        'cdrResponse',
        'companie_id',
        'client_id',
        'sucursal_id'
    ];

    public function companies(){
        return $this->belongsTo(Company::class,'companie_id');
    }

    public function clients(){
        return $this->belongsTo(Client::class,'client_id');
    }

    public function sucursal(){
        return $this->belongsTo(Sucursal::class);
    }

    public function data_sends() {
        return $this->hasMany(DataSend::class);
    }

    public function details() {
        return $this->hasMany(details::class);
    }

}
