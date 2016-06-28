<?php

/**
 * @author timrodger
 * Date: 28/06/2016
 */
class SignatureHeader
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $mechanism;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @param string $value
     */
    public function __construct($value)
    {
        $this->value = $value;

        $parts = explode(' ', $value);
        $this->mechanism = array_shift($parts);
        foreach ($parts as $part){
            $items = explode('=', $part);
            $this->data[$items[0]] = trim($items[1], ',');
        }
    }

    public function getMechanism()
    {
        return $this->mechanism;
    }

    public function getCredential()
    {
        return $this->data['Credential'];
    }

    public function getSignedHeaders()
    {
        return $this->data['SignedHeaders'];
    }

    public function getSignature()
    {
        return $this->data['Signature'];
    }

    public function __toString()
    {
        return $this->value;
    }
}