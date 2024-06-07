<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientAddress;
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\DataSend;
use App\Models\despatch;
use App\Models\details;
use App\Models\DriversPivot;
use App\Models\VehiclesPivot;
use App\Services\SunatService;
use App\Traits\SunatTrait;
use Greenter\Report\XmlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;

use PhpParser\Node\Stmt\TryCatch;
use ZipArchive;

class DespatchController extends Controller
{
    use SunatTrait;

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

       $iddatasend = $despatch[0]->data_sends[0]->id;
       $despatch[0]['data_sends'][0]['vehiculos'] = VehiclesPivot::where('data_sends_id',$iddatasend)->get();
       $despatch[0]['data_sends'][0]['conductores'] = DriversPivot::where('data_sends_id',$iddatasend)->get();
    
       return response()->json($despatch);
    }

    public function search(Request $request){

        $data = $request->all();
        //$despatch = despatch::where('serie',$data['serie'])->where('correlativo',$data['correlativo'])->firstOrFail();

        $view = despatch::with('clients','companies','data_sends')->where('serie',$data['serie'])->where('correlativo',$data['correlativo'])->get();
        return response()->json($view);
    }   

    public function detailsdespatch(Request $request){

        $data = $request->all();
        $filas =$request->rows?$request->rows:5;
        $filters = $data['filters'];
        if($filters['descripcion']['value'] != null || $filters['codigo']['value'] != null || $filters['equipo']['value'] != null ){

            /*$detailsdespatch = despatch::with('data_sends','details')->wherehas('details',function($q) use ($filters){
                $q->where('descripcion','LIKE', "%{$filters['descripcion']['value']}%")->where('codigo','LIKE', "%{$filters['codigo']['value']}%")
                ->where('equipo','LIKE', "%{$filters['equipo']['value']}%");
            })->where('active',1)->orderBy('correlativo', 'desc')->paginate($filas);*/

            $detailsdespatch = details::select("despatches.serie","despatches.correlativo","despatches.fechaEmision","data_sends.fecTraslado","descripcion",
            "codigo","cantidad","unidad","equipo","data_sends.localPartida","data_sends.localLlegada")->where(function($q) use ($filters){
                $q->where('descripcion','LIKE', "%{$filters['descripcion']['value']}%")->where('codigo','LIKE', "%{$filters['codigo']['value']}%")
                ->where('equipo','LIKE', "%{$filters['equipo']['value']}%");})
            ->join("despatches", "despatches.id", "=", "details.despatch_id")
            ->join("data_sends", "data_sends.despatch_id", "=", "despatches.id")
            ->orderBy('despatches.correlativo', 'desc')->paginate($filas);

            //->orWhere('equipo','LIKE', "%{$filters['equipo']['value']}%")
            /*$detailsdespatch = despatch::with('data_sends','details')->wherehas('details',function($q) use ($filters){
                $q->orWhere([
                    ['descripcion','LIKE', "%{$filters['descripcion']['value']}%"],
                    ['codigo','LIKE', "%{$filters['codigo']['value']}%"],
                    ['equipo','LIKE', "%{$filters['equipo']['value']}%"]
                ]);
            })->orderBy('correlativo', 'desc')->paginate($filas);*/

        }else{
            $detailsdespatch = details::select("despatches.serie","despatches.correlativo","despatches.fechaEmision","data_sends.fecTraslado","descripcion",
            "codigo","cantidad","unidad","equipo","data_sends.localPartida","data_sends.localLlegada")
            ->join("despatches", "despatches.id", "=", "details.despatch_id")
            ->join("data_sends", "data_sends.despatch_id", "=", "despatches.id")
            ->orderBy('despatches.correlativo', 'desc')
            ->paginate($filas);
        }

        //->where('active',1)

        return response()->json($detailsdespatch); 
    }


    public function store(Request $request){

        $data = $request->all();

        $company = Company::where('user_id', auth()->id())
        ->where('ruc', $data['company']['ruc'])
        ->firstOrFail();

        //return $data;
        
        //$fechaEmision = $request->fechaEmision;
        //$carbon = new \Carbon\Carbon($fechaEmision);
        //$fechaEmision = $carbon->format('Y-m-d H:i:sT');//Y-m-d\Th:i:sT
        $fechaEmision = Carbon::now()->format('Y-m-d H:i:s');
        $fechaEmision;


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
        $Despatch->areatrabajo=$request->areatrabajo;
        
        $direccion = $request->envio['llegada']['direccion'];
        //*Datos de envio
        $envio->codtraslado = $request->envio['codtraslado'];
        $envio->modtraslado = $request->envio['modtraslado'];
        $envio->fecTraslado = $fecTraslado;
        $envio->pesoTotal = $request->envio['pesoTotal'];
        $envio->undPesoTotal = $request->envio['undPesoTotal'];

        $envio->ubigueollegada = ($request->envio['llegada']['ubigeo'])??'';
        $envio->direccionLlegada =($request->envio['llegada']['direccion'])??'';
        $envio->codLocalLlegada = ($request->envio['llegada']['codLocal'])??'';
        $envio->localLlegada = ($request->envio['llegada']['nomlocal'])??'';
        $envio->rucLlegada = ($request->envio['llegada']['ruc'])??'';

        $envio->ubigueoPartida = ($request->envio['partida']['ubigeo'])??'';
        $envio->direccionPartida = ($request->envio['partida']['direccion'])??'';
        $envio->codLocalPartida = ($request->envio['partida']['codLocal'])??'';
        $envio->localPartida = ($request->envio['partida']['nomlocal'])??'';
        $envio->rucPartida = ($request->envio['partida']['ruc'])??'';

        $envio->tipoDocTransp = ($request->envio['transportista']['tipoDoc'])??'';
        $envio->numDocTransp = ($request->envio['transportista']['numDoc'])??'';
        $envio->rzSocialTransp = ($request->envio['transportista']['rzSocial'])??'';
        $envio->nroMtcTransp = ($request->envio['transportista']['nroMtc'])??'';

        /*$envio->tipoDocChofer = ($request->envio['choferes'][0]['tipoDoc'])??'';
        $envio->nroDocChofer = ($request->envio['choferes'][0]['nroDoc'])??'';
        $envio->licenciaChofer = ($request->envio['choferes'][0]['licencia'])??'';
        $envio->nombresChofer = ($request->envio['choferes'][0]['nombres'])??'';
        $envio->apellidosChofer = ($request->envio['choferes'][0]['apellidos'])??'';

        $envio->placaVehiculo = ($request->envio['vehiculos'][0]['placa'])??'';
        $envio->mtcCirculacion = ($request->envio['vehiculos'][0]['mtcCirculacion'])??'';

 */

       /* $envio->tipoDocChoferTransp = ($request->envio['transportista'][0]['tipoDoc'])??'sin datos';
        $envio->nroDocChoferTransp = ($request->envio['transportista'][0]['nroDoc'])??'sin datos';
        $envio->licenciaChoferTransp = ($request->envio['transportista'][0]['licencia'])??'sin datos';
        $envio->nombresChoferTransp = ($request->envio['transportista'][0]['nombres'])??'sin datos';
        $envio->apellidosChoferTransp = ($request->envio['transportista'][0]['apellidos'])??'sin datos';

        $envio->placaVehiculo = ($request->envio['transportista'][0]['placa'])??'sin datos';
        $envio->placaVehiculo = ($request->envio['transportista'][0]['placa'])??'sin datos';*/


        $vehiculos = $data['envio']['vehiculos'][0];
        $det_vehiculos = [];

        $driver = $data['envio']['choferes'][0];
        $det_driver =[];

        $productos = $data['details']['_value'];
        $det_prod = [];
 
        DB::beginTransaction();
        try{
            $Despatch->save();
            
            $iddesp=$Despatch->id;
            $envio->despatch_id = $iddesp;
            $envio->save();

            $idenvio = $envio->id;

            foreach($vehiculos as $item){
                $det_driver[] =[
                    "tipo" => $item['tipo'],
                    "placa" => $item['placa'],
                    "mtc" => $item['mtcNroAutorizacion'],
                    "codemisor" => 'MTC',
                    "data_sends_id" =>$idenvio,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ];   
            }
            VehiclesPivot::insert($det_driver);

            foreach($driver as $item){
                $det_vehiculos[] =[
                    "tipo" => $item['tipo'],
                    "tipoDoc" => $item['tipoDoc']['id'],
                    "nroDoc" => $item['nroDoc'],
                    "licencia" => $item['licencia'],
                    "nombres" => $item['nombres'],
                    "apellidos" => $item['apellidos'],
                    "data_sends_id" =>$idenvio,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ];   
            }
            DriversPivot::insert($det_vehiculos);


            foreach($productos as $prod){
                $det_prod[] =[
                    "codigo" => $prod['codigo']??'',
                    "descripcion" => $prod['descripcion'],
                    "cantidad" => $prod['cantidad'],
                    "unidad" => $prod['unidad'],
                    "equipo" => $prod['equipo']??'',
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
        //cambiar orgin, no recibir datos del front si no crear una consulta y crear todo el objecto

         $file = Storage::allFiles('zipDespatch/');
         //$file = Storage::disk('my_custom_path')->allFiles('zipDespatch/'); // en servidor cpanel
         Storage::delete($file);
         //Storage::disk('my_custom_path')->delete($file); //en servidor cpanel
     


        $consultadespatch = despatch::where('active',1)
        ->where('serie',$data['serie'])
        ->where('correlativo',$data['correlativo'])
        ->firstOrFail();

        $code = explode(",",$consultadespatch['cdrResponse']);

        if($code[0] == "" && !$consultadespatch['ticket']){// SE ENVIA X PRIMERA VEZ 
            $ticket0 = null; 
        }else if($code[0] == '98'){ // SI RETORNO UN CODIGO EN PROCESO (98), SE CONSULTA EL MISMO TICKET DE LA BD CREADA
            $ticket0 = $consultadespatch['ticket'];
        }else if($consultadespatch['ticket']){ // SI RETORNA CODIGO DE ERROR (0100 A MAS), SE GENERA UN NUEVO TICKET
            $ticket0 = null; 
        }else{
            $ticket0 = null; 
        }

        //$ticket0 = "";//($code > 100)?$consultadespatch['ticket']:; 
        //$ticket0 = $consultadespatch['ticket'];

        $company = Company::where('user_id', auth()->id())
        ->where('ruc', $data['company']['ruc'])
        ->firstOrFail();

        $sunat = new SunatService();
        $despatch = $sunat->getDespatch($data);

        $api = $sunat->getSeeApi($company);
        $result = $api->send($despatch);

        
            if(!$ticket0){ //si no hay ticket registrado en bd se solicita uno nuevo

                try{
                  
                    $ticket = $result->getTicket();//SE OBTIENE EL TICKET por primera vez
                    // OJO, cuando se edita el documento no puedes utilizar el mismo ticket
                    if(!$ticket){
                        sleep(3);
                        $ticket = $result->getTicket();
                        if(!$ticket){
                            $response['sunatResponse']['code'] = "No hay respuesta SUNAT";
                            $response['sunatResponse']['message'] = "No se genero un ticket, intentalo mas tarde";
                            return response()->json($response,200);
                        }
                    }

                }catch(Exception $e){
                    return response()->json("Error de ticket",$e);
                }

                if($ticket){// VALIDACION SI SE GENERO EL TICKET EN SUNAT Y SE GUARDA EN LA BD
                    despatch::where('active',1)
                    ->where('serie',$data['serie'])
                    ->where('correlativo',$data['correlativo'])
                    ->update([
                        'ticket' => $ticket
                    ]); 
                }
            }
            
        //ticket0 => es igual al ticket en la bd
        //ticket => Se crea el ticket por primera vez
        //$result=($ticket0)?"ticket0":"ticketnuevo";  
        $ticket = ($ticket0)?$ticket0:$ticket;
        $result=$api->getStatus($ticket);
 
        $response['xml'] = $api->getLastXml();//se obtiene el xml
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']); //se firma
        $response['sunatResponse'] = $sunat->sunatResponse($result);//Respuesta de sunat

        //$response['pdf'] = response()->download($this->pdfdata($data));

        /*$consultadespatch = despatch::where('active',1)
        ->where('serie',$data['serie'])
        ->where('correlativo',$data['correlativo'])
        ->firstOrFail();*/
 
        if($response['sunatResponse']['success']){

            $sunatRespArray =[
                "code" => $response['sunatResponse']['cdrResponse']['code'],
                "description" => $response['sunatResponse']['cdrResponse']['description'],
                "notes" => implode(" | ",$response['sunatResponse']['cdrResponse']['notes'])
            ];

            $cdrResp = implode(",",$sunatRespArray);

            $cdrzipb64 = $response['sunatResponse']['cdrZip'];
            $file= base64_decode($cdrzipb64);

            $filename = $consultadespatch['serie'].'-'.$consultadespatch['correlativo'].'CDR.zip';
            
            Storage::put('zipDespatch/'.$filename,$file);

            $zipFilePath = 'storage/zipDespatch/'.$filename;
            $zip = new ZipArchive();
            $fileNamexml=""; $urlcodeqr="";

            if ($zip->open($zipFilePath) === TRUE) {
                // Obtener el nombre del archivo
                $fileNamexml = $zip->getNameIndex(0);
                // Leer el contenido del archivo
                $fileContent = $zip->getFromName($fileNamexml);
                // Guardar el contenido en el array
                $extractedFiles = $fileContent;
                $zip->close();
            }

            if (isset($extractedFiles)) {
                // Obtener el contenido del archivo XML
                $xmlContent = $extractedFiles;
                // Cargar el contenido del XML con SimpleXML
                $xml = simplexml_load_string($xmlContent);

                if ($xml === false) {
                    $urlcodeqr = 'Error al obtener el url qr en el XML .';
                } else {
                    // Acceder a elementos del XML 
                    $namespaces = $xml->getNamespaces(true);           
                    // Extraer el valor de cbc:Description dentro de cac:Response
                    $cac = $xml->children($namespaces['cac']);
                    $documentResponse = $cac->DocumentResponse->children($namespaces['cac']);
                    $DocumentReference = $documentResponse->DocumentReference->children($namespaces['cbc']);
                    $urlcodeqr = (string) $DocumentReference->DocumentDescription;
                }
            }

            despatch::where('active',1)
            ->where('serie',$data['serie'])
            ->where('correlativo',$data['correlativo'])
            ->update([
                'hash' => $response['hash'],
                'estHash'=> 1,
                'xml' => $response['xml'],
                'estXml' => 1,
                'cdrZip' => $response['sunatResponse']['cdrZip'],
                'estcdrZip' => 1,
                'cdrResponse' => $cdrResp,
                'urlcodeqr' => $urlcodeqr
            ]);    

        }else{

            $sunatRespArray =[
                "code" => $response['sunatResponse']['error']['code'],
                "description" => $response['sunatResponse']['error']['message']
            ];

            $cdrResp = implode(",",$sunatRespArray);

            despatch::where('active',1)
            ->where('serie',$data['serie'])
            ->where('correlativo',$data['correlativo'])
            ->update([
                'cdrResponse' => $cdrResp,
            ]);    

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

        return response()->json($response,200);

    }

    public function pdf(Request $request){
        //pdf solo descarga 
        
        $data = $request->all();

         //$details = $data['details']['_value'];
        $sunat = new  SunatService();
        $despatch = $sunat->getDespatch($data);// Se genera la guia //
        //$despatch = $sunat->getDespatch();
        //return $sunat->generatePdfReport($despatch);
        //return $sunat->getHtmlReport($despatch);

        return $sunat->getDespatchpdfreport($despatch);

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


    public function b64_d($input){
        $keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
        $chr1 = $chr2 = $chr3 = "";
        $enc1 = $enc2 = $enc3 = $enc4 = "";
        $i = 0;
        $output = "";

        // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
        $filter = $input;
        $input = preg_replace("[^A-Za-z0-9\+\/\=]", "", $input);
        if ($filter != $input) {
            return false;
        }

        do {
            $enc1 = strpos($keyStr, substr($input, $i++, 1));
            $enc2 = strpos($keyStr, substr($input, $i++, 1));
            $enc3 = strpos($keyStr, substr($input, $i++, 1));
            $enc4 = strpos($keyStr, substr($input, $i++, 1));
            $chr1 = ($enc1 << 2) | ($enc2 >> 4);
            $chr2 = (($enc2 & 15) << 4) | ($enc3 >> 2);
            $chr3 = (($enc3 & 3) << 6) | $enc4;
            $output = $output . chr((int) $chr1);
            if ($enc3 != 64) {
                $output = $output . chr((int) $chr2);
            }
            if ($enc4 != 64) {
                $output = $output . chr((int) $chr3);
            }
            $chr1 = $chr2 = $chr3 = "";
            $enc1 = $enc2 = $enc3 = $enc4 = "";
        } while ($i < strlen($input));
        return urldecode($output);
    }

    





}
