var ASSIGNMENT_SPELLING = 1; var ASSIGNMENT_VOCAB_GRAMMAR = 2; var ASSIGNMENT_READING = 3; var ASSIGNMENT_WRITING = 4; var ASSIGNMENT_VOCAB_BUILDER = 5; var ASSIGNMENT_REPORT = 6;
(function($){
	$(function(){
		$("#btn-main-menu").click(function(){$("#main-nav").slideToggle().toggleClass("show")});
		$("#btn-sub-menu").click(function(){$("#sub-user-nav").slideToggle()});
		$("#sub-user-nav").find("> ul > li > a").click(function(e){
			var viewport = getViewport();
			if(viewport.width <= 1199){
				e.preventDefault();
				$("#sub-user-nav").find("> ul > li > .sub-menu").slideUp();
				var menu = $(this).next();
				menu.is(":visible") ? menu.slideUp() : menu.slideDown();
			}
		});

		$(window).on("resize", function(){
			$("#sub-user-nav").find("> ul > li > .sub-menu").css("display", "");
			$("#main-nav").css("display", "");
			$("#sub-user-nav").css("display", "");
		});

		$("#lang-switcher").on("option-click", function(){
			window.location.href = $(this).val();
		});

		$("#page-tabs-mobile").on("option-click", function(){
			window.location.href = $(this).val();
		});

		$(".modal").on("show.bs.modal", function(){
			centerModals();
			var modal = this;
			var hash = "#" + modal.id;
			if(!location.hash || location.hash != hash){
				var href = location.href.replace(location.hash, "");
				history.pushState("", document.title, href + hash);
			}
			window.onhashchange = function() {
				if (!location.hash){
					$(modal).modal("hide");
				}
			}
		});

		$(".modal").on("hidden.bs.modal", function(){
			var hash = "#" + this.id;
			if(location.hash == hash){
				window.history.back()
			}
		});

		$(window).on('resize', centerModals);

		$(".scroll-list").mCustomScrollbar({
			theme: "rounded",
			mouseWheel:{scrollAmount:120},
			callbacks:{
				onOverflowY:function(){
					$(this).parents(".box").css("padding-right", "10px");
					$(this).parents(".box").find(".row.grid-table-head").css("padding-right", "30px");
				},
				onOverflowYNone:function(){
					$(this).parents(".box").css("padding-right", "");
					$(this).parents(".box").find(".row.grid-table-head").css("padding-right", "");
				}
			}
		});

		$(".scroll-list2").mCustomScrollbar({
			mouseWheel:{scrollAmount:180},
			callbacks:{
				onOverflowY:function(){
					$(this).parent().css("padding-right", "0");
				},
				onOverflowYNone:function(){
					$(this).parent().css("padding-right", "");
				}
			}
		});

		$(".check-all").change(function(){
			var selector = "[name='" + $(this).attr("data-name") + "']";
			$(selector).prop("checked", $(this).prop("checked"));
		});

		$("body").popover({selector: "[data-toggle='popover']", trigger: '"' + $(this).attr("data-trigger") + '"'})
			.on("show.bs.popover", function(){ $(this).data("bs.popover").tip().css("max-width", $(this).attr("data-max-width"))});

		$(".select-box-it").selectBoxIt({
			autoWidth: false,
			isMobile: function(){
				return false;
			}
		});

		$("body").on("click", ".play-sound-button", function(){
			var src_wav = $(this).attr("data-src-wav");

			if (supportAudioHtml5())
				playHtml5(src_wav);
			else if (supportAudioFlash())
				playFlash(src_wav);
			else
				playRaw(src_wav);
		});

		$(".sheet-editor").on("click", "tr", function(){
			var a = parseInt($("#assignments").val());
			if(a != ASSIGNMENT_SPELLING && a != ASSIGNMENT_VOCAB_BUILDER){
				var td = $(this).children();
				var i1i = $("#editor-input-1i");
				var i1a = $("#editor-input-1a");
				var i2 = $("#editor-input-2");
				var i3 = $("#editor-input-3");
				var i4 = $("#editor-input-4");
				var i5 = $("#editor-input-5");
				var i6 = $("#editor-input-6");
				var i7 = $("#editor-input-7");
				if(a == ASSIGNMENT_WRITING){
					i1i.hide(); i1a.show();
					i6.parent().hide(); i7.parent().hide();
				}else{
					i6.parent().show(); i7.parent().show();
					i1i.show(); i1a.hide();
				}
				switch(a){
					case ASSIGNMENT_VOCAB_GRAMMAR:
					case ASSIGNMENT_READING:
						i2.parent().show();
						i3.parent().show();
						i4.parent().show();
						i1i.val($(td["2"]).find("input").val());
						i2.val($(td["3"]).find("input").val());
						i3.val($(td["4"]).find("input").val());
						i4.val($(td["5"]).find("input").val());
						i6.val($(td["6"]).find("input").val());
						i7.val($(td["7"]).find("input").val());
						i5.val($(td["1"]).find("input").val());
						break;
					case ASSIGNMENT_WRITING:
						i2.parent().hide();
						i3.parent().hide();
						i4.parent().hide();
						i1a.val($(td["2"]).find("textarea").val());
						i5.val($(td["1"]).find("input").val());
						break;
				}
				$("#current-row-index").val($(this).attr("data-index"));
				$("#sheet-editor-modal .modal-title span").text($(this).attr("data-index"));
				$("#sheet-editor-modal").modal();
			}
		});

		$("#editor-save-btn").click(function(){
			var td = $(".sheet-editor [data-index='" + $("#current-row-index").val() + "'] td");
			var a = parseInt($("#assignments").val());
			switch(a){
				case ASSIGNMENT_VOCAB_GRAMMAR:
				case ASSIGNMENT_READING:
					$(td["2"]).find("input").val($("#editor-input-1i").val());
					$(td["3"]).find("input").val($("#editor-input-2").val());
					$(td["4"]).find("input").val($("#editor-input-3").val());
					$(td["5"]).find("input").val($("#editor-input-4").val());
					$(td["1"]).find("input").val($("#editor-input-5").val());
					$(td["6"]).find("input").val($("#editor-input-6").val());
					$(td["7"]).find("input").val($("#editor-input-7").val());
					break;
				case ASSIGNMENT_WRITING:
					$(td["2"]).find("textarea").val($("#editor-input-1a").val());
					$(td["1"]).find("input").val($("#editor-input-5").val());
					break;
			}
			
			$("#sheet-editor-modal").modal("hide");
		});

		$("#sheet-editor-modal").on("keydown", "input", function(e){
			if(e.keyCode == 13){
				e.preventDefault();
				$("#editor-save-btn").click();
			}
		});

		$(".order-btn").click(function(){
			$("[name='cid']").val($(this).attr("data-id"));
		});

		document.addEventListener("play", function(e){
			if($(e.target).is("audio.test-mode")){
				$(e.target).hide();
			}
		}, true);

		$("#no-of-points").change(function(){
			var p = parseInt($(this).val());
			p = isNaN(p) ? 0 : p;
			$("#total-amount-points").text(p / ptsr);
		});

		$(".sortable").click(function(e){
			e.preventDefault();
			$("#filter-order").val($(this).attr("data-sort-by"));
			var dir = $("#filter-order-dir").val();
			dir = dir == "" ? 'asc' : dir;
			$("#filter-order-dir").val(dir == 'asc' ? 'desc' : 'asc');
			$("#main-form").submit();
		});

		$("#page-info-tab").click(function(){
			$("#page-info-tab-dialog").modal();
		});
	});
})(jQuery);

