<?php
	include('config.php');
	$con = mysqli_connect(HOST,USER,PASSWORD,DATABASE) OR die('Cannot connect to databases');
	session_start();
	if($_SESSION['userId'])
	{
		header("Location: activity.php");
	}

	$response = "";
	$rEmail="";
	$rFirst="";
	$rLast="";
	$rPass="";
	$rConf="";
	$rEmailin="";
	$rPassin="";
	$responsein="";
	if($_SERVER['REQUEST_METHOD']=='POST'){
		if (isset($_POST['signup'])){	
			$emailadd = mysqli_real_escape_string($con,$_POST['email']);
			$firstname = mysqli_real_escape_string($con,$_POST['first']);
			$lastname = mysqli_real_escape_string($con,$_POST['last']);
			$password = mysqli_real_escape_string($con,$_POST['pass']);
			$conf = mysqli_real_escape_string($con,$_POST['confirm']);
			if(empty($_POST['email'])||empty($_POST['first'])||empty($_POST['last'])||empty($_POST['pass'])||empty($_POST['confirm'])){
				$response = "Fill in all fields.";
			}
			else{
				$response="";
				if($conf==$password){
					$sql = "SELECT userId FROM users WHERE email = '{$_POST['email']}'";
					$result = mysqli_query($con, $sql);
					if(mysqli_num_rows($result) > 0)
					{
						$response = 'You already have an account';
					}
					else{
						$rEmail="";
						$rConf="";
						$add = "INSERT INTO users (userId,first,last,email,pass) VALUES (null,'$firstname','$lastname','$emailadd', sha1('$password'))";
						
						$result= mysqli_query($con,$add);

						$id = mysqli_insert_id($con);

						$_SESSION['userId']=$id;
						$_SESSION['firstname']= $_POST['first'];
						$_SESSION['lastname']= $_POST['last'];
						

						header("Location: activity.php");
						exit();
						
					} 
					
				}
				else{
					$rConf="Password and confirmation do not match";
				}
			}
			
		}
		if(isset($_POST['signin'])){
			if(empty($_POST['emailin'])||empty($_POST['passin'])){
				$responsein="fill in all fields";
			}
			else{
				$responsein="";
				$emailsafe = mysqli_real_escape_string($con,$_POST['emailin']);
				$result = mysqli_query($con,"SELECT userId, first, last FROM users WHERE email='$emailsafe' AND pass = sha1('{$_POST['passin']}') LIMIT 1");
				
				$row = mysqli_fetch_assoc($result);

				
				
				if(!$row['userId']){
					$responsein="Invalid email and/or password";
				}
				else{
					$_SESSION['userId'] = $row['userId'];
					$_SESSION['first'] = $row['first'];
					$_SESSION['last'] = $row['last'];
			
					// redirect user to profile page
					header("Location: activity.php");
					exit();
				}
			}
		}
	}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
	<link href="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
	<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
	<link rel="stylesheet" type="text/css" href="style.css">
	<title>Public Playlist</title>
	<style>
		.navbar-collapse.collapse {
			display: block!important;
		}

		.navbar-nav>li, .navbar-nav {
			float: left !important;
		}

		.navbar-nav.navbar-right:last-child {
			margin-right: -15px !important;
		}

		.navbar-right {
			float: right!important;
		}
		
	</style>
</head>
<body>
	<div class="page-header" style="margin:0 0 10px 0; padding:10px 0 10px 10px;background-color:rgba(255,255,255,.7)">
		<h1>Public Playlist<small> Post your favorite song</small></h1>
		<p style="max-width:500px;">Here, you can add your favorite song to our Public Playlist and vote on songs to see what people enjoy listening to!</p>
	</div>
	<div class="panel panel-default"style="max-width:500px;margin-left:10px;margin-right:10px">
  		<div class="panel-heading"style="background-color:#333">
    		<h3 class="panel-title"style="color:#ddd">Sign In Here!</h3>
  		</div>
  		<div class="panel-body" style="background-color:#8a8a83" >
    		<form id="signinForm" method="post" onsubmit="return validate()" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
				<div class="form-group">
					<input name="emailin" type="text" class="form-control" placeholder="Email Address" />
					<span class="text-success"><?php echo $rEmail;?></span>
				</div>
				<div class="form-group">
					<input name="passin" type="password" class="form-control" placeholder="Password" />
					<span class="text-success"><?php echo $rPass;?></span>	
				</div>

				<input name="signin" type="submit" value="submit" class="btn btn-default") />
			</form>
			<span class="text-success"><?php echo $responsein;?></span>
  		</div>
	</div>
	<div class="panel panel-default"style="max-width:500px;margin-left:10px;margin-right:10px">
  		<div class="panel-heading" style="background-color:#333 ">
    		<h3 class="panel-title" style="color:#ddd">Sign up to start contributing to the playlist!</h3>
  		</div>
  		<div class="panel-body" style= "background-color:#8a8a83" >
    		<form id="signupForm" method="post" onsubmit="return validate()" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
				<div class="form-group">
					<input name="email" type="text" class="form-control" placeholder="Email Address" />
					<span class="text-success"><?php echo $rEmail;?></span>
				</div>
				<div class="form-group"> 
					<input name="first" type="text" class="form-control" placeholder="First Name"/>
					<span class="text-success"><?php echo $rFirst;?></span>
				</div>
				<div class="form-group">
					<input name="last" type="text" class="form-control" placeholder="Last Name"/>
					<span class="text-success"><?php echo $rLast;?></span>	
				</div>
				<div class="form-group">
					<input name="pass" type="password" class="form-control" placeholder="Password"/>
					<span class="text-success"><?php echo $rPass;?></span>	
				</div>
				<div class="form-group">
					<input name="confirm" type="password" class="form-control" placeholder="Confirm Password"/>
					<span class="text-success"><?php echo $rConf;?></span>	
				</div>

				<input name="signup" type="submit" value="submit" class="btn btn-default") />
			</form>
			<span class="text-success"><?php echo $response;?></span>
  		</div>
	</div>




</body>
