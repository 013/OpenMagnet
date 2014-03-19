<?php

/* TODO */
// Remember regex options
// Search by category
// Update seeders/leechers

/* Done */
// Show files for each magnet link
// Page numbers don't remember sort
require '../config.php';
 
class site {
	public $db;
	
	function __construct() {
		try {
			$this->db = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
		} catch (PDOException $exception) {
			die();
		}
		$this->memcache = new Memcached();
		$this->memcache->addServer(MEMCACHE_HOST, MEMCACHE_PORT);
		
		$this->page =		isset($_GET['p']) ? $_GET['p'] : 'home';
		$this->sort =		isset($_GET['sort']) ? $_GET['sort'] : '';
		 // Current page number
		$this->pno =		isset($_GET['n']) ? (int) $_GET['n'] : 0;
		$this->category =	array();
		$this->pageTitle =	'';
		
		if (isset($_GET['category']) && $_GET['category'] == 'all') {
			$this->category = $this->listcat();
		} else {
			foreach($_GET as $key => $value) {
				if ($key=='category') {
					array_push($this->category, $value);
				}
			}
		}

		$this->term =		isset($_GET['t']) ? $_GET['t'] : '0';
		$this->regex =		isset($_GET['r']) ? true : false; // false true true false maybe
		$this->rss =		isset($_GET['rss']) ? true : false;
	}
	
	public function html() {
		switch($this->page) {
			case 'home':
				$topday = $this->memcache->delete("topday");
				$topday = $this->memcache->get("topday");
				if ($topday) {
					//echo "Using memcache";
					$results = $topday;
				} else {
					//echo "not using memcache";
					$results = $this->topday();
					$this->memcache->set("topday", $results, 8640); // Cache for 24 hours
				}
				$count = 0;
				include 'header.php'; // Maybe show 10 top of day out of each category
				include 'results.php';
				include 'footer.php';
				
				break;
			case 'search':
				// Cache searches for 5 minutes
				$mem_search = $this->memcache->get("search_".$this->term."_".$this->pno."_".$this->sort);
				if ($mem_search) {
					$results = $mem_search;
					$count = $this->memcache->get("count_".$this->term."_".$this->pno."_".$this->sort);
				} else {
					$query = $this->search($this->term, $this->category, $this->regex, $this->rss, $this->sort, $this->pno);
					$results = $query['results'];
					$count = $query['totalRows']; // Amount of results returned
					$this->memcache->set("search_".$this->term."_".$this->pno, $results, 300);
					$this->memcache->set("count_".$this->term."_".$this->pno, $count, 300);
				}
				
				if ($this->rss && RSS_ENABLED) {
					include 'rss.php';
				} else {
					include 'header.php';
					include 'results.php';
					include 'footer.php';
				}
				break;
			case 'result':
				$magnet = new magnet($_GET['id']);
				$this->pageTitle = ' - '.$magnet->title;
				include 'header.php';
				$files = $magnet->getFilelist();
				$fx = '<ul class="list-group">';
				foreach($files as $f) {
					$fx .= "<li class=\"list-group-item\">".$f['file'] . " - " . $this->humanFileSize($f['file_size']) . "</li>";
				}
				$fx .= '</ul>';
				echo <<<HTML
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">{$magnet->title}
					<span style="float: right">
					<span style="color: green">{$magnet->seeders}</span> - <span style="color: red">{$magnet->leechers}</span> - <a href="{$magnet->uri}"><span class="glyphicon glyphicon-magnet"></a>
					</span>
					</h3>
				</div>
				<div class="panel-body">
					{$magnet->description} <br>
				</div>
				{$fx}
			</div>
		</div>
	</div>
HTML;
				include 'footer.php';
				break;
			case 'top': // Top seeded magnets
				if (!isset($_GET['category'])) {
					// Create links for all categories
					$cats = $this->listcat();
					$links = '';
					foreach($cats as $cat) {
						$links .= "<a href=\"?p=top&category={$cat['category']}\">{$cat['category']}</a><br>";
					}
					include 'header.php';
					echo <<<HTML
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">Top</h3>
				</div>
				<div class="panel-body">
					{$links}
				</div>
			</div>
		</div>
	</div>
HTML;
					include 'footer.php';
				} else {
					// Get top ten
					if (isset($_GET['category']) && $_GET['category'] == 'all') {
						$this->category = $this->listcat();
					} else {
						foreach($_GET as $key => $value) {
							//if (preg_match('/^c\d+$/', $key)) {
							if ($key=='category') {
								array_push($this->category, $value);
							}
						}
					}
					$query = $this->getTop($this->category, $this->rss, $this->pno);
					$results = $query['results'];
					$count = $query['totalRows']; // Amount of results returned
					if ($this->rss && RSS_ENABLED) {
						include 'rss.php';
					} else {
						include 'header.php';
						include 'results.php';
						include 'footer.php';
					}
				}
				break;
			case 'category':
				if (!isset($_GET['c'])) { // If viewing the main category page and 
					include 'header.php'; // not an individual category
				$cats = $this->listcat();
				$links = '';
				foreach($cats as $cat) {
					$links .= "<a href=\"?p=top&category={$cat['category']}\">{$cat['category']}</a><br>";
				}
				echo <<<HTML
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">Categories</h3>
				</div>
				<div class="panel-body">
					{$links}
				</div>
			</div>
		</div>
	</div>
HTML;
					include 'footer.php';
				}
				break;
			case 'about':
				include 'header.php';
				echo <<<HTML
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">About</h3>
				</div>
				<div class="panel-body">
					About
				</div>
			</div>
		</div>
	</div>
HTML;
				include 'footer.php';
			default:
				break;
		}
	}
	
