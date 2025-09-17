<?php
/**
 * Smart Marketing
 *
 *  @author    E-goi
 *  @copyright 2018 E-goi
 *  @license   LICENSE.txt
 */

$sub = empty($customer) ? '' : "_egoiaq.push(['setSubscriber', \"$customer\"]);";
$te = "<script type='text/javascript'>
    var _egoiaq = _egoiaq || [];
    var u=((\"https:\" == document.location.protocol) ? \"https://egoimmerce.e-goi.com/\" : \"http://egoimmerce.e-goi.com/\");
    _egoiaq.push(['setClientId', \"$client\"]);
    _egoiaq.push(['setListId', \"$list_id\"]);
    $sub
    _egoiaq.push(['setTrackerUrl', u+'collect']);
    _egoiaq.push(['trackPageView']);\n";

$te .= "
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript';
    g.defer=true;
    g.async=true;
    g.src=u+'egoimmerce.js';
    s.parentNode.insertBefore(g,s);
</script>";
