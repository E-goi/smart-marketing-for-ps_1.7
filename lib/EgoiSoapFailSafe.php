<?php

/*
 * Used to mock fake soap client
 * */
class EgoiSoapFailSafe
{
    public function __call($name, $arguments)
    {
        return [];    
    }

}