<?php
/**
 * Smart Marketing
 *
 *  @author    E-goi
 *  @copyright 2018 E-goi
 *  @license   LICENSE.txt
 */


$sub = empty($customer)?'':"_egoiaq.push(['setSubscriber', \"$customer\"]);";
$te = "<script type='text/javascript'>
		var _egoiaq = _egoiaq || [];
		var u=((\"https:\" == document.location.protocol) ? \"https://egoimmerce.e-goi.com/\" : \"http://egoimmerce.e-goi.com/\");
		_egoiaq.push(['setClientId', \"$client\"]);
		_egoiaq.push(['setListId', \"$list_id\"]);
		$sub
		_egoiaq.push(['setTrackerUrl', u+'collect']);
        _egoiaq.push(['trackPageView']);\n";
		$sum_price = 0;


if(!isset($order)){

    $sum_price = 0;

    if(!empty($products) && is_array($products)){

        foreach($products as $key => $product){

            $product_id = $product['id_product'];
            $product_name = htmlentities($product['name']);
            $product_cat = empty($product['id_category_default'])?'-':$product['id_category_default'];
            $product_price = (float)$product['price'];
            $product_quantity = $product['cart_quantity'];

            $sum_price += (float)round(($product_price * $product_quantity), 2);

            $te .= "_egoiaq.push(['addEcommerceItem',
            \"$product_id\",
            \"$product_name\",
            \"$product_cat\",
            parseFloat($product_price).toFixed(2),
            $product_quantity]);\n";
        }

        $te .= "_egoiaq.push(['trackEcommerceCartUpdate',
        $sum_price\n
        ]);\n";
    }
}
		
    $te .= "
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript';
    g.defer=true;
    g.async=true;
    g.src=u+'egoimmerce.js';
    s.parentNode.insertBefore(g,s);
</script>";