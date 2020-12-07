(function($){
	$(function(){
		$("#search-form").submit(function(e){
			e.preventDefault();
			window.location.href = home_url + "/?r=dictionary/" + dictionary_slug + "/" + $("#keyword").val();
		});

		$(".history-see-more").click(function(e){
			e.preventDefault();
			$("#search-history-modal").modal();
		});

		$(".xbtn").click(function(){
			var e = $(this).attr("data-entry");
			$.post(home_url + "/?r=ajax/history/remove",{id: e, d: dictionary_slug});
			$("[data-entry='" + e + "']").parent().remove()
		});

		var timer;
		$("#keyword").keyup(function(){
			var kw = $("#keyword");
			if(kw.val().trim() ==""){return false;}
			clearTimeout(timer);
			timer = setTimeout(function(){
				$.get(
					home_url + "/?r=ajax/dictionary",
					{d: dictionary_slug, w: kw.val()},
					function(data){
						$("#seach-results").html(data);
						$("#seach-results").show();						
					}
				);
				kw.focus();
			}, 600);
		});

		$("#keyword").blur(function(){
			setTimeout(function(){$("#seach-results").hide();}, 300);			
		});

		$(".english-quiz-tab-sm, .english-quiz-tab").click(function(e){
			e.preventDefault();
			$(".quiz-box").removeClass().addClass("quiz-box quiz-english");
			$("#quiz-header-title").text("English");
			$(".quiz-get-more").show();
			$("#quiz-answer").text("");
			trivia = 1;
			get_quiz();
		});

		$(".science-quiz-tab-sm, .science-quiz-tab").click(function(e){
			e.preventDefault();
			$(".quiz-box").removeClass().addClass("quiz-box quiz-science");
			$("#quiz-header-title").text("Science");
			$(".quiz-get-more").hide();
			$("#quiz-answer").text("");
			trivia = 2;
			get_quiz();
		});

		$(".history-quiz-tab-sm, .history-quiz-tab").click(function(e){
			e.preventDefault();
			$(".quiz-box").removeClass().addClass("quiz-box quiz-history");
			$("#quiz-header-title").text("History");
			$(".quiz-get-more").hide();
			trivia = 3;
			$("#quiz-answer").text("");
			get_quiz();
		});

		$(".general-quiz-tab-sm, .general-quiz-tab").click(function(e){
			e.preventDefault();
			$(".quiz-box").removeClass().addClass("quiz-box quiz-general");
			$("#quiz-header-title").text("General");
			$(".quiz-get-more").hide();
			$("#quiz-answer").text("");
			trivia = 4;
			get_quiz();
		});

		$("#next-quiz").click(function(e){
			e.preventDefault();
			$("#quiz-answer").text("");
			get_quiz();
		});

		$("#get-answer").click(function(e){
			e.preventDefault();
			$("#quiz-answer").text(trivia_a);
		});

		$("#save-flash-cards").click(function(){
			if(!parseInt($(this).attr("data-islogin"))){
				$("#require-modal .modal-header h3").html(JS_MESSAGES.login_req_h);
				$("#require-modal .modal-body").html(JS_MESSAGES.login_req_err);
				$("#require-modal .modal-footer a").attr("href", home_url + "/?r=login").html('<span class="icon-user"></span> ' + JS_MESSAGES.login_req_lbl);
				$("#require-modal").modal();
				return;
			}
			$("#save-flashcard-modal").modal();
		});

		$("#add-flashcard").click(function(){
			var tthis = $(this);
			var fid = $("[name='flashcard-folders']:checked").val();
			var scrolldiv = tthis.parents(".modal-content").find(".scroll-list3");
			if(typeof fid == "undefined"){
				scrolldiv.attr("title", JS_MESSAGES.folder_sel).tooltip("fixTitle").tooltip("show");
				setTimeout(function(){scrolldiv.tooltip("hide")}, 1500);
				return;
			}
			tthis.button("loading");
			$.post(home_url + "/?r=ajax/flashcard/addcard",{did: dictionary_slug, e: entry, fid: fid}, function(data){
				data = JSON.parse(data);
				tthis.button("reset");
				if(data.status == 1){
					$("#save-flashcard-modal").modal("hide");
				}else if(data.status == 2){
					scrolldiv.attr("title", JS_MESSAGES.fc_sub_req).tooltip("fixTitle").tooltip("show");
				}
			});
		});

		$("#fc-folder-form").click(function(){
			$("#save-flashcard-modal").modal("hide")
				.one("hidden.bs.modal", function(){
					if(annoying){
						$("#require-modal").modal();
					}else{
						$("#create-folder-modal").modal();
					}
				});
		});

		$("#create-folder-modal").on("shown.bs.modal", function(){
			$("#fc-folder-name").focus();
		});

		$("#create-flashcard-folder").click(function(){
			var fc_name = $("#fc-folder-name");
			var tthis = $(this);
			if(fc_name.val().trim() != ""){
				tthis.button("loading");
				$.post(home_url + "/?r=ajax/flashcard/addfolder",{did: dictionary_slug,n: fc_name.val()}, function(data){
					var fid = JSON.parse(data);
					var ele = '<li>' +
								'<div class="radio radio-style2">' +
									'<input id="folder_' + fid[0] + '" type="radio" name="flashcard-folders" value="' + fid[0] + '" checked>' +
									'<label for="folder_' + fid[0] + '">' + fc_name.val() + '</label>' +
								'</div>' +
							'</li>';
					$(".flashcard-folders").append(ele);
					fc_name.val("");
					tthis.button("reset");
				});
			}
			$("#create-folder-modal").modal("hide");
			$("#create-folder-modal").one("hidden.bs.modal", function(){
				$("#save-flashcard-modal").modal();
			});
		});

		$(".scroll-list3").mCustomScrollbar({
			theme: "rounded",
			mouseWheel:{scrollAmount:120}
		});
	});
})(jQuery);

function get_quiz()
{
	jQuery("#quiz-loader").fadeIn();
	jQuery.getJSON(home_url + "/?r=ajax/randomquiz",
		{
			d: dictionary_slug,
			c: trivia
		},
		function(data){
			jQuery("#quiz-loader").fadeOut();
			jQuery("#quiz-qe").html(data.quiz.q);
			jQuery("#quiz-q").html(data.quiz.sentence);
			shuffle(data.quiz.choice);
			jQuery("#quiz-a-1").html('<span class="q-sn">(<span class="semi-bold">A</span>)</span> ' + data.quiz.choice[0]);
			jQuery("#quiz-a-2").html('<span class="q-sn">(<span class="semi-bold">B</span>)</span> ' + data.quiz.choice[1]);
			jQuery("#quiz-a-3").html('<span class="q-sn">(<span class="semi-bold">C</span>)</span> ' + data.quiz.choice[2]);
			if(trivia == 1){
				jQuery("#quiz-level").text(data.level);
				jQuery("#quiz-lesson").text(data.lesson);
			}
			trivia_a = data.quiz.ca;
		}
	);
}