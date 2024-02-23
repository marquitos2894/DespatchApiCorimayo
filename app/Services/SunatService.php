<?php

namespace App\Services;

use App\Models\Company as ModelsCompany;
use App\Models\despatch as despatchesModel;

use DateTime;
use Exception;

use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Driver;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Transportist;
use Greenter\Model\Despatch\Vehicle;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Report\HtmlReport;
use Greenter\Report\PdfReport;
use Greenter\Report\Resolver\DefaultTemplateResolver;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use Illuminate\Support\Facades\Storage;


class SunatService
{
    //funcion para obtener los datos de la compañia, certifcado y credenciales
    public function getSee($company){

        //$certificate = Storage::get($company->cert_path); //Storage, obtiene el contenifo del archivo y este se almacenara en la variable certificate.
        $see = new See();
        $see->setCertificate(Storage::get($company->cert_path));
        $see->setService($company->production ? SunatEndpoints::FE_PRODUCCION:SunatEndpoints::FE_BETA);
        $see->setClaveSOL($company->ruc, $company->sol_user, $company->sol_pass);

        return $see;
    }

    public function getSeeApi($company){//conexion
        $api = new \Greenter\Api($company->production ?[
            'auth' => 'https://api-seguridad.sunat.gob.pe/v1',
            'cpe' => 'https://api-cpe.sunat.gob/v1',
        ]:[
            'auth' => 'https://gre-test.nubefact.com/v1',
            'cpe' => 'https://gre-test.nubefact.com/v1', 
        ]);

        $api->setBuilderOptions([
            'strict_variables' => true,
            'optimizations' => 0,
            'debug' => true,
            'cache' => false,
        ])->setApiCredentials(
            $company->production ? $company->client_id:"test-85e5b0ae-255c-4891-a595-0b98c65c9854",
            $company->production ? $company->client_secret:"test-Hty/M6QshYvPgItX2P0+Kw==",
        )->setClaveSOL(
            $company->ruc,
            $company->production ?$company->sol_user:"MODDATOS",
            $company->production ?$company->sol_pass:"MODDATOS"
        )->setCertificate(Storage::get($company->cert_path));

        return $api;
    }


    public function getInvoice($data){


        return $invoice = (new Invoice())
            ->setUblVersion($data['ublVersion'] ?? '2.1')
            ->setTipoOperacion($data['tipoOperacion'] ?? null) // Venta - Catalog. 51
            ->setTipoDoc($data['tipoDoc']?? null) // Factura - Catalog. 01 
            ->setSerie($data['serie']?? null)
            ->setCorrelativo($data['correlativo']?? null)
            ->setFechaEmision(new DateTime($data['fechaEmision'])?? null) // Zona horaria: Lima
            ->setFormaPago(new FormaPagoContado()) // FormaPago: Contado
            ->setTipoMoneda($data['tipoMoneda']?? null) // Sol - Catalog. 02
            ->setCompany($this->getCompany($data['company'])) //
            ->setClient($this->getClient($data['client']))
            //MtoOperaciones
            ->setMtoOperGravadas($data['mtoOperGravadas'])
            ->setMtoOperExoneradas($data['mtoOperExonerada'])
            ->setMtoOperInafectas($data['mtoOperInafectas'])
            ->setMtoOperExportacion($data['mtoOperExportacion'])
            ->setMtoOperGratuitas($data['mtoOperGratuitas'])
            //Impuestos
            ->setMtoIGV($data['mtoIGV'])
            ->setMtoIGVGratuitas($data['mtoIGVGratuitas'])
            ->setIcbper($data['icbper'])//bolsas plasticas
            ->setTotalImpuestos($data['totalImpuestos'])

            //Totales
            ->setValorVenta($data['valorVenta'])
            ->setSubTotal($data['subTotal'])
            ->setRedondeo($data['redondeo'])
            ->setMtoImpVenta($data['mtoImpVenta'])

            //Detalle
            ->setDetails($this->getDetails($data['details']))

            //Legenda
            ->setLegends($this->getLegends($data['legends']));

    }

