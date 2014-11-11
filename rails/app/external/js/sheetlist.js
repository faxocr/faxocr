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

	targetid = FieldList.init();

	// set callback function when clicking the marker button
	StatusMenu.MarkerButton.setCallbackHandler('click', function (e) {
		this.disabled=true;
		new FieldForm().submit();
	});
});


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
			new FieldForm().submit();
		}
	}

	// Tab
	if (keycode == '9') {
		if (shift) {
			return FieldList.moveFocusPrev();
		} else {
			return FieldList.moveFocusNext();
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

		SheetFieldProcessor.set($target);
		targetid = null;

		StatusMenu.MarkerButton.makeItClickable();
	}
}

/**
 * Main sheet field processor
 * @class SheetFieldProcessor
 */
var SheetFieldProcessor = {
	/**
	 * Actions when the sheet cell is clicked
	 * @param {} target jQuery object of clicked cell
	 *
	 */
	set: function (target) {
		var field = $('#field').val();

		if (!targetid) {
			var idx = target.index();
			target = $('#field_list td').eq(idx);

			$('#fieldreset li a').bind('click', function(e) {
				e.preventDefault();
				SheetFieldProcessor._delColumn(target, idx);
			});

			return false;
		}

		field = field.replace(/<[^>]*>?/g, '');
		$('#' + targetid).html('<b>' + field + '</b>');

		var retVal = FieldList.addOrSetContents(targetid, field);
		if (retVal == true) {
			return false;
		}
		SheetFieldProcessor._add_column(field, 80);
	},
	/**
	 * Actions when reset is clicked for the sheet cell
	 */
	reset: function () {
		var targettd = $('td[name="' + targetid + '"]');

		if (isset(targettd)) {
			var index = targettd.index() + 1;
			target = $('.hDivBox th:nth-child(' + index + ')');
			if (target.length) {
				SheetFieldProcessor._del_column(targettd, index);
			}
		}

		targetid = FieldList.applyFieldListStatusToMarkerButton();
	},
	/**
	 * @private
	 */
	_add_column: function (html, width) {
		var tagStrippedHtml = html.replace(/<[^>]*>?/g, '');

		FieldList.addColumn(tagStrippedHtml, width);
		$('#field_list').flexReload();
		dirty = true;

		StatusMenu.MarkerButton.makeItClickable();
	},
	/**
	 * @private
	 */
	_del_column: function (target, index) {
		if (!target) {
			return false;
		}

		targetid = FieldList.del_Column(index);

		SheetCell.clearHtmlText(targetid, cellBgColorManager);
		SheetCell.notSelected(targetid, cellBgColorManager);

		FieldList.renumberHeader();

		new FieldForm().cellClear(targetid);
		dirty = true;
		$('#field_list').flexReload();

		StatusMenu.MarkerButton.makeItClickable();
	},
	/**
	 * @private
	 */
	_delColumn: function (target, idx) {
		// global
		targetid = FieldList.delColumn(idx);

		SheetCell.clearHtmlText(targetid, cellBgColorManager);
		SheetCell.notSelected(targetid, cellBgColorManager);

		FieldList.renumberHeader();

		new FieldForm().cellClear(targetid);
		$('#field_list').flexReload();
		dirty = true;

		StatusMenu.MarkerButton.makeItClickable();

		$('#fieldreset li a').unbind('click');
	},

};


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
		setCallbackHandler: function (type, callback) {
			return $('.statusMenu .marker button').bind(type, callback);
		}
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

/**
 * Field form handler for field list
 * @class FieldForm
 */
var FieldForm = function () {
	this.form = $('form#form-save');
};

FieldForm.prototype = {
	cellClear: function (targetid) {
		var cmd_c = '<input type="hidden" name="cell-' + targetid + '-clear" value="" />';
		return this.form.append(cmd_c);
	},
	cellMark: function (targetid, txt) {
		var cmd_m = '<input type="hidden" name="cell-' + targetid + '-mark" value="' + txt + '" />';
		return this.form.append(cmd_m);
	},
	cellType: function (targetid, txt, type) {
		var cmd_f = '<input type="hidden" name="field-' + targetid + '-' + type + '" value="' + txt + '" />';
		return this.form.append(cmd_f);
	},
	/**
	 * Submit the form
	 * @public
	 */
	submit: function () {
		this.packAllFieldInfoToInputTags();
		return this.form.submit();
	},
	/**
	 * @private
	 */
	packAllFieldInfoToInputTags: function () {
		var self = this;
		FieldList.traverseItems(function (fieldname, txt) {
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

			txt = txt.replace(/<\s*script[^>]*>[\s\S]*?<\s*\/script>/ig, '');
			txt = $.escapeHTML(txt);

			self.cellMark(fieldname, txt);
			self.cellType(fieldname, txt, type);
		});
	},
};

/**
 * Provide operations for field list
 * @class FieldList
 */
