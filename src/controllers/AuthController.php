<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\JwtHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Validation\Factory as Validator;

class AuthController
{
    protected Manager $eloquent;
    protected Validator $validator;
    protected JwtHelper $jwt;

    public function __construct(
        JwtHelper $jwt,
        Manager $eloquent,
        Validator $validator
    ) {
        $this->eloquent = $eloquent;
        $this->validator = $validator;
        $this->jwt = $jwt;
    }

    public function login(Request $request, Response $response, array $args): Response
    {
        $body = $request->getParsedBody();
        $response = $response->withHeader('Content-type', 'application/json');

        try {
            $this->validator->validate($request->getParsedBody(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);
        } catch (\Exception $err) {
            $response->getBody()->write(json_encode([
                'error' => 'Invalid input',
                'message' => $err->getMessage()
            ]));

            return $response->withStatus(400);
        }

        $user = $this->eloquent->table('users')->where('email', $body['email'])->first();

        if (empty($user) || !password_verify($body['password'], $user->password)) 
        {
            $response->getBody()->write(json_encode([
                'error' => 'Unauthorized',
                'message' => 'Invalid credentials'
            ]));

            return $response->withStatus(401);
        }

        unset($user->password);

        $token = $this->jwt->encode($user);

        $user->token = $token;

        $response->getBody()->write(json_encode(['data' => $user]));

        return $response->withStatus(200);
    }

    public function register(Request $request, Response $response, array $args): Response
    {
        $body = $request->getParsedBody();
        $response = $response->withHeader('Content-type', 'application/json');

        try {
            $this->validator->validate($request->getParsedBody(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);
        } catch (\Exception $err) {
            $response->getBody()->write(json_encode([
                'error' => 'Invalid input.',
                'message' => $err->getMessage()
            ]));

            return $response->withStatus(400);
        }

        if ($this->eloquent->table('users')->where('email', $body['email'])->exists()) 
        {
            $response
                ->getBody()
                ->write(json_encode([
                    'error' => 'Invalid resource.',
                    'message' => 'The email is already taken. Please, try again.'
                ]));

            return $response->withStatus(400);
        }

        $success = $this->eloquent->table('users')->insert([
            'email' => $body['email'],
            'password' => password_hash($body['password'], PASSWORD_BCRYPT),
        ]);

        if (! $success) {
            $response
                ->getBody()
                ->write(
                    json_encode([
                        'error' => 'Database error.',
                        'message' => 'Something went wrong while trying to save the record. Please, contact the support.'
                        ])
                );

            return $response->withStatus(500);
        }

        $user = $this->eloquent->table('users')->where('email', $body['email'])->first();
        
        unset($user->password);

        $response->getBody()->write(json_encode($user));

        return $response->withStatus(200);
    }
}
