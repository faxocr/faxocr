<?php
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

require_once 'config.php';
require_once 'init.php';
require_once 'lib/common.php';
require_once 'contrib/peruser.php';

// ファイルハンドリング
if (isset($file_id) && $file_id) {
	$tgt_file = DST_DIR . $file_id . ".xls";
} else {
	// XXX: $errmsg = "ファイルが読み込めません";
	put_err_page("不正なアクセスです");
	die;
}

//
// ヘッダ処理
//
$header_opt .= "<link rel=\"stylesheet\" href=\"/external/css/jqDnR.css\" type=\"text/css\">\n";
$header_opt .= "<script type=\"text/javascript\" src=\"/external/js/jquery-1.4.1.min.js\"></script>\n";
$header_opt .= "<script type=\"text/javascript\" src=\"/external/js/sheetedit.js\"></script>\n";
$header_opt .= "<script type=\"text/javascript\" src=\"/external/js/jqDnR.js\"></script>\n";
include( TMP_HTML_DIR . "tpl.header.html" );


// Excelファイル読み込み処理
if ($tgt_file && $errmsg === "") {

	$xls = NEW Excel_Peruser;
	$xls->setErrorHandling(1);
	$xls->setInternalCharset($charset);
	$result = $xls->fileread($tgt_file);

	if ($xls->isError($result)) {
		$errmsg = $result->getMessage();
		$xls = null;
	}
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
	// ステータス表示
	print "<table width=\"100%\">\n";
	print "<tr>\n";
	print "<td><button onclick=\"show_marker();\" style=\"z-index:10;\">マーカー</button></td>\n";
	print "<td align=\"right\"\"  width=\"450px\">";
	put_status();
	print "</td>\n";
	print "</tr></table>\n";
	print "<br />\n";
}

//
// Excelファイル表示処理
//
if ($xls) {

	put_css($xls);

	// シート表示
	print "<center>";
	put_excel_form($xls);
	print "</center>";
	print "<br>";

	// フッタ表示
	print "<form action=\"commit.php?ret\" method=\"POST\" id=\"form-commit\">";
	print "<input type=\"hidden\" name=\"file\" value=\"" . $file_id . "\" />";
	print "<input id=\"sbmt\" type=\"submit\" value=\"保存\" disabled/>";
	print "</form>";

	// XXX
	print "<form action=\"setting.php\" method=\"POST\" id=\"form-setting\">";
	print "<input type=\"hidden\" name=\"file\" value=\"" . $file_id . "\" />";
	print "<input type=\"hidden\" name=\"password\" id=\"passwd\" />";
	print "</form>";
}

//
// フッタ読み込み
//
include( TMP_HTML_DIR . "tpl.footer.html" );

	print <<< STR
<script type="text/javascript">
<!--

function show_marker()
{
	$("#ex3").show("slow");
}

function hide_marker()
{
	$("#ex3").hide("slow");
}

var $ = jQuery;

$().ready(function() {
  $('#ex3').jqDrag('.jqDrag').jqResize('.jqResize');
});

var last_size = 32;

function change_size(size) {

	$(".mark-img").each( function () {
		$(this).css("width", size);
	});

	var w = parseInt($("#instrctn").css("width"));
	var h = parseInt($("#instrctn").css("height"));

	$("#instrctn").css("height", h * size / last_size);
	$("#instrctn").css("width", w * size / last_size);

	last_size = size;
}

var last_time = 0;

function insn_move(d) {
	var new_time = +new Date();
	var diff = new_time - last_time;
	last_time = new_time;

	var ofst = $("#instrctn").offset();
	switch (d) {
		case 'u':
		ofst.top += Math.ceil(2000 / diff);
		break;

		case 'l':
		ofst.left -= Math.ceil(2000 / diff);
		break;

		case 'r':
		ofst.left += Math.ceil(2000 / diff);
		break;

		case 'd':
		ofst.top -= Math.ceil(2000 / diff);
		break;
	}
	$("#instrctn").offset(ofst);
}

