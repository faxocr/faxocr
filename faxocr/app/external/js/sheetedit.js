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

// for escape html tag
(function($) {
	$.escapeHTML = function(val) {
		return $('<div />').text(val).html();
	};
})(jQuery);

// global variables (do not move)
var defaultbg,
    defaultid,
    dirty = false,
    target,
    tablesize_h = [],
    tablesize_w = [],
    col1pass,
    current_row;

// a dirty hack for undefined "userpass"
if (typeof userpass == 'undefined') {
	var userpass;
}

function cell_click() {
	var cell_id,
	    loc,
	    mtarget;

// taka: don't put the vars here. this is defined elsewhere,
//       as global variables.
//
//		userpass,
//		target,
//		current_row

	if (!dirty) {
		if (col1pass) {
			target = $(this);
			cell_id = target.attr('id');
			loc = cell_id.match(/\w+/g);
			mtarget = target.parent().children('td').first();
			col1pass = mtarget.text();
			current_row = 1 + loc[1];
			$.jqDialog.prompt('「' + col1pass + '」を入力して下さい', function(data) {
				if (data == col1pass) {
					col1pass = '';
					target.click();
					mtarget.parent().css('backgroundColor', '#ffdddd');
				}
			});
			return;
		}
		if (userpass) {
			target = $(this);
			$.jqDialog.password('書込み用パスワードを入力して下さい', function(data) {
				if (data == userpass) {
					userpass = ''; 
					current_row = 0;
					target.click();
				}
			});
			return;
		}
	} else {
		if (current_row) {
			cell_id = $(this).attr('id');
			loc = cell_id.match(/\w+/g);
			if (current_row != 1 + loc[1]) {
				return;
			}
		}
	}

	if (!$(this).hasClass('on')) {

		/* for write protection */
		if (!$(this).attr('id')) {
			return;
		}

		$(this).addClass('on');
		var txt = $(this).text(),
			html =  $(this).html(),
			width = $(this).width(),
			height = $(this).height(),
			fsize = $(this).css('font-size'),
			nlines = height / 16 | 0; // int convert

		if (nlines > 1) {
			html = html.replace(/(<br \/>|<br>)/g, '\n');

			$(this).html('<textarea id="active" rows="'
				+ nlines
				+ '" style="text-align:'
				+ $(this).css('textAlign')
				+ ';width:'
				+ width
				+ 'px; height:'
				+ height
				+ 'px; font-size:'
				+ fsize
				+ '; ">'
				+ html
				+ '</textarea>'
			);
		} else {
			$(this).html('<input type="text" id="active" value="'
				+ txt+'" size="'
				+ txt.length * 2
				+ '" style="text-align:'
				+ $(this).css('textAlign')
				+ '; width:'
				+ width
				+ 'px; font-size:'
				+ fsize
				+ '; " />'
			);
		}

		target = $('#active');

		if (!$.support.noCloneEvent) {
			target.bind('focus', function() {
				$(this).addClass('focus');
			}).bind('blur', function() {
				$(this).removeClass('focus');
			});
		}

		if (defaultid == $(this).attr('id')) {
			$(this).css('backgroundColor', defaultbg);
		}

		target.focus();
		if (!$.support.noCloneEvent) {// force to focus IE
			target.focus();
		}
		
		target.blur(function() {
			var inputVal = $(this).val(),
				cell_id = target.parent().attr('id'),
				htmlval,
				$cmd,
				$form;

			if  (target) {
				cell_id = target.parent().attr('id');
			} else {
				cell_id = $(this).parent().attr('id');
			}

			if (!cell_id) {
				cell_id = $(this).parent().attr('id');
			}

			htmlval = inputVal.replace(/(\n)/g, '<br />');
			htmlval = htmlval.replace(/<[^>]*>?/g, ''); // for prevent script injection
			$(this).parent().removeClass('on')
							.text('' + htmlval);

			if (!dirty) {
				document.getElementById('sbmt').disabled = null;
				dirty = true;
			}
			
			loc = cell_id.match(/\d+/g);
			if (loc[1] < tablesize_h[loc[0]] && loc[2] < tablesize_w[loc[0]]) {
				$cmd = 'cell-';
			} else {
				$cmd = 'apnd-';
			}

			$form = $('form#form-commit');

			inputVal = $.escapeHTML(inputVal);// improve escape
			$form.append('<input type=\"hidden\" name=\"' + $cmd + cell_id + '\" value=\"' + inputVal + '\" />');

			target = undefined;
		});
	}
}


