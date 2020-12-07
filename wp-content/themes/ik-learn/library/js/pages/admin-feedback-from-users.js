(function($){
	$(function(){
		$("#filter-email").keypress(function(e){
			if(e.keyCode == 13){
				e.preventDefault();
				$("#search-btn").click();
			}
		});

		$(".view-msg").click(function(){
			$("#message-from").text($(this).attr("data-email"));
			$("#message-subject").text($(this).attr("data-subject"));
			$("#reply-id").val($(this).attr("data-reply-id"));
			$("#reply-subject").val($(this).attr("data-subject"));
			$("#recipient-id").val($(this).attr("data-recipient-id"));
			
			if(tinyMCE.activeEditor){
				var str = "<blockquote>" + $(this).next().html() + "</blockquote><p></p>"; console.log(str.length);
				tinyMCE.get('message').focus();
				tinyMCE.activeEditor.setContent($(this).next().html() + "<p></p>");
			}
			$("#reply-feedback-modal").modal();
		});
		
		$('#send-message-to-all-a').click(function() {
			$('#send-message-to-all-modal').modal('show');
			return false;
		});
		
		$("#send-to-all-send").click(function() {
			$('#send-message-to-all-modal').modal('hide');
			$("#process-message-modal").modal('show');
		});
		
	});
})(jQuery);