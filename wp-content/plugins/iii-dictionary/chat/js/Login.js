function CustomPrompt(){
	this.render = function(dialog,func){
		if(sessionStorage.getItem('name')==null){
		if(name==""||name==null){
			var winW = window.innerWidth;
			var winH = window.innerHeight;
			var dialogoverlay = document.getElementById('dialogoverlay');
			var dialogbox = document.getElementById('dialogbox');
			dialogoverlay.style.display = "none";
			dialogoverlay.style.height = winH+"px";
			dialogbox.style.left = (winW/2) - (550 * .5)+"px";
			dialogbox.style.top = "100px";
			dialogbox.style.display = "block";
			document.getElementById('dialogboxhead').innerHTML = "Login";
			document.getElementById('dialogboxbody').innerHTML = dialog;
			document.getElementById('dialogboxbody').innerHTML += '<b>User Name or e-mail</b>';
			document.getElementById('dialogboxbody').innerHTML += '<br><input id="prompt_value1">';
			document.getElementById('dialogboxbody').innerHTML += '<br><br><b id="for">Password</b>';
			document.getElementById('dialogboxbody').innerHTML += '<br><input id="prompt_value2" type="password"><br>';
			document.getElementById('dialogboxbody').innerHTML +='<br><input type="checkbox" id="remember" checked="checked"><label for="checkbox" onclick="Prompt.remember()" style="cursor:pointer;display:none;">Remember</label><br>';
			document.getElementById('dialogboxbody').innerHTML += '<br><br><button id="btn_login" onclick="Prompt.ok(\''+func+'\')"></button>';
			//document.getElementById('dialogboxbody').innerHTML += '<a href="http://chat.fogwareonline.com/public/signup.php" target="_blank"><img src="/Sign-up_btn.png" width="150" height="25" style="cursor:pointer" /></a><br>';
			//document.getElementById('dialogboxbody').innerHTML += '<br><a>Forgot password?></a><br>';
			document.getElementById('dialogboxfoot').innerHTML += '<br><b id="err" style="float:left;margin-left:30px;margin-right:30px;max-width:400px;color:red"></b>';
			 //<button onclick="Prompt.cancel()">Cancel</button>
			document.getElementById('prompt_value1').focus();
		}
		}else{
			this.okonreload(sessionStorage.getItem('name'),sessionStorage.getItem('pass'));
			}
	}
	this.okonreload = function(namep,passp){
		socket.emit('config', {name: namep,pass:passp});
	}
	this.remember = function(){
		if(document.getElementById('remember').checked){
			document.getElementById('remember').checked = false;
		}else{
			document.getElementById('remember').checked = true;
		}
	}
	//login click
	this.ok = function(func){
		var prompt_value1 = document.getElementById('prompt_value1').value;
		var prompt_value2 = document.getElementById('prompt_value2').value;
		socket.emit('config', {name: prompt_value1,pass:prompt_value2});
	}
}
var Prompt = new CustomPrompt();