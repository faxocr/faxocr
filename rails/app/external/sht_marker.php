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
	global $conf;

	if (!$conf)
		$conf = new FileConf($file_id);
	
	$target = $conf->get("target") == "registered" ? 1 : 0;
	
	// ステータス表示
	print "<table width=\"100%\">\n";
	print "<tr>\n";
	print "<td>\n";

	print "<button onclick=\"show_marker();\" style=\"z-index:10;\">位置指定</button>\n";
	print "<form action=\"/external/sht_config/\" method=\"post\" id=\"form-save\">\n";
	print "<input type=\"hidden\" name=\"fileid\" value=\"" . $file_id . "\" />\n";
	print "<input type=\"hidden\" name=\"gid\" value=\"" . $group_id . "\" />\n";
	print "<input type=\"hidden\" name=\"sid\" value=\"" . $sheet_id . "\" />\n";
	print "<input type=\"hidden\" name=\"target\" value=\"" . $target . "\" />\n";
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
		print " (時間が掛かります)";
		print "</form>";
	}

	print "</td>\n";
	print "<td align=\"right\" width=\"450px\">";
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
	print "<center>\n";
	put_excel($xls);
	print "</center>\n";
	print "<br />\n";
}

	print <<< STR
<script type="text/javascript">
<!--

function show_marker()
{
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
		  'value="' + last_size + '" />';
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
});

var last_size = $marker_size;

function size_up() {
	last_size = last_size + 1;
	$(".mark-img").each( function () {
		$(this).css("width", last_size);
	});
}

function size_down() {
	last_size = last_size > 17 ? (last_size - 1) : 16;
	$(".mark-img").each( function () {
		$(this).css("width", last_size);
	});
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

<img src="/image/mark.gif" class="mark-img" style="top: 0;left: 0; width: {$marker_size}px;" />
<img src="/image/mark.gif" class="mark-img" style="top: 0;right: 0; width: {$marker_size}px;" />
<img src="/image/mark.gif" class="mark-img" style="bottom: 0;left: 0; width: {$marker_size}px;" />

<br /><br />
<center>
<h3>マーカーサイズ</h3>

縮小 <img src="/image/arrow-l.gif" onmousedown="size_down();" />
　　<img src="/image/arrow-r.gif" onmousedown="size_up();" /> 拡大<br />

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

$marker_size = 0;

function put_excel($xls) {

	global $marker_size;
	
	// シート表示
	// for ($sn = 0; $sn < 1; $sn++) {

	$sn = 0;
	{
		$tblwidth = 0;
		$tblheight = 0;
		for ($i = 0; $i <= $xls->maxcell[$sn]; $i++) {
			$tblwidth += $xls->getColWidth($sn, $i);
		}
		for ($i = 0; $i <= $xls->maxrow[$sn]; $i++) {
			$tblheight += $xls->getRowHeight($sn, $i);
		}
		$scale = get_scaling($tblwidth, $tblheight, 940);
		$tdwidth = floor($xls->getColWidth($sn, 0) * $scale);
		$trheight = floor($xls->getRowHeight($sn, 0) * $scale);
		$tblwidth = $tdwidth * ($xls->maxcell[$sn]+1);
		
		$marker_size = $tdwidth;
		
		// シートテーブル表示
		print "<table class=\"sheet_marker\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"";
		print ${tblwidth} . "\" bgcolor=\"#FFFFFF\" style=\"table-layout:fixed; border-collapse: collapse;\">\n";

		if (!isset($xls->maxrow[$sn]))
			$xls->maxrow[$sn] = 0;
		for ($r = 0; $r <= $xls->maxrow[$sn]; $r++) {

			print "  <tr height=\"" . $trheight . "\">" . "\n";
			
			for ($i = 0; $i <= $xls->maxcell[$sn]; $i++) {

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

					print " <td nowrap=\"nowrap\" $class $align>$dispval</td>\n";
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

	$status_label = file_exists(DST_DIR . $file_id . ".rb") ? "" : "disabled=\"disabled\"";

	$style = array();
	$style["normal"] = "style=\"border-style:solid;border-width:1px;border-color:#dddddd;background-color:#ffffff;padding:1px;color:gray\"";
	$style["gray"] = "style=\"border-style:solid;border-width:1px;border-color:#dddddd;background-color:#bbbbbb;padding:1px\"";
	$style["lgray"] = "style=\"border-style:solid;border-width:1px;border-color:#dddddd;background-color:#dddddd;padding:1px\"";
	$style["pink"] = "style=\"border-style:solid;border-width:1px;border-color:#dddddd;background-color:#ffdddd;padding:1px\"";

	// XXX
	print "\n";
	print "<div style=\"border-style:solid;border-color:#dddddd;border-width:1px;padding:2px;\" class=\"statusMenu\">\n";	
	print "<form method=\"post\" id=\"form-status\" >\n";
	print "<input type=\"hidden\" name=\"fileid\" value=\"" . $file_id . "\" />\n";
	// for sht_field
	print "<input type=\"hidden\" name=\"file_id\" value=\"" . $file_id . "\" />\n";
	print "<input type=\"hidden\" name=\"gid\" value=\"" . $group_id . "\" />\n";
	print "<input type=\"hidden\" name=\"sid\" value=\"" . $sheet_id . "\" />\n";

	print "<div ${style["gray"]}><button type=\"button\" id=\"prev\" onclick=\"this.disabled=true; go_prev();\" >フィールド指定</button></div>\n";
	print "<div ${style["pink"]}><span>マーカー指定</span></div>\n";
	print "<div ${style["lgray"]}><button type=\"button\" id=\"next\" onclick=\"this.disabled=true; go_next();\" {$status_label}>シート確認</button></div>\n";
	print "<div ${style["gray"]}><span>シート登録</span></div>\n";
	
	print "</form>\n";
	print "</div>\n";
}

?>