function add_row() {
	var target_table = $('.currentTab .sheet'),
	    target_trs = target_table.find('tr'),
	    target_tr = target_trs.last(),
	    new_tr = target_tr.clone().css('backgroundColor', '#ffdddd'),
	    new_th = new_tr.find('th'),
	    new_td = new_tr.find('td'),
	    target_row = target_trs.length - 1;

	new_th.text('N');

	// 最新ID取得
	var target_sheet = target_table.attr('id').match(/\d+/),
	    target_number = 0,
	    target_tds = target_table.find('tr').find('td'),
	    $form,
	    cell_id,
	    cell_name,
	    $cmd,
	    $inputVal;

	target_tds.each(function() {
		if ($(this).attr('id')) {
			target_number++;
		}
	});

	$form = $('form#form-commit');

	// セル拡張
	new_td.each(function(target_column) {

		// Cell ID処理
		cell_id = target_sheet + '-' + target_row + '-' + target_column;
		cell_name = target_sheet + '-' + target_number++;

		// Cell追加処理
		$cmd = 'apnd-';
		$inputVal = $(this).text();

		$inputVal = $inputVal.replace(/<[^>]*>?/g, '');

		$form.append('<input type=\"hidden\" name=\"' + $cmd + cell_id + '\" value=\"' + $inputVal + '\" />');

		$(this).attr('id', cell_id);
		$(this).attr('name', cell_name);
		$(this).click(cell_click);

		$(this).hover(function() {
			defaultbg = $(this).css('backgroundColor');
			defaultid = $(this).attr('id');
			if (defaultid) {
				$(this).css('backgroundColor', '#ddffdd');
			} else {
				$(this).css('backgroundColor', '#dddddd');
			}
		}, function() {
			$(this).css('backgroundColor', defaultbg);
		});
	});

	target_table.append(new_tr);
}

function add_column() {
	var target_table = $('.currentTab .sheet'),
	    target_ths = target_table.find('tr:first').find('th'),
	    last_th = target_ths.last(),
	    w = target_table.width() + last_th.width(),
	    new_th = last_th.clone();

	target_table.width(w + 'px');
	new_th.text('N');
	last_th.parent().append(new_th);

	// 最新ID取得
	var target_sheet = target_table.attr('id').match(/\d+/),
	    target_column = target_ths.length - 1,
	    $form,
	    cell_id,
	    $cmd,
	    $inputVal,
	    target_number;

	$form = $('form#form-commit');

	// セル拡張
	var target_tds = target_table.find('tr').find('td:last');

	target_tds.each(function(target_row) {

		// Cell ID処理
		cell_id = target_sheet + '-' + target_row + '-' + target_column;

		// Cell追加処理
		$cmd = 'apnd-';
		$inputVal = $(this).text();
		$inputVal = $inputVal.replace(/<[^>]*>?/g, '');

		$form.append('<input type=\"hidden\" name=\"' + $cmd + cell_id + '\" value=\"' + $inputVal + '\" />');

		var new_td = $(this).clone().css('backgroundColor', '#ffdddd');

		$(this).parent().append(new_td);

		new_td.attr('id', cell_id);
		new_td.click(cell_click);

		new_td.hover(function() {
			defaultbg = $(this).css('backgroundColor');
			defaultid = $(this).attr('id');
			if (defaultid) {
				$(this).css('backgroundColor', '#ddffdd');
			} else {
				$(this).css('backgroundColor', '#dddddd');
			}
		}, function() {
			$(this).css('backgroundColor', defaultbg);
		});
	});

	// リナンバリング
	target_sheet = target_table.attr('id').match(/\d+/);
	target_number = 0;
	target_tds = target_table.find('tr').find('td');
	target_tds.each(function() {
		if (!$(this).attr('id')) {
			return;
		}
		$(this).attr('name', target_sheet + '-' + target_number++);
	});	
}

