<?php
include('config.php');

// Include SimpleImage library (https://github.com/claviska/SimpleImage)
include('abeautifulsite/SimpleImage.php');

dbConnect();
session_start();

if(!$_SESSION['userId'])
{
	header("Location: index.php");	
}
if(isset($_POST['change'])){
	if($_FILES['file']['tmp_name'])
	{
		if ($_FILES['file']['error'] > 0)
		{
			$error['file'] = 'An error has occurred';
		}
	
		if (($_FILES["file"]["type"] != "image/gif")   &&
			($_FILES["file"]["type"] != "image/jpeg")  &&
			($_FILES["file"]["type"] != "image/pjpeg") &&
			($_FILES["file"]["type"] != "image/png"))
		{
			$error['file'] = 'Invalid file type';
		}
	}
	if($_FILES['file']['tmp_name'])
		{
			// upload photo (https://github.com/claviska/SimpleImage)
			try
			{
				// initialize simpleImage
				$img = new abeautifulsite\SimpleImage($_FILES['file']['tmp_name']);
				
				// create a small photo
				$img->fit_to_width(250)->save('photos/' . $_SESSION['userId'] . '.jpg');
				
				// create a large photo
				$img->fit_to_width(800)->save('photos/large_' . $_SESSION['userId'] . '.jpg');

				//create a thumbnail
				$img->fit_to_width(50)->save('photos/tiny_' . $_SESSION['userId'] . '.jpg');   
			
			} catch(Exception $e) {
				echo 'Error: ' . $e->getMessage();
			}
			header("Location: profile.php?confirmation=profile");
			exit();
		}

}
if(isset($_POST['submit']))
{
	$error = array();

	if(empty($_POST['firstname']))
	{
		$error['firstname'] = 'Required field';
	} else {
		$firstname = mysql_real_escape_string($_POST['firstname']);
	}
	
	// Check for a lastname
	if(empty($_POST['lastname']))
	{
		$error['lastname'] = 'Required field';
	} else {
		$lastname = mysql_real_escape_string($_POST['lastname']);
	}
	
	// Check for a email
	if(empty($_POST['email']))
	{
		$error['email'] = 'Required field';
	} else {
	
		// Check to see if email address is unique
		$email = mysql_real_escape_string($_POST['email']);
		$query = "select userId from users where email = {$email}";
		$result = mysql_query($query);
		$row = mysql_fetch_array($result);
		if(mysql_num_rows($result) > 0)
		{
			// Check to see if this email address is owned by this user
			if($row['userId'] != $_SESSION['userId'])
			{
				$error['email'] = 'This email address is already taken';
			}
		}
	}
	
	
	// if there are no errors
	if(sizeof($error) == 0)
	{
		// edit user information in the users table
		$query = "update users set 
						first = {$firstname}, 
						last = {$lastname},  
						email = {$email}
				 	where
				 		userId = {$_SESSION['userId']}";
		$result = mysql_query($query);
		
		
		
		// Redirect user to profile page (with a confirmation)
		header("Location: profile.php?confirmation=profile");
		exit();
				
	} 

// If the form has not been submitted, get user information so that we can fill in the default form values
} else {

	// Get user information
	$query = "SELECT first, last, email FROM users WHERE userId = {$_SESSION['userId']}";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	
	// Assign user information to template
	$firstname = $row['first'];
	$lastname = $row['last'];
	$email = $row['email'];
	
}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Public Playlist</title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<!-- jQuery -->
		<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>

		<!-- bootstrap -->
		<script src="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
		<link href="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">	
		
		<style type="text/css">
			.profileimage {
				border: 1px solid #ccc; 
				width: 100%;
			}
			#list{
				margin-top: -1px;
				margin-bottom:-1px;
				padding:0;
				padding-top: 10px;
				border-style: solid;
				border-width: 1px;
				border-color:#aaa;
				border-radius:2px;
			}
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
			body{
				background-image: url("crackles.jpg");
			}
		</style>			
	</head>
	<body>
		
		<!-- top navigation -->
		<?php include('nav.php'); ?>
		
		<!-- content -->	
		<div class="container" style="margin-top: 40px;margin-bottom:10px;background-color:rgba(255,255,255,.7);">

			<h2><?php echo "{$_SESSION['first']} {$_SESSION['last']}"; ?></h2>
			<?php
				
				// display a confirmation message if applicable
				if($_GET['confirmation'] == 'profile')
				{
					echo "<div class=\"alert alert-success\">Your profile has been updated</div>";
				}
			
			?>
			
			<!-- bootstrap row -->
			<div class="row"style="margin-bottom:10px;padding-bottom:5px; border-bottom: solid #CCC 1px">
			
				<!-- left column -->
				<div class="col-xs-6"style="max-width:300px;">
				
					<?php
					
						// Check if the user has a profile image on file 
						if(file_exists('photos/' . $_SESSION['userId'] . '.jpg'))
						{
							// Assign time to prevent image caching
							$timestamp = time();
							
							// If the user has a profile image on file, display the user's profile image
							echo "<img src=\"photos/{$_SESSION['userId']}.jpg?time={$timestamp}\" class=\"img-rounded profileimage\"  />";
							
						} else {
						
							// If the user does not have a profile image on file, display a default profile image
							echo "<img src=\"photos/large_noimage.png\" class=\"img-rounded profileimage\" />";
							
						}
					?>
					<form method="post" enctype="multipart/form-data" action="profile.php">
						<div class="form-group">
							<label for="file">Profile Image</label>
							<input id="file" name="file" type="file" />
						</div>
						<p class="text-danger"><?php echo $error['file']; ?></p>
						<input name="change" type="submit" value="Change" class="btn btn-default" />
					</form>
				</div>
				
				<!-- right column -->
				<div class="col-xs-6" style="max-width:600px;">
					
					<!-- edit profile form -->
					<form method="post" enctype="multipart/form-data" action="profile.php">
						
						<!-- first name -->
						<div class="form-group">
							<label>First Name</label>
							<input name="firstname" type="text" value="<?php echo $firstname; ?>" autocomplete="off" class="form-control" />
							<span class="text-danger"><?php echo $error['firstname']; ?></span>
						</div>
						
						<!-- last name -->
						<div class="form-group">
							<label>Last Name</label>
							<input name="lastname" type="text" value="<?php echo $lastname; ?>" autocomplete="off" class="form-control" />
							<span class="text-danger"><?php echo $error['lastname']; ?></span>
						</div>
						
						<!-- email -->
						<div class="form-group">
							<label>Email</label>
							<input name="email" type="text" value="<?php echo $email; ?>" autocomplete="off" class="form-control" />
							<span class="text-danger"><?php echo $error['email']; ?></span>
						</div>
						
						<!-- profile photo -->
						
						
						<!-- submit button -->
						<input name="submit" type="submit" value="Save" class="btn btn-default" />
						
					</form>
	
				</div>
			
			</div>
			<div class="panel panel-default"style="max-width:700px;margin:0 10px 0 10px;">	
				<div class="panel-heading" style = "background-color:#333">
    				<h3 class="panel-title"style="margin-bottom:10px;color:#ddd">Songs Added By You</h3>
    			</div>
    			<div class="panel-body" style="background-color:#8a8a83">
    				<?php
    					$sql = "SELECT songId, song, artist, owner, vote FROM songs WHERE owner ={$_SESSION['userId']} ORDER BY songId DESC";
    					$result = mysql_query($sql) or die('Query failed');
    					$color=true;
    					while ($row = mysql_fetch_array($result)){
	    					if($color==true){
								echo "<div class=\"panel panel-body\" id=\"list\" style=\"background-color:#d3d1c4;\">";
								$color=false;
							}
							else{
								echo "<div class=\"panel panel-body\" id=\"list\" style=\"background-color:#fff;\">";
								$color=true;
							}
							echo "<div class=\"col-xs-12\"style=\"margin-right:0;padding-right:0;\">";
							echo "<p><strong>{$row['song']} </strong>";
							echo "<span class=\"text-muted\">By: </span>{$row['artist']}</p>"; 
							echo "</div>";
							echo "</div>";
						}
					?>


    			</div>


			</div>		
		</div>
		
	</body>
</html>