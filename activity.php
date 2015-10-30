<?php
	include('config.php');
	dbConnect();
	session_start();
	if(!$_SESSION['userId'])
	{
		header("Location: index.php");
	}
	header('X-Frame-Options: GOFORIT');
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
	<script src="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
	<script src="script.js"></script>
	<link href="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
	<link href="http://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="style.css">
	
	<title>Public Playlist</title>

</head>
<body>
	
	<?php include('nav.php');?>
<?php
	if ($_SERVER["REQUEST_METHOD"] == "POST"){
		$response = "";
		if (mysqli_connect_errno()) {
				$response= ("Failed to connect to MySQL");
		}
		$songTitle = mysql_real_escape_string($_POST['song']);
		$artistTitle = mysql_real_escape_string($_POST['artist']);
		$owner = $_SESSION['userId'];
		if($songTitle!=""||$artistTitle!=""){
			$songName= urlencode($songTitle);
			
			$url = 'https://api.spotify.com/v1/search?q=' . $songName . '&type=track';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
			$output = curl_exec($ch);
			curl_close($ch);
			$response = json_decode($output);
			foreach ($response->tracks->items as $track) 
			{	
				if(strcasecmp(mysql_real_escape_string($track->artists[0]->name),$artistTitle)==0&&strcasecmp(mysql_escape_string($track->name),$songTitle)==0){
					$id=$track->uri;
					$isSong=true;
					$songTitle=$track->name;
					$artistTitle=$track->artists[0]->name;
					
				}
			}

			if($isSong){
				$sql="INSERT INTO songs (songId, song, artist, owner, vote, id) VALUES ('null', '$songTitle','$artistTitle', '$owner','null', '$id')";
				if (!mysql_query($sql)) {
						die('Error');
				}
				$response = "added " . $songTitle;
			}
			else{
				$response = $songTitle . " is not on Spotify";
			}
		}
		else{
			$response = "fill all fields";
		}
	}
?>
	<div class="page-header" style="margin:50px 0 10px 0;padding:10px 0 10px 10px;background-color:rgba(255,255,255,.7)">
		<h1>Public Playlist<small> Post your favorite song</small></h1>
		<p style="max-width:500px;">Here, you can add your favorite song to our Public Playlist and vote on songs to see what people enjoy listening to!</p>
	</div>
	<div class="row">
	<div class="col-md-12">
	<div class="panel panel-default"style="max-width:500px;margin-left:10px;margin-right:10px">
  		<div class="panel-heading" style="background-color:#333">
    		<h3 class="panel-title"style="color:#ddd">Tell me your favorite song</h3>
  		</div>
  		<div class="panel-body" style="background-color:#8a8a83">
    		<form id="quizForm" method="post" onsubmit="return validate()" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
				<div class="form-group">
					<input name="song" type="text" class="form-control" placeholder="Title"/>
				</div>
				<div class="form-group">
					<input name="artist" type="text" class="form-control" placeholder="Artist"/>
				</div>
				<input name="submit" type="submit" value="Submit" class="btn btn-default"/>
				<span class="text-success"><?php echo $response;?></span>
			</form>
			
  		</div>
	</div>
	<div class="panel panel-default"style="max-width:500px;margin-left:10px;margin-right:10px">
		<div class="panel-heading" style = "background-color:#333">
    		<h3 class="panel-title"style="margin-bottom:10px;color:#ddd">Current Playlist</h3>
    	</div>
    	<div class="panel-body" style="background-color:#8a8a83;">
		<?php
			$sql = "SELECT songId, song, artist, owner, vote, id FROM songs ORDER BY vote DESC LIMIT 150";
			$result = mysql_query($sql) or die('Query failed');
			$color=true;

			while($row = mysql_fetch_array($result)){ 
				$selectUser = "SELECT userId, first, last FROM users WHERE userId = {$row['owner']}";
				$userResult = mysql_query($selectUser);
				$userRow = mysql_fetch_array($userResult);
				if($color==true){
					echo "<div class=\"panel panel-body\" id=\"list\" style=\"background-color:#d3d1c4;\">";
					$color=false;
				}
				else {
					echo "<div class=\"panel panel-body\" id=\"list\" style=\"background-color:#fff;\">";
					$color=true;
				}
				echo "<div class=\"item, col-xs-2\" data-postid=\"".$row['songId']."\" data-score=\"".$row['vote']."\">";
				echo "<div class=\"vote-span\">";
				echo "<div class=\"vote\" data-action=\"up\" title=\"Vote up\">";
					echo "<i class=\"icon-chevron-up\"></i>";
				echo "</div>";
				echo "<div class=\"vote-score\">".$row['vote']."</div>";
				echo "<div class=\"vote\" data-action=\"down\" title=\"Vote down\">";
					echo "<i class=\"icon-chevron-down\"></i>";
				echo "</div>";
				echo "</div>";
				echo "</div>";


				echo "<div class=\"post, col-xs-3\">";
			    	echo "<iframe src=\"https://embed.spotify.com/?uri=".$row['id']."\" width=\"80\" height=\"80\" frameborder=\"0\" allowtransparency=\"true\"></iframe>";
			    echo "</div>";
			    echo "<div class=\"post, col-xs-7\"style=\"padding-left:19px;\">";
			    echo "<p><strong>{$row['song']} </strong></p>";
			    echo "<p>{$row['artist']}</p>";
			    echo "</div>";
			    echo "</div>";
			}
			
		?>
	</div>
	</div>
	</div>
	<!-- <div class="col-md-6 pull-left">
	<div class="panel panel-default"style="max-width:500px;margin-left:10px;margin-right:10px">
		<div class="panel-heading" style = "background-color:#333">
    		<h3 class="panel-title"style="margin-bottom:10px;color:#ddd">It's A Pretty Big Deal</h3>
    	</div>
    	<div class="panel-body" style="background-color:#8a8a83">
		<a class="twitter-timeline" height="400px"width="200px"data-dnt="true" href="https://twitter.com/publicplaylist" data-widget-id="529504572025233409">Tweets by @publicplaylist</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		</div>
	</div>
	</div> -->
</div>

</body>
</html>





