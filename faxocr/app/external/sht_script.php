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
require_once 'contrib/reviser.php';

// XXX
// file_put_contents("/tmp/faxocr.log", "test");

if (isset($_REQUEST["file_id"])) {
	$file_id = $_REQUEST["file_id"];
}

$list_colspan = array();

//
// ファイルハンドリング
//
if (isset($file_id) && $file_id) {
	$tgt_file = DST_DIR . $file_id . ".xls";
} else {
	print "不正なアクセスです\n";
	// put_err_page("不正なアクセスです");
	die;
}

if (isset($_REQUEST["target"])) {
	$target = $_REQUEST["target"];
} else {
	$target = "registered";
}

//
// Read Excelファイル処理
//
if ($tgt_file) {
	global $xls;
	$xls = NEW Excel_Reviser;
	$xls->setErrorHandling(1);
	$xls->setInternalCharset($charset);
	$result = $xls->parseFile($tgt_file, 1);

	if ($xls->isError($result)) {
		$errmsg = $result->getMessage();
		$xls = null;
	}

	foreach ($xls->cellblock as $keysheet => $sheet) {
		$maxcol = 0;
		foreach ($sheet as $keyrow => $rows) {
			foreach($rows as $keycol => $cell) {
				if ($maxcol < $keycol) $maxcol = $keycol;
			}
		}
		$xls->maxcell[$keysheet] = $maxcol;
		$xls->maxrow[$keysheet] = $keyrow;
	}
	$xls->sheetnum = count($xls->boundsheets);
}

$msg = "tgt: " . $tgt_file . "\n";

//
// reviserのClassオブジェクトを新規作成
//
$reviser = NEW Excel_Reviser;
$reviser->setInternalCharset($charset);
$reviser->setErrorHandling(1); // エラーハンドリング依頼
$reviser->addSheet(0, $xls->sheetnum);

for ($sn = 0; $sn < $xls->sheetnum; $sn++) {

	if (!isset($xls->maxcell[$sn]))
		$xls->maxcell[$sn] = 0;

	if (!isset($xls->maxrow[$sn]))
		$xls->maxrow[$sn] = 0;

	// Sheet 設定
	$reviser->setSheetname($sn + 2, strconv($xls->getSheetName($sn)));

	for ($r = 0; $r <= $xls->maxrow[$sn]; $r++) {
		for ($i = 0; $i <= $xls->maxcell[$sn]; $i++) {
			$val = $xls->getCellVal($sn, $r, $i);
			$val = strconv($val['val']);
			$xf = $xls->getCellAttrib($sn, $r, $i);
			$xf = $xf['xf'];
			$bgcolor = false;
			if (isset($xf['fillpattern']) && $xf['fillpattern'] == 1) {
				if ($xf['PtnFRcolor'] == COLOR_FILL)
					$bgcolor = 2; // header
				else
					$bgcolor = 1; // other
			}
			if (isset($xls->celmergeinfo[$sn][$r][$i]['cond'])) {
				if ($xls->celmergeinfo[$sn][$r][$i]['cond'] == 1) {
					$rowspan = $xls->celmergeinfo[$sn][$r][$i]['rspan'];
					$colspan = $xls->celmergeinfo[$sn][$r][$i]['cspan'];

					if ($colspan > 1) {
						$list_colspan["${sn}-${r}-${i}"] = $colspan;
					}

					$reviser->setCellMerge($sn + 2, $r, $r + $rowspan - 1, $i,
							       $i + $colspan - 1);
				} else {
					continue;
				}
			}

			if (isset($_REQUEST["cell-${sn}-${r}-${i}-mark"])) {
				$val = $_REQUEST["cell-${sn}-${r}-${i}-mark"];
				$bgcolor = 1;
$msg .= ")" . $val . "\n";
			} else if (isset($_REQUEST["cell-${sn}-${r}-${i}-clear"])) {
				$val = $_REQUEST["cell-${sn}-${r}-${i}-clear"];
				$bgcolor = 0;
			}

			if ($bgcolor == 1) {
				if (is_numeric($val)) {
					$reviser->addNumber($sn + 2, $r, $i, $val, 0, 0, 1);
				} else {
					$reviser->addString($sn + 2, $r, $i, $val, 0, 0, 1);
				}
			} else if ($bgcolor == 2) {
				if (is_numeric($val)) {
					$reviser->addNumber($sn + 2, $r, $i, $val, 1, 0, 1);
				} else {
					$reviser->addString($sn + 2, $r, $i, $val, 1, 0, 1);
				}
			} else {
				// blank
				if (is_numeric($val)) {
					$reviser->addNumber($sn + 2, $r, $i, $val, 0, 0, 0);
				} else {
					$reviser->addString($sn + 2, $r, $i, $val, 0, 0, 0);
				}
			}
		}
	}

	for ($i = 0; $i <= $xls->maxcell[$sn]; $i++) {
		// Col Width 設定
		$col_width = $xls->getColWidth($sn, $i);
		$col_width = ($col_width >= 160) ? $col_width - 160: $col_width;
		$reviser->chgColWidth($sn + 2, $i, $col_width);
	}

	for ($r = 0; $r <= $xls->maxrow[$sn]; $r++) {
		// Row height 設定
		$row_height = $xls->getRowHeight($sn, $r);
		$reviser->chgRowHeight($sn + 2, $r, $row_height);
	}

	// $msg .= "col_width: " . $col_width . "\n";
	// $msg .= "row_height: " . $row_height . "\n";
}

