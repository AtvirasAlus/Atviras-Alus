<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
	<meta name="author" content="Settenke" />
	<style>
		/* demo page style */
		table {
			width: 90%;
			margin: auto;
		}
		table td {
			font-family: Arial;
			color: #282828;
			padding: 2px;
			border: 1px solid #EAEAEA;
		}
		.tabletitle td {
			text-align: center;
			font-size: 18px;
			font-weight: bold;
			background-color: #E1E1E1;
		}
		button {
			border: 1px solid #909090;
			background: #E5E5E5;
			color: #282828;
			font-weight: bold;
			padding-left: 20px;
			padding-right: 20px;
			padding-top: 4px;
			padding-bottom: 4px;
		}
		.bl {
			color: blue;
			margin-left: 15px;
		}
		.rd {
			color: red;
		}
		.bk {
			color: black;
			font-weight: bold;
		}
		.gr {
			color: green;
		}
		.tab {
			margin-left: 15px;
		}
		.lb {
			color: #0080C0;
		}
		.lg {
			color: #008040;
		}
		.yw {
			color: #FF8040;
		}
		/* feed style */
		.test_div {
			font-family: Arial;
			color: #282828;
			margin: 20px;
			width: 300px;
			font-size: 14px;
		}
		.test_div h4 {
			margin: 0px;
			padding: 0px;
			font-size: 18px;
		}
		.minimeFeed {
			margin: 0px;
			padding: 0px;
			margin-top: 5px;			
		}
		.minimeFeed li {
			cursor: default;
			list-style: none;
			margin-top: 5px;
			padding: 5px;
			border: 1px solid #D7D7D7;
		}
		.minimeFeed li:hover {
			cursor: default;
			background-color: #EBEBEB;
			border: 1px solid #949494;
		}
		.minimeFeedDate {
			font-size: 12px;
			margin: 3px;
		}
		.minimeFeed a, a:visited {
			color: #006C36;
			text-decoration: none;
			font-style: italic;
		}
		.minimeFeed a:hover {
			font-style: normal;
			text-decoration: underline;
		}
		.minimeFeed a:active {
			color: #00AE57;			
		}
	</style>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script> 
	<script src="jquery.minimefeed.js"></script> 

	<title>Demo feed</title>
</head>
<body>
	<table>
		
		<tr>
			<td rowspan="11" valign="top">
				<div id="test1" class="test_div"></div>
			</td>
			<td rowspan="11" valign="top">
				<div id="test2" class="test_div"></div>
			</td>
			</tr>
	</table>
	
	
	<script>
	$(document).ready(function(){
		// load default feed
		$('#test1').miniFeed('http://pipes.yahoo.com/pipes/pipe.run?_id=273dd8f7a41beeb30a6ab04526d127da&_render=rss');
		
		
	});
	
	
	</script>
</body>
</html>