<?php

namespace App\Middlewares;

use App\Helpers\JwtHelper;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class UserHasValidJwtMiddleware 
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $auth = $request->getHeader('Authorization');

        $response = new Response();
        $response = $response->withHeader('Content-type', 'application/json');

        if (empty($auth)) {
            return $response->withStatus(401);
        }

        $token = explode('Bearer ', $auth[0]);

        if (!$token) {
            return $response->withStatus(401);
        }

        $jwtHelper = new JwtHelper();

        try {
            $jwtHelper->decode($token[1]);
        } catch (\Exception $e) {
            return $response->withStatus(401);
        }

        $response
            ->getBody()
            ->write((string) $handler->handle($request)->getBody());

        return $response;
    }
}
