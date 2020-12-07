(function($){
	$(function(){
		if(typeof localStorage.speaker_on === "undefined"){
			localStorage.speaker_on = true;
		}
                jQuery("body").on("click", "#wp-live-chat-minimize", function() {
                    document.getElementById('wp-live-chat-2').style.display = "none";
                });
                $("#modal-custom").draggable({
                    handle: ".notice-header"
                });
                $( "body" ).append("<div id='wplc_hovercard' style='display:none' class='modern'><div id='wplc_hovercard_min' class='wplc_button_standard wplc-color-border-1 wplc-color-bg-1'>close</div><div id='wplc_hovercard_content'><div class='wplc_hovercard_content_left'><div class='wplc_left_logo' style='background:url(http://dicprj.moe/wp-content/plugins/wp-live-chat-support/images/iconmicro.png) no-repeat; background-size: cover;'></div></div><div class='wplc_hovercard_content_right'><div id='wplc_first_message'><div class='wplc-color-4'><strong>Questions?</strong> Chat with us</div></div></div></div><div id='wplc_hovercard_bottom'><button id=\"speeching_button\" type=\"button\"  class='wplc-color-bg-1 wplc-color-2'>Start Chat</button></div></div><div id=\"wp-live-chat\" wplc_animation=\"none\" style=\"margin-bottom: 0; left: 100px; bottom:0px;;\" class=\"modern  wplc_close\" original_pos=\"bottom_right\"  > <div class=\"wp-live-chat-wraper\"><div id=\"wp-live-chat-header\" class='wplc-color-bg-1 wplc-color-2'><style>#wp-live-chat-header { background:url(http://dicprj.moe/wp-content/plugins/wp-live-chat-support/images/chaticon.png) no-repeat; background-size: cover; }</style> </div> <div id=\"wp-live-chat-2\" style=\"display:none;\"> <i id=\"wp-live-chat-close\" style=\"right: 23px;display: block;\"></i> <i id=\"wp-live-chat-minimize\" class=\"fa fa-minus wplc-color-bg-1 wplc-color-2\" style=\"right: 50px;display: block;\"></i> <i id=\"wp-live-chat-close\" class=\"fa fa-times\" style=\"display:none;\" ></i> <div id=\"wplc-extra-div\" style=\"display:none;\"></div>    <div id=\"wp-live-chat-4\" ><div id=\"wplc_sound_update\" style=\"height:0; width:0; display:none; border:0;\"></div><div id='wplc_chatbox_header' class='wplc-color-bg-2 wplc-color-4'></div><div id=\"wplc_chatbox\"></div><div id='wplc_user_message_div'><p style=\"text-align:center; font-size:11px;\" id='wplc_msg_notice'>Press ENTER to send your message</p><p><input type=\"text\" name=\"wplc_chatmsg\" id=\"txt-chat\" class=\"txt-chat1\" value=\"\" /><input type=\"hidden\" name=\"wplc_cid\" id=\"wplc_cid\" value=\"\" /><input id=\"wplc_send_msg\" type=\"button\" value=\"Send\" style=\"display:none;\" /></p></div></div>   </div> </div></div>");
                jQuery("body").on("click", "#wp-live-chat-header", function() {
                document.getElementById('wp-live-chat-2').style.display = 'none';
                });
                jQuery('#wp-live-chat').hide();
		localStorage.speaker_on == "false" ? $("#speaker-button").addClass("off") : $("#speaker-button").removeClass("off");

		function turn_speaker(id, state){
			var e = document.getElementById(id);
			if(localStorage.speaker_on == "true" && e != null){
				if(state == "off"){
					e.pause();
					e.currentTime = 0;
				}else{
					e.play();
				}
			}
		}

		$("#speaker-button").click(function(){
			var tthis = $(this);
			$(this).toggleClass("off");
			localStorage.speaker_on = $(this).hasClass("off") ? false : true;

			if(localStorage.speaker_on == "false"){
				var elements = document.getElementsByTagName("audio");
				for(i = 0; i < elements.length; i++){
					elements[i].pause();
				}
			}
		});

		function setup_questions_nav(){
			if(getViewport().width >= 768){
				$("span.ar").remove();
				$("ul.nav-items").removeClass("horizontal");
				$(".scroll-list-v").mCustomScrollbar({
					theme: "rounded",
					mouseWheel:{scrollAmount:120}
				});
			}else{
				$(".scroll-list-v").mCustomScrollbar("destroy");
				var _nav = $("ul.nav-items"), _liWidth = 0;
				_nav.find("li").each(function(){
					_liWidth += $(this).outerWidth();
				});

				if(_liWidth > _nav.innerWidth()){
					var _larrow = $("<span id='question-nav-left' class='ar'></span>");
					_larrow.click(function(){_nav.scrollLeft(_nav.scrollLeft() - 31)});
					var _rarrow = $("<span id='question-nav-right' class='ar'></span>");
					_rarrow.click(function(){_nav.scrollLeft(_nav.scrollLeft() + 31)});
					var _lr_row = ($('span#question-nav-left').length > 0 || $('span#question-nav-right').length > 0) ? 1 : 0;
					if(_lr_row == 0) { _nav.addClass("horizontal").before(_larrow).after(_rarrow); }
				}
			}
		}

		setup_questions_nav();

		var resizing;
		$(window).on("resize", function(){
			clearTimeout(resizing);
			resizing = setTimeout(setup_questions_nav, 100);
		});

		$("#answer-steps").find("li:not(.ar)").click(function(){
			var _ul = $("#answer-steps"), _selector, _sn;
			var _cn = $(this).attr("data-n");
			$(this).toggleClass("active");
			_ul.find("li.active").each(function(j, li){
				var _s = parseInt($(li).attr("data-n"));
				if(!$(li).hasClass("nlast") && !is_div_type){
					if(_s % 2 != 0){
						_selector = ".input-box:last";
						_sn =_s;
					}else{
						_selector = ".input-box:not(:last)";
						_sn = _s - 1;
					}
				}else{
					_selector = ".input-box";
					_sn =_s;
				}

				for(i = _sn; i <= _sn + 1; i++){
					$("#answer-step-" + i).find(_selector).each(function(i,v){
						var _e = $(v).children();
						if(_e.val() == _e.attr("data-answer")){
							_e.parent().removeClass("has-incorrect").addClass("has-correct visited" + _s);
						}else{
							_e.parent().removeClass("has-correct").addClass("has-incorrect visited" + _s);
						}
					});
				}
				if(is_div_type){
					var _answerbox = $("#answer-step-" + answer_step_num).find(".input-box:nth-child(" + $(li).text() + ") input");
					if(_answerbox.val() == _answerbox.attr("data-answer")){
						_answerbox.parent().removeClass("has-incorrect").addClass("has-correct visited" + _s);
					}else{
						_answerbox.parent().removeClass("has-correct").addClass("has-incorrect visited" + _s);
					}
				}
			});

			if(!$(this).hasClass("active")){
				$("span.visited" + _cn).removeClass("has-incorrect has-correct visited" + _cn);
			}
		});

		$("#question-nav").find("li").click(function(){
			$("#question-nav").find("li").removeClass("active");
			$("div.flashcard-question").addClass("hidden");
			$(this).addClass("active");
			$("#flashcard-q" + $(this).attr("data-n")).removeClass("hidden");
		});

		$("#question-nav").find("li:first").click();

		$("#input-answer").keydown(function(e){
			if(e.keyCode == 13){
				e.preventDefault();
				return false;
			}
		});

		if(_CMODE == "practice"){
			if(_SHOW_TIME > 0){
				var _aInterval;
				_aInterval = setInterval(function(){
					var _c = $("#question-nav").find("li.active"), _i = $("#flashcard-q" + _c.attr("data-n")).find("input");
					_i.val(_i.attr("data-answer"));
					if(_c.next().length > 0)
						setTimeout(function(){_c.next().click()}, 1500);
					else
						clearInterval(_aInterval);
				}, _SHOW_TIME * 1000);
			}

			var timer;
			$("input.answer-box").keyup(function(){
				var tthis = $(this);
				clearTimeout(timer);
				timer = setTimeout(function(){
					if(tthis.val() == "") {
						tthis.parent().removeClass("has-correct has-incorrect");
					}else{
						var user_type = tthis.val();
						var answer = tthis.attr("data-answer");
						user_type = user_type.replace(/\s/g,'');
						user_type = user_type.toUpperCase();
						answer = answer.replace(/\s/g,'');
						answer = answer.toUpperCase();
						if(compare_fraction(user_type,answer )){
							tthis.parent().removeClass("has-incorrect").addClass("has-correct");
						}else{
							tthis.parent().removeClass("has-correct").addClass("has-incorrect");
						}
					}
				}, 100);
			});
			function compare_fraction(answer, result) {
				var _parse_result 	= result.split(' ');
				var _case 			= 0;
				var _result 		= false;
				if(_parse_result.length > 1) { /* 1 : - , 2 : 2 2/3*/ 
				_case = (isNaN(_parse_result[0])) ? 1 : 2; }

				switch(_case) {
					case 1 	: 
						_result = (answer.replace('- ', '-') == result.replace('- ', '-'))  ? true : false;
						break;
					case 2 	: 
						_result = answer == result ? true : false;
						if(!_result) { 
							var _parse_part = _parse_result[1].split('/');
							
							result 			= ((parseInt(_parse_result[0]) * parseInt(_parse_part[1]) + parseInt(_parse_part[0]))) + '/' + parseInt(_parse_part[1]);
							_result 		= (answer.replace('- ','-') == result) ? true : false;
						}
						break;
					default : 
						_result = (answer.replace('- ', '-') == result.replace('- ', '-'))  ? true : false;
					break;
				} 
				return _result;
			}

			var timer2;
			$("#input-answer").keyup(function(e){
				var tthis = $(this), title;
				clearTimeout(timer2);
				timer2 = setTimeout(function(){
					if(tthis.val() == "") {
						tthis.tooltip("destroy");
					}else{
						var user_type = tthis.val();
						var answer = tthis.attr("data-answer");
						user_type = user_type.replace(/\s/g,'');
						user_type = user_type.toUpperCase();
						answer = answer.replace(/\s/g,'');
						answer = answer.toUpperCase();
						if(user_type == answer){
							title = tthis.attr("data-correct");
							tthis.addClass("correct").removeClass("incorrect");
						}else{
							title = tthis.attr("data-incorrect");
							tthis.removeClass("correct").addClass("incorrect");
						}
						tthis.attr("title", title).tooltip("fixTitle").tooltip("show");
					}
				}, 200);
			});
		}else{
			if(_ANSWER_TIME > 0){
				var _aInterval;
				_aInterval = setInterval(function(){
					var _c = $("#question-nav").find("li.active"), _i = $("#flashcard-q" + _c.attr("data-n")).find("input");
					if(_c.next().length > 0)
						setTimeout(function(){_c.next().click()}, 1500);
					else{
						clearInterval(_aInterval);
						$("#submit-homework-modal").modal();
					}
				}, _ANSWER_TIME * 1000);
			}
		}
		
		$("#step-nav").find("li").click(function(){
			var _id = "word-prob-sound-q" + $(this).attr("data-n"), _cctrl = $(this).attr("data-ctrl");
			if($(this).hasClass("active")){
				turn_speaker(_id, "off");
				$(this).removeClass("active");
				$("#word-prob-step-q" + $(this).attr("data-n")).fadeOut(400);
			}else{
				turn_speaker(_id, "on")
				$(this).addClass("active");
				$("#word-prob-step-q" + $(this).attr("data-n")).fadeIn(400);
			}

			if(_cctrl == "1" || _cctrl == "2"){
				$("#step-nav").find("li.active").not(this).each(function(i, v){
					
					turn_speaker("word-prob-sound-q" + $(v).attr("data-n"), "off");
					if(_cctrl == "2"){
						$(v).removeClass("active");
						$("#word-prob-step-q" + $(v).attr("data-n")).fadeOut();
					}
					
					if($(v).attr('data-ctrl') == '2') {
						var c_next = $(v).next().attr('data-ctrl'); 
						var c_prev = $(v).prev();
						if( ( c_prev.attr('data-ctrl') == '1' && c_prev.hasClass('active') ) || ( c_next == '2' || c_next == undefined ) ) {
								$(v).removeClass('active');
								$("#word-prob-step-q" + $(v).attr("data-n")).fadeOut();
						}
					}
						
				});
			}
		});
		
		if(_CMODE == "homework" && _TYPE == 13 ) {
			var _not_click = $('#step-nav').find('li');
			_not_click.not(_not_click.eq(0)).addClass('not-active visited').off('click');
			_not_click.last().addClass("nlast");
		}

		$("#qbox-step-nav").find("li").click(function(){
			var _id = "qbox-step-q" + $(this).attr("data-n");
			if($(this).hasClass("active")){
				$(this).removeClass("active");
				$("#qbox-step-q" + $(this).attr("data-n")).fadeOut(400);
			}else{
				$(this).addClass("active");
				$("#qbox-step-q" + $(this).attr("data-n")).fadeIn(400);
			}
		});

		$("#step-nav, #qbox-step-nav").find("li:first").click();

		$("#submit-homework").click(function(){
			$("#submit-homework-modal").modal();
		});

		$("button.submit-lesson-btn").click(function(){
			var tthis = $(this);
			$("#input-ref").val(tthis.attr("data-ref"));
		});

		$(".formula-steps").each(function(i, v){
			var len = $(v).children().length;
			if(len >= 10){
				$("#homework-content").addClass("math-digit-10");
			}else if(len >= 8){
				$("#homework-content").addClass("math-digit-8");
			}else if(len >= 6){
				$("#homework-content").addClass("math-digit-6");
			}
		});
		
		$('.math-number').each(function() {
			if($(this).text().match(/XX/ig)) {
				$(this).html($(this).text().replace('XX', 'X<sup>2</sup>'));
			}
		});
		
		function homeworkResponsive() {
			var $height = $('header.math-homework-header').outerHeight();
			var $rps 	= $('div.math-homework-sounds');
			$rps.css('height', $height);
		}
		homeworkResponsive();
		$(window).resize(homeworkResponsive);
	});
})(jQuery);