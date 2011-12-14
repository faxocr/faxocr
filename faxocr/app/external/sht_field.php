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

require_once "config.php";
require_once "init.php";
require_once "lib/common.php";
require_once "contrib/peruser.php";

// require_once "lib/file_conf.php";
// require_once "lib/form_db.php";

$field_list = array();
$field_width = array();

//
// ファイルハンドリング
//
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
//$header_opt .= "<link rel=\"stylesheet\" href=\"/css/simpletabs.css\" type=\"text/css\" />\n";
$header_opt .= "<link rel=\"stylesheet\" href=\"/external/css/jqcontextmenu.css\" type=\"text/css\" />\n";
$header_opt .= "<link rel=\"stylesheet\" href=\"/external/css/flexigrid.css\" type=\"text/css\" />\n";
//$header_opt .= "<link rel=\"stylesheet\" href=\"/css/jqdialog.css\" type=\"text/css\" />\n";
$header_opt .= "<script type=\"text/javascript\" src=\"/external/js/jquery-1.4.1.min.js\"></script>\n";
$header_opt .= "<script type=\"text/javascript\" src=\"/external/js/jqcontextmenu.js\"></script>\n";
//$header_opt .= "<script type=\"text/javascript\" src=\"/js/simpletabs_1.3.js\"></script>\n";
$header_opt .= "<script type=\"text/javascript\" src=\"/external/js/flexigrid.js\"></script>\n";
// $header_opt .= "<script type=\"text/javascript\" src=\"/js/jqdialog.js\"></script>\n";
$header_opt .= "<script type=\"text/javascript\" src=\"/external/js/sheetlist.js\"></script>\n";
$body_opt .= "<ul id=\"contextmenu\" class=\"jqcontextmenu\">\n";
$body_opt .= "<li>　フィールド名 <input id=\"field\" size=10 value=\"\"></li>\n";
$body_opt .= "<li class=\"btGray\"><a onclick=\"sval = 0;reset_field();\">リセット</a></li>\n";
$body_opt .= "</ul>\n";
$body_opt .= "<ul id=\"fieldreset\" class=\"jqcontextmenu\">\n";
$body_opt .= "<li style=\"z-index:10\"><a onclick=\"del_column();\">リセット</a></li>\n";
$body_opt .= "</ul>\n";
include( TMP_HTML_DIR . "tpl.header.html" );

//
// Excelファイル読み込み処理
//
if ($tgt_file && $errmsg === "") {
	$xls = NEW Excel_Peruser;
	$xls->setErrorHandling(1);
	$xls->setInternalCharset($charset);
	$result = $xls->fileread($tgt_file);
	if ($xls->isError($result)) {
		$errmsg = $result->getMessage();
		$xls = null;
	}

	if ($xls) {
		if (count($xls->boundsheets) != 1) {
			$errmsg = "シートの数 (" . count($xls->boundsheets) .
				  ") が、多すぎます";
			$xls = null;
		}
	}
}

if ($errmsg) {
	// エラーメッセージ処理
	print "<blockquote><font color=\"red\"><strong>";
	print strconv($errmsg);
	print "</strong></font></blockquote>";
}

{
	// ステータス表示
	print "<table width=\"100%\"><tr><td></td>\n";
	print "<td align=\"right\"\"  width=\"450px\">\n";
	put_status();
	print "</td>\n";
	print "</tr></table>\n";
	print "<br />\n";
}

// Excelファイル表示処理
if ($xls) {

	put_css($xls);
	put_excel($xls);
	put_fields();

	// XXX : sht_script行きへと変更
	print "<form action=\"form-commit.php?ret\" method=\"POST\" id=\"form-commit\">";
	print "<input type=\"hidden\" name=\"file\" value=\"" . $file_id . "\" />";
	print "<input type=\"hidden\" name=\"password\" id=\"passwd\" />";
	print "<button id=\"sbmt\" onclick=\"pack_fields();\" disabled/>保存</button>";
	print "</form>";
}

//
// フッタ読み込み
//
include( TMP_HTML_DIR . "tpl.footer.html" );

die;

