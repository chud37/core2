<form action='/manage-core-settings/<?=$section;?>' method='POST'>
<input type='hidden' name='update-core-settings' value='website-settings' />
                                                                
<div class='container-fluid'>
	<div class='row'>
		<div class='col-sm-4 text-right bold'>Site Name<button type='button' class='toggle-info' href='#site-name' data-toggle='collapse'>?</button></div>
		<div class='col-sm-8'>
			<input type='text' name='setting[site_name]' value='<?=$core_settings['site_name'];?>' placeholder='My Website' class='form-control' />
		</div>
	</div>
	<div id='site-name' class='row collapse'>
		<div class='col-sm-offset-4 col-sm-8'>
			<span class='default-setting'>Default Setting: <b>v<?=CORE_VERSION;?></b></span>
			The &lt;title&gt; tag is populated with the <i>$page</i> variable, then <i>site_name</i>.  If left blank, the /home page would be <b>Home - v<?=CORE_VERSION;?></b>.  Change to brand your website. 	
		</div>
	</div>
	
	<div class='row'>
		<div class='col-sm-4 text-right bold'>Home Page<button type='button' class='toggle-info' href='#home-page' data-toggle='collapse'>?</button></div>
		<div class='col-sm-8'>
			<input type='text' name='setting[home]' value='<?=$core_settings['home'];?>' placeholder='home' class='form-control' />
		</div>
	</div>
	<div id='home-page' class='row collapse'>
		<div class='col-sm-offset-4 col-sm-8'>
			<span class='default-setting'>Default Setting: <b>/home</b></span>
			The homepage for the website.  This is the page that is loaded if the user simples browses to <i><?=$_SERVER['SERVER_NAME'];?>.</i>	
		</div>
	</div>

	
	
	<div class='row'>
		<div class='col-sm-4 text-right bold'>Login Page<button type='button' class='toggle-info' href='#login' data-toggle='collapse'>?</button></div>
		<div class='col-sm-8'>
			<input type='text' name='setting[login]' value='<?=$core_settings['login'];?>' placeholder='login' class='form-control' />
		</div>
	</div>
	<div id='login' class='row collapse'>
		<div class='col-sm-offset-4 col-sm-8'>
			<span class='default-setting'>Default Setting: <b>/login</b></span>
			The page where the login functionality is located.  The user will be redirected here if authentication fails.
		</div>
	</div>
	
	
	<div class='row'>
		<div class='col-sm-4 text-right bold'>FavIcon<button type='button' class='toggle-info' href='#favicon' data-toggle='collapse'>?</button></div>
		<div class='col-sm-8'>
			<input type='text' name='setting[favicon]' value='<?=$core_settings['favicon'];?>' placeholder='/favicon.ico' class='form-control' />
		</div>
	</div>
	<div id='favicon' class='row collapse'>
		<div class='col-sm-offset-4 col-sm-8'>
			<span class='default-setting'>Default Setting: <b>/favicon.ico</b></span>
			You can modify the path to the favicon here.  However this will not stop bots and other websites referencing favicon.ico in the root.
		</div>
	</div>
	
	
	<div class='row'>
		<div class='col-sm-4 text-right bold'>Error Email Address<button type='button' class='toggle-info' href='#error-email' data-toggle='collapse'>?</button></div>
		<div class='col-sm-8'>
			<input type='text' name='setting[error_email]' value='<?=$core_settings['error_email'];?>' placeholder='john@hotmail.com' class='form-control' />
		</div>
	</div>
	<div id='error-email' class='row collapse'>
		<div class='col-sm-offset-4 col-sm-8'>
			<span class='default-setting'>Default Setting: <b>blank</b></span>
			If <i>error_email</i> is <b>populated</b>, any errors encountered will be notified via an email message when and where they occur.  Leave blank to turn off this functionality.
		</div>
	</div>

	
	<div class='row'>
		<div class='col-sm-4 text-right bold'>Under Maintenance<button type='button' class='toggle-info' href='#under-maintenance' data-toggle='collapse'>?</button></div>
		<div class='col-sm-8'>
			<select name='setting[under-maintenance]' class='form-control' />
				<option value='0' <?=selected($core_settings['under-maintenance'], "0");?>>No</option>
				<option value='1' <?=selected($core_settings['under-maintenance'], "1");?>>Yes</option>
			</select>
		</div>
	</div>
	<div id='under-maintenance' class='row collapse'>
		<div class='col-sm-offset-4 col-sm-8'>
			<span class='default-setting'>Default Setting: <b>Off</b></span>
			If <b>Under Maintenance</b> is <i>On</i> the entire site is shut down to the outside world and only IPs within the Developer IP list can visit the site.  The <i>503 error page</i> (found in /lib/error-pages/) is displayed to the user instead. 
		</div>
	</div>
	
	
	<div class='row'>
		<div class='col-sm-4 text-right bold'>Wrappers?<button type='button' class='toggle-info' href='#wrappers' data-toggle='collapse'>?</button></div>
		<div class='col-sm-8'>
			<select name='setting[wrappers]' class='form-control' />
				<option value='0' <?=selected($core_settings['wrappers'], "0");?>>Off</option>
				<option value='1' <?=selected($core_settings['wrappers'], "1");?>>On</option>
			</select>
		</div>
	</div>
	<div id='wrappers' class='row collapse'>
		<div class='col-sm-offset-4 col-sm-8'>
			<span class='default-setting'>Default Setting: <b>On</b></span>
			If <i>wrappers</i> is set to <b>On</b>, the website is displayed with HTML5 Semantic wrappers such as &lt;header&gt;&lt;/header&gt;, &lt;main&gt;&lt;/main&gt; and &lt;footer&gt;&lt;/footer&gt;.
			<br/>
			If <i>wrappers</i> is set to <b>Off</b>, the various elements of the page will be rendered but without the semantic tags that would usually wrap them.
		</div>
	</div>
	
	
	<div class='row'>
		<div class='col-sm-4 text-right bold'>Admin Access?<button type='button' class='toggle-info' href='#admin-access' data-toggle='collapse'>?</button></div>
		<div class='col-sm-8'>
			<select name='setting[admin_access]' class='form-control' />
				<option value='ip' <?=selected($core_settings['admin_access'], "ip");?>>By Developer IP</option>
				<option value='login' <?=selected($core_settings['admin_access'], "login");?>>By Administrator Login</option>
				<option value='both' <?=selected($core_settings['admin_access'], "both");?>>By Administrator Login &amp; Developer IP</option>
			</select>
		</div>
	</div>
	<div id='admin-access' class='row collapse'>
		<div class='col-sm-offset-4 col-sm-8'>
			<span class='default-setting'>Default Setting: <b>Login</b></span>
			This determines how you access this page, <b>/manage-core-settings</b>.  There are two options:
			<ul>
				<li>By Developer IP<br/>You can only access this area of the website if you are accessing it from an authorised Developer IP (set below).</li>
				<li>By Administrator Login<br/>You will only be allowed to access this area of the website if you are an authorised Administrator user (with a login level of 100).</li>    
			</ul>	
		</div>
	</div>
	
	<div class='row'>
		<div class='col-sm-4 text-right bold'>Standalone Login Forms?<button type='button' class='toggle-info' href='#login_standalone' data-toggle='collapse'>?</button></div>
		<div class='col-sm-8'>
			<select name='setting[login_standalone]' class='form-control' />
				<option value='0' <?=selected($core_settings['login_standalone'], "ip");?>>Off</option>
				<option value='1' <?=selected($core_settings['login_standalone'], "login");?>>On</option>
			</select>
		</div>
	</div>
	<div id='login_standalone' class='row collapse'>
		<div class='col-sm-offset-4 col-sm-8'>
			<span class='default-setting'>Default Setting: <b>True</b></span>
			If this setting is <b>On</b>, The forms for the <a href='/login' target='_blank'>/login</a> page, the <a href='/login/create-account' target='_blank'>/login/create_account</a> page and the <a href='/login/forgot-password' target='_blank'>/login/forgot-password</a> page will be displayed <b>without</b> the header and the footer for the site.  If set to <b>Off</b>, the authentication form <b>is not</b> standalone, and therefore the headers and footers will be displayed.
		</div>
	</div>
	
	<div class='row'>
		<div class='col-sm-4 text-right bold'>Site Version<button type='button' class='toggle-info' href='#site_version' data-toggle='collapse'>?</button></div>
		<div class='col-sm-8'>
			<input type='text' name='setting[site_version]' value='<?=$core_settings['site_version'];?>' placeholder='1.0' class='form-control' />
		</div>
	</div>
	<div id='site_version' class='row collapse'>
		<div class='col-sm-offset-4 col-sm-8'>
			<span class='default-setting'>Default Setting: <b>Blank</b></span>
			This will apply the site version number to the end of the style sheets and javascript includes, so when you update your site you it should load the new version instead of the cache.
		</div>
	</div>
	
	
	
	<div class='row'>
		<div class='col-sm-12 text-right'>
			<button type='submit' class='btn btn-primary' alt='Save' title='Save'><span class='fa fa-floppy-o'></span>Save Site Settings</button>
		</div>
	</div>

