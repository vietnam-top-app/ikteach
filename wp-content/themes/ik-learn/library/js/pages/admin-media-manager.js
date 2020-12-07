(function($){
	$(function(){
		
		$('#input-file-media').change(function() {
			$("#imported-file-media").val($(this)[0].files.length);
			return false;
		});
		$('#main-folder-media').change(function() {
			get_data_sub($(this).val(), '#sub-folder-media');
		});
		
		$('#s_main_folder').change(function() {
			get_data_sub($(this).val(), '#s_sub_folder');
		});
		function get_data_sub($sub, $sub_media) {
			$.post(home_url + "/?r=ajax/get_sub_dic",{ sub: $sub }, function(data){ 
				$($sub_media).html(data).selectBoxIt("refresh");
			});
		}
		var $selected = $('#s_main_folder option:selected');
		if($selected.val()) {
			get_data_sub($selected.val(), '#s_sub_folder');
		}
		
		var $media_table = jQuery("#omg-media-table");
		if($media_table.has("tr").length == 0) {
			var _html = "<tr><td colspan='3'>No Result</td></tr>";
			$media_table.html(_html);
		}
	});
})(jQuery);