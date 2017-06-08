<?
function fancyarray($arr, $toggle = "") {
	$return = "<ul class='fancyarray ".($toggle == "" ? "parent" : "child toggle{$toggle}")."'>\n";
	$filetypes = array("js","css","png","jpg","pdf","gif");
	foreach ($arr as $key => $val) {
		// $name = $p->getFilename();
		if($val != "." && $val != "..") {
			if (is_array($val)) {
				$toggle .= preg_replace("/[^A-Za-z0-9]/","",strtolower($key));
				$return .= "<li class='key'><a href='#' class='folder' data-toggle='{$toggle}'><b>{$key}</b></a>\n";
				$return .= fancyarray($val, $toggle);
				$return .= "</li>";
			} else {
				$link = false;
				$exp = explode(".",substr($val,-4));
				if(isset($exp[1])) {foreach($filetypes as $f) {if(stristr($f,$exp[1]) != false) {$link = true; break;}}}
				
				if($link) {
					$return .= "<li class='var'><b>{$key}</b><a href='{$val}' target='_blank'>{$val}</a></li>\n";
				} else {
					$return .= "<li class='var'><b>{$key}</b>".($val != "" ? $val : "<small>no data</small>")."</li>\n";	
				}
			}
		}
	}
	if(!$arr) {
		$return .= "<li class='var no-data'><small>no data</small></li>\n";
	}
	
	$return .= "</ul>\n";
	return $return;
} 

?>
<div class='container-fluid'>
	<div class='row'>
		<div class='col-sm-10'><h2>Current Build</h2></div>
		<div class='col-sm-2'><button id='toggle-all' class='btn btn-default'><span class='fa fa-toggle-on'></span>Toggle All</button></div>
	</div>
	<div class='row'><div class='col-sm-12'><?=fancyarray($core_settings);?></div></div>
	<div class='row'><div class='col-sm-12'><?=fancyarray($core_files);?></div></div>
	<div class='row'><div class='col-sm-12'><?=fancyarray($core_error);?></div></div>
</div>