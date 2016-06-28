<?php

use Aws\Credentials\CredentialProvider;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;


/**
 * @author timrodger
 * Date: 28/06/2016
 */
class SignatureValidatorTest extends PHPUnit_Framework_TestCase
{

    public function getValidationData()
    {
        return [
            ['https://abcdef.eu.cloudsearch.amazonaws.com/2013-01-01/search', 'eu-west-1', 'cloudsearch', 'user_name', 'qweasd']
        ];
    }

    /**
     * @dataProvider getValidationData
     *
     * @param $url
     * @param $region
     * @param $service
     * @param $user
     * @param $token
     */
    public function testValidation($url, $region, $service, $key, $token)
    {
        $request = new Request('GET', $url);

        $credentials = new \Aws\Credentials\Credentials($key, $token);

        // Construct a request signer
        $signer = new SignatureV4($service, $region);

        // Sign the request
        $request = $signer->signRequest($request, $credentials);

        // get the signature from the header
        $auth = $request->getHeaderLine('Authorization');

        $header = new SignatureHeader($auth);

        var_dump($header);
    }

}