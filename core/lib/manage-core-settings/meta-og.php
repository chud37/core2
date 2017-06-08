<form action='/manage-core-settings/<?=$section;?>' method='POST'>
<input type='hidden' name='update-core-settings' value='meta-tags' />

<div class='container-fluid'>
	
	<div class='row'>
		<div class='col-sm-12 bold'>Meta Tags</div>
	</div>
	<div class='row'>
		<div class='col-sm-12 editable'>
			<? foreach($core_settings['meta'] as $tag => $val) { ?>
				<div class='row row-edit meta'>
					<div class='col-sm-5'>
						<input type='text' name='setting[name][]' value='<?=$tag;?>' placeholder='Meta tag name' class='form-control' />
					</div>
					<div class='col-sm-5'>				
						<input type='text' name='setting[val][]' value='<?=$val;?>' placeholder='Meta tag value' class='form-control' />
					</div>
					<div class='col-sm-2 text-right'>
						<button class='btn btn-default plus-minus fa' data-section='meta'></button>					
					</div>
				</div>
			<? } ?>
		</div>
	</div>

	<div class='row'>
		<div class='col-sm-12 text-right'>
			<button type='submit' class='btn btn-primary' alt='Save' title='Save'><span class='fa fa-floppy-o'></span>Save Site Settings</button>
		</div>
	</div>


</div>
</form>
