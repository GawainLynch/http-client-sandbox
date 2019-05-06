<?php

namespace App;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Sandbox
{
    /** @var LoggerInterface */
    private $logger;
    private $defaults = [
        'http://localhost:8000/index.html',
        'http://localhost:8000/assets/app.css',
        'http://localhost:8000/assets/runtime.js',
        'http://localhost:8000/assets/app.js',
        'http://localhost:8000/assets/fonts/font-a-600.woff2',
        'http://localhost:8000/assets/fonts/font-a-700.woff2',
        'http://localhost:8000/assets/fonts/font-b-600.woff2',
        'http://localhost:8000/assets/fonts/font-b-700.woff2',
    ];

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function createClient(array $defaultOptions = []): HttpClientInterface
    {
        $defaultOptions = $defaultOptions + ['http_version' => '2.0'];
        $client = new CurlHttpClient($defaultOptions);
        $client->setLogger($this->logger);

        return $client;
    }

    public function run(HttpClientInterface $client, array $uris = null): array
    {
        $uris = $uris ?: $this->defaults;
        $result = ['pass' => [], 'fail' => []];
        foreach ($uris as $uri) {
            $response = $client->request('GET', $uri);
            try {
                $result['pass'][$uri] = [
                    'content' => $response->getContent(),
                    'status' => $response->getStatusCode()
                ];
            } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
                $result['fail'][$uri] = $e;
            }
        }

        return $result;
    }
}
