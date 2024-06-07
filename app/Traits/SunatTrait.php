<?php

namespace App\Traits;

use Luecano\NumeroALetras\NumeroALetras;

trait SunatTrait {

    public function setTotales(&$data){//& valor por referencia, significa que involucra a los metodos por fuera

        $details = collect($data['details']);

        $data['mtoOperGravadas'] = $details->where('tipoAfeIgv',10)->sum('mtoValorVenta');
        $data['mtoOperExonerada'] = $details->where('tipoAfeIgv',20)->sum('mtoValorVenta');
        $data['mtoOperInafectas'] = $details->where('tipoAfeIgv',30)->sum('mtoValorVenta');
        $data['mtoOperExportacion'] = $details->where('tipoAfeIgv',40)->sum('mtoValorVenta');
        $data['mtoOperGratuitas'] = $details->whereNotIn('tipoAfeIgv',[10,20,30,40])->sum('mtoValorVenta');

        $data['mtoIGV'] =  $details->whereIn('tipoAfeIgv',[10,20,30,40])->sum('igv');//Solo suma los igv de segun los codigos de la tabla sunat
        $data['mtoIGVGratuitas'] =  $details->whereNotIn('tipoAfeIgv',[10,20,30,40])->sum('igv');
        $data['icbper'] = $details->sum('icbper');
        $data['totalImpuestos'] = $data['mtoIGV'] + $data['icbper'] ;
        
        $data['valorVenta'] =$details->whereIn('tipoAfeIgv',[10,20,30,40])->sum('mtoValorVenta');
        $data['subTotal'] = $data['valorVenta'] + $data['mtoIGV'];

        $data['mtoImpVenta'] = floor($data['subTotal']*10)/10;

        $data['redondeo'] = $data['mtoImpVenta'] - $data['subTotal'];

    }

    public function setLegends(&$data){

        $formatter = new NumeroALetras();

        $data['legends'] = [
                [
                    'code' => '1000',
                    'value' => $formatter->toInvoice($data['mtoImpVenta'],2,'SOLES')
                ]
            ];

    }

    public function setMotivotraslado(&$data){

        $motTraslado="";

        if($data == "2"){
            $motTraslado="Compra";
        }else if($data == "4"){
            $motTraslado="Traslado entre establecimientos de la misma empresa";
        }else if($data == "6"){
            $motTraslado="Devolucion";
        }


    }




}
