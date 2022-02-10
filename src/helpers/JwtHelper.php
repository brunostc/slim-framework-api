<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ServerRequestInterface;

class JwtHelper {

    private $key = 'test';

    public function encode($payload)
    {
        $jwt = JWT::encode($payload, $this->key, 'HS256');

        return $jwt;
    }

    public function decode($jwt)
    {
        $decoded = JWT::decode($jwt, new Key($this->key, 'HS256'));

        return $decoded;
    }

    public function getUserId(ServerRequestInterface $request)
    {
        $auth = $request->getHeader('Authorization');

        $token = explode('Bearer ', $auth[0]);

        $jwtHelper = new JwtHelper();

        return $jwtHelper->decode($token[1])->id;
    }

    public function getUserEmail(ServerRequestInterface $request)
    {
        $auth = $request->getHeader('Authorization');

        $token = explode('Bearer ', $auth[0]);

        $jwtHelper = new JwtHelper();

        return $jwtHelper->decode($token[1])->email;
    }
}