<?php

$sub = empty($customer) ? '' : "_egoiaq.push(['setSubscriber', \"$customer\"]);";

$te = "<script type='text/javascript'>
    var _egoiaq = _egoiaq || [];
    _egoiaq.push(['setListId', \"$list_id\"]);
    $sub
    _egoiaq.push(['trackPageView']);
</script>";
