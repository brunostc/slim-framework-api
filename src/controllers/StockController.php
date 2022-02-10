<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\JwtHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Capsule\Manager as Eloquent;
use Illuminate\Validation\Factory as Validator;
use GuzzleHttp\Client;
use Swift_Mailer;
use Swift_Message;

class StockController
{
    protected Eloquent $eloquent;
    protected Client $stooq;
    protected JwtHelper $jwt;
    protected Swift_Mailer $mailer;
    protected Validator $validator;

    public function __construct(
        Eloquent $eloquent,
        JwtHelper $jwt,
        Client $stooq,
        Swift_Mailer $mailer,
        Validator $validator
    ) {
        $this->stooq = $stooq;
        $this->eloquent = $eloquent;
        $this->jwt = $jwt;
        $this->mailer = $mailer;
        $this->validator = $validator;
    }

    public function getStock(Request $request, Response $response, array $args): Response
    {
        $body = $request->getQueryParams();

        try {
            $this->validator->validate($body, [
                'stock' => 'required|string',
            ]);
        } catch (\Exception $err) {
            $response->getBody()->write(json_encode([
                'error' => 'Invalid input',
                'message' => $err->getMessage()
            ]));

            return $response->withStatus(400);
        }

        $stooqResponse = $this->stooq->request('GET', "?e=json&s={$body['stock']}.us");

        $log = $stooqResponse->getBody()->getContents();

        $this->eloquent->table('user_logs')->insert([
            'user_id' => $this->jwt->getUserId($request),
            'json' => $log
        ]);

        $response->getBody()->write($log);

        $userEmail = $this->jwt->getUserEmail($request);

        $message = (new Swift_Message('New Stock Request'))
            ->setFrom(['brunostacheski@jobsity.phpchallenge.com' => 'Bruno Stacheski'])
            ->setTo($userEmail)
            ->setBody(json_encode($log));

        $this->mailer->send($message);

        return $response;
    }

    public function getStockHistory(Request $request, Response $response, array $args): Response
    {
        $userId = $this->jwt->getUserId($request);

        $history = $this->eloquent->table('user_logs')->where('user_id', $userId)->get()->each(function ($item) {
            return $item->json = json_decode($item->json, true);
        });

        $response->getBody()->write(json_encode([
            'data' => $history
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
