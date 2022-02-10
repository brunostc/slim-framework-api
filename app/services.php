<?php
declare(strict_types=1);

namespace App;

use App\Helpers\JwtHelper;
use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as Eloquent;
use Illuminate\Translation\FileLoader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use GuzzleHttp\Client;
use Swift_Mailer;
use Swift_SmtpTransport;

$databaseConfig = include 'config/database.php';

return function (ContainerBuilder $containerBuilder) use ($databaseConfig) {
    $containerBuilder->addDefinitions([

        Swift_Mailer::class => function() {
            $host = $_ENV['MAILER_HOST'] ?? 'smtp.mailtrap.io';
            $port = intval($_ENV['MAILER_PORT']) ?? 465;
            $username = $_ENV['MAILER_USERNAME'] ?? 'test';
            $password = $_ENV['MAILER_PASSWORD'] ?? 'test';

            $transport = (new Swift_SmtpTransport($host, $port))
                ->setUsername($username)
                ->setPassword($password);

            return new Swift_Mailer($transport);
        },

        Eloquent::class => function() use ($databaseConfig) {
            $capsule = new Eloquent();
            $capsule->addConnection($databaseConfig);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            return $capsule;
        },

        Factory::class => function () use ($databaseConfig) {
            $loader = new FileLoader(new Filesystem(), 'lang');
            $translator = new Translator($loader, 'en');
            $validation = new Factory($translator);

            return $validation;
        },

        Client::class => function () {
            $client = new Client(['base_uri' => 'https://stooq.com/q/l/']);

            return $client;
        },

        JwtHelper::class => new JwtHelper(),
    ]);
};
