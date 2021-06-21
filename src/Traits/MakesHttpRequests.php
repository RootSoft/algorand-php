<?php


namespace Rootsoft\Algorand\Traits;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Rootsoft\Algorand\Clients\AlgorandClient;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Exceptions\ApiKeyException;
use Rootsoft\Algorand\Exceptions\NotFoundException;
use Rootsoft\Algorand\Exceptions\OverspendException;
use Rootsoft\Algorand\Utils\AlgorandUtils;

trait MakesHttpRequests
{
    /**
     * Make a GET request to Algorand nodes and return the response.
     *
     * @param AlgorandClient $client
     * @param string $uri
     * @param array $params
     * @return mixed
     * @throws AlgorandException
     */
    protected function get(AlgorandClient $client, string $uri, array $params = [])
    {
        return $this->request($client, 'GET', $uri, $params);
    }

    /**
     * Make a POST request to Algorand nodes and return the response.
     *
     * @param AlgorandClient $client
     * @param string $uri
     * @param array $queryParams
     * @param array $payload
     * @param array $headers
     * @return mixed
     * @throws AlgorandException
     */
    protected function post(AlgorandClient $client, string $uri, array $queryParams = [], array $payload = [], array $headers = [])
    {
        return $this->request($client, 'POST', $uri, $queryParams, $payload, $headers);
    }

    /**
     * Make a PUT request to Algorand nodes and return the response.
     *
     * @param AlgorandClient $client
     * @param string $uri
     * @param array $payload
     * @return mixed
     * @throws AlgorandException
     */
    protected function put(AlgorandClient $client, string $uri, array $payload = [])
    {
        return $this->request($client, 'PUT', $uri, $payload);
    }

    /**
     * Make a DELETE request to Algorand nodes and return the response.
     *
     * @param AlgorandClient $client
     * @param string $uri
     * @param array $payload
     * @return mixed
     * @throws AlgorandException
     */
    protected function delete(AlgorandClient $client, string $uri, array $payload = [])
    {
        return $this->request($client, 'DELETE', $uri, $payload);
    }

    /**
     * Make request to Algorand nodes and return the response.
     *
     * @param AlgorandClient $client
     * @param string $verb
     * @param string $uri
     * @param array $queryParams
     * @param array $payload
     * @param array $headers
     * @return mixed
     * @throws AlgorandException
     */
    protected function request(AlgorandClient $client, string $verb, string $uri, array $queryParams = [], array $payload = [], array $headers = [])
    {
        // Strip leading slashes - RFC 3986
        $uri = ltrim($uri, '/');

        // Build the options
        $options = $this->buildOptions($verb, $queryParams, $payload, $headers);

        // Make the request
        try {
            $response = $client->client->request(
                $verb,
                $uri,
                $options,
            );
        } catch (GuzzleException $e) {
            throw new AlgorandException($e->getMessage());
        }

        if ($response->getStatusCode() != 200) {
            $this->handleRequestError($response);
        }

        $responseBody = (string) $response->getBody()->getContents();

        return json_decode($responseBody) ?: $responseBody;
    }

    /**
     * Handle the request error.
     *
     * @param ResponseInterface $response
     * @return void
     * @throws AlgorandException
     */
    protected function handleRequestError(ResponseInterface $response)
    {
        $errorMessage = $response->getBody()->getContents();
        if ($response->getStatusCode() == 401 || $response->getStatusCode() == 403) {
            throw new ApiKeyException((string) $response->getBody());
        }

        if ($response->getStatusCode() == 404) {
            throw new NotFoundException((string) $response->getBody());
        }

        if ($response->getStatusCode() == 400) {
            if ($this->isOverspend($errorMessage)) {
                throw new OverspendException((string) $response->getBody());
            }
        }

        throw new AlgorandException((string) $response->getBody());
    }

    /**
     * @param string $method
     *
     * @param array $queryParams
     * @param array $payload
     * @param array $headers
     * @return array
     */
    private function buildOptions($method = 'get', array $queryParams = [], array $payload = [], array $headers = [])
    {
        $options = [
            'query' => [],
        ];

        // Add the query params
        $options['query'] = array_merge($options['query'], $queryParams);

        if ($method == 'POST') {
            if (! array_key_exists('body', $payload)) {
                // Body is given
                $options = array_merge($payload, $options);
            } else {
                // Body is given
                $options = array_merge($payload, $options);
            }
        }

        $options['http_errors'] = false;

        if (! empty($headers)) {
            $options['headers'] = $headers;
        }

        return $options;
    }

    /**
     * Check if the given transaction was overspend.
     *
     * @param string $response
     * @return bool
     */
    private function isOverspend(string $response)
    {
        return AlgorandUtils::string_contains($response, 'overspend');
    }
}
