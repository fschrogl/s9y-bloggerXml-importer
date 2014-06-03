<?php
/**
 * Usage:
 *   php bloggerXmlImporter.php <path/to/bloggerExport.xml>
 */

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
	
	echo PHP_EOL;
	for ($i = (int) $start; $i < $end; $i++) {
		$post = array('title' 			 => (string) $xml->entry[$i]->title,
					  'timestamp'		 => (string) $xml->entry[$i]->published,
					  'last_modified'	 => time(),
					  'body'			 => "<p>\n<i></i>\n</p>",
					  'comments'		 => (int) 0,
					  'moderate_comments'=> false,
					  'trackbacks'		 => (int) 0,
					  'extended'		 => (string) $xml->entry[$i]->content,
				      'exflag'			 => (int) 1,
					  'isdraft' 		 => true,
					  'allow_comments'	 => false,
					  'author'			 => 'Fritz Schrogl',
					  'authorid'		 => (int) 1);
		
		// TODO convert timestamp, db connection + insert
		
		echo "DONE -> [" . $i . "] \t - " . $xml->entry[$i]->title . PHP_EOL; 
	}
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