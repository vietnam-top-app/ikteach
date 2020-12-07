(function($){
	$(function(){
		$("#input-file").change(function(e){
			var ext = $("#input-file").val().split(".").pop().toLowerCase();
			if($.inArray(ext, ["csv"]) == -1){
				alert('Please Upload CSV');
				return false;
			}
			$("#imported-file").val($(this).val());
			if (e.target.files != undefined){
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
			return false;
		});

		$("#input-file-math").change(function(e){
			var _aid = $("#math-assignments").val();
			$("#imported-file-math").val($(this).val());
			_aid = _aid == "10" ? "9" : _aid;
					var _tb = $("#math-sheet-form-" + _aid).find("table tbody");
					_tb.find("input").val("");
					if(_tb.find("select").length > 0)
						_tb.find("select").data("selectBox-selectBoxIt").selectOption(0);

			if (e.target.files != undefined){
				var reader = new FileReader();
				var lines, line, sign;
				reader.onload = function(e){
					var content = e.target.result;
					lines = content.split(/\r\n|\n/);
					for(var i = 0; i < lines.length; i++){
						lines[i] = lines[i].replace(/[\t]/g,"");
						if(lines[i].length == 0){
							lines.splice(i, 1);
							i--;
						}
					}

					switch(_aid){ // switch homework format
						case "7":
							for(var i = 0; i < lines.length; i++){
								lines[i] = lines[i].replace(/ /g,"");
								if(i == 1){
									sign = lines[1].substring(0, 1).toLowerCase();
									lines[1] = lines[1].substring(1);
									_tb.find("tr:first td:nth-child(2) select").data("selectBox-selectBoxIt").selectOption(sign);
								}
								_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(3) input").val(lines[i]);
							}
							break;
						case "8":
							for(var i = 0; i < lines.length; i++){
								lines[i] = lines[i].replace(/ /g,"");
								if(i == 1) lines[1] = lines[1].toLowerCase().replace("x", "");
								_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(2) input").val(lines[i]);
							}
							break;
						case "9":
						case "10":
							var _s = [];
							_tb.find("tr:nth-child(1) td:nth-child(2) input").val(lines[0].replace(/ /g,""));
							_tb.find("tr:nth-child(2) td:nth-child(2) input").val(lines[1].replace(/ /g,""));
							_tb.find("tr:nth-child(3) td:nth-child(2) input").val(lines[2].replace(/ /g,""));
							for(var i = 3; i < lines.length - 1;i++){
								_s.push(lines[i].replace(/ /g,""));
							}
							_tb.find("tr:nth-child(4) td:nth-child(2) textarea").val(_s.join("\n"));
							_tb.find("tr:nth-child(5) td:nth-child(2) input").val(lines[lines.length - 1].replace(/ /g,""));
							break;
						case "11":
						case "12":
							var parts, left, right, lsign, ops, de, v, sign_pos, max_len = lines.length <= 20 ? lines.length : 20;
							for(var i = 0; i < max_len; i++){
								mainParts = lines[i].split("|");
								parts = mainParts[0].split("=");
								parts[0] = _aid == "11" ? parts[0].replace(/ /g,"") : parts[0].trim().replace(/  +/g, " ");
								parts[1] = parts[1].trim().replace(/  +/g, ' ');
								if(parts[0].indexOf("+") == -1 && parts[0].indexOf("-") == -1 && parts[0].indexOf("x") == -1 && parts[0].indexOf(":") == -1){
									_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(3) select").data("selectBox-selectBoxIt").selectOption(0);
									_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(4) input").val(parts[0]);
								}else{
									lsign = parts[0].substring(0, 1);
									if(lsign == "-"){
										parts[0] = parts[0].substring(1);
									}else{
										lsign = "";
									}

									if(parts[0].indexOf("+") != -1){
										de = "+"; v = 1;
									}else if(parts[0].indexOf("x") != -1){
										de = "x"; v = 3;
									}else if(parts[0].indexOf(":") != -1){
										de = ":"; v = 4;
									}else if(parts[0].indexOf("-") != -1){
										de = "-"; v = 2;
									}
									sign_pos = parts[0].indexOf(de);
									_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(2) input").val(lsign + parts[0].substring(0, sign_pos).trim());
									_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(3) select").data("selectBox-selectBoxIt").selectOption(v);
									_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(4) input").val(parts[0].substring(sign_pos + 1).trim());
								}
								_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(6) input").val(parts[1]);

								if(mainParts[1] !== "undefined"){
									_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(7) input").val(mainParts[1]);
								}
							}
							break;
						case "13":
							var cols;
							_tb.find("tr:first td:nth-child(2) input").val(lines[0].replace(/ /g,""));
							for(var i = 1; i < lines.length; i++){
								cols = lines[i].replace(/ /g,"").split(",");
								_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(2) input").val(cols[0]);
								_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(3) input").val(cols[1]);
								_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(4) input").val(cols[2]);
							}
							break;
						case "14":
							var cols;
							for(var i = 0; i < lines.length; i++){
								cols = lines[i].split(",");
								_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(2) input").val(cols[0]);
								_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(3) input").val(cols[1]);
								_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(4) input").val(cols[2]);
								_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(5) input").val(cols[3]);
								_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(7) input").val(cols[4]);
								_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(8) input").val(cols[5]);
							}
							break;
						case "15":
							var parts;
							for(var i = 0; i < lines.length; i++){
								parts = lines[i].split("=");
								_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(3) input").val(parts[parts.length - 1].trim());
								parts.splice(parts.length - 1, 1);
								_tb.find("tr:nth-child(" + (i + 1) + ") td:nth-child(2) input").val(parts.join("=").trim().replace(":", "&divide;") + " =");
							}
							break;
					}
				};
				reader.readAsText(e.target.files.item(0));
			}
			return false;
		});

		$("#check-word").click(function(){
			if($("#sel-dictionary").val() == ""){alert("Please select a Dictionary!");return false;}
			var tthis = $(this);
			var val = [];var err;val[0] = [];val[1] = [];
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
					if((data[0].length == 0 && data[1].length == 0) || (data[0].length == 0 && ($("#assignments").val() == "2" || $("#assignments").val() == "5"))){
						$(".loading-overlay").hide();
						tthis.button("reset");
						$(".scroll-list").mCustomScrollbar("update");
						$("#wordchecked").val(1); 
						alert("All words are correct");
						if($("#spelling").is(':checked')) return;
					}else{
						if($("#vocabulary").is(':checked')){
							$("#wordchecked").val(1);
						}
					}
					$.each(data[1], function(i, v){
						$("#sheet > tbody > tr").each(function(index, element){
							var input = $(element).find("td:nth-child(2) > input");
							if(input.val().trim().replace(/\s{2,}/g, ' ') == v){
								input.addClass("word-sound-error");
								input.attr("title", "No sound file available");
							}
						});
					});
					$.each(data[0], function(i, v){
						$("#sheet > tbody > tr").each(function(index, element){
							var input = $(element).find("td:nth-child(2) > input");
							if(input.val().trim().replace(/\s{2,}/g, ' ') == v){
								input.addClass("word-error");
								input.attr("title", "Word not found in Dictionary");
							}
						});
					});
					$(".loading-overlay").hide();
					tthis.button("reset");
					$(".scroll-list").mCustomScrollbar("update");
				}
			);
		});

		$("#filter-sheet-name").keypress(function(e){
			if(e.keyCode == 13){
				e.preventDefault();
				$("#search-btn").click();
			}
		});

		$("#assignments").change(function(){
			if($(this).val() == ASSIGNMENT_WRITING){
				$("#grading-price-block").slideDown();
			}else{
				$("#grading-price-block").slideUp();
			}
		});

		$("#homework-types").change(function(){
			if($(this).val() == 5 && $("#assignments").val() == ASSIGNMENT_WRITING){
				$("#grading-price-block").slideDown();
			}else{
				$("#grading-price-block").slideUp();
			}
		});

		$("#sel-dictionary").change(function(){
			var input = $("#sheet > tbody > tr input");
			input.removeClass("word-error");
			input.attr("title", "");
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

		$(".worksheet-details-btn").click(function(){
			$("#hw-desc").html($(this).next().html());
			$("#worksheet-details-modal").modal();
		});

		$(".scroll-list2").mCustomScrollbar({
			scrollButtons:{enable:true},
			theme:"light-thick",
			mouseWheel:{scrollAmount:160}
		});

		$("#dictionary-sel-btn").click(function(){
			$("#sel-dictionarySelectBoxIt").click();
		});

		$("#grade").selectBoxIt({
			autoWidth: false,
			showFirstOption: false
		});

		$("#grade").on("option-click", function(){
			if($(this).attr("data-custom") == $(this).val()){
				$("#custom-grade-modal").modal("show");
			}
		});

		$("#save-grade").click(function(){
			var v = $("#custom-grade-txt").val();
			$("#op-custom").val(v);
			$("#op-custom").text(v);
			$("#sel-custom").val(v);
			$("#grade").attr("data-custom", v);
			$("#grade").data("selectBox-selectBoxIt").refresh();
			$("#custom-grade-modal").modal("hide");
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

		$("#conf-del-btn").click(function(){
			$("#confirm-deletion-modal").find(".modal-body").html("You are about to delete " + $("[name='cid[]']:checked").length + " worksheets. Do you want to continue?");
			$("#confirm-deletion-modal").modal();
		});

		$(".change-order").click(function(){
			$("#oid").val($(this).attr("data-id"));
		});

		$("button.cache-form").click(function(){
			window.sessionStorage.assignment = $("#math-assignments").val();
			window.sessionStorage.homework_type = $("#homework-types").val();
			window.sessionStorage.level_cat = $("#sel-level-categories").val();
			window.sessionStorage.level = $("#sel-levels").val();
			window.sessionStorage.sublevel = $("#sel-sublevels").val();
		});

		if(typeof _PRELOAD !== "undefined"){
			if(_PRELOAD){
				if(typeof window.sessionStorage.assignment === "undefined"){
					window.sessionStorage.assignment = "7";
				}

				$("#math-assignments").val(window.sessionStorage.assignment).data("selectBox-selectBoxIt").refresh();
				$("#homework-types").val(window.sessionStorage.homework_type).data("selectBox-selectBoxIt").refresh();
				$("#sel-level-categories").val(window.sessionStorage.level_cat).trigger("option-click").data("selectBox-selectBoxIt").refresh();
				$("#sel-levels").val(window.sessionStorage.level).trigger("option-click").data("selectBox-selectBoxIt").refresh();
				$("#sel-sublevels").val(window.sessionStorage.sublevel).data("selectBox-selectBoxIt").refresh();
				$("#math-assignments").trigger("change")
			}else{
				window.sessionStorage.assignment = "7";
				window.sessionStorage.homework_type = "";
				window.sessionStorage.level_cat = "";
				window.sessionStorage.level = "";
				window.sessionStorage.sublevel = "";
			}
		}
	});
})(jQuery);