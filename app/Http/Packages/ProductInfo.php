<?php

namespace App\Http\Packages;

use Illuminate\Http\Request as RequestParams;

class ProductInfo
{
    protected $requestType;
    protected $fullUrl;

    /**
     * ProductInfo constructor.
     */
    public function __construct(Url $fullUrl, Request $request, RequestParams $requestParams = null)
    {
        $this->fullUrl = $fullUrl;
        $this->requestType = $request;
        $this->requestParams = $requestParams;

        $this->args = func_get_args();
        $this->argsCount = count($this->args);
    }

    public function getAll()
    {
        if ($this->argsCount  >= 1) {
            return $this->requestType->make($this->fullUrl, $this->requestParams);
        }
        return $this->requestType->make($this->fullUrl);
    }

    public function getBySku()
    {
        return $this->requestType->make($this->fullUrl);
    }

    public function getAllPricesList()
    {
        if ($this->argsCount  >= 1) {
            return $this->requestType->make($this->fullUrl, $this->requestParams);
        }
        return $this->requestType->make($this->fullUrl);
    }

    public function getAllOffices()
    {
        if ($this->argsCount  >= 1) {
            return $this->requestType->make($this->fullUrl, $this->requestParams);
        }
        return $this->requestType->make($this->fullUrl);
    }
  
     public function getAllDocuments()
      {
          if ($this->argsCount  >= 1) {
              return $this->requestType->make($this->fullUrl, $this->requestParams);
          }
          return $this->requestType->make($this->fullUrl);
    }
  
     public function getAllClients()
    {
        if ($this->argsCount  >= 1) {
            return $this->requestType->make($this->fullUrl, $this->requestParams);
        }
        return $this->requestType->make($this->fullUrl);
    }

    public function saveSale($requestParams)
    {
        return $this->requestType->make($this->fullUrl, $requestParams);
    }
  
    public function auth($requestParams)
    {
        return $this->requestType->make($this->fullUrl, $requestParams);
    }
  
    public function saveProducts($requestParams)
    {
        return $this->requestType->make($this->fullUrl, $requestParams);
    }
  
    public function saveOffices($requestParams)
    {
        return $this->requestType->make($this->fullUrl, $requestParams);
    }


    public function getVariantProduct($requestParams)
    {
        return $this->requestType->make($this->fullUrl, $requestParams);
    }
   public function getVariant($requestParams)
    {
        return $this->requestType->make($this->fullUrl, $requestParams);
    }
  public function getStock($requestParams)
    {
        return $this->requestType->make($this->fullUrl, $requestParams);
    }
  public function getProductosCantidad($requestParams)
    {
        return $this->requestType->make($this->fullUrl, $requestParams);
    }
    public function getProductosTipo($requestParams)
    {
        return $this->requestType->make($this->fullUrl, $requestParams);
    }
   public function getListaPrecioDetalle()
    {
        return $this->requestType->make($this->fullUrl);
    }
    
    public function getDocument()
    {
        return $this->requestType->make($this->fullUrl);
    }
  public function getDocumentId()
    {
        return $this->requestType->make($this->fullUrl);
    }

}
