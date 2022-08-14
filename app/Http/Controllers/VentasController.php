<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request as RequestParams;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Exception;

class VentasController extends Controller{

    private $idSesson;
    private $codigoProducto;
    private $innerUrl;
    private $code;
    private $request;
    public $client;
    public $responseString;
    public $response=[];
    private $counter = 0;

    function __construct() {
        $this->client = new Client(['verify' => true]);
        $this->username   = 'samurai';
        $this->password   = 'Axam2021';
        $this->rut   = '76299574-3';
        $this->url = 'https://axam.managermas.cl/';
        //$this->setSale( \Illuminate\Http\Request $request);
    }
    public function setSale(RequestParams $request){
      //dd(json_decode($this->trySave($request)));
        $res=json_decode($this->trySave($request));
        $response['retorno']=$res->retorno;
        $response['mensaje']=$res->mensaje;
        return json_encode($response);
        
    }
 //login() para obtener token de manager+
    public function login()
    {
        try
        {
            $curl = curl_init();
            //$url = $this->getInnerUrl()->getUrl();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "$this->url"."api/auth/",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS =>"{\"username\":\"$this->username\", \"password\":\"$this->password\"}",
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json"
                ),
            ));

            $response = curl_exec($curl);

            $responseArrray = json_decode($response, true);

            $authToken = $responseArrray['auth_token'];
            $this->setIdSesson($authToken);
            curl_close($curl);
        }
        catch (Exception $e)
        {
            dd($e);
        }
    }
  
    
 //Armado formato Json para envio de datos hacia M+
    public function trySave(RequestParams $request)
    {
      //dd($request);
      $params = json_decode($request->getContent());

      $numOrden = $params->oc;
      $rut_cliente = substr($params->cliente->rut,0,-1).'-'.substr($params->cliente->rut, -1);
      $info_cliente = $this->getClientInfo(mb_strtoupper($rut_cliente), $request, $params);      
      //$cod_vendedor = 0;
      $cod_vendedor = "ventasamurai";      
      $cod_plazoPago = $this->getCodigoPlazoPago();   
      $cod_listaPrecio = $this->getCodigoListaPrecio();   
      $cod_unidadNegocio = "UNEG-001";
      
      $emissionDate = $params->fechaEmision;
      $fecha_doc = date('d/m/Y', $emissionDate);
      
      //$montoTotal = $params->totalAmount;
      //$montoAfecto = round($params->netAmount);
      //$montoIva = round($params->ivaAmount);
      
      $getMontos = $this->getMontos($params);
      
      if($params->tipoDocumento == 1)
        {
        $tipodocumento = 'NV';
          
      }if($params->tipoDocumento == 2){
        $tipodocumento = 'NV';
        
      //Rut de Axam 
      }if($this->rut == "76299574-3"){
        $tipodocumento = 'NV';
      }
      
      $fields =
            "{
            \n\t\"rut_empresa\": \"$this->rut\",
            \n\t\"tipodocumento\": \"$tipodocumento\",
            \n\t\"num_doc\": \"0\",
            \n\t\"fecha_doc\": \"$fecha_doc\",
            \n\t\"fecha_ref\": \"\",
            \n\t\"fecha_vcto\": \"$fecha_doc\",
            \n\t\"modalidad\": \"S\",
            \n\t\"cod_unidnegocio\": \"$cod_unidadNegocio\",
            \n\t\"rut_cliente\": \"$info_cliente[0]\",
            \n\t\"dire_cliente\": \"$info_cliente[1]\",
            \n\t\"rut_facturador\": \"\",
            \n\t\"cod_vendedor\": \"$cod_vendedor\",
            \n\t\"cod_comisionista\": \"$cod_vendedor\",
            \n\t\"lista_precio\": \"$cod_listaPrecio\",
            \n\t\"plazo_pago\": \"$cod_plazoPago\",
            \n\t\"cod_moneda\": \"CLP\",
            \n\t\"tasa_cambio\": \"\",
            \n\t\"afecto\": \"$getMontos[0]\",
            \n\t\"exento\": \"0\",
            \n\t\"iva\": \"$getMontos[1]\",
            \n\t\"imp_esp\": \"\",
            \n\t\"iva_ret\": \"\",
            \n\t\"imp_ret\": \"\",
            \n\t\"tipo_desc_global\": \"\",
            \n\t\"monto_desc_global\": \"\",
            \n\t\"total\": \"$getMontos[2]\",
            \n\t\"deuda_pendiente\": \"0\",
            \n\t\"glosa\": \"$numOrden\",
            \r\n\"detalles\":
            [\r\n";

      
      $products = $params->detalle;

      foreach($products as $item)
      {
       // dd($item->productId);
        $codigo_producto = $item->idProducto;

        if($tipodocumento == 'BOVE')
        {
          //$totalProducto = round($item->price);
          $precio_producto = round($item->precio, 4);
          $precio_despacho = round($params->totalDespacho, 4);
          
        }
        if($tipodocumento == 'FAVE' || $tipodocumento == 'NV')
        {
          $precio_producto = round($item->neto, 4);
          $precio_despacho = round($params->despachoNeto, 4);
        }
       
        
        $cantidad_producto = $item->cantidad;
        $cod_bodega = $this->getCodigoBodega();     
        $cod_ubicacion = $this->getCodigoUbicacion();      
        $cod_centroCosto = $this->getCodigoCentroCosto();     
        $unidad_producto = $this->getUnidadProducto($codigo_producto);
        $cod_stock = $this->getCodStock();
                     
            $fields .= "{\r\n
            \n\t\t\"cod_producto\": \"$codigo_producto\",
            \n\t\t\"cantidad\": \"$cantidad_producto\",
            \n\t\t\"unidad\": \"$unidad_producto\",
            \n\t\t\"precio_unit\": \"$precio_producto\",
            \n\t\t\"moneda_det\": \"CLP\",
            \n\t\t\"tasa_cambio_det\": \"\",
            \n\t\t\"nro_serie\": \"\",
            \n\t\t\"num_lote\": \"\",
            \n\t\t\"fecha_vec\": \"\",
            \n\t\t\"cen_cos\": \"$cod_centroCosto\",
            \n\t\t\"tipo_desc\": \"\",
            \n\t\t\"descuento\": \"\",
            \n\t\t\"ubicacion\": \"$cod_ubicacion\",
            \n\t\t\"bodega\": \"$cod_bodega\",
            \n\t\t\"concepto1\": \"\",
            \n\t\t\"concepto2\": \"\",
            \n\t\t\"concepto3\": \"\",
            \n\t\t\"concepto4\": \"\",
            \n\t\t\"descrip\": \"\",
            \n\t\t\"desc_adic\": \"\",
            \n\t\t\"stock\": \"$cod_stock\",
            \n\t\t\"comentario1\": \"\",
            \n\t\t\"comentario2\": \"\",
            \n\t\t\"comentario3\": \"\",
            \n\t\t\"comentario4\": \"\",
            \n\t\t\"comentario5\": \"\",
            \n\t\t\"cod_impesp1\": \"\",
            \n\t\t\"mon_impesp1\": \"\",
            \n\t\t\"cod_impesp2\": \"\",
            \n\t\t\"mon_impesp2\": \"\",
            \n\t\t\"fecha_comp\": \"\",
            \n\t\t\"porc_retencion\": \"\"
            \r\n},";
                 
        }
      
        if($params->tipoDespacho == 1)
        {
        $codigo_despacho = $this->getCodigoDespacho();
          
        $fields .= "{\r\n
            \n\t\t\"cod_producto\": \"DPCHO\",
            \n\t\t\"cantidad\": \"1\",
            \n\t\t\"unidad\": \"UMS\",
            \n\t\t\"precio_unit\": \"$precio_despacho\",
            \n\t\t\"moneda_det\": \"CLP\",
            \n\t\t\"tasa_cambio_det\": \"\",
            \n\t\t\"nro_serie\": \"\",
            \n\t\t\"num_lote\": \"\",
            \n\t\t\"fecha_vec\": \"\",
            \n\t\t\"cen_cos\": \"$cod_centroCosto\",
            \n\t\t\"tipo_desc\": \"\",
            \n\t\t\"descuento\": \"\",
            \n\t\t\"ubicacion\": \"$cod_ubicacion\",
            \n\t\t\"bodega\": \"$cod_bodega\",
            \n\t\t\"concepto1\": \"\",
            \n\t\t\"concepto2\": \"\",
            \n\t\t\"concepto3\": \"\",
            \n\t\t\"concepto4\": \"\",
            \n\t\t\"descrip\": \"\",
            \n\t\t\"desc_adic\": \"\",
            \n\t\t\"stock\": \"$cod_stock\",
            \n\t\t\"comentario1\": \"\",
            \n\t\t\"comentario2\": \"\",
            \n\t\t\"comentario3\": \"\",
            \n\t\t\"comentario4\": \"\",
            \n\t\t\"comentario5\": \"\",
            \n\t\t\"cod_impesp1\": \"\",
            \n\t\t\"mon_impesp1\": \"\",
            \n\t\t\"cod_impesp2\": \"\",
            \n\t\t\"mon_impesp2\": \"\",
            \n\t\t\"fecha_comp\": \"\",
            \n\t\t\"porc_retencion\": \"\"
            \r\n},";
         }
   
        $fields = substr($fields, 0, -1);
        $fields .= "
                \r\n  ]}";
    
            //dd($this->sale($fields));
            $response=$this->sale($fields);
             
        return json_encode($response);
   
    }
  //Envio de formato Json hacia Manager+
   public function sale($fields){
   
        $this->login();
        //$curl = curl_init();
        //$url = $this->getInnerUrl()->getUrl();
      $res= $this->client->request('POST',$this->url.'api/import/create-document/?emitir=N&docnumreg=N',[
            'headers'=>[
                'Authorization' => "token ".$this->idSesson,
                'Content-Type' => 'application/json',
            ],
            'body'=> $fields
            ]);
           // dd($res->getBody()->getContents());
                $response = json_decode($res->getBody()->getContents(), true);
                //dd($response);
              return  $response;    
           
            
        } /*
        curl_setopt_array($curl, array(
            CURLOPT_URL => "$this->url"."api/import/create-document/?emitir=N&docnumreg=N",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>"$fields",
            CURLOPT_HTTPHEADER => array(
                "Authorization: token $this->idSesson",
                "Content-Type: application/json"
            ),
        ));
 
        $response = curl_exec($curl);
        //dd(json_decode($response));
            return json_decode($response);*/
        
      

      
    
  //get cliente // If existe retorna RUT y Direccion // else crea nuevo cliente, retorna RUT y Direccion ingresados recientemente
  //No ingresa venta si no existe rut registrado en M+
   public function getClientInfo($rut_cliente, $request, $params){
        
        $this->login();
        $curl = curl_init();
        //$url = $this->getInnerUrl()->getUrl();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "$this->url"."api/clients/$this->rut/$rut_cliente/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: token $this->idSesson",
                "Content-Type: application/json"
            ),
        ));

        $curlResponse = curl_exec($curl);
     
        $response = json_decode($curlResponse,true);

        
        if(!empty($response['data'])){
            $firstName = $request['cliente']['nombre'];
            $lastName = $request['cliente']['apellido'];
            $razon_social = $response['data'][0]['razon_social'];
            $giro = $response['data'][0]['giro'];
            $email = $response['data'][0]['email'];
            $telefono = $request['cliente']['telefono'];
            $dire_cliente = $response['data'][0]['direccion'];
            $comuna =  $request['cliente']['direccion'][0]['municipalidad'];
            $ciudad =  $request['cliente']['direccion'][0]['ciudad'];
            //dd($response);
            //dd($request->client['address'][0]['city']);
            return $this->saveClient($rut_cliente, $firstName, $lastName, $razon_social, $giro, $dire_cliente, $email, $telefono, $comuna, $ciudad, $params);
        }else{
            $firstName = $request['cliente']['nombre'];
            $lastName = $request['cliente']['apellido'];
            $razon_social = $request['cliente']['razonSocial'];
            $giro = $request['cliente']['giro'];
            $email = $request['cliente']['email'];
            $telefono = $request['cliente']['telefono'];
            $dire_cliente = $request['cliente']['direccion']['direccion'] . ' ' . $request['cliente']['direccion']['numero'] . ', ' .       $request['cliente']['direccion']['municipalidad'];
            $comuna =  $request['cliente']['direccion']['municipalidad'];
            //$ciudad =  $request->client['direccion'][0]['ciudad']; //Samurai no envia parametro city.
          //dd($ciudad);
            $ciudad = "Santiago";//Parametro provisorio
         
            return $this->saveClient($rut_cliente, $firstName, $lastName, $razon_social, $giro, $dire_cliente, $email, $telefono, $comuna, $ciudad, $params);
          
        }

     
    }
  //Post para guardar datos de nuevo cliente
   public function saveClient($rut_cliente, $firstName, $lastName, $razon_social, $giro, $dire_cliente, $email, $telefono, $comuna, $ciudad, $params){
             
        $cod_vencimientoCliente = $this->getCodigoVencimientoCliente();
        $cod_comuna = $this->getComunaInfo($comuna);  
        $cod_ciudad = $this->getCiudadInfo($ciudad);
        $codVend = "ventasamurai";
        $listaPrecio = $this->getCodigoListaPrecio();
        $caracteristica1 = $this->getCaracteristica1();
        $clasificacion = $this->getClasificacionCliente();
        $tipoCliente = $this->getTipoCliente();
        
        if($params->tipoDocumento == 1)
        {
        $razon_social = $firstName . ' ' . $lastName;
        $giro = "Persona Natural";
        $cod_comuna = $cod_comuna;//"13101";
        $cod_ciudad = $cod_ciudad;//"13";
        $dire_cliente = $dire_cliente;//"Direccion Principal";  
        }
        if($params->tipoDocumento == 2)
        {
        $razon_social = $razon_social;
        $giro = $giro;
        $cod_comuna = $cod_comuna;
        $cod_ciudad = $cod_ciudad;
        }
        $atencion = $this->getAtencion($razon_social);
        
        $fields =
            "{
            \n\t\"rut_empresa\": \"$this->rut\",
            \n\t\"rut_cliente\": \"$rut_cliente\",
            \n\t\"razon_social\": \"$razon_social\",
            \n\t\"nom_fantasia\": \"$razon_social\",
            \n\t\"giro\": \"$giro\",
            \n\t\"holding\": \"\",
            \n\t\"area_prod\": \"\",
            \n\t\"clasif\": \"$clasificacion\",
            \n\t\"email\": \"$email\",
            \n\t\"emailsii\": \"$email\",
            \n\t\"comentario\": \"\",
            \n\t\"tipo\": \"C\",
            \n\t\"tipo_prov\": \"N\",
            \n\t\"vencimiento\": \"$cod_vencimientoCliente\",
            \n\t\"plazo_pago\": \"\",
            \n\t\"cod_vendedor\": \"$codVend\",
            \n\t\"cod_comis\": \"\",
            \n\t\"lista_precio\": \"$listaPrecio\",
            \n\t\"comen_emp\": \"\",
            \n\t\"descrip_dir\": \"$dire_cliente\",
            \n\t\"direccion\": \"$dire_cliente\",
            \n\t\"cod_comuna\": \"$cod_comuna\",
            \n\t\"cod_ciudad\": \"$cod_ciudad\",
            \n\t\"atencion\": \"$atencion\",
            \n\t\"emailconta\": \"$email\",
            \n\t\"telefono\": \"$telefono\",
            \n\t\"fax\": \"\",
            \n\t\"cta_banco\": \"\",
            \n\t\"cta_tipo\": \"\",
            \n\t\"cta_corr\": \"\",
            \n\t\"id_ext\": \"\",
            \n\t\"texto1\": \"\",
            \n\t\"texto2\": \"\",
            \n\t\"caract1\": \"$caracteristica1\",
            \n\t\"caract2\": \"\"
            }";
            //dd($fields);
  
