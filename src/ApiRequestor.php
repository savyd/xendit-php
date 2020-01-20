<?php

namespace Xendit;

use Xendit\Exceptions\AuthenticationException;

/**
 * Class ApiRequestor
 *
 * @package Xendit
 */
class ApiRequestor
{
    private static $_httpClient;

    /**
     * Send request and processing response
     *
     * @param string $method  request method (get, post, patch, etc)
     * @param string $url     base url
     * @param array  $params  user's params
     * @param array  $headers user's additional headers
     *
     * @return array
     * @throws AuthenticationException
     */
    public function request($method, $url, $params = [], $headers = [])
    {
        list($rbody, $rcode, $rheaders)
            = $this->_requestRaw($method, $url, $params, $headers);

        return json_decode($rbody, true);
    }

    /**
     * Set must-have headers
     *
     * @param array $headers user's headers
     *
     * @return array
     */
    private function _setDefaultHeaders($headers)
    {
        $defaultHeaders = [];
        $lib = 'php';
        $libVersion = Xendit::$libVersion;

        $defaultHeaders['Content-Type'] = 'application/json';
        $defaultHeaders['xendit-lib'] = $lib;
        $defaultHeaders['xendit-lib-ver'] = $libVersion;

        return array_merge($defaultHeaders, $headers);
    }

    /**
     * Send request from client
     *
     * @param string $method  request method
     * @param string $url     additional url to base url
     * @param array  $params  user's params
     * @param array  $headers request' headers
     *
     * @return array
     * @throws AuthenticationException
     */
    private function _requestRaw($method, $url, $params, $headers)
    {
        $apiKey = Xendit::$apiKey;

        if (!$apiKey) {
            $message = 'No API Key provided. Please set your API key first using '
                . '"Xendit::setApiKey(your-secret-API-key)". You can generate API'
                . ' keys from the Xendit Dashboard.';
            throw new AuthenticationException($message);
        }

        $defaultHeaders = self::_setDefaultHeaders($headers);

        [$rbody, $rcode, $rheaders] = $this->_createHttpClient()->sendRequest(
            $method,
            $url,
            $defaultHeaders,
            $params
        );

        return [$rbody, $rcode, $rheaders];
    }

    /**
     * Create HTTP CLient
     *
     * @return HttpClient\GuzzleClient
     */
    private function _createHttpClient()
    {
        if (!self::$_httpClient) {
            self::$_httpClient = HttpClient\GuzzleClient::instance();
        }
        return self::$_httpClient;
    }

}
