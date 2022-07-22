<?php

use Shopify;
use Symfony\Component\HttpFoundation\Session\Session;
use Shopify\Clients\Rest;

$session = Shopify\Utils::loadCurrentSession(
    $headers,
    $cookies,
    $isOnline
);
$client = new Rest(
    $session->getShop(),
    $session->getAccessToken()
);
$response = $client->get('shop');