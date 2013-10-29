How To Use:

1, Include jQuery and RSS plugin:

	<script type="text/javascript" src="jquery.js"></script>
	<script type="text/javascript" src="jquery.minimefeed.js"></script>

2, Call with default data:

	$('#test').miniFeed('http://example.com/rss/');

3, Call with custom data:

	$('#test').miniFeed('http://example.com/rss/', { limit: 1, getItemDate: true});

3, Call with custom data, and use callback function:

	$('#test').miniFeed('http://example.com/rss/', { limit: 1, getItemDate: true}, function() { console.log(this); });


Read online documentation:
	http://sdnetwork.hu/press/nincs-kategorizalva/rss-and-atom-feeds-reader-plugin-to-jquery/