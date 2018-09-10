<?php
/**
 *  Smart Marketing
 *
 *  @author    E-goi
 *  @copyright 2018 E-goi
 *  @license   LICENSE.txt
 *  @package override/classes/webservice/WebserviceRequest
 */

class WebserviceRequest extends WebserviceRequestCore 
{

    /**
     * Get Resources from Web Service Overrides
     * 
     * @return array
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