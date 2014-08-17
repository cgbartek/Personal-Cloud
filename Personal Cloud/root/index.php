<?php
require_once('config.php');
session_destroy();
$redir = 'remote';
if($_GET['redir']){
	$redir = $_GET['redir'];
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, user-scalable=no">
	<title>Login</title>
	<link href="css/style.css" rel="stylesheet">
</head>
<body>
	<h1>Login</h1>
	<form action="api?a=login&u=<?php echo $_REQUEST['u'];?>&p=<?php echo $_REQUEST['p'];?>" method="post">
		<input type="text" name="username" placeholder="username"><br>
		<input type="password" name="password" placeholder="password"><br>
		<input type="hidden" name="redir" value="<?php echo $redir;?>">
		<input type="submit" value="Login">
	</form>
	
	<script src="js/vendor/jquery-1.10.2.min.js"></script>
	<script>
		$("form").submit(function(e) {
			e.preventDefault();
			$.post($(this).attr('action'), $(this).serialize(),
			function(data) {
				data = $.parseJSON(data);
				if(data.success){
					window.location = data.redir;
				} 
				if(data.error) {
					$('input[name="username"]').val('');
					$('input[name="password"]').val('');
					alert(data.error);
				}
			});
		});
	</script>
	<?php if($_REQUEST['u']){?>
	<script>
	$('h1').text('Logging in...');
	$('form').hide().submit();
	</script>
	<?php }?>
</body>
</html>