    public function getNote($data){
        return (new Note)
        ->setUblVersion($data['ublVersion'] ?? '2.1')
        ->setTipoDoc($data['tipoDoc']?? null) // Factura - Catalog. 01 
        ->setSerie($data['serie']?? null)
        ->setCorrelativo($data['correlativo']?? null)
        ->setFechaEmision(new DateTime($data['fechaEmision'])?? null)
        ->setTipDocAfectado($data['tipDocAfectado']?? null) // Zona horaria: Lima
        ->setNumDocfectado($data['numDocfectado']??null)
        ->setCodMotivo($data['codMotivo'] ?? null)//---
        ->setDesMotivo($data['desMotivo'] ?? null)//---
        ->setTipoMoneda($data['tipoMoneda'] ?? null)
        ->setCompany($this->getCompany($data['company'])) //
        ->setClient($this->getClient($data['client']))
        //MtoOperaciones
        ->setMtoOperGravadas($data['mtoOperGravadas'])
        ->setMtoOperExoneradas($data['mtoOperExonerada'])
        ->setMtoOperInafectas($data['mtoOperInafectas'])
        ->setMtoOperExportacion($data['mtoOperExportacion'])
        ->setMtoOperGratuitas($data['mtoOperGratuitas'])
        //Impuestos
        ->setMtoIGV($data['mtoIGV'])
        ->setMtoIGVGratuitas($data['mtoIGVGratuitas'])
        ->setIcbper($data['icbper'])//bolsas plasticas
        ->setTotalImpuestos($data['totalImpuestos'])

        //Totales
        ->setValorVenta($data['valorVenta'])
        ->setSubTotal($data['subTotal'])
        ->setRedondeo($data['redondeo'])
        ->setMtoImpVenta($data['mtoImpVenta'])

        //Detalle
        ->setDetails($this->getDetails($data['details']))

        //Legenda
        ->setLegends($this->getLegends($data['legends']));

    }

    public function getDespatch($data){
        return (new Despatch)
            ->setVersion($data['version'] ?? '2022')
            ->setTipoDoc($data['tipoDoc'] ?? '09')//guia de remision
            ->setSerie($data['serie']?? null)
            ->setCorrelativo($data['correlativo']?? null)
            ->setFechaEmision(new DateTime($data['fechaEmision'])?? null)
            ->setCompany($this->getCompany($data['company']))
            ->setDestinatario($this->getClient($data['destinatario']))
            ->setEnvio($this->getEnvio($data['envio']))
            ->setDetails($this->getDespatchDetails($data['details']));
            //->setDetails($this->getDespatchDetails($data['details']['_value']));
    }


    public function getCompany($company){

        return $company = (new Company())
            ->setRuc($company['ruc'] ?? null)
            ->setRazonSocial($company['razonSocial'] ?? null)
            ->setNombreComercial($company['nombreComercial'] ?? null)
            ->setAddress($this->getAddress($company['address']) ?? null);
    }

    public function getClient($client){
        
        //Cliente ?? null
        return $client = (new Client())
            ->setTipoDoc($client['tipoDoc'] ?? null)//catalogo 1 sunat DNI, RUC,
            ->setNumDoc($client['numDoc'] ?? null)
            ->setRznSocial($client['rzSocial'] ?? null)
            ->setAddress($this->getAddress($client['address']) ?? null);

    }

    public function getAddress($address){
        // Emisor
        return $address = (new Address())
            ->setUbigueo($address['ubigueo'] ?? null ?? null ?? null)
            ->setDepartamento($address['departamento'] ?? null ?? null ?? null)
            ->setProvincia($address['provincia'] ?? null ?? null ?? null)
            ->setDistrito($address['distrito'] ?? null ?? null ?? null)
            ->setUrbanizacion($address['urbanizacion'] ?? null ?? null ?? null)
            ->setDireccion($address['direccion'] ?? null ?? null ?? null)
            ->setCodLocal($address['codLocal'] ?? null ?? null ?? null); // Codigo de establecimiento asignado por SUNAT, 0000 por defecto.
    }

