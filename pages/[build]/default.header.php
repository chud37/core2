<a name='top'></a>

<div class='container'>
	<div class='row m-b-20'>
		<div class='xs-center col-xs-12 col-sm-8'>
			<h1>
				<a href='/'>
					<span>core:</span>two
				</a>
			</h1>
			version <?=CORE_VERSION;?>
			
		</div>
		<div class='col-sm-4 hidden-xs text-right'>
			<span class='username'><?=$auth->me['name'];?></span>
			<span class='email'><?=$auth->me['email'];?></span>
		</div>
	</div>
</div>



<!--------MAIN MENU-------------->
<div class='menubar'>
	<div class='container'>
		<nav id='myNavBar' class='navbar navbar-default' role='navigation'>
		<div class='navbar-container'>
			
			<div class='navbar-header'>
				<button type='button' class='navbar-toggle' data-toggle='collapse' data-target='#navbarCollapse'>
					<span class='sr-only'>Toggle Navigation</span>
					<span class='icon-bar'></span><span class='icon-bar'></span><span class='icon-bar'></span>
				</button>
				<span class='navbar-brand hidden-sm hidden-md hidden-lg'>Home</span>
			</div>
			
			<div class='collapse navbar-collapse' id='navbarCollapse'>
				<ul class='nav navbar-nav'>
					
					<li class="dropdown">
						<a href="#" data-toggle="dropdown" class="dropdown-toggle">Home<b class="caret"></b></a>
						<ul role="menu" class="dropdown-menu">
							<li><a href="/">Home</a></li>
							<li><a href="/about">About</a></li>
							<li><a href="/contact">Contact</a></li>
							<li class="divider"></li>
							<li><a href="/logout">Logout</a></li>
						</ul>
					</li>
					
				</ul>
			</div>    
		</div>
		</nav>
	</div>
</div>
