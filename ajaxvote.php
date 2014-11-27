<?php
	include('config.php');
	session_start();
	dbConnect();
	if ($_SERVER['HTTP_X_REQUESTED_WITH']) {
		if (isset($_POST['postid']) AND isset($_POST['action'])) {

			$postId = $_POST['postid'];

			# check if already voted, if found voted then return
			if (isset($_SESSION['vote'][$postId])) return;
			# connect mysql db
			

			# query into db table to know current voting score 
			$query = mysql_query("
				SELECT vote
				from songs
				WHERE songId = '{$postId}'
				LIMIT 1" );
			# increase or dicrease voting score
			if($data = mysql_fetch_array($query)) {
				if ($_POST['action'] === 'up'){
					$vote = ++$data['vote'];
				} else {
					$vote = --$data['vote'];
				}
				# update new voting score
				if($vote<-3){
					$update = mysql_query("
					DELETE FROM songs WHERE songId = '{$postId}'
					");
				}
				else{
				$update = mysql_query("
					UPDATE songs
					SET vote = '{$vote}'
					WHERE songId = '{$postId}' ");
			  
				# set session with post id as true
				$_SESSION['vote'][$postId] = true;
				# close db connection
				}
			}
		}
	}
	
?>