</div>
</form>


<form action='/manage-core-settings/<?=$section;?>' method='POST'>
<input type='hidden' name='update-core-settings' value='developer-IPs' />
                                                                
<div class='container-fluid'>
	<div class='row'>
		<div class='col-sm-4 text-right bold'>Developer IPs<button type='button' class='toggle-info' href='#developer-ips' data-toggle='collapse'>?</button></div>
		<div class='col-sm-8 editable'>
			<? if($core_settings['developerIP']) {
				   foreach($core_settings['developerIP'] as $ip) { ?>
					<div class='row row-edit developerIP'>
						<div class='col-sm-10'>				
							<input type='text' name='setting[]' value='<?=$ip;?>' placeholder='IP' class='form-control' />
						</div>
						<div class='col-sm-2 text-right'>
							<button class='btn btn-default plus-minus fa' data-section='developerIP'></button>					
						</div>
					</div>
				<? 	}
				} else { ?>
					<div class='row row-edit developerIP'>
						<div class='col-sm-10'>				
							<input type='text' name='setting[]' value='' placeholder='IP' class='form-control' />
						</div>
						<div class='col-sm-2 text-right'>
							<button class='btn btn-default plus-minus fa fa-plus' data-section='developerIP'></button>					
						</div>
					</div>
			<?	}	?>
		</div>
	</div>
	
	<div id='developer-ips' class='row collapse'>
		<div class='col-sm-offset-4 col-sm-8'>
			<span class='default-setting'>
			<b>Developer IPs</b> allow you program a certain level of security into your website.  For instance, you can only access <i>/manage-core-settings</i> because your IP is added here.  Furthermore, you can utilize the <b>isDev();</b> function within <i>PHP</i>, which will return <b><i>true</i></b> or <b><i>false</i></b> depending on not whether the users IP is found in this list.
		</div>
	</div>
	
	<div class='row'>
		<div class='col-sm-offset-4 col-sm-3'>
			<button type='submit' name="wipe-data" class='btn btn-danger' alt='Wipe' title='Wipe'><span class='fa fa-times'></span>Remove All</button>
		</div>
		<div class='col-sm-5 text-right'>
			<button type='submit' class='btn btn-primary' alt='Save' title='Save'><span class='fa fa-floppy-o'></span>Save</button>
		</div>
	</div>
	
</div>