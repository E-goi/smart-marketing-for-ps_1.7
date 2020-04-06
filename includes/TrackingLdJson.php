<?php
/**
 * Ld+Json to enable automated tracking of products
 *
 *  @author    E-goi
 *  @copyright 2018 E-goi
 *  @license   LICENSE.txt
 */
$img = $product->getCover($product->id);
$te .= '
<script type="application/ld+json" class="egoi-smart-marketing">
{
"@context":"https://schema.org",
"@type":"Product",
"productID":"'.$product->id.'",
"name":"'.$product->name.'",
"description":"'.filter_var($product->description_short ? $product->description_short : $product->description, FILTER_SANITIZE_STRING).'",
"url":"'.$this->context->link->getProductLink($product).'",
"image":"'.$this->context->link->getImageLink(isset($product->link_rewrite) ? $product->link_rewrite : $product->name, (int)$img["id_image"], "home_default").'",
"brand":"'.$product->manufacturer_name.'",
"offers": [
        {
        "@type": "Offer",
        "price": "'.round($product->price ? $product->price : $product->base_price, 2).'",
        "priceCurrency": "'.$this->context->currency->iso_code.'",
        "itemCondition": "https://schema.org/'. ($product->condition == "new" ? "NewCondition" : "UsedCondition") .'",
        "availability": "http://schema.org/'. ($product->quantity <= 0 ? ($product->available_for_order ? "Order" : "InStock") : "OutOfStock") .'"
        }
    ]
}
</script>
<script>var egoi_product = { "id":"'.$product->id;
if($client){$te .= '","client_id":"'.$client;}
$te .= '","name":"'.$product->name.'","price":"'.round($product->price ? $product->price : $product->base_price, 2).'"};</script>';