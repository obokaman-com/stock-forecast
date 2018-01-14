<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class Webhook
{
    public function bot(): JsonResponse
    {
        return new JsonResponse(['ok']);
    }
}