var FieldList = {
	/**
	 * Initialize
	 */
	init: function () {
		return FieldList.applyFieldListStatusToMarkerButton();
	},
	/**
	 * Change the status of marker button according to the number of columns of field list.
	 */
	applyFieldListStatusToMarkerButton: function () {
		var targetTDs = $('#field_list td');
		var targetid = targetTDs.attr('name');
		if (targetTDs.length == 1 && targetid == 0) {
			// if field list contains only 1 column.
			StatusMenu.MarkerButton.makeItUnClickable();
		} else {
			StatusMenu.MarkerButton.makeItClickable();
		}
		return targetid;
	},
	/**
	 * Add a new column to field list.
	 * @param {string} html html text to be set as a content
	 * @param {number} width width of the contents
	 * @public
	 */
	addColumn: function (html, width) {
		var num;

		var dupColumn = function (origElement, callback) {
			var cloned = origElement.clone(true);
			callback(origElement, cloned);
			origElement.after(cloned);
			return cloned;
		};

		dupColumn($('.nDiv tr:last'), function () {});
		dupColumn($('.cDrag div:last'), function () {});

		num = $('.hDiv th').length + 1;
		dupColumn($('.hDiv th:last'), function (origElement, newElement) {
			newElement.wrapInner(document.createElement('div'));
			newElement.find('div').css('width', width + 'px').text(num);
		});

		dupColumn($('.bDiv td:last'), function (origElement, newElement) {
			newElement.attr('name', targetid);
			newElement.wrapInner(document.createElement('div'));
			newElement.find('div').css('width', width + 'px').html(html); // ここが効く
		});
	},
	/**
	 * Delete a column in the field list.
	 * @param {number} idx index of the field list set in TD tag.
	 * @return {string} the ID name of removed field
	 * @public
	 */
	delColumn: function (idx) {
		var targettd = $('#field_list td').eq(idx);
		var fieldName = targettd.attr('name');

		// idx starts from 0
		$('.nDiv tr').eq(idx).remove();
		$('.cDrag div').eq(idx).remove();
		$('.hDiv th').eq(idx).remove();
		targettd.remove();

		return fieldName;
	},
	/**
	 * Delete a column in the field list.
	 * @param {number} index index of the field list set in TD tag.
	 * @return {string} the ID name of removed field
	 * @public
	 */
	del_Column: function (index) {
		// index starts from 1
		$('.nDiv tr:nth-child(' + index + ')').remove();
		$('.cDrag div:nth-child(' + index + ')').remove();
		$('.hDiv th:nth-child(' + index + ')').remove();
		var targettd = $('#field_list td:nth-child(' + index + ')');
		var targetid = targettd.attr('name');
		targettd.remove();
		return targetid;
	},
	/**
	 * Set a content to the specified field.
	 * @param {string} targetid Cell's id. ex. "0-1-2"
	 * @param {string} contents A text string of contents
	 * @return {boolean} true: if success, false: if failed
	 * @public
	 */
	addOrSetContents: function (targetid, contents) {
		var targettd = $('td[name="' + targetid + '"]');
		if (targettd.length) {	// set contents if exists
			targettd.find('div').text(contents);
			return true;
		}
		return false;
	},
	/**
	 * Renumber the header of field list
	 * @public
	 */
	renumberHeader: function () {
		var ths = $('.hDiv th'); // All columns of field list's header
		ths.each(function(index) {	// index starts from 0
			// To display the number from 1, increment the number
			$(this).find('div:first').text(++index);
		});
	},
	/**
	 * Do some actions for all field
	 * @param {} callback function to apply to each field list's element
	 * @public
	 */
	traverseItems: function (callback) {
		$('#field_list td').each(function() {
			var fieldname = $(this).attr('name');
			var txt = $(this).text();
			return callback(fieldname, txt);
		});
	},
	/**
	 * Show text input field to edit
	 */
	editContents: function () {
		if ($(this).hasClass('on')) {
			return;
		}

		$(this).addClass('on');
		// get current contents and attributes
		var txt = $(this).text(),
		    html =  $(this).html(),
		    width = $(this).width(),
		    height = $(this).height(),
		    fsize = $(this).css('font-size'),
		    nlines = height / 16 | 0; // int conversion
		// remove html tags
		txt = txt.replace(/<[^>]*>?/g, '');
		// show text input small window
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

		// prepare for closing the small window when being unfocused
		var target = $('#active');
		target.focus();
		target.blur(function() {
			// set the user input with <div> tag.
			var inputVal = $(this).val();
			targetid = $(this).parent().attr('name');
			width = width - 10;
			inputVal = inputVal.replace(/<[^>]*>?/g, '');
			var htmlval = inputVal.replace(/(\\n)/g, '<br />');
			$(this).parent().removeClass('on')
			    .html('<div style="width: ' + width + 'px" >' +
				  htmlval + '</div>');

			// update the corresponding sheet cell
			SheetCell.setHtmlText(targetid, '<b>' + inputVal + '</b>');
			// update marker button.
			StatusMenu.MarkerButton.makeItClickable();
		});
	},
	getFocusedFieldObject: function () {
		return $('#active');
	},
	/**
	 * Move the focus next
	 * @public
	 */
	moveFocusNext: function () {
		var currentFocused = FieldList.getFocusedFieldObject();
		var nextField = currentFocused.parent().next('td:first');
		return FieldList.moveFocus(currentFocused, nextField);
	},
	/**
	 * Move the focus previous
	 * @public
	 */
	moveFocusPrev: function () {
		var currentFocused = FieldList.getFocusedFieldObject();
		var nextField = currentFocused.parent().prev();
		return FieldList.moveFocus(currentFocused, nextField);
	},
	/**
	 * Move the focus next
	 * @private
	 */
	moveFocus: function (currentFocusedField, nextField) {
		if (nextField.length) { // if found
			currentFocusedField.blur();
			nextField.click();
			return false;
		}
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
		$('#field_list td').click(FieldList.editContents);

		$('#bt-password').click(function() {
			$.jqDialog.password('パスワードを入力して下さい', function(data) {
				$('#passwd').val(data);
				new FieldForm().submit();
				$('#form-status').attr('action', 'form-commit.php?start');
				$('#form-status').submit();
			});
		});
	}
};