var rot = Array(
	'insn-01.gif',
	'insn-02.gif',
	'insn-03.gif',
	'insn-04.gif'
);

var rotation = 0;
var magnitude = 0.2;

function insn_rotate() {
	rotation = (rotation > 2) ? 0 : rotation + 1;
	var w = $("#instrctn").css("width");
	var h = $("#instrctn").css("height");

	$("#instrctn").attr("src", "./image/" + rot[rotation]);
	$("#instrctn").css("width", h);
	$("#instrctn").css("height", w);
}

-->
</script>

<div id="ex3" class="jqDnR" style="opacity:0.8; position: absolute; top:100px; left:50px;display:none">
<div class="jqDrag" style="height:100%">

<img src="/image/mark.gif" class="mark-img" style="top: 0;left: 0">
<img src="/image/mark.gif" class="mark-img" style="top: 0;right: 0">
<img src="/image/mark.gif" class="mark-img" style="bottom: 0;left: 0">

<BR><BR>
<center>
<h3>マーカーサイズ</h3>

<input type="radio" name="size" onclick="change_size(64);">特大
<input type="radio" name="size" onclick="change_size(48);">大
<input type="radio" name="size" onclick="change_size(32);" checked>中
<input type="radio" name="size" onclick="change_size(16);">小<BR>

<h3>用紙方向</h3>

<img valign="bottom" src="/image/direc-01.gif"><input type="radio" name="drctn" onclick="change_drctn(false);" checked>横
<img valign="bottom" src="/image/direc-02.gif"><input type="radio" name="drctn" onclick="change_drctn(true);">縦<BR>


<h3>見本位置</h3>

<table style="position:relative; z-index:10;">
<tr><td></td><td><img src="/image/arrow-u.gif" onmousedown="insn_move('d');" ></td><td></td><td></td></tr>
<tr><td><img src="/image/arrow-l.gif" onmousedown="insn_move('l');" ></td><td></td><td><img src="/image/arrow-r.gif" onmousedown="insn_move('r');" >
</td><td>　　<img src="/image/rotate.gif" onclick="insn_rotate();" ></td></tr>
<tr><td></td><td><img src="/image/arrow-d.gif" onmousedown="insn_move('u');" >
</td><td></td><td></td></tr>
</table>

<BR><BR><BR>
<button onclick="hide_marker();" style="position:relative; z-index:10;">確定</button><BR><BR>
</center>

<img id="instrctn" valign="bottom" src="/image/insn-01.gif" style="position:relative; top:-100px; left:10px; z-index:0; width:200px;">

</div>
<div class="jqResize"></div>
</div>

STR;

die;

