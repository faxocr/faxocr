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
var targetid;
var dirty = false;
var $ = jQuery;

// to escape html tag
(function($) {
	$.escapeHTML = function(val) {
		return $('<div />').text(val).html();
	};
})(jQuery);

// initialization
jQuery(document).ready(function($) {
	$('.sheet td').addcontextmenu('contextmenu');
	$('.sheet td').hover(function() {
		defaultbg = $(this).css('backgroundColor');
		default_target = $(this).attr('id');
		$(this).css({backgroundColor: '#dddddd'});
	}, function() {
		$(this).css('backgroundColor', defaultbg);
		defaultbg = null;
	});

	document.onkeyup = on_keyup;
	document.onkeydown = on_keydown;
});

// existence check
function isset(data) {
	return ( typeof( data ) != 'undefined' );
}

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

	if (default_target == targetid) {
		defaultbg = target.css('backgroundColor');
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
	document.getElementById('sbmt').disabled = null;
}

//
// 「集計フィールド」削除関数
//
function delColumn(target, idx) {
	var targettd = $('#field_list td').eq(idx);

	// global
	$sval = 0;
	targetid = targettd.attr('name');

	$('.nDiv tr').eq(idx).remove();
	$('.cDrag div').eq(idx).remove();
	$('.hDiv th').eq(idx).remove();
	targettd.remove();
	$('#' + targetid).html('').css('background-color', '#ffffff');

	ths = $('.hDiv th');
	ths.each(function(num) {
		th = $(this);
		th.find('div:first').text(++num);
	});

	var cmd_c = '<input type="hidden" name="cell-' + targetid +
	    '-clear" value="" />';
	var form = $('form#form-commit');
	form.append(cmd_c);

	$('#field_list').flexReload();
	dirty = true;
	document.getElementById('sbmt').disabled = null;
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
	$('#' + targetid).html('').css('background-color', '#ffffff');

	ths = $('.hDiv th');
	ths.each(function(num) {
		th = $(this);
		th.find('div:first').text(++num);
	});

	var cmd_c = '<input type="hidden" name="cell-' + targetid +
	    '-clear" value="" />';
	var form = $('form#form-commit');
	form.append(cmd_c);

	dirty = true;
	$('#field_list').flexReload();
	document.getElementById('sbmt').disabled = null;
}

