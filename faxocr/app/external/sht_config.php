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

define('border_sw', true);

$msg = "start\n";

//
// ファイルハンドリング
//
if (isset($file_id) && $file_id) {
	// $tgt_file = DST_DIR . $file_id . ".xls";
	$tgt_file = DST_DIR . $file_id . ORIG_EXT;
} else {
	print "不正なアクセスです\n";
	// put_err_page("不正なアクセスです");
	die;
}

put_config($file_id, $_REQUEST);
get_config($file_id);


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
// HTMLファイル作成処理
//
if ($xls) {
	$html = put_header();
	$html .= put_css($xls);
	$html .= put_excel($xls);
	$html .= put_footer();
	file_put_contents(DST_DIR . $file_id . ".html", $html);

	$msg .= "put_excel\n";
}

// XXX: debugging purpose
file_put_contents("/tmp/faxocr.log",
  "----------------------------------------\n" .
  date("Y/m/d H:i:s") . "\n(" .
  count($_REQUEST) . ")\n" .
  $msg . "\n\n",
  FILE_APPEND | LOCK_EX
);

die;

function get_config($file_id)
{
	global $field_index;
	global $conf;

	//
	// 設定情報読込
	//
	if (!$conf)
		$conf = new FileConf($file_id);

	$xls_fields_list = $conf->array_getall("field");

	$num = 0;
	foreach ($xls_fields_list as $xls_fields) {
		$location = $xls_fields["sheet_num"] . "-" .
		   $xls_fields["row"] . "-" . $xls_fields["col"];
		$field_index[$location] = $location;
	}

	return $field_index;
}

//
// config出力
//
function put_config($file_id, $_REQUEST)
{
	global $target;
	global $group_id;
	global $sheet_id;
	global $conf;

	$conf = new FileConf($file_id);

	if (isset($group_id))
		$conf->set("gid", $group_id);

	if (isset($sheet_id))
		$conf->set("sid", $sheet_id);

	foreach (array("block_width",
			"block_height",
			"block_size",
			"block_offsetx",
			"block_offsety") as $item) {
		if (isset($_REQUEST[$item])) {
			$val = $_REQUEST[$item];
			if (strlen($val) != 0) {
				$conf->set($item, $val);
			}
		}
	}

	$conf->commit();
}

function put_header()
{
	$html = <<< STR
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta http-equiv="Content-Style-Type" content="text/css" />
  <meta http-equiv="Content-Script-Type" content="text/javascript" />
  <title>Shinsai FaxOCR</title>
</head>
<body>

STR;
	return $html;
}

function put_footer()
{
	global $conf;

	$sid = $conf->get("sid");
	$tid = "00000";

	$width = $conf->get("block_width");
	$height = $conf->get("block_height");
	$size = $conf->get("block_size");
	$offsetx = $conf->get("block_offsetx");
	$offsety = $conf->get("block_offsety");

	$offsetx = $offsetx > 0 ? $offsetx : 0;
	$offsety = $offsety > 0 ? $offsety : 0;

	$html = <<< STR

<div id="ex3" class="jqDnR" style="opacity:0.8; top:{$offsety}px; left:{$offsetx}px; z-index: 3; position: absolute; width: {$width}px; height:{$height}px; font-size: 12px; ">

<div class="jqDrag" style="height:100%; width: 100%;">
<img src="http://localhost:3000/image/mark.gif" class="mark-img" style="position: absolute; top: 0;left: 0; width: {$size}px;"><div style="position: absolute; left:40px; "><font style="line-height: 28pt; font-size: 28pt; font-family: 'Arial'; ">00001</font></div>
<img src="http://localhost:3000/image/mark.gif" class="mark-img" style="position: absolute; top: 0;right: 0; width: {$size}px;">
<img src="http://localhost:3000/image/mark.gif" class="mark-img" style="position: absolute; bottom: 0;left: 0; width: {$size}px;"><div style="position: absolute; left:40px; bottom: 0"><font style="line-height: 28pt; font-size: 28pt; font-family: 'Arial'; ">00001</font></div>
</div>
<div class="jqResize"></div>
</div>

</body>
</html>

STR;

	return $html;
}

function put_excel($xls)
{
	global $field_index;
	global $conf;

	$offsetx = $conf->get("block_offsetx");
	$offsety = $conf->get("block_offsety");
	$tablex = $offsetx > 0 ? 0 : 0 - $offsetx;
	$tabley = $offsety > 0 ? 0 : 0 - $offsety;
	$html = "<div style=\"top:{$tabley}px; left:{$tablex}px; position: absolute\">\n"; 

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
		// $w = $w / 2;

		// シートテーブル表示
		$html .= "<table class=\"sheet\" border=\"0\" cellpadding=\"0\"";
		$html .= "cellspacing=\"0\" width=\"" . ${w} . "\" ";
		$html .= "style=\"border-collapse: collapse;\"";
		$html .=" bgcolor=\"#FFFFFF\" >\n";

		if (!isset($xls->maxrow[$sn]))
			$xls->maxrow[$sn] = 0;
		for ($r = 0; $r <= $xls->maxrow[$sn]; $r++) {
			// XXX
			$trheight = $xls->getRowHeight($sn, $r) * 0.8;
			$html .= "  <tr height=\"" . $trheight . "\">" . "\n";
			for ($i = 0; $i <= $xls->maxcell[$sn]; $i++) {

				$tdwidth = $xls->getColWidth($sn, $i);
// XXX
//				$tdwidth = $tdwidth / 2;
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
				$loc = $sn . "-" . $r . "-" . $i;
				if (isset($field_index[$loc])) {
					$dispval = "";
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
	$html .= "</div>\n";

	return $html;
}

?>
