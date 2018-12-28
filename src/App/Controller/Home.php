<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class Home
{
    public function index(): Response
    {
        return new Response('<html><body>Hi there!</body></html>');
    }
}