    public function getDetails($details){

        $green_details = [];

        foreach ($details as $detail) {

            $green_details[] = (new SaleDetail())
            ->setTipAfeIgv($detail['tipoAfeIgv'] ?? null ?? null)// Gravado Op. Onerosa - Catalog. 07
            ->setCodProducto($detail['codProducto'] ?? null ?? null)
            ->setUnidad($detail['unidad'] ?? null ?? null) // Unidad - Catalog. 03
            ->setDescripcion($detail['descripcion'] ?? null ?? null)
            ->setCantidad($detail['cantidad'] ?? null ?? null)
            ->setMtoValorUnitario($detail['mtoValorUnitario'] ?? null ?? null)
            ->setMtoValorVenta($detail['mtoValorVenta'] ?? null ?? null)
            ->setMtoBaseIgv($detail['mtoBaseIgv'] ?? null ?? null)
            ->setPorcentajeIgv($detail['porcentajeIgv'] ?? null ?? null) // 18%
            ->setIgv($detail['igv'] ?? null ?? null)
            ->setFactorIcbper($detail['factorIcbper'] ?? null)
            ->setIcbper($detail['icbper'] ?? null)
            ->setTotalImpuestos($detail['totalImpuestos'] ?? null ?? null) // Suma de impuestos en el detalle
            ->setMtoPrecioUnitario($detail['mtoPrecioUnitario'] ?? null ?? null);
        }

        return $green_details;
    }

    public function getDespatchDetails($details){

        $green_details = [];
        
        foreach($details as $detail){
            $green_details[] = (new DespatchDetail)
                ->setCantidad($detail['cantidad'] ?? null)
                ->setUnidad($detail['unidad'] ?? null)
                ->setEquipo($detail['equipo'] ?? null)
                ->setDescripcion($detail['descripcion'] ?? null)
                ->setCodigo($detail['codigo'] ?? null);
        }

        return $green_details;

    }

    public function getLegends($legends){

        $green_legends = [];

        foreach ($legends as $legend) {
            $green_legends[] = (new Legend())
                ->setCode($legend['code'] ?? null) // Monto en letras - Catalog. 52
                ->setValue($legend['value'] ?? null);
        }

        return $green_legends;
    }

    public function getEnvio($data){
        $shipment = (new Shipment)
        ->setCodTraslado($data['codtraslado']??null) // catalogo 20
        ->setModTraslado($data['modtraslado']??null)//catalogo 18: transporte publico 01 y  privado 02
        ->setFecTraslado(new DateTime($data['fecTraslado']))
        ->setPesoTotal($data['pesoTotal'] ?? null)
        ->setUndPesoTotal($data['undPesoTotal'] ?? null)
        ->setLlegada(new Direction($data['llegada']['ubigueo'],$data['llegada']['direccion']))
        ->setPartida(new Direction($data['partida']['ubigueo'],$data['partida']['direccion']));
        
        if($data['modtraslado']=='01'){ //transporte publico 01
            $shipment->setTransportista($this->getTransportista($data['transportista']));
            /*$shipment->setVehiculo($this->getVehiculo($data['vehiculos']))
            ->setChoferes($this->getChoferes($data['choferes']));*/      
        }

        if($data['modtraslado']=='02'){ //transporte privado 02
            $shipment->setVehiculo($this->getVehiculo($data['vehiculos']))
            ->setChoferes($this->getChoferes($data['choferes']));
        }

        if($data['codtraslado']=='04'){ //Entre establecimiento misma empresa
            $shipment
            ->setLlegada((new Direction($data['llegada']['ubigueo'],$data['llegada']['direccion']))->setCodLocal($data['llegada']['codLocal'])->setRuc($data['llegada']['ruc']))
            ->setPartida((new Direction($data['partida']['ubigueo'],$data['partida']['direccion']))->setCodLocal($data['llegada']['codLocal'])->setRuc($data['llegada']['ruc']));
        }


        return $shipment;

    }

    public function getTransportista($transportista){

        return $transportista = (new Transportist)
            ->setTipoDoc($transportista['tipoDoc'] ?? null)//catalogo 1 sunat DNI, RUC, **verificar !!!!!
            ->setNumDoc($transportista['numDoc'] ?? null)
            ->setRznSocial($transportista['rzSocial'] ?? null)
            ->setNroMtc($transportista['nroMtc'] ?? null)
            ->setPlaca($transportista['placa'] ?? null)//opcional
            ->setChoferTipoDoc($transportista['choferTipoDoc'] ?? null)//opcional
            ->setChoferDoc($transportista['choferDoc'] ?? null)//opcional
            ->setNroCirculacion($transportista['mtc'] ?? null);//opcional
            
    }

