<div class='container'>
	<div class='row text-center'>
		<div class='img-whitebox'>
			<h1>Error 401: Unauthorized</h1>
			<p>
				We're sorry, you do not have permission to access the page '<b>/<?=$core_files['requested'];?></b>' 
				<br/><br/>
				Please <a href='/'>click here</a> to be taken back to the homepage.
			</p>
		</div>
	</div>
	<div class='row text-center small-print'>
		Website Core v<?=CORE_VERSION;?>, <?=date("H:m, jS M Y",time());?>, if you feel you have reached this page in error, please <a href='/contact'>contact us here.</a>
	</div>
</div>
