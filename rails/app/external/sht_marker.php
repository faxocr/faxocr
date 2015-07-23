<?php
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

require_once 'config.php';
require_once 'init.php';
require_once 'lib/common.php';
require_once "lib/sheet.php";
require_once 'lib/file_conf.php';
require_once 'contrib/peruser.php';

// define('border_sw', true);

// ファイルハンドリング
if (isset($file_id) && $file_id) {
	$tgt_file = DST_DIR . $file_id . ".xls";
} else {
	// XXX: $errmsg = "ファイルが読み込めません";
	put_err_page("不正なアクセスです");
	die;
}

$conf = new FileConf($file_id);

//
// ヘッダ処理
//
$header_opt .= "<link rel=\"stylesheet\" href=\"/external/css/jqDnR.css\" type=\"text/css\">\n";
$header_opt .= "<script type=\"text/javascript\" src=\"/external/js/jquery-1.4.1.min.js\"></script>\n";
$header_opt .= "<script type=\"text/javascript\" src=\"/external/js/jqDnR.js\"></script>\n";
//$header_opt .= "<script type=\"text/javascript\" src=\"/external/js/sheetedit.js\"></script>\n";
include( TMP_HTML_DIR . "tpl.header.html" );

// Excelファイル読み込み処理
if ($tgt_file) {
	list($xls, $errmsg) = excel_peruser_factory($charset, $tgt_file);
	$sheet = new Sheet($xls);
}

//
// エラーメッセージ処理
//
if ($errmsg) {
	print "<blockquote><font color=\"red\"><STRONG>";
	print strconv($errmsg);
	print "</STRONG></font></blockquote>";
}

{
	$target = $conf->get("target") == "registered" ? 1 : 0;

	// ステータス表示
	put_status($file_id, $group_id, $sheet_id);

	// アクションボタン表示
	$label_marker = "位置指定";
	if (file_exists(DST_DIR . $file_id . ".rb")) {
		$label_marker = "位置再指定";
	}
	print "<div class=\"clearfix\" style=\"padding: 10px 0; margin-bottom: 30px;\">\n";
	print "<button onclick=\"show_marker();\" style=\"z-index:10; float: left; margin-right: 10px;\">" . $label_marker . "</button>\n";
	print "<form action=\"/external/sht_config/\" method=\"post\" id=\"form-save\">\n";
	print "<input type=\"hidden\" name=\"fileid\" value=\"" . $file_id . "\" />\n";
	print "<input type=\"hidden\" name=\"gid\" value=\"" . $group_id . "\" />\n";
	print "<input type=\"hidden\" name=\"sid\" value=\"" . $sheet_id . "\" />\n";
	print "<input type=\"hidden\" name=\"target\" value=\"" . $target . "\" />\n";
	print "<input type=\"hidden\" name=\"scale\" value=\"" . $sheet->scale . "\" />\n";
	print "</form>\n";

	if (file_exists(DST_DIR . $file_id . ".rb")) {
		$orientation = ($conf->get("block_width") > $conf->get("block_height")) ? "Landscape" : "Portrait";
		print "<form action=\"/external/sht_config/\" method=\"post\">\n";
		print "<input type=\"hidden\" name=\"fileid\" value=\"" . $file_id . "\" />\n";
		print "<input type=\"hidden\" name=\"gid\" value=\"" . $group_id . "\" />\n";
		print "<input type=\"hidden\" name=\"sid\" value=\"" . $sheet_id . "\" />\n";
		print "<input type=\"hidden\" name=\"func\" value=\"generate\" />\n";
		print "<input type=\"hidden\" name=\"orient\" value=\"" . $orientation . "\" />\n";
		print "<input id=\"sbmt\" type=\"submit\" value=\"PDF生成\" />\n";
		print "</form>";
	}
	print "</div>\n";
}

//
// Excelファイル表示処理
//
if ($xls) {

	put_css($xls);

	// シート表示
	print "<center>\n";
	put_excel($xls, $sheet);
	print "</center>\n";
	print "<br />\n";
}

	print <<< STR
<script type="text/javascript">
<!--

function show_marker()
{
    table = $(".sheet_marker");
	$("#ex3").css("top", table.position().top).css("left", table.position().left).css("width", table.width()).css("height", (table.height()));

	$("#ex3").show("slow");
}

