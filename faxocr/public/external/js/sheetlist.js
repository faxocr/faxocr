/*
 * Ez-Cloud (Kantan cloud)
 * 
 * Copyright (C) 2011 National Institute of Public Health, Japan.
 * All rights Reserved.
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 */

var defaultbg;
var default_target;
var field;

jQuery(document).ready(function($) {
	$('.sheet td').addcontextmenu('contextmenu');
	$('.sheet td').hover(function(){
		defaultbg = $(this).css('backgroundColor');
		default_target = $(this).attr("id");
		$(this).css({backgroundColor:'#dddddd'});
	},function(){
		$(this).css('backgroundColor', defaultbg);
		defaultbg = null;
	});

	document.onkeyup = on_keyup;
	document.onkeydown = on_keydown;
});

var $ = jQuery;

function set_field (target) {

	field = $("#field").val();
	target = $("#" + targetid).html("<B>" + field + "</B>");

	targettd = $('td[name="' + targetid + '"]');
	if (targettd.length) {
		targettd.find("div").text(field);
		return;
	}

	if (default_target == targetid) {
		defaultbg = target.css('backgroundColor');
	}

//	add_column("<input value=\"" + field + "\">", 80);
	add_column(field, 80);
}

function reset_field () {
    	var targettd = $('td[name="' + targetid + '"]');

	if (isset(targettd)) {
		var index = targettd.index() + 1;
		target = $(".hDivBox th:nth-child(" + index + ")");
		if (target.length){
			del_column();
		}
	}
}

function add_column(html, width) {
	var tr,
		newtr,
		td,
		newtd,
		num,
		th,
		newth,
		newdiv;

	tr = $(".nDiv tr:last");
	newtr = tr.clone(true);
	tr.after(newtr);

	td = $(".cDrag div:last");
	newtd = td.clone(true);
	td.after(newtd);

	num = $(".hDiv th").length + 1;
	th = $(".hDiv th:last");
	newth = th.clone(true);
	newdiv = document.createElement("div");
	newth.wrapInner(newdiv);
	newth.find("div").css("width", width + "px").text(num);
	th.after(newth);

	td = $(".bDiv td:last");
	newtd = td.clone(true);
	newtd.attr("name", targetid);
	newtd.wrapInner(document.createElement("div"));
	newtd.find("div").css("width", width + "px").html(html); // ここが効く
	td.after(newtd);

	$("#field_list").flexReload();
	document.getElementById('sbmt').disabled = null;
}

function del_column() {
	var index,
		ths,
		th;

	if (!target){
		return false;
	}
	
	index = target.children().text();
	
	$(".nDiv tr:nth-child(" + index + ")").remove();
	$(".cDrag div:nth-child(" + index + ")").remove();
	$(".hDiv th:nth-child(" + index + ")").remove();
	var targettd = $("#field_list td:nth-child(" + index + ")");
	var targetid = targettd.attr("name");

	targettd.remove();
	$("#" + targetid).html("").css("background-color", "#ffffff");

	ths = $(".hDiv th");
	ths.each(function(num) {
		th = $(this);
		th.find("div:first").text(++num);
	});

	var cmd_c = '<input type="hidden" name="cell-' +
		targetid + '-clear" value="" />';
	var form = $("form#form-commit");
	form.append(cmd_c);

	$("#field_list").flexReload();
	document.getElementById('sbmt').disabled = null;
}

function pack_fields() {
	var form = $("form#form-commit");
	$("#field_list td").each(function() {
		var fieldname = $(this).attr("name");
//		var width = $(this).css("width");
//		alert($(this).width())
		var width = $(this).width();

		var cmd_m = '<input type="hidden" name="cell-' +
		  fieldname + '-mark" value="' + $(this).text() + '" />';
		var cmd_f = '<input type="hidden" name="field-' +
		  fieldname + '-' + width + '" value="' + $(this).text() + '" />';

		form.append(cmd_m);
		form.append(cmd_f);
	});
}

