App = {};
App.socket = io.connect('http://localhost:8000');
//END - define core global variable 
//----------------------------------------------------------------------
var colorvalue = {
    den: "#000000",
    lacay: "#00ce00",
    cam: "#f7941d",
    d_o: "#f71d1d",
    nau: "#970c0c",
    datroi: "#00a99d",
    tim: "#92278f",
};

var linewidth = 1;
var _linewidth = 1;
var _cur_active;
var __canvas;
var __newlayer = 'new-layer-q';
var __type_draw = "source-over";
var __color = colorvalue.den;
var __src = 'lib/img/';
var __can = 0;
var x = $(location).attr('search').substr(1).split('&');
var client = decodeURIComponent(x[0].split('=')[1]);
var sid = decodeURIComponent(x[1].split('=')[1]);
idroom = decodeURIComponent(x[13].split('=')[1]);
idstudent = decodeURIComponent(x[12].split('=')[1]);
var data_li_lick = [];
//END - define global variable
//----------------------------------------------------------------------
//SECTION - SETUP AND FUNCTION JQUERY FOR NOTEPAD
$(function () {
    $('.btn_color').on('click', function () {
        $('.btn_color').removeClass('btn_color_active');
        if (!$(this).hasClass('btn_color_active')) {
            $(this).addClass('btn_color_active');
        }
    });
    $('#btn_eraser').click(function () {
        offBtnActive();
        $('.btn_active').removeClass('btn_active');
        if (!$(this).hasClass('btn_active')) {
            $(this).addClass('btn_active');
            $('#canvas_panel').css('cursor', 'url(../img/eraser.png), crosshair');
            __type_draw = "destination-out";
            setcursorsize();
            $(this).attr('src', __src + 'eraser_hl.png');
        }
    });

    $('#btn_clear').click(function () {
        $(this).attr('src', __src + 'clear_hl.png');
        $('#nlayer_step li.active, #nlayer_step.use_layer, #nav_steps li.active').removeClass('active use_layer');
        $('canvas').removeClass('can-paint').fadeOut(400);
        $('#canvas_panel > div').fadeOut(400);
        $("[id$=-q" + $('#nav_steps li.active').attr("data-n") + "]").not("canvas[id*=new-layer-q]").fadeIn(400);
        setTimeout(function () {
            $('#btn_clear').attr('src', __src + 'clear.png');
        }, 1000);
    });

    $('#btn_pen').click(function () {
        offBtnActive();
        $('.btn_active').removeClass('btn_active');
        if (!$(this).hasClass('btn_active')) {
            $(this).addClass('btn_active');
            $('#canvas_panel').css('cursor', 'url(../img/cursor.png), crosshair');
            __type_draw = "source-over";
            $(this).attr('src', __src + 'pen_hl.png');
        }
    });


    //set color
    $('#lacay').click(function () {
        __color = colorvalue.lacay;
        offerase();
    });
    $('#cam').click(function () {
        __color = colorvalue.cam;
        offerase();
    });
    $('#d_o').click(function () {
        __color = colorvalue.d_o;
        offerase();
    });
    $('#nau').click(function () {
        __color = colorvalue.nau;
        offerase();
    });
    $('#datroi').click(function () {
        __color = colorvalue.datroi;
        offerase();
    });
    $('#tim').click(function () {
        __color = colorvalue.tim;
        offerase();
    });
    $('#den').click(function () {
        __color = colorvalue.den;
        offerase();
    });
    //set size linewidth
    $('#subsize').click(function () {
        linewidth -= 2;
        if (linewidth < 1) {
            linewidth = 1;
        }
        $('#size_num').text(linewidth);
    });
    $('#addsize').click(function () {
        linewidth += 2;
        if (linewidth > 30) {
            linewidth = 30;
        }
        $('#size_num').text(linewidth);
    });
    //set size _linewidth
    $('#subsize_ers').click(function () {
        _linewidth -= 2;
        if (_linewidth < 1) {
            _linewidth = 1;
        }
        setcursorsize();
        $('#size_num_ers').text(_linewidth);
    });
    $('#addsize_ers').click(function () {
        _linewidth += 2;
        if (_linewidth > 30) {
            _linewidth = 30;
        }
        setcursorsize();
        $('#size_num_ers').text(_linewidth);
    });


    $('.scroll-list-v').mCustomScrollbar();

    $(document).on('click', '#nav_steps li', function () {
        var _cctrl = $(this).attr("data-ctrl");
        document.getElementById('word-prob-step').style.display = "none";
        if ($(this).hasClass("active")) {
            $(this).removeClass("active");
            $("[id$=-q" + $(this).attr("data-n") + "]").not("canvas[id*=new-layer-q]").fadeOut(400);
        } else {
            $(this).addClass("active");
            $("[id$=-q" + $(this).attr("data-n") + "]").not("canvas[id*=new-layer-q]").fadeIn(400);
        }
        if (_cctrl == "1" || _cctrl == "2" || _cctrl == "0") {
            $(document).find('#nav_steps li.active').not(this).each(function (i, v) {

                if (_cctrl == "2" || _cctrl == "0") {
                    $(v).removeClass("active");
                    $("[id$=-q" + $(v).attr("data-n") + "]").not("canvas[id*=new-layer-q]").fadeOut();
                }

                if ($(v).attr('data-ctrl') == '2') {
                    var c_next = $(v).next().attr('data-ctrl');
                    var c_prev = $(v).prev();
                    if ((c_prev.attr('data-ctrl') == '1' && c_prev.hasClass('active')) || (c_next == '2' || c_next == undefined)) {
                        $(v).removeClass('active');
                        $("[id$=-q" + $(v).attr("data-n") + "]").not("canvas[id*=new-layer-q]").fadeOut();
                        //$("#word-prob-step-q" + $(v).attr("data-n")).fadeOut();
                    }
                }
            });
        }
//                App.socket.emit('click_step', {'id_step': $(this).attr("data-n"),'client':client,'idroom':idroom,'idteacher':idstudent,'id_click':1} );
    });
//        App.socket.on('update_click_step', function (data) {
//            if (data.idteacher == client && data.idroom == idroom && data.client == idstudent) {
//                jQuery('ul#nav_steps').find('li[data-n='+data.id_step+']').closest('li').click();
//            }
//        });

    $(document).on('click', '#nlayer_step li', function () {

        var c_length = $('#nlayer_step li').length;
        var g_canvas = $("#new-layer-q" + $(this).attr("data-n"));
        var cex_use_layer = $('#nlayer_step li').hasClass('use_layer');
        var flag = 0;
        var set_btn = !$('#btn_eraser').hasClass('btn_active') ? 1 : 0;
        $(document).find('*[id^="new-layer"]').hide();
        $(document).find('#nlayer_step li').removeClass('active');
        $(this).addClass("active");
        flag = $(this).hasClass("active") ? 1 : 0;
        flag = $(this).hasClass("use_layer") ? 2 : flag;
        if (set_btn == 1) {
            $('#btn_pen').click()
        }
        if (!cex_use_layer) {
            $(this).addClass("active");
            flag = 1;
        }
        switch (flag) {
            case 0 :
                $(this).addClass("active");
//                g_canvas.fadeIn(400);
                break;
            case 1 :
                $(document).find('#nlayer_step li.use_layer').removeClass('use_layer');
                $(document).find('canvas.can-paint').css({'z-index': 1}).removeClass('can-paint');
                $(this).addClass("use_layer");
                g_canvas.addClass('can-paint').css({'z-index': 2}).fadeIn(400);
                canDraw(g_canvas[0]);
                break;
            case 2 :
                var g_data_n = $(this).attr('data-n');
                var g_data_first_n = $('#nlayer_step li:first').attr('data-n');
                var g_data_last_n = $('#nlayer_step li:last').attr('data-n');
                var c_use_layer = $((g_data_n - 1) < g_data_first_n) ? 1 : 0;
                var li_data_n = (g_data_n == g_data_first_n) ? g_data_last_n : g_data_n - 1;

                $(this).removeClass("use_layer active");
                g_canvas.removeClass('can-paint').css({'z-index': 1}).fadeOut(400);
                if ($('#nlayer_step li.active').length > 0) {
                    var $exist_active = $('#nlayer_step li.active:last');
                    $exist_active.addClass('use_layer');
                    $('#new-layer-q' + $exist_active.attr('data-n')).addClass('can-paint').css({'z-index': 2}).fadeIn(400);
                    canDraw($('#new-layer-q' + $exist_active.attr('data-n'))[0]);
                } else {
                    $('#nlayer_step').find('[data-n="' + li_data_n + '"]').addClass('use_layer active');
                    $('#new-layer-q' + li_data_n).addClass('can-paint').css({'z-index': 2}).fadeIn(400);
                    canDraw($('#new-layer-q' + li_data_n)[0]);
                }

                break;
        }
    });

    $(document).on('click', '.resetdraw', function () {
        if ($('canvas.can-paint').length > 0) {
            canDraw($('canvas.can-paint')[0]);
        }
    });


    $(document).on('pointerdown', '#btn_new_layer', function () {
        var _data_text = parseInt($('#nlayer_step li').length) + 1;
        var _new_layer = new Object;
        $(this).attr('src', __src + 'new_layer_hl.png');
//		if(_data_text <= 10 ) {
        var _data_n = parseInt($('#canvas_panel canvas').length) + 1;

        append_newlayer(_data_n, _data_text);
        if (_data_text == 1) {
            jQuery('ul li:contains("' + _data_text + '")').click();
            jQuery('#nav_steps li:first').click();
        }
//		}
        setTimeout(function () {
            $('#btn_new_layer').attr('src', __src + 'new_layer.png');
        }, 1000);

    });

    

});
//END - SETUP
//----------------------------------------------------------------------

