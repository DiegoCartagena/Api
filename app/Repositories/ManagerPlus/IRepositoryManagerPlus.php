<?php

namespace App\Repositories\ManagerPlus;
use Illuminate\Http\Request;


interface IRepositoryManagerPlus
{
    public function getAllDocuments(Request $request);
  
    public function getAllOffices(Request $request);

    public function getAll(Request $request);
  
    public function getBySku($sku, Request $request);

    public function getAllPricesLists(Request $request);

    public function saveSale(Request $request);

}