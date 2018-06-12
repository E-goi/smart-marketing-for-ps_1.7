<?php

class WebserviceRequest extends WebserviceRequestCore 
{

    /**
     * Get Resources from Web Service Overrides
     * 
     * @return type
     */
	public static function getResources()
    {
    	$resources = parent::getResources();

    	$resources['egoi'] = array(
    		'description' => 'Manage Product info in JSON', 
    		'specific_management' => true
    	);
 
        ksort($resources);
        return $resources;
    }
}