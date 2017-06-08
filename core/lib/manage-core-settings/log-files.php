<?
	$allfiles = getContents(ROOT.$core_dirs['logs']);
	
	// Flatten the array
	$logfiles = array();
	array_walk_recursive($allfiles,function($v, $k) use (&$logfiles){ $logfiles[] = $v; });
	
	// Sort it out
	sort($logfiles);
	
	$filename = urldecode($uri[2]);

	if($filename != "") {
		
		// Scan $allfiles to find the file.
		$dir = false;
		foreach($allfiles as $key => $a) {
			if(is_array($a)) {
				foreach($a as $file) {
					if($file == $filename) $dir = $core_dirs['logs'].$key."/";
				}
			} else {
				if($a == $filename) $dir = $core_dirs['logs'];
			}
		}
		$display_file = ROOT.$dir.$filename;
		
		if(($uri[3] == "delete") && (is_file($display_file))) {
			if(unlink($display_file)) {
				$msgs[] = "Successfully deleted <b>{$filename}</b>";
				
				// Load all the files again.
				$allfiles = getContents(ROOT.$core_dirs['logs']);
				// Flatten the array
				$logfiles = array();
				array_walk_recursive($allfiles,function($v, $k) use (&$logfiles){ $logfiles[] = $v; });
				// Load the first file found.
				foreach($allfiles as $filename) {
					if((!is_array($filename)) && is_file(ROOT.$core_dirs['logs'].$filename)) {$display_file = ROOT.$core_dirs['logs'].$filename; break;}	
				}
			} else {
				$msgs[] = "There was a problem deleting the file {$filename}";
			}
		}
	} else {
		// Load the first file found.
		foreach($allfiles as $filename) {
			if((!is_array($filename)) && is_file(ROOT.$core_dirs['logs'].$filename)) {$display_file = ROOT.$core_dirs['logs'].$filename; break;}	
		}
	}
	
	
?>

<div class='container-fluid'>
	<div class='row'>
		<div class='col-sm-8'>
			<h2>Log Files</h2>
			All log files are displayed in reverse, with the timestamp hidden.
		</div>
		<div class='col-sm-4 text-right'>
			<? if($logfiles) { ?>
				<select id='logfile' class='form-control'>
					<? foreach($logfiles as $file) { ?>
						<option <?=selected($filename,$file);?>><?=$file;?></option>
					<? } ?>
				</select>
				<div class='log-controls row'>
					<div class='col-xs-12'>
						<div class='pull-left'>
							<a href='/manage-core-settings/log-files/<?=$filename;?>/delete' class='btn btn-xs btn-danger'><i class='fa fa-times'></i>&nbsp;Delete this file</a><a href='/manage-core-settings/log-files/<?=$filename;?>'><i class='fa fa-refresh'></i></a>	
						</div>
						<div class='pull-right'>
							<input type='checkbox' id='remove-parenthesis' /> Show Date / Time	
						</div>
					</div>
				</div>
			<? } ?>
		</div>
	</div>
	
	<?=messages($msgs,false);?>
	
	<div class='row'>
		<div class='col-sm-12'>
		<pre><?
			if(is_file($display_file)) {
				$contents = str_replace(array("[","]"),array("<span class='brackets'>[","]</span>"),file_get_contents($display_file));
				$rows = explode("\n",$contents);
				$reversed = implode("\n",array_reverse($rows));
				echo $reversed;	
			} else {
				echo "File {$filename} not found.";	
			}
			?></pre>
		</div>
	</div>
</div>