function getViewport(){
    var e = window, a = 'inner';
    if (!('innerWidth' in window )) {
        a = 'client';
        e = document.documentElement || document.body;
    }
    return { width : e[ a+'Width' ] , height : e[ a+'Height' ] };
}

function supportAudioHtml5(){
	var audioTag  = document.createElement('audio');
	try {
		return ( !!(audioTag.canPlayType)
				 && ( ( audioTag.canPlayType("audio/mpeg") != "no" && audioTag.canPlayType("audio/mpeg") != "" )
				 || ( audioTag.canPlayType("audio/ogg") != "no" && audioTag.canPlayType("audio/ogg") != "" )
				 || ( audioTag.canPlayType("audio/wav") != "no" && audioTag.canPlayType("audio/wav") != "" )) );
	} catch(e){
		return false;
	} 
}

function supportAudioFlash() {
	var flashinstalled = 0;
	var flashversion = 0;
	if (navigator.plugins && navigator.plugins.length){
		x = navigator.plugins["Shockwave Flash"];
		if (x){
			flashinstalled = 2;
			if (x.description) {
				y = x.description;
				flashversion = y.charAt(y.indexOf('.')-1);
			}
		} else {
			flashinstalled = 1;
		}
		if (navigator.plugins["Shockwave Flash 2.0"]){
			flashinstalled = 2;
			flashversion = 2;
		}
	} else if (navigator.mimeTypes && navigator.mimeTypes.length){
		x = navigator.mimeTypes['application/x-shockwave-flash'];
		if (x && x.enabledPlugin)
			flashinstalled = 2;
		else
			flashinstalled = 1;
	} else {
		for(var i=7; i>0; i--){
			flashVersion = 0;
			try{
				var flash = new ActiveXObject("ShockwaveFlash.ShockwaveFlash." + i);
				flashVersion = i;
				return (flashVersion > 0);
			} catch(e){}
		}
	}
	return (flashinstalled > 0);
}

