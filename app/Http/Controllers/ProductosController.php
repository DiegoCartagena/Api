<?php

namespace App\Http\Controllers;

//namespace App\Http\Packages\ManagerPlus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request as RequestParams;
use App\Http\Packages\Url;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Exception;

class ProductosController extends Controller{


    private $idSesson;
    private $codigoProducto;
    private $innerUrl;
    private $code;
    public $client;
    public $response=[];
    private $counter = 0;

    function __construct(RequestParams $request) {
        $this->client = new Client(['verify' => false]);
        $this->username   = 'samurai';
        $this->password   = 'Axam2021';
        $this->rut   = '76299574-3';
        $this->code = $request->code;
        $this->url = 'https://axam.managermas.cl/';
        $this->setInnerUrl($this->url);
        $this->getProductos($this->url);
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
            $url = 'https://axam.managermas.cl/';
            $res= $this->client->request('POST',$this->url.'api/auth/',[
                'headers'=>[
                    'Content-Type' => 'application/json',
                ],
                'json'=>[
                    'username'=> $this->username,
                    'password'=> $this->password
                ]
            ]);
            if($res->getStatusCode() == 200){
                $body = json_decode($res->getBody());
                $authToken = $body->auth_token;
                //dd($token);
                $this->setIdSesson($authToken);
                
            }
            
        }
        catch (Exception $e)
        {
            dd($e);
        }
    }


    /**
     * @param Url $url
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getProductos()
    {
        $this->login();
        //dd($a);
        $res= $this->client->request('GET',$this->url.'api/pricelist/'.$this->rut.'/?dets=1',[
            'headers'=>[
                'Authorization' => "token ".$this->idSesson,
                'Content-Type' => 'application/json',
                ]
            ]);
            if($res->getStatusCode() == 200){
                $body = json_decode($res->getBody());
               // dd($body->data);
              return  $this->fillResponse($body);    
           
            
        }
      
    }
  
    public function fillResponse($res){
           //dd($res->data);
      $products = $res->data;
      $response=[];
      $lista=[];
      foreach ($products as $key => $prod) {
          if($prod->id==18){
              
              $lista=$prod;
            }
        }
        //dd($lista->products);
        
        foreach ($lista->products as $key => $products){
          
        
        $codigo = $products->cod;
        if($products->cod!="KAR-1.198-282.0" && $products->cod !="KAR-1.673-003.0"){

            $stock = $this->getStock($products->cod);
        }
         
        
        $response[$key]['id'] = $products->cod;
        $response[$key]['code'] = $products->cod;
        $response[$key]['name'] = $products->name;
        $response[$key]['price'] = $products->price;
        $response[$key]['description'] = $products->descrip;
        $response[$key]['state'] = 1;
        $response[$key]['stock'] = $stock;
  
        $this->counter++;
        }
         //dd(json_encode($response));
        return json_encode($response);
    }
  
    public function getListaPorCliente($products){
      
          $buscarLista=[];   
        foreach($products as $product){
            if($product->id==18){
                //dd($product);
                $buscarLista = $product;
            }             
      }
    
      return $buscarLista;
    }
  
    public function getStock($codigo){
      
        //$this->login();
        $res= $this->client->request('GET',$this->url.'api/stock/'.$this->rut.'/'.$codigo.'/?dets=1&resv=0',[
            'headers'=>[
                'Authorization' => "token ".$this->idSesson,
                'Content-Type' => 'application/json',
                ]
            ]);
            if($res->getStatusCode() == 200){
                $response = json_decode($res->getBody());
                //dd($response['data']);
                
 
    
        $sumaStock = 0;
        $codigoBodega = 1;
        //dd($response->data[0]->stock[0]->bodega_stock);    
     
        if(empty($response->data)){
          return null;
        }else{
          foreach($response->data[0]->stock as $stock){
            //dd($value['']['nombre_bodega_stock']);
            if($stock->bodega_stock == "B1"){
            
            $sumaStock+=$stock->saldo;  
            $codigoBodega = "1";//$this->codigoBodega($stock->bodega_stock);  
              
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