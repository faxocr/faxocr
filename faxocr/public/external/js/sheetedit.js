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
var defaultid;
var dirty = false;
var target;
var tablesize_h = [];
var tablesize_w = [];

// a dirty hack for undefined "userpass"
if (typeof userpass == "undefined") {
    var userpass;
}

// debug code
function var_dump(obj){
	var $ret;
	if (typeof obj == "object") {
		for (i in obj){
			$ret = $ret + "(" + i + ")" + ' => ' + obj[i] + "\n";
		} 

		return $ret + "Type: " + typeof(obj) +
		    ((obj.constructor) ? "\nConstructor: " + obj.constructor : "") +
		    "\nValue: " + obj;
	} else {
		return "Type: "+ typeof(obj) + "\nValue: "+obj;
	}
}
// debug code

function cell_click() {
	var cell_id, loc, mtarget, col1pass, current_row = 0;

// taka: don't put the vars here. this is defined elsewhere, as a global variable.
//
//		userpass,
//		target,

	if (!dirty) {
		if (col1pass) {
			target = $(this);
			cell_id = target.attr("id");
			loc = cell_id.match(/\w+/g);
			mtarget = target.parent().children("td").first();
			col1pass = mtarget.text();
			current_row = 1 + loc[1];
			$.jqDialog.prompt("「" + col1pass + "」を入力して下さい", 
				function(data) {
					if (data == col1pass) {
						col1pass = "";
						target.click();
						mtarget.parent().css('backgroundColor', "#ffdddd");
					}
				});
			return;
		}
		if (userpass) {
			target = $(this);
			$.jqDialog.password("書込み用パスワードを入力して下さい", 
				function(data) {
					if (data == userpass) {
						userpass = ""; 
						current_row = 0;
						target.click();
					}
				});
			return;
		}
	} else {
		if (current_row) {
			cell_id = $(this).attr("id");
			loc = cell_id.match(/\w+/g);
			if (current_row != 1 + loc[1]){
				return;
			}
		}
	}

	if (!$(this).hasClass('on')) {

		/* for write protection */
		if (!$(this).attr("id")){
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
			html = html.replace(/(<br \/>|<br>)/g, "\n");
			//$("#debug").append(">" + tmp + "<BR>");

			$(this).html('<textarea id="active" rows="' + nlines + 
				     '" style="text-align:' + $(this).css('textAlign') +
				     ';width:' + width + 'px; height:' +
				     height + 'px; font-size:' + fsize + '; ">' +
				     html + '</textarea>');
		} else {
			$(this).html('<input type="text" id="active" value="'+txt+'" size="' +
			txt.length * 2 + '" style="text-align:' + $(this).css('textAlign') +
			'; width:' + width + 'px; font-size:' + fsize + '; " />');
		}

		if (defaultid == $(this).attr("id")){
			$(this).css('backgroundColor', defaultbg);
		}

		target = $('#active');
		target.focus();
		target.blur(function() {
			var inputVal = $(this).val(),
			    cell_id = 0,
			    htmlval,
			    $cmd,
			    $form;
			if  (target) {
				cell_id = target.parent().attr("id");
			} else {
				cell_id = $(this).parent().attr("id");
			}

			if (!cell_id){
				cell_id = $(this).parent().attr("id");
			}

			htmlval = inputVal.replace(/(\n)/g, "<br />");
			//$(this).parent().removeClass('on').html(htmlval);
			$(this).parent().removeClass('on').text('' + htmlval);

			if (!dirty) {
			   	document.getElementById('sbmt').disabled = null;
				dirty = true;
			}
			
			loc = cell_id.match(/\d+/g);
			if (loc[1] < tablesize_h[loc[0]] && loc[2] < tablesize_w[loc[0]]) {
				$cmd = "cell-";
			} else {
				$cmd = "apnd-";
			}
			// alert(tablesize_h[0] + "/" + tablesize_w[0] );

			$form = $("form#form-commit");
			inputVal = inputVal.replace(/<[^>]*>?/g, ''); // for prevent script injection
			$form.append("<input type=\"hidden\" name=\"" + $cmd +
				      cell_id + "\" value=\"" + inputVal + "\" />");
			// $("#debug").append(cell_id + "<BR>\n");
			target = undefined;
		});
	}
}

