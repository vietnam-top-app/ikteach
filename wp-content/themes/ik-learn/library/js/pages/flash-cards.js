var flashs = [];
(function($){
	$(function(){
		toggle_fc_type($("#sel-fc-type").val());
		$("#sel-fc-folders").val(2); $("#sel-fc-folders").data("selectBox-selectBoxIt").refresh();
		

		$("#sel-fc-type").change(function(){
			toggle_fc_type($(this).val());
		});

		$("#sel-fc-folders").change(function(){
			var fid = parseInt($(this).val());
			flashs = [];
			if(fid == 1){
				setup_teacher_cards();
			}else{
				setup_my_own_cards();
			}
			update_header_width();
			$("#dictionary-block").hide();
		});

		$("#sel-teacher-sets").change(function(){
			setup_teacher_cards();
			update_header_width();
			$("#dictionary-block").hide();
		});

		function toggle_fc_type(id){
			switch(id){
				case "my-own": $("#teacher-sets-block").hide(); $("#my-own-block").show(); setup_my_own_cards(); break;
				case "teacher-sets": $("#my-own-block").hide(); $("#teacher-sets-block").show(); setup_teacher_cards(); break;
			}
			update_header_width();
		}

		function setup_my_own_cards(){
			var fid = parseInt($("#sel-fc-folders").val());
			var rows, m;
			$("#teacher-sets-block").hide();
			$("#flashcard-set-header").hide();
			if(!$.isEmptyObject(fc_folders[fid])){
				$.each(fc_folders[fid], function(i, v){
					if(!v.memorized){
						flashs.push(v);
					}
					m = v.memorized == 1 ? "<span class='icon-yes2'></span>" : "<span class='icon-no2'></span>";
					rows += '<tr>' +
							'<td class="fh">' + v.word + '</td>' +
							'<td><input type="text" class="flashcard-note" data-id="' + v.word_id + '" autocomplete="off" value="' + v.notes + '"></td>' +
							'<td><a class="toggle-memorized" data-id="' + v.word_id + '" href="#">' + m + '</a></td>' +
							'<td><a href="#" data-id="' + v.word_id + '" class="delete-card">delete</a></td>' +
						'</tr>';
				});
				$("#fc-table tbody").html(rows);
			}else{
				flashs = [];
				$("#fc-table tbody").html('<tr><td>There\'s no flashcard in this folder</td></tr>');
			}
		}

		function setup_teacher_cards(){
			if(fc_sets.length > 0){
				$("#teacher-sets-block").show();
				$("#flashcard-set-header").show();
				var setid = parseInt($("#sel-teacher-sets").val());
				$("#set-header span").html(fc_sets[setid].header);
				$("#set-teacher span").html(fc_sets[setid].teacher);
				$("#set-group span").html(fc_sets[setid].group);
				$("#set-date span").html(fc_sets[setid].date);
				$("#set-comment span").html(fc_sets[setid].comment);
				var rows;
				flashs = [];
				if(!$.isEmptyObject(fc_sets[setid].words)){
					$.each(fc_sets[setid].words, function(i, v){
						if(!v.memorized){
							flashs.push(v);
						}
						m = v.memorized == 1 ? "<span class='icon-yes2'></span>" : "<span class='icon-no2'></span>";
						rows += '<tr>' +
								'<td class="fh">' + v.word + '</td>' +
								'<td><input type="text" class="flashcard-note" data-id="' + v.word_id + '" autocomplete="off" value="' + v.notes + '"></td>' +
								'<td><a class="toggle-memorized" data-id="' + v.word_id + '" href="#">' + m + '</a></td>' +
								'<td></td>' +
							'</tr>';
					});
					$("#fc-table tbody").html(rows);
				}else{
					flashs = [];
					$("#fc-table tbody").html('<tr><td>There\'s no flashcard in this folder</td></tr>');
				}
			}
		}

		$("#fc-table").on("click", ".fh", function(){
			var a = $(this).parent().find("a");
			$.get(home_url + "/?r=ajax/flashcard/lookup", {id: a.attr("data-id")}, function(data){
				$("#dictionary-block").show();
				$("#fc-meaning").html(data);
			});
		});

		var timer;
		$("#fc-table").on("keyup", ".flashcard-note", function(e){
			var tthis = $(this);
			clearTimeout(timer);
			timer = setTimeout(function(){
				var $id = tthis.attr("data-id");
				if($("#sel-fc-type").val() == "teacher-sets"){
					fc_sets[parseInt($("#sel-teacher-sets").val())].words["w" + $id].notes = tthis.val();
				}else{
					fc_folders[parseInt($("#sel-fc-folders").val())]["w" + $id].notes = tthis.val();
				}
				$.post(home_url + "/?r=ajax/flashcard/savenotes", {id: $id, notes: tthis.val()});
			}, 500);
		});

		$("#fc-table").on("click", ".toggle-memorized", function(e){
			e.preventDefault();
			var $id = parseInt($(this).attr("data-id"));
			$.post(home_url + "/?r=ajax/flashcard/memorized", {id: $id}, function(data){
				if($("#sel-fc-type").val() == "teacher-sets"){
					fc_sets[parseInt($("#sel-teacher-sets").val())].words["w" + $id].memorized = Math.abs(fc_sets[parseInt($("#sel-teacher-sets").val())].words["w" + $id].memorized - 1);
				}else{
					fc_folders[parseInt($("#sel-fc-folders").val())]["w" + $id].memorized = Math.abs(fc_folders[parseInt($("#sel-fc-folders").val())]["w" + $id].memorized - 1);
				}
			});
			var $child = $(this).children();
			if($child.hasClass("icon-yes2")){
				$child.removeClass("icon-yes2");
				$child.addClass("icon-no2");
				if($("#sel-fc-type").val() == "teacher-sets"){
					flashs.push(fc_sets[parseInt($("#sel-teacher-sets").val())].words["w" + $id]);
				}else{
					flashs.push(fc_folders[parseInt($("#sel-fc-folders").val())]["w" + $id]);
				}
			}else{
				$child.removeClass("icon-no2");
				$child.addClass("icon-yes2");
				$.each(flashs, function(i,v){
					if(v.word_id == $id){
						flashs.splice(i, 1);
						return false;
					}
				});
			}
		});

		$("#fc-table").on("click", ".delete-card", function(e){
			e.preventDefault();
			$.post(home_url + "/?r=ajax/flashcard/delete", {id: $(this).attr("data-id")});
			delete fc_folders[parseInt($("#sel-fc-folders").val())]["w" + $(this).attr("data-id")];
			$(this).parents("tr").remove();
		});

		$("#flash-card-mode").click(function(){
			if(flashs.length == 0){
				$("#require-modal").modal();
			}else{
				setup_flash();
				$("#flashcard-modal").modal();
			}
		});

		$("#memorized-radio").change(function(){
			if($(this).is(":checked")){
				if($("#sel-fc-type").val() == "teacher-sets"){
					fc_sets[parseInt($("#sel-teacher-sets").val())].words["w" + flashs[0].word_id].memorized = 1;
				}else{
					fc_folders[parseInt($("#sel-fc-folders").val())]["w" + flashs[0].word_id].memorized = 1;
				}
				$.post(home_url + "/?r=ajax/flashcard/memorized", {id: flashs[0].word_id, memorized: 1});
				flashs.splice(0, 1);
			}
		});

		$("#next-flashcard").click(function(){
			setup_flash();
		});

		function setup_flash(){
			shuffle(flashs);
			$("#answer-block").text(flashs[0].word);
			$("#hints").text(flashs[0].notes);
			$("#memorized-radio").prop("checked", false);
		}

		$("body").tooltip({
			selector: '[data-toggle="tooltip"]',
			container: "body",
			trigger: "focus",
			html: true
		});

		$(".scroll-list2").mCustomScrollbar({
			theme: "rounded",
			mouseWheel:{scrollAmount:120},
			callbacks:{
				onOverflowY:function(){
					$(this).css("padding-right", "5px");
					$(this).find(".mCSB_inside > .mCSB_container").css("margin-right", "20px");
					update_header_width();
				},
				onOverflowYNone:function(){
					$(this).css("padding-right", "");
					$(this).find(".mCSB_inside > .mCSB_container").css("margin-right", "");
					update_header_width();
				}
			}
		});

		$(window).resize(function(){
			update_header_width();
		});

		function update_header_width(){
			if($(".flashcard-table tr td").length > 1){
				$(".flashcard-table-header > div:first-child").outerWidth($(".flashcard-table tr td:first-child").outerWidth());
				$(".flashcard-table-header > div:nth-child(2)").outerWidth($(".flashcard-table tr td:nth-child(2)").outerWidth());
				$(".flashcard-table-header > div:nth-child(3)").outerWidth($(".flashcard-table tr td:nth-child(3)").outerWidth());
			}
		}
	});
})(jQuery);