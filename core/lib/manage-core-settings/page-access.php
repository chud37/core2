<form action='/manage-core-settings/<?=$section;?>' method='POST'>
<input type='hidden' name='update-core-authentication' value='page-access-levels' />
<div class='container-fluid'>
	<div class='row'>
		<div class='col-sm-11 bold'>Page Access Levels</div>
		<div class='col-sm-1 text-right'>
			<button type='button' class='toggle-info' href='#pageaccess' data-toggle='collapse'>?</button>
		</div>
	</div>
	
	<div id='pageaccess' class='row collapse'>
		<div class='col-sm-12'>
			<b>If Locked Access = On</b><br/>
			All pages are automatically locked to the user, unless they are logged in.<br/>
			In order for a page to be public with <i>Locked Access</i> turned on, the page <b>must</b> be added here with a level of <b>zero</b>.
			<br/><br/>
			<b>If Locked Access = Off</b><br/>
			All pages are available to the public unless added here.
			<br/><br/>
			An <b>Administrator</b> or <b>SuperUser</b> will have an access level of 100.  An Administrator will be able to access a page no matter what the level it is set too.  They can access all areas.
		</div>
	</div>
	
	
	
	<div class='row'>
		<div class='col-sm-12 editable'>
			<? foreach($auth->page_access_levels as $tag => $val) { ?>
				<div class='row row-edit styles'>
					<div class='col-sm-5'>
						<input type='text' name='setting[name][]' value='<?=$tag;?>' placeholder='home' class='form-control' />
					</div>
					<div class='col-sm-5'>
						<select name='setting[val][]' class='form-control'>
							<? for($x=0;$x<=100;$x++) {
									switch($x) {
									case 0:echo "<option value='0' ".(selected($val,$x)).">Unlocked (0)</option>";break;
									case 1:echo "<option value='1' ".(selected($val,$x)).">All Users With Access (1)</option>";break;
									case 100:echo "<option value='100' ".(selected($val,$x)).">Only Administrator Access (100)</option>";break;
									default:echo "<option value='{$x}' ".(selected($val,$x)).">Level {$x}</option>";break;
									}
								}
							?>
						</select>
					</div>
					<div class='col-sm-2 text-right'>
						<button class='btn btn-default plus-minus fa' data-section='styles'></button>					
					</div>
				</div>
			<? } ?>
		</div>
	</div>
	
	<div class='row'>
		<div class='col-sm-12 text-right'>
			<button type='submit' class='btn btn-primary' alt='Save' title='Save'><span class='fa fa-floppy-o'></span>Save</button>
		</div>
	</div>

</div>
</form>

<?
	// Check to see if there are any pages that are not found in the page_access_levels INI settings.
	$directories = getContents($core_paths['local']."/".$core_dirs['bin'],true);
	$notfound = array();
	foreach($directories as $dir => $values) {
		if($dir != "[build]") {
			$found = false;
			foreach($auth->page_access_levels as $tag => $val) {
				if($tag == $dir) $found = true;	
			}
			if(!$found) $notfound[] = $dir;
		}
	}
	if($notfound) {
		echo "<div class='container-fluid'>
				<div class='row'><div class='col-xs-12 bold' style='color:#f00;'>Missing Pages</div></div>
				<div class='row'><div class='col-xs-12'><p>Below are the directories / pages that were not found in the page_access_levels variable.  This means that these pages are automatically open to the public.</p></div></div>";
		
		foreach($notfound as $dir) {	
			$val = 0;	
		?>
			<form action='/manage-core-settings/<?=$section;?>' method='POST'>
			<input type='hidden' name='update-core-authentication' value='page-access-levels' />
			<?
				// Got to output all the current page access levels, as it is overritten in each save.
				foreach($auth->page_access_levels as $tag => $val) {
					echo "	<input type='hidden' name='setting[name][]' value='{$tag}' />
							<input type='hidden' name='setting[val][]' value='{$val}' />";
					
				}
			?>
				<div class='row'>
					<div class='col-sm-12'>
						<div class='row'>
							<div class='col-sm-5'>
								<input type='text' name='setting[name][]' value='<?=$dir;?>' placeholder='home' class='form-control' />
							</div>
							<div class='col-sm-5'>
								<select name='setting[val][]' class='form-control'>
									<? for($x=0;$x<=100;$x++) {
											switch($x) {
											case 0:echo "<option value='0' ".(selected($val,$x)).">Unlocked (0)</option>";break;
											case 1:echo "<option value='1' ".(selected($val,$x)).">All Users With Access (1)</option>";break;
											case 100:echo "<option value='100' ".(selected($val,$x)).">Only Administrator Access (100)</option>";break;
											default:echo "<option value='{$x}' ".(selected($val,$x)).">Level {$x}</option>";break;
											}
										}
									?>
								</select>
							</div>
							<div class='col-sm-2 text-right'>
								<button type='submit' class='btn btn-default btn-sm' alt='Save' title='Save'><span class='fa fa-floppy-o'></span>Save</button>					
							</div>
						</div>
					</div>
				</div>
			
			</form>
		<? }
		echo "</div>";
	}

?>


