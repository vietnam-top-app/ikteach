(function($){
	$(function(){
		get_sel_groups_options($("#sel-group-types").val());

		$("#sel-group-types").change(function(){
			get_sel_groups_options($(this).val());
		});

		function get_sel_groups_options(id){
			var ops = '<option value="">Select group</option>' + $("#class-group" + id).html();
			if(typeof $("#sel-group").html(ops).data("selectBox-selectBoxIt") !== "undefined")
				$("#sel-group").html(ops).data("selectBox-selectBoxIt").refresh();
		}

		if($("#assignments").val() == ASSIGNMENT_VOCAB_BUILDER){
			$("#vocab-block-1").hide();
			$("#vocab-block-2").show();
		}

		$("#assignments").change(function(){
			var _id = $(this).val();
			if(_id == ASSIGNMENT_VOCAB_BUILDER){
				$("#vocab-block-1").slideUp();
				$("#vocab-block-2").slideDown();
			}else{
				$("#vocab-block-2").slideUp();
				$("#vocab-block-1").slideDown();
			}
		});

		$("#create-homework").click(function(e){
			if($("#assignments").val() == ASSIGNMENT_VOCAB_BUILDER){
				e.preventDefault();
				$("#comments-box-modal").modal();
			}
		});

		$("#new-group-btn").click(function(){
//			$("#assign-homework-modal").modal("hide")
//				.one("hidden.bs.modal", function(){
//					$("#create-group-modal").modal()
//						.one("hidden.bs.modal", function(){
//							$("#assign-homework-modal").modal();
//						});
//				});
                    $('#assign-homework-modal').modal("hide");
                    $('#create-group-modal').modal("show");
		});

		$("#input-file").change(function(e){
			var ext = $("#input-file").val().split(".").pop().toLowerCase();
			if($.inArray(ext, ["csv"]) == -1){
				alert('Please Upload CSV');
				return false;
			}
			$("#imported-file").val($(this).val());
			if (e.target.files != undefined && typeof FileReader !== "undefined"){
				var reader = new FileReader();
				var csvRow,csvCol;
				reader.onload = function(e){							
					var csv = $.csv.toArrays(e.target.result);
					for(var i=0;i<csv.length;i++){				
						for(var j=0;j<csv[i].length;j++){
							var $td = $("#sheet > tbody > tr:nth-child(" + (i+1) + ") > td:nth-child(" + (j+2) + ")");
							$td.find("input, textarea").val(csv[i][j]);
							$td.find(".sentence_popover").attr("title", csv[i][j]);
							$td.find(".quiz_popover").attr("title", csv[i][j]);
						}
					}
				};
				reader.readAsText(e.target.files.item(0));
			}
			$("#clear-btn").hide();
			return false;
		});

		$(".icon-availability").popover({
			title: "What is CSV file?",
			content: function(){
				var html = "When you create a file using Excel, you can save it as .CSV instead of .XLS or .XLSX";
				return html;
			},
			html: true,
			trigger: "hover",
			container: "body"				
		}).on("show.bs.popover", function(){ $(this).data("bs.popover").tip().css("max-width", 350)});

		$("#check-word").click(function(){
			if($("#sel-dictionary").val() == ""){alert("Please select a Dictionary!");return false;}
			var val = [];var err;val[0] = [];val[1] = [];
			var tthis = $(this);

			$("#sheet > tbody > tr").each(function(index, element){
				var input = $(element).find("td:nth-child(2) > input");
				val[0].push(input.val().trim().replace(/\s{2,}/g, ' '));
				input.removeClass("word-error");
				input.removeClass("word-sound-error");
				input.attr("title", "");
			});

			if(err==true){return false;}
			$(".loading-overlay").show();
			tthis.button("loading");
			$(".scroll-list").mCustomScrollbar("disable", true);
			$.getJSON(
				home_url + "/?r=ajax/checkword",
				{w: JSON.stringify(val), dict: $("#sel-dictionary").val()},
				function(data){
					if((data[0].length == 0 && data[1].length == 0) || (data[0].length == 0 && ($("#assignments").val() == ASSIGNMENT_VOCAB_GRAMMAR || $("#assignments").val() == ASSIGNMENT_VOCAB_BUILDER))){
						$("#wordchecked").val(1);
						alert("All words are correct");
						if($("#spelling").is(':checked')) return;
					}
					$.each(data[1], function(i, v){
						$("#sheet > tbody > tr").each(function(index, element){
							var input = $(element).find("td:nth-child(2) > input");
							if(input.val() != "" && input.val().trim().replace(/\s{2,}/g, ' ') == v){
								input.addClass("word-sound-error");
								input.attr("title", "No sound file available");
							}
						});
					});
					$.each(data[0], function(i, v){
						$("#sheet > tbody > tr").each(function(index, element){
							var input = $(element).find("td:nth-child(2) > input");
							if(input.val() != "" && input.val().trim().replace(/\s{2,}/g, ' ') == v){
								input.addClass("word-error");
								input.attr("title", "Word not found in Dictionary");
							}
						});
					});
					tthis.button("reset");
					$(".loading-overlay").hide();
					$(".scroll-list").mCustomScrollbar("update");
				}
			);
		});

		$("body").on("keydown", "#sheet input", function(e){
			if(e.keyCode == 13){
				e.preventDefault();
				$($("#sheet input")[$("#sheet input").index(this) + 1]).focus();
				return false;
			}
		});

		$("#create-group-btn").click(function(){
			var tthis = $(this);
			var $gname = $("#group-name");
			var $gpasswrd = $("#group-password");
			if($gname.val().trim() != "" && $gpasswrd.val().trim() != ""){
				tthis.data("loadingText", "Saving...").button("loading");
				$.post(home_url + "/?r=ajax/group/create", {gname: $gname.val(), gpasswrd: $gpasswrd.val()}, function(data){
					data = JSON.parse(data);
					tthis.button("reset");
					if(data["status"] == 0){
						$("#create-group-error").html(data.msg);
					}else{
						var selbox = $("#sel-group").data("selectBox-selectBoxIt");
						selbox.add("<option value='" + data.id + "'>" + $gname.val() + "</option>");
						selbox.selectOption(data.id.toString());
						$("#create-group-error").html(data.msg);
						$gname.val("");
						$gpasswrd.val("");
					}
				});
			}else{
				$("#create-group-error").html("Group name and Passwords must not be empty");
			}
		});

		$(".preview-btn").click(function(){
			var tthis = $(this);
			var tr = tthis.parents("tr");
			$("#current-assignment").val(tr.attr("data-assignment"));
			tthis.data("loadingText", "Loading...").button("loading");
			$.get(home_url + "/?r=ajax/sheets", {sid: tr.attr("data-id")}, function(data){
				if(data != "0"){
					$("#question-i").text(1);
					data = JSON.parse(data);
					$("#questions-table").html(data.html);
					$("#current-row").val(1);
					setup_homework_viewer(tr.attr("data-assignment"));
					if(tr.attr("data-assignment") == "3"){
						$("#reading-passage").html(data.passage);
						$("#passage-block").show();
					}else{
						$("#passage-block").hide();
					}
					$("#homework-detail").text("Grade " + tr.find("td:nth-child(2)").text() + " " + tr.find("td:first-child").text() + ", " + tr.find("td:nth-child(3)").text() + ", Question no.");
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
                $("#worksheet-details-modal").on("click", ".checkboxlastpage", function () {
                if($(this).is(':checked') ){
                   $(this).val('1');
                }else{
                    $(this).val('0');
                }
            });
		$(".worksheet-details-btn").click(function () {
			$("#hw-desc").html($(this).next().html());
                        var tthis = $(this);
                        var tr = $(this).parents("tr");
                        $("#sheet-name").text(tr.find("td:nth-child(4)").text());
                        var _check = tthis.parents("tr").find("input.checkboxpage");
                        var _id = $(this).attr("data-id");
                        $('.data-id-dialog').val(_id);
                        if(_check.val()==1){
                        $('.checkboxlastpage').prop('checked', true);
                        $('.checkboxlastpage').val('1');
                    }else{
                        $('.checkboxlastpage').prop('checked', false);
                        $('.checkboxlastpage').val('0');
                    }
			$("#worksheet-details-modal").modal();
		});
                
                 $("#worksheet-details-modal").on("click", "button.btn-rename", function () {
                        var tthis = $(this);
                        var _check = $('#checkboxlastpage');
                        var _id = $('#data-id-dialog').val();
                        if(_check.val()==1){
                        $('.checkboxlastpage').prop('checked', true);
                        $('.checkboxlastpage').val('1');
                    }else{
                        $('.checkboxlastpage').prop('checked', false);
                        $('.checkboxlastpage').val('0');
                    }
                    $.post(home_url + "/?r=ajax/grade/changelastpage", {id: _id,check :_check.val() }, function (data) {
                        //tthis.button("reset");
                        location.reload();
                    });
            });
                
		$(".assign-btn").click(function(){
			var tr = $(this).parents("tr");
			$("#sheet-id").val(tr.attr("data-id"));
			$("#homework-name").text(tr.find("td:nth-child(4)").text());
			$("#assign-homework-modal").modal();
		});

		$("#send-btn").click(function(){
			var $valid = true;
			if($("#sel-group").val() == ""){
				$valid = false;
				var $g = $("#sel-groupSelectBoxItContainer");
				$g.popover({content: '<span class="text-danger">Please select a Group</span>', html: true, trigger: "hover", placement: "bottom"})
				.popover("show");
				setTimeout(function(){$g.popover("destroy")}, 2000);
			}
			if(!$valid) return false;
		});

		$("#deadline").datepicker({minDate: 0, maxDate: "+1M"});

		$("#show-datepicker").click(function(e){
			e.preventDefault();
			$("#deadline").datepicker("show");
		});

		$("#sel-level-categories").on("option-click", function(){
			var _c = $(this).val();
			var $_l = $("#sel-levels");
			var $_sl = $("#sel-sublevels");
			$_l.html("").data("selectBox-selectBoxIt").refresh();
			$_sl.html("").data("selectBox-selectBoxIt").refresh();
			if(_c != ""){
				$_l.html($("#_l" + _c).html()).data("selectBox-selectBoxIt").refresh();
				if($_l.attr("data-selected") != ""){
					$_l.data("selectBox-selectBoxIt").selectOption($_l.attr("data-selected"));
				}

				$_sl.html($("#_sl" + $_l.val()).html()).data("selectBox-selectBoxIt").refresh();
				if($_sl.attr("data-selected") != ""){
					$_sl.data("selectBox-selectBoxIt").selectOption($_sl.attr("data-selected"));
				}
			}
		});

		$("#sel-levels").on("option-click", function(){
			var _l = $(this).val();
			$("#sel-sublevels").html($("#_sl" + _l).html()).data("selectBox-selectBoxIt").refresh();
		});

		$("#sel-level-categories").trigger("option-click");

		$("#filter-level-categories").on("option-click", function(){
			var _c = $(this).val();
			var $_l = $("#filter-levels");
			var $_sl = $("#filter-sublevels");
			$_l.find("option:not(:first-child)").remove();
			$_l.data("selectBox-selectBoxIt").refresh();
			$_sl.find("option:not(:first-child)").remove();
			$_sl.data("selectBox-selectBoxIt").refresh();
			if(_c != ""){
				$_l.append($("#_l" + _c).html()).data("selectBox-selectBoxIt").refresh().selectOption($_l.attr("data-selected"));
				$_sl.append($("#_sl" + $_l.val()).html()).data("selectBox-selectBoxIt").refresh().selectOption($_sl.attr("data-selected"));
			}
		});

		$("#filter-levels").on("option-click", function(){
			var _l = $(this).val();
			var $_sl = $("#filter-sublevels");
			$_sl.find("option:not(:first-child)").remove();
			$_sl.append($("#_sl" + _l).html()).data("selectBox-selectBoxIt").refresh();
		});

		$("#filter-level-categories").trigger("option-click");
	});
})(jQuery);