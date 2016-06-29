<?php

use Aws\Credentials\Credentials;
use Aws\Signature\SignatureV4;
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
            ['https://abcdef.eu.cloudsearch.amazonaws.com/2013-01-01/search', 'eu-west-1', 'cloudsearch', 'user_name', 'qweasd'],
            ['https://mydomain.com/path/b?q=foo', 'eu', 'api', 'user_name', 'qweasd'],
            ['https://other.net/path/b?a=2&z=boo', 'eu', 'api', 'frankie', 'h0ll1WooD']
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
    public function testValidatingGetRequest($url, $region, $service, $key, $token)
    {
        $request = new Request('GET', $url);

        // Construct a request signer
        $signer = new SignatureV4($service, $region);

        // Sign the request
        $request = $signer->signRequest($request, new Credentials($key, $token));

        #var_dump($request->getHeaderLine('X-Amz-Date'));
        #var_dump($request->getHeaderLine('Host'));

        // get the signature from the header
        $auth = $request->getHeaderLine('Authorization');

      //  var_dump($auth);

        $header = new SignatureHeader($auth);

        #var_dump($header->getSignature());

        $validator = new SignatureV4Validator($key, $token, $service, $region);

        $query = new AssocArrayString(parse_url($url, PHP_URL_QUERY));

        $req = parse_url($url);

//        var_dump($req);

        $parsedRequest = [
            'headers' => [
                'X-Amz-Date' => $request->getHeaderLine('X-Amz-Date'),
                'Host' => $request->getHeaderLine('Host')
            ],
            'method' => 'GET',
            'path' => parse_url($url, PHP_URL_PATH),
            'query' => $query->toArray(),
        ];

        $result = $validator->validate($header->getSignature(), $parsedRequest, $payload = '');

        $this->assertTrue($result);
    }

}