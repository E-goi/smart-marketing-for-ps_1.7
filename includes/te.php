<?php
/**
 * Smart Marketing
 *
 *  @author    E-goi
 *  @copyright 2018 E-goi
 *  @license   LICENSE.txt
 */

$te = "<script type='text/javascript'>
		var _egoiaq = _egoiaq || [];
		var u=((\"https:\" == document.location.protocol) ? \"https://te.e-goi.com/\" : \"http://te.e-goi.com/\");
		_egoiaq.push(['setClientId', \"$client\"]);
		_egoiaq.push(['setListId', \"$list_id\"]);
		_egoiaq.push(['setSubscriber', \"$customer\"]);
		_egoiaq.push(['setTrackerUrl', u+'collect']);\n";
		
		$sum_price = '';

if(!empty($products)){
    foreach($products as $key => $product){

        $product_id = $product['id_product'];
        $product_name = htmlentities($product['name']);
        $product_cat = '-';
        $product_price = floatval($product['price_wt']);
        $product_quantity = $product['quantity'];

        $sum_price += floatval(round(($product_price * $product_quantity), 2));

        $te .= "_egoiaq.push(['addEcommerceItem',
        \"$product_id\",
        \"$product_name\",
        \"$product_cat\",
        $product_price,
        $product_quantity]);\n";
    }

}else if ($cart_zero == 1) {
    $id = 0;
    $name = '';
    $cat = '';
    $price = 0;
    $qty = 0;
    $sum_price = 0;

    $te .= "_egoiaq.push(['addEcommerceItem',
        \"$id\",
        \"$name\",
        \"$cat\",
        $price,
        $qty]);\n";

    $te .= "_egoiaq.push(['trackEcommerceCartUpdate',
            0\n
        ]);\n";
}

if(isset($order)){
    $order_total = floatval(round($sum_price, 2));

    $te .= "_egoiaq.push(['trackEcommerceOrder',
    \"$order_id\",
    \"$order_total\",
    \"$order_subtotal\",
    $order_tax,
    $order_shipping,
    $order_discount]);\n";

}else{

    if($sum_price){

        $te .= "_egoiaq.push(['trackEcommerceCartUpdate',
        $sum_price\n
        ]);\n";
    }
}
		
    $te .= "_egoiaq.push(['trackPageView']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript';
    g.defer=true;
    g.async=true;
    g.src=u+'egoimmerce.js';
    s.parentNode.insertBefore(g,s);
</script>";