<html>
	<head>
		<title>404 - Kerris Haus</title>
		<link rel='stylesheet' type='text/css' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'/>
		<link rel='stylesheet' type='text/css' href='assets/style.css'/>
		
        <style>
            .center {
              display: flex;
              justify-content: center;
              align-items: center;
              text-align: center;
              height: 100%;
            }
        </style>
	</head>

	<body>
	    <div class="center">
	        <div>
                <h1 style="font-size:72;"><strong>404</strong></h1>
                <p>The requested page does not exist on this server.</p>
                <p>If you believe this to be a mistake, <a href="mailto:support@kerrishaus.com?subject=404 on Portal">contact us</a>.</p>
                <small class='text-muted'><?php echo $_SERVER['REQUEST_URI'] ?></small><br/>
            </div>
        </div>
	</body>
</html>