<form action='/manage-core-settings/<?=$section;?>' method='POST'>
<input type='hidden' name='update-core-authentication' value='create-page' />
<div class='container-fluid'>
	<div class='row'>
		<div class='col-sm-11 bold'>Create New Page</div>
		<div class='col-sm-1 text-right'>
			<button type='button' class='toggle-info' href='#createpage' data-toggle='collapse'>?</button>
		</div>
	</div>
	<div id='createpage' class='row collapse'>
		<div class='col-sm-12'>
			You can <b>create a new page here</b> by giving a small amount of detail as to which pages you'd like to create.  This makes it quicker than manually creating the files and the permissions on those files, whilst also pre-populating with default content so you can get to work faster.
		</div>
	</div>
	
	<div class='row'>
		<div class='col-sm-12 editable'>
			<div class='row row-edit create-page'>
				<div class='col-sm-5'>
					<input type='text' name='setting[name][]' placeholder='home' class='form-control' />
				</div>
				<div class='col-sm-5'>
					<select name='setting[type][]' class='form-control'>
						<option value='cfg' selected>Config File (.cfg.php)</option>
						<option value='head'>&lt;Head&gt; File  (.head.php)</option>
						<option value='header'>Header File  (.header.php)</option>
						<option value='footer'>Footer File  (.footer.php)</option>
						<option value='php'>PHP file (.php)</option>
						<option value='js'>JQuery File (.js)</option>
						<option value='jshead'>JQuery &lt;Head&gt; File (.head.js)</option>
						<option value='jsfoot'>JQuery &lt;Foot&gt; File (.foot.js)</option>
						<option value='css'>CSS File (.css)</option>
						<option value='cssphp'>PHP CSS File (.css.php)</option>
					</select>
				</div>
				<div class='col-sm-2 text-right'>
					<button class='btn btn-default plus-minus fa' data-section='create-page'></button>					
				</div>
			</div>
			
			<div class='row row-edit create-page'>
				<div class='col-sm-5'>
					<input type='text' name='setting[name][]' placeholder='home' class='form-control' />
				</div>
				<div class='col-sm-5'>
					<select name='setting[type][]' class='form-control'>
						<option value='cfg'>Config File (.cfg.php)</option>
						<option value='head'>&lt;Head&gt; File  (.head.php)</option>
						<option value='header'>Header File  (.header.php)</option>
						<option value='footer'>Footer File  (.footer.php)</option>
						<option value='php' selected>PHP file (.php)</option>
						<option value='js'>JQuery File (.js)</option>
						<option value='jshead'>JQuery &lt;Head&gt; File (.head.js)</option>
						<option value='jsfoot'>JQuery &lt;Foot&gt; File (.foot.js)</option>
						<option value='css'>CSS File (.css)</option>
						<option value='cssphp'>PHP CSS File (.css.php)</option>
					</select>
				</div>
				<div class='col-sm-2 text-right'>
					<button class='btn btn-default plus-minus fa' data-section='create-page'></button>					
				</div>
			</div>
			
			<div class='row row-edit create-page'>
				<div class='col-sm-5'>
					<input type='text' name='setting[name][]' placeholder='home' class='form-control' />
				</div>
				<div class='col-sm-5'>
					<select name='setting[type][]' class='form-control'>
						<option value='cfg'>Config File (.cfg.php)</option>
						<option value='head'>&lt;Head&gt; File  (.head.php)</option>
						<option value='header'>Header File  (.header.php)</option>
						<option value='footer'>Footer File  (.footer.php)</option>
						<option value='php'>PHP file (.php)</option>
						<option value='js'>JQuery File (.js)</option>
						<option value='jshead'>JQuery &lt;Head&gt; File (.head.js)</option>
						<option value='jsfoot'>JQuery &lt;Foot&gt; File (.foot.js)</option>
						<option value='css' selected>CSS File (.css)</option>
						<option value='cssphp'>PHP CSS File (.css.php)</option>
					</select>
				</div>
				<div class='col-sm-2 text-right'>
					<button class='btn btn-default plus-minus fa' data-section='create-page'></button>					
				</div>
			</div>
			
			<div class='row row-edit create-page'>
				<div class='col-sm-5'>
					<input type='text' name='setting[name][]' placeholder='home' class='form-control' />
				</div>
				<div class='col-sm-5'>
					<select name='setting[type][]' class='form-control'>
						<option value='cfg'>Config File (.cfg.php)</option>
						<option value='head'>&lt;Head&gt; File  (.head.php)</option>
						<option value='header'>Header File  (.header.php)</option>
						<option value='footer'>Footer File  (.footer.php)</option>
						<option value='php'>PHP file (.php)</option>
						<option value='js' selected>JQuery File (.js)</option>
						<option value='jshead'>JQuery &lt;Head&gt; File (.head.js)</option>
						<option value='jsfoot'>JQuery &lt;Foot&gt; File (.foot.js)</option>
						<option value='css'>CSS File (.css)</option>
						<option value='cssphp'>PHP CSS File (.css.php)</option>
					</select>
				</div>
				<div class='col-sm-2 text-right'>
					<button class='btn btn-default plus-minus fa' data-section='create-page'></button>					
				</div>
			</div>
		</div>
	</div>
	
	<div class='row'>
		<div class='col-sm-5'>
			<input type='text' class='form-control' name='page_name' placeholder='Page Name (Required)' />
		</div>
		<div class='col-sm-5'>
			<select name='page_level' class='form-control'>
				<? for($x=0;$x<=100;$x++) {
						switch($x) {
						case 0:echo "<option value='0' selected>Unlocked (0)</option>";break;
						case 1:echo "<option value='1'>All Users With Access (1)</option>";break;
						case 100:echo "<option value='100'>Only Administrator Access (100)</option>";break;
						default:echo "<option value='{$x}'>Level {$x}</option>";break;
						}
					}
				?>
			</select>
		</div>
		<div class='col-sm-2 text-right'>
			<button type='submit' class='btn btn-primary' alt='Save' title='Save'><span class='fa fa-plus'></span>Create</button>
		</div>
	</div>

</div>
</form>


