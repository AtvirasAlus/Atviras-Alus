<?php
include("config.php");
?>

<html>
<head>
	<title>Votes</title>
	
<style type='text/css'>
body {
	background: #e8e6de;
}

a {
outline:none;
}

.entry {
	width: 710px;
	background: #ffffff;
	padding:8px;
	border:1px solid #bbbbbb;
	margin:5px auto;
	-moz-border-radius:8px;
}

span.link a {
	font-size:150%;
	color: #000000;
	text-decoration:none;
}

a.vote_up, a.vote_down {
	display:inline-block;
	background-repeat:none;
	background-position:center;
	height:16px;
	width:16px;
	margin-left:4px;
	text-indent:-900%;
}

a.vote_up {
	background:url("images/thumb_up.png");
}

a.vote_down {
	background:url("images/thumb_down.png");
}
</style>

<script type='text/javascript' src='jquery.pack.js'></script>
<script type='text/javascript'>
$(function(){
	$("a.vote_up").click(function(){
	//get the id
	the_id = $(this).attr('id');
	
	// show the spinner
	$(this).parent().html("<img src='images/spinner.gif'/>");
	
	//fadeout the vote-count 
	$("span#votes_count"+the_id).fadeOut("fast");
	
	//the main ajax request
		$.ajax({
			type: "POST",
			data: "action=vote_up&id="+$(this).attr("id"),
			url: "votes.php",
			success: function(msg)
			{
				$("span#votes_count"+the_id).html(msg);
				//fadein the vote count
				$("span#votes_count"+the_id).fadeIn();
				//remove the spinner
				$("span#vote_buttons"+the_id).remove();
			}
		});
	});
	
	$("a.vote_down").click(function(){
	//get the id
	the_id = $(this).attr('id');
	
	// show the spinner
	$(this).parent().html("<img src='images/spinner.gif'/>");
	
	//the main ajax request
		$.ajax({
			type: "POST",
			data: "action=vote_down&id="+$(this).attr("id"),
			url: "votes.php",
			success: function(msg)
			{
				$("span#votes_count"+the_id).fadeOut();
				$("span#votes_count"+the_id).html(msg);
				$("span#votes_count"+the_id).fadeIn();
				$("span#vote_buttons"+the_id).remove();
			}
		});
	});
});	
</script>

</head>
<body>

<?php
/**
Display the results from the database
**/
$q = "SELECT * FROM entries";
$r = mysql_query($q);

if(mysql_num_rows($r)>0): //table is non-empty
	while($row = mysql_fetch_assoc($r)):
		$net_vote = $row['votes_up'] - $row['votes_down']; //this is the net result of voting up and voting down
?>
<div class='entry'>

	<span class='link'>
		<a href='<?php echo $row['link']; ?>'> <?php echo $row['title']; ?> </a>
	</span>
	
	<span class='votes_count' id='votes_count<?php echo $row['id']; ?>'><?php echo $net_vote." votes"; ?></span>
	
	<span class='vote_buttons' id='vote_buttons<?php echo $row['id']; ?>'>
		<a href='javascript:;' class='vote_up' id='<?php echo $row['id']; ?>'>Vote Up!</a>
		<a href='javascript:;' class='vote_down' id='<?php echo $row['id']; ?>'>Vote Down!</a>
	</span>
	
</div>
<?php
	endwhile;
endif;
?>


</body>
</html>