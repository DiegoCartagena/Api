<?php

namespace App\Http\Packages;

use App\Http\Packages\Url;
use App\Http\Packages\RandomRepository;
use App\Http\Packages\Request as RequestClass;
use Exception;
use Illuminate\Http\Request as RequestParams;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
abstract class Request
{
    private $innerResponse;
    public $response;
    private $handshakeUser;
    private $handshakePassword;
    private $offset;
    private $limit;
    private $countRequests;
    private $modalidadRandom;
    private $empresaRandom;
  
  


    /**
     * @param $productId
     * @param Url $url
     * @param $client
     * @return array
     */
    public function getStockInfo($productId, Url $url, $client, $handshakeUser, $handshakePassword)
    {
        $url->setShape('api/latest/item_stock_units?item__sku=' . $productId);
        $url->make();
        try {
            $result = $client->request(
                'GET',
                $url->getUrl(),
                ['auth' => [$handshakeUser, $handshakePassword]]
            );
        } catch (Exception $e) {
            $error = 'Caught exception: ' . $e->getMessage() . "\n";
            throw new Exception($error);
        }

        $stockInfo = json_decode($result->getBody())->objects ?? null;

        $response = [];
        foreach ($stockInfo as $key => $info) {
            $whereHouseInfo = $this->getWhereHouseInfo($url, $info, $client, $handshakeUser, $handshakePassword);

            $response[] = [
                'position' => $key,
                'shelfQty' => $info->shelfQty,
                'isAvailable' => $info->isAvailable,
                'officeId' => $whereHouseInfo->objID,
                'wherehouseName' => $whereHouseInfo->name,
            ];
        }
        return $response;
    }

    /**
     * @param Url $url
     * @param $stockInfo
     * @param $client
     * @return mixed
     */
    public function getWhereHouseInfo(Url $url, $stockInfo, $client, $handshakeUser, $handshakePassword)
    {

        $url->setShape(ltrim($stockInfo->warehouse, '/'));
        $url->make();

        try {
            $result = $client->request(
                'GET',
                $url->getUrl(),
                ['auth' => [$handshakeUser, $handshakePassword]]
            );
        } catch (Exception $e) {
            $error = 'Caught exception: ' . $e->getMessage() . "\n";
            throw new Exception($error);
        }

        return json_decode($result->getBody());
    }

    /**
     * @return mixed
     */
    public function getInnerResponse()
    {
        return $this->innerResponse;
    }

    /**
     * @param mixed $innerResponse
     */
    public function setInnerResponse($innerResponse): void
    {
        $this->innerResponse = $innerResponse;
    }

    /**
     * @return mixed
     */
    public function getCountRequestsRemain()
    {
        return $this->countRequestsRemain;
    }

    /**
     * @param mixed $countRequestsRemain
     */
    public function setCountRequestsRemain($countRequestsRemain): void
    {
        $this->countRequestsRemain = $countRequestsRemain;
    }

    /**
     * @return mixed
     */
    public function getCountRequests()
    {
        return $this->countRequests;
    }

    /**
     * @param mixed $countRequests
     */
    public function setCountRequests($countRequests): void
    {
        $this->countRequests = $countRequests;
    }

