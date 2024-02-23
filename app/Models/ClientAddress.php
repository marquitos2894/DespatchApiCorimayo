<?php

namespace App\Models;

use Greenter\Model\Client\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientAddress extends Model
{
    use HasFactory;


        
    protected $table = 'client_addresses';

    protected $fillable = [
        'ubigueo',
        'departamento',
        'provincia',
        'distrito',
        'urbanizacion',
        'direccion',
        'client_idAddresses',
        'estDelete'
    ];

    public function clients(){
        return $this->belongsTo(Client::class, 'client_idAddresses');
    }

}
