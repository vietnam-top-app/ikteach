(function($){
	$(function(){
		$('.remove_file').click(function(e) {
			$('.show_file').hide();
			$('.btn_upload_file').show();
		});
		$('.remove_image').click(function(e) {
			$('.show_image').hide();
			$('.upload_image').show();
		});
		$('.continute-send-email').click(function(e){
			$('#continute-send-tomorrow-modal').addClass('in');
			$('#continute-send-tomorrow-modal').modal();

		});
		$('.sent-all-email').click(function(e){
			$('#sent-all-email-modal').addClass('in');
			$('#sent-all-email-modal').modal();

		});
		$("#form_email_marketing").submit(function(e) {
            alert('Sending Email');
        });
   });
})(jQuery);