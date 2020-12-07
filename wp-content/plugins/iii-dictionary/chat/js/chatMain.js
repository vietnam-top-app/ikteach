var socket = io.connect('http://107.180.78.211:8000');
var message = {
    _content: '',
    _time: '',
    _from: '',
    _to: ''
};
var current_connect = '';
var last_connect = '';
var queu_connect = []; //open muilti connect at this time, so just accept one connect at this time.
var accept_connect = {'_from': '', '_to': ''};
var open_notepad = 0;
//const 
var HAVE_CONNECT_CLASS = 'have-connect';
(function ($) {
    $(document).ready(function () {
        if (__IS == 1) {
            $('#open-chat-btn').click();
        }
        socket.emit('set_config', {'id': __US, 'name': __NAME});
    });

//	$('#block-chat').draggable({ cancel: '.block-content, #modal-notice-close'});

    $('#open-notepad-btn').click(function () {
        open_notepad = (open_notepad) ? 0 : 1;
    });
    /* SECTION FOR MATH HOMEWORK */
    $('#btnClose').click(function () {
        $.ajax('http://ikteacher.com/?r=ajax/chat/notice', {
            method: 'post',
            data: {id: 1},
        }).done(function (data) {
            $('.notice-content').html('').append(data);
            $('.modal-notice-close').slideDown('slow');
        });
    });
    jQuery("body").on("keyup", "#txt-chat", function (event) {
        if (event.which == 13 || event.keyCode == 13) {
            $('#btn-send').click();
        }
    });
    jQuery("body").on("click", "#wp-live-chat-close", function () {
        if ($('#wp-live-chat-2').css('display') == 'block')
        {
            $.ajax('http://ikteacher.com/?r=ajax/chat/notice', {
                method: 'post',
                data: {id: 1},
            }).done(function (data) {
                $('.notice-content').html('').append(data);
                $('#modal-notice-close').slideDown('slow');
            });
        } else {
            $('#modal-notice-close').hide();
        }
    });
    $(document).on('click', '.btn-quit', function () {
        $('.modal-notice-close').hide();
        if(typeof idroom === "undefined"){
            location.reload();
        }else{
            if (idroom != null && idroom != '') {
                if (jQuery('#wp-live-chat-2:visible').length == 1) {

                    $.ajax('http://ikteacher.com/?r=ajax/chat/notice', {
                        method: 'post',
                        data: {id: 7, _id: idroom}
                    }).done(function (data) {
                        $.ajax('http://ikteacher.com/?r=ajax/chat/update_quit_status', {
                            method: 'post',
                            data: {id: 2, _id: idroom}
                        }).done(function (data) {
                        });
                        socket.emit('quit_message', {'idroomquit': idroom, 'idteacher': __US, 'idstudent': idstudent, 'email': EMAILSTUDENT,'idstop':1});
                        document.getElementById('wp-live-chat-2').style.display = "none";
                        $('#parent').css('margin-left', '');
                    });

                }
            }
        }
//        socket.emit('stop_time', {'idroomquit': idroom, 'idteacher': __US, 'idstudent': idstudent });

    });
    socket.on('update_quit_message', function (data) {
        if (data.idstudent == idstudent && data.idroomquit == idroom && data.idteacher==__US && data.idstop==0) {
            var html1 = '<em>' + EMAILSTUDENT + ' has closed and ended the chat</em><div class="wplc-clear-float-message"></div>';
            jQuery("#wplc_chatbox").prepend(html1);
            var html2 = '<em>[System Message} Your tutoring connection is closed</em><div class="wplc-clear-float-message"></div>';
            jQuery("#wplc_chatbox").prepend(html2);
            alert('Tutoring stopped by the student.');
        }
    });
    $(document).on('click', '.btn-close-md', function () {
        $('#modal-notice-close').slideUp('slow');
    });

    $(document).on('click', '#open-chat-btn,#btn-chat-quit, .btn-quit', function () {
        $('#block-chat').toggleClass('opened');
        document.getElementById('wp-live-chat-2').style.display = 'none';
        var isVisible = $('#block-chat').is('.opened');
        if (!isVisible) {
            socket.emit('remove_user', {'id': __US});
        } else {
            if (!isNaN(parseInt(__US))) {
                socket.emit('push_user', {
                    'id': __US,
                    'name': __NAME,
                    'email': __EMAIL,
                    'is': __IS,
                    'can': __IS
                });
            }
        }
    });
    $(document).on('click', '.btn-cancel-session', function () {
        $.ajax('http://ikteacher.com/?r=ajax/chat/cancel_session', {
            method: 'post',
            data: {'sid': __SID, 'uid': __US}
        }).done(function () {
            $('.block-popup').slideToggle('slow');
        });
    });

    $('body').on('click', '.btn-close-bp', function () {
        $('.block-popup').slideToggle('slow');
    });
    $('#btn-chat-accept').click(function () {
        $.ajax('http://ikteacher.com/?r=ajax/chat/request', {
            method: 'post',
            data: {'id': __US, 'return': __URL, 'sid': __SID, 'check': false},
            beforeSend: function () {
                $('#btn-chat-accept').button('loading')
            }
        }).done(function (data) {
            $('#btn-chat-accept').button('reset');
            $('.block-popup-content').html('').append(data.html);
            $('.block-popup').slideDown('slow');
        });
    });

    /*SECTION FOR TEACHING*/
    $('.tt-btn-accept').click(function (event) {
        event.preventDefault();
        var $tthis = $(this),
                $_teacher_id = $tthis.attr('data-teacher'),
                $_id = $tthis.attr('data-session'),
                $_return = $tthis.attr('data-url'),
                __return = $tthis.attr('data-url'),
                $_user_id = $tthis.attr('data-user'),
                $_user_name = $tthis.attr('data-user-name'),
                $_sheet_id = $tthis.attr('data-sheet');
        $_online = $tthis.attr('data-online');
        if ($_online == '1') {
            if (confirm('Do you accept the student request?')) {
                $.ajax('http://ikteacher.com/?r=ajax/chat/update_session', {
                    method: 'post',
                    data: {'id': $_id, 'teacher_id': $_teacher_id, 'user_id': $_user_id}
                }).done(function () {
                    socket.emit('teacher_accept_request', {
                        __SID: $_sheet_id,
                        __US: $_user_id,
                        __IDROOM: $_id,
                        idteacher: __US,
                        name : __NAME,
                        email: __EMAIL,
                        class:'is_teacher',
                    });
                    window.location.href = __return;
                });
            } else {

            }
        } else {
            confirm('Sorry, this student withdrew the request.');
        }

        return false;
    });
    $(document).on('click', '#auto-open-chat', function () {
        jQuery("#modal-notice").hide();
    });
    $(document).on('click', '#hidden-modal-start', function () {
        jQuery("#modal-notice-start").hide();
    });
//    var str = window.location.href;
//        var n = str.indexOf("Draw.html");
//        if (n != -1) {
//            var span = document.getElementsByClassName("close")[0];
//                span.onclick = function() {
//                jQuery("#modal-notice").hide();
//            }
//        }
    socket.on('get_room', function (data) {
        if(data.email==__EMAIL){
        idroom = data.idroom;
        $('#sl-reciplent').attr('IDROOM', data.idroom);
        jQuery('#open-notepad-btn').click();
        jQuery("#modal-notice-start").hide();
    }
    });
    socket.on('update_list_rmv', function (data) {
        $('#sl-reciplent option[value="' + data.id + '"]').remove();
        $('#sl-reciplent').change();
        $.ajax('http://ikteacher.com/?r=ajax/chat/clear_session', {
            method: 'post',
            data: {'uid': data.id, 'sid': __SID}
        });
    });

    /*SECTION FIND NEW FRIEND*/
    function connect_change() {
        current_connect = jQuery('#sl-reciplent').val();
        switch (current_connect) {
            case 'new-friend' :
                var $tthis = $('#block-new-friend');
                $tthis.fadeIn('slow');
                $('#nf-close').click(function () {
                    $tthis.fadeOut('slow');
                });
                break;
            case '0':
                var selector = $('.block-apend-message');
                var html = '<div class="receive-message col-md-12">';
                html += '<div class="col-md-8 cm-text">';
                html += 'Hello <b class="b-name">' + __NAME + '</b>';
                html += '</div>';
                html += '</div>';
                selector.html('');
                append_notice(selector, $("#block-cc-chat"), html);
                break;
            default :
                socket.emit('make_room', {uid: __US, cid: current_connect, sid: __SID, is: __IS, from: __US});
                var selector = $('.block-apend-message');
                selector.html('');
                $.ajax('http://ikteacher.com/?r=ajax/chat/get_history', {
                    'method': 'post',
                    'data': {'room': room($('#sl-reciplent').val(), __US), 'id': __US, 'idteacher': '', 'idstudent': idstudent},
                    beforeSend: function () {
                        $(this).prop('readonly', true);
                        $('#btn-send').button('loading');
                    }
                }).done(function (data) {
                    $(this).prop('readonly', false);
                    $('#btn-send').button('reset');
                    if (data.length > 0) {
                        jQuery("#wplc_chatbox").append(data);
                        jQuery('#wplc_chatbox').scrollTop(0);
                    }
                });
                break;
        }
    }


    socket.on('send_connect', function (data) {
        if (data.id == __US) {
            $('#txt-chat').prop({'readonly': true}).val(data.who_name + ' is requesting to connect');
            $('#btn-send').addClass(HAVE_CONNECT_CLASS).text('Accept ?');
            $("#btn-send").focus();
            accept_connect._from = data.who;
            accept_connect._to = data.id;
            setTimeout(function () {
                $("#btn-send").removeClass(HAVE_CONNECT_CLASS).text('Send');
                $("#txt-chat").prop('readonly', false).text('');
            }, 1000 * 60 * 10);
        }
    });

    $(document).on('click', '#btn-send', function () {
        if ($(this).hasClass(HAVE_CONNECT_CLASS)) {
            //accept_connect;
            $("#btn-send").removeClass(HAVE_CONNECT_CLASS).text('Send');
            $("#txt-chat").prop('readonly', false).val('');
            socket.emit('accept_connect', accept_connect);
        } else {

            var message = {
                '_from': __US,
                '_to': idstudent,
                '_content': $('#txt-chat').val(),
                '_id_db': '',
                'name': CURRENTNAME
            }
            $('#txt-chat').val('');
            var date = new Date();
            var html = '<span class="wplc-user-message">Tutor : ' + message._content + '</span><div class="wplc-clear-float-message"></div>';

            $.ajax('http://ikteacher.com/?r=ajax/chat/insert_history', {
                'method': 'post',
                'data': {'from_id': message._from,
                    'to_id': message._to,
                    'from_time': date.toLocaleTimeString(),
                    'content': message._content,
                    'room': room(message._from, message._to),
                    'name': CURRENTNAME,
                    'idroom': idroom
                },
                beforeSend: function () {
                    $('#btn-send').button('loading')
                }
            }).done(function (data) {
                if (data != '1') {
                    $('#btn-send').button('reset');
                    append_notice($('.block-apend-message'), $("#block-cc-chat"), html);
                    jQuery("#wplc_chatbox").prepend(html);
                    jQuery('#wplc_chatbox').scrollTop(0);
                    message._id_db = data;
                    socket.emit('send_message', message);
                    if ($('#sl-reciplent').val() == '') {
                        socket.emit('send_message_html', message);
                    } else {
                        socket.emit('send_message_php', message);
                    }
                } else {
                    var html2 = '<em>[System Message} Your tutoring connection is closed</em><div class="wplc-clear-float-message"></div>';
                    jQuery("#wplc_chatbox").prepend(html2);
                    alert('you can not send a message when the conversation has ended');

                }
            });
        }
    });
    socket.on('receive_message', function (data) {
        if (data._to == __US) {
            var date = new Date();
            $.ajax('http://ikteacher.com/?r=ajax/chat/update_history', {
                'method': 'post',
                'data': {'to_time': date.toLocaleTimeString(),
                    'id': data._id_db
                }
            }).done(function () {
                var html = '<span class="wplc-admin-message "><strong></strong>Student : ' + data._content + '</span><br /><div class="wplc-clear-float-message"></div>';
                jQuery("#wplc_chatbox").prepend(html);

                jQuery('#wplc_chatbox').scrollTop(0);
            });
        }
    });
    socket.on('update_message_html', function (data) {
        if (data._from == __US) {
            if ($('#sl-reciplent').val() != '') {
                var html = '<span class="wplc-user-message ">Student : ' + data._content + '</span><div class="wplc-clear-float-message"></div>';
                jQuery("#wplc_chatbox").prepend(html);
                jQuery('#wplc_chatbox').scrollTop(0);

            }
        }
    });
    
    socket.on('user_not_online', function (data) {
        if (data.id == __US) {
            var html = '<div class="receive-message col-md-12">';
            html += '<div class="col-md-8 cm-text">';
            html += '<p class="system-notice">Missing user : ' + data.find + '</p>';
            html += '</div>';
            html += '</div>';
            append_notice($('.block-apend-message'), $("#block-cc-chat"), html);
        }

    });
    socket.on('reload_tab_update', function (data) {
        if (data.uid == __US && data.idroom == idroom && data.idteacher == idstudent) {
            alert('You has closed and ended the chat');
        }
    });
    $(document).on('click', '.close', function () {
        $('#modal-notice').hide();
    });
    $(document).on('click', '.continue_session', function () {
        $('#modal-notice-close').slideUp('slow');
        socket.emit('start_time', {'std': __US, 'tch': jQuery('#sl-reciplent').attr('data-email'), 'btn_first': false});
    });
    /*
     $(document).on('click', '.btn-updt-session', function() {
     $.ajax(home_url + '/?r=ajax/chat/clear_session', {
     method 	: 'post',
     data 	: {'uid' : __US, 'sid' : __SID}
     }).done(function() {
     $('.block-popup').slideUp('slow');
     $(this).removeClass('btn-updt-session');
     });
     return false;
     });*/
    socket.on('push_register', function (data) {
            console.log(parseInt(__US));
            console.log(parseInt(data.idteacher));
            if (parseInt(data.idteacher) == parseInt(__US)) {
                idstudent = data.std;
                $('#sl-reciplent').attr('data-log', data.name);
                $('#sl-reciplent').attr('data-email', data.email);
                $('#sl-reciplent').attr('class', data.class);
                $('#sl-reciplent').attr('value', data.std);
                
                var $set_session = $('#sl-reciplent').attr('data-email');
                if ($set_session != "") {
//                    connect_change();
                }
            }
//        } else {
//            $.ajax('http://ikteacher.com/?r=ajax/chat/update_quit_status', {
//                method: 'post',
//                data: {id: 2, _id: idroom}
//            }).done(function (data) {
//            });
//            socket.emit('notification_quit', {'idteacher': __US});
//        }
    });
})(jQuery);

function push_select(selector, us, __us) {
    if (us.tch == __us && us.idroom==idroom) {
        selector.attr({'data-log': us.name, 'data-email': us.email, 'class': class_teacher, 'value': idstudent});
    }
}
function in_array(value, array) {
    for (var i = 0; i < array.length; i++) {
        if (value == array[i]) {
            return true;
        }
    }
    return false;
}
function append_notice(selector, scrollbar, html) {
    selector.append(html);
    scrollbar.mCustomScrollbar("scrollTo", "bottom");
}
function room(_from, _to) {
    if (_from > _to) {
        return _to + '.' + _from;
    }
    return _from + '.' + _to;
}

