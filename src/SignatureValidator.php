<?php

/**
 */
class SignatureValidator
{

    public function __construct()
    {

    }

    public function validate()
    {

    }

    private function getSignatureKey($key, $dateStamp, $region, $service)
    {


//        date= Crypto.HMAC(Crypto.SHA256, dateStamp, "AWS4" + key, { asBytes: true})
//        var kRegion= Crypto.HMAC(Crypto.SHA256, regionName, kDate, { asBytes: true });
//        var kService=Crypto.HMAC(Crypto.SHA256, serviceName, kRegion, { asBytes: true });
//        var kSigning= Crypto.HMAC(Crypto.SHA256, "aws4_request", kService, { asBytes: true });
//
//        return kSigning;
    }
}