function playHtml5(src_wav) {		
	audio = new Audio(src_wav);		

	//play
	audio.addEventListener("error", function(e){alert("Apologies, the sound is not available.");});
	audio.play();
}

function playFlash(src_wav) {
	var src_flash ="http://iklearn.com/wp-content/plugins/iii-dictionary/flash/speaker.swf?song_url=" +  src_wav + "&autoplay=true&version=2.9.25";
	if (navigator.plugins && navigator.mimeTypes && navigator.mimeTypes.length)
		jQuery("body").append("<embed type='application/x-shockwave-flash' src='" + src_flash + "' width='0' height='0'></embed>");
	else
		jQuery("body").append("<object type='application/x-shockwave-flash' width='0' height='0' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0' data='" + src_flash + "'><param name='wmode' value='transparent'/><param name='movie' value='" + src_flash + "'/><embed src='" + src_flash + "' width='0' height='0' ></embed></object>");
}

function playRaw(src_wav) {
	window.open(src_wav, "Sound", "menubar=no, status=no, scrollbars=no, menubar=no, width=200, height=100");
}

function centerModals(){
	jQuery('.modal').each(function(i){
		var $clone = jQuery(this).clone().css('display', 'block').appendTo('body');
		var top = Math.round(($clone.height() - $clone.find('.modal-content').height()) / 2);
		top = top > 0 ? top : 0;
		$clone.remove();
		jQuery(this).find('.modal-content').css("margin-top", top);
	});
}

function setup_popover(selector, title, readonly, textarea, max_width){
	readonly = typeof readonly !== "undefined" ? readonly : false;
	max_width = typeof max_width != "undefined" ? max_width : 500;
	jQuery(selector).popover({
		container: "body",
		placement: "bottom",
		html: true,
		title: title,
		content: function(){
			var readonly_txt = readonly ? " readonly" : "";
			if(typeof textarea !== 'undefined' && textarea){
				return '<textarea rows="7" cols="100" class="form-control txt-popover"' + readonly_txt + '></textarea>';
			}else{
				return '<input type="text" class="form-control txt-popover" size="50"' + readonly_txt + '>';
			}
		}
	}).on({
		"show.bs.popover": function(){ jQuery(this).data("bs.popover").tip().css("max-width", max_width); },
		"shown.bs.popover": function (){
			var $popover = jQuery("#" + jQuery(this).attr("aria-describedby"));
			var $sentence_input = jQuery(this).next();
			var $popover_input = $popover.find(".txt-popover");
			$popover_input.val($sentence_input.val());
			$popover_input.focus();
			$popover_input.on("blur", function(){
				$popover.popover("hide");
				$sentence_input.val($popover_input.val());
			});
		}
	});
}