	private function search($term, $category=false, $regex=false, $rss=false, $sort='sd', $pno=0) {
		if ($rss && RSS_ENABLED) {
			// If we're loading an RSS feed, we also want the description
			$sql = "SELECT SQL_CALC_FOUND_ROWS id,uri,title,description,category,seeders,leechers,total_size FROM magnets WHERE ";
		} else {
			$sql = "SELECT SQL_CALC_FOUND_ROWS id,uri,title,category,seeders,leechers,total_size FROM magnets WHERE ";
		}
		if ($regex && ALLOW_REGEXP_SEARCHES) {
			$sql .= "(title REGEXP :term ";
			$sql .= "or description REGEXP :term ) ";
		} else {
			$sql .= "(title LIKE :term "; // Need to split the search term by whitespace and search as array
			$sql .= "or description LIKE :term ) "; // Like how we do with categories
			/*$term = preg_split('/\s+/', $term);
			$sql .= "( ";
			foreach ($term as $ind) {
				$sql .= "title like ''";
			}*/
		}
		if ($category) {
			$category = join(',',$category);
			$sql .= "AND category IN (:category) ";
		}
		$sql .= "AND active=1 "; // We don't want any magnets that are inactive
		switch ($sort) {
			case 'sd':
				$sql .= "ORDER BY seeders DESC ";
				break;
			case 'ld':
				$sql .= "ORDER BY leechers DESC ";
				break;
			case 'td':
				$sql .= "ORDER BY title DESC ";
				break;
			case 'cd':
				$sql .= "ORDER BY category DESC ";
				break;
			case 'fd':
				$sql .= "ORDER BY total_size DESC";
				break;
			case 'sa':
				$sql .= "ORDER BY seeders ASC ";
				break;
			case 'la':
				$sql .= "ORDER BY leechers ASC ";
				break;
			case 'ta':
				$sql .= "ORDER BY title ASC ";
				break;
			case 'ca':
				$sql .= "ORDER BY category ASC ";
				break;
			case 'fa':
				$sql .= "ORDER BY total_size ASC";
				break;
			default:
				$sql .= "ORDER BY seeders DESC ";
				break;
		}
		$pno = $pno*RESULTS_PER_PAGE;
		if ($rss && RSS_ENABLED) { // RSS feeds only get a maximum of 50 results
			$sql .= "LIMIT 0,50";
		} else {
			$sql .= "LIMIT :pno,".RESULTS_PER_PAGE;
		}
		$st = $this->db->prepare($sql);
		if ($regex) {
			$st->bindValue(":term", $term, PDO::PARAM_STR);
		} else {
			$st->bindValue(":term", '%'.$term.'%', PDO::PARAM_STR);
		}
		if ($category) {
			$st->bindValue(":category", $category, PDO::PARAM_STR);
		}
		$st->bindValue(":pno", $pno, PDO::PARAM_INT);
		$st->execute();
		
		$sql = "SELECT FOUND_ROWS() AS totalRows";
		$totalRows = $this->db->query($sql)->fetch();
		
		$results = array();
		while ($result = $st->fetch()) {
			array_push($results, $result);
		}
		return(array("results"=>$results, "totalRows"=>$totalRows[0]));
	}
	
