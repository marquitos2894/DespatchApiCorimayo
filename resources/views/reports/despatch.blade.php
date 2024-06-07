@php
use App\Services\SunatService;
use Illuminate\Support\Facades\Storage;
@endphp
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style type="text/css">
        .bold,b,strong{font-weight:700}body{background-repeat:no-repeat;background-position:center center;text-align:center;margin:0;font-family: Verdana, monospace}  .tabla_borde{border:1px solid #666;border-radius:10px}  tr.border_bottom td{border-bottom:1px solid #000}  tr.border_top td{border-top:1px solid #666}td.border_right{border-right:1px solid #666}.table-valores-totales tbody>tr>td{border:0}  .table-valores-totales>tbody>tr>td:first-child{text-align:right}  .table-valores-totales>tbody>tr>td:last-child{border-bottom:1px solid #666;text-align:right;width:30%}  hr,img{border:0}  table td{font-size:12px}  html{font-family:sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;font-size:10px;-webkit-tap-highlight-color:transparent}  a{background-color:transparent}  a:active,a:hover{outline:0}  img{vertical-align:middle}  hr{height:0;-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box;margin-top:20px;margin-bottom:20px;border-top:1px solid #eee}  table{border-spacing:0;border-collapse:collapse}@media print{blockquote,img,tr{page-break-inside:avoid}*,:after,:before{color:#000!important;text-shadow:none!important;background:0 0!important;-webkit-box-shadow:none!important;box-shadow:none!important}a,a:visited{text-decoration:underline}a[href]:after{content:" (" attr(href) ")"}blockquote{border:1px solid #999}img{max-width:100%!important}p{orphans:3;widows:3}.table{border-collapse:collapse!important}.table td{background-color:#fff!important}}  a,a:focus,a:hover{text-decoration:none}  *,:after,:before{-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}  a{color:#428bca;cursor:pointer}  a:focus,a:hover{color:#2a6496}  a:focus{outline:dotted thin;outline:-webkit-focus-ring-color auto 5px;outline-offset:-2px}  h6{font-family:inherit;line-height:1.1;color:inherit;margin-top:10px;margin-bottom:10px}  p{margin:0 0 10px}  blockquote{padding:10px 20px;margin:0 0 20px;border-left:5px solid #eee}  table{background-color:transparent}  .table{width:100%;max-width:100%;margin-bottom:20px}  h6{font-weight:100;font-size:10px}  body{line-height:1.42857143;font-family:"open sans","Helvetica Neue",Helvetica,Arial,sans-serif;background-color:#2f4050;font-size:13px;color:#676a6c;overflow-x:hidden}  .table>tbody>tr>td{vertical-align:top;border-top:1px solid #e7eaec;line-height:1.42857;padding:8px}  .white-bg{background-color:#fff}  td{padding:6}  .table-valores-totales tbody>tr>td{border-top:0 none!important}td{padding: 3px;}
    </style>
    </head>
   
    <body class="white-bg">
        <table width="100%">
            <tbody>
                <tr>
                    <td style="padding:30px;">
                        <table width="100%" height="200px" border="0" aling="center" cellpadding="0" cellspacing="0">
                            <tbody>
                                <tr>
                                    <td width="40%" height="90" align="center">
                                        <span>
                                            <img src="{{ 'storage/'.$params['system']['logo']}}" height="100" style="text-align:center" border="0">
                                        </span>
                                    </td>
                                    <td width="5%" height="40" align="center"></td>
                                    <td width="45%" rowspan="2" valign="bottom" style="padding-left:0">
                                        <div class="tabla_borde">
                                            <table width="100%" border="0" height="200" cellpadding="6" cellspacing="0">
                                                <tbody>
                                                    <tr>
                                                        <td align="center">
                                                            <span style="font-family:Tahoma, Geneva, sans-serif; font-size:29px" text-align="center">GUÍA DE REMISIÓN REMITENTE</span>
                                                            <br>
                                                            <span style="font-family:Tahoma, Geneva, sans-serif; font-size:19px" text-align="center">E L E C T R Ó N I C A</span>
                                                        </td>
                                                    </tr>
                                                    @php
                                                        //$data = json_decode($despatch, true);
                                                    @endphp
                                                    <tr>
                                                        <td align="center">
                                                            <span style="font-size:15px" text-align="center">R.U.C.: {{$despatch->getCompany()->getRuc()}} </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="center">
                                                            <span style="font-size:24px">{{$despatch->getSerie()}}-{{$despatch->getCorrelativo()}}</span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="bottom" style="padding-left:0">
                                        <div class="tabla_borde">
                                            <table width="96%" height="100%" border="0" border-radius="" cellpadding="9" cellspacing="0">
                                                <tbody>
                                                    <tr>
                                                        <td align="center">
                                                            <strong>
     
                                                                <span style="font-size:15px">{{$despatch->getCompany()->getRazonSocial()}}</span>
                                                            </strong>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="left">
                                                            <strong>Dirección: </strong>{{$despatch->getCompany()->getAddress()->getDireccion()}}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="left">
                                                            <b>Surcursal:</b> 
                                                            Almacen Chorrillos
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <br>
                        <div class="tabla_borde">
                            <table width="100%" border="0" cellpadding="5" cellspacing="0">
                                <tbody>
                                    <tr>
                                        <td colspan="2">DESTINATARIO</td>
                                    </tr>
                                    <tr class="border_top">
                                        <td width="60%" align="left">
                                            <strong>Razón Social:</strong>{{$despatch->getDestinatario()->getRznSocial()}}
                                        </td>
                                        <td width="40%" align="left">
                                            <strong>RUC:</strong>  {{$despatch->getDestinatario()->getNumDoc()}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="40%" align="left" colspan="2">
                                            <strong>Dirección:</strong> {{$despatch->getDestinatario()->getAddress()->getDireccion()}}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <br>
                        <div class="tabla_borde">
                            <table width="100%" border="0" cellpadding="5" cellspacing="0">
                                <tbody>
                                    <tr>
                                        <td colspan="2">ENVIO</td>
                                    </tr>
                                    <tr class="border_top">
                                        <td width="60%" align="left">
                                            <strong>Fecha Emisión:</strong> {{$despatch->getFechaEmision()->format('d-m-Y')}}
                                        </td>
                                        <td width="40%" align="left">
                                            <strong>Fecha Inicio de Traslado:</strong>   {{$despatch->getEnvio()->getFecTraslado()->format('d-m-Y')}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="60%" align="left">
                                            <strong>Motivo Traslado:</strong>   {{$despatch->getEnvio()->getCodTraslado()}} | Traslado entre establecimiento de la misma empresa
                                        </td>
                       
                                        @if ($despatch->getEnvio()->getModTraslado() == "01")
                                            @php $var_modtraslado = "Transporte publico" @endphp
                                        @else
                                            @php $var_modtraslado = "Transporte privado" @endphp
                                        @endif
                                        <td width="40%" align="left">
                                            <strong>Modalidad de Transporte:</strong>  {{$despatch->getEnvio()->getModTraslado()}} | {{$var_modtraslado}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="60%" align="left">
                                            <strong>Peso Bruto Total ({{$despatch->getEnvio()->getUndPesoTotal()}}):</strong> {{$despatch->getEnvio()->getPesoTotal()}}
                                        </td>
                                        <td width="40%">
                                            <strong>Area:</strong>  {{ $despatch->getAreatrabajo() }} 
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="60%" align="left">
                                            <strong>P. Partida:</strong> {{$despatch->getEnvio()->getPartida()->getUbigueo()}} - {{$despatch->getEnvio()->getPartida()->getDireccion()}}
                                        </td>
                                        <td width="40%" align="left">
                                            <strong>P. Llegada: </strong>  {{$despatch->getEnvio()->getLlegada()->getUbigueo()}} - {{$despatch->getEnvio()->getLlegada()->getDireccion()}}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <br>
       

                        @if ($despatch->getEnvio()->getModTraslado() == "01")
                        <div class="tabla_borde">
                            <table width="100%" border="0" cellpadding="5" cellspacing="0">
                                <tbody>
                                <tr>
                                    <td colspan="2">TRANSPORTE PUBLICO</td>
                                </tr>
                                <tr class="border_top">
                                    <td width="40%" align="left"><strong>Razón Social:</strong>  {{ $despatch->getEnvio()->getTransportista()->getRznSocial() }}</td>
                                    <td width="30%" align="left"><strong>{{ SunatService::catalogo6($despatch->getEnvio()->getTransportista()->getTipoDoc()) }}:</strong>  {{ $despatch->getEnvio()->getTransportista()->getNumDoc()}}</td>
                                    <td width="30%" align="left"><strong>MTC:</strong> {{ $despatch->getEnvio()->getTransportista()->getNroMtc() }}</td>
                                </tr>
                               
                                <tr>
                                    <td width="40%" align="left"><strong>Vehiculo:</strong>  {{ $despatch->getEnvio()->getVehiculo()->getPlaca()}}</td>
                                    <td width="30%" align="left"><strong>NroAutorizacion MTC:</strong>  {{ $despatch->getEnvio()->getVehiculo()->getNroAutorizacion() }}</td>
                                    <td width="30%" align="left"><strong>Conductor:</strong>  {{ SunatService::catalogo6($despatch->getEnvio()->getChoferes()[0]->getTipoDoc())  }} | {{ $despatch->getEnvio()->getChoferes()[0]->getNroDoc() }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div><br>
                            
                        @else

                        <div class="tabla_borde">
                            <table width="100%" border="0" cellpadding="5" cellspacing="0">
                                <tbody>
                                <tr>
                                    <td colspan="2">TRANSPORTE PRIVADO</td>
                                </tr>
                                <tr class="border_top">
                                    <td width="40%" align="left"><strong>Vehiculo:</strong>  {{ $despatch->getEnvio()->getVehiculo()->getPlaca() }}</td>
                                    <td width="30%" align="left"><strong>NroAutorizacion MTC:</strong>  {{ $despatch->getEnvio()->getVehiculo()->getNroAutorizacion() }}</td>
                                    <td width="30%" align="left"><strong>Conductor:</strong>   {{ SunatService::catalogo6($despatch->getEnvio()->getChoferes()[0]->getTipoDoc()) }} | {{ $despatch->getEnvio()->getChoferes()[0]->getNroDoc() }}</td>
                                </tr>
                                </tbody></table>
                        </div><br>
                            
                        @endif


                        <div class="tabla_borde">
                            <table width="100%" border="0" cellpadding="5" cellspacing="0">
                                <tbody>
                                    <tr>
                                        <td align="center" class="bold">Item</td>
                                        <td align="center" class="bold">Código</td>
                                        <td align="center" class="bold" width="300px">Descripción</td>
                                        <td align="center" class="bold">Unidad</td>
                                        <td align="center" class="bold">Cantidad</td>
                                        <td align="center" class="bold">Equipo</td>
                                    </tr>
                                    @foreach ($despatch->getDetails() as $item)
                                        
                                        <tr class="border_top">
                                            <td align="center">{{$loop->index+1}}</td>
                                            <td align="center">{{$item->getCodigo()}}</td>
                                            <td align="center">{{$item->getDescripcion()}}</td>
                                            <td align="center">{{$item->getUnidad()}}</td>
                                            <td align="center">{{$item->getCantidad()}}</td>
                                            <td align="center">{{$item->getEquipo()}}</td>
                                        </tr>
                                    @endforeach
                       
                                </tbody>
                            </table>
                        </div>
                        <div>
                        <table width="100%"> 
                            <tr>
                                <td>   
                                    <table width="50%" border="0" cellpadding="0" cellspacing="0">
                                        <tbody>
                                            @if (count($despatch->getEnvio()->getVehiculo()->getSecundarios())>0)
                                                <tr>
                                                    <td width="50%" colspan="2" align="left">VEHICULO SECUNDARIOS</td>
                                                </tr>
                                                @foreach ($despatch->getEnvio()->getVehiculo()->getSecundarios() as $item)
                                                <tr>
                                                    <td align="left"><strong>Placa:</strong>  {{ $item->getPlaca() }}</td>
                                                    <td align="left"><strong>NroAutorizacion MTC:</strong>  {{  $item->getNroAutorizacion() }}</td>
                                                </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </td>
                                <td>
                                    <table width="90%" border="0" cellpadding="0" cellspacing="0">
                                        <tbody>
                                            @if (count($despatch->getEnvio()->getChoferes())>1)
                                                <tr>
                                                    <td align="right">CONDUCTORES SECUNDARIOS</td>
                                                </tr>
                                                @foreach (array_slice($despatch->getEnvio()->getChoferes(),1) as $item)
                                                <tr>
                                                    <td align="right"><strong>Conductor:</strong> {{ SunatService::catalogo6($item->getTipoDoc()) }} | {{ $item->getNroDoc() }}</td>
                                                </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        </div>
                        <div>
                            <table>
                                <tbody>
                                    <tr>
                                        <td width="100%">
                                            <blockquote>
                                                <strong>Resumen:</strong>  {{$params['system']['hash']}}
                                                <br>
                                                <span>Representación Impresa de la GUÍA DE REMISIÓN REMITENTE ELECTRÓNICA.</span>
                                            </blockquote>
                                        </td>
                                        <td width="25%" align="right">
                                            <blockquote>
                                                @if  ($params['system']['estcdrZip'] == '1')
                                                    <span><img src="{{ 'storage/'.$params['system']['qr']}}" height="100" style="text-align:center" border="0"></span>
                                                @endif
                                            </blockquote>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </body>
</html>