(function($){
	$(function(){
		if(window.location.hash != ""){
			var _v = window.location.hash.replace("#", "");
			$("input[name='subscription-type'][value=" + _v + "]").prop("checked", true);
			show_subscription_panel(_v);
		}else{
			show_subscription_panel($("[name='subscription-type']:checked").val());
		}	

		$("[name='subscription-type']").change(function(){
			show_subscription_panel($(this).val());
		});

		function show_subscription_panel(id){
			id = parseInt(id);
                        
			$("#addi-sub-type").val(id);
			switch(id){
                                case 0: 
                                        $("#self-study-detail").slideUp();
					$("#teacher-detail").slideUp();
                                        $("#self-study-detail-math").slideUp();
                                        $("#teacher-detail-math").slideUp();
					$("#dictionary-detail").slideUp();
					$("#sat-detail").slideUp();
					$("#sat-i-detail").slideUp();
					$("#sat-ii-detail").slideUp();
					$("#purchase-points-detail").slideDown();
					$("#purchase-points-detail-math").slideUp();
					break;
				case 1: $("#self-study-detail").slideUp();
                                        $("#self-study-detail-math").slideUp();
					$("#dictionary-detail").slideUp();
					$("#sat-detail").slideUp();
					$("#purchase-points-detail").slideUp();
                                        $("#purchase-points-detail-math").slideUp();
					$("#sat-i-detail").slideUp();
					$("#sat-ii-detail").slideUp();
					$("#teacher-detail").slideDown();
                                        $("#teacher-detail-math").slideUp();
					break;
				case 6: $("#self-study-detail").slideUp();
                                        $("#self-study-detail-math").slideUp();
					$("#dictionary-detail").slideUp();
					$("#sat-detail").slideUp();
					$("#purchase-points-detail").slideUp();
                                        $("#purchase-points-detail-math").slideUp();
					$("#sat-i-detail").slideUp();
					$("#sat-ii-detail").slideUp();
                                        $("#teacher-detail").slideUp();
					$("#teacher-detail-math").slideDown();
					break;
				case 2: $("#self-study-detail").slideUp();
					$("#teacher-detail").slideUp();
                                        $("#self-study-detail-math").slideUp();
                                        $("#teacher-detail-math").slideUp();
					$("#sat-detail").slideUp();
					$("#purchase-points-detail").slideUp();
                                        $("#purchase-points-detail-math").slideUp();
					$("#sat-i-detail").slideUp();
					$("#sat-ii-detail").slideUp();
					$("#dictionary-detail").slideDown();
					break;
				case 3: $("#self-study-detail").slideUp();
					$("#teacher-detail").slideUp();
                                        $("#self-study-detail-math").slideUp();
                                        $("#teacher-detail-math").slideUp();
					$("#dictionary-detail").slideUp();
					$("#purchase-points-detail").slideUp();
                                        $("#purchase-points-detail-math").slideUp();
					$("#sat-i-detail").slideUp();
					$("#sat-detail").slideDown();
					break;
				case 4: $("#self-study-detail").slideUp();
					$("#teacher-detail").slideUp();
                                        $("#self-study-detail-math").slideUp();
                                        $("#teacher-detail-math").slideUp();
					$("#dictionary-detail").slideUp();
					$("#sat-detail").slideUp();
					$("#sat-i-detail").slideUp();
					$("#sat-ii-detail").slideUp();
					$("#purchase-points-detail").slideUp();
					$("#purchase-points-detail-math").slideDown();
					break;
				case 5: $("#teacher-detail").slideUp();
					$("#self-study-detail-math").slideUp();
                                        $("#teacher-detail-math").slideUp();
					$("#dictionary-detail").slideUp();
					$("#sat-detail").slideUp();
					$("#purchase-points-detail").slideUp();
                                        $("#purchase-points-detail-math").slideUp();
					$("#sat-i-detail").slideUp();
					$("#sat-ii-detail").slideUp();
					$("#self-study-detail").slideDown();
					break;
				case 9: $("#teacher-detail").slideUp();
                                        $("#teacher-detail-math").slideUp();
					$("#dictionary-detail").slideUp();
					$("#sat-detail").slideUp();
					$("#purchase-points-detail").slideUp();
                                        $("#purchase-points-detail-math").slideUp();
					$("#sat-i-detail").slideUp();
					$("#sat-ii-detail").slideUp();
					$("#self-study-detail-math").slideDown();
                                        $("#self-study-detail").slideUp();
					break;
				case 7: $("#self-study-detail").slideUp();
					$("#teacher-detail").slideUp();
                                        $("#self-study-detail-math").slideUp();
                                        $("#teacher-detail-math").slideUp();
					$("#purchase-points-detail").slideUp();
                                        $("#purchase-points-detail-math").slideUp();
					$("#sat-ii-detail").slideUp();
					$("#sat-i-detail").slideDown();
					break;
				case 8: $("#self-study-detail").slideUp();
					$("#teacher-detail").slideUp();
                                        $("#self-study-detail-math").slideUp();
                                        $("#teacher-detail-math").slideUp();
					$("#purchase-points-detail").slideUp();
                                        $("#purchase-points-detail-math").slideUp();
					$("#sat-i-detail").slideUp();
					$("#sat-ii-detail").slideDown();
					break;
			}
		}

		$("#val-credit-code").click(function(){
			var tthis = $(this);
			var $c = $("#credit-code");
			var code = $c.val().trim();
			$('#activation-code').val('');
			if(code == ""){
				$c.popover({content: '<span class="text-danger">' + tthis.attr("data-error-text") + '</span>', html: true, trigger: "hover", placement: "bottom"})
				.popover("show");
				setTimeout(function(){$c.popover("destroy")}, 1500);
				return false;
			}
			tthis.button("loading");
			$.post(home_url + "/?r=ajax/validatecredit",{c: code}, function(data){
				data = JSON.parse(data);
				if(data.status == 0){
					$("#credit-error-dialog .error").html('<span class="icon-error"></span>' + data.title);
					$("#credit-error-dialog .error-msg").text(data.msg);
					$("#credit-error-dialog").modal();
				}else{
					if(data.did == 0 && data.ltype != 3){
						$("#choose-dictionary-block").show();
					}else{
						$("choose-dictionary-block").hide();
					}
					$("#ltype").val(data.ltype);
					if(data.ltype == 1 || data.ltype == 6){
						$("#teacher-tool-block").show();
					}else{
						$("#teacher-tool-block").hide();
					}
					$("#add-code-dialog").modal();
				}
				tthis.button("reset");
			});
		});
		
		$('#apply-credit-code').click(function() {
			var activation_code = $(this).attr('data-active').trim();
			var tthis = $(this);
			tthis.parents('#site-messages-modal').modal('hide');
			$('#credit-code').val('');
			$('#activation-code').val(activation_code);
			if(activation_code == '') {
				return false;
			}
			$.post(home_url + "/?r=ajax/validatecredit",{c: activation_code}, function(data){ 
				if(data.did == 0 && data.ltype != 3){
					$("#choose-dictionary-block").show();
				}else{
					$("choose-dictionary-block").hide();
				}
				$("#ltype").val(data.ltype);
				if(data.ltype == 1 || data.ltype == 6){
					$("#teacher-tool-block").show();
				}else{
					$("#teacher-tool-block").hide();
				}
				$("#add-code-dialog").modal();
			});
		});

		$("#credit-code").focus(function(){
			$(this).popover("destroy");
		});

		$("#pop-starting-date").datepicker({minDate: 0}).datepicker("setDate", new Date());

		$("#choose-dictionary").change(function(){
			$("#dictionary-id").val($(this).val());
		});

		$("#submit-code").click(function(){
			var $valid = true;
			if($("#choose-dictionary-block").is(":visible") && $("#dictionary-id").val() == ""){
				var $choose_dict = $("#choose-dictionarySelectBoxItContainer");
				$choose_dict.popover({content: '<span class="text-danger">' + DICT_EMPTY_ERR + '</span>', html: true, trigger: "hover", placement: "bottom"})
				.popover("show");
				setTimeout(function(){$choose_dict.popover("destroy")}, 2000);
				$valid = false;
			}

			$("#starting-date-txt").val($("#pop-starting-date").val());

			var $gid, $selbox, $tgname, $tgpass;
			if($("#ltype").val() == "1" || $("#ltype").val() == "6"){
				$gid = $("#pop-sel-group-teacher");
				$selbox = $("#pop-sel-group-teacherSelectBoxItContainer");
				$tgname = $("#pop-gname-teacher");
				$tgpass = $("#pop-gpass-teacher");

				if($gid.val() == "" && $tgname.val().trim() == ""){
					$selbox.popover({content: '<span class="text-danger">' + GRP_EMPTY_ERR + '</span>', html: true, trigger: "hover", placement: "bottom"})
					.popover("show");
					setTimeout(function(){$selbox.popover("destroy")}, 2000);
					$valid = false;
				}
				if($tgname.val().trim() != "" && $tgpass.val().trim() == ""){
					$tgpass.popover({content: '<span class="text-danger">' + GRP_PW_ERR + '</span>', html: true, trigger: "hover", placement: "bottom"})
					.popover("show");
					setTimeout(function(){$tgpass.popover("destroy")}, 2000);
					$valid = false;
				}
				$("#assoc-group").val($gid.val());
				$("#group-name").val($tgname.val());
				$("#group-pass").val($tgpass.val());
			}

			if(!$valid){return false}
			$("#main-form").submit();
		});

		$(".choose-sub-btn").click(function(){
			var tthis = $(this);
			var t = parseInt($("[name='subscription-type']:checked").val());
			$("#sub-id").val(0);
			$("#student_num").prop("disabled", false);
			$("#sel-dictionary").data("selectBox-selectBoxIt").enable().selectOption(0);
			$("#sel-teacher-tool").data("selectBox-selectBoxIt").enable().selectOption(0);
			$("#num-of-months-lbl").text(LBL_NO_M);
			$("#addi-sub-type").val(t);
			switch(t){
				case 0:
                                        $("#purchase-points-dialog").modal();
					break;
				case 1:
				case 6:
					$("#teacher-sub-details-dialog").modal();
					break;
				case 2: 
					var $modal = $("#additional-subscription-dialog");
					var $modal_title = $modal.find(".modal-header h3");
					$modal_title.text($modal_title.attr("data-ds-text"));
					$("#selected-group-label").hide();
					$("#num-of-student-lbl").text(LBL_NO_USERS);
					$("#student_num").val(1).attr("min", 1);
					calc_total_price();
					$modal.modal();
					break;
				case 3:
				case 7:
				case 8:
					var typeid = parseInt(tthis.attr("data-type"));
					$("#sat-sub-type").val(t);
					switch(typeid){
						case 3: $("#sat-test-block").show(); break;
						case 10: $("#sat-test-i-block").show(); $("#sat-test-ii-block").hide(); break;
						case 16: $("#sat-test-ii-block").show(); $("#sat-test-i-block").hide(); break;
						default: $("#sat-test-block").hide();
							$("#sat-test-i-block").hide();
							$("#sat-test-ii-block").hide();
							break;
					}
					$("#sat-class").val(typeid);
					$("#selected-class").text(tthis.attr("data-sat-class"));
					$("#sel-sat-months").data("selectBox-selectBoxIt").selectOption(0);
					calc_sat_total_price();
					$("#sat-subscription-dialog").modal();
					break;
				case 4:
					$("#purchase-points-dialog").modal();
					break;
				case 5:
                                        $("#ss-dict-block").show();
					$("#self-study-subscription-dialog").modal();
					calc_self_study_price();
					break;
				case 9:
					$("#ss-dict-block").hide();
					$("#self-study-math-subscription-dialog").modal();
					calc_self_study_price();
					break;
			}
		});

		$("#sub-continue").click(function(){
			var tthis = $(this);
			var $sub_modal = $("#additional-subscription-dialog");
			var $modal_title = $sub_modal.find(".modal-header h3");
			var subtype = parseInt($("[name='subscription-type']:checked").val());
			var $valid = true, $waiting = false, $gid, $selbox, $tgname, $tgpass;
			$("#sub-id").val(0);
			
			$(".tops").remove();
			$("#sel-teacher-tool").data("selectBox-selectBoxIt").enable().refresh();
			$("#student_num").prop("disabled", false).val($("#student_num").attr("data-min")).attr("min", $("#student_num").attr("data-min"));
			$("#num-of-months-lbl").text(LBL_NO_M);
			$("#total-amount").text(0);

			$("#selected-group-label").show();
			$modal_title.text($modal_title.attr("data-ts-text"));
			$("#num-of-student-lbl").text(LBL_NO_STUDENTS);
			$gid = $("#sel-group-teacher");
			$selbox = $("#sel-group-teacherSelectBoxItContainer");
			$tgname = $("#teacher-gname");
			$tgpass = $("#teacher-gpass");
			if($gid.val() == "" && $tgname.val().trim() == ""){
				$selbox.popover({content: '<span class="text-danger">' + GRP_EMPTY_ERR + '</span>', html: true, trigger: "hover", placement: "bottom"}).popover("show");
				setTimeout(function(){$selbox.popover("destroy")}, 2000);
				$valid = false;
			}
			if($tgname.val().trim() != "" && $tgpass.val().trim() == ""){
				$tgpass.popover({content: '<span class="text-danger">' + GRP_PW_ERR + '</span>', html: true, trigger: "hover", placement: "bottom"}).popover("show");
				setTimeout(function(){$tgpass.popover("destroy")}, 2000);
				$valid = false;
			}

			var gn = $tgname.val();
			if(gn == ""){
				gn = $("#sel-group-teacher :selected").text();
			}else{
				tthis.button("loading");
				$waiting = true;
				$.post(home_url + "/?r=ajax/group/availability", {gn: gn}, function(data){
					if(parseInt(data) == 0){
						$tgname.popover({content: '<span class="text-danger">' + GRP_EXIST_ERR + '</span>', html: true, trigger: "hover", placement: "bottom"}).popover("show");
						setTimeout(function(){$tgname.popover("destroy")}, 2000);
						$valid = false;
					}
					tthis.button("reset");
					if($valid){
						$("#teacher-sub-details-dialog").modal("hide")
							.one("hide.bs.modal", function(){
								calc_total_price();
								$sub_modal.modal();
							});
					}
				});
			}
			$("#addi-selected-group").text(gn);
			$("#addi-gid").val($gid.val());
			$("#addi-gname").val($tgname.val());
			$("#addi-gpass").val($tgpass.val());

			if($valid && !$waiting){
				$("#teacher-sub-details-dialog").modal("hide")
					.one("hide.bs.modal", function(){
						calc_total_price();
						$sub_modal.modal();
					});
			}
		});

		$(".sel-sat-class").change(function(){
			$("#sat-class").val($(this).val());
		});

		$(".extend-sub-btn").click(function(){
			var data = $(this).parent();
			var subtype = data.attr("data-type");
			$("#addi-sub-type").val(subtype);
			$("[name='sub-id']").val(data.attr("data-subid"));
			if(subtype != "3" && subtype != "7" && subtype != "8"){
				var $modal = $("#additional-subscription-dialog");
				var $modal_title = $modal.find(".modal-header h3");
				$modal_title.text($modal_title.attr("data-ext-text"));
				$("#sel-dictionary").data("selectBox-selectBoxIt").selectOption(data.attr("data-did")).disable();
				$(".tops").remove();
				var os = '<option value="1" class="tops">1 ' + M_SINGLE + '</option><option value="2" class="tops">2 ' + M_PLURAL + '</option>';
				var $el = $("#sel-teacher-tool"); $el.prepend(os); $el.val(data.attr("data-months")); var $elbox = $el.data("selectBox-selectBoxIt"); $elbox.refresh();
				if((subtype == "1" || subtype == "6") && typeof $(this).attr("data-task") !== "undefined"){
					$("#selected-group-label").show();
					$("#num-of-student-lbl").text(LBL_NO_STUDENTS_ADD);
					$("#student_num").prop("disabled", false).attr("min", 1);
					$("#addi-selected-group").text(data.attr("data-group"));
					$("#num-of-months-lbl").text(LBL_NO_M_REMAIN);
					$elbox.disable();
				}else{
					if(subtype == "1"){
						$("#selected-group-label").show();
						$("#addi-selected-group").text(data.attr("data-group"));
					}else{
						$("#selected-group-label").hide();
					}
					$("#num-of-student-lbl").text(LBL_NO_USERS);
					$("#student_num").val(data.attr("data-size")).prop("disabled", true);
					$("#num-of-months-lbl").text(LBL_NO_M_ADD);
					$el.data("selectBox-selectBoxIt").enable();
				}
				calc_total_price();
				$modal.modal();
			}else{
				$("#selected-class").text(data.attr("data-sat-class"));
				$("#sat-class").val(data.attr("data-sat-class-id"));
				$("#sat-test-block").hide();
				calc_sat_total_price();
				$("#sat-subscription-dialog").modal();
			}
		});

		$("#sel-teacher-tool, #student_num, #sel-dictionary").change(function(){
			calc_total_price();
		});

		$("#sel-sat-months").change(function(){
			calc_sat_total_price();
		});

		$("#sel-self-study-months").change(function(){
			calc_self_study_price();
		});

		function calc_sat_total_price(){
			var p;
			switch($("#sat-class").val()){
				case "1": p = satGp; break;
				case "2": p = satWp; break;
				case "3": case "4": case "5": case "6": case "7": p = satStp; break;
				case "9": case "10": case "11": case "12": case "13": case "14": p = satMIP; break;
				case "15": case "16": case "17": case "18": case "19": case "20": p = satMIIP; break;
			}
			$("#total-amount-sat").text(parseInt($("#sel-sat-months").val()) * p);
		}

		function calc_total_price(){
			var students = isNaN(parseInt($("#student_num").val())) ? 0 : parseInt($("#student_num").val());
			var months = isNaN(parseInt($("#sel-teacher-tool").val())) ? 0 : parseInt($("#sel-teacher-tool").val());
			switch($("#addi-sub-type").val()){
				case "1":
				case "6":
					$("#total-amount").text(students * months * ttp / 100);
					break;
				case "2":
					var p = $("#sel-dictionary").val() == "6" ? adp : dp;
					$("#total-amount").text(students * months * p / 100);
					break;
				case "5":
				case "9":
					$("#total-amount").text(months * ssp);
					break;
			}
		}

		function calc_self_study_price(){
			var months = parseInt($("#sel-self-study-months").val());
			$("#ss-total-amount").text(months * ssp);
		}

		$("#add-to-cart").click(function(){
			var $valid = true, $subid = $("#sub-id").val();
			if($("#addi-sub-type").val() != "6" && $("#sel-dictionary").val() == ""){
				var $selbox = $("#sel-dictionarySelectBoxItContainer");
				$selbox.popover({content: '<span class="text-danger">' + DICT_EMPTY_ERR + '</span>', html: true, trigger: "hover", placement: "bottom"}).popover("show");
				setTimeout(function(){$selbox.popover("destroy")}, 2000);
				$valid = false;
			}

			if($("#sub-teacher-tool").is(":checked") && $("#sel-teacher-tool").val() == ""){
				var $selbox = $("#sel-teacher-toolSelectBoxItContainer");
				$selbox.popover({content: '<span class="text-danger">' + M_EMPTY_ERR + '</span>', html: true, trigger: "hover", placement: "bottom"}).popover("show");
				setTimeout(function(){$selbox.popover("destroy")}, 2000);
				$valid = false;
			}

			var $txt_student = $("#student_num");
			if($subid == 0 && $("#addi-sub-type").val() != "5" && $("#addi-sub-type").val() != "2" && ($txt_student.val() == "" || parseInt($txt_student.val()) < min_student)){
				$txt_student.popover({content: '<span class="text-danger">' + NUMBER_INV + '</span>', html: true, trigger: "hover", placement: "bottom"}).popover("show");
				setTimeout(function(){$txt_student.popover("destroy")}, 2000);
				$valid = false;
			}

			return $valid;
		});

		$("#add-to-cart-ss").click(function(){
			if($("#self-study-sub").val() == "5" && $("#sel-dictionary2").val() == ""){
				var $selbox = $("#sel-dictionary2SelectBoxItContainer");
				$selbox.popover({content: '<span class="text-danger">' + DICT_EMPTY_ERR + '</span>', html: true, trigger: "hover", placement: "bottom"}).popover("show");
                        setTimeout(function(){$selbox.popover("destroy")}, 2000);
				return false;
			}
		});

		$("[name='app-instructions']").change(function(){
			if($(this).is(":checked")){
				$(".app-ins").slideUp();
				$("#app-s" + $(this).val() + "-in").slideDown();
			}
		});

		$("#instructions-dialog").on("show.bs.modal", function(){
			$(".app-ins").hide();
		});

		$(".worksheets-preview").click(function(e){
			e.preventDefault();
			var modal = $("#subscribed-worksheets-dialog");
			if(modal.find("tbody").children().length == 0){
				$.get(home_url + "/?r=ajax/worksheet/get",{type: 3, assignment_name: 1, grade_name: 1, is_math: _ISMATH}, function(data){
					var it = JSON.parse(data);
					$('span#omg_sub-total').text(it.length);
					$.each(it, function(i, v){
						modal.find("tbody").append("<tr><td>" + v.aname + "</td><td>" + v.grade + "</td><td>" + v.name + "</td></tr>");
					});
				});
			}
			modal.modal();
		});
                $(".worksheets-preview-math").click(function(e){
			e.preventDefault();
			var modal = $("#subscribed-worksheets-dialog-math");
			if(modal.find("tbody").children().length == 0){
				$.get(home_url + "/?r=ajax/worksheetmath/get",{type: 3, assignment_name: 1, grade_name: 1, is_math: _ISMATH}, function(data){
					var it = JSON.parse(data);
					$('span#omg_sub-total').text(it.length);
					$.each(it, function(i, v){
						modal.find("tbody").append("<tr><td>" + v.aname + "</td><td>" + v.grade + "</td><td>" + v.name + "</td></tr>");
					});
				});
			}
			modal.modal();
		});
		
		if(_IM4 != 0) {
			$('#sub-purchase-points').click();
			$('#no-of-points').val(_IM4);
			$('#total-amount-points').text(_IM4);
			show_subscription_panel(4);
		}
	});
})(jQuery);