<form action='/manage-core-settings/<?=$section;?>' method='POST'>
<input type='hidden' name='update-core-settings' value='google-recaptcha-codes' />

<div class='container-fluid'>
	
	<div class='row'>
		<div class='col-sm-11 bold'>Google Recaptcha</div>
		<div class='col-sm-1 text-right'>
			<button type='button' class='toggle-info' href='#developer-ips' data-toggle='collapse'>?</button>
		</div>
	</div>
	<div class='row'>
		<div class='col-sm-2  text-right'>Key</div>
		<div class='col-sm-10'>
			<input type='text' name='setting[key]' value='<?=$core_settings['recaptcha']['key'];?>' placeholder='Google Recaptcha Key' class='form-control' />
		</div>
	</div>
	<div class='row'>
		<div class='col-sm-2  text-right'>Secret</div>
		<div class='col-sm-10'>
			<input type='text' name='setting[secret]' value='<?=$core_settings['recaptcha']['secret'];?>' placeholder='Google Recaptcha Secret' class='form-control' />
		</div>
	</div>

	
	<div id='developer-ips' class='row collapse'>
		<div class='col-sm-offset-2 col-sm-10'>
			<span class='default-setting'>
			The <b>Google Recaptcha</b> program allows you to integrate a recaptcha system quickly and easily into your website.  Recaptcha requires both a <b>key</b> and a <b>secret</b> to be set - copy them over directly from the <a href='https://www.google.com/recaptcha/admin#list' target='_blank'>Google website</a> and save them here.
			<br/><br/>
			To use the Recaptcha functionality on your web page, simple paste the code: <b>&#x3C;?=recaptcha();?&#x3E;</b>.  Then, when you are processing the form, simply test the recaptcha code with the function by passing the string "capture": <b>if(!recaptcha(&#x22;capture&#x22;)) {}</b>.
		</div>
	</div>
	
	<div class='row'>
		<div class='col-sm-offset-2 col-sm-6'>
			<a href='https://www.google.com/recaptcha/admin#list' target='_blank'>Get Codes From Google</a>
		</div>
		<div class='col-sm-4 text-right'>
			<button type='submit' class='btn btn-primary' alt='Save' title='Save'><span class='fa fa-floppy-o'></span>Save Site Settings</button>
		</div>
	</div>


</div>
</form>