    private $countRequestsRemain;

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param array $response
     */
    public function setResponse(array $response): void
    {
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getHandshakeUser()
    {
        return $this->handshakeUser;
    }

    /**
     * @param mixed $handshakeUser
     */
    public function setHandshakeUser($handshakeUser): void
    {
        $this->handshakeUser = $handshakeUser;
    }

    /**
     * @return mixed
     */
    public function getHandshakePassword()
    {
        return $this->handshakePassword;
    }

    /**
     * @param mixed $handshakePassword
     */
    public function setHandshakePassword($handshakePassword): void
    {
        $this->handshakePassword = $handshakePassword;
    }
    /**
     * @return mixed
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param mixed $offset
     */
    public function setOffset($offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param mixed $limit
     */
    public function setLimit($limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return mixed
     */
    public function getManagerToken()
    {
        return $this->ManagerToken;
    }

    /**
     * @param mixed $ManagerToken
     */
    public function setManagerToken($ManagerToken): void
    {
        $this->ManagerToken = $ManagerToken;
    }

    /**
     * @return mixed
     */
    public function getManagerRutEmpresa()
    {
        return $this->ManagerRutEmpresa;
    }

    /**
     * @param mixed $ManagerRutEmpresa
     */
    public function setManagerRutEmpresa($ManagerRutEmpresa): void
    {
        $this->ManagerRutEmpresa = $ManagerRutEmpresa;
    }

    /**
     * @return mixed
     */
    public function getModalidadRandom()
    {
        return $this->modalidadRandom;
    }

    /**
     * @param mixed $modalidadRandom
     */
    public function setModalidadRandom($modalidadRandom): void
    {
        $this->modalidadRandom = $modalidadRandom;
    }

    public function getEmpresaRandom()
    {
        return $this->empresaRandom;
    }

    public function setEmpresaRandom($empresaRandom): void
    {
        $this->empresaRandom = $empresaRandom;
    }

    


    /**
     * @param $productId
     * @param Url $url
     * @param $client
     * @return array
     */
     public function getStockInfoRamdom($urlUnidad)
    {
        //$clientUnidad = new \GuzzleHttp\Client();
         $token='WEB';
        $kolt = "01E";
        $urlUnidad->setShape('web32/precios/pidelistaprecio?modalidad='.$this->modalidadRandom.'&rut='.$this->empresaRandom.'&kolt='.$kolt);
        $urlUnidad->make();
  //dd($urlUnidad);      
        try {
        
            
           $result = new Client([
                'request'=>'GET',
                'base_uri' => $urlUnidad->getUrl(),
                "verify" => false,
                'headers'=> [
                  'token'=>$this->modalidadRandom,
                  'clientid'=>$this->empresaRandom
                  
                ]
            ]);
         //dd($result);
         //dd($result->request('GET',$urlUnidad->getUrl()));
          $res=$result->request('GET',$urlUnidad->getUrl());
           //dd($res);
        } catch (Exception $e) {
            $error = 'Caught exception: ' . $e->getMessage() . "\n";
            throw new Exception($error);
        }

        $stockInfos = json_decode($res->getBody(),true) ?? null;
        $response = [];
    // dd($stockInfos);
      
      if(isset($stockInfos['bodega'])){
        $bodega = $stockInfos['bodega'];
      }
      if(isset($stockInfos['datos'])){
        $nombreLista = $stockInfos['nombre'];
          
        foreach ($stockInfos['datos'] as $key => $info) { 
          //dd($info);
          foreach($info['unidades'] as $z =>$value){
            //dd($info);
              $listPrice['pricelistId']=$nombreLista;
              $listPrice['price']=$info['unidades'][0]['prunneto'][0]['f'];
          
     
          }
                array_push($response, [
                    'id'=>$info['kopr'],                   
                    'nombreUnid' => $value['nombre'],
                    'officeId' => $bodega,
                    'porciva' => $info['porciva'],
                    'listPrice' => $listPrice,
                    'price' => $info['unidades'][0]['prunneto'][0]['f'],
                    'quantity' => $value['stockfisico']
                ]);
           // }
        }
      }
      
         //dd($response);
        return $response;
}

    /**
     * @param $manufacter
     * @param Url $url
     * @param $client
     * @return array
     */
    public function getManufacturerInfo($manufacter, Url $url, $client, $handshakeUser, $handshakePassword)
    {
        $manufacter = substr($manufacter, 1);
        $url->setShape($manufacter);
        $url->make();

        try {
            $result = $client->request(
                'GET',
                $url->getUrl(),
                ['auth' => [$handshakeUser, $handshakePassword]]
            );
        } catch (Exception $e) {
            $error = 'Caught exception: ' . $e->getMessage() . "\n";
            throw new Exception($error);
        }

        $manufacterInfo = json_decode($result->getBody());

        $response = $manufacterInfo->name;

        return $response;
    }
}
