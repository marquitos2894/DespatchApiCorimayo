<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'razon_social',
        'ruc',
        'direccion',
        'logo_path',
        'sol_user',
        'sol_pass',
        'cert_path',
        'client_id',
        'client_secret',
        'production',
        'user_id'        
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

   public function sucursal(){
        return $this->hasMany(Sucursal::class);
   }

   public function despatches(){
    return $this->hasMany(Despatch::class);
    }
   
   public function company_addresses(){
    return $this->hasMany(CompanyAddress::class);
}

}
