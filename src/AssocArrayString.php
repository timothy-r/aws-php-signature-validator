<?php

/**
 * @author timrodger
 * Date: 28/06/2016
 */
class AssocArrayString
{

    private $data = [];

    private $value;

    /**
     * @param string $value, eg q=foo&bar=good
     */
    public function __construct($value, $delimA = '&', $delimB = '=')
    {
        if (strlen($value) > 0) {
            $parts = explode($delimA, $value);

            foreach ($parts as $part) {
                $items = explode($delimB, $part);
                $this->data[$items[0]] = $items[1];
            }

            ksort($this->data);
        }
        $this->value = $value;
    }

    public function toArray()
    {
        return $this->data;
    }

    public function __toString()
    {
        return $this->value;
    }
}
