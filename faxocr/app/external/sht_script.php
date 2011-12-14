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
require_once 'contrib/reviser.php';

// リファラーチェック
is_referer_err();

//
// 不正アクセス疑い
//
if (!isset($file_id) || !$file_id) {
	put_reject("不正なアクセスです");
	die;
}

$tgt_file = DST_DIR . $file_id . DATA_EXT;
$template_xls = TMP_XLS;
$tmp_file = uniqid();

//
// Read Excelファイル処理
//
if ($tgt_file && $errmsg === "") {
	global $xls;
	$xls = NEW Excel_Reviser;
	$xls->setErrorHandling(1);
	$xls->setInternalCharset($charset);
	$result = $xls->parseFile($tgt_file, 1);

	if ($xls->isError($result)) {
		$errmsg = $result->getMessage();
		if (strpos($errmsg, $_FILES['userfile']['tmp_name']) !== false) {
			$errmsg = str_replace($tgt_file, $file_name, $errmsg);
			$errmsg = str_replace('Template file', 'Uploaded file',
					      $errmsg);
		}
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

					$reviser->setCellMerge($sn + 2, $r, $r + $rowspan - 1, $i,
							       $i + $colspan - 1);
				} else {
					continue;
				}
			}

			if (isset($_POST["cell-${sn}-${r}-${i}-mark"])) {
				$val = $_POST["cell-${sn}-${r}-${i}-mark"];
				$bgcolor = 1;
			} else if (isset($_POST["cell-${sn}-${r}-${i}-clear"])) {
				$val = $_POST["cell-${sn}-${r}-${i}-clear"];
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

if (isset($_GET['ret'])) {
	$url = $_SERVER["HTTP_REFERER"];
	// XXX: is the url generation reasonable??
	// (trying to remove the strings after "?")
	$url = preg_replace("/(\?.+)/", "", $url);
	$url = $url . "?file=" . $file_id;

	header('Content-Type: text/html');
	header('Location: ' . $url);

	die;
}

//
// ヘッダ処理
//
$header_opt .= "<script type=\"text/javascript\" src=\"./js/jquery-1.4.1.min.js\"></script>\n";
include( TMP_HTML_DIR . "tpl.header.html" );

//
// タイトル
//
print "<H1>かんたんクラウド</H1>";

if ($reviser->isError($result)) {
	$errmsg = $result->getMessage();

	// エラーメッセージ処理
	print "ファイル : " . $tgt_file . "の処理中にエラーが発生しました<BR><BR>";
	print "<blockquote><font color=\"red\"><STRONG>";
	print strconv($errmsg);
	print "</STRONG></font></blockquote>";
}

print "<BR>";
print "<div align=\"right\"><a href=\"./index.php\">[トップ]</a></div>";

//
// フッタ読み込み
//
include( TMP_HTML_DIR . "tpl.footer.html" );

die;

?>
