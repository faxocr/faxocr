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

$field_list = array();
$field_width = array();

if (!isset($file_id) || !$file_id) {
	put_err_page("不正なアクセスです");
	die;
}

//
// XXX: peruser/reviser initialization
//
// makeptn関数(セルのパターン塗りつぶし画像生成)を使う場合は必ず先頭で処理
if (isset($_GET['ptn']))
	makeptn($_GET['ptn'], $_GET['fc']);

if (This_Class_Name != "Excel_Peruser")
	$errmsg = "Excel_Peruserが正しく読み込まれていません";

if (Peruser_Ver != 0.110)
	$errmsg = "Excel_Peruserのバージョンが異なります";

$tgt_file = DST_DIR . $file_id . DATA_EXT;

//
// ヘッダ処理
//
$header_opt .= "<link rel=\"stylesheet\" href=\"./css/simpletabs.css\" type=\"text/css\" />\n";
$header_opt .= "<link rel=\"stylesheet\" href=\"./css/jqcontextmenu.css\" type=\"text/css\" />\n";
$header_opt .= "<link rel=\"stylesheet\" href=\"./css/flexigrid.css\" type=\"text/css\" />\n";
$header_opt .= "<script type=\"text/javascript\" src=\"./js/jquery-1.4.1.min.js\"></script>\n";
$header_opt .= "<script type=\"text/javascript\" src=\"./js/jqcontextmenu.js\"></script>\n";
$header_opt .= "<script type=\"text/javascript\" src=\"./js/simpletabs_1.3.js\"></script>\n";
$header_opt .= "<script type=\"text/javascript\" src=\"./js/flexigrid.js\"></script>\n";
$header_opt .= "<script type=\"text/javascript\" src=\"./js/sheetlist.js\"></script>\n";
$body_opt .= "<ul id=\"contextmenu\" class=\"jqcontextmenu\">\n";
$body_opt .= "<li>　フィールド名 <input id=\"field\" size=10 value=\"\"></li>\n";
// 一時的にコメントアウト
//$body_opt .= "<HR>\n";$body_opt .= "<li><a href=\"\">文字</a>\n";
//$body_opt .= "<ul>\n";
//$body_opt .= "<li><a onclick=\"\$sval = 1;\">全角</a></li>\n";
//$body_opt .= "<li><a onclick=\"\$sval = 2;\">半角</a></li>\n";
//$body_opt .= "<li><a onclick=\"\$sval = 3;\">アルファベット</a></li>\n";
//$body_opt .= "</ul>\n";
//$body_opt .= "</li>\n";
//$body_opt .= "<li><a onclick=\"\$sval = 4;\">数字</a></li>\n";
//$body_opt .= "<li>　正規表現 <input name=\"rule\" size=10 value=\"\"></li>\n";
//$body_opt .= "<HR>\n";
$body_opt .= "<li><a onclick=\"\$sval = 0;reset_field();\">リセット</a></li>\n";
$body_opt .= "</ul>\n";
$body_opt .= "<ul id=\"fieldreset\" class=\"jqcontextmenu\">\n";
$body_opt .= "<li style=\"z-index:10\"><a onclick=\"del_column();\">リセット</a></li>\n";
$body_opt .= "</ul>\n";
include( TMP_HTML_DIR . "tpl.header.html" );

//
// Excelファイル読み込み処理
//
if (isset($tgt_file) && $errmsg === "") {
	$xls = NEW Excel_Peruser;
	$xls->setErrorHandling(1);
	$xls->setInternalCharset($charset);
	$result = $xls->fileread($tgt_file);
	if ($xls->isError($result)) {
		$errmsg = $result->getMessage();
		if (strpos($errmsg, $tgt_file) !== false) {
			$errmsg = str_replace('Template file', 'Uploaded file',
					      $errmsg);
		}
		$xls = null;
	}
}

//
// エラーメッセージ処理
//
if (isset($_REQUEST["msg"])) {
	$errmsg = $_REQUEST["msg"];
}

if ($errmsg) {
	print "<H1>かんたんクラウド</H1>";

	// エラーメッセージ処理
	if ($errmsg) {
		print "<blockquote><font color=\"red\"><STRONG>";
		print strconv($errmsg);
		print "</STRONG></font></blockquote>";
	}
}

// Excelファイル表示処理
if ($xls) {

	put_css($xls);

	put_excel($xls);
	put_fields();

	print "<form action=\"form-commit.php?ret\" method=\"POST\" id=\"form-commit\">";
	print "<input type=\"hidden\" name=\"file\" value=\"" . $file_id . "\" />";
	print "<button id=\"sbmt\" onclick=\"pack_fields();\" disabled/>保存</button>";
	print "</form>";

	print "<BR><div align=\"right\"><a href=\"./\">[トップ]</a></div>";
}

