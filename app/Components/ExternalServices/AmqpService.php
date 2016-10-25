<?php

namespace App\Components\ExternalServices;

use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AmqpService
 * @package App\Components\ExternalServices
 */
class AmqpService
{
    protected $config;

    public function __construct()
    {
        $this->config = config('amqp');
    }

    /**
     * @param $exchange
     * @param $routingKey
     * @param $msgBody
     * @return bool
     * @throws \RuntimeException
     */
    public function sendMsg($exchange, $routingKey, $msgBody)
    {
        $queryData = http_build_query([
            'exchange' => $exchange,
            'routing_key' => $routingKey,
            'data' => $msgBody
        ]);

        $url = 'http://' . $this->config['http_host'] . ':' . $this->config['http_port'] . '/api/mqsend';

        $response = app('Guzzle')::request(
            'POST',
            $url,
            [
                RequestOptions::HEADERS => [
                    'Accept' => 'application/json'
                ],
                RequestOptions::FORM_PARAMS => $queryData
            ]
        );

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new \RuntimeException('Not ok response code');
        }

        $data = $response->getBody();
        if (!$data) {
            throw new \RuntimeException('Empty body response');
        }

        $decodedData = json_decode($data->getContents(), true);
        return (isset($decodedData['result']) && $decodedData['result'] === 'ok');
    }
}
