<?php

namespace Weisskpub\Ethereum;

use GuzzleHttp\Client as GuzzleHttp;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /**
     * Http Client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client = null;

    /**
     * JSON-RPC Id.
     *
     * @var int
     */
    protected $rpcId = 0;

    /**
     * JSON-RPC version.
     *
     * @var int
     */
    protected $rpcVersion = '2.0';

    /**
     * Wei divisor.
     *
     * @var int
     */
    protected $weiDivisor = '1000000000000000000';

    /**
     * Constructs new client.
     *
     * @param mixed $config
     *
     * @return void
     */
    public function __construct($config = [])
    {
        // init defaults
        $config = $this->defaultConfig($this->parseUrl($config));

        $handlerStack = HandlerStack::create();
        $handlerStack->push(
            Middleware::mapResponse(function (ResponseInterface $response) {
                return EthereumdResponse::createFrom($response);
            }),
            'json_response'
        );

        // construct client
        $this->client = new GuzzleHttp([
            'base_uri'    => "${config['scheme']}://${config['host']}:${config['port']}",
            'handler'     => $handlerStack,
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    /**
     * Gets http client config.
     *
     * @param string|null $option
     *
     * @return mixed
     */
    public function getConfig($option = null)
    {
        return (
            isset($this->client) &&
            $this->client instanceof ClientInterface
        ) ? $this->client->getConfig($option) : false;
    }

    /**
     * Gets http client.
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Sets http client.
     *
     * @param  \GuzzleHttp\ClientInterface
     *
     * @return void
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Makes request to Ethereum Core.
     *
     * @param string $method
     * @param mixed  $params
     *
     * @return array
     */
    public function request($method, $params = [])
    {
        try {
            $json = [
                'method'  => $method,
                'params'  => (array) $params,
                'id'      => ++$this->rpcId,
                'jsonrpc' => $this->rpcVersion,
            ];

            $response = $this->client->request('POST', '/', ['json' => $json]);

            if ($response->hasError()) {
                // throw exception on error
                throw new Exceptions\EthereumdException($response->error());
            }

            return $response;
        } catch (RequestException $exception) {
            if (
                $exception->hasResponse() &&
                $exception->getResponse()->hasError()
            ) {
                throw new Exceptions\EthereumdException($exception->getResponse()->error());
            }

            throw new Exceptions\ClientException(
                $exception->getMessage(),
                $exception->getCode()
            );
        } catch (Exceptions\EthereumdException $exception) {
            throw $exception;
        }
    }

    /**
     * Makes async request to Ethereum Core.
     *
     * @param string        $method
     * @param mixed         $params
     * @param callable|null $onFullfiled
     * @param callable|null $onRejected
     *
     * @return \GuzzleHttp\Promise\Promise
     */
    public function requestAsync(
        $method,
        $params = [],
        callable $onFullfiled = null,
        callable $onRejected = null)
    {
        $json = [
            'method' => $method,
            'params' => (array) $params,
            'id'     => $this->rpcId++,
        ];

        $promise = $this->client
            ->requestAsync('POST', '/', ['json' => $json]);

        $promise->then(
            function (ResponseInterface $response) use ($onFullfiled) {
                $error = null;
                if ($response->hasError()) {
                    $error = new Exceptions\EthereumdException($response->error());
                }

                if (is_callable($onFullfiled)) {
                    $onFullfiled($error ?: $response);
                }
            },
            function (RequestException $exception) use ($onRejected) {
                if (
                    $exception->hasResponse() &&
                    $exception->getResponse()->hasError()
                ) {
                    $exception = new Exceptions\EthereumdException(
                        $exception->getResponse()->error()
                    );
                }

                if ($exception instanceof RequestException) {
                    $exception = new Exceptions\ClientException(
                        $exception->getMessage(),
                        $exception->getCode()
                    );
                }

                if (is_callable($onRejected)) {
                    $onRejected($exception);
                }
            }
        );

        return $promise;
    }

    /**
     * Makes request to Ethereum Core.
     *
     * @param string $method
     * @param array  $params
     *
     * @return array
     */
    public function __call($method, array $params = [])
    {
        $method = str_ireplace('async', '', $method, $count);
        if ($count > 0) {
            return $this->requestAsync($method, ...$params);
        }

        return $this->request($method, $params);
    }

    /**
     * Set default config values.
     *
     * @param array $config
     *
     * @return array
     */
    protected function defaultConfig(array $config = [])
    {
        $defaults = [
            'scheme' => 'http',
            'host'   => '127.0.0.1',
            'port'   => 8545,
        ];

        return array_merge($defaults, $config);
    }

    /**
     * Expand URL config into components.
     *
     * @param mixed $config
     *
     * @return array
     */
    protected function parseUrl($config)
    {
        if (is_string($config)) {
            $allowed = ['scheme', 'host', 'port'];

            $parts = (array) parse_url($config);
            $parts = array_intersect_key($parts, array_flip($allowed));

            if (!$parts || empty($parts)) {
                throw new Exceptions\ClientException('Invalid url');
            }

            return $parts;
        }

        return $config;
    }

    /**
     * Convert wei value to ethereum.
     *
     * @param mixed $wei
     *
     * @return float
     */
    public function weiToEth($wei) : float
    {
        return (float)bcdiv($this->decode_hex($wei), $this->weiDivisor,strlen($this->weiDivisor) - 1);
    }


    /**
     * Convert ethereum value to wei.
     *
     * @param mixed $wei
     *
     * @return string
     */
    public function ethToWei($eth) : string
    {
        return $this->encode_hex($eth * $this->weiDivisor);
    }

    /**
     * Decode hex value.
     *
     * @param mixed $input
     *
     * @return mixed
     */
    public function decode_hex($input)
    {
        if (substr($input, 0, 2) === '0x') {
            $input = substr($input, 2);
        }
        if (preg_match('/[a-f]+/', $input)) {
            return hexdec($input);
        }
        return $input;
    }

    /**
     * Encode value to hex.
     *
     * @param mixed $input
     *
     * @return string
     */
    public function encode_hex($input) : string
    {
        return '0x' . dechex($input);
    }
}