function on_keyup(e){
	var keycode,
		shift,
		cell_id,
		loc,
		active_table,
		ntarget,
		a;
//		target;

	if (e != null) {
	    // Mozilla(Firefox, NN) and Opera 
		keycode = e.which;
	    shift = typeof e.modifiers == 'undefined' ? e.shiftKey : e.modifiers & Event.SHIFT_MASK;
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

		if (!target){
			return true;
		}

		cell_id = target.parent().attr("id");
		loc = cell_id.match(/\w+/g);
		if (shift) {
			loc[1] = parseInt(loc[1]) - 1;
		} else {
			loc[1] = parseInt(loc[1]) + 1;
		}

		// scrollにより同じtableのcloneができてしまうため
		active_table = target.parent().parent().parent();
		ntarget = active_table.find("#" + loc[0] + "-" + loc[1] + "-" + loc[2]);

		target = $('#active');
		target.blur();

		if (ntarget){
			ntarget.click();
		}
	}
}

function on_keydown(e){
	var keycode,
		ctrl,
		shift,
   		cell_name,
		loc,
		ntarget,
		keychar,
		frm;
//		target;

	if (e != null) {
	        // Mozilla(Firefox, NN) and Opera 
	        keycode = e.which;
	        ctrl = typeof e.modifiers == 'undefined' ? e.ctrlKey : e.modifiers & Event.CONTROL_MASK;
	        shift = typeof e.modifiers == 'undefined' ? e.shiftKey : e.modifiers & Event.SHIFT_MASK;

		// Tab
		if (keycode == 9) {

			if (modal || !target){
				return true;
			}

			cell_name = target.parent().attr("name");
			loc = cell_name.match(/\w+/g);
			if (shift) {
				loc[1] = parseInt(loc[1]) - 1;
			} else {
				loc[1] = parseInt(loc[1]) + 1;
			}

			ntarget = $("*[name=" + loc[0] + "-" + loc[1] + "]");

			target.blur();
			if (ntarget) {
				ntarget.click();
			}

			e.preventDefault(); 
		    e.stopImmediatePropagation();
		    e.stopPropagation();
		}
	} else {
	        // Internet Explorer 
	        keycode = event.keyCode;
	        ctrl = event.ctrlKey;
	        shift = event.shiftKey;

	        // Tab
		if (keycode == 9) {

			if (modal || !target){
				return true;
			}

			cell_name = target.parent().attr("name");
			loc = cell_name.match(/\w+/g);
			if (shift) {
				loc[1] = parseInt(loc[1]) - 1;
			} else {
				loc[1] = parseInt(loc[1]) + 1;
			}

			ntarget = $("*[name=" + loc[0] + "-" + loc[1] + "]");

			target.blur();
			if (ntarget) {
				ntarget.click();
			}

			event.returnValue = false;
			event.cancelBubble = true;
		}
	}

	if (ctrl) {
		keychar = String.fromCharCode(keycode).toUpperCase();
	    if (keychar == "S") { 
			if (e) {
				e.preventDefault(); 
				e.stopPropagation(); 
			} else {
				event.returnValue = false;
				event.cancelBubble = true;
			}
			if (target){
				target.blur();
			}
			frm = document.getElementById('form-commit');
			// frm.action = frm.action + "?ret";
			frm.submit();
		}
		
		if (keychar == "B") {
			if (e) {
				e.preventDefault(); 
			    e.stopPropagation(); 
			} else {
				event.returnValue = false;
				event.cancelBubble = true;
			}
			
			if (target){
				target.blur();
			}

			//add_column();
			//add_row();
		}
	}
}

function add_row(){
	var target_table = $(".currentTab .sheet"),
		target_trs = target_table.find("tr"),
		target_tr = target_trs.last(),
		new_tr = target_tr.clone().css('backgroundColor', "#ffdddd"),
		new_th = new_tr.find("th"),
		new_td = new_tr.find("td"),
		target_row = target_trs.length - 1;

	new_th.text("N");

	// 最新ID取得
	var target_sheet = target_table.attr("id").match(/\d+/),
		target_number = 0,
		target_tds = target_table.find("tr").find("td"),
		$form,
		cell_id,
		cell_name,
		$cmd,
		$inputVal;

	target_tds.each(function() {
		if ($(this).attr("id"))
				target_number++;
		});

		$form = $("form#form-commit");

	// セル拡張
		new_td.each(function(target_column) {

			// Cell ID処理
			cell_id = target_sheet + "-" + target_row + "-" + target_column;
			cell_name = target_sheet + "-" + target_number++;

			// Cell追加処理
			$cmd = "apnd-";
			$inputVal = $(this).text();
			$form.append("<input type=\"hidden\" name=\"" + $cmd +
				      cell_id + "\" value=\"" + $inputVal +"\" />");

			$(this).attr("id", cell_id);
			$(this).attr("name", cell_name);
			$(this).click(cell_click);

			$(this).hover(function() {
				defaultbg = $(this).css('backgroundColor');
				defaultid = $(this).attr("id");
				if (defaultid)
					$(this).css('backgroundColor', "#ddffdd");
				else
					$(this).css('backgroundColor', "#dddddd");
				}, function() {
					$(this).css('backgroundColor', defaultbg);
				});
		    });

		target_table.append(new_tr);
}

