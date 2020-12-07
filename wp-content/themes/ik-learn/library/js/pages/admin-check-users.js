(function($){
	$(function(){
	
		/*bank - $("#confirm-modal .modal-footer > .row").html('<div class="col-md-6"><a href="#" data-dismiss="modal" id="btnConfirm" class="btn btn-block orange confirm"><span class="icon-accept"></span>Yes</a></div><div class="col-md-6"><a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-block grey secondary"><span class="icon-cancel"></span>No</a></div>');*/
		$("#toggle-active").click(function(){
			var check_count = $('[name="cid[]"]:checked').length;
			if(check_count == 0) {
				$("#confirm-modal .modal-body").html("You must select a User first.");
				$("#confirm-modal .modal-footer > .row").html('<div class="col-md-12"><a href="#" data-dismiss="modal" class="btn btn-block grey"><span class="icon-cancel"></span>Back</a></div>');
			}else{
				$("#task").val("toggle-active");
				$("#confirm-modal .modal-body").html("You are about to Active/Deactive " + check_count + " Users.<br>Do you want to process?");
				$("#confirm-modal .modal-footer > .row").html('<div class="col-md-6"><button type="submit" form="main-form" id="btnConfirm" class="btn btn-block orange confirm"><span class="icon-accept"></span>Yes</button></div><div class="col-md-6"><a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-block grey secondary"><span class="icon-cancel"></span>No</a></div>');
			}
			$("#confirm-modal").modal();
		});
		
		$("#opt_email_list").click(function(){
			
			var check_count = $('[name="cid[]"]:checked').length;
			if(check_count == 0) {
				$("#confirm-modal .modal-body").html("You must select a Email first.");
				$("#confirm-modal .modal-footer > .row").html('<div class="col-md-12"><a href="#" data-dismiss="modal" class="btn btn-block grey"><span class="icon-cancel"></span>Back</a></div>');
			}else{
				$("#task").val("opt_email_list");
				$("#confirm-modal .modal-body").html("You are about to Output " + check_count + " Emails.<br>Do you want to process?");
				$("#confirm-modal .modal-footer > .row").html('<div class="col-md-6"><button type="submit" form="main-form" id="btnConfirm" class="btn btn-block orange confirm"><span class="icon-accept"></span>Yes</button></div><div class="col-md-6"><a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-block grey secondary"><span class="icon-cancel"></span>No</a></div>');
			}
			
			$("#confirm-modal").modal();
		});
		
		$("#remove").click(function(){
			var check_count = $('[name="cid[]"]:checked').length;
			if(check_count == 0) {
				$("#confirm-modal .modal-body").html("You must select a User first.");
				$("#confirm-modal .modal-footer > .row").html('<div class="col-md-12"><a href="#" data-dismiss="modal" class="btn btn-block grey"><span class="icon-cancel"></span>Back</a></div>');
			}else{
				$("#task").val("remove");
				$("#confirm-modal .modal-body").html("You are about to Remove " + check_count + " Users.<br>Do you want to process?");
				$("#confirm-modal .modal-footer > .row").html('<div class="col-md-6"><button type="submit" form="main-form" id="btnConfirm" class="btn btn-block orange confirm"><span class="icon-accept"></span>Yes</a></div><div class="col-md-6"><a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-block grey secondary"><span class="icon-cancel"></span>No</a></div>');
			}
			$("#confirm-modal").modal();
		});
		
		$("#search").on('click',function() {
			$("#task").val("");
		});
		/*
		$("body").on("click", "#btnConfirm", function(){
			$("#main-form").submit();
		});
		*/
	});
})(jQuery);