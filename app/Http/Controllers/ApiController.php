<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Repositories\ManagerPlus\ManagerPlusRepository;

class Apicontroller extends Controller
{

    function __construct(Request $request)
    {
        
    }

    public function getAllProducts(Request $request){
        $sku= $request->code;
        if ($sku){
            return $this->getBySku($request, $sku);
        }

        switch ($this->erp_type) {
            case 'managerplus':
                $repository = new ManagerPlusRepository($request);
                $response = $repository->getAll($request);
                break;
            
            default:
                # code...
                break;
        }
    }
}