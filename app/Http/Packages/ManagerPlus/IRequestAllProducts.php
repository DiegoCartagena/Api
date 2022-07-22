<?php

namespace App\Http\Packages\ManagerPlus;
use App\Http\Packages\Url;

interface IRequestAllProducts
{
    public function make(Url $url);
    public function tryRequest();
    public function login();

}