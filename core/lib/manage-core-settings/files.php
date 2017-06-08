<form action='/manage-core-settings/<?=$section;?>' method='POST'>
<input type='hidden' name='update-core-settings' value='styles' />

<div class='container-fluid'>
	<div class='row'>
		<div class='col-sm-11 bold'>CSS Styles</div>
		<div class='col-sm-1 text-right'>
			<button type='button' class='toggle-info' href='#css-styles' data-toggle='collapse'>?</button>
		</div>
	</div>
	<div class='row'>
		<div class='col-sm-12 editable'>
			<? foreach($default_core_settings['styles'] as $tag => $val) { ?>
				<div class='row row-edit styles'>
					<div class='col-sm-5'>
						<input type='text' name='setting[name][]' value='<?=$tag;?>' placeholder='Style Title' class='form-control' />
					</div>
					<div class='col-sm-5'>				
						<input type='text' name='setting[val][]' value='<?=$val;?>' placeholder='Style URL' class='form-control' />
					</div>
					<div class='col-sm-2 text-right'>
						<button class='btn btn-default plus-minus fa' data-section='styles'></button>					
					</div>
				</div>
			<? } ?>
		</div>
	</div>
		
	<div id='css-styles' class='row collapse'>
		<div class='col-sm-12'>
			Place <i>URLs</i> or <i>relative paths</i> to your <b>CSS Styles</b> here.  <b>Core</b> will automatically load the files contained in <b>/bin/[build]/css</b> and <b>/bin/$page/</b>.  You can add further CDNs here if you wish.  <a href='https://fortawesome.github.io/Font-Awesome/icons/' target='_blank'>Font-Awesome</a> and <a href='http://getbootstrap.com/' target='_blank'>Bootstrap</a> are set by default.  			
		</div>
	</div>
	
	<div class='row'>
		<div class='col-sm-12 text-right'>
			<button type='submit' class='btn btn-primary' alt='Save' title='Save'><span class='fa fa-floppy-o'></span>Save</button>
		</div>
	</div>

</div>
</form>


<form action='/manage-core-settings/<?=$section;?>' method='POST'>
<input type='hidden' name='update-core-settings' value='header-scripts' />

<div class='container-fluid'>
	<div class='row'>
		<div class='col-sm-11 bold'>Javascript Headers</div>
		<div class='col-sm-1 text-right'>
			<button type='button' class='toggle-info' href='#js-headers' data-toggle='collapse'>?</button>
		</div>
	</div>
	<div class='row'>
		<div class='col-sm-12 editable'>
			<? foreach($default_core_settings['scripts']['head'] as $tag => $val) { ?>
				<div class='row row-edit header-scripts'>
					<div class='col-sm-5'>
						<input type='text' name='setting[name][]' value='<?=$tag;?>' placeholder='Script Title' class='form-control' />
					</div>
					<div class='col-sm-5'>				
						<input type='text' name='setting[val][]' value='<?=$val;?>' placeholder='Script URL' class='form-control' />
					</div>
					<div class='col-sm-2 text-right'>
						<button class='btn btn-default plus-minus fa' data-section='header-scripts'></button>					
					</div>
				</div>
			<? } ?>
		</div>
	</div>

	<div id='js-headers' class='row collapse'>
		<div class='col-sm-12'>
			Javascript Headers are placed in the &#x3C;head&#x3E;&#x3C;/head&#x3E; section of the page.  Place <i>URLs</i> or <i>relative paths</i> to your <b>JS Files</b> here.  <b>Core</b> will automatically load files contained in <b>/bin/[build]/css</b> and <b>/bin/$page/</b> into the Javascript Headers that contain <b>.head.js</b>, <b>.h.js</b> and <b>.head.js.php</b>.  You can add further CDNs here if you wish.
		</div>
	</div>
	
	
	<div class='row'>
		<div class='col-sm-12 text-right'>
			<button type='submit' class='btn btn-primary' alt='Save' title='Save'><span class='fa fa-floppy-o'></span>Save</button>
		</div>
	</div>

</div>
</form>


<form action='/manage-core-settings/<?=$section;?>' method='POST'>
<input type='hidden' name='update-core-settings' value='footer-scripts' />

<div class='container-fluid'>
	<div class='row'>
		<div class='col-sm-11 bold'>Javascript Footers</div>
		<div class='col-sm-1 text-right'>
			<button type='button' class='toggle-info' href='#js-footers' data-toggle='collapse'>?</button>
		</div>
	</div>
	<div class='row'>
		<div class='col-sm-12 editable'>
			<? if($default_core_settings['scripts']['foot']) {
					foreach($default_core_settings['scripts']['foot'] as $tag => $val) { ?>
					<div class='row row-edit footer-scripts'>
						<div class='col-sm-5'>
							<input type='text' name='setting[name][]' value='<?=$tag;?>' placeholder='Script Title' class='form-control' />
						</div>
						<div class='col-sm-5'>				
							<input type='text' name='setting[val][]' value='<?=$val;?>' placeholder='Script URL' class='form-control' />
						</div>
						<div class='col-sm-2 text-right'>
							<button class='btn btn-default plus-minus fa' data-section='footer-scripts'></button>					
						</div>
					</div>
			<? 		}
				} else { ?>
					<div class='row row-edit footer-scripts'>
						<div class='col-sm-5'>
							<input type='text' name='setting[name][]' value='' placeholder='Script Title' class='form-control' />
						</div>
						<div class='col-sm-5'>				
							<input type='text' name='setting[val][]' value='' placeholder='Script URL' class='form-control' />
						</div>
						<div class='col-sm-2 text-right'>
							<button class='btn btn-default plus-minus fa' data-section='footer-scripts'></button>					
						</div>
					</div>
			<?	}	?>
		</div>
	</div>

	<div id='js-footers' class='row collapse'>
		<div class='col-sm-12'>
			Javascript Footers are placed at the end of a page.  Place <i>URLs</i> or <i>relative paths</i> to your <b>JS Files</b> here.  <b>Core</b> will automatically load files contained in <b>/bin/[build]/css</b> and <b>/bin/$page/</b> into the Javascript Headers that contain <b>.foot.js</b>, <b>.foot.js.php</b>, <b>.f.js.php</b> and <b>.js</b>.  You can add further CDNs here if you wish.
		</div>
	</div>
	
	<div class='row'>
		<div class='col-sm-12 text-right'>
			<button type='submit' class='btn btn-primary' alt='Save' title='Save'><span class='fa fa-floppy-o'></span>Save</button>
		</div>
	</div>

</div>
</form>

