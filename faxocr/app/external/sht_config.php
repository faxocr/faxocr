<?php
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

require_once 'config.php';
require_once 'init.php';
require_once 'lib/common.php';
require_once 'contrib/peruser.php';

// ファイルハンドリング
if (isset($file_id) && $file_id) {
	$tgt_file = DST_DIR . $file_id . ".xls";
} else {
	put_err_page("不正なアクセスです");
	die;
}

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
// HTMLファイル作成処理
//
if ($xls) {
	$html = put_header();
	$html .= put_css($xls);
	$html .= put_excel($xls);
	$html .= put_footer();

	file_put_contents(DST_DIR . $file_id . ".html", $html);
}

die;

function put_header()
{
	$html = << STR
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta http-equiv="Content-Style-Type" content="text/css" />
  <meta http-equiv="Content-Script-Type" content="text/javascript" />
  <title>Shinsai FaxOCR</title>
  <link rel="stylesheet" href="/external/css/jqDnR.css" type="text/css">
  <script type="text/javascript" src="/external/js/jquery-1.4.1.min.js"></script>
  <script type="text/javascript" src="/external/js/jqDnR.js"></script>
</head>
<body>

STR;
	return $html
}

function put_footer()
{
	$html = <<< STR

<script type="text/javascript">
<!--

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

<div id="ex3" class="jqDnR" style="opacity:0.8; position: absolute; top:100px; left:50px;">
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

	return $html
}

function put_excel($xls)
{
	var $html;

	// シート表示
	// for ($sn = 0; $sn < 1; $sn++) {

	$sn = 0; 
	{
		$w = 32;
		if (!isset($xls->maxcell[$sn]))
			$xls->maxcell[$sn] = 0;
		for ($i = 0; $i <= $xls->maxcell[$sn]; $i++) {
			$w += $xls->getColWidth($sn, $i);
		}

		// XXX
		$w = $w / 2;

		// シートテーブル表示
		$html .= "<table class=\"sheet\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"";
		$html .= ${w} . " bgcolor=\"#FFFFFF\" style=\"border-collapse: collapse;\">";

		if (!isset($xls->maxrow[$sn]))
			$xls->maxrow[$sn] = 0;
		for ($r = 0; $r <= $xls->maxrow[$sn]; $r++) {
			// XXX
			$trheight = $xls->getRowHeight($sn, $r) * 0.8;
			$html .= "  <tr height=\"" . $trheight . "\">" . "\n";
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
						$html .= " <td $class $rcspan $align>$dispval</td>\n";
					}
				} else {
					$class = " class=\"XF" . $xfno . "\" ";
					$id = " id=\"". $sn . "-" . $r . "-" . $i . "\"";
					$width = "width=\"" . $tdwidth . "\" ";

					$html .= " <td $class $width $align>$dispval</td>\n";
				}
			}
			$html .= "</tr>\n";
		}
		$html .= "</table>\n";

		// シート終了
	}

	return $html;
}

?>