	private function getTop($category, $rss=false, $pno=0) {
		if ($rss && RSS_ENABLED) {
			// If we're loading an RSS feed, we also want the description
			$sql = "SELECT SQL_CALC_FOUND_ROWS id,uri,title,description,category,seeders,leechers,total_size FROM magnets WHERE ";
		} else {
			$sql = "SELECT SQL_CALC_FOUND_ROWS id,uri,title,category,seeders,leechers,total_size FROM magnets WHERE ";
		}

		$category = join(',',$category);
		$sql .= "category IN (:category) ";
		$sql .= "AND active=1 ORDER BY seeders DESC ";
		
		$pno = $pno*RESULTS_PER_PAGE;
		if ($rss && RSS_ENABLED) { // RSS feeds only get a maximum of 50 results
			$sql .= "LIMIT 0,50";
		} else {
			$sql .= "LIMIT :pno,".RESULTS_PER_PAGE;
		}
		$st = $this->db->prepare($sql);
		$st->bindValue(":category", $category, PDO::PARAM_STR);
		$st->bindValue(":pno", $pno, PDO::PARAM_INT);
		//$st->bindValue(":pnn", $pno+RESULTS_PER_PAGE, PDO::PARAM_INT);
		$st->execute();
		echo $sql;
		echo $category, $pno;
		$sql = "SELECT FOUND_ROWS() AS totalRows";
		$totalRows = $this->db->query($sql)->fetch();
			
		$results = array();
		while ($result = $st->fetch()) {
			array_push($results, $result);
		}
		var_dump($results);
		return(array("results"=>$results, "totalRows"=>$totalRows[0]));
	}
	
	private function topday() {
		//$sql = "SELECT id,uri,title,category,seeders,leechers,total_size FROM magnets WHERE (date BETWEEN :start AND :end) ORDER BY seeders DESC LIMIT 0,10;";
		$sql = "SELECT id,uri,title,category,seeders,leechers,total_size FROM magnets ORDER BY date DESC, seeders DESC LIMIT 0,10;";
		//$year =         (int) strftime("%Y");
		//$month =        (int) strftime("%m");
		//$day =          (int) strftime("%d");
		//$start =		sprintf("%d-%d-%d 00:00:00",$year,$month,$day-1);
		//$end =			sprintf("%d-%d-%d 23:59:59",$year,$month,$day+1);
		$st =			$this->db->prepare($sql);
		//$st->bindvalue(":start", $start, PDO::PARAM_STR);
		//$st->bindvalue(":end", $end, PDO::PARAM_STR);
		$st->execute();
		$results = $st->fetchAll(PDO::FETCH_ASSOC);
		//while ($result = $st->fetch()) {
		//	array_push($results, $result);
		//}
		//return array();
		return $results;
	}
	