function hide_marker()
{
	var form = $('form#form-save');

	// block_width
	var w = parseInt($("#ex3").css("width"));
	var cmd = '<input type="hidden" name="block_width" ' +
		  'value="' + w + '" />';
	form.append(cmd);

	// block_height
	var h = parseInt($("#ex3").css("height"));
	var cmd = '<input type="hidden" name="block_height" ' +
		  'value="' + h + '" />';
	form.append(cmd);

	// block_size
	var cmd = '<input type="hidden" name="block_size" ' +
		  'value="' + prev_size + '" />';
	form.append(cmd);

	var m = $(".sheet_marker").get(0);
	var x = parseInt($("#ex3").css("left"));
	var cmd = '<input type="hidden" name="block_offsetx" ' +
		  'value="' + (x - m.offsetLeft) + '" />';
	form.append(cmd);

	var y = parseInt($("#ex3").css("top"));
	var cmd = '<input type="hidden" name="block_offsety" ' +
		  'value="' + (y - m.offsetTop) + '" />';
	form.append(cmd);

	// enable button
	$("#ex3").hide("slow");

	// XXX: experimental
	// document.getElementById('sbmt').disabled = null;
	$("#form-save").submit();
}

var $ = jQuery;

$().ready(function() {
	$('#ex3').jqDrag('.jqDrag').jqResize('.jqResize');
//	$('#ex3').jqResize('.jqResize');

	var btn = $('.statusMenu button:disabled');
	btn.parent().addClass('disable');
});

var last_size = $sheet->marker_size;
var prev_size = $sheet->marker_size;

function size_up() {
	var w = parseInt($("#ex3").css("width"));
	var block = w / prev_size;

	prev_size = prev_size + 1;
	$(".mark-img").each( function () {
		$(this).css("width", prev_size);
	});

	//$("#ex3").css("width", (block * prev_size) + "px");
}

function size_down() {
	var w = parseInt($("#ex3").css("width"));
	var block = w / prev_size;

	prev_size = prev_size > 1 ? (prev_size - 1) : 0;
	$(".mark-img").each( function () {
		$(this).css("width", prev_size);
	});

	//$("#ex3").css("width", (block * prev_size) + "px");
}

function go_prev() {
	$("#form-status").attr("action", "/external/sht_field/").submit();
}

function go_next() {
	$("#form-status").attr("action", "/external/sht_verify/").submit();
}

// -->
</script>

<div id="ex3" class="jqDnR" style="opacity:0.9; position: absolute; top:200px; left:100px;display:none">
<div class="jqDrag" style="height:100%">

<img src="/external/image/mark.gif" class="mark-img" style="top: 0;left: 0; width: {$sheet->marker_size}px;" />
<img src="/external/image/mark.gif" class="mark-img" style="top: 0;right: 0; width: {$sheet->marker_size}px;" />
<img src="/external/image/mark.gif" class="mark-img" style="bottom: 0;left: 0; width: {$sheet->marker_size}px;" />

<br /><br />
<center>
<h3>マーカー位置指定</h3>
<p>
マーカーが置かれるセルを指定します。<br>
マーカーの詳細な大きさは無視していただいて結構です。<br>
</p>

<h3>マーカーサイズ変更</h3>
縮小 <img src="/external/image/arrow-l.gif" onmousedown="size_down();" />
　　<img src="/external/image/arrow-r.gif" onmousedown="size_up();" /> 拡大<br />

<br /><br /><br />
<button onclick="hide_marker();" style="position:relative; z-index:10;">確定</button><br /><br />
</center>

</div>
<div class="jqResize"></div>
</div>

STR;

//
// フッタ読み込み
//
include( TMP_HTML_DIR . "tpl.footer.html" );

die;

