<html>
	<head>
		<title>IGIP TESTCASE VIEWS</title>
		<script src='{{asset("/js/jquery.js")}}'></script>
		<script src='{{asset("/js/jquery-ui.js")}}'></script>
		@yield('extFile')
		<script>
			$(document).ready(function(){
				@yield('docReady')
			});
		</script>
	</head>

	<body>
		@yield('mainBody')
	</body>
</html>