function add_column(){
	var target_table = $(".currentTab .sheet"),

	// ヘッダ拡張
		target_ths = target_table.find("tr:first").find("th"),
		last_th = target_ths.last(),
		w = target_table.width() + last_th.width(),
		new_th = last_th.clone();
	target_table.width(w + "px");
	new_th.text("N");
	last_th.parent().append(new_th);

	// 最新ID取得
	var target_sheet = target_table.attr("id").match(/\d+/),
		target_column = target_ths.length - 1,
		$form,
		cell_id,
		$cmd,
		$inputVal,
		target_number;

	$form = $("form#form-commit");

	// セル拡張
	var target_tds = target_table.find("tr").find("td:last");
	target_tds.each(function(target_row) {

			// Cell ID処理
		cell_id = target_sheet + "-" + target_row + "-" + target_column;

			// Cell追加処理
		$cmd = "apnd-";
		$inputVal = $(this).text();
		$form.append("<input type=\"hidden\" name=\"" + $cmd +
				      cell_id + "\" value=\"" + $inputVal + "\" />");

		var new_td = $(this).clone().css('backgroundColor', "#ffdddd");
		$(this).parent().append(new_td);

		new_td.attr("id", cell_id);
		new_td.click(cell_click);

		new_td.hover(function() {
			defaultbg = $(this).css('backgroundColor');
			defaultid = $(this).attr("id");
			if (defaultid){
				$(this).css('backgroundColor', "#ddffdd");
			}else{
				$(this).css('backgroundColor', "#dddddd");
			}	
		}, function() {
			$(this).css('backgroundColor', defaultbg);
		});
	});

	// リナンバリング
	target_sheet = target_table.attr("id").match(/\d+/);
	target_number = 0;
	target_tds = target_table.find("tr").find("td");
	target_tds.each(function() {
		if (!$(this).attr("id")){
			return;
		}
		$(this).attr("name", target_sheet + "-" + target_number++);
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
		defaultid = $(this).attr("id");
		if (defaultid)
			$(this).css('backgroundColor', "#ddffdd");
		else
			$(this).css('backgroundColor', "#dddddd");
	
	}, function() {
		$(this).css('backgroundColor', defaultbg);
	});

	$(".sheet td").click(cell_click);

	document.onkeyup = on_keyup;
	document.onkeydown = on_keydown;

	// Tableサイズ取得
	$(".sheet").each(function(i) {
		//
		// XXX
		//
		// 書き込み位置がTable内か外かを判別するために、現在の
		// テーブルサイズを取得している。が、下記のような形で
		// 直接数えると、colspan等を使われることでカウントが
		// 誤ってしまうケースがある。それを防ぐためには、より
		// *-view.php側でgetColWidth()等をしてform-commitに
		// 埋め込めば良いはずですが、なぜそうしていないのかは
		// 不明。
		//
		var trs = $(this).find("tr"),
			ths = trs.first().find("th");
		if (ths.length) {
			tablesize_h[i] = trs.length - 1;
			tablesize_w[i] = ths.length - 1;
		} else {
			// form_viewの可能性
			ths = trs.first().find("td");
			tablesize_h[i] = trs.length;
			tablesize_w[i] = ths.length;
		}

		var $form = $("form#form-commit");
		$form.append("<input type=\"hidden\" name=\"sizeh-" +
				i + "\" value=\"" + tablesize_h[i] + "\" />");
		$form.append("<input type=\"hidden\" name=\"sizew-" +
				i+ "\" value=\"" + tablesize_w[i] + "\" />");
	});
});