function shuffle(array) {
	var currentIndex = array.length, temporaryValue, randomIndex ;
	while (0 !== currentIndex) {
		randomIndex = Math.floor(Math.random() * currentIndex);
		currentIndex -= 1;
		temporaryValue = array[currentIndex];
		array[currentIndex] = array[randomIndex];
		array[randomIndex] = temporaryValue;
	}

	return array;
}

function setup_homework_viewer(assignment){
	var i = jQuery("#current-row").val();
	var tbl = jQuery("#questions-table");
	if(assignment == "4"){
		jQuery("#question-box").hide();
		jQuery("#spelling-subject-block").hide();
		jQuery("#exit-btn").hide();
		jQuery("#writing-subject-block").show();
		jQuery("#next-btn").show();
		jQuery("#writing-subject").html(tbl.find("tr:nth-child(" + i + ") td:nth-child(1) textarea").val().replace(/(?:\r\n|\r|\n)/g, "<br>"));
		jQuery("#quiz").html(tbl.find("tr:nth-child(" + i + ") td:nth-child(2) input").val());
	}else if(assignment == "1") {
		jQuery("#writing-subject-block").hide();
		jQuery("#question-box").hide();
		jQuery("#next-btn").hide();
		jQuery("#exit-btn").show();
		jQuery("#spelling-subject-block").show();
		jQuery("#quiz").html('');
		jQuery("h3.modal-title > span").hide();
		var spelling_html = "<ul class='select-box' >";
		jQuery("#questions-table tr").each(function(index){  index++;  spelling_html +=  "<li> No." + index + " - " + jQuery(this).find("td:nth-child(1) input").val() + "</li>"; })
		spelling_html += "</ul>";
		jQuery("#spelling-subject-block").html(spelling_html);
	}
	else{
		jQuery("#writing-subject-block").hide();
		jQuery("#spelling-subject-block").hide();
		jQuery("#exit-btn").hide();
		jQuery("#next-btn").show();
		jQuery("#question-box").show();
		jQuery("#vocab-question").html(tbl.find("tr:nth-child(" + i + ") td:nth-child(1) input").val());
		jQuery("#quiz").html(tbl.find("tr:nth-child(" + i + ") td:nth-child(7) input").val());
		jQuery("#answer-a").html(tbl.find("tr:nth-child(" + i + ") td:nth-child(2) input").val());
		jQuery("#answer-b").html(tbl.find("tr:nth-child(" + i + ") td:nth-child(3) input").val());
		jQuery("#answer-c").html(tbl.find("tr:nth-child(" + i + ") td:nth-child(4) input").val());
		var td5v = tbl.find("tr:nth-child(" + i + ") td:nth-child(5) input").val();
		var td6v = tbl.find("tr:nth-child(" + i + ") td:nth-child(6) input").val();
		if(td5v != ""){
			jQuery("#answer-d").html(td5v).parents("li").removeClass("hidden");
		}else{
			jQuery("#answer-d").parents("li").addClass("hidden");
		}
		if(td6v != ""){
			jQuery("#answer-e").html(td6v).parents("li").removeClass("hidden");
		}else{
			jQuery("#answer-e").parents("li").addClass("hidden");
		}
	}
}

