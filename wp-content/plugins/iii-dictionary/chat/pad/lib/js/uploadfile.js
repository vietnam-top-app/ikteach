App = {};
App.socket = io.connect('http://107.180.78.211:8000');
var x = $(location).attr('search').substr(1).split('&');
var client = decodeURIComponent(x[0].split('=')[1]);
var sid = decodeURIComponent(x[1].split('=')[1]);

//console.log(client);
//console.log(sid);

$(function () {
    App.socket.on('new_image', function (data) {
        $(document).find('*[id^="word-prob-step"]').show();
        $(document).find('*[id^="word-prob-step-"]').hide();
        $(document).find('*[id^="flashcard-"]').hide();
        if (data.sid == sid && client!=data.id) {
            var img = new Image();
            var ctx = document.getElementById('word-prob-step').getContext('2d');
            img.onload = function () {
                ctx.drawImage(img, 0, 0, $('#canvas_panel').width(), $('#canvas_panel').height());
            };
            img.src = data.link;
            var n = parseInt(jQuery('#nav_steps li:last').attr('data-n'))+1;
            jQuery('#nav_steps').append('<li data-n="'+n+'" data-ctrl="2">student image</li>');
            jQuery('#canvas_panel').append('<img src="'+data.link+'" alt="" id="word-prob-step-q'+n+'" class="word-prob-steps canvas-layer" >');
        }
    });
});
function draw(ev) {
    document.getElementById('word-prob-step').style.display = "block";
    $(document).find('*[id^="word-prob-step-"]').hide();
    $(document).find('*[id^="flashcard-"]').hide();
    var ctx = document.getElementById('word-prob-step').getContext('2d'),
            img = new Image(),
            f = document.getElementById("input-image").files[0],
            url = window.URL || window.webkitURL,
            src = url.createObjectURL(f);
    img.src = src;
    img.onload = function () {
        ctx.drawImage(img, 0, 0, $('#canvas_panel').width(), $('#canvas_panel').height());
        url.revokeObjectURL(src);
    }
}
function uploadFile(file) {
    var url = "http://ikstudy.com/?r=ajax/uploadimage";
    var xhr = new XMLHttpRequest();
    var fd = new FormData();
    xhr.open("POST", url, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var linklocal=xhr.responseText;
            var url      = window.location.href; 
            var splitted = url.split("/wp-content", 1);
            var splitted1 = linklocal.split(".net", 2);
            App.socket.emit('study_file_upload', {link: splitted+splitted1[1], id: client,sid :sid});
            // Every thing ok, file uploaded
            jQuery('#nav_steps').append('<li data-n="'+n+'" data-ctrl="2">my image</li>');
            jQuery('#canvas_panel').append('<img src="'+splitted+splitted1[1]+'" alt="" id="word-prob-step-q'+n+'" class="word-prob-steps canvas-layer" data-img-src="'+splitted+splitted1[1]+'">');
        }
    };
    fd.append("upload_file", file);
    xhr.send(fd);
}
document.getElementById('input-image').addEventListener('change', draw, false);
var uploadfiles = document.querySelector('#input-image');
uploadfiles.addEventListener('change', function () {
    var files = this.files;
    for (var i = 0; i < files.length; i++) {
        uploadFile(this.files[i]); // call the function to upload the file
    }
}, false);