<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    
    protected $table = 'clients';

    protected $fillable = [
        'tipoDoc',
        'numDoc',
        'razonsocial',
        'nombreComercial',
        'estDelete'
    ];


    public function clientAddresses(){
        return $this->hasMany(ClientAddress::class);
    }

    public function despatches(){
        return $this->hasMany(Despatch::class);
    }
}
