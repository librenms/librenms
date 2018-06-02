<p id="apip">Coucou.</p>
<script>
//$(window).bind("load", function() {
$(function() {
	$('#apip').click(function() {
		$.get("/snmptrapmanager/MIBUploaderAPI.php", {'coucou': 1}, success, "json");
	});

	function success(data) {
		$('#apip').text($('#apip').text() + ' ' + data['coucou']);
	}
});
</script>
