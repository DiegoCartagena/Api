<?php

namespace App\Repositories\ManagerPlus;
use App\Http\Packages\ManagerPlus\RequestAllDocuments;
use App\Http\Packages\ManagerPlus\RequestAllOffices;
use App\Http\Packages\ManagerPlus\RequestAllProducts;
use App\Http\Packages\ManagerPlus\RequestProductsBySku;
use App\Http\Packages\ManagerPlus\RequestPriceList;
use App\Http\Packages\ManagerPlus\RequestSaveSale;
use App\Http\Packages\ProductInfo;
use Illuminate\Http\Request;
use Illuminate\Http\Request as RequestParams;
use App\Http\Packages\Url;


class ManagerPlusRepository implements IRepositoryManagerPlus
{
    private $codigoProducto;
    private $appurl;

    function __construct(Request $request) {
     
        $this->username   = $request->header('username');
        $this->password   = $request->header('password');
        $this->codigoProducto   = $request->header('codigoProducto');
        $this->appurl   = $request->header('appurl');
    }


    public function getAll(Request $requestParams)
    {

        $url = new Url();
        $url->setBaseUrl($this->appurl);
        $url->setParams($requestParams->all());
        $fullUrl = $url->make();
        $request = new RequestAllProducts($requestParams);
        $product_info = new ProductInfo($fullUrl, $request);

        return $product_info->getAll()->getResponse();

    }
   public function getBySku($sku, Request $request)
    {
        $url = new Url();
        $url->setBaseUrl($this->appurl);
        $url->setParams($request->all());
        //$url->setShape('products');
      
        $fullUrl = $url->make();

        $requestType = new RequestProductsBySku($request, $sku);
      
        $product_info = new ProductInfo($fullUrl, $requestType);

        return $product_info->getBySku()->getResponse();
    }

    public function getAllPricesLists(Request $requestParams)
    {
        $url = new Url();
        $url->setBaseUrl($this->appurl);
        $url->setParams($requestParams->all());

        $fullUrl = $url->make();

        $request = new RequestPriceList($requestParams);
        $product_info = new ProductInfo($fullUrl, $request);

        return $product_info->getAll()->getResponse();
    }

    public function getAllOffices(Request $requestParams)
    {
        $url = new Url();
        $url->setBaseUrl($this->appurl);
        $url->setParams($requestParams->all());
     

        $fullUrl = $url->make();

        $request = new RequestAllOffices($requestParams);
        $product_info = new ProductInfo($fullUrl, $request);

        return $product_info->getAllOffices()->getResponse();

    }
  
    public function getAllDocuments(Request $requestParams)
      {
          $url = new Url();
          $url->setBaseUrl($this->appurl);
          $url->setParams($requestParams->all());


          $fullUrl = $url->make();

          $request = new RequestAllDocuments($requestParams);
          $product_info = new ProductInfo($fullUrl, $request);

          return $product_info->getAllDocuments()->getResponse();

    }


    public function saveSale(Request $requestParams)
    {
        $url = new Url();
        $url->setBaseUrl($this->appurl);
        $url->setParams($requestParams->all());

        $fullUrl = $url->make();

        $request = new RequestSaveSale($requestParams);
      
        $product_info = new ProductInfo($fullUrl, $request);
      
//print_r($product_info->saveSale($requestParams));
      
        //return $product_info->saveSale($requestParams)->getResponse();
     
return $product_info->saveSale($requestParams)->getResponseString();
    }


}