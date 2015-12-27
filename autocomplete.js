var MIN_LENGTH = 1;
var test;
$( document ).ready(function() {
	$("#keyword").keyup(function() {
		var keyword = $("#keyword").val();
		if (keyword.length >= MIN_LENGTH) {

			$.get( "autocomplete.php", { keyword: keyword } )
			//$.get( "getData.php", { keyword: keyword } )
			.done(function( data ) {
				$('#results').html('');
				var results = jQuery.parseJSON(data);
				test = results;
				$(results).each(function(key, value) {
					$('#results').append('<div class="item" onclick="plotStock(this.innerHTML)">' + value + '</div>');
				})

			    $('.item').click(function() {
			    	var text = $(this).html();
			    	$('#keyword').val(text);
			    })

			});
		} else {
			$('#results').html('');
		}
	});

    $("#keyword")
    	.blur(function(){
    		$("#results").fadeOut(500);
    	})
        .focus(function() {		
    	    $("#results").show();
    	});
});