<?php

namespace Platform\Support\Data;

use Platform\Support;

class Json
{
    private $path = null;

    public function __construct($source)
    {
        $this->path = $source;
    }

    public static function readFrom($source)
    {
        $instance = new Json ($source);
        return $instance->read();
    }

    public function read()
    {
        $str = file_get_contents($this->path);
        $json = json_decode($str, true);
        if ($json [basename($this->path, '.json')] != null)
            $result = $json [basename($this->path, '.json')];
        else
            $result = $json;
        return $result;
    }


}