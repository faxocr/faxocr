/*
 * Shinsai FaxOCR
 *
 * Copyright (C) 2009-2013 National Institute of Public Health, Japan.
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

var cellBgColorManager;	// global: also referenced from jqcontextmenu
var cell_type;
var targetid;
var dirty = false;
var $ = jQuery;


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


// initialization
jQuery(document).ready(function($) {

	if ($('#jsiSetup').length > 0) {
		SHEET.setFlexiInSetup();
	}

	$('.sheet_field td').addcontextmenu('contextmenu');
	cell_type = new Array();

	cellBgColorManager = new SheetCellBackgroundColor();

	document.onkeyup = on_keyup;
	document.onkeydown = on_keydown;

	var targettd = $('#field_list td');
	targetid = targettd.attr('name');
	if (targettd.length == 1 && targetid == 0) {
		StatusMenu.MarkerButton.makeItUnClickable();
	} else {
		StatusMenu.MarkerButton.makeItClickable();
	}
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
	$('#' + targetid).html('<b>' + field + '</b>');

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
		StatusMenu.MarkerButton.makeItUnClickable();
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

	StatusMenu.MarkerButton.makeItClickable();
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
	SheetCell.clearHtmlText(targetid, cellBgColorManager);
	SheetCell.notSelected(targetid, cellBgColorManager);

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

	StatusMenu.MarkerButton.makeItClickable();

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
	SheetCell.clearHtmlText(targetid, cellBgColorManager);
	SheetCell.notSelected(targetid, cellBgColorManager);

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

	StatusMenu.MarkerButton.makeItClickable();
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

		var target = $('#active');
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
			SheetCell.setHtmlText(targetid, '<b>' + inputVal + '</b>');

			StatusMenu.MarkerButton.makeItClickable();
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
		dirty = !StatusMenu.MarkerButton.isUnClickable();

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
		var target = $('#active');
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
		$target = SheetCell.enterSelected(targetid, cellBgColorManager);

		set_field($target);
		targetid = null;

		StatusMenu.MarkerButton.makeItClickable();
	}
}

/**
 * Cell background color management
 * @class SheetCellBackgroundColor
 */
var SheetCellBackgroundColor = function () {
	/**
	 * @property {Array} cell_sw
	 */
	this.cell_sw = new Array();

	this.setBgColorFlagForAllCells();
};

SheetCellBackgroundColor.prototype = {
	/**
	 * Store flags to cell_sw where the cell's bg color is set or not
	 * @public
	 */
	setBgColorFlagForAllCells: function () {
		// 各セルの背景色を取得し格納
		var elements = document.getElementsByTagName('*');
		for (var elm_cnt = 0; elm_cnt < elements.length; elm_cnt++) {
			if (elements[elm_cnt].className == 'sheet_field') {
				var table = elements[elm_cnt];
				var tds = table.getElementsByTagName('td');
				for (var tb_cnt = 0; tb_cnt < tds.length; tb_cnt++) {
					var tbg = $(tds[tb_cnt]).css('background-color');
					var h = this.cutHex(tbg);
					if (h == tbg) {
						var rgb = (tbg == 'rgb(255, 255, 255)') ? 255 * 3 :
						(tbg == 'rgba(0, 0, 0, 0)') ? 255 * 3 :
						(tbg == 'transparent') ? 255 * 3 : 1;
					} else {
						var r = this.HexToR(tbg);
						var g = this.HexToG(tbg);
						var b = this.HexToB(tbg);
						var rgb = r + g + b;
						rgb = rgb ? rgb : 255 * 3;
					}
					this.cell_sw[tds[tb_cnt].getAttribute('id')] = (rgb == 255 * 3) ? 0 : 1;
				}
			}
		}
	},
	/**
	 * Set the information that the cell's bg color is set.
	 * @param {string} targetid Cell's id. ex. "0-1-2"
	 * @public
	 */
	set: function (targetid) {
		this.cell_sw[targetid] = 1;
	},
	/**
	 * Clear the information that the cell's bg color is set.
	 * @param {string} targetid Cell's id. ex. "0-1-2"
	 * @public
	 */
	clear: function (targetid) {
		this.cell_sw[targetid] = 0;
	},
	/**
	 * Get the information whether the cell's bg color is set or not.
	 * @param {string} targetid Cell's id. ex. "0-1-2"
	 * @return {boolean}
	 * @public
	 */
	isSet: function (targetid) {
		return this.cell_sw[targetid] == 1 ? true : false;
	},
	/**
	 * @private
	 */
	HexToR: function (h) {
		return parseInt((cutHex(h)).substring(0, 2), 16);
	},
	/**
	 * @private
	 */
	HexToG: function (h) {
		return parseInt((cutHex(h)).substring(2, 4), 16);
	},
	/**
	 * @private
	 */
	HexToB: function (h) {
		return parseInt((cutHex(h)).substring(4, 6), 16);
	},
	/**
	 * @private
	 */
	cutHex: function (h) {
		return (h.charAt(0) == '#') ? h.substring(1, 7) : h;
	},
};

