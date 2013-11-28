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
	put_err_page("不正なアクセスです");
	die;
}

if (isset($_REQUEST["target"])) {
	$target = $_REQUEST["target"];
}

if (file_exists(DST_DIR . $file_id . ARRAY_CONF_EXT)) {
	$conf_sw = true;
} else {
	$conf_sw = false;
}

//
// ヘッダ処理
//
//$header_opt = "<base target=\"/external/sht_field/\">\n";
$header_opt .= "<link rel=\"stylesheet\" href=\"/external/css/jqcontextmenu.css\" type=\"text/css\" />\n";
$header_opt .= "<link rel=\"stylesheet\" href=\"/external/css/flexigrid.css\" type=\"text/css\" />\n";
$header_opt .= "<script type=\"text/javascript\" src=\"/external/js/jquery-1.4.1.min.js\"></script>\n";
$header_opt .= "<script type=\"text/javascript\" src=\"/external/js/jqcontextmenu.js\"></script>\n";
$header_opt .= "<script type=\"text/javascript\" src=\"/external/js/flexigrid.js\"></script>\n";
$header_opt .= "<script type=\"text/javascript\" src=\"/external/js/sheetlist.js\"></script>\n";

$body_opt .= "<ul id=\"contextmenu\" class=\"jqcontextmenu\">\n";
$body_opt .= "<li>　フィールド名 <input id=\"field\" size=\"10\" value=\"\" /></li>\n";
$body_opt .= "<li><a onclick=\"cell_type[targetid] = 1;\">数字</a></li>\n";
$body_opt .= "<li><a onclick=\"cell_type[targetid] = 2;\">○×△</a></li>\n";
$body_opt .= "<li><a onclick=\"cell_type[targetid] = 3;\">画像</a></li>\n";
$body_opt .= "<li class=\"btGray\"><a onclick=\"cell_type[targetid] = -1;reset_field();\">リセット</a></li>\n";
$body_opt .= "</ul>\n";
$body_opt .= "<ul id=\"fieldreset\" class=\"jqcontextmenu\">\n";
$body_opt .= "<li style=\"z-index:10\"><a onclick=\"del_column();\">リセット</a></li>\n";
$body_opt .= "</ul>\n";

include( TMP_HTML_DIR . "tpl.header.html" );