//
// ファイル表示エリア
//
function put_excel($xls)
{
	global $field_list;
	global $field_width;

	// タブコントロール表示
	// print "<div class=\"simpleTabs\">";
	// print "<ul class=\"simpleTabsNavigation\">";
	// for($sn = 0; $sn < $xls->sheetnum; $sn++) {
	// 	print "<li><a href=\"#\">" .
	//	  strconv($xls->sheetname[$sn]) . "</a></li>";
	// }
	// print "</ul>";

	print "<center>";

	// シート表示
	// for ($sn = 0; $sn < $xls->sheetnum; $sn++) {
	$sn = 0; 
	{
		print "<div class=\"simpleTabsContent\">";

		$w = 32;
		if (!isset($xls->maxcell[$sn]))
			$xls->maxcell[$sn] = 0;
		for ($i = 0; $i <= $xls->maxcell[$sn]; $i++) {
			$w += $xls->getColWidth($sn, $i);
		}
// XXX
$w = $w / 2;

		// シートテーブル表示
		print <<< STR
<table class="sheet" border="0" cellpadding="0" cellspacing="0" width="${w}" bgcolor="#FFFFFF" style="border-collapse: collapse;">
STR;
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
				$dispval = htmlspecialchars($dispval);

				if (isset($xls->hlink[$sn][$r][$i])) {
					$dispval = "<a href=\"" . $xls->hlink[$sn][$r][$i] . "\">" . $dispval . "</a>";
				}

				$xf = $xls->getAttribute($sn, $r, $i);
				if (isset($xf["wrap"]) && $xf["wrap"])
					$dispval = ereg_replace("\n", "<br />", $dispval);
				$xfno = ($xf["xf"] > 0) ? $xf["xf"] : 0;

				$align = "x";
				if (isset($xf["halign"]) && $xf["halign"] != 0)
					$align= "";
				if ($align == "x") {
					if ($xf["type"] == Type_RK) $align = " align=\"right\"";
					else if ($xf["type"] == Type_RK2) $align = " align=\"right\"";
					else if ($xf["type"] == Type_NUMBER) $align = " align=\"right\"";
					else if ($xf["type"] == Type_FORMULA && is_numeric($dispval)) $align = " align=\"right\"";
					else if ($xf["type"] == Type_FORMULA2 && is_numeric($dispval)) $align = " align=\"right\"";
					else if ($xf["type"] == Type_FORMULA && ($dispval == "TRUE" || $dispval == "FALSE")) $align = " align=\"center\"";
					else if ($xf["type"] == Type_FORMULA2 && ($dispval == "TRUE" || $dispval == "FALSE")) $align = " align=\"center\"";
					else if ($xf["type"] == Type_BOOLERR) $align = " align=\"center\"";
					else $align= '';
					if ($xf["format"] == "@") $align = "";
				} else {
					$align = "";
				}

				if (substr($dispval, 0, 1) == "'") $dispval = substr($dispval, 1);
				if (substr($dispval, 0, 6) == "&#039;") $dispval = substr($dispval, 6);

				// セル表示
				if (isset($xf["fillpattern"]) && $xf["fillpattern"] == 1) {
					if ($xf["PtnFRcolor"] == COLOR_FILL)
						$bgcolor = 2; // header
					else
						$bgcolor = 1; // other
				} else {
					$bgcolor = 0;
				}

				if (isset($xls->celmergeinfo[$sn][$r][$i]["cond"])) {
					if ($xls->celmergeinfo[$sn][$r][$i]["cond"] == 1) {
						$colspan = $xls->celmergeinfo[$sn][$r][$i]["cspan"];
						$rowspan = $xls->celmergeinfo[$sn][$r][$i]["rspan"];

						if ($colspan > 1) {
							$rcspan = " colspan=\"" . $colspan . "\"";
						} else {
							$rcspan = " width=\"" . $tdwidth . "\"";
						}

						if ($rowspan > 1)
							$rcspan .= " rowspan=\"" . $rowspan . "\"";

						if ($bgcolor == 1 && !is_null($dispval)) {
							$id = $sn . "-" . $r . "-" . $i;
							if (trim($dispval)) {
								$field_list[$id] = $dispval;
							} else {
								$field_list[$id] = $dispval = $id;
							}
							$dispval = "<b>" . $dispval . "</b>";
							$field_width[$id] = $tdwidth;
						}

						$class = " class=\"XFs" . $sn . "r" . $r . "c" . $i . "\"";
						$id = " id=\"". $sn . "-" . $r ."-" . $i . "\"";
						print " <td $class $id $name $rcspan $align>". $dispval . "</td>\n";
					}
				} else {
					if ($bgcolor == 1 && !is_null($dispval)) {
						$id = $sn . "-" . $r . "-" . $i;
						if (trim($dispval)) {
							$field_list[$id] = $dispval;
						} else {
							$field_list[$id] = $dispval = $id;
						}
						$dispval = "<b>" . $dispval . "</b>";
						$field_width[$id] = $tdwidth;
					}
					$class = " class=\"XF" . $xfno . "\" ";
					$width = "width=\"" . $tdwidth . "\" ";
					$id = " id=\"". $sn . "-" . $r . "-" . $i . "\"";
					print " <td $class $id $width $align>". $dispval . "</td>\n";
				}
			}
			print "</tr>\n";
		}
		print "</table>\n"; // シートテーブル終了
		// print "</div>\n"; // シート終了 (simpleTabsContent)
	}
	// print "</div>\n"; // タブ全体終了 (simpleTabs)
	print "</center><br>\n";
}