	private function listcat() {
		// To create a new category, all that is needed, is a magnet with the new category assigned
		$allcats = $this->memcache->get("all_cats");
		if (!$allcats) {
			$sql = "SELECT DISTINCT category FROM magnets WHERE active=1 ORDER BY category DESC;";
			$allcats = $this->db->query($sql)->fetchAll();
			$this->memcache->set("all_cats", $allcats, 8640); // Cache for 24 hours
		}
		
		return $allcats;
	}
	
	private function createtables() {
		$sql = <<<SQL
CREATE TABLE IF NOT EXISTS `magnets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uri` varchar(512) DEFAULT NULL,
  `hash` varchar(64) NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `description` text,
  `category` varchar(16) DEFAULT NULL,
  `seeders` int(11) DEFAULT NULL,
  `leechers` int(11) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT '1',
  `last_checked` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB;
SQL;
	}
	public function humanFileSize($size, $precision = 2, $show = "") {
		$b = $size;
		$kb = round($size / 1024, $precision);
		$mb = round($kb / 1024, $precision);
		$gb = round($mb / 1024, $precision);

		if($kb < 1 || $show == "B") {
			return $b . " bytes";
		} else if($mb < 1 || $show == "KB") {
			return $kb . " KB";
		} else if($gb < 1 || $show == "MB") {
			return $mb . " MB";
		} else {
			return $gb . " GB";
		}
	}

}

class magnet {
	public $id;
	public $uri;
	public $hash;
	public $title;
	public $description;
	public $category;
	public $seeders;
	public $leechers;
	public $date;

	public function __construct($id) {
		try {
			$this->db = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
		} catch (PDOException $exception) {
			die();
		}
		
		$this->getById($id);
	}
	
	public function getById($id) {
		$sql = "SELECT * FROM magnets WHERE id=:id";
		$st = $this->db->prepare($sql);
		$st->bindValue(":id", $id, PDO::PARAM_INT);
		$st->execute();
		$magnet = $st->fetch();
		
		if (!$magnet) return -1;
		
		$this->id =				$magnet['id'];
		$this->uri =			$magnet['uri'];
		$this->hash =			$magnet['hash'];
		$this->title =			$magnet['title'];
		$this->description =	$magnet['description'];
		$this->category =		$magnet['category'];
		$this->seeders =		$magnet['seeders'];
		$this->leechers =		$magnet['leechers'];
		$this->date =			$magnet['date'];
	}

	public function getFilelist() {
		$sql = "SELECT DISTINCT file, file_size FROM files WHERE info_hash=:hash";
		$st = $this->db->prepare($sql);
		$st->bindValue(":hash", $this->hash, PDO::PARAM_STR);
		$st->execute();
		$files = $st->fetchAll();
		//foreach( $files as $f ) {
			//var_dump($f);
			//echo $f['file'] . $f['file_size'] . "<br>";
		//}
		return $files;
	}
	
	function create() {
		$sql = "INSERT INTO magnets ( uri, hash, title, description, category, seeders, leechers, date ) VALUES ( :uri, :title, :hash, :description, :category, :seeders, :leechers, :date )";
		$st = $this->db->prepare($sql);
		$st->bindValue(":uri", $this->uri, PDO::PARAM_STR);
		$st->bindValue(":hash", $this->hash, PDO::PARAM_STR);
		$st->bindValue(":title", $this->title, PDO::PARAM_STR);
		$st->bindValue(":description", $this->description, PDO::PARAM_STR);
		$st->bindValue(":category", $this->category, PDO::PARAM_STR);
		$st->bindValue(":seeders", $this->seeders, PDO::PARAM_INT);
		$st->bindValue(":leechers", $this->leechers, PDO::PARAM_INT);
		//$st->bindValue(":date", $this->date, PDO::PARAM_STR);
		$st->execute();
		$this->id = $this->db->lastinsertId();
	}
	
	function update() {}
	
	function delete() {
		$sql = "DELETE FROM magnets WHERE id=:id";
		$st = $this->db->prepare($sql);
		$st->bindValue(":id", $this->id, PDO::PARAM_INT);
		$st->execute();
	}
	
}
 
?>