//SECTION - SOCKET
$(function () {
    App.socket.on('connect', function () {
        App.socket.on('drawMathImageData', function (data) {
            data[client].forEach(
                    function (data) {
                        switch (data.typeHA) {
                            case 11:
                            case 12:
                            case 15:
                                var $tthis = $('#canvas_panel > div');
                                $(data.mathListDataImageUrl).appendTo('#canvas_panel');
                                $tthis.attr({"data-readonly": 1}).addClass("main-canvas").removeClass("hidden");
                                $tthis.css({"position": "absolute", "z-index": "1", "display": "none", "width": "100%", "pointer-events": "none"});
                                break;
                            default :
                                var img = new Image();
                                img.crossOrigin = 'anonymous';
                                img.onload = function () {
                                    var canvas = cloneCanvas();

                                    $(canvas).attr({"id": data.stepCanvas, "data-readonly": 1, "class": "main-canvas"});
                                    $(canvas).css({"position": "absolute", "z-index": "1", "display": "none"});
                                    $(canvas).appendTo('#canvas_panel');

                                    var ctx = canvas.getContext('2d');
                                    ctx.drawImage(this, 0, 0, $('#canvas_panel').width(), $('#canvas_panel').height());
                                };
                                img.src = data.mathListDataImageUrl;
                                break;
                        }

                    }
            );
        });

        App.socket.on('clickInMathPanel', function (data) {
            var data_n = (data[client] == undefined) ? 1 : data[client];
            var $tthis = $(document).find('li#first_click');
            $tthis.attr({
                'data-n': data_n,
                'data-ctrl': 0
            });
        });

        $('#btn_import').on('pointerdown', function () {
            if (!$(this).hasClass('btn_import_active')) {
                $(this).addClass('btn_import_active').attr('src', __src + 'loaded.png');
                App.socket.emit('get_all_nav', {__US: client});
            }

        });

        App.socket.on('putAllNav', function (data) {
            $('#nav_steps').html(data.putLi);
            $("[id$=-q" + $('#nav_steps li.active').attr("data-n") + "]").not("canvas[id*=new-layer-q]").fadeIn(400);
        });

        App.socket.on('update_canvas', function (data) {
            if (data.canvas.uid == idstudent && data.canvas.iduser == client && data.canvas.idroom == idroom) {
                if (data.canvas != null) {
                    if ($('#new-layer-q' + data.canvas.id).length > 0) {
                        var imageObj = new Image();
                        var canvas = $('#new-layer-q' + data.canvas.id)[0];
                        imageObj.src = data.canvas.canvas;
                        imageObj.onload = function () {
                            var ctx = canvas.getContext('2d');
                            ctx.clearRect(0, 0, canvas.width, canvas.height);
                            ctx.drawImage(imageObj, 0, 0, imageObj.width, imageObj.height, 0, 0, canvas.width, canvas.height);
                        }
                    } else {
                        append_newlayer(data.canvas.id, data.canvas.li, 1, data.canvas.canvas);
                        jQuery('ul#nlayer_step li:contains("' + data.canvas.li + '")').click();
                    }
                    jQuery('ul#nav_steps').find('li[class="active"]').removeClass('active');
                    $(document).find('canvas').not("canvas[id*=new-layer-q]").css({'display': 'none'});
                    $.each(data.canvas.li_click, function (key, value) {
                        jQuery('ul#nav_steps li:contains("' + value + '")').addClass('active');
                        jQuery("[id$=-q"+value+"]").not("canvas[id*=new-layer-q]").fadeIn();
                       
                    });
                }
            }
        });
    });

    //END - connect to socket.io and function for NOTEPAD
    //----------------------------------------------------------------------

});
//END - SOCKET
//----------------------------------------------------------------------


