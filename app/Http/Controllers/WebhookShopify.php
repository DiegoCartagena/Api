<?php

namespace App\Http\Controllers;

use Shopify\Rest\Admin2022_07\Product;
use Shopify\Utils;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Shopify\ApiVersion;
use Shopify\Auth\Session;
use GuzzleHttp\Psr7\Request;
use Shopify\Clients\Rest;
use Shopify\Auth\OAuth;
use Shopify\Context;
use Shopify\Auth\FileSessionStorage;

class WebhookShopify extends Controller{
  public $session;
  public $request;
    public $token;
    public $client;
    //public $response=[];
    public $url;
    
    function __construct() {
        $this->url='https://multitienda-en-linea.myshopify.com/';
        $this->client = new Client(['verify' => false]);
        $this->token = "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";
        //$this->request=$request;
        $this->productos();
        //$this->update();
    }

    public function productos(){
        
        
        $session=new Session('1','https://multitienda-en-linea',true,ApiVersion::LATEST);
        //$load=$storage->loadSession('1');
        //$client=new Rest($this->url,$this->token);
        
        //dd($client);
            $context = Context::initialize(
            env('SHOPIFY_API_KEY'),
            env('SHOPIFY_API_SECRET'),
            [env('SHOPIFY_APP_SCOPES')],
            env('SHOPIFY_APP_HOST_NAME'),
            new FileSessionStorage('C:\laragon\www\Api\storage\sessions\tmp\shopify_api_sessions'),
            ApiVersion::LATEST,
            true,
            true,
            $this->token,
        );
        //dd($context);
        $res= $this->client->request('GET',$this->url.'admin/products.json',[
            'headers'=>[
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token'=>$this->token
            ],
        ]);
        $resp= $this->client->request('GET',$this->url.'admin/orders.json',[
            'headers'=>[
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token'=>$this->token
            ],
        ]);
        $orders=json_decode($resp->getBody()->getContents());
        foreach ($orders as $key => $orden) {
        //    dd($orden[0]->id);
        }
        $productosShopi=json_decode($res->getBody()->getContents());
        dd($productosShopi);
        /*$webhook=$this->client->request('GET',$this->url.'admin/api/2022-07/webhooks.json',[
            'headers'=>[
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token'=>$this->token
            ]
            
        ]);*/
        return $resp->getBody()->getContents();
    }
    public function update(){
        
         $session = $this->client->request('GET',$this->url,[
            'headers'=>[
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token'=>$this->token
            ]]); // Provided by the shopify.auth middleware, guaranteed to be active
            $cookie=['SHOPIFY_API_KEY'=>env('SHOPIFY_API_KEY'),
                'SHOPIFY_API_SECRET'=>env('SHOPIFY_API_SECRET'),
                'SHOPIFY_APP_SCOPES'=>env('SHOPIFY_APP_SCOPES'),
                'SHOPIFY_APP_HOST_NAME'=>env('SHOPIFY_APP_HOST_NAME')];
                $session = OAuth::callback(
                    $cookie,//cookie('laravel',$cookie,400,'app',$this->url,false,true),
                    $query=['laravel'=>'laravel'],
                    ['App\Lib\CookieHandler', 'saveShopifyCookie'],true
                );
                dd($session);
    $client = new Rest($session->getShop(), $session->getAccessToken());
    $result = $client->get('products', [], ['limit' => 5]);
        
    }
}   