//
// ファイル修正エリア表示
//
function put_fields()
{
	global $field_list;
	global $field_width;
	$i = 1;

	//
	// XLSフィールド情報取得
	//
	/*
	$xls_fields_list = $conf->array_getall("field");
	foreach ($xls_fields_list as $xls_fields) {
		$id = $xls_fields["sheet_num"] . "-" . $xls_fields["row"] . "-" . $xls_fields["col"];
		$new_list[$id]  = $xls_fields["item_name"];
		$new_width[$id] = $xls_fields["width"];
	}
	*/

	if (isset($new_list)) {
		$field_list = $new_list;
		$field_width = $new_width;
	}

	if (!count($field_list)) {
		$field_list[0] = "セルをクリックして下さい";
		$field_width[0] = 160;
	}

	print "<span style=\"position:relative;top:10px;left:10px;border:1px solid #E0E0E0;margin: 8px; padding:3px\">\n";
	print "<small>集計フィールド</small>\n";
	print "</span>\n";

	print "<div style=\"position:relative;top:10px;left:10px;\" id=\"jsiSetup\">\n";
	print "<table id=\"field_list\">\n";

	print "<thead>\n";
	print "<tr>\n";
	foreach ($field_list as $id => $val) {
		print "<th width=\"${field_width[$id]}\">${i}";
		print "</th>\n";

		$i++;
	}
	print "</tr>\n";
	print "</thead>\n";

	print "<tbody>\n";

	print "<tr>\n";
	foreach ($field_list as $id => $val)
		print "<td name=\"${id}\">${val}</td>\n";
	print "</tr>\n";
	print "</tbody>\n";

	print "</table>\n";
	print "</div><br />\n";
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
	print "<form action=\"/external\sht_marker/\" method=\"POST\" id=\"form-commit\">\n";
	print "<input type=\"hidden\" name=\"file\" value=\"" . $file_id . "\" />\n";
	print "<input type=\"hidden\" name=\"gid\" value=\"" . $group_id . "\" />\n";
	print "<input type=\"hidden\" name=\"sid\" value=\"" . $sheet_id . "\" />\n";

	print "<div style=\"border-style:solid;border-color:#dddddd;border-width:1px;padding:2px;\" class=\"statusMenu\">\n";
	print "<div ${style["pink"]}><span>フィールド指定</span></div>\n";

	print "<div ${style["lgray"]}><button id=\"next\" onclick=\"this.form.submit();\">マーカー指定</button></div>\n";

	print "<div ${style["gray"]}><span>シート確認</span></div>\n";
	print "<div ${style["gray"]}><span>シート登録</span></div>\n";
	print "</div>\n";

	print "</form>\n";
}

?>