    public function getVehiculo($vehiculos){//transporte privado
        /*$vehiculo = (new Vehicle())
            ->setPlaca($data['placa'] ?? null);*/

        $vehiculos = collect($vehiculos);
        $secundarios = [];

        foreach($vehiculos->slice(1) as $item){
            $secundarios[] = (new Vehicle())
                ->setPlaca($item['placa'] ?? null)
                ->setNroCirculacion($item['mtc'] ?? null);

            /*$secundarios[] = (new Transportist())
                ->setNroMtc($item['nroMtc'] ?? null);*/
        }

        return (new Vehicle())
            ->setPlaca($vehiculos->first()['placa'] ?? null)
            ->setNroCirculacion($vehiculos->first()['mtc']?? null)
            ->setSecundarios($secundarios);
    }

    public function getChoferes($choferes){//transporte privado

        $choferes = collect($choferes);
        $drivers[] = (new Driver)
            ->setTipo('Principal')
            ->setTipoDoc($choferes->first()['tipoDoc'] ?? null)
            ->setNroDoc($choferes->first()['nroDoc'] ?? null)
            ->setLicencia($choferes->first()['licencia'] ?? null)
            ->setNombres($choferes->first()['nombres'] ?? null)
            ->setApellidos($choferes->first()['apellidos'] ?? null);

        foreach($choferes->slice(1) as $item){
            $drivers[] = (new Driver)
            ->setTipo('Secundario')
            ->setTipoDoc($item['tipoDoc'] ?? null)
            ->setNroDoc($item['nroDoc'] ?? null)
            ->setLicencia($item['licencia'] ?? null)
            ->setNombres($item['nombres'] ?? null)
            ->setApellidos($item['apellidos'] ?? null);
        }

        return $drivers;
    }


    //Response y reporte
    public function sunatResponse($result){

        $response['success'] = $result->isSuccess();

         // Verificamos que la conexión con SUNAT fue exitosa.
         if (!$response['success']) {
            // Mostrar error al conectarse a SUNAT.

            $response['error'] =[
                'code' => $result->getError()->getCode(),
                'message' => $result->getError()->getMessage()
            ];

            return $response;
        }

        $response['cdrZip'] = base64_encode($result->getCdrZip());

        //leer el cdr
        $cdr = $result->getCdrResponse();

        $response['cdrResponse'] = [
            'code'=>(int)$cdr->getCode(),
            'description'=> $cdr->getDescription().PHP_EOL,
            'notes'=>$cdr->getNotes()
        ];

        return $response;

    }

    public function getHtmlReport($invoice){

        $report = new HtmlReport();
        $resolver = new DefaultTemplateResolver();
        $report->setTemplate($resolver->getTemplate($invoice));

        $ruc = $invoice->getCompany()->getRuc();
        $company = ModelsCompany::where('ruc',$ruc)
            ->where('user_id',auth()->id())
            ->first();

        $params = [
            'system' => [
                'logo' => Storage::get($company->logo_path), // Logo de Empresa
                'hash' => 'qqnr2dN4p/HmaEA/CJuVGo7dv5g=', // Valor Resumen 
            ],
            'user' => [
                'header'     => 'Telf: <b>(01) 123375</b>', // Texto que se ubica debajo de la dirección de empresa
                'extras'     => [
                    // Leyendas adicionales
                    ['name' => 'CONDICION DE PAGO', 'value' => 'Efectivo'     ],
                    ['name' => 'VENDEDOR'         , 'value' => 'GITHUB SELLER'],
                ],
                'footer' => '<p>Nro Resolucion: <b>3232323</b></p>'
            ]
        ];

        return $html = $report->render($invoice, $params);
    
    }

