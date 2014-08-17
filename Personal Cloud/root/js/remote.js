// Remote JS

var Remote = Remote || {};
Remote.currentId = 0;
Remote.buttons = {
    "0":{"type":"command", "command":"play", "icon":"img/btns/play.png", "class":"", "style":""},
    "1":{"type":"command", "command":"previous", "icon":"img/btns/previous.png", "class":"", "style":""},
	"2":{"type":"command", "command":"next", "icon":"img/btns/next.png", "class":"", "style":""}
};
Remote.icons = {};
Remote.iconPos = 0;



$(function() {
	$.getJSON( "api?a=iconList", function( data ) {
		Remote.icons = data;
	});
});

var container = document.querySelector('#remoteBoard');
var pckry = new Packery( container, {
	itemSelector: '.item',
	gutter: 5,
	columnWidth: 105,
	rowHeight: 105
});

function initDrag() {
	var itemElems = pckry.getItemElements();
	for ( var i=0, len = itemElems.length; i < len; i++ ) {
		var elem = itemElems[i];
		var draggie = new Draggabilly( elem );
		pckry.bindDraggabillyEvents( draggie );
	}
}

loadButtons();
//initDrag();

pckry.on( 'dragItemPositioned', function( pckryInstance, draggedItem ) {
	saveButtons();
});


var element = document.getElementById('remoteBoard');

// Button was held
Hammer(element).on("hold", function(e) {
	// A particular button was held
	if($(e.target).attr('id') != "remoteBoard") {
		Remote.currentId = parseInt($(e.target).attr('id').split("-")[1]);
		icon = "!.png";
		//icon = $(e.target).css('background-image').match(/\((.*?)\)/)[1].replace(/('|")/g,'').split('/').pop();
		icon = Remote.icons.list[Remote.currentId];
		iconPos = getIconPos(icon);
		$('.iconView').css('background-image',$(e.target).css('background-image'));
		$('#command').val(Remote.buttons[Remote.currentId].command);
		$('#buttonSetup').fadeIn(250);
	} else {
		// The remote itself was targeted, so add a new button
		var elems = [];
		var fragment = document.createDocumentFragment();
		for ( var i = 0; i < 1; i++ ) {
			var elem = document.createElement('div');
			elem.className = 'item';
			var newId = 0;
			for ( var x = 0; x < 1024; x++ ) {
				if($("#btn-"+x).length == 0) {
					newId = x;
					break;
				}
			}
			elem.id = 'btn-'+newId;
			fragment.appendChild(elem);
			elems.push(elem);
			Remote.buttons[newId] = {"type":"command", "command":"", "icon":"img/btns/!.png", "class":"", "style":""};
		}
		container.appendChild(fragment);
		pckry.appended(elems);
		initDrag();
		saveButtons();
	}
});

// Button was tapped
Hammer(element).on("tap", function(e) {
	if($(e.target).attr('id') != "remoteBoard") {
		sendCommand(Remote.buttons[parseInt($(e.target).attr('id').split("-")[1])].command);
	}
});

// Button Setup
$(".iconNext").on("click", function() {
	if(Remote.iconPos < Remote.icons.count) {
		Remote.iconPos++;
	} else {
		Remote.iconPos = 0;
	}
	$('.iconView').css('background-image','url('+Remote.icons.list[Remote.iconPos]+')');
	$('.iconView').attr('data-img',Remote.icons.list[Remote.iconPos]);
});
$(".iconPrev").on("click", function() {
	if(Remote.iconPos > 0) {
		Remote.iconPos--;
	} else {
		Remote.iconPos = Remote.icons.count;
	}
	$('.iconView').css('background-image','url('+Remote.icons.list[Remote.iconPos]+')');
	$('.iconView').attr('data-img',Remote.icons.list[Remote.iconPos]);
});
$("#buttonSetupClose").on("click", function() {
	$('#buttonSetup').fadeOut(250);
});
$("#buttonSetupDelete").on("click", function() {
	pckry.remove($('#btn-'+Remote.currentId));
	delete Remote.buttons[Remote.currentId];
	saveButtons();
	$('#buttonSetup').fadeOut(250);
});
$("#buttonSetupSave").on("click", function() {
	Remote.buttons[Remote.currentId].command = $("#command").val();
	Remote.buttons[Remote.currentId].icon = $(".iconView").attr('data-img');
	$('#btn-'+Remote.currentId).css('background-image','url('+$(".iconView").attr('data-img')+')');
	saveButtons();
	$('#buttonSetup').fadeOut(250);
});
$('#command').on('input',function(e){
	icon = $('#command').val()+'.png';
	iconPos = getIconPos(icon);
	if(iconPos > 0) {
		$('.iconView').css('background-image','url('+Remote.icons.list[iconPos]+')');
		$(".iconView").attr('data-img',Remote.icons.list[iconPos]);
		Remote.iconPos = iconPos;
	}
});

// Save/Load
function saveButtons() {
	$.each(Remote.buttons, function(k,v) {
		Remote.buttons[k].style = $('#btn-'+k).attr('style');
	});
	//localStorage["buttons"] = JSON.stringify(Remote.buttons);
	$.post( "api?a=saveButtons", { content: JSON.stringify(Remote.buttons) } );
}
function loadButtons() {
	$.getJSON( "api?a=loadButtons", function( data ) {
		//alert(data.content);
		if(data.content) {
			Remote.buttons = JSON.parse(data.content);
			$('#remoteBoard').html('');
			//alert(JSON.stringify(Remote.buttons));
			$.each(Remote.buttons, function(k,v) {
				$('#remoteBoard').append('<div id="btn-'+k+'" class="item" style="'+v.style+'"></div>');
			});
		}pckry.reloadItems();initDrag();$('#remoteStatus').text('Welcome.');
		
	});
	/*if(isDataAvailable()){
		if(localStorage["buttons"]) {
			Remote.buttons = JSON.parse(localStorage["buttons"]);
			localStorage["buttons"] = JSON.stringify(Remote.buttons);
			$('#remoteBoard').html('');
			$.each(Remote.buttons, function(k,v) {
				$('#remoteBoard').append('<div id="btn-'+k+'" class="item" style="'+v.style+'"></div>');
			});
		}
		pckry.reloadItems();
	}*/
}

function getIconPos(icon) {
	icon.split('/').pop();
	var toReturn = -1;
	$.each(Remote.icons.list, function(k,v) {
		if (v.split('/').pop() == icon) {
			toReturn = k;
			return false;
		}
	});
	return toReturn;
}

// Is storage data available? (prevents some Android Webview wackiness)
function isDataAvailable() {
	try {
		return localStorage["buttons"];
	} catch (e) {
		return false;
	}
}

// Send
function sendCommand(command) {
	$('#remoteStatus').text('Sending...');
	$.get( "cmd?a="+command, function(data) {
		//alert(data);
		$('#remoteStatus').text(data);
		orgBg = $('#remoteStatus').css('background-color');
		$('#remoteStatus').animate({opacity: 0.25}, 200, function() {
			$('#remoteStatus').animate({opacity: 1}, 200);
		});
	});
}
