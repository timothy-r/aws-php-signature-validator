<?php

use Aws\Signature\SignatureV4;
use Aws\Signature\SignatureTrait;
use GuzzleHttp\Psr7;

/**
 * @todo pass $service and $region to the constructor
 *
 * pass RequestInterface and CredentialsInterface to validate function
 * use a helper class to extract keyId from the header string
 * use base class createContext method as-is
 */
class SignatureV4Validator extends SignatureV4
{
    use SignatureTrait;

    private $key;

    private $token;

    private $service;

    private $region;

    /**
     * @param string $key
     * @param string $token
     * @param string $service
     * @param string $region
     */
    public function __construct($key, $token, $service, $region)
    {
        $this->key = $key;
        $this->token = $token;
        $this->service = $service;
        $this->region = $region;
    }

    /**
     * @param string $actual
     * @param array $parsedRequest
     * @param string $payload
     * @return bool
     */
    public function validate($actual, array $parsedRequest, $payload)
    {
        $date = $parsedRequest['headers']['X-Amz-Date'];
        $shortDate = substr($date, 0, 8);

        $payload = hash('sha256', $payload);

        $scope = $this->createScope($shortDate, $this->region, $this->service);
        $context = $this->createContext($parsedRequest, $payload);

        $toSign = $this->createStringToSign($date, $scope, $context['creq']);

        $signingKey = $this->getSigningKey($shortDate, $this->region, $this->service, $this->token);

        $calculated = hash_hmac('sha256', $toSign, $signingKey);

        return ($calculated === $actual);
    }

    private function createStringToSign($longDate, $scope, $creq)
    {
        $hash = hash('sha256', $creq);

        return "AWS4-HMAC-SHA256\n{$longDate}\n{$scope}\n{$hash}";
    }

    /**
     * @param array  $parsedRequest
     * @param string $payload Hash of the request payload
     * @return array Returns an array of context information
     */
    private function createContext(array $parsedRequest, $payload)
    {
        // Normalize the path as required by SigV4
        $canon = $parsedRequest['method'] . "\n"
            . $this->createCanonicalizedPath($parsedRequest['path']) . "\n"
            . $this->getCanonicalizedQuery($parsedRequest['query']) . "\n";

        // Case-insensitively aggregate all of the headers.
        $aggregate = [];
        foreach ($parsedRequest['headers'] as $key => $value) {
            $key = strtolower($key);
            $aggregate[$key][] = $value;
        }

        ksort($aggregate);
        $canonHeaders = [];
        foreach ($aggregate as $k => $v) {
            if (count($v) > 0) {
                sort($v);
            }
            $canonHeaders[] = $k . ':' . preg_replace('/\s+/', ' ', implode(',', $v));
        }

        $signedHeadersString = implode(';', array_keys($aggregate));
        $canon .= implode("\n", $canonHeaders) . "\n\n"
            . $signedHeadersString . "\n"
            . $payload;

        return ['creq' => $canon, 'headers' => $signedHeadersString];
    }

    private function getCanonicalizedQuery(array $query)
    {
        unset($query['X-Amz-Signature']);

        if (!$query) {
            return '';
        }

        $qs = '';
        ksort($query);
        foreach ($query as $k => $v) {
            if (!is_array($v)) {
                $qs .= rawurlencode($k) . '=' . rawurlencode($v) . '&';
            } else {
                sort($v);
                foreach ($v as $value) {
                    $qs .= rawurlencode($k) . '=' . rawurlencode($value) . '&';
                }
            }
        }

        return substr($qs, 0, -1);
    }

}