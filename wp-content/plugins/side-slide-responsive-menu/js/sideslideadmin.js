jQuery(document).ready(function($) {

	$("#ssMenuID").on('change', function() {
		if ($(this).val() == '-1') {
			$("#ssmiconWrapper").html('<strong>First select menu to use from the list above.</strong>');
			return;
		}

		$("#ssmiconWrapper").css('opacity', '0.3');
		//alert('oce li');
		$.post(
		    ajaxurl, 
		    {
		        'action': 'ssm_load_icons',
		        'data':   $(this).val()
		    }, 
		    function(response){
		        var result = JSON.parse(response);
		        var menuitems = result[0];
		        var icons = result[1];
		        $("#ssmiconWrapper").html('');
		        var newHtml = '';

		        var icn;
		        for (var i = 0; i < menuitems.length; i++) {
		        	icn = '';
		        	if (typeof icons[i] != 'undefined')
		        		icn = icons[i];
		        	newHtml += '<input placeholder="Font Awesome code..." style="width: 150px !important;" type="text" class="regular-text ssmIconField" value="'+icn+'"> <i class="fa '+icn+'"></i> <span>'+menuitems[i]['title']+'</span><br/>';
		        }
		        $("#ssmAllIconsString").val(icons.join());
		        $("#ssmiconWrapper").html(newHtml);
		        $("#ssmiconWrapper").css('opacity', '1');
		        changeIconInput();
		    }
		);		
	});


	$('#ssmfont_family').on('change', function() {
		var vars = $('option:selected', this).attr('data-variants');
		var variants = vars.split('|');
		
		$('#ssmfont_style').empty();
		for (var i = 0; i < variants.length; i++)
			$('#ssmfont_style').append('<option value="' + variants[i] + '">' + variants[i] + '</option>');
	});

	function changeIconInput() {
		var allicons = new Array();
		$(".ssmIconField").on('change', function() {
			$(this).next().attr('class', 'fa ' + $(this).val());

			var cnt = 0;
			$(".ssmIconField").each(function() {
				allicons[cnt] = $(this).val();

				cnt++;
			});

			$("#ssmAllIconsString").val(allicons.join());
		});
	};
	changeIconInput();

});