//SECITON - SUPPORT FUNCTION

function getMousePos(canvas, evt) {
    var rect = canvas.getBoundingClientRect();

    return {
        x: evt.clientX - rect.left,
        y: evt.clientY - rect.top
    };
}

function offerase() {
    __type_draw = "source-over";
    $('#btn_pen').click();

}
function setcursorsize() {
    if (__type_draw == "destination-out") {
        if (_linewidth < 6) {
            $('#canvas_panel').css('cursor', '../url(img/eraser.png), auto');
        }
        if (_linewidth > 5 && _linewidth < 13) {
            $('#canvas_panel').css('cursor', '../url(img/eraser2.png), auto');
        }
        if (_linewidth > 12 && _linewidth < 20) {
            $('#canvas_panel').css('cursor', '../url(img/eraser3.png), auto');
        }
        if (_linewidth > 19) {
            $('#canvas_panel').css('cursor', '../url(img/eraser4.png), auto');
        }
    }
}

function cloneCanvas() {

    //create a new canvas
    var newCanvas = document.createElement('canvas');
    var context = newCanvas.getContext('2d');

    //set dimensions
    newCanvas.width = $('#canvas_panel').width();
    newCanvas.height = $('#canvas_panel').height();

    //return the new canvas
    return newCanvas;
}
function offBtnActive() {
    $('#btn_pen').attr('src', __src + 'pen.png');
    $('#btn_eraser').attr('src', __src + 'btn_eraser.png');
}