//
// Excelファイル読み込み処理
//
if ($tgt_file) {
	global $xls;
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

	if ($xls) {
		// 各 cell のサイズからテーブルの大きさを求める
		$sn = 0;
		$tblwidth = 0;
		$tblheight = 0;
		// 最小のセルのサイズ
		$min_cell_width = 0;
		$min_cell_height = 0;
		for ($i = 0; $i <= $xls->maxcell[$sn]; $i++) {
			$tblwidth += floor($xls->getColWidth($sn, $i));
			if ($min_cell_width == 0 || $min_cell_width > $xls->getColWidth($sn, $i)) {
				$min_cell_width = $xls->getColWidth($sn, $i);
			}
		}
		for ($i = 0; $i <= $xls->maxrow[$sn]; $i++) {
			$tblheight += floor($xls->getRowHeight($sn, $i));
			if ($min_cell_height == 0 || $min_cell_height > $xls->getRowHeight($sn, $i)) {
				$min_cell_height = $xls->getRowHeight($sn, $i);
			}
		}

		$scale = get_scaling($tblwidth, $tblheight, 940);

		$enableRectCell = false;
		if (!$enableRectCell) {
			// 長方形版では以下のコードは用いない
			// cellのサイズの縦横比が10%以上はエラー
			$cellaspect_range = 0.1;
			$cellaspect = $xls->getColWidth($sn, 0) / $xls->getRowHeight($sn, 0);
			if ($cellaspect != 1) {
				// cellが厳密に正方形ではない場合メッセージのみ表示
				$errmsg = "";
			}
			if (($cellaspect < 1 - $cellaspect_range) || ($cellaspect > 1 + $cellaspect_range)) {
				$xls = null;
				$errmsg = "セルが正方形になっていません";
			} else if ((floor($xls->getColWidth($sn, 0)) * ($xls->maxcell[$sn]+1)) != $tblwidth) {
				$xls = null;
				$errmsg = "セルはすべて同じサイズにしてください";
			} else if ((floor($xls->getRowHeight($sn, 0)) * ($xls->maxrow[$sn]+1)) != $tblheight) {
				$xls = null;
				$errmsg = "セルはすべて同じサイズにしてください";
			}
		}
		if ($xls) {
		if (($xls->maxcell[$sn]+1 <= MIN_SHEET_WIDTH || $xls->maxrow[$sn]+1 <= MIN_SHEET_HEIGHT) && ($xls->maxrow[$sn]+1 <= MIN_SHEET_WIDTH || $xls->maxcell[$sn]+1 <= MIN_SHEET_HEIGHT)) {
			// シートサイズチェック
			$xls = null;
			$errmsg = "シートのサイズが小さすぎます ".MIN_SHEET_WIDTH."x".MIN_SHEET_HEIGHT."以上にしてください";
		} else if (($xls->maxcell[$sn]+1 >= MAX_SHEET_WIDTH || $xls->maxrow[$sn]+1 >= MAX_SHEET_HEIGHT) && ($xls->maxrow[$sn]+1 >= MAX_SHEET_WIDTH || $xls->maxcell[$sn]+1 >= MAX_SHEET_HEIGHT)) {

			// シートサイズチェック
			$xls = null;
			$errmsg = "シートのサイズが大きすぎます ".MAX_SHEET_WIDTH."x".MAX_SHEET_HEIGHT."以下にしてください";
		}
		// セルサイズチェック
		if ( ($min_cell_width != 0 && ($min_cell_width * $scale) <= MIN_CELL_WIDTH) || ($min_cell_height != 0 &&($min_cell_height * $scale) <= MIN_CELL_HEIGHT) ) {
			// 厳密にはマーカー指定時のサイズによって決まる
			$errmsg = "セルのサイズが小さすぎます ".MIN_CELL_WIDTH."px x ".MIN_CELL_HEIGHT."px以上にしてください";
		}
		}
	}
}

// エラーメッセージ処理
if ($errmsg) {
	print "<blockquote><font color=\"red\"><strong>";
	print strconv($errmsg);
	print "</strong></font></blockquote>";
}

{
	// ステータス表示
	put_status();
}