function put_excel_form($xls) {

	// シート表示
	// for ($sn = 0; $sn < 1; $sn++) {

	$sn = 0; 
	{
		// $fnum = 0;

		$w = 32;
		if (!isset($xls->maxcell[$sn]))
			$xls->maxcell[$sn] = 0;
		for ($i = 0; $i <= $xls->maxcell[$sn]; $i++) {
			$w += $xls->getColWidth($sn, $i);
		}
// XXX
$w = $w / 2;

		// シートテーブル表示
		print "<table class=\"sheet\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"";
		print ${w} . " bgcolor=\"#FFFFFF\" style=\"border-collapse: collapse;\">";

		if (!isset($xls->maxrow[$sn]))
			$xls->maxrow[$sn] = 0;
		for ($r = 0; $r <= $xls->maxrow[$sn]; $r++) {
			// XXX
			$trheight = $xls->getRowHeight($sn, $r) * 0.8;
			print "  <tr height=\"" . $trheight . "\">" . "\n";
			for ($i = 0; $i <= $xls->maxcell[$sn]; $i++) {

// XXX
				$tdwidth = $xls->getColWidth($sn, $i) / 2;
				$dispval = $xls->dispcell($sn, $r, $i);
				$dispval = strconv($dispval);
				if (isset($xls->hlink[$sn][$r][$i])){
					$dispval = "<a href=\"" . $xls->hlink[$sn][$r][$i] . "\">" . $dispval . "</a>";
				}

				$xf = $xls->getAttribute($sn, $r, $i);
				if (isset($xf['wrap']) && $xf['wrap'])
					$dispval = ereg_replace("\n", "<br />", $dispval);
				$xfno = ($xf['xf'] > 0) ? $xf['xf'] : 0;

				$align = "x";
				if (isset($xf['halign']) && $xf['halign'] != 0)
					$align= "";
				if ($align == "x") {
					if ($xf['type'] == Type_RK) $align = " Align=\"right\"";
					else if ($xf['type'] == Type_RK2) $align = " Align=\"right\"";
					else if ($xf['type'] == Type_NUMBER) $align = " Align=\"right\"";
					else if ($xf['type'] == Type_FORMULA && is_numeric($dispval)) $align = " Align=\"right\"";
					else if ($xf['type'] == Type_FORMULA2 && is_numeric($dispval)) $align = " Align=\"right\"";
					else if ($xf['type'] == Type_FORMULA && ($dispval=="TRUE" || $dispval=="FALSE")) $align = " Align=\"center\"";
					else if ($xf['type'] == Type_FORMULA2 && ($dispval=="TRUE" || $dispval=="FALSE")) $align = " Align=\"center\"";
					else if ($xf['type'] == Type_BOOLERR) $align = " Align=\"center\"";
					else $align= '';
					if ($xf['format'] == "@") $align = "";
				} else {
					$align = "";
				}

				if (substr($dispval,0,1) == "'") $dispval = substr($dispval, 1);
				if (substr($dispval,0,6) == "&#039;") $dispval = substr($dispval, 6);

				// セル表示
				$bgcolor = ($xf['fillpattern'] == 1);

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
						print " <td $class $rcspan $align>$dispval</td>\n";
					}
				} else {
					$class = " class=\"XF" . $xfno . "\" ";
					$id = " id=\"". $sn . "-" . $r . "-" . $i . "\"";
					$width = "width=\"" . $tdwidth . "\" ";

					print " <td $class $width $align>$dispval</td>\n";
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
function put_status()
{
	global $file_id;
	global $group_id;
	global $sheet_id;

	$style = array();
	$style["normal"] = "style=\"border-style:solid;border-width:1px;border-color:#dddddd;background-color:#ffffff;padding:1px;color:gray\"";
	$style["gray"] = "style=\"border-style:solid;border-width:1px;border-color:#dddddd;background-color:#bbbbbb;padding:1px\"";
	$style["lgray"] = "style=\"border-style:solid;border-width:1px;border-color:#dddddd;background-color:#dddddd;padding:1px\"";
	$style["pink"] = "style=\"border-style:solid;border-width:1px;border-color:#dddddd;background-color:#ffdddd;padding:1px\"";

	// XXX
	print "<form action=\"/external\sht_verify/\" method=\"POST\" id=\"form-commit\">\n";
	print "<input type=\"hidden\" name=\"file\" value=\"" . $file_id . "\" />\n";
	print "<input type=\"hidden\" name=\"gid\" value=\"" . $group_id . "\" />\n";
	print "<input type=\"hidden\" name=\"sid\" value=\"" . $sheet_id . "\" />\n";

	print "<div style=\"border-style:solid;border-color:#dddddd;border-width:1px;padding:2px;\" class=\"statusMenu\">\n";
	print "<div ${style["gray"]}><span>フィールド指定</span></div>\n";

	print "<div ${style["pink"]}><span>マーカー指定</span></div>\n";
	print "<div ${style["lgray"]}><button id=\"next\" onclick=\"this.form.submit();\">シート確認</button></div>\n";
	print "<div ${style["gray"]}><span>シート登録</span></div>\n";
	print "</div>\n";

	print "</form>\n";
}

?>
