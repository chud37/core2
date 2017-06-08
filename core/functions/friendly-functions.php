<?
//
// --------------------
//  Friendly Functions: Output a english representation of string.
//	v1.1
// --------------------
//
function friendlyName($str) {
	$split = array(); $find = array("`"); $replace = array("'");
	$explode = explode(" ",strtolower(str_replace($find,$replace,$str)));
	foreach($explode as $s) {
		if(stripos($s,"'") !== false) {
			$exp = explode("'",$s);
			$tmp_split = array();
			foreach($exp as $e) $tmp_split[] = ucwords($e); 
			$split[] = implode("'",$tmp_split);
		} elseif(substr(strtolower($s),0,2) == "mc") {
			$tmp_split = substr(strtolower($s),2);
			$split[] = "Mc".ucwords($tmp_split);
		} else {
			$split[] = ucwords($s);
		}
	}
	$name = implode(" ", $split);
	return $name;
}
function friendlyAddress($a = array(), $type = false, $class = false) {
	if((!$a) || (!is_array($a))) return false;
	
	if((($type) && (isset($a['type'])) && ($a['type'] == $type)) || ($type == false)) {
		$tmp = array();
		if($a['add1'] != "") $tmp[] = "<span class='add1'>{$a['add1']}</span>";
		if($a['add2'] != "") $tmp[] = "<span class='add2'>{$a['add2']}</span>";
		if($a['add3'] != "") $tmp[] = "<span class='add3'>{$a['add3']}</span><br/>";
		if($a['add4'] != "") $tmp[] = "<span class='add4'>{$a['add4']}</span>";
		echo "<div class='friendly-address'><span class='name'>{$a['name']}</span>".implode("",$tmp)."<span class='postcode'>{$a['postcode']}</span><br/><span class='country'>".countryData($a['country'],"name")."</span><span class='telephone'>{$a['telephone']}</span></div>";
	}
}
function friendlyURL($str) {
	return str_replace(array(" ", "__"),"_",strtolower(preg_replace("/[^A-Za-z0-9- _&]/", '', trim($str))));	
}
function friendlyBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
function friendlyPrice($price,$html = true) {
	if($html) {
		$html = "&pound;";
	} else { 
		$html = "£";
	}
	if(!is_numeric($price)) return false;
	switch($price) {
		case ($price === 0): return "0p"; break;	
		case (($price > -1) && ($price < 0)): return ($price*100)."p"; break;
		case (($price < 1) && ($price > 0)): return ($price*100)."p"; break;
		case ($price >= 1): return $html . number_format($price,2); break;
		case ($price < 0): return "-" . $html . number_format(abs($price),2); break;
	}
}
function friendlyDateTime($ts,$html = true) {
	if(!is_int($ts)) {$ts = strtotime($ts);} 
	if(date("dmy",strftime($ts)) != date("dmy",time())) {
		if((strftime($ts)) > (time() - 602000)) {
			if((strftime($ts)) > (time() - 86400)) {
				$date = date("\Y\\e\\s\\t\\e\\r\\d\\a\\y \a\\t H:i",strftime($ts));		
			} else {
				$date = date("\L\a\s\\t l \a\\t H:i",strftime($ts));	
			}
		} else {
			if($html) {
				$date = date("j<\s\u\p>S</\s\u\p> F \a\\t H:i",strftime($ts));
			} else {
				$date = date("jS F \a\\t H:i",strftime($ts));
			}
		}
	} else {
		$date = date("\T\o\d\a\y \a\\t H:i ",strftime($ts));
	}
	$fulldate = date("jS M, Y - H:m",$ts);
	if($html) { 
		return("<span title='{$fulldate}'>{$date}</span>");	
	} else {
		return($date);	
	}
}
function friendlyDate($ts,$html = true) {
	if(!is_int($ts)) {$ts = strtotime($ts);}
	if(date("dmy",strftime($ts)) != date("dmy",time())) {
		if((strftime($ts)) > (time() - 602000)) {
			if((strftime($ts)) > (time() - 86400)) {
				$date = date("\Y\\e\\s\\t\\e\\r\\d\\a\\y \a\\t H:i Y",strftime($ts));
			} else {
				$date = date("\L\a\s\\t l \a\\t H:i Y",strftime($ts));	
			}
		} else {
			if($html) {
				$date = date("j<\s\u\p>S</\s\u\p> F Y",strftime($ts));
			} else {
				$date = date("jS F Y",strftime($ts));
			}
		}
	} else {
		$date = date("\T\o\d\a\y",strftime($ts));
	}
	$fulldate = date("jS M, Y - H:m",$ts);
	if($html) { 
		return("<span title='{$fulldate}'>{$date}</span>");	
	} else {
		return($date);	
	}
}
function emoticons($str) { 
	$replace = array("<img src=\"/img/icons/emoticons/confused.png\" class=\"emoticon\">",
		"<img src=\"/img/icons/emoticons/cry.png\" class=\"emoticon\">",
		"<img src=\"/img/icons/emoticons/duh.png\" class=\"emoticon\">",
		"<img src=\"/img/icons/emoticons/gasp.png\" class=\"emoticon\">",
		"<img src=\"/img/icons/emoticons/gerty.gif\" class=\"emoticon\">",
		"<img src=\"/img/icons/emoticons/happy.gif\" class=\"emoticon\">",
		"<img src=\"/img/icons/emoticons/lol.gif\" class=\"emoticon\">",
		"<img src=\"/img/icons/emoticons/sad.png\" class=\"emoticon\">",
		"<img src=\"/img/icons/emoticons/smile.gif\" class=\"emoticon\">",
		"<img src=\"/img/icons/emoticons/tounge.png\" class=\"emoticon\">");
	$search = array(":S",":'(",":|",":o"," :/ ",":D","lol",":(",":)",":P");
	return trim(str_replace($search,$replace,$str)); 
} 
function highlight($haystack = false, $needle = false) {
	if((!$needle) || (!$haystack)) return $haystack;
	$words = explode(" ",$needle);
	$highlighted = $haystack;
	foreach($words as $w) {
		$original_case_word = substr($haystack, stripos($haystack, $w), strlen($w));
		$highlighted = str_ireplace($w, "<span style='background-color:#FFFFA5;'>{$original_case_word}</span>", $highlighted);			
	}
	return $highlighted;
}
function parse_hyperlinks($m)
{
	$len = 50;
	$format = '<a href="%s" target="_blank">%s</a>';
	$href = $name = html_entity_decode($m[0]);
	if (strpos($href,'://')===false) $href = 'http://'.$href;
	if( strlen($name) > $len) {
		$k = ($len - 3) >> 1;
		$name = substr($name, 0, $k ) . '...' . substr( $name, -$k );
	}
	return sprintf($format, htmlentities($href), htmlentities($name) );
}
function hyperlinks($str) {
	return preg_replace_callback('~((?:https?://|www\d*\.)\S+[-\w+&@#/%=\~|])~','parse_hyperlinks',$str);	
}
function getElapsedTime($eventTime)
{	$totaldelay = time() - strtotime($eventTime);
	if($totaldelay <= 0)
		{	return '';	} else 
		{	$days=floor($totaldelay/86400);
			$totaldelay = $totaldelay % 86400;
			$string =  $days.' days, ' . floor($totaldelay / 3600) . " hours and "; 
			$totaldelay = $totaldelay % 3600;
			$string .= floor($totaldelay / 60) . " minutes.";
			return $string;
		}
}

function ordinal($i) {
	// http://www.binarytides.com/php-function-to-add-st-nd-rd-th-to-the-end-of-numbers/
	$l = substr($i,-1);
	$s = substr($i,-2,-1);
	return (($l==1&&$s==1)||($l==2&&$s==1)||($l==3&&$s==1)||$l>3||$l==0?'th':($l==3?'rd':($l==2?'nd':'st')));
}

?>
