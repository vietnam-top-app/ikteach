(function($){
	$(function(){
		$(".select-math-level").click(function(){
			$("#math-sublevel").text($(this).text());
			$("#math-level").text($(this).parents(".math-levels").find("h6").text());
			$("#math-category").text($(".page-title").text());
			$("#start-math-worksheet").attr("href", "#");
			$("#sel-worksheets").html("");
			$.get(home_url + "/?r=ajax/math_worksheet/get", {lid: $(this).attr("data-level")}, function(data){
				var worksheets = JSON.parse(data), _tbl = $("#sel-worksheets");
				
				_tbl.html("");
				if(worksheets.length > 0){
					$.each(worksheets, function(i, v){
						var css = '',
							url = "href='" + home_url + "/" + LANG_CODE + "/?r=math-homework&sid=" + v.sid + "&ref=" + $("#uref").val() + "'",
							SUB = '3';
						if(v.type != SUB || (v.type == SUB && v.is == true)){ v.sub = ''; }
						if(v.type == SUB && v.sub == 'text-muted') { 
							css 			= 'lock';
							url 			= '';
						}
						
						_tbl.append("<tr class='"+ v.sub +"'><td>" + v.name + "</td>" +
							"<td style='width: 125px'><a " + url +" class='btn btn-block btn-tiny orange "+ css +"'><span class='icon-start'></span>Start</a></td></tr>"
						);
					});
				}else{
					_tbl.append("<tr><td>" + _tbl.attr("data-empty-msg") + "</td></tr>");
				}
			});
			$("#select-math-worksheet-dialog").modal();
		});
	});
})(jQuery);