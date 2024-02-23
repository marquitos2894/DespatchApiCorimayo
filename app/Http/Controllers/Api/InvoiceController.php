<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\SunatService;
use App\Traits\SunatTrait;
use Greenter\Report\XmlUtils;
use Illuminate\Http\Request;
use League\CommonMark\Util\Xml;
use Luecano\NumeroALetras\NumeroALetras;

class InvoiceController extends Controller
{
    
    use SunatTrait;


    public function send(Request $request){
     

        $request->validate([
            'company' => 'required|array',
            'company.address' => 'required|array',
            'client' => 'required|array',
            'details' => 'required|array',
            'details.*' => 'required|array'
        ]);

        $data = $request->all();

        $company = Company::where('user_id', auth()->id())
                            ->where('ruc', $data['company']['ruc'])
                            ->firstOrFail();

        $this->setTotales($data);
        $this->setLegends($data);

        //return $data;

        $sunat = new  SunatService();
        $see = $sunat->getSee($company);//se envia los datos de la compañia; usuario, clave sol ..
        $invoice = $sunat->getInvoice($data);// Se genera la factura //
        
        $result = $see->send($invoice);//Se envia a Sunat

        $response['xml'] = $see->getFactory()->getLastXml();//se obtiene el xml
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']); //se firma
        $response['sunatResponse'] = $sunat->sunatResponse($result);//Respuesta de sunat


        return response()->json($response,200);
            
    }

    public function xml(Request $request){

        $request->validate([
            'company' => 'required|array',
            'company.address' => 'required|array',
            'client' => 'required|array',
            'details' => 'required|array',
            'details.*' => 'required|array'
        ]);

        $data = $request->all();

        $company = Company::where('user_id', auth()->id())
                            ->where('ruc', $data['company']['ruc'])
                            ->firstOrFail();

        $this->setTotales($data);
        $this->setLegends($data);
        
        $sunat = new  SunatService();
        $see = $sunat->getSee($company);//se envia los datos de la compañia; usuario, clave sol ..
        $invoice = $sunat->getInvoice($data);// Se genera la factura //

        $response['xml'] = $see->getXmlSigned($invoice);
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);//firmar el documento

        return response()->json($response,200);

    }

    public function pdf(Request $request){

        $request->validate([
            'company' => 'required|array',
            'company.address' => 'required|array',
            'client' => 'required|array',
            'details' => 'required|array',
            'details.*' => 'required|array'
        ]);

        $data = $request->all();

        $company = Company::where('user_id', auth()->id())
                            ->where('ruc', $data['company']['ruc'])
                            ->firstOrFail();

        $this->setTotales($data);
        $this->setLegends($data);
        
        $sunat = new  SunatService();
        $invoice = $sunat->getInvoice($data);// Se genera la factura //

        $sunat->generatePdfReport($invoice);   
        return $sunat->getHtmlReport($invoice);
    }


}
