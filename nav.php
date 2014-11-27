<div class="navbar navbar-fixed-top" role="navigation" style="background-color:#333;border-color:#ddd;">
			<ul class="nav navbar-nav navbar-left ">
			
				<?php 
					// If the user is signed in
					if($_SESSION['userId'])
					{
						echo "<li><a href=\"activity.php\" style=\"color:#ddd;margin-left:10px;\">Home</a></li>";
						echo "<li><a href=\"profile.php\" style=\"color:#ddd;\">Profile</a></li>";
						echo "<li><a href=\"signout.php\" style=\"color:#ddd;\">Sign Out</a></li>";
					} else {
					}
				?>
				
			</ul>
</div>
