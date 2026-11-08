<?php

namespace WHMCS\Module\Server\Namecranemail;

/**
 * Talks to the NameCrane API (/api/v1) with the server's API key.
 *
 * Written for PHP 7.2 and up, so the module still runs on WHMCS 7.
 */
class ApiClient
{
    const DEFAULT_HOST = 'namecrane.com';

    /** @var string */
    private $baseUrl;

    /** @var string */
    private $apiKey;

    /**
     * @param string $host
     * @param string $apiKey
     */
    public function __construct($host, $apiKey)
    {
        $this->baseUrl = 'https://' . $host . '/api/v1/';
        $this->apiKey = trim((string) $apiKey);
    }

    /**
     * The client for a WHMCS server: its Hostname is the API host (namecrane.com when empty;
     * the old module's instructions said to enter workspace.org, which means the same), and
     * its Access Hash holds the API key.
     *
     * @param array $params
     * @return self
     */
    public static function fromParams(array $params)
    {
        $host = strtolower(trim(isset($params['serverhostname']) ? (string) $params['serverhostname'] : ''));
        $host = preg_replace('#^https?://#', '', $host);
        $host = rtrim($host, '/');

        if ($host === '' || $host === 'workspace.org') {
            $host = self::DEFAULT_HOST;
        }

        return new self($host, isset($params['serveraccesshash']) ? $params['serveraccesshash'] : '');
    }

    /**
     * @param string $path
     * @param array $query
     * @return array
     * @throws ApiException
     */
    public function get($path, array $query = array())
    {
        return $this->request('GET', $path . ($query ? '?' . http_build_query($query) : ''));
    }

    /**
     * @param string $path
     * @param array $data
     * @return array
     * @throws ApiException
     */
    public function post($path, array $data = array())
    {
        return $this->request('POST', $path, $data);
    }

    /**
     * @param string $path
     * @param array $data
     * @return array
     * @throws ApiException
     */
    public function put($path, array $data = array())
    {
        return $this->request('PUT', $path, $data);
    }

    /**
     * @param string $path
     * @return array
     * @throws ApiException
     */
    public function delete($path)
    {
        return $this->request('DELETE', $path);
    }

    /**
     * A path segment (domain, mailbox or alias name) made safe for the URL.
     *
     * @param string $segment
     * @return string
     */
    public static function segment($segment)
    {
        return rawurlencode(strtolower(trim((string) $segment)));
    }

    /**
     * @param string $method
     * @param string $path
     * @param array|null $data
     * @return array
     * @throws ApiException
     */
    private function request($method, $path, ?array $data = null)
    {
        if ($this->apiKey === '') {
            throw new ApiException('No API key: put your NameCrane API key in the server\'s Access Hash field.', 0);
        }

        $curl = curl_init($this->baseUrl . ltrim($path, '/'));
        $headers = array(
            'Authorization: Bearer ' . $this->apiKey,
            'Accept: application/json',
            'User-Agent: NameCrane-WHMCS-Mail/' . NAMECRANEMAIL_VERSION,
        );

        if ($data !== null) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt_array($curl, array(
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
        ));

        $raw = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        $body = is_string($raw) ? json_decode($raw, true) : null;

        if (function_exists('logModuleCall')) {
            logModuleCall('namecranemail', $method . ' ' . $path, self::masked($data), $raw, $body, array($this->apiKey));
        }

        if ($raw === false) {
            throw new ApiException('Could not reach NameCrane: ' . $error, 0);
        }

        if (! is_array($body)) {
            throw new ApiException('NameCrane sent back something unexpected (HTTP ' . $status . '). Try again shortly.', $status);
        }

        if ($status >= 400) {
            throw new ApiException(self::errorMessage($body, $status), $status, $body);
        }

        return $body;
    }

    /**
     * @param array $body
     * @param int $status
     * @return string
     */
    private static function errorMessage(array $body, $status)
    {
        if (! empty($body['errors']) && is_array($body['errors'])) {
            foreach ($body['errors'] as $messages) {
                return is_array($messages) ? (string) reset($messages) : (string) $messages;
            }
        }

        if (! empty($body['error'])) {
            return (string) $body['error'];
        }

        return ! empty($body['message']) ? (string) $body['message'] : 'NameCrane refused the request (HTTP ' . $status . ').';
    }

    /**
     * The request as the module log shows it, passwords hidden.
     *
     * @param array|null $data
     * @return array|null
     */
    private static function masked(?array $data = null)
    {
        if ($data === null) {
            return null;
        }

        foreach (array('password') as $secret) {
            if (isset($data[$secret])) {
                $data[$secret] = '********';
            }
        }

        return $data;
    }
}
