<?php
/**
 * Usage:
 *   php bloggerXmlImporter.php <path/to/bloggerExport.xml>
 */

CONST DB_CONNECTION = "mysql:host=localhost;dbname=s9y";
CONST DB_USER = "s9y";
CONST DB_PASS = "12init34";
CONST DB_TABLENAME = "s9y_entries";

// =============================================================================
// Helper
// =============================================================================

function listEntries($start, $end, $xml) {
	if ($end < $start || $end == 0)
		$end = count($xml->entry);
	else
		$end++;
	
	echo PHP_EOL;
	for ($i = $start; $i < $end; $i++) {
		echo "[" . $i . "] \t - " . $xml->entry[$i]->title . PHP_EOL; 
	}
}

function importEntries($start, $end, $xml) {
	if ($end < $start || $end == 0)
		$end = count($xml->entry);
	else
		$end++;
	
	// Database
	$dbh = new PDO(DB_CONNECTION, DB_USER, DB_PASS);
	$stmt = $dbh->prepare("INSERT INTO " . DB_TABLENAME 
				. "(title, timestamp, body, comments, trackbacks, extended, exflag, author, authorid, isdraft, allow_comments, last_modified, moderate_comments)"
				. "VALUES (:title, :timestamp, :body, :comments, :trackbacks, :extended, :exflag, :author, :authorid, :isdraft, :allow_comments, :last_modified, :moderate_comments)");
	
	// Insert entries
	$dbh->beginTransaction();
	echo PHP_EOL;
	for ($i = (int) $start; $i < $end; $i++) {
		$post = array('title' 			 => (string) $xml->entry[$i]->title,
					  'timestamp'		 => (string) $xml->entry[$i]->published,
					  'last_modified'	 => time(),
					  'body'			 => "<p>\n<i></i>\n</p>",
					  'comments'		 => (int) 0,
					  'moderate_comments'=> true,
					  'trackbacks'		 => (int) 0,
					  'extended'		 => (string) $xml->entry[$i]->content,
				      'exflag'			 => (int) 1,
					  'isdraft' 		 => true,
					  'allow_comments'	 => 'false',
					  'author'			 => 'Fritz Schrogl',
					  'authorid'		 => (int) 1);
		
		// Convert timestamp to Unix-Time
		$post['timestamp'][10] = ' ';
		$post['timestamp'][19] = ' ';
		$post['timestamp'][20] = ' ';
		$post['timestamp'][21] = ' ';
		$post['timestamp'][22] = ' ';
		$datetime = date_create_from_format("Y-m-d H:i:s T", $post['timestamp']);
		$post['timestamp'] = $datetime->getTimestamp();
		
		$stmt->execute($post);
		echo "DONE -> [" . $i . "] \t - " . $xml->entry[$i]->title . PHP_EOL; 
	}
	$dbh->commit();
	$dbh = null; // Close connection
}

function readInput($text = "Input: ") {
	echo $text . ": ";
	$handle = fopen("php://stdin", "r");
	if (!$handle)
		die("Can't read from stdin");
	$input = stream_get_line($handle, 1024, PHP_EOL);
	fclose($handle);
	return $input;
}

// =============================================================================
// Main
// =============================================================================

if (!isset($_SERVER['argv'][1])) {
	echo "Usage:\n\tphp bloggerXmlImporter.php <path/to/bloggerExport.xml>";
	exit(1);
}

// Parse Blogger's XML
$xml = simplexml_load_file($_SERVER['argv'][1]);
echo "BlogID:\t\t" . $xml->id . PHP_EOL;
echo "Title:\t\t" . $xml->title . PHP_EOL;
echo "Posts:\t\t" . count($xml->entry) . PHP_EOL;

// Command prompt/loop
while (TRUE) {
	echo PHP_EOL;
	$cmd = readInput("(l)ist entries/(i)mport entries/e(x)it program");
	
	switch (strtolower($cmd)) {
		case "l":
			$start = (int) readInput("Start from entry");
			$end = (int) readInput("Stop at entry");
			listEntries($start, $end, $xml);
			break;
		
		case "i":
			$start = (int) readInput("Start from entry");
			$end = (int) readInput("Stop at entry");
			importEntries($start, $end, $xml);
			break;
		
		case "x":
			exit(0);
			break;
	}
}
?>