function put_excel($xls, $sheet) {
	// シート表示
	// for ($sn = 0; $sn < 1; $sn++) {

	$sn = 0;
	{
		// シートテーブル表示
		print "<table class=\"sheet_marker\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"";
		print $sheet->disp->tblwidth . "\" bgcolor=\"#FFFFFF\" style=\"table-layout:fixed; border-collapse: collapse;\">\n";

		print "<tr>\n";
		for ($i = 0; $i <= $sheet->col_count; $i++) {
			$tdwidth  = $sheet->disp->get_col_size($i);
			print "<th height=\"0\" width=\"$tdwidth\"></th>";
		}
		print "\n</tr>\n";

		for ($r = 0; $r <= $sheet->row_count; $r++) {

			$trheight = $sheet->disp->get_row_size($r);
			print "  <tr height=\"" . $trheight . "\">" . "\n";

			for ($i = 0; $i <= $sheet->col_count; $i++) {
                $tdwidth  = $sheet->disp->get_col_size($i);

				$dispval = $xls->dispcell($sn, $r, $i);
				$dispval = strconv($dispval);
				if (isset($xls->hlink[$sn][$r][$i])){
					$dispval = "<a href=\"" . $xls->hlink[$sn][$r][$i] . "\">" . $dispval . "</a>";
				}

				$xf = $xls->getAttribute($sn, $r, $i);
				if (isset($xf['wrap']) && $xf['wrap'])
					$dispval = preg_replace('/\n/', '<br />', $dispval);
				$xfno = ($xf['xf'] > 0) ? $xf['xf'] : 0;

				$align = "x";
				if (isset($xf['halign']) && $xf['halign'] != 0)
					$align= "";
				if ($align == "x") {
					if ($xf['type'] == Type_RK) $align = " align=\"right\"";
					else if ($xf['type'] == Type_RK2) $align = " align=\"right\"";
					else if ($xf['type'] == Type_NUMBER) $align = " align=\"right\"";
					else if ($xf['type'] == Type_FORMULA && is_numeric($dispval)) $align = " align=\"right\"";
					else if ($xf['type'] == Type_FORMULA2 && is_numeric($dispval)) $align = " align=\"right\"";
					else if ($xf['type'] == Type_FORMULA && ($dispval=="TRUE" || $dispval=="FALSE")) $align = " align=\"center\"";
					else if ($xf['type'] == Type_FORMULA2 && ($dispval=="TRUE" || $dispval=="FALSE")) $align = " align=\"center\"";
					else if ($xf['type'] == Type_BOOLERR) $align = " align=\"center\"";
					else $align= '';
					if ($xf['format'] == "@") $align = "";
				} else {
					$align = "";
				}

				if (substr($dispval,0,1) == "'") $dispval = substr($dispval, 1);
				if (substr($dispval,0,6) == "&#039;") $dispval = substr($dispval, 6);

				// セル表示
				$bgcolor = ($xf['fillpattern'] == 1);

				$celattr =  $xls->getAttribute($sn, $r, $i);
				$fontsize =  $celattr["font"]["height"] * $sheet->scale / 16;
				if (isset($xls->celmergeinfo[$sn][$r][$i]['cond'])) {
					if ($xls->celmergeinfo[$sn][$r][$i]['cond'] == 1) {
						$colspan = $xls->celmergeinfo[$sn][$r][$i]['cspan'];
						$rowspan = $xls->celmergeinfo[$sn][$r][$i]['rspan'];
						if ($colspan > 1) {
							$rcspan = " colspan=\"" . $colspan . "\"";
						} else {
							$rcspan = " width=\"" . $tdwidth . "\"";
						}

						if ($rowspan > 1)
							$rcspan .= " rowspan=\"" . $rowspan . "\"";
						$class = " class=\"XFs" . $sn . "r" . $r . "c" . $i . "\"";
						$id = " id=\"". $sn . "-" . $r ."-" . $i . "\"";
						print " <td $class $rcspan $align style=\"font-size: " . $fontsize . "px;\">$dispval</td>\n";
					}
				} else {
					$class = " class=\"XF" . $xfno . "\" ";
					$id = " id=\"". $sn . "-" . $r . "-" . $i . "\"";

					print " <td nowrap=\"nowrap\" $class $align style=\"font-size: " . $fontsize . "px;\">$dispval</td>\n";
				}
			}
			print "</tr>\n";
		}
		print "</table>\n";

		// シート終了
	}
}

//
// ステータス操作エリア表示
//
function put_status($file_id, $group_id, $sheet_id)
{
	$status_label = file_exists(DST_DIR . $file_id . ".rb") ? "" : "disabled=\"disabled\"";

	// XXX
	print "<div class=\"statusMenu clearfix\">\n";
	print "<form method=\"post\" id=\"form-status\" >\n";
	print "<input type=\"hidden\" name=\"fileid\" value=\"" . $file_id . "\" />\n";
	// for sht_field
	print "<input type=\"hidden\" name=\"file_id\" value=\"" . $file_id . "\" />\n";
	print "<input type=\"hidden\" name=\"gid\" value=\"" . $group_id . "\" />\n";
	print "<input type=\"hidden\" name=\"sid\" value=\"" . $sheet_id . "\" />\n";

	print "<div class=\"upload disable\"><button type=\"button\" disabled=\"disabled\">再読み込み</button></div>\n";
	print "<div class=\"field\">&gt;<button type=\"button\" id=\"prev\" onclick=\"this.disabled=true; go_prev();\" >フィールド指定</button></div>\n";
	print "<div class=\"marker current\">&gt;<button type=\"button\" disabled=\"disabled\">マーカー指定</button></div>\n";
	print "<div class=\"verify\">&gt;<button type=\"button\" id=\"next\" onclick=\"this.disabled=true; go_next();\" {$status_label}>シート確認</button></div>\n";
	print "<div class=\"commit disable\">&gt;<button type=\"button\" disabled=\"disabled\">シート登録</button></div>\n";

	print "</form>\n";
	print "</div>\n";
}

?>