//Guardar nuevo cliente  
      $this->login();
      $curl = curl_init();
      //$url = $this->getInnerUrl()->getUrl();
      
      curl_setopt_array($curl, array(
            CURLOPT_URL => "$this->url"."api/import/create-client/?sobreescribir=S",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>"$fields",
            CURLOPT_HTTPHEADER => array(
                "Authorization: token $this->idSesson",
                "Content-Type: application/json"
            ),
        ));

      $response = curl_exec($curl);
      curl_close($curl);
  
        $fields2 =
            "{
            \n\t\"rut_empresa\": \"$this->rut\",
            \n\t\"rut_cliente\": \"$rut_cliente\",
            \n\t\"nombres\": \"$firstName\",
            \n\t\"apaterno\": \"$lastName\",
            \n\t\"amaterno\": \"null\",
            \n\t\"cargo\": \"Contacto de cliente\",
            \n\t\"email\": \"$email\",
            \n\t\"telefono\": \"$telefono\",
            \n\t\"saludo\": \"Estimado(a)\",
            \n\t\"direccion\": \"$dire_cliente\",
            \n\t\"contprin\": \"S\",
            \n\t\"contdesp\": \"S\",
            \n\t\"contfact\": \"S\",
            \n\t\"contvent\": \"S\",
            \n\t\"contcomp\": \"S\"
            }";
     
   //Con los datos del nuevo cliente se llena tabla contacto de M+    
      $this->login();
      $curl = curl_init();
      //$url = $this->getInnerUrl()->getUrl();
     
      curl_setopt_array($curl, array(
            CURLOPT_URL => "$this->url"."api/import/create-contact/?sobreescribir=S",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>"$fields2",
            CURLOPT_HTTPHEADER => array(
                "Authorization: token $this->idSesson",
                "Content-Type: application/json"
            ),
        ));

      $response = curl_exec($curl);
      curl_close($curl);
     
        return array($rut_cliente, $dire_cliente);
    }
  
    public function getCodigoVendedor($cod_vendedor){
      switch ($this->rut) {
          //Appia
                case '76831659-7':
                    $cod_vendedor = "vendedorsamurai";
                    break;
          //Axam
                case '76299574-3':
                    $cod_vendedor = "ventasamurai";
                    break;
          //Bbcos
                case '76795658-4':
                    $cod_vendedor = "bbcosweb";
                    break;
      }
      return $cod_vendedor;
    }
  
    public function getCodigoPlazoPago(){
      switch ($this->rut) {
                case '76831659-7':
                    $cod_plazoPago = "001";
                    break;
                case '76299574-3'://Axam
                    $cod_plazoPago = "0";
                    break;
                case '76795658-4':
                    $cod_plazoPago = "01";
                    break;
      }
      return $cod_plazoPago;
    }
  
    public function getCodigoListaPrecio(){
      switch ($this->rut) {
                case '76831659-7':
                    $cod_listaPrecio = "4";
                    break;
                case '76299574-3':
                    $cod_listaPrecio = "18";
                    break;
                case '76795658-4':
                    $cod_listaPrecio = "1";
                    break;
      }
      return $cod_listaPrecio;
    }

    public function getCodigoUnidadNegocio(){
      switch ($this->rut) {
                
                case '76299574-3':
                    $cod_unidadNegocio = "UNEG-001";
                    break;
                
      }
      return $cod_unidadNegocio;
    }
  
    public function getCodigoBodega(){
      switch ($this->rut) {
                case '76831659-7':
                    $cod_bodega = "111";
                    break;
                case '76299574-3':
                    $cod_bodega = "B1";
                    break;
                case '76795658-4':
                    $cod_bodega = "B1";
                    break;
      }
      return $cod_bodega;
    }

    
     public function getCodigoUbicacion(){
      switch ($this->rut) {
                case '76831659-7':
                    $cod_ubicacion = "U1";
                    break;
                case '76299574-3':
                    $cod_ubicacion = "";
                    break;
                case '76795658-4':
                    $cod_ubicacion = "U01";
                    break;
      }
      return $cod_ubicacion;
    }
  
    public function getCodigoCentroCosto(){
      switch ($this->rut) {
                case '76831659-7':
                    $cod_centroCosto = "200";
                    break;
                case '76299574-3':
                    $cod_centroCosto = "A05";
                    break;
                case '76795658-4':
                    $cod_centroCosto = "1000";
                    break;
      }
      return $cod_centroCosto;
    }
   
    public function getCodigoVencimientoCliente(){
      switch ($this->rut) {
                case '76831659-7':
                    $cod_vencimientoCliente = "001";
                    break;
                case '76299574-3':
                    $cod_vencimientoCliente = "0";
                    break;
                case '76795658-4':
                    $cod_vencimientoCliente = "06";
                    break;
      }
      return $cod_vencimientoCliente;
    }
  
    public function getCaracteristica1(){
      switch ($this->rut) {
                case '76831659-7':
                    $caracteristica1 = "";
                    break;
                case '76299574-3':
                    $caracteristica1 = "";
                    break;
                case '76795658-4':
                    $caracteristica1 = "03";
                    break;
      }
      return $caracteristica1;
    }
  
    public function getCodStock(){
      switch ($this->rut) {
                case '76831659-7':
                    $cod_stock = "M";
                    break;
                case '76299574-3':
                    $cod_stock = "N";
                    break;
                case '76795658-4':
                    $cod_stock = "M";
                    break;
      }
      return $cod_stock;
    }
  
    public function getCodigoDespacho(){
      switch ($this->rut) {
                case '76831659-7':
                    $codigo_despacho = "DPCHO";
                    break;
                case '76299574-3':
                    $codigo_despacho = "Serv-Prest001";
                    break;
                case '76795658-4':
                    $codigo_despacho = "DPCHO";
                    break;
      }
      return $codigo_despacho;
    }
  public function getTipoCliente(){
      switch ($this->rut) {
                case '76831659-7': //Appia
                    $tipoCliente = "C";
                    break;
                case '76299574-3': //Axam
                    $tipoCliente = "E-COMMERCE";
                    break;
                case '76795658-4': // Bbcos
                    $tipoCliente = "C";
                    break;
      }
      return $tipoCliente;
  }
  public function getAtencion($razon_social){
      switch ($this->rut) {
                case '76831659-7': //Appia
                    $atencion = "Atención";
                    break;
                case '76299574-3': //Axam
                    $atencion = $razon_social;
                    break;
                case '76795658-4': // Bbcos
                    $tipoCliente = "Atención";
                    break;
      }
      return $atencion;
  }
  
    public function getClasificacionCliente(){
      switch ($this->rut) {
                case '76831659-7':
                    $clasificacion = "";
                    break;
                case '76299574-3':
                    $clasificacion = "A5";
                    break;
                case '76795658-4':
                    $clasificacion = "";
                    break;
      }
      return $clasificacion;
    }
  
    public function getMontos($params){
           
      $totalNeto = 0;
      $netoDespacho = round($params->despachoNeto);
      
      $products = $params->detalle;
      
      foreach($products as $item)
      {
       $netoProducto = $item->neto;
       $cantidad = $item->cantidad;
       $totalNetoProducto = round($netoProducto*$cantidad);
       $totalNeto+=$totalNetoProducto;
        
       }
       $netoDoc = round($totalNeto+$netoDespacho);
       $ivaDoc = round($netoDoc*0.19);
       $totalDoc = $netoDoc+$ivaDoc;
      
      return array($netoDoc, $ivaDoc, $totalDoc);
      }
      
    public function getUnidadProducto($codigo_producto){
        
        $this->login();
        $curl = curl_init();
        //$url = $this->getInnerUrl()->getUrl();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "$this->url"."api/products/$this->rut/$codigo_producto",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: token $this->idSesson",
                "Content-Type: application/json"
            ),
        ));

        $curlResponse = curl_exec($curl);
    
        $response = (json_decode($curlResponse,true));

 
        if(empty($response['data'])){
            $codigo_producto = "No se encuentra codigo producto";
        }else{
            $codigo_producto = $response['data'][0]['unidadstock'];
        }
            //dd($codigo_producto);
          return $codigo_producto;
          
        } 
  
    public function getComunaInfo($comuna){
        
        $this->login();
        $curl = curl_init();
        //$url = $this->getInnerUrl()->getUrl();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "$this->url"."api/comunas/$comuna",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: token $this->idSesson",
                "Content-Type: application/json"
            ),
        ));

        $curlResponse = curl_exec($curl);
    
        $response = (json_decode($curlResponse,true));

        if(empty($response['data'])){
            $comuna = "No se encuentra codigo comuna";
        }else{
            $comuna = $response['data'][0]['code_ext'];
        }
 
          return $comuna;
          
        }
  
    public function getCiudadInfo($ciudad){
        
        $this->login();
        $curl = curl_init();
        //$url = $this->getInnerUrl()->getUrl();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "$this->url"."api/ciudades/$ciudad/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: token $this->idSesson",
                "Content-Type: application/json"
            ),
        ));

        $curlResponse = curl_exec($curl);
    
        $response = (json_decode($curlResponse,true));

  
        if(empty($response['data'])){
            $ciudad = "No se encuentra codigo comuna";
        }else{
            $ciudad = $response['data'][0]['code'];
        }

          return $ciudad;
          
        }


    
        /**
     * @return mixed
     */
    public function getIdSesson()
    {
        return $this->idSesson;
    }

    /**
     * @param mixed $idSesson
     */
    public function setIdSesson($idSesson): void
    {
        $this->idSesson = $idSesson;
    }

    /**
     * @return mixed
     */
    public function getInnerUrl()
    {
        return $this->innerUrl;
    }

    /**
     * @param mixed $innerUrl
     */
    public function setInnerUrl($innerUrl): void
    {
        $this->innerUrl = $innerUrl;
    }

    public function getResponseString()
    {
        return $this->responseString;
    }
  
    public function setResponseString($responseString): void
    {
        $this->responseString = $responseString;
    }
    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code): void
    {
        $this->code = $code;
    }
}