/**
 * Sheet Cell Operation Class
 * @class SheetCell
 */
// used also in jqcontextmenu
var SheetCell = {
	/**
	 * Make the cell's state enter-selected.
	 * @param {string} targetid Cell's id. ex. "0-1-2"
	 * @param {} cellBgColorManager Object to SheetCellBackgroundColorManager class
	 * @return {} jQuery object
	 */
	enterSelected: function (targetid, cellBgColorManager) {
		cellBgColorManager.set(targetid);
		$('#' + targetid).removeClass('not-selected');
		$('#' + targetid).removeClass('click-selected');
		return $('#' + targetid).addClass('enter-selected');
	},
	/**
	 * Make the cell's state click-selected.
	 * @param {string} targetid Cell's id. ex. "0-1-2"
	 * @param {} cellBgColorManager Object to SheetCellBackgroundColorManager class
	 */
	clickSelected: function (targetid, cellBgColorManager) {
		if (cellBgColorManager.isSet(targetid)) {
			//$("#" + targetid).removeClass('not-selected');
			$("#" + targetid).removeClass('enter-selected');
			$("#" + targetid).addClass('click-selected');
		}
		//cellBgColorManager.set();
	},
	/**
	 * Make the cell's state not-selected.
	 * @param {string} targetid Cell's id. ex. "0-1-2"
	 * @param {} cellBgColorManager Object to SheetCellBackgroundColorManager class
	 */
	notSelected: function (targetid, cellBgColorManager) {
		$('#' + targetid).removeClass('enter-selected');
		$('#' + targetid).removeClass('click-selected');
		$('#' + targetid).addClass('not-selected');
		cellBgColorManager.clear(targetid);
	},
	/**
	 * Set the cell's contents.
	 * @param {string} targetid Cell's id. ex. "0-1-2"
	 * @param {string} text Html text
	 * @return {} jQuery object
	 */
	setHtmlText: function (targetid, html) {
		return $('#' + targetid).html(html);
	},
	/**
	 * Clear the cell's contents.
	 * @param {string} targetid Cell's id. ex. "0-1-2"
	 * @return {} jQuery object
	 */
	clearHtmlText: function (targetid) {
		return $('#' + targetid).html('');
	},
};

/**
 * Status menu class
 * @class StatusMenu
 */
var StatusMenu = {
	/**
	 * Marker button class
	 * @class MarkerButton
	 */
	MarkerButton: {
		/**
		 * Make the button clickable
		 */
		makeItClickable: function() {
			$('.statusMenu .marker button').attr('disabled', false);
			$('.statusMenu .marker').removeClass('disable');
		},
		/**
		 * Make the button un-clickable
		 */
		makeItUnClickable: function() {
			$('.statusMenu .marker button').attr('disabled', true);
			$('.statusMenu .marker').addClass('disable');
		},
		/**
		 * Get whether the marker button is UNclickable or not.
		 * @return {boolean}
		 */
		isUnClickable: function() {
			return $('.statusMenu .marker button').attr('disabled');
		},
	},
	ReloadButton: {
		/**
		 * Go to the page of reselecting an excel file
		 */
		reloadSheetExcelFile: function() {
			var gid = $("#form-status input[name=gid]").val();
			var sid = $("#form-status input[name=sid]").val();
			location.href = "/external/sheet/" + gid + "/" + sid + "/";
		},

	},
};

var SHEET = SHEET || {};// namespace

SHEET = {
	setFlexiInSetup: function() {// set flexigrid in form-setup.php
		$('#field_list').flexigrid({
			showToggleBtn:false,
			resizable:false,
			height:70,
			onDragCol: function() {
				var ths = $('.hDiv th');
				ths.each(function(num) {
					var th = $(this);
					th.find('div:first').text(++num);
				});
			}
		});

		$('.hDivBox th').addcontextmenu('fieldreset');
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
