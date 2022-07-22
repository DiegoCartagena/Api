<?php

namespace App\Http\Packages\ManagerPlus;
use Illuminate\Http\Request as RequestParams;
use App\Http\Packages\Url;
use App\Http\Packages\Request;

class RequestAllProducts extends Request implements IRequestAllProducts
{
    private $idSesson;
    private $codigoProducto;
    private $innerUrl;
    private $code;
    public $response = array();
    private $counter = 0;


    function __construct(RequestParams $request) {
        $this->username   = $request->header('clientid');
        $this->password   = $request->header('secretid');
        $this->rut   = $request->header('token');
        $this->code = $request->code;
    }

    public function make(Url $url)
    {
      //dd($url);
        $this->setInnerUrl($url);
        $this->tryRequest($url);
      
        return $this;
    }
    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function login()
    {
        try
        {
            $curl = curl_init();
            $url = $this->getInnerUrl()->getUrl();
            //dd($url);
            curl_setopt_array($curl, array(
                CURLOPT_URL => "$url"."api/auth/",
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
                //dd($curl);
            $response = curl_exec($curl);

            $responseArrray = json_decode($response, true);
            //dd($responseArrray);
            $authToken = $responseArrray['auth_token'];
            $this->setIdSesson($authToken);
            
            curl_close($curl);
        }
        catch (Throwable $t)
        {
            dd($t);
        }
    }


    /**
     * @param Url $url
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function tryRequest()
    {
        $this->login();
        $curl = curl_init();
        $url = $this->getInnerUrl()->getUrl();
         //dd($url);
        curl_setopt_array($curl, array(
            CURLOPT_URL => "$url"."api/pricelist/$this->rut/?dets=1",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0,
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => array(
                "Authorization: token $this->idSesson",
                "Content-Type: application/json"
            ),
        ));
        
        $curlResponse = curl_exec($curl);
        $res = array(json_encode($curlResponse));
        $this->setInnerResponse(json_decode($curlResponse));
        
        $this->fillResponse();    
       
      
    }
  
    public function fillResponse(){
           
      $products = $this->getInnerResponse();
     
      //dd($products);
      $lista = $this->getListaPorCliente($products);
      foreach ($lista->products as $key => $products){
        
        $codigo = $products->cod;
        $stock = $this->getStock($codigo);
          //dd($this->code);
        
        $this->response[$this->counter]['id'] = $products->cod;
        $this->response[$this->counter]['code'] = $products->cod;
        $this->response[$this->counter]['name'] = $products->name;
        $this->response[$this->counter]['price'] = $products->price;
        $this->response[$this->counter]['description'] = $products->descrip;
        $this->response[$this->counter]['state'] = 1;
        $this->response[$this->counter]['stock'] = $stock;
  
        $this->counter++;
        }
    }
  
    public function getListaPorCliente($products){
      
      switch ($this->rut) {
          //AppiaGroup
                case '76831659-7':
                    //dd($products->data[3]);
          foreach($products->data as $product){
                         if($product->id==5){
                         
                            $buscarLista = $product;
                               }
                            }
                    //$buscarLista = $products;
                    break;
           //Axam/Lista Ecommerce 2022//id:18
                case '76299574-3':
                      foreach($products->data as $product){
                         if($product->id==18){
                         
                            $buscarLista = $product;
                               }
                            }
          //dd($buscarLista);
                    break;
                   
                case '76795658-4':
          //Bbcos//PaginaWeb//id:86//
                    foreach($products->data as $product){
                         if($product->id==86){
                         
                            $buscarLista = $product;
                               }
                            }
                    break;
      }
    
      return $buscarLista;
    }
  
    public function getStock($codigo){
      
        //$this->login();
        $curl = curl_init();
        $url = $this->getInnerUrl()->getUrl();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "$url"."api/stock/$this->rut/$codigo/?dets=1&resv=0",
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
        //dd($curl);
        $curlResponse = curl_exec($curl);
        $response = json_decode($curlResponse, true);
 
        $bodega = $this->getBodegaCliente();
        $sumaStock = 0;
        $codigoBodega = 1;
        //dd($response);    
     
        if(empty($response['data'])){
          return null;
        }else{
          foreach($response['data'][0]['stock'] as $stock){
            //dd($value['']['nombre_bodega_stock']);
            if($stock['bodega_stock'] == $bodega){
            
            $sumaStock+=$stock['saldo'];  
            $codigoBodega = $this->codigoBodega($stock['bodega_stock']);  
              
            }else{
              $sumaStock+=0;  
            $codigoBodega = $codigoBodega;
            }
          }
          $stockArray[] = [
                      'officeId' =>$codigoBodega,
                      'quantity'=>$sumaStock
                  ];
           
          return $stockArray;
        } 
      }
     
    public function getBodegaCliente(){
      switch ($this->rut) {
          //AppiaGroup
                case '76831659-7':
                    $bodega = "111";
                    break;
          //Axam
                case '76299574-3':
                    $bodega = "B1";
                    break;
          //Bbcos
                case '76795658-4':
                    $bodega = "B1";
                    break;
      }
    
      return $bodega;
    }
      
  
    public function codigoBodega($stockCodigoBodega){
      switch ($stockCodigoBodega) {
          case 'B1':
              $stockCodigoBodega = "1";
              break;
          case 'B2':
              $stockCodigoBodega = "2";
              break;
          case 'B3':
              $stockCodigoBodega = "3";
              break;
          case 'B4':
              $stockCodigoBodega = "4";
              break;
          case 'B5':
              $stockCodigoBodega = "5";
              break;
          default:
          return $stockCodigoBodega;
          break;
      }
      return $stockCodigoBodega;
    }
  
    public function getResponse(){
        return $this->response;
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
