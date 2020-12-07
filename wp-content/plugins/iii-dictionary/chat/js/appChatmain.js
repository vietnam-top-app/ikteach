var socket = io.connect('http://107.180.78.211:8000');
//END - define core global variable 
//END - define global variable
//--------------------------------------------------------------------------
//SECTION - function when DOM load finish

jQuery(document).ready(function() {
	//clean variable store iamge in server when store new image
	get_all_image();
	get_all_nav();
});

//END - function when DOM load finish
//--------------------------------------------------------------------------
//SECTION : NOTEPAD
jQuery('.nav-items li').on('click',function() {
	switch(__HA) {
		case 7:
		case 8:
		case 9:
		case 10:
			socket.emit('click_in_math_panel', { __US : __US, dataN : 1 });
		break;
		case 11:
		case 12:
		case 13:
		case 14:
		case 15:
			socket.emit('click_in_math_panel', { __US : __US, dataN : jQuery(this).attr('data-n') });
		break;
	}
});
jQuery('#open-notepad-btn').on('click',function() {
	//send data to socket.io
        get_all_image();
	get_all_nav();
        if(jQuery('#sl-reciplent').val()==undefined){
            jQuery( "#auto-open-chat" ).html('Click here to open drawing');
        }else{
            jQuery( "#auto-open-chat" ).html('Click here to open chat and drawing');
        }
        jQuery( "#auto-open-chat" ).attr('href','/wp-content/plugins/iii-dictionary/chat/pad/Draw.html?client=' 
                + __US + '&sid=' + __SID+'&__HA='+__HA+'&__US='+__US+'&__URL='
                +__URL+'&__IS='+__IS+'&__NAME='+__NAME+'&__EMAIL='+__EMAIL+'&__PRICE='
                +__PRICE+'&CURRENTNAME='+CURRENTNAME +'&IDSTUDENT='+ jQuery('#sl-reciplent').val() 
                + '&IDROOM='+jQuery('#sl-reciplent').attr('IDROOM')+'&EMAILSTUDENT='+jQuery('#sl-reciplent').attr('data-email'));
//        document.getElementById('auto-open-chat').click();
            jQuery( "#modal-notice" ).show();
//	var notepad = window.open('/wp-content/plugins/iii-dictionary/chat/pad/Draw.html?client=' 
//                + __US + '&sid=' + __SID+'&__HA='+__HA+'&__US='+__US+'&__URL='
//                +__URL+'&__IS='+__IS+'&__NAME='+__NAME+'&__EMAIL='+__EMAIL+'&__PRICE='
//                +__PRICE+'&CURRENTNAME='+CURRENTNAME +'&IDSTUDENT='+ jQuery('#sl-reciplent').val() 
//                + '&IDROOM='+jQuery('#sl-reciplent').attr('IDROOM')+'&EMAILSTUDENT='+jQuery('#sl-reciplent').attr('data-email'));
});

//END - draw current image to notepad
//END - SECTION : NOTEPAD
//--------------------------------------------------------------------------

//function support 

function get_all_image() {
	switch(__HA) {
		case 7: 
		case 8:
		case 9:
		case 10:
			var _data = [];
			var _time = 0;
			html2canvas(jQuery("#homework-content")[0], {
				useCORS: true,
				width: 865,
				height: 525,
				onrendered: function(canvas) {
					_data.push({ mathListDataImageUrl: canvas.toDataURL(), stepCanvas: 'canvas-q1', typeHA : __HA });
					
				}
			});
			if(_time == 0 ) {
				setInterval(function() {
					if(_data.length == 1 && _time == 0){
						socket.emit('math_canvas_all_image_data', { __US : __US, Data : _data });
						_time = 1;
					}	
				}, 500);
			}
		break;
		case 11: //crash
		case 12: //crash
		case 15: //crash
			var crash_data = [];
			var crash_selectr = jQuery("#homework-content > div");
			crash_selectr.each(function(i,v){ 
				crash_data.push({ mathListDataImageUrl: jQuery(this)[0].outerHTML  , stepCanvas: jQuery(v).attr('id'), typeHA : __HA });
			});
			socket.emit('math_canvas_all_image_data', { __US : __US, Data : crash_data } );
		break;
		case 14:
			var __data 		= [];
			var __time 		= 0;
			var __selector 	= jQuery("#homework-content > div");
			__selector.each(function(i,v){
				var ele = jQuery(this);
				switch(__HA) {
					case 14:  ele.css({ 'display' : 'block' });  break;
					default : ele.removeClass('hidden'); break;
					
				}
				
				html2canvas(jQuery(this)[0], {
					useCORS: true,
					width: 865,
					height: 525,
					onrendered: function(canvas) {
						if(i != '0') { 
							switch(__HA) {
							case 14: ele.css({ 'display' : 'none' }); break;
							default : ele.addClass('hidden');  break;	
							}
							
						}
						__data.push({ mathListDataImageUrl: canvas.toDataURL(), stepCanvas: jQuery(v).attr('id'), typeHA : __HA });
					}
				});
			});
			if(__time == 0 ) {
				setInterval(function() {
					if(__data.length == __selector.length && __time == 0){
						socket.emit('math_canvas_all_image_data', { __US : __US, Data : __data } );
						__time = 1;
					}	
				}, 100);
			}
		break;
		case 13:
			var ___data  = [];
			jQuery("#homework-content").find('img').each(function(i,v){
				___data.push({ mathListDataImageUrl: jQuery(v).attr('data-img-src'), stepCanvas: jQuery(v).attr('id'), typeHA : __HA });
			});
			socket.emit('math_canvas_all_image_data', { __US : __US, Data : ___data });
		break;
	}
}

function get_all_nav() {
	switch(__HA) {
		case 7:
		case 8:
		case 9:
		case 10:
			var _nav = [];
			_nav.push({ mathListDataLi : jQuery(this).attr('data-ctrl') !== undefined ? jQuery(this).attr('data-ctrl') : 0  })
			socket.emit('math_canvas_all_li_data', { __US : __US, Data : _nav });
		break;
		case 14:
			var __nav = [];
			jQuery('.nav-items').find('li').each(function(i,v) {
				__nav.push({ mathListDataLi : jQuery(this).attr('data-ctrl') !== undefined ? jQuery(this).attr('data-ctrl') : 1  });
			});
			socket.emit('math_canvas_all_li_data', { __US : __US, Data : __nav });
			break;
		case 12:
		case 13:
		case 11:
		case 15:
			var ___nav = [];
			jQuery('.nav-items').find('li').each(function(i,v) {
				___nav.push({ mathListDataLi : jQuery(this).attr('data-ctrl') !== undefined ? jQuery(this).attr('data-ctrl') : 0  });
			});
			socket.emit('math_canvas_all_li_data', { __US : __US, Data : ___nav });
		break;
	}	
}
//--------------------------------------------------------------------------
