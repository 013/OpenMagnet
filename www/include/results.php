<?
$requesturi = htmlentities(preg_replace('/&sort=\w+/i','',$_SERVER["REQUEST_URI"]));
$page_no_uri = htmlentities($_SERVER["REQUEST_URI"]);
$page_no_uri = preg_replace('/&amp;n=\d+/i','',$page_no_uri);
$requesturi = preg_replace('/&amp;n=\d+/i','',$requesturi);
function full_url($s) {
	$ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
	$sp = strtolower($s['SERVER_PROTOCOL']);
	$protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
	$port = $s['SERVER_PORT'];
	$port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
	$host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : $s['SERVER_NAME'];
	return $protocol . '://' . $host . $port . $s['REQUEST_URI'];
}
$rss_url = full_url($_SERVER) . "&rss=on";
?>
	<table class="table table-striped table-bordered table-condensed">
		<thead>
			<tr>
				<th  class="tab_col_me"><a href="<?=$requesturi;?>&sort=ca" class="up">&nbsp;&nbsp;</a><a href="<?=$requesturi;?>&sort=cd" class="down">&nbsp;&nbsp;</a>Category</th>
				<th><a href=<?=$requesturi;?>&sort=ta" class="up">&nbsp;&nbsp;</a><a href="<?=$requesturi;?>&sort=td" class="down">&nbsp;&nbsp;</a>Title</th>
				<th class="tab_col_sm"><a href="<?=$requesturi;?>&sort=sa" class="up">&nbsp;&nbsp;</a><a href="<?=$requesturi;?>&sort=sd" class="down">&nbsp;&nbsp;</a>Seeders</th>
				<th class="tab_col_sm"><a href="<?=$requesturi;?>&sort=la" class="up">&nbsp;&nbsp;</a><a href="<?=$requesturi;?>&sort=ld" class="down">&nbsp;&nbsp;</a>Leechers</th>
				<th class="tab_col_sm"><a href="<?=$requesturi;?>&sort=fa" class="up">&nbsp;&nbsp;</a><a href="<?=$requesturi;?>&sort=fd" class="down">&nbsp;&nbsp;</a>Size</th>
				<th class="tab_col_ti">URI</th>
			</tr>
		</thead>
		<tbody>
<? foreach ($results as $r) {
	if (strlen($r['title']) >= 85) {
		$title = substr($r['title'],0,82) . '...';
	} else {
		$title = $r['title'];
	}

?>
			<tr>
				<td class="tab_col_me"><a href="?p=category&c=<?=$r['category']?>"><?=ucfirst(htmlentities($r['category']));?></a></td>
				<td><a href="?p=result&id=<?=$r['id']?>"><?=htmlentities($title);?></a></td>
				<td class="tab_col_sm"><?=$r['seeders']?></td>
				<td class="tab_col_sm"><?=$r['leechers']?></td>
				<td class="tab_col_sm"><?=$this->humanFileSize($r['total_size'])?></td>
				<td class="tab_col_ti"><a href="<?=$r['uri']?>"><span class="glyphicon glyphicon-magnet"></span></a></td>
			</tr>
<? }	// Magnet BG color static gray - href 1px white drop shadow ?>
		</tbody>
	</table>
<?
/* Pagination */

if ($count > RESULTS_PER_PAGE) {
	$pages = ceil($count / RESULTS_PER_PAGE);
	if ($pages > 100) {
		$pages = 100;
	}
?>	
<ul class="pagination">
<?
	if ($this->pno == 0) { ?>
	<li class="disabled"><span>&laquo;</span></li>
<?	} else { ?>
	<li><a href="<?=$page_no_uri;?>&n=<?=$this->pno-1;?>">&laquo;</a></li>
<?	}
	if ($this->pno > 3) { ?>
	<li><a href="<?=$page_no_uri;?>&n=0">0</a></li>
<?		if ($this->pno > 4) { ?>
	<li><span>...</span></li>
<?		}
	} 
	for ($i=$this->pno-3; $i<=$this->pno+3; $i++) {
		if ($i < 0) {
			$i = 0;
		}
		if ($i > $pages) {
			break;
		}
		if ($i == $this->pno) { ?>
	<li class="active"><span><?=$i;?><span class="sr-only">(current)</span></span></li>
<?		} else { ?>
	<li><a href="<?=$page_no_uri;?>&n=<?=$i;?>"><?=$i;?></a></li>
<? 		}
	}
	if ($this->pno < $pages-3) { ?>
	<li><span>...</span></li>
	<li><a href="<?=$page_no_uri;?>&n=<?=$pages;?>"><?=$pages;?></a></span></li>
<?	}
	if ($this->pno == $pages) { ?>
	<li class="disabled"><span>&raquo;</span></li>
<?	} else { ?>
	<li><a href="<?=$page_no_uri;?>&n=<?=$this->pno+1;?>">&raquo;</a></li>
<?	} ?>
</ul>
<?
}
?>