// Excelファイル表示処理
if ($xls) {
	put_css($xls);

	print "<div style=\"margin: 20px 0 30px; text-align:center;\">読み取りたいセルをクリックし、フィールド指定して下さい。</div>\n";

	put_excel($xls);
	if ($conf_sw) {
		$dirty_label = " disabled";
	} else {
		$dirty_label = count($field_list) > 0 ? "" : "disabled=\"disabled\"";
	}

	// 集計フィールド
	put_fields();

	print "<div class=\"clearfix\" style=\"padding: 10px 0; margin-bottom: 30px;\">\n";
	print "<form action=\"/external/sht_script/\" method=\"post\" id=\"form-save\">\n";
	print "<input type=\"hidden\" name=\"fileid\" value=\"" . $file_id . "\" />\n";
	print "<input type=\"hidden\" name=\"gid\" value=\"" . $group_id . "\" />\n";
	print "<input type=\"hidden\" name=\"sid\" value=\"" . $sheet_id . "\" />\n";
	print "<input type=\"hidden\" name=\"sname\" value=\"" . $sheet_name . "\" />\n";
	print "<input type=\"hidden\" name=\"target\" value=\"" . $target . "\" />\n";
	//	print "<button type=\"button\" id=\"sbmt\" onclick=\"this.disabled=true; pack_fields();\" {$dirty_label}>保存</button>\n";
	print "</form>\n";
	print "</div>\n";
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
	global $tblwidth;
	global $tblheight;

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
		$scale = get_scaling($tblwidth, $tblheight, 940);
		$tblwidth_scaled = floor($tblwidth * $scale);
		$tblheight_scaled = floor($tblheight * $scale);

		// シートテーブル表示
		print <<< STR
		\n<table class="sheet_field" border="0" cellpadding="0" cellspacing="0" width="${tblwidth_scaled}" bgcolor="#FFFFFF" style="table-layout:fixed; border-collapse: collapse;">\n
STR;

		print "<tr>\n";
		for ($i = 0; $i <= $xls->maxcell[$sn]; $i++) {
			$tdwidth  = floor($xls->getColWidth($sn, $i) * $scale);
			print "<th height=\"0\" width=\"$tdwidth\"></th>";
		}
		print "\n</tr>\n";
		if (!isset($xls->maxrow[$sn]))
			$xls->maxrow[$sn] = 0;
		for ($r = 0; $r <= $xls->maxrow[$sn]; $r++) {
			$trheight = floor($xls->getRowHeight($sn, $r) * $scale);

			print "  <tr height=\"" . $trheight . "\">" . "\n";

			for ($i = 0; $i <= $xls->maxcell[$sn]; $i++) {
				$tdwidth  = floor($xls->getColWidth($sn, $i) * $scale);

				$dispval = $xls->dispcell($sn, $r, $i);
				$dispval = strconv($dispval);

				if (isset($xls->hlink[$sn][$r][$i])) {
					$dispval = "<a href=\"" . $xls->hlink[$sn][$r][$i] . "\">" . $dispval . "</a>";
				}

				$xf = $xls->getAttribute($sn, $r, $i);
				if (isset($xf["wrap"]) && $xf["wrap"])
					$dispval = preg_replace('/\n/', '<br />', $dispval);
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

				$celattr =  $xls->getAttribute($sn, $r, $i);
				$fontsize =  $celattr["font"]["height"] * $scale / 16;
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
						print " <td $class $id $name $rcspan $align style=\"font-size: " . $fontsize . "px;\">". $dispval . "</td>\n";
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
					$id = " id=\"". $sn . "-" . $r . "-" . $i . "\"";
					print " <td nowrap=\"nowrap\" $class $id $align style=\"font-size: " . $fontsize . "px;\">". $dispval . "</td>\n";
				}
			}
			print "</tr>\n";
		}
		print "</table>\n"; // シートテーブル終了
		// print "</div>\n"; // シート終了 (simpleTabsContent)
	}
	// print "</div>\n"; // タブ全体終了 (simpleTabs)
	print "</center><br />\n";
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
	global $conf_sw;

	$status_label = $conf_sw ? "" : " disabled";

	// XXX
	print "<div class=\"statusMenu clearfix\">\n";
	print "<form action=\"/external/sht_marker/\" method=\"post\" id=\"form-status\">\n";
	print "<input type=\"hidden\" name=\"fileid\" value=\"" . $file_id . "\" />\n";
	print "<input type=\"hidden\" name=\"gid\" value=\"" . $group_id . "\" />\n";
	print "<input type=\"hidden\" name=\"sid\" value=\"" . $sheet_id . "\" />\n";

	print "<div class=\"upload\"><button type=\"button\" id=\"\" onclick=\"this.disabled=false; go_sheet_upload(); return false;\" >再読み込み</button></div>\n";
	print "<div class=\"field current\">&gt;<button type=\"button\" disabled=\"disabled\">フィールド指定</button></div>\n";
	print "<div class=\"marker\">&gt;<button type=\"button\" id=\"next\" onclick=\"this.disabled=true; pack_fields();\" " . $status_label . ">マーカー指定</button></div>\n";
	print "<div class=\"verify disable\">&gt;<button type=\"button\" disabled=\"disabled\">シート確認</button></div>\n";
	print "<div class=\"commit disable\">&gt;<button type=\"button\" disabled=\"disabled\">シート登録</button></div>\n";

	print "</form>\n";
	print "</div>\n";
}

?>
