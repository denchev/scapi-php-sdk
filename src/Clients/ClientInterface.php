<?php

use Psr\Http\Message\RequestInterface;


interface ClientInterface
{
    public function sendRequest(\Psr\Http\Message\RequestInterface $request);
}