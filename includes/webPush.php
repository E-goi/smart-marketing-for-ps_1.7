<?php

$webPush = '<script type="text/javascript">
	var _egoiwp = _egoiwp || {};
	(function(){
	var u="https://cdn-static.egoiapp2.com/";
	_egoiwp.code = "' . $appCode . '";
	var d=document, g=d.createElement(\'script\'), s=d.getElementsByTagName(\'script\')[0];
	g.type=\'text/javascript\';
	g.defer=true;
	g.async=true;
	g.src=u+\'webpush.js\';
	s.parentNode.insertBefore(g,s);
	})();
</script>';

if(empty($appCode)){
    $webPush = '';
}
