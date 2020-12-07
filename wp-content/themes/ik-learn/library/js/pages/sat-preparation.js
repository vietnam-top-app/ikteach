(function($){
	$(function(){
		$(".class-detail-btn").click(function(e){
			e.preventDefault();
			var modal = $("#class-detail-modal");
			modal.find(".modal-body").html($(this).next().html());
			modal.modal();
		});

		$(".view-score").click(function(e){
			e.preventDefault();
			$("#table-score").find("tbody").html($(this).next().find("tbody").html());
			$("#view-score-modal").modal();
		});

		$(".start-class-btn").click(function(e){
			e.preventDefault();
			if(!annoying){
				$("#jid").val($(this).attr("data-jid"));
				$("#main-form").submit();
			}else{
				var modal = $("#require-modal");
				if(!isuserloggedin){
					modal.find("h3").text(JS_MESSAGES.login_req_h);
					modal.find(".modal-body").html(JS_MESSAGES.login_req_err);
					modal.find(".modal-footer a").html('<span class="icon-issue2"></span> ' + JS_MESSAGES.login_req_lbl);
				}else{
					modal.find("h3").text(JS_MESSAGES.sub_req_h);
					modal.find(".modal-body").html(JS_MESSAGES.sub_req_err);
					modal.find(".modal-footer a").html('<span class="icon-issue2"></span> ' + JS_MESSAGES.sub_req_lbl);
				}
				modal.modal();
			}
		});
	});
})(jQuery);