//
// 設定済みフィールドをinputタグに変換
//
function pack_fields() {
	var form = $('form#form-commit');
	$('#field_list td').each(function() {
		var fieldname = $(this).attr('name');
		var width = $(this).width();

		var txt = $(this).text();
		txt = txt.replace(/<\s*script[^>]*>[\s\S]*?<\s*\/script>/ig,
				  '');
		txt = $.escapeHTML(txt);

		var cmd_m = '<input type="hidden" name="cell-' + fieldname +
		    '-mark" value="' + txt + '" />';
		var cmd_f = '<input type="hidden" name="field-' + fieldname +
		    '-' + width + '" value="' + txt + '" />';

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
			document.getElementById('sbmt').disabled = null;
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
		if ($('#sbmt').length > 0) {
			dirty = !document.getElementById('sbmt').disabled;
		}
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

		$sval = 1;
		jquerycontextmenu.hidebox($, $('.jqcontextmenu'));
		$target = $('#' + targetid).css('background-color', ctbl[$sval]);

		set_field($target);
		$sval = 0;
		targetid = null;

		document.getElementById('sbmt').disabled = null;
	}
}

var SHEET = SHEET || {};// namespace

SHEET = {
	setFlexi: function() {
		// set flexigrid in form-list.php
		var $listHidden = $('#jsiListHidden'),
		    $hiddens = $listHidden.find('input'),
		    isResize = $hiddens.eq(4).val() === 'true' ? true : false,
		    isPager = $hiddens.eq(5).val() === 'true' ? true : false,
		    isRp = $hiddens.eq(6).val() === 'true' ? true : false,
		    rpNum = $hiddens.eq(7).val()*1,
		    isNov = $hiddens.eq(9).val() === 'true' ? true : false,
		    isToggle = $hiddens.eq(10).val() === 'true' ? true : false,
		    isTableToggle = $hiddens.eq(11).val() === 'true' ? true : false,
		    $colModel = $('#jsiColModel'),
		    $colModels = $colModel.find('input'),
		    colModelVal,
		    valList = [],
		    ary = [];

		for (var i = 0, max = $colModels.length; i < max; i++) {
			colModelVal = $.escapeHTML($colModels.eq(i).val());
			ary[i] = colModelVal.split(',');
			valList[i] = {display: ary[i][0],
				      name: ary[i][0],
				      width: ary[i][1],
				      align: 'left'};
		}

		$('#field_list').flexigrid({
			url: $hiddens.eq(0).val(),
			dataType: $hiddens.eq(1).val(),
			height: $hiddens.eq(2).val(),
			width: $hiddens.eq(3).val(),
			resizable: isResize,
			usepager: isPager,
			useRp: isRp,
			rp: rpNum,
			method: $hiddens.eq(8).val(),
			novstripe: isNov,
			showToggleBtn: isToggle,
			showTableToggleBtn: isTableToggle,
			procmsg: $hiddens.eq(12).val(),
			pagestat: $hiddens.eq(13).val(),
			pagetext: $hiddens.eq(14).val(),
			colModel: valList
		});
	},
	setParam: function() {// status menuでのurlパラメータ設定
		var $param = $('#jsiParam'),
		　　$hidden = $param.find('input'),
		　　hiddenVal = $hidden.val();

		if (hiddenVal === 'start') {
			$('#bt-password').click(function() {
				$.jqDialog.password('パスワードを入力して下さい', function(data) {
					$('#passwd').val(data);
					$('#form-commit').attr('action', 'form-setup.php?file=' + $('#file').val());
					$('#form-commit').submit();
				});
			});
		} else if (hiddenVal === 'running') {
			$('#bt-password1').click(function() {
				$.jqDialog.password('パスワードを入力して下さい', function(data) {
					$('#passwd').val(data);
					$('#status').val('stop');
					$('#form-commit').attr('action', 'form-list.php?file=' + $('#file').val());
					$('#form-commit').submit();
				});
			});
			$('#bt-password2').click(function() {
				$.jqDialog.password('パスワードを入力して下さい', function(data) {
					$('#passwd').val(data);
					$('#status').val('close');
					$('#form-commit').attr('action', 'form-list.php?file=' + $('#file').val());
					$('#form-commit').submit();
				});
			});
		} else if (hiddenVal === 'stop') {
			$('#bt-password1').click(function() {
				$.jqDialog.password('パスワードを入力して下さい', function(data) {
					$('#passwd').val(data);
					$('#status').val('running');
					$('#form-commit').attr('action', 'form-list.php?file=' + $('#file').val());
					$('#form-commit').submit();
				});
			});
			$('#bt-password2').click(function() {
				$.jqDialog.password('パスワードを入力して下さい', function(data) {
					$('#passwd').val(data);
					$('#status').val('close');
					$('#form-commit').attr('action', 'form-list.php?file=' + $('#file').val());
					$('#form-commit').submit();
				});
			});
		} else if (hiddenVal === 'close') {
			$('#bt-password3').click(function() {
				$.jqDialog.password('パスワードを入力して下さい', function(data) {
					url = $('#form_url').val();
					$('#status').val('close');
					$('#passwd2').val(data);
					$('#next_url').val(url);
					$('#form-list').submit();
				});
			});
		}
	},
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
				$('#form-commit').attr('action', 'form-commit.php?start');
				$('#form-commit').submit();
			});
		});
	}
};

$(document).ready(function() {
	if ($('#jsiColModel').length > 0) {
		SHEET.setFlexi();
	}

	if ($('#jsiParam').length > 0) {
		SHEET.setParam();
	}

	if ($('#jsiSetup').length > 0) {
		SHEET.setFlexiInSetup();
	}
});
