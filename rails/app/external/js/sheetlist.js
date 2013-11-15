/*
 * Shinsai FaxOCR
 *
 * Copyright (C) 2009-2011 National Institute of Public Health, Japan.
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

var cell_sw;
var cell_type;
var field;
var targetid;
var dirty = false;
var $ = jQuery;

function HexToR(h) {
	return parseInt((cutHex(h)).substring(0, 2), 16);
}

function HexToG(h) {
	return parseInt((cutHex(h)).substring(2, 4), 16);
}

function HexToB(h) {
	return parseInt((cutHex(h)).substring(4, 6), 16);
}

function cutHex(h) {
	return (h.charAt(0) == '#') ? h.substring(1, 7) : h;
}

// existence check
function isset(data) {
	return ( typeof( data ) != 'undefined' );
}

// to escape html tag
(function($) {
	$.escapeHTML = function(val) {
		return $('<div />').text(val).html();
	};
})(jQuery);

function go_sheet_upload() {
	gid = $("#form-status input[name=gid]").val();
	sid = $("#form-status input[name=sid]").val();
	location.href = "/external/sheet/" + gid + "/" + sid + "/";
}


// initialization
jQuery(document).ready(function($) {

	if ($('#jsiSetup').length > 0) {
		SHEET.setFlexiInSetup();
	}

	$('.sheet_field td').addcontextmenu('contextmenu');
	cell_type = new Array();

	// 各セルの背景色を取得し格納
	cell_sw = new Array();
	var elements = document.getElementsByTagName('*');
	for (var elm_cnt = 0; elm_cnt < elements.length; elm_cnt++) {
		if (elements[elm_cnt].className == 'sheet_field') {
			var table = elements[elm_cnt];
			var tds = table.getElementsByTagName('td');
			for (var tb_cnt = 0; tb_cnt < tds.length; tb_cnt++) {
				var tbg = $(tds[tb_cnt]).css('background-color');
				h = cutHex(tbg);
				if (h == tbg) {
					rgb = (tbg == 'rgb(255, 255, 255)') ? 255 * 3 :
					(tbg == 'rgba(0, 0, 0, 0)') ? 255 * 3 :
					(tbg == 'transparent') ? 255 * 3 : 1;
				} else {
					r = HexToR(tbg);
					g = HexToG(tbg);
					b = HexToB(tbg);
					rgb = r + g + b;
					rgb = rgb ? rgb : 255 * 3;
				}
				cell_sw[tds[tb_cnt].getAttribute('id')] = (rgb == 255 * 3) ? 0 : 1;
			}
		}
	}

	document.onkeyup = on_keyup;
	document.onkeydown = on_keydown;

	var targettd = $('#field_list td');
	targetid = targettd.attr('name');
	if (targettd.length = 1 && targetid == 0) {
		btn = $('.statusMenu .marker button').attr('disabled', true);
	}

	btn = $('.statusMenu button:disabled');
	btn.parent().addClass('disable');
});

//
// セル指定クリック時関数
//
function set_field (target) {
	var field = $('#field').val();

	if (!targetid) {
		var idx = target.index();
		target = $('#field_list td').eq(idx);

		$('#fieldreset li a').bind('click', function(e) {
			e.preventDefault();
			delColumn(target, idx);
		});

		return false;
	}

	field = field.replace(/<[^>]*>?/g, '');
	target = $('#' + targetid).html('<b>' + field + '</b>');

	var targettd = $('td[name="' + targetid + '"]');
	if (targettd.length) {
		targettd.find('div').text(field);
		return;
	}

	add_column(field, 80);
}

//
// セルリセット時関数(jqcontextmenuよりcall)
//
function reset_field () {
	var targettd = $('td[name="' + targetid + '"]');

	if (isset(targettd)) {
		var index = targettd.index() + 1;
		target = $('.hDivBox th:nth-child(' + index + ')');
		if (target.length) {
			del_column(targettd, index);
		}
	}

	var targettd = $('#field_list td');
	targetid = targettd.attr('name');
	if (targettd.length = 1 && targetid == 0) {
		btn = $('.statusMenu .marker button').attr('disabled', true);
		$('.statusMenu .marker').addClass('disable');
	}
}

//
// 「集計フィールド」追加時関数
//
function add_column(html, width) {
	var tr, newtr, td, newtd, num, th, newth, newdiv;

	html = html.replace(/<[^>]*>?/g, '');

	tr = $('.nDiv tr:last');
	newtr = tr.clone(true);
	tr.after(newtr);

	td = $('.cDrag div:last');
	newtd = td.clone(true);
	td.after(newtd);

	num = $('.hDiv th').length + 1;
	th = $('.hDiv th:last');
	newth = th.clone(true);
	newdiv = document.createElement('div');
	newth.wrapInner(newdiv);
	newth.find('div').css('width', width + 'px')
	    .text(num);
	th.after(newth);

	td = $('.bDiv td:last');
	newtd = td.clone(true);
	newtd.attr('name', targetid);
	newtd.wrapInner(document.createElement('div'));
	newtd.find('div').css('width', width + 'px')
	    .html(html); // ここが効く

	td.after(newtd);

	$('#field_list').flexReload();
	dirty = true;

	$('.statusMenu .marker button').attr('disabled', false);
	$('.statusMenu .marker').removeClass('disable');
}

//
// 「集計フィールド」削除関数
//
function delColumn(target, idx) {
	var targettd = $('#field_list td').eq(idx);

	// global
	targetid = targettd.attr('name');

	$('.nDiv tr').eq(idx).remove();
	$('.cDrag div').eq(idx).remove();
	$('.hDiv th').eq(idx).remove();
	targettd.remove();
	$('#' + targetid).html('');
	$('#' + targetid).removeClass('enter-selected');
	$('#' + targetid).removeClass('click-selected');
	$('#' + targetid).addClass('not-selected');
	cell_sw[targetid] = 0;


	ths = $('.hDiv th');
	ths.each(function(num) {
		th = $(this);
		th.find('div:first').text(++num);
	});

	var cmd_c = '<input type="hidden" name="cell-' + targetid +
	    '-clear" value="" />';
	var form = $('form#form-save');
	form.append(cmd_c);

	$('#field_list').flexReload();
	dirty = true;

	$('.statusMenu .marker button').attr('disabled', false);
	$('.statusMenu .marker').removeClass('disable');

	$('#fieldreset li a').unbind('click');
}

//
// 「集計フィールド」削除関数 (jqcontextmenuよりcall)
//
function del_column(target, index) {
	var ths, th;

	if (!target) {
		return false;
	}

	$('.nDiv tr:nth-child(' + index + ')').remove();
	$('.cDrag div:nth-child(' + index + ')').remove();
	$('.hDiv th:nth-child(' + index + ')').remove();
	var targettd = $('#field_list td:nth-child(' + index + ')');
	targetid = targettd.attr('name');

	targettd.remove();
	$('#' + targetid).html('');
	$('#' + targetid).removeClass('enter-selected');
	$('#' + targetid).removeClass('click-selected');
	$('#' + targetid).addClass('not-selected');
	cell_sw[targetid] = 0;

	ths = $('.hDiv th');
	ths.each(function(num) {
		th = $(this);
		th.find('div:first').text(++num);
	});

	var cmd_c = '<input type="hidden" name="cell-' + targetid +
	    '-clear" value="" />';
	var form = $('form#form-save');
	form.append(cmd_c);

	dirty = true;
	$('#field_list').flexReload();

	$('.statusMenu .marker button').attr('disabled', false);
	$('.statusMenu .marker').removeClass('disable');
}

//
// 設定済みフィールドをinputタグに変換
//
function pack_fields() {
	var form = $('form#form-save');
	$('#field_list td').each(function() {
		var fieldname = $(this).attr('name');
		var txt = $(this).text();
		var type;

		if (fieldname == "0")
			return;
		if (typeof(cell_type[fieldname]) == 'undefined') {
			type = 1;
		} else if (cell_type[fieldname] != -1) {
			type = cell_type[fieldname];
		} else {
			return;
		}

		txt = txt.replace(/<\s*script[^>]*>[\s\S]*?<\s*\/script>/ig,
				  '');
		txt = $.escapeHTML(txt);

		var cmd_m = '<input type="hidden" name="cell-' + fieldname +
		    '-mark" value="' + txt + '" />';
		var cmd_f = '<input type="hidden" name="field-' + fieldname +
		    '-' + type + '" value="' + txt + '" />';

		form.append(cmd_m);
		form.append(cmd_f);
	});

	// save hidden input
	form.submit();
}

// 「集計フィールド」内のセルクリック時関数
function field_click() {
	var htmlval;

	if (!$(this).hasClass('on')) {
	    $(this).addClass('on');
		var txt = $(this).text(),
		    html =  $(this).html(),
		    width = $(this).width(),
		    height = $(this).height(),
		    fsize = $(this).css('font-size'),
		    nlines = height / 16 | 0; // int conversion

		txt = txt.replace(/<[^>]*>?/g, '');
		$(this).html('<input type="text" id="active" value="'
			+ txt
			+ '" size="'
			+ txt.length * 2
			+ '" style="width:'
			+ width
			+ 'px; font-size:'
			+ fsize
			+ '; " />'
		);

		target = $('#active');
		target.focus();
		target.blur(function() {
			var inputVal = $(this).val();
			targetid = $(this).parent().attr('name');
			width = width - 10;
			inputVal = inputVal.replace(/<[^>]*>?/g, '');
			htmlval = inputVal.replace(/(\\n)/g, '<br />');
			$(this).parent().removeClass('on')
			    .html('<div style="width: ' + width + 'px" >' +
				  htmlval + '</div>');
			$('#' + targetid).html('<b>' + inputVal + '</b>');

			$('.statusMenu .marker button').attr('disabled', false);
			$('.statusMenu .marker').removeClass('disable');
		});
	}
}

// key押下時
function on_keydown(e) {
	var keycode,
	    ctrl,
	    shift,
	    keychar,
	    frm,
	    target_next;

	if (e != null) {
		// Mozilla(Firefox, NN) and Opera
		keycode = e.which;
		ctrl = (typeof e.modifiers == 'undefined') ?
		    e.ctrlKey : e.modifiers & Event.CONTROL_MASK;
		shift = (typeof e.modifiers == 'undefined') ?
		    e.shiftKey : e.modifiers & Event.SHIFT_MASK;
	} else {
		// Internet Explorer
		keycode = event.keyCode;
		ctrl = event.ctrlKey;
		shift = event.shiftKey;
	}

	if (ctrl) {
		dirty = !$('.statusMenu .marker button').attr('disabled');

		keychar = String.fromCharCode(keycode).toUpperCase();

		if (keychar == 'S') {
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
			frm = document.getElementById('form-save');
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
			target_next = target.parent().next('td:first');
			if (target_next.length) {
				target.blur();
				target_next.click();
				return false;
			}
		}
	}
}

// keyリリース時
function on_keyup(e) {
	var keycode,
	    shift;

	if (e != null) {
		// Mozilla(Firefox, NN) and Opera
		keycode = e.which;
		shift = (typeof e.modifiers == 'undefined') ?
		    e.shiftKey : e.modifiers & Event.SHIFT_MASK;
	} else {
		// Internet Explorer
		keycode = event.keyCode;
		shift = event.shiftKey;
	}

	// Enter
	if (keycode == '13') {

		if (!targetid) {
			$('#active').blur();
			return true;
		}

		jquerycontextmenu.hidebox($, $('.jqcontextmenu'));
		$('#' + targetid).removeClass('not-selected');
		$('#' + targetid).removeClass('click-selected');
		$target = $('#' + targetid).addClass('enter-selected');
		cell_sw[targetid] = 1;

		set_field($target);
		targetid = null;

		$('.statusMenu .marker button').attr('disabled', false);
		$('.statusMenu .marker').removeClass('disable');
	}
}

var SHEET = SHEET || {};// namespace

SHEET = {
	setFlexiInSetup: function() {// set flexigrid in form-setup.php
		$('#field_list').flexigrid({
			showToggleBtn:false,
			resizable:false,
			height:70,
			onDragCol: function() {
				ths = $('.hDiv th');
				ths.each(function(num) {
					th = $(this);
					th.find('div:first').text(++num);
				});
			}
		});

		$('.hDivBox th').addcontextmenu('fieldreset');
		$('.hDivBox th').click(function() {
			target = $(this);
		});
		$('#field_list td').click(field_click);

		$('#bt-password').click(function() {
			$.jqDialog.password('パスワードを入力して下さい', function(data) {
				$('#passwd').val(data);
				pack_fields();
				$('#form-status').attr('action', 'form-commit.php?start');
				$('#form-status').submit();
			});
		});
	}
};
