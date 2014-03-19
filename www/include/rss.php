<?php
header("Content-Type:text/xml; charset=UTF-8");
function full_url($s) {
	$ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
	$sp = strtolower($s['SERVER_PROTOCOL']);
	$protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
	$port = $s['SERVER_PORT'];
	$port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
	$host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : $s['SERVER_NAME'];
	return $protocol . '://' . $host . $port . $s['REQUEST_URI'];
}
$absolute_url = full_url($_SERVER);
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<feed xmlns="http://www.w3.org/2005/Atom">
<title>OpenMagnet Feed</title>
<link href="<?=$absolute_url;?>" rel="self" />
<updated></updated>
<? foreach ($results as $r) { ?>
<entry>
<title><?=$r['title']?></title>
<description><?=$r['description']?></description>
<link><?=$r['uri']?></link>
</entry>
<? } ?>
</feed>
