<form action='/manage-core-settings/<?=$section;?>' method='POST'>
<input type='hidden' name='update-core-settings' value='user-defined-constants' />
<div class='container-fluid'>
	<div class='row'>
		<div class='col-sm-12 bold'>User Defined Constants</div>
	</div>
	<div class='row'>
		<div class='col-sm-12 editable'>
			<? if($core_settings['constants']) {
					foreach($core_settings['constants'] as $key => $val) { ?>
					<div class='row row-edit constants'>
						<div class='col-sm-5'>
							<input type='text' name='setting[name][]' value='<?=$key;?>' placeholder='Constant name' class='form-control' />
						</div>
						<div class='col-sm-5'>				
							<input type='text' name='setting[val][]' value='<?=$val;?>' placeholder='Constant value' class='form-control' />
						</div>
						<div class='col-sm-2 text-right'>
							<button class='btn btn-default plus-minus fa' data-section='constants'></button>					      
						</div>
					</div>
			<? 		}
				} else {
			?>
				<div class='row row-edit constants'>
					<div class='col-sm-5'>
						<input type='text' name='setting[name][]' value='' placeholder='Constant name' class='form-control' />
					</div>
					<div class='col-sm-5'>				
						<input type='text' name='setting[val][]' value='' placeholder='Constant value' class='form-control' />
					</div>
					<div class='col-sm-2 text-right'>
						<button class='btn btn-default plus-minus fa' data-section='constants'></button>					      
					</div>
				</div>
			<? } ?>
		</div>
	</div>
	
	<div class='row'>
		<div class='col-sm-6'>
			<button type='submit' name="wipe-data" class='btn btn-danger' alt='Wipe' title='Wipe'><span class='fa fa-times'></span>Remove All</button>
		</div>
		<div class='col-sm-6 text-right'>
			<button type='submit' class='btn btn-primary' alt='Save' title='Save'><span class='fa fa-floppy-o'></span>Save Site Settings</button>
		</div>
	</div>
	
</div>
</form>
