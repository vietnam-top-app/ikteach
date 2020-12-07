(function($){
	$(function(){
		$("#join-group").click(function(e){
			$("#join-group-dialog").modal();
		});

		$("#join-group-dialog").on("show.bs.modal", function (e) {
			$("#rdo-yes").prop("checked", true);
		});

		$(".leave-grp-btn").click(function(e){
			var tthis = $(this);
			$("#lev-group-name").text(tthis.attr("data-gname"));
			$("#gid").val(tthis.attr("data-gid"));
			$("#leave-group-dialog").modal();
		});

		$(".request-grading").click(function(){
			$("#grading-cost").text($(this).attr("data-cost"));
			$("#hrid").val($(this).attr("data-hrid"));
			$("#hid").val($(this).attr("data-hid"));
			if(ypoints < parseInt($(this).attr("data-cost"))){
				$("#request-grading-err").html(JS_MESSAGES.point_err);
			}
			$("#request-grading-dialog").modal();
		});

		$(".goto-homework").click(function(e){
			e.preventDefault();
			var html = "";
			if($(this).attr("data-for-practice") == 1){
				html += JS_MESSAGES.practice_inst;
				$("#btn-practice").attr("href", $(this).attr("data-practice-url") + "&ref=" + $("#uref").val());
			}else{
				html += JS_MESSAGES.test_inst;
				$("#btn-practice").attr("href", $(this).attr("data-homework-url") + "&ref=" + $("#uref").val());
			}
			if($("#unfinished_homework").val() == 1 && $(this).attr("data-startnew") == 1){
				html += '<hr><p class="text-warning2">' + JS_MESSAGES.unfinished_homework + '</p>';
			}
			$("#switch-mode-dialog .modal-body").html(html);
			
			$("#switch-mode-dialog").modal("show");
		});
	});		
})(jQuery);