<form action='/manage-core-settings/<?=$section;?>' method='POST'>
<input type='hidden' name='update-core-settings' value='allowed-upload-extensions' />
<div class='container-fluid'>
	<div class='row'>
		<div class='col-sm-4 text-right bold'>Allowed Upload File Types</div>
		<div class='col-sm-8 editable'>
			<? foreach($core_settings['allowed']['upload'] as $val) { ?>
				<div class='row row-edit allowed-upload'>
					<div class='col-sm-10'>				
						<input type='text' name='setting[]' value='<?=$val;?>' placeholder='pdf' class='form-control' />
					</div>
					<div class='col-sm-2 text-right'>
						<button class='btn btn-default plus-minus fa' data-section='allowed-upload'></button>					
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


<form action='/manage-core-settings/<?=$section;?>' method='POST'>
<input type='hidden' name='update-core-settings' value='allowed-download-extensions' />
<div class='container-fluid'>
	<div class='row'>
		<div class='col-sm-4 text-right bold'>Allowed Download File Types</div>
		<div class='col-sm-8 editable'>
			<? foreach($core_settings['allowed']['download'] as $val) { ?>
				<div class='row row-edit allowed-download'>
					<div class='col-sm-10'>				
						<input type='text' name='setting[]' value='<?=$val;?>' placeholder='pdf' class='form-control' />
					</div>
					<div class='col-sm-2 text-right'>
						<button class='btn btn-default plus-minus fa' data-section='allowed-download'></button>					
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
