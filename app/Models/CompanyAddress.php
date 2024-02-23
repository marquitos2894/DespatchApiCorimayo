<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAddress extends Model
{
    use HasFactory;

    protected $table = 'company_addresses';

    protected $fillable = [
        'ubigueo',
        'departamento',
        'provincia',
        'distrito',
        'urbanizacion',
        'direccion',
        'codLocal',
        'companie_id',      
    ];

    
    public function companies(){
        return $this->belongsTo(Company::class);
    }

}