    public function generatePdfReport($invoice){//

        //$files = glob('/storage/*');
         //Storage::delete(File::glob('path/*.jpg'));
        $file = Storage::allFiles('inovoices/');
        Storage::delete($file);
        $htmlreport = new HtmlReport();

        $resolver = new DefaultTemplateResolver();
        //$htmlreport->setTemplate($resolver->getTemplate($invoice));


        $htmlreport->setTemplate($resolver->getTemplate($invoice));

        $ruc = $invoice->getCompany()->getRuc();
        $company = ModelsCompany::where('ruc',$ruc)
            ->where('user_id',auth()->id())
            ->first();
        
        $consultadespatch = despatchesModel::where('active',1)
            ->where('serie',$invoice->getSerie())
            ->where('correlativo',$invoice->getCorrelativo())
            ->firstOrFail();
        
        $hash = ($consultadespatch['hash'])??"";

        $report = new PdfReport($htmlreport);

        $report->setOptions( [
            'no-outline',
            'viewport-size' => '1280x1024',
            'page-width' => '21cm',
            'page-height' => '29.7cm',
        ]);
        $report->setBinPath(env('WKHTMLTOPDF_PATH'));


        $params = [
            'system' => [
                'logo' => Storage::get($company->logo_path), // Logo de Empresa
                'hash' => $hash, // Valor Resumen 
            ],
            'user' => [
                'header'     => 'Telf: <b> -------- </b>', // Texto que se ubica debajo de la dirección de empresa
                'extras'     => [
                    // Leyendas adicionales
                    ['name' => 'CONDICION DE PAGO', 'value' => 'Efectivo'     ],
                    ['name' => 'VENDEDOR'         , 'value' => 'GITHUB SELLER'],
                ],
                'footer' => '<p>Nro Resolucion: <b></b></p>'
            ]
        ];

        $pdf = $report->render($invoice, $params);

       
        Storage::put('inovoices/'.$invoice->getName().'.pdf',$pdf);

        try{
            $headers = [
                "Content-Type" => "application/pdf"   
            ];

            return response()->download(Storage::get('inovoices/'.$invoice->getName().'.pdf'), $headers);


         }catch(Exception $e){
            return $e->getMessage();
         }
       
    }

    public function generatePdfReport2($invoice){//

        $htmlreport = new HtmlReport();

        $resolver = new DefaultTemplateResolver();
        $htmlreport->setTemplate($resolver->getTemplate($invoice));

        $ruc = $invoice->getCompany()->getRuc();
        $company = ModelsCompany::where('ruc',$ruc)
            ->where('user_id',auth()->id())
            ->first();

        $report = new PdfReport($htmlreport);

        $report->setOptions( [
            'no-outline',
            'viewport-size' => '1280x1024',
            'page-width' => '21cm',
            'page-height' => '29.7cm',
        ]);
        $report->setBinPath(env('WKHTMLTOPDF_PATH'));


        $params = [
            'system' => [
                'logo' => Storage::get($company->logo_path), // Logo de Empresa
                'hash' => 'qqnr2dN4p/HmaEA/CJuVGo7dv5g=', // Valor Resumen 
            ],
            'user' => [
                'header'     => 'Telf: <b>(01) 123375</b>', // Texto que se ubica debajo de la dirección de empresa
                'extras'     => [
                    // Leyendas adicionales
                    ['name' => 'CONDICION DE PAGO', 'value' => 'Efectivo'     ],
                    ['name' => 'VENDEDOR'         , 'value' => 'GITHUB SELLER'],
                ],
                'footer' => '<p>Nro Resolucion: <b>3232323</b></p>'
            ]
        ];

        $pdf = $report->render($invoice, $params);
       //return file_put_contents('invoice.pdf', $pdf);

       
        Storage::put('inovoices/'.$invoice->getName().'.pdf',$pdf);

        //$path = Storage::get('inovoices/'.$invoice->getName().'.pdf');

        //$header = ["Content-Type: application/pdf", "Content-Disposition:attachment","Content-Transfer-Encoding: binary","Content-Length: ".filesize(Storage::get('inovoices/'.$invoice->getName().'.pdf'))];
        
        /*$header = [
            "Content-Type" => "application/pdf; charset=utf-8",
            "Content-Disposition" => "attachment",
            "Content-Transfer-Encoding" => " binary"
        ];*/

        //$header = ['Content-Type' => 'application/pdf'];

//        return response()->download(Storage::get('inovoices/20513963085.pdf',$header));


        try{
            $headers = [
                'Content-Type' => 'application/pdf',
            ];
            return response()->download(Storage::get('inovoices/20513963085.pdf'), $headers);


         }catch(Exception $e){
            return $e->getMessage();
         }


        //return Storage::download(Storage::get('inovoices/'.$invoice->getName().'.pdf'),"archivo.pdf");

        //return Storage::download("app/public/storage/logos/0E6HkL9NQFEGmT20qhpjkCe9sGPOcZRc7V7QbUXS.png",$header);

    }



}