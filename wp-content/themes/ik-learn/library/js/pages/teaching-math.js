(function($){
	$(function(){
		$("#i-agree").click(function(){
			$("[name='registered-teacher']").prop("checked", true);
		});

		$("#input-image").change(function(){
			var input = this;
			if(input.files && input.files[0]){
				var reader = new FileReader();

				reader.onload = function(e){
					$("#profile-picture").find("img").attr("src", e.target.result);
				}

				reader.readAsDataURL(input.files[0]);
			}
		});

		$("button.offer-worksheet").click(function(){
			$("#osid").val($(this).attr("data-sid"));
			$("#offer-price").val("");
			$("#offer-worksheet-modal").modal();
		});

		$("button.remove-offer").click(function(){
			$("#rsid").val($(this).attr("data-oid"));
		});

		$("button.worksheet-des").click(function(){
			var tr = $(this).parents("tr");
			$("#hw-desc").html($("#ws-des-" + tr.attr("data-id")).html());
			$("div.purchase-controls").hide();
			var modal = $("#purchase-worksheet-modal");
			var _h = modal.find("h3.modal-title");
			_h.text(_h.attr("data-details-title"));
			modal.modal();
		});

		$("button.purchase-worksheet").click(function(){
			var tr = $(this).parents("tr");
			$("#cid").val($(this).attr("data-oid"));
			$("#hw-desc").html($("#ws-des-" + tr.attr("data-id")).html());
			$("div.purchase-controls").show();
			var modal = $("#purchase-worksheet-modal");
			var _h = modal.find("h3.modal-title");
			_h.text(_h.attr("data-purchase-title"));
			modal.modal();
		});

		$("button.preview-btn").click(function(){
			var tthis = $(this);
			var tr = tthis.parents("tr");
			$("#current-assignment").val(tr.attr("data-assignment"));
			tthis.button("loading");
			$.get(home_url + "/?r=ajax/sheets", {sid: tr.attr("data-id")}, function(data){
				if(data != "0"){
					$("#question-i").text(1);
					data = JSON.parse(data);
					$("#questions-table").html(data.html);
					setup_homework_viewer(tr.attr("data-assignment"));
					if(tr.attr("data-assignment") == "3"){
						$("#reading-passage").html(data.passage);
						$("#passage-block").show();
					}else{
						$("#passage-block").hide();
					}
					$("#homework-detail").text("Grade " + tr.find("td:nth-child(2)").text() + " " + tr.find("td:first-child").text() + ", " + tr.find("td:nth-child(3)").text() + ", Question no.");
					$("#current-row").val(1);
					$("#homework-viewer-modal").modal();
					tthis.button("reset");
				}
			});
		});

		$("#next-btn").click(function(){
			var ni = parseInt($("#current-row").val()) + 1;
			var assignment = $("#current-assignment").val();
			if(assignment == "4"){
				var e = "textarea";
			}else{
				var e = "input";
			}
			var nr = $("#questions-table tr:nth-child(" + ni + ") td:nth-child(1) " + e).val();
			if(typeof nr == "undefined" || nr == ""){
				ni = 1;
			}
			$("#current-row").val(ni);
			$("#question-i").text(ni);
			setup_homework_viewer(assignment);
		});

		$(".accept-request").click(function(){
			var input = $("<input>").attr("type", "hidden").attr("name", "request-id").val($(this).attr("data-request-id"));
			$("#main-form").append($(input));
		});
		$('.check_lb').click(function(e){
			var checked = $(this).find('input').attr('checked');
			if(checked == 'checked'){
				$(this).addClass('checked_lb');
			}
			else{
				$(this).removeClass('checked_lb');
			}
		});
	});
})(jQuery);