function canDraw(_canvas) {
    var isDown = false;
    var _ctx = _canvas.getContext("2d");

    _ctx.lineJoin = "round";
//	_ctx.lineWidth = radius;
    _ctx.strokeStyle = __color;
    _ctx.globalCompositeOperation = __type_draw;
    if (__type_draw == 'source-over') {
        _ctx.lineWidth = linewidth;
    } else {
        _ctx.lineWidth = _linewidth;
    }


    $(_canvas)
            .mousedown(function (e) {
                isDown = true;
                var pos = getMousePos(_canvas, e);
                _ctx.beginPath();
                _ctx.moveTo(pos.x, pos.y);
            })
            .mousemove(function (e) {
                var pos = getMousePos(_canvas, e);
                if (isDown !== false) {
                    _ctx.lineTo(pos.x, pos.y);
                    _ctx.stroke();
                }
            })
            .mouseup(function (e) {
                dataURL(client, idstudent, idroom, sid, $('#nlayer_step li.use_layer').attr('data-n'), $('#nlayer_step li.use_layer').text(), $('canvas.can-paint'));
                isDown = false;
                _ctx.closePath();

            });

    draw = {
        started: false,
        start: function (evt) {
            var _pos = getMousePos(_canvas, evt.touches[0]);
            _ctx.beginPath();
            _ctx.moveTo(
                    _pos.x,
                    _pos.y
                    );

            this.started = true;

        },
        move: function (evt) {
            var _pos = getMousePos(_canvas, evt.touches[0]);
            if (this.started) {
                _ctx.lineTo(
                        _pos.x,
                        _pos.y
                        );
                _ctx.stroke();
            }

        },
        end: function (evt) {
            this.started = false;
            dataURL(client, idstudent, idroom, sid, $('#nlayer_step li.use_layer').attr('data-n'), $('#nlayer_step li.use_layer').text(), $('canvas.can-paint'));
        }
    };
    _canvas.addEventListener('touchstart', draw.start, false);
    _canvas.addEventListener('touchend', draw.end, false);
    _canvas.addEventListener('touchmove', draw.move, false);

    document.body.addEventListener('touchstart', function (evt) {
        if (evt.target == _canvas) {
            evt.preventDefault();
        }
    }, false);
    document.body.addEventListener('touchend', function (evt) {
        if (evt.target == _canvas) {
            evt.preventDefault();
        }
    }, false);
    document.body.addEventListener('touchmove', function (evt) {
        if (evt.target == _canvas) {
            evt.preventDefault();
        }
    }, false);
}