//
// Color sheet削除
//
$reviser->rmSheet(0);
$reviser->rmSheet(1);

//
// コミット
//
$lockfp = fopen(LOCK_FILE . $file_id, 'w');
flock($lockfp, LOCK_EX);
$template_xls = TMP_XLS;
$tmp_file = uniqid();

// $msg .= "tmp file: " . $tmp_file . "\n";
// $msg .= "tmp xls: " . $template_xls . "\n";

if (filesize($tgt_file)) {
	$result = $reviser->reviseFile($template_xls, $tmp_file, TMP_DIR);
}

if (!file_exists(DST_DIR . $file_id . ORIG_EXT)) {
	copy($tgt_file, DST_DIR . $file_id . ORIG_EXT);
}

if (file_exists(TMP_DIR . $tmp_file)) {
	rename(TMP_DIR . $tmp_file, $tgt_file);
}
flock($lockfp, LOCK_UN);
fclose($lockfp);
unlink(LOCK_FILE . $file_id);

put_config($file_id, $_REQUEST);

// XXX: debugging purpose
file_put_contents("/tmp/faxocr.log",
  "----------------------------------------\n" .
  date("Y/m/d H:i:s") . "\n(" .
  count($_REQUEST) . ")\n" .
  $msg . "\n\n",
  FILE_APPEND | LOCK_EX
);

die;

function is_next($prev, $next)
{
	global $msg;

// $msg .= "0";
	if (!isset($prev) || !isset($next)) {
		return false;
	}

// $msg .= "1(${prev[1]}, ${next[1]})";
	# sheet_num
	if ($prev[1] != $next[1]) {
		return false;
	}

// $msg .= "2(${prev[2]}, ${next[2]})";
	# row
	if ($prev[2] != $next[2]) {
		return false;
	}

// $msg .= "3(${prev[3]}, ${next[3]})\n";
	# col
	if ($next[3] - $prev[3] == 1) {
		return true;
	}

// $msg .= "4\n";
	return false;
}

//
// config出力
//
function put_config($file_id, $_REQUEST)
{
	global $target;
	global $sheet_name;
	global $list_colspan;

	$conf = new FileConf($file_id);

	$list_requests = array();
	foreach ($_REQUEST as $item => $val) {
		preg_match("/field-(\d+)-(\d+)-(\d+)-(\d+)/", $item, $loc);
		if ($loc && $loc[0]) {
			$location = "${loc[1]}-${loc[2]}-${loc[3]}";
			$list_requests[$location] = $val;
		}
	}

	foreach ($list_requests as $item => $val) {
		preg_match("/(\d+)-(\d+)-(\d+)/", $item, $loc);
		if ($loc && $loc[0]) {
			$col = $loc[3];
		}

		$cnt = 0;
		$label = $list_requests["${loc[1]}-${loc[2]}-${col}"];
		while (isset($list_requests["${loc[1]}-${loc[2]}-${col}"]) &&
		       $list_requests["${loc[1]}-${loc[2]}-${col}"] == $label) {
			$cnt++;

			// XXX
			if ($cnt > 1)
				$list_colspan["${loc[1]}-${loc[2]}-${col}"] = 0;
			$col++; // should be here
		}
		if ($cnt > 1) {
			$list_colspan[$item] = $cnt;
		}
	}

	// XLSフィールド情報REQUEST取得
	$conf->array_destroy("field");
	foreach ($_REQUEST as $item => $val) {
		preg_match("/field-(\d+)-(\d+)-(\d+)-(\d+)/", $item, $loc);
		if ($loc && $loc[0]) {
			$xls_fields = array();
			$xls_fields["sheet_num"] = $loc[1];
			$xls_fields["row"]       = $loc[2];
			$xls_fields["col"]       = $loc[3];
			$xls_fields["width"]     = $loc[4];
			$xls_fields["item_name"] = $val;

			# XXX: number, rating, image
			$xls_fields["type"] = "number";

			$location = "${loc[1]}-${loc[2]}-${loc[3]}";

			if (isset($list_colspan[$location])) {

				if ($list_colspan[$location] == 0)
					continue;

				$xls_fields["colspan"] = $list_colspan[$location];
			}

			// XLSフィールド情報保存
			$conf->array_set("field", $xls_fields);
		}
	}
	$conf->array_commit();

	if (isset($target) && strlen($target) > 0) {
		$conf->set("target", $target);
	}

	if (isset($sheet_name) && strlen($sheet_name) > 0) {
		$conf->set("name", $sheet_name);
	}

	$conf->commit();
}

?>
