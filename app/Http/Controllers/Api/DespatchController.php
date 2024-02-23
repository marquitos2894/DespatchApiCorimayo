<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientAddress;
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\DataSend;
use App\Models\despatch;
use App\Models\details;
use App\Services\SunatService;
use Greenter\Report\XmlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DespatchController extends Controller
{
    public function index(Request $request){//lista
        $view = despatch::with('clients','companies','data_sends')->orderBy('correlativo', 'desc')->get();
        return response()->json($view);
    }

    public function view(Request $request){//ver una sola guia
       $data = $request->all();
       
       $despatch = despatch::with('clients','companies','data_sends','details')->where('id',$data['0'])->get();

       $id_client =  $despatch[0]->clients->id;
       $ClientAddress = ClientAddress::where('client_idAddresses',$id_client)
        ->firstOrFail();
       $despatch[0]['clients']['address'] = $ClientAddress;

       $id_companie =  $despatch[0]->companies->id;
       $addressCompany = CompanyAddress::where('companie_id', $id_companie)->firstOrFail();
       $despatch[0]['companies']['address'] = $addressCompany;
       
       return response()->json($despatch);
    }


    public function store(Request $request){

        $data = $request->all();

        $company = Company::where('user_id', auth()->id())
        ->where('ruc', $data['company']['ruc'])
        ->firstOrFail();

        //return $data;
        
        $fechaEmision = $request->fechaEmision;
        //$carbon = new \Carbon\Carbon($fechaEmision);
        //$fechaEmision = $carbon->format('Y-m-d');//Y-m-d\Th:i:sT

        $fecTraslado = $request->envio['fecTraslado'];
        $carbon = new \Carbon\Carbon($fecTraslado);
        $fecTraslado = $carbon->format('Y-m-d');

        

        $tipoDoc = '09';//09 guia

        $Despatch = new despatch();
        $envio = new DataSend();
        $details = new details();

        //$data = $request->all();, que huevon soy, almacene el request en data, 
        //y de nuevo estoy llamando request -- CORREGIR !!
        $Despatch->version = $request->version;
        $Despatch->tipoDoc = $tipoDoc;
        $Despatch->serie = $request->serie;

        /*$correlativo = DB::select("SELECT MAX(d.correlativo)+1
        FROM despatches d WHERE d.serie = 'T001' ");*/
        //$Despatch->correlativo = $request->correlativo;

        $correlativo = Despatch::where('serie', $request->serie )->max('correlativo');
        $Despatch->correlativo = $correlativo+1;

        $Despatch->fechaEmision = $fechaEmision;
        $Despatch->hash=$request->hash;
        $Despatch->estHash=($request->estHash)??0;
        $Despatch->xml=$request->estXml;
        $Despatch->estXml=($request->estXml)??0;
        $Despatch->cdrZip=$request->cdrZip;
        $Despatch->estcdrZip=($request->estcdrZip)??0;
        $Despatch->cdrResponse=$request->cdrResponse;
        $Despatch->companie_id=$request->company['idCompany'];
        $Despatch->client_id=$request->destinatario['idclient'];
        $Despatch->sucursal_id=$request->idsucursal;


        $direccion = $request->envio['llegada']['direccion'];
        //*Datos de envio
        $envio->codtraslado = $request->envio['codtraslado'];
        $envio->modtraslado = $request->envio['modtraslado'];
        $envio->fecTraslado = $fecTraslado;
        $envio->pesoTotal = $request->envio['pesoTotal'];
        $envio->undPesoTotal = $request->envio['undPesoTotal'];

        $envio->ubigueollegada = ($request->envio['llegada']['ubigueo'])??'';
        $envio->direccionLlegada =($request->envio['llegada']['direccion'])??'';
        $envio->codLocalLlegada = ($request->envio['llegada']['codLocal'])??'';
        $envio->rucLlegada = ($request->envio['llegada']['ruc'])??'';

        $envio->ubigueoPartida = ($request->envio['partida']['ubigueo'])??'';
        $envio->direccionPartida = ($request->envio['partida']['direccion'])??'';
        $envio->codLocalPartida = ($request->envio['partida']['codLocal'])??'';
        $envio->rucPartida = ($request->envio['partida']['ruc'])??'';

        $envio->tipoDocChofer = ($request->envio['choferes'][0]['tipoDoc'])??'';
        $envio->nroDocChofer = ($request->envio['choferes'][0]['nroDoc'])??'';
        $envio->licenciaChofer = ($request->envio['choferes'][0]['licencia'])??'';
        $envio->nombresChofer = ($request->envio['choferes'][0]['nombres'])??'';
        $envio->apellidosChofer = ($request->envio['choferes'][0]['apellidos'])??'';

        $envio->placaVehiculo = ($request->envio['vehiculos'][0]['placa'])??'';
        $envio->mtcCirculacion = ($request->envio['vehiculos'][0]['mtcCirculacion'])??'';


        $envio->tipoDocTransp = ($request->envio['transportista']['tipoDoc'])??'';
        $envio->numDocTransp = ($request->envio['transportista']['numDoc'])??'';
        $envio->rzSocialTransp = ($request->envio['transportista']['rzSocial'])??'';
        $envio->nroMtcTransp = ($request->envio['transportista']['nroMtc'])??'';

       /* $envio->tipoDocChoferTransp = ($request->envio['transportista'][0]['tipoDoc'])??'sin datos';
        $envio->nroDocChoferTransp = ($request->envio['transportista'][0]['nroDoc'])??'sin datos';
        $envio->licenciaChoferTransp = ($request->envio['transportista'][0]['licencia'])??'sin datos';
        $envio->nombresChoferTransp = ($request->envio['transportista'][0]['nombres'])??'sin datos';
        $envio->apellidosChoferTransp = ($request->envio['transportista'][0]['apellidos'])??'sin datos';

        $envio->placaVehiculo = ($request->envio['transportista'][0]['placa'])??'sin datos';
        $envio->placaVehiculo = ($request->envio['transportista'][0]['placa'])??'sin datos';*/

        $productos = $data['details']['_value'];
        $det_prod = [];
 
        DB::beginTransaction();
        try{
            $Despatch->save();
            
            $iddesp=$Despatch->id;
            $envio->despatch_id = $iddesp;
            $envio->save();

            foreach($productos as $prod){

                $det_prod[] =[
                    "codigo" => $prod['codigo'],
                    "descripcion" => $prod['descripcion'],
                    "cantidad" => $prod['cantidad'],
                    "unidad" => $prod['unidad'],
                    "equipo" => $prod['equipo'],
                    "despatch_id" => $iddesp,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()

                ]; 
            }

            details::insert($det_prod);


            ///datos de tabla data_sends
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(["mensaje"=>"Error de registro","error"=>$e],500);
        }

        
        return response()->json(["mensaje"=>"Registro exitoso"]);

    }


    public function send(Request $request){

        $data = $request->all();

        //$data = $data['datos'];

        $company = Company::where('user_id', auth()->id())
        ->where('ruc', $data['company']['ruc'])
        ->firstOrFail();

        $sunat = new SunatService();
        $despatch = $sunat->getDespatch($data);

        $api = $sunat->getSeeApi($company);
        $result = $api->send($despatch);

        $ticket = $result->getTicket();
        $result=$api->getStatus($ticket);

        $response['xml'] = $api->getLastXml();//se obtiene el xml
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']); //se firma
        $response['sunatResponse'] = $sunat->sunatResponse($result);//Respuesta de sunat

        //$response['pdf'] = response()->download($this->pdfdata($data));

        $consultadespatch = despatch::where('active',1)
        ->where('serie',$data['serie'])
        ->where('correlativo',$data['correlativo'])
        ->firstOrFail();
 
        if($response['sunatResponse']['success']){

            $sunatRespArray =[
                "code" => $response['sunatResponse']['cdrResponse']['code'],
                "description" => $response['sunatResponse']['cdrResponse']['description'],
                "notes" => implode(" | ",$response['sunatResponse']['cdrResponse']['notes'])
            ];

            $cdrResp = implode(",",$sunatRespArray);   

            //if($consultadespatch['estHash']==0 && $consultadespatch['estXml']==0){

                

                despatch::where('active',1)
                ->where('serie',$data['serie'])
                ->where('correlativo',$data['correlativo'])
                ->update([
                    'hash' => $response['hash'],
                    'estHash'=> 1,
                    'xml' => $response['xml'],
                    'estXml' => 1,
                    'xml' => $response['xml'],
                    'estXml' => 1,
                    'cdrZip' => $response['sunatResponse']['cdrZip'],
                    'estcdrZip' => 1,
                    'cdrResponse' => $cdrResp
                ]);    
            //}else{
                /*despatch::where('active',1)
                ->where('serie',$data['serie'])
                ->where('correlativo',$data['correlativo'])
                ->update([
                    'cdrZip' => $response['sunatResponse']['cdrZip'],
                    'estcdrZip' => 1,
                    'cdrResponse' => $cdrResp
                ]);*/    
            //}
        }



      
        return response()->json($response,200);

    }

    public function xml(Request $request){
        
        $data = $request->all();

        $company = Company::where('user_id', auth()->id())
                            ->where('ruc', $data['company']['ruc'])
                            ->firstOrFail();

        /*$company = despatch::where('user_id', auth()->id())
        ->where('ruc', $data['company']['ruc'])
        ->firstOrFail();*/

        $sunat = new  SunatService();
        $see = $sunat->getSee($company);//se envia los datos de la compañia; usuario, clave sol ..
        //$invoice = $sunat->getInvoice($data);// Se genera la factura //

        $despatch = $sunat->getDespatch($data);

        $response['xml'] = $see->getXmlSigned($despatch);
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);//firmar el documento

        despatch::where('active',1)
            ->where('serie',$data['serie'])
            ->where('correlativo',$data['correlativo'])
            ->update([
                'hash' => $response['hash'],
                'estHash'=> 1,
                'xml' => $response['xml'],
                'estXml' => 1
            ]);
  
            //estHash

        return response()->json($response,200);


    }

    public function pdf(Request $request){
        //pdf solo descarga 
        $data = $request->all();
        //$details = $data['details']['_value'];
        $sunat = new  SunatService();
        $despatch = $sunat->getDespatch($data);// Se genera la guia //
        //$despatch = $sunat->getDespatch();
        return $sunat->generatePdfReport($despatch);
        //return $sunat->getHtmlReport($despatch);

    }

    public function pdfdown(Request $request){

         //$sunat->getHtmlReport($despatch);

         // pdf original
 

        $data = $request->all();

        //$data = $data['data'];


         /*$company = Company::where('user_id', auth()->id())
                             ->where('ruc', $data['company']['ruc'])
                             ->firstOrFail();*/
         
         $sunat = new  SunatService();
         $despatch = $sunat->getDespatch($data);// Se genera la guia //
         //$despatch = $sunat->getDespatch();
         return $sunat->generatePdfReport($despatch);
   
          //$sunat->getHtmlReport($despatch);
 

    }




    public function pdfdata($data){

        
        //$data = $request->all();

        $company = Company::where('user_id', auth()->id())
                            ->where('ruc', $data['company']['ruc'])
                            ->firstOrFail();
        
        $sunat = new  SunatService();
        $despatch = $sunat->getDespatch($data);// Se genera la guia //

        return $sunat->generatePdfReport($despatch);   
        //return $sunat->getHtmlReport($despatch);

    }



}