function field_click() {
	var htmlval;

	//alert($(this).text());
	if (!$(this).hasClass('on')) {
		$(this).addClass('on');
		var txt = $(this).text();
		var html =  $(this).html();
		var width = $(this).width();
		var height = $(this).height();
		var fsize = $(this).css('font-size');

		var nlines = height / 16 | 0; // int convert
		
		txt = txt.replace(/<[^>]*>?/g, '');
		$(this).html('<input type="text" id="active" value="' + txt + '" size="' +
		txt.length * 2 + '" style="width:' + width + 'px; font-size:' + fsize + '; " />');

		target = $('#active');
		target.focus();
		target.blur(function() {
//alert("!");
			var inputVal = $(this).val();
			var targetid = $(this).parent().attr("name");
			width = width - 10;
			inputVal = inputVal.replace(/<[^>]*>?/g, '');
			htmlval = inputVal.replace(/(\\n)/g, "<br />");
			$(this).parent().removeClass('on').html('<div style="width: ' +
								+ width + 'px" >' + htmlval + '</div>');
			$("#" + targetid).html("<B>" + inputVal + "</B>");
			document.getElementById('sbmt').disabled = null;
		});
	}
}

function isset(data) {
    return ( typeof( data ) != 'undefined' );
}

function on_keydown(e) {
	var keycode;
	var ctrl;
	var shift;
	var keychar,
		frm,
		target_next;

	if (e != null) {
		// Mozilla(Firefox, NN) and Opera
		keycode = e.which;
		ctrl = typeof e.modifiers == 'undefined' ?
			e.ctrlKey : e.modifiers & Event.CONTROL_MASK;
	        shift = typeof e.modifiers == 'undefined' ?
			e.shiftKey : e.modifiers & Event.SHIFT_MASK;
	} else {
	        // Internet Explorer
	        keycode = event.keyCode;
	        ctrl = event.ctrlKey;
	        shift = event.shiftKey;
	}

	if (ctrl) {
		var dirty;
		if($('#sbmt').length > 0){
			dirty = !document.getElementById('sbmt').disabled;
		}
		keychar = String.fromCharCode(keycode).toUpperCase();

		if (keychar == "S") {
			if (e) {
				e.preventDefault();
				e.stopPropagation();
			} else {
				event.returnValue = false;
				event.cancelBubble = true;
			}
			if (!dirty) {
				return false;
			}
			frm = document.getElementById('form-commit');
			pack_fields();
			frm.submit();
		}
	}

	// Tab
	if (keycode == '9') {
		target = $('#active');
		if (shift) {
			target_next = target.parent().prev();
			if (target_next.length) {
				target.blur();
				target_next.click();
				return false;
			}
		} else {
			target_next = target.parent().next("td:first");
			if (target_next.length) {
				target.blur();
				target_next.click();
				return false;
			}
		}
	}
}

function on_keyup(e) {
	var keycode;
	var shift;
	var a,
	    $sval;

	// note: targetid is defined in jqcontextmenu.js??
	
	if (e != null) {
	    // Mozilla(Firefox, NN) and Opera
		keycode = e.which;
		shift = typeof e.modifiers == 'undefined' ?
		e.shiftKey : e.modifiers & Event.SHIFT_MASK;
	} else {
	    // Internet Explorer
		keycode = event.keyCode;
	    shift = event.shiftKey;
	}

	// Enter
	if (keycode == '13') {

		if (modal) {
			// password modal
			a = document.activeElement.parentNode.nextSibling;
			a.click();
			return;
		}

		if (!targetid) {
			$('#active').blur();
			return true;
		}

		$sval = 1;
		jquerycontextmenu.hidebox($, $('.jqcontextmenu'));
		$target = $("#" + targetid).css('background-color', sa1[$sval]);
		set_field($target);
		$sval = 0;
		targetid = null;

	    document.getElementById('sbmt').disabled = null;
	}
}