jQuery(function($) {

	//
	// XXX
	//
	// 下記のhover処理は、cssでのhoverによるbackground切り替えを
	// 知らなかった名残だったかと思います。可能であれば削りたい。
	//
	$('.sheet td').hover(function() {
		defaultbg = $(this).css('backgroundColor');
		defaultid = $(this).attr('id');
		if (defaultid) {
			$(this).css('backgroundColor', '#ddffdd');
		} else {
			$(this).css('backgroundColor', '#dddddd');
		}
	}, function() {
		$(this).css('backgroundColor', defaultbg);
	});

	$('.sheet td').click(cell_click);

	// Tableサイズ取得
	$('.sheet').each(function(i) {
		//
		// XXX
		//
		// 書き込み位置がTable内か外かを判別するために、現在の
		// テーブルサイズを取得している。が、下記のような形で
		// 直接数えると、colspan等を使われることでカウントが
		// 誤ってしまうケースがある。それを防ぐために、カラの
		// <td>等をphpで生成してその数を数えている様子。しかし、
		// ここは*-view.php側でgetColWidth()等をして値を渡すな
		// りすれば良いはずが(あるいはform-commitに細工？)、
		// なぜそうしていないのかは不明。
		//
		var trs = $(this).find('tr'),
			ths = trs.first().find('th');

		if (ths.length) {
			tablesize_h[i] = trs.length - 1;
			tablesize_w[i] = ths.length - 1;
		} else {
			// form_viewの可能性
			ths = trs.first().find('td');
			tablesize_h[i] = trs.length;
			tablesize_w[i] = ths.length;
		}

		var $form = $('form#form-commit');

		$form.append('<input type=\"hidden\" name=\"sizeh-' + i + '\" value=\"' + tablesize_h[i] + '\" />');
		$form.append('<input type=\"hidden\" name=\"sizew-' + i + '\" value=\"' + tablesize_w[i] + '\" />');
	});
});


var SHEET = SHEET || {};// namespace

