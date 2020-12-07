(function($){
	$(function(){
		$(".view-password").click(function(e){
			e.preventDefault();
			$("#modal-group-id").val($(this).attr("data-group-id"));
			$("#modal-group-name strong").html($(this).attr("data-group-name"));
			$("#modal-group-pass strong").html($(this).attr("data-group-pass"));
			$("#view-password-modal").modal();
		});
            // check only enter numberic text ordering    
                $('.txt-ordering').keyup(function(e){
                    var charCode = (e.which) ? e.which : e.keyCode;
                    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                        $(this).popover({content: '<span class="">'+"please enter numberic!"+'</span>', html: true, placement: "top"});
                        $(this).val("");
                        $(this).popover("show");
                    }
                    else {
                        $(this).popover("destroy")
                    }
                });
            // Click button set ordering    
                $('#btn-set-ordering').click(function(){
                    $('.txt-ordering').each(function() {
                        var id = $(this).attr("data-id");
                        var number = $(this).val();
                        $.get(home_url+"/?r=ajax/set-ordering",{id:id,number:number},function(data){
                        });
                    });
                    location.reload();
                });
		$("#btn-check-pass").click(function(){
			var tthis = $(this);
			var apw = $("#a_pw").val();
			tthis.button("loading");
			$.post(home_url + "/?r=ajax/user/passcheck",{pw: apw}, function(data){
				data = JSON.parse(data);
				tthis.button("reset");
				if(data[0] == 1){
					$("#modal-group-pass").fadeIn();
					$("#p-change").fadeIn();
					$("#p-check").hide();
					$("body").append('<input type="hidden" id="apw" value="' + apw + '">');
				}else{
					$("#p-check").after('<div id="tmp-msg">' + JS_MESSAGES.pw_inc + '</div>');
					setTimeout(function(){$("#tmp-msg").remove()}, 2000);
				}
			});
		});

		$("#btn-change-pass").click(function(){
			var tthis = $(this);
			var _gid = $("#modal-group-id").val();
			var _npw = $("#n_pw").val();
			tthis.button("loading");
			$.post(home_url + "/?r=ajax/group/changepass",{gid: _gid,apw: $("#apw").val(), npw: _npw}, function(data){
				data = JSON.parse(data);
				tthis.button("reset");
				if(data[0] == 1){
					tthis.text(JS_MESSAGES.pw_changed);
					$("#_group-" + _gid).attr("data-group-pass", _npw);
					$("#view-password-modal").modal("hide");
				}else{
					$("#p-check").after('<div id="tmp-msg">' + JS_MESSAGES.pw_change_err + '</div>');
					setTimeout(function(){$("#tmp-msg").remove()}, 2000);
				}
			});
		});

		$('#view-password-modal').on('hidden.bs.modal', function (e) {
			$("#modal-group-pass").hide();
			$("#p-change").hide();
			$("#p-check").show();
			$("#a_pw").val("");
			$("#n_pw").val("");
		});

		$(".view-students").click(function(){
			var tthis = $(this);
			tthis.button("loading");
			$("#group-id").val(tthis.attr("data-gid"));
			var tbody = $("#list-students tbody");
			tbody.html("");
			$.get(home_url + "/?r=ajax/group/students", {gid: tthis.attr("data-gid")}, function(data){
				tthis.button("reset");
				data = JSON.parse(data);
				if(data.length > 0){
					$.each(data, function(i,v){
						var tr = "<tr><td>" + v.name + "</td><td>" + v.email + "</td><td>" + v.joined_date + "</td><td>" + v.done_hw + "</td></tr>";
						tbody.append(tr);
					});
				}else{
					var tr = "<tr><td colspan='4'>" + JS_MESSAGES.empty_group + "</td></tr>";
					tbody.append(tr);
				}
				$("#list-members-modal").modal();
			});
		});

		$(".send_a_message").click(function(){
			var tthis = $(this);
			$("#message_group_id").val(tthis.attr("data-gid"));
			$("#send-message-modal").modal();
		});

		$("[name='remove-assignment']").click(function(){
			$("#cid").val($(this).attr("data-cid"));
		});

		$(".download-report").click(function(){
			$("#input-hrid").val($(this).attr("data-hrid"));
			$("#report-download-link").attr("href", $(this).attr("data-url"));
			$("#txt-score").val($(this).attr("data-score"));
			$("#download-report-modal").modal();
		});

		$("#btn-grade-report").click(function(){
			var tthis = $(this);
			var _s = $("#txt-score")
			if(_s.val().trim() == ""){
				_s.popover({content: '<span class="text-danger">' + _s.attr("data-err-msg") + '</span>', html: true, trigger: "hover", placement: "bottom"}).popover("show");
				setTimeout(function(){_s.popover("destroy")}, 2000);
			}else{
				tthis.button("loading");
				$.post(home_url + "/?r=ajax/grade_homework", {score: _s.val(), hrid: $("#input-hrid").val()}, function(data){
					location.reload();
				});
			}
		});

		$("#deadline").datepicker({minDate: 0, maxDate: "+1M"});

                $("#update-homework-modal").on("click", ".checkboxpagemodal", function () {
                if($(this).is(':checked') ){
                         $(this).val('1');
                     }else{
                        $(this).val('0');
                     }
                 });
                $("#update-homework-modal").on("click", ".checkboxpageadminmodal", function () {
                    if($(this).is(':checked') ){
                       $(this).val('1');
                    }else{
                        $(this).val('0');
                    }
                });


		$(".update-homework").click(function(){
			var _tr = $(this).parents("tr"), dl = $(this).attr("data-deadline"), _tbody = _tr.parent(), _cid = $(this).attr("data-cid"), _adcheck = $(this).attr("data-checkbox-admin"), _teachercheck = $(this).attr("data-checkbox-teacher"), _clink = $(this).attr("data-link"), html = "";
			var _mode = $(this).attr("data-check-practive"); 
                        var _retry = _tr.attr("data-rta");
                        var _check = $(this).parents("tr").find("input.checkboxpage");
                        var _checkadmin = $(this).parents("tr").find("input.checkboxpageadmin");
			_mode == 1 ? $("#for-practice").click() : $("#for-test").click();
			_retry == 1 ? $("#is-retryable-yes").click() : $("is-retryable-no").click();
			$("#_cid").val(_cid);
                        if(_check.val()==1){
                        $('.checkboxpagemodal').prop('checked', true);
                        $('.checkboxpagemodal').val('1');
                    }else{
                        $('.checkboxpagemodal').prop('checked', false);
                        $('.checkboxpagemodal').val('0');
                    }
                        if(_checkadmin.val()==1){
                        $('.checkboxpageadminmodal').prop('checked', true);
                        $('.checkboxpageadminmodal').val('1');
                    }else{
                        $('.checkboxpageadminmodal').prop('checked', false);
                        $('.checkboxpageadminmodal').val('0');
                    }
                        
			$("#homework-name").val(_tr.attr("data-name"));
			$("#lab").html( 'Homework Name :'+ ' ' +_tr.attr("data-name"));
			if(dl != "0000-00-00"){
				$("#deadline").datepicker("setDate", dl);
			}else{
                            $( "#deadline" ).datepicker({
                                defaultDate: null
                            });
			}

			if(_tbody.children().length > 0){
				html += "<tr><td>" +
							"<div class='radio radio-style1'>" +
								"<input id='_l0' name='link-id' value='0' type='radio'>" +
								"<label for='_l0'>" + JS_MESSAGES.empty_op + "</label>" +
							"</div></td></tr>";
			}
			$.each(_tbody.children(), function(i,v){
				var id = $(v).attr("data-id");
				var name = $(v).attr("data-name");
				if(_cid != id){
					html += "<tr><td>" +
								"<div class='radio radio-style1'>" +
									"<input id='_l" + id + "' name='link-id' value='" + id + "' type='radio'>" +
									"<label for='_l" + id + "'>" + $(v).find("td:first").html() + "</label>" +
									name +
								"</div></td></tr>";
				}
			});
			if(html != ""){
				$("#link-homework-tbl").html(html);
			}
			$("#_l" + _clink).prop("checked", true);
			$("#update-homework-modal").modal();
		});

		$("#reset-deadline").click(function(){
			$("#deadline").datepicker("setDate", null);
		});
	});
})(jQuery);