function dataURL(uid, iduser, idroom, sid, pos, li, canvas) {
    $.each(jQuery('ul#nav_steps').find('li[class="active"]'), function (key, value) {
        data_li_lick.push(value.getAttribute('data-n'));
    });
    App.socket.emit('put_canvas', {uid: uid, iduser: iduser, idroom: idroom,  li_click: data_li_lick,sid: sid, id: pos, li: li, 'canvas': canvas[0].toDataURL()});
    data_li_lick = [];
}
function split_room(id, room) {
    var split = room.split('.');
    if (split[0] == id || split[1] == id) {
        return true;
    }
    return false;
}
function check_room(array) {
    var stc, std;
    for (var i = 0; i < array.length; i++) {
        if (array[i].is != undefined) {
            switch (array[i].is) {
                case 0 :
                    std = array[i].sid;
                    break;
                case 1 :
                    stc = array[i].sid;
                    break;
            }
        }
    }
    return stc == std;
}
function append_newlayer(_data_n, _data_text, draw = 0, canvasdata = '') {

    var li = "<li touch-action='none' data-n='" + _data_n + "'>" + _data_text + "</li>";
    var canvas = cloneCanvas();

    $(canvas).attr({"id": 'new-layer-q' + _data_n, "data-readonly": 0});
    $(canvas).css({"position": "absolute", "z-index": 1, "display": "none"});
    $('#nlayer_step').append(li);
    if (draw == 1) {
        var imageObj = new Image();
        imageObj.src = canvasdata;
        imageObj.onload = function () {
            ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(imageObj, 0, 0, imageObj.width, imageObj.height, 0, 0, canvas.width, canvas.height);
        }
    }
    $(canvas).appendTo('#canvas_panel');
}

//END - SUPPORT FUNCTION
//----------------------------------------------------------------------