SHEET = {
	
	PAGEVAL: 0,
	pressKeyDown: function(e) {// press tab key on cell

		var keycode,
		    ctrl,
		    shift,
		    cell_name,
		    cell_id,
		    loc,
		    ntarget,
		    keyChar,
		    frm;

		keycode = e.which || e.keyCode;
		shift = e.shiftKey;
		ctrl = e.ctrlKey;

		if (keycode === 9) {
			if (target) {
				cell_name = target.parent().attr('name');
				if (!cell_name) {
					if ($('#jsiUserPass').length > 0 || $('#jsiColPass').length > 0) {
						return false;
					}
				}
				loc = cell_name.match(/\w+/g);
				loc[1] = (shift) ? (loc[1] * 1 - 1) : (loc[1] * 1 + 1);
		
				ntarget = $('*[name=' + loc[0] + '-' + loc[1] + ']');
				target.blur();
				if (ntarget) {
					ntarget.click();
				}
			}
			e.preventDefault();
		}

		if (ctrl) {
			keyChar = String.fromCharCode(keycode).toUpperCase();
			if (keyChar === 'S') {
				e.preventDefault();
				if (target) {
					target.blur();
				}

				frm = document.getElementById('form-commit');
				frm.submit();
			}
			if (keyChar === 'B') {
				e.preventDefault();
				if (target) {
					target.blur();
				}
			}
		}
	},
	pressKey: function(e) {// press enter key on cell
		var keycode,
		    shift,
		    cell_name,
		    cell_id,
		    loc,
		    ntarget,
		    keyChar,
		    frm,
		    active_table,
		    a;

		keycode = e.which || e.keyCode;
		shift = e.shiftKey;

		if (keycode === 13) {
			e.preventDefault();

		    /*
			if (modal) {
				// password modal
				$('#jqDialog_ok').click();
				return false;
			}
		    */

			if (target) {
				cell_id = target.parent().attr('id');
				loc = cell_id.match(/\w+/g);

				// scrollにより同じtableのcloneができてしまうため
				active_table = target.parent().parent().parent();

				loc[1] = (shift) ? (loc[1] * 1 - 1) : (loc[1] * 1 + 1);
				ntarget = active_table.find('#' + loc[0] + '-' + loc[1] + '-' + loc[2]);

				target = $('#active');
				target.blur();

				if (ntarget) {
					ntarget.click();
				}
			}
		}
	},
	// form-view.phpでのページ指定で移動のページ読み込み時の値チェック
	readInitialVal: function() {
		var $pager = $('#jsiPager'),
			$pageInput = $pager.find('input');

		SHEET.PAGEVAL = $pageInput.val();
	},
	// form-view.phpでのページ指定で移動
	changePage: function(e) {
		var $dataUrl = $('#jsiDataUrl'),
			urlVal = $dataUrl.val(),
			$target = $(e.target),
			targetVal = $target.val();

		if ((e.keyCode || e.which) === 13 &&
		    targetVal !== SHEET.PAGEVAL) {
			if (((targetVal - 0) == targetVal) &&
			    targetVal.length > 0) {
				window.location.href = urlVal + targetVal;
			}
		}
	},
	// to prevent backspace key
	preventBackSpace: function(e) {
		var keycode = e.which || e.keyCode,
		    $inputs = $('input, textarea'),
		    $target = $(e.target),
		    targetNodeName = $target[0].nodeName;

		if (keycode === 8 && targetNodeName !== 'INPUT' &&
		    targetNodeName !== 'TEXTAREA') {
			e.preventDefault();
		}
	},
	setUrl: function() {
		var $hiddenUrl = $('#jsiHiddenUrl'),
		    $hidden = $hiddenUrl.find('input'),
		    urlVal = $hidden.val(),
		    $btPass = $('#bt-password');
		
		$btPass.click(function() {
			window.location.href = urlVal;
		});	
	},
	items_rev: '',
	// form-target.php & form-select.phpのセレクトボックス設定
	setSelect: function() {
		var $selectHidden = $('#jsiSelectHidden'),
		    $selects = $selectHidden.find('input'),
		    valData = [],
		    $parent,
		    $child;
		
		for (var i = 0, max = $selects.length; i < max; i++) {
			valData[i] = (i !== 0) ? $.parseJSON($selects.eq(i).val()) : $selects.eq(i).val() * 1;
			if (i === (max - 1)) {
				SHEET.items_rev = valData[i];
			}
		}

		if (valData[0] === 1) {
			set_select('select_1', valData[1], null, null);
		} else if (valData[0] === 2) {
			set_select('select_2', valData[1], 'select_1', valData[2]);
		} else if (valData[0] === 3) {
			set_select('select_3', valData[1], 'select_2', valData[2]);
			set_select('select_2', valData[2], 'select_1', valData[3]);
		}

		function set_select(parent, p_items, child, c_items) {
			var options, parentValue;

			// parent
			options = null;
			parentValue = p_items[0][0];

			for (var i = 0; i < p_items.length; i++) {
				e = p_items[i];
				if (e[0] == parentValue) {
					options = options + '<option value=\"' + e[2] + '\">' + e[1] + '</option>';
				}
			}
			$parent = $('#' + parent);
			$parent.html(options);

			if (c_items == null) {
				return;
			}

			// child
			options = null;
			parentValue = p_items[0][2];

			for (var j = 0; j < c_items.length; j++) {
				e = c_items[j];
				if (e[0] == parentValue) {
					options = options + '<option value=\"' + e[2] + '\">' + e[1] + '</option>';
				}
			}

			$child = $('#' + child);
			$child.html(options);

			var defaultVal = SHEET.items_rev[$child.find(':selected').val()];
			$('#reporter').val(defaultVal);

			// XXX: experimental
			//$('#' + child).change( function() {
			$child.change(function() {
				$('#reporter').val(SHEET.items_rev[$(this).val()]);
			});

			$parent.change(function() {
				var options = null;
				var parentValue = $('#' + parent).attr('value');
				var $child = $('#' + child);

				for (var k = 0; k < c_items.length; k++) {
					e = c_items[k];
					if (e[0] == parentValue) {
						options = options + '<option value=\"' + e[2] + '\">' + e[1] + '</option>';
					}
				}
				// taka: don't change this "html" to "val".
				// the selector algorithm would fail.
				$child.html(options);
				$('#' + child).trigger('change');
				$child.focus();
			});
		}
	},
	setPrompt: function() {// セル・ユーザー認証の設定
		var $userPass = $('#jsiUserPass'),
			$colPass = $('#jsiColPass');

		userpass = $userPass.val();
		col1pass = $colPass.val();

		$('#bt-password').click(function() {
			$.jqDialog.password('パスワードを入力して下さい', function(data) { 
				$('#passwd').val(data);
				$('#form-setting').submit();
			});
		});
	},
	setScrollVal: function() {// set the value of simpletab's scroll bar
		var $scrollHidden = $('#jsiScrollHidden'),
			$hiddens = $scrollHidden.find('input'),
			scrollW,
			scrollH,
			headerRow,
			headerColumn;

		scrollW = $hiddens.eq(0).val() *1;
		scrollH = $hiddens.eq(1).val() *1;
		headerRow = $hiddens.eq(2).val() *1;
		headerColumn = $hiddens.eq(3).val() *1;

		$('div.simpleTabsContent').css('overflow-x', 'hidden');
		jQuery.event.add(window, 'load', function() {
			$('div.simpleTabsContent').each(function() {
				var v = $(this).css('display');
				if (v == 'block') {
					t = $(this).children('table.sheet');
					t.tablefix({
						width: scrollW,
						height: scrollH,
						fixRows: headerRow,
						fixCols: headerColumn
					});
				} else {
					t = $(this).children('table.sheet');
					ctab = t.attr('id').match(/\d+/);
					$('[name=tab-' + ctab + ']').one('click', {ctab: ctab}, function(e) {
						$('#tablefix-' + e.data.ctab).tablefix({
							width: scrollW,
							height: scrollH,
							fixRows: headerRow,
							fixCols: headerColumn
						});
					});
				}
			});
		});
	}
};

var $doc = $(document);

$doc.bind('keydown', function(e) {
	SHEET.pressKeyDown(e);
	SHEET.preventBackSpace(e);
});

$doc.bind('keypress', function(e) {
	SHEET.pressKey(e);
});

$doc.ready(function() {
	var $page = $('#jsiPager'),
	    $pageInput = $page.find('input');
	
	SHEET.readInitialVal();

	$pageInput.bind('keypress blur', function(e) {
		SHEET.changePage(e);
	});

	if ($('#jsiSelectHidden').length > 0) {
		SHEET.setSelect();
	}
	if ($('#jsiPass').length > 0) {
		SHEET.setPrompt();
	}

	if ($('#jsiScrollHidden').length > 0) {
		SHEET.setScrollVal();
	}
	if ($('#jsiHiddenUrl').length > 0) {
		SHEET.setUrl();
	}
});
