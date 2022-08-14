<?php

namespace App\Http\Controllers;

use Shopify\Rest\Admin2022_07\Product;
use Illuminate\Http\Request as RequestParams;
use GuzzleHttp\Client;
use App\Http\Controllers\ProductosController;
use Shopify\ApiVersion;
use GuzzleHttp\Psr7\Request;
use stdClass;

class WebhookShopify extends Controller{
  public $session;
  public $request;
    public $token;
    public $client;
    public $venta;
    //public $response=[];
    public $manager;
    public $url;
    
    function __construct() {
        $this->url='https://multitienda-en-linea.myshopify.com/';
        $this->client = new Client(['verify' => false]);
        $this->token =env('SHOPIFY_APP_ADMIN_ACCESS_TOKEN');
        $this->manager=new ProductosController();
        //$this->request=RequestParams::create('/sale','POST',array());
        //$this->sale();
        //$this->update();
    }

    public function sale(){    
            $res= $this->client->request('GET',$this->url.'admin/api/2022-07/orders.json',[
                'headers'=>[
                    'Content-Type' => 'application/json',
                    'X-Shopify-Access-Token'=>$this->token
                ],
            ]);
            $ventasShopi = json_decode($res->getBody()->getContents());
            //dd($ventasShopi);
            $hoy=date('Y-m-d:H-m-s');
            $direccionCliente="";
            $ciudad="";
            $comuna="";
            $rut="";
            $razonSocial="";
            $giro="";
            $tipoDocumento="";
            $nv=[];
            //$this->request->setJson($nv);
            foreach($ventasShopi->orders as $key=> $orden){
                $nv['fechaEmision']=date('Y-m-d');//$fechaActual[0];
                //detalle venta
                $nv['detalle']['idProducto']="LTZ-MASKCU";//$orden->line_items[0]->sku;
                $nv['detalle']['cantidad'] = $orden->line_items[0]->quantity;
                $nv['detalle']['precio'] = $orden->line_items[0]->price; 
                $nv['detalle']['neto'] = $orden->line_items[0]->price / 1.19;
                //dd(json_encode($nv));
                
                //dd($orden);
                foreach ($orden->note_attributes as $key => $atributo) {
                    if($atributo->name=="Boleta/Factura"){
                        $tipoDocumento= $atributo->value;
                    }
                    if ($atributo->name=="Rut") {
                        $rut=$atributo->value;
                    }
                    if ($atributo->name=="Razón social") {
                        $razonSocial=$atributo->value;
                    }
                    if ($atributo->name=="Giro") {
                        $giro=$atributo->value;
                    }
                    if ($atributo->name=="Comuna") {
                        $comuna=$atributo->value;
                    }
                    if ($atributo->name=="Ciudad") {
                        $ciudad=$atributo->value;
                    }
                    if ($atributo->name=="Direccion de facturacion") {
                        $direccionCliente=$atributo->value;
                    }
                    //dd([$rut,$tipoDocumento,$razonSocial,$giro,$comuna,$ciudad,$direccionCliente]);
                }
                $fechasHora=explode('T',$orden->created_at);
                $fecha=explode('-',$fechasHora[0]);
                $hora =explode('-',$fechasHora[1]);
                $fechaHoraActual=explode(':',date('Y-m-d:H-m-s'));
                $fechaActual= explode('-',$fechaHoraActual[0]);
                $horaActual= explode('-',$fechaHoraActual[1]);
                
                $nv['tipoDocumento']=1;
                //cliente
                $nv['cliente']['rut']=$rut;
                $nv['cliente']['nombre']=$orden->customer->first_name;
                $nv['cliente']['apellido']=$orden->customer->last_name;
                $nv['cliente']['telefono']=$orden->customer->phone;
                $nv['cliente']['email']=$orden->customer->email;
                $nv['cliente']['razonSocial']=$razonSocial;
                $nv['cliente']['giro']=$giro;
                //direccion cliente 
                $nv['cliente']['direccion']['direccion']=$direccionCliente;
                $nv['cliente']['direccion']['numero']="";
                $nv['cliente']['direccion']['depto']="";
                $nv['cliente']['direccion']['region']="RM";    
                $nv['cliente']['direccion']['municipalidad']=$comuna;
                $nv['cliente']['direccion']['ciudad']=$ciudad;
                //direccion despacho
                $nv['direccionDespacho']['direccion']="";
                $nv['direccionDespacho']['numero']="";
                $nv['direccionDespacho']['depto']="";
                $nv['direccionDespacho']['region']="";
                $nv['direccionDespacho']['municipalidad']="";
                $nv['direccionDespacho']['ciudad']="";
                $nv['despachoTotal']=0;
                $nv['despachoNeto']=0;
                $nv['total']=1;//$orden->current_subtotal_price;
                $nv['declareSii']=1;
                $nv['totalNeto']= $nv['total']/1.19;
                $nv['totalIva']= $nv['total']*0.19;
                $nv['oc']=$orden->id;
                //dd( json_encode($nv));
                $response=$this->client->request("POST","http://api.test/sale",[
                    'headers'=>[
                            "Content-Type"=> "application/json"
                    ],'body'=>json_encode($nv)
                ]);
                return $response->getBody()->getContents();//$sendmanager->getBody()->getContents();
            }
            //$sendmanager= new VentasController();
            //$sendmanager->setSale($this->request);
            //dd( [$sendmanager->getBody()->getContents()]);
            
    }

    public function update(){
        $resp= $this->client->request('GET',$this->url.'admin/api/2022-07/products.json?limit=250;rel=next',[
            'headers'=>[
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token'=>$this->token
            ]
        ]);
        
        $productos = json_decode($resp->getBody()->getContents());
        
        $productosManager= json_decode($this->manager->getProductos());
        //$res=$this->client;
        foreach ($productosManager as $j => $product) {
            foreach ($productos as $key => $producto) {
                foreach ($producto as  $prod) {
                     //dd($producto[$i]);
                     for ($i=0; $i <=count($producto) ; $i++) { 
                     $stock=$product->stock==0 ? $product->stock : $product->stock[0]->quantity;
                        if($product->id==$productos[$i]->variants[0]->sku){
                        $body ='{
                            "product":{
                                "variants":[{
                                    "price":'."$product->price".',
                                    "sku":"'.$product->id.'",
                                    "inventory_quantity":'.$stock.'
                                   }]
                               }
                           }';
                           $res= $this->client->request('PUT',$this->url."admin/api/".ApiVersion::LATEST."/products/".$prod->id.".json",[
                               'headers'=>[
                                   'Content-Type' => 'application/json',
                                   'X-Shopify-Access-Token'=>$this->token
                               ],"body"=>$body
                           ]);
                           
                           return $res->getBody()->getContents();
                        }else{
                            $i++;
                        }
                    }
                    /*$res= $this->client->request('PUT',$this->url."admin/api/".ApiVersion::LATEST."/products/".$prod->id.".json",[
                        'headers'=>[
                            'Content-Type' => 'application/json',
                            'X-Shopify-Access-Token'=>$this->token
                        ],"body"=>$body
                    ]);*/
                   }
                }
            }
            
        }
}   
