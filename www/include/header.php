<?
$page = isset($_GET['p']) ? $_GET['p'] : '';
$term = isset($_GET['t']) ? $_GET['t'] : '';
$regex = isset($_GET['r']) ? 'checked' : '';
if ($page == 'contact' || $page == 'about' || $page == 'result' || $page == 'top'){
	$smallsearch = true;
} else {
	$smallsearch = false;
}
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
<title><?=SITE_NAME.$this->pageTitle;?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="favicon.ico" rel="icon" type="image/x-icon" />
<link type="text/css" rel="stylesheet" href="//fonts.googleapis.com/css?family=Lobster">
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
<link rel="stylesheet" href="css/style.css">
<script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>
<script src="js/script.js"></script>
</head>
<body>
<div class="container">
	<div class="navbar navbar-default" role="navigation">
		<div class="navbar-header">
		<a class="navbar-brand brand_n" href="/">
		<?=SITE_NAME;?>
		</a>
		</div>
		<ul class="nav navbar-nav">
			<li <? if ($page == '') {?>class="active"<? } ?>><a href="/">Home</a>
			<li <? if ($page == 'top') {?>class="active"<? } ?>><a href="?p=top">Top</a>
			<li <? if ($page == 'category') {?>class="active"<? } ?>><a href="?p=category">Categories</a>
			<?/*<li <? if ($page == 'contact') {?>class="active"<? } ?>><a href="?p=contact">Contact</a>*/?>
			<li <? if ($page == 'about') {?>class="active"<? } ?>><a href="?p=about">About</a>
		</ul>
<? if ($smallsearch) { ?>
		<form class="navbar-form navbar-right role="form" method="get">
			<input type="hidden" name="p" value="search">
			
			<div class="form-group">
				<div class="input-group custom-search-form">
				<input type="text" class="form-control" id="inputSearch" placeholder="Search" name="t">
				<span class="input-group-btn">
				<button class="btn btn-default" type="button" action="submit">
				<span class="glyphicon glyphicon-search"></span>
				</button>
				</span>
				</div>
			</div>
		</form>
<? } ?>
	</div>
<? if (!$smallsearch) { ?>
	<div class="jumbotron">
		<form role="form" method="get">
		<input type="hidden" name="p" value="search">
			<?
			if (isset($_GET['sort'])) {
			$sort = htmlspecialchars($_GET['sort']);
			echo <<<HTML
			<input type="hidden" name="sort" value="{$sort}">
HTML;
			}
			?>
			<div class="form-group">
				<div class="input-group custom-search-form">
				<input type="text" class="form-control" id="inputSearch" placeholder="Search" name="t" value="<?=$term;?>">
				<span class="input-group-btn">
				<button class="btn btn-default" type="button" action="submit">
				<span class="glyphicon glyphicon-search"></span>
				</button>
				</span>
				</div>
			</div>
			<div class="form-group">
				<? if (ALLOW_REGEXP_SEARCHES) { ?>
				<span class="button-checkbox">
					<button type="button" class="btn" data-color="success">Regexp</button>
					<input type="checkbox" class="hidden" name="r" <?=$regex;?> />
				</span>
				<? } ?>
				<span class="button-checkbox">
					<button type="button" class="btn" data-color="primary">Movies</button>
					<input type="checkbox" class="hidden" name="category" values="movies" />
				</span>
				<span class="button-checkbox">
					<button type="button" class="btn" data-color="primary">TV</button>
					<input type="checkbox" class="hidden" name="category" values="tv"/>
				</span>
				<span class="button-checkbox">
					<button type="button" class="btn" data-color="primary">Audio</button>
					<input type="checkbox" class="hidden" name="category" values="audio" />
				</span>
			</div>
		</form>
	</div>
	<? } ?>