//
// フッタ読み込み
//
include( TMP_HTML_DIR . "tpl.footer.html" );

die;

function put_excel($xls) {
	global $field_list;
	global $field_width;

	// プロパティ表示
	if (isset($_POST['selprop']) && $_POST['selprop'] == "on") {
	  //	$prp = $xls->getPropEN();
		$prp = $xls->getPropJP();
		if (count($prp) > 1) {
			print "<table border=\"0\" cellpadding=\"0\" cellspacing=\"1\" bgcolor=\"#CCCCCC\"><tr bgcolor=\"#F8FFFF\"><td bgcolor=\"#E0E0E0\">プロパティ</td><td bgcolor=\"#E0E0E0\">値</td></tr>\n";
			foreach ($prp as $propid => $val) {
				$val = mb_eregi_replace ('&lt;br */?&gt;','<br />',strconv($val));
				print "	<tr bgcolor='#F8FFFF'><td bgcolor='#E0E0E0'><font size=2>".strconv($propid)."</font></td>";
				print "<td bgcolor='#F8FFFF'><font size=2>${val}</font></td></tr>\n";
			}
			print "</table><p></p>\n";
		} else{
			print "\n<small>有効なプロパティを取得できませんでした。</small><br><br>\n";
		}
	}

	// タブコントロール表示
	print "<div class=\"simpleTabs\">";
	print "<ul class=\"simpleTabsNavigation\">";
	for($sn = 0; $sn < $xls->sheetnum; $sn++) {
		print "<li><a href=\"#\">" .
		  strconv($xls->sheetname[$sn]) . "</a></li>";
	}
	print "</ul>";

	// シート表示
	for ($sn = 0; $sn < $xls->sheetnum; $sn++) {
		print "<div class=\"simpleTabsContent\">";

		$w = 32;
		if (!isset($xls->maxcell[$sn]))
			$xls->maxcell[$sn] = 0;
		for ($i = 0; $i <= $xls->maxcell[$sn]; $i++) {
			$w += $xls->getColWidth($sn, $i);
		}

		// シート毎ヘッダ表示
		$hd = $xls->getHEADER($sn);
		$ft = $xls->getFOOTER($sn);
		if ($hd !== null) {
			$hd['left'] = (isset($hd['left'])) ? strconv($hd['left']) : "";
			$hd['center'] = (isset($hd['center'])) ? strconv($hd['center']) : "";
			$hd['right'] = (isset($hd['right'])) ? strconv($hd['right']) : "";

			print <<< STR1

<table width="${w}" border="0" cellpadding="0" cellspacing="1" bordercolor="#CCCCCC" bgcolor="#CCCCCC">
<tr>
    <td width="30" nowrap><font size="1">ヘッダ</font></td>
    <td bgcolor="#FFFFFF"><div align="left"> ${hd['left']} </div></td>
    <td bgcolor="#FFFFFF"><div align="center"> ${hd['center']} </div></td>
    <td bgcolor="#FFFFFF"><div align="right"> ${hd['right']} </div></td>
</tr></table>
STR1;
		}

		// シートテーブル表示
		print <<< STR2
<table class="sheet" border="0" cellpadding="0" cellspacing="0" width="${w}" bgcolor="#FFFFFF" style="border-collapse: collapse;">
STR2;
		if (!isset($xls->maxrow[$sn]))
			$xls->maxrow[$sn] = 0;
		for ($r = 0; $r <= $xls->maxrow[$sn]; $r++) {
			print "  <tr height=\"" .
			  $xls->getRowHeight($sn, $r) . "\">" . "\n";
			for ($i = 0; $i <= $xls->maxcell[$sn]; $i++) {

				$tdwidth = $xls->getColWidth($sn, $i);
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
					$align= '';
				if ($align == 'x') {
					if ($xf['type'] == Type_RK) $align = " Align=\"right\"";
					else if ($xf['type'] == Type_RK2) $align = " Align=\"right\"";
					else if ($xf['type'] == Type_NUMBER) $align = " Align=\"right\"";
					else if ($xf['type'] == Type_FORMULA && is_numeric($dispval)) $align = " Align=\"right\"";
					else if ($xf['type'] == Type_FORMULA2 && is_numeric($dispval)) $align = " Align=\"right\"";
					else if ($xf['type'] == Type_FORMULA && ($dispval == "TRUE" || $dispval == "FALSE")) $align = " Align=\"center\"";
					else if ($xf['type'] == Type_FORMULA2 && ($dispval == "TRUE" || $dispval == "FALSE")) $align = " Align=\"center\"";
					else if ($xf['type'] == Type_BOOLERR) $align = " Align=\"center\"";
					else $align= '';
					if ($xf['format'] == '@') $align = "";
				} else {
					$align = "";
				}

				if (substr($dispval,0,1) == "'") $dispval = substr($dispval, 1);
				if (substr($dispval,0,6) == "&#039;") $dispval = substr($dispval, 6);

				// セル表示
				if (isset($xf['fillpattern']) && $xf['fillpattern'] == 1) {
					if ($xf['PtnFRcolor'] == COLOR_FILL)
						$bgcolor = 2; // header
					else
						$bgcolor = 1; // other
				} else {
					$bgcolor = 0;
				}

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

						if ($bgcolor == 1 && $dispval) {
							$id = $sn . "-" . $r . "-" . $i;
							if (trim($dispval)) {
								$field_list[$id] = $dispval;
							} else {
								$field_list[$id] = $dispval = $id;
							}
							$dispval = "<B>" . $dispval . "<B>";
							$field_width[$id] = $tdwidth;
						}

						$class = " class=\"XFs" . $sn . "r" . $r . "c" . $i . "\"";
						$id = " id=\"". $sn . "-" . $r ."-" . $i . "\"";
						print " <td $class $id $name $rcspan $align>$dispval</td>\n";
					}
				} else {
					if ($bgcolor == 1 && $dispval) {
						$id = $sn . "-" . $r . "-" . $i;
						if (trim($dispval)) {
							$field_list[$id] = $dispval;
						} else {
							$field_list[$id] = $dispval = $id;
						}
						$dispval = "<B>" . $dispval . "<B>";
						$field_width[$id] = $tdwidth;
					}
					$class = " class=\"XF" . $xfno . "\" ";
					$width = "width=\"" . $tdwidth . "\" ";
					$id = " id=\"". $sn . "-" . $r . "-" . $i . "\"";
					print " <td $class $id $width $align>$dispval</td>\n";
				}
			}
			print "</tr>\n";
		}
		print "</table>\n"; // シートテーブル終了

		// フッタ表示
		if ($ft !== null) {
			$ft['left'] = (isset($ft['left'])) ? strconv($ft['left']) : "";
			$ft['center'] = (isset($ft['center'])) ? strconv($ft['center']) : "";
			$ft['right'] = (isset($ft['right'])) ? strconv($ft['right']) : "";

			print <<< STR3
<table width="${w}" border="0" cellpadding="0" cellspacing="1" bordercolor="#CCCCCC" bgcolor="#CCCCCC"><tr>
    <td width="30" nowrap><font size="1">フッタ</font></td>
    <td bgcolor="#FFFFFF"><div align="left">${ft['left']} </div></td>
    <td bgcolor="#FFFFFF"><div align="center">${ft['center']}</div></td>
    <td bgcolor="#FFFFFF"><div align="right">${ft['right']}</div></td>
</tr></table>
STR3;
		}
		print "</div>\n"; // シート終了 (simpleTabsContent)

	}
	print "</div>\n"; // タブ全体終了 (simpleTabs)
}