function setup_sheet_layout(id){
	id = parseInt(id);
	if(id != ""){
		var ele = "";
		switch(id){
			case ASSIGNMENT_SPELLING:
				for(i = 1; i <= 20; i++){
					ele += '<tr><td class="order-number">' + i + '.</td><td><input name="words[]" class="input-box-style2" autocomplete="off" value="" type="text"></td></tr>';
				}
				break;
			case ASSIGNMENT_VOCAB_GRAMMAR:
				for(i = 1; i <= 20; i++){
					ele += '<tr data-index="' + i + '"><td class="order-number">' + i + '.</td><td><a class="btn btn-tiny orange" href="#" onClick="return false">Subject</a><input type="text" name="words[quiz][]" class="quiz_input" value=""></td><td><a class="btn btn-tiny orange" href="#" onClick="return false">Question</a><input type="text" name="words[question][]" class="sentence_input" value=""></td><td><input type="text" name="words[c_answer][]" class="input-box-style2" autocomplete="off" value=""></td><td><input type="text" name="words[w_answer1][]" class="input-box-style2" autocomplete="off" value=""></td><td><input type="text" name="words[w_answer2][]" class="input-box-style2" autocomplete="off" value=""></td><td><input type="text" name="words[w_answer3][]" class="input-box-style2" autocomplete="off" value=""></td><td><input type="text" name="words[w_answer4][]" class="input-box-style2" autocomplete="off" value=""></td></tr>';
				}
				break;
			case ASSIGNMENT_READING:
				for(i = 1; i <= 20; i++){
					ele += '<tr data-index="' + i + '"><td class="order-number">' + i + '.</td><td><input type="text" class="input-box-style2" name="words[quiz][]" value=""></td><td class="hidden"><a class="btn btn-tiny orange" href="#" onClick="return false">Question</a><input type="text" name="words[question][]" class="sentence_input" value=""></td><td class="hidden"><a class="btn btn-tiny orange" href="#" onClick="return false">Correct</a><input type="text" name="words[c_answer][]" class="sentence_input" value=""></td><td class="hidden"><a class="btn btn-tiny orange" href="#" onClick="return false">Incorrect 1</a><input type="text" name="words[w_answer1][]" class="sentence_input" value=""></td><td class="hidden"><a class="btn btn-tiny orange" href="#" onClick="return false">Incorrect 2</a><input type="text" name="words[w_answer2][]" class="sentence_input" value=""></td><td class="hidden"><a class="btn btn-tiny orange" href="#" onClick="return false">Incorrect 3</a><input type="text" name="words[w_answer3][]" class="sentence_input" value=""></td><td class="hidden"><a class="btn btn-tiny orange" href="#" onClick="return false">Incorrect 4</a><input type="text" name="words[w_answer4][]" class="sentence_input" value=""></td></tr>';
				}
				break;
			case ASSIGNMENT_WRITING:
				for(i = 1; i <= 20; i++){
					ele += '<tr data-index="' + i + '"><td class="order-number">' + i + '.</td><td><input type="text" name="words[quiz][]" class="input-box-style2" value=""></td><td class="hidden"><textarea name="words[question][]" class="sentence_input" value=""></textarea></td></tr>';
				}
				break;
			case ASSIGNMENT_VOCAB_BUILDER:
				for(i = 1; i <= 20; i++){
					ele += '<tr><td class="order-number">' + i + '.</td>' +
						'<td class="fc"><input name="words[word][]" class="input-box-style2" autocomplete="off" type="text" placeholder="Word"></td>' +
						'<td><input name="words[sentence][]" class="input-box-style2" autocomplete="off" type="text" placeholder="Sentence"></td></tr>';
				}
				break;
		}
		jQuery("#sheet").html(ele);

		if(id == ASSIGNMENT_SPELLING){
			jQuery("#check-word").show();
		}else{
			jQuery("#check-word").hide();
		}

		if(id == ASSIGNMENT_READING){
			jQuery("#reading-passage-block").show();
		}else{
			jQuery("#reading-passage-block").hide();
		}

		if(id == ASSIGNMENT_VOCAB_BUILDER){
			jQuery("#description-block").hide();
		}else{
			jQuery("#description-block").show();
		}

		var _h = jQuery("#sheet-header-block");
		if(id == ASSIGNMENT_REPORT){
			jQuery("#import-block").slideUp();
			jQuery("#questions-sheet").slideUp();
			_h.removeClass("col-md-5");
			jQuery("#report-assignment-block").slideDown();
		}else{
			jQuery("#import-block").slideDown();
			jQuery("#questions-sheet").slideDown();
			_h.addClass("col-md-5");
			jQuery("#report-assignment-block").slideUp();
		}
	}
}