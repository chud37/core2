<?
	// Render the page using the files provided.
	if(!$core_files) {trigger_error("No files given to <b>render()</b>.", true);}
	// Define error classes.  A class name cannot begin with a number.
	if($core_error['html']) {$core_errorClass = "error{$core_error['html']}";} else {$core_errorClass = "default";}
	// Include the configuration files.
	foreach($core_files['config'] as $phpfile) {include($phpfile);}
	
	if(!$core_settings['detach']) {
		
		// Begin <html> and <head>
		echo "<!DOCTYPE html>\n<head>\n\t<title>{$core_settings['title']}</title>\n\t<link rel='shortcut icon' href='{$core_settings['favicon']}' />\n\t<meta name='viewport' content='width=device-width, initial-scale=1' />\n\t<meta charset='UTF-8' />\n";
		
		echo "\t<!-- Meta Tags & OG Data-->\n";
		foreach($core_settings['meta'] as $k => $v) {if($v) {echo "\t<meta name='{$k}' content='{$v}' />\n";}}
		foreach($core_settings['og'] as $k => $v) {if($v) {echo "\t<meta property='og:{$k}' content='{$v}' />\n";}}
		
		echo "\t<!-- Page specific <head>, CSS & jQuery -->\n";
		foreach($core_files['styles'] 	as 	$key => $css) 	{echo "\t<link rel='stylesheet' type='text/css' media='".($key==="print" ? "print" : "screen")."' href='{$css}".($core_settings['site_version'] ? "?core_version=".$core_settings['site_version']:false)."' />\n";}
		foreach($core_files['scripts']['head'] as $key => $js) 	{echo "\t<script src='{$js}".($core_settings['site_version'] ? "?core_version=".$core_settings['site_version']:false)."'></script>\n";}
		foreach($core_files['head'] as $phpfile) {include($phpfile);}
		echo "</head>\n";       
																								   
		// Begin <body>		
		echo "<body class='{$page}'>\n";
		
		if(is_file($core_paths['local'].$core_dirs['build']."/script_includes.php")) include($core_paths['local'].$core_dirs['build']."/script_includes.php");
		
		echo "<div id='wrapper'>";
		if($core_settings['wrappers']) echo "<header id='header' class='{$core_errorClass} ".($core_error['html'] ? $core_error['html'] : $core_files['received'])."'>\n"; 
		foreach($core_files['headers'] as $phpfile) {include($phpfile);}
		if($core_settings['wrappers']) echo "</header>\n";
	
		if($core_settings['wrappers']) echo "<div id='content' class='{$core_errorClass} ".($core_error['html'] ? $core_error['html'] : $core_files['received'])."'>\n";
	}
													 
	require_once("{$core_files['root']}{$core_files['dir']}{$core_files['received']}/{$core_files['received']}.php");
																			
	if(!$core_settings['detach']) {         
		if($core_settings['wrappers']) echo "</div>\n";
																		
		if($core_files['footers']) {
			if($core_settings['wrappers']) echo "<footer id='footer' class='{$core_errorClass} ".($core_error['html'] ? $core_error['html'] : $core_files['received'])."'>\n"; 
			foreach($core_files['footers'] as $phpfile) {include($phpfile);}
			if($core_settings['wrappers']) echo "</footer>\n";                     
		}
		
		foreach($core_files['scripts']['foot'] as $key => $js) 	{echo "<script src='{$js}".($core_settings['site_version'] ? "?core_version=".$core_settings['site_version']:false)."'></script>\n";}
		foreach($core_files['foot'] as $foot) {include($foot);}
				
		echo "</div></body>\n</html>";
	}
?>