function put_fields()
{
	global $field_list;
	global $field_width;
	$i = 1;

	if (!count($field_list)) {
		$field_list[0] = "セルをクリックして下さい";
		$field_width[0] = 160;
	}

	print "<span style=\"position:relative;top:10px;left:10px;border:1px solid #E0E0E0;margin: 8px; padding:3px\">\n";
	print "<small>集計フィールド</small>\n";
	print "</span>\n";

	print "<DIV style=\"position:relative;top:10px;left:10px;\">\n";
	print "<TABLE id=\"field_list\">\n";

	print "<THEAD>\n";
	print "<TR>\n";
	foreach ($field_list as $id => $val) {
		print "<TH width=\"${field_width[$id]}\">${i}";
		print "</TH>\n";

		$i++;
	}
	print "</TR>\n";
	print "</THEAD>\n";

	print "<TBODY>\n";

	print "<TR>\n";
	foreach ($field_list as $id => $val)
		print "<TD name=\"${id}\">${val}</TD>\n";
	print "</TR>\n";
	print "</TBODY>\n";

	print "</TABLE>\n";
	print "</DIV><BR>\n";

	print "<script type=\"text/javascript\">\n";
	print "<!--\n";

	print <<< STR

	$(document).ready(function() {
		$("#field_list").flexigrid({
			showToggleBtn:false,
			resizable:false,
			height:70,
			onDragCol: function() {
				ths = $(".hDiv th");
				ths.each(function(num) {
					th = $(this);
					th.find("div:first").text(++num);
				});
			}
		});

		$('.hDivBox th').addcontextmenu('fieldreset');
		$('.hDivBox th').click(function() { target = $(this); });
		$("#field_list td").click(field_click);
	});

STR;

	print "-->\n";
	print "</script>\n";
}

?>
