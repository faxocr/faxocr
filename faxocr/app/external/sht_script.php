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

// XXX: debugging purpose
file_put_contents("/tmp/faxocr.log",
  "----------------------------------------\n" .
  date("Y/m/d H:i:s") . "\n(" .
  count($_REQUEST) . ")\n" .
  $msg . "\n\n",
  FILE_APPEND | LOCK_EX
);

put_config($file_id, $_REQUEST);
put_rails($file_id, $_REQUEST);

die;

//
// config出力
//
function put_config($file_id, $_REQUEST)
{
	global $target;

	$conf = new FileConf($file_id);

	//
	// XLSフィールド情報REQUEST取得
	//
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

			//
			// XLSフィールド情報保存
			//
			$conf->array_set("field", $xls_fields);
		}
	}
	$conf->array_commit();

	if (strlen($target) != 0) {
		$conf->set("target", $target);
		$conf->commit();
	}
}

//
// Railsスクリプトの作成
//
function put_rails($file_id, $_REQUEST)
{

	//
	// XLSフィールド情報取得
	//
	$tgt_items = "";
	foreach ($_REQUEST as $item => $val) {
		preg_match("/field-(\d+)-(\d+)-(\d+)-(\d+)/", $item, $loc);
		if ($loc && $loc[0]) {
			$xls_fields = array();
			$xls_fields["sheet_num"] = $loc[1];
			$xls_fields["row"]       = $loc[2];
			$xls_fields["col"]       = $loc[3];
			$xls_fields["width"]     = $loc[4];
			$xls_fields["item_name"] = $val;

			$tgt_items .= implode(",", $xls_fields) . "\n";
		}
	}

	$tgt_script = <<< "STR"

#!/usr/bin/ruby
# -*- coding: utf-8 -*-

require "rubygems"
require "active_record"
require "yaml"

rails_prefix = ARGV[0] || "./"
group = ARGV[1] || exit(0)
survey = ARGV[2] || exit(0) # XXX

config_db = rails_prefix + "/config/database.yml"
db_env = "{$rails_env}"

ActiveRecord::Base.configurations = YAML.load_file(config_db)
ActiveRecord::Base.establish_connection(db_env)
Dir.glob(rails_prefix + '/app/models/*.rb').each do |model|
  load model
end

#
# create a new survey
#
do
  @group = Group.find(group)
  @survey = @group.surveys.build(survey)
  @survey.report_header = ""
  @survey.report_footer = ""

  @candidates = @group.candidates
  @candidates.each do |candidate|
    survey_candidate = SurveyCandidate.new
    survey_candidate.candidate_id = candidate.id
    survey_candidate.role = 'sr'
    @survey.survey_candidates << survey_candidate
  end
  if @survey.save
    print "success"
  else
    print "fail"
  end
end

#
# create survey properties
#

props = []
{$tgt_items}

props.each do |prop|

  # object building
  @survey_property = @survey.survey_properties.build
  @survey_property.survey_id = survey		# integer
  @survey_property.ocr_name = prop[0]		# string
  @survey_property.ocr_name_full = prop[1]	# string
  @survey_property.view_order = prop[2]		# integer
  @survey_property.data_type = prop[3]		# string

  # save
  if @survey_property.save
    print  prop[0] + " success\n"
  else
    print  prop[0] + " fail\n"
  end
end

#
# create a new sheet
#

#  create_table "sheets", :force => true do |t|
#    t.string   "sheet_code",   :null => false
#    t.string   "sheet_name",   :null => false
#    t.integer  "survey_id",    :null => false
#    t.integer  "block_width",  :null => false
#    t.integer  "block_height", :null => false
#    t.integer  "status",       :null => false
#    t.datetime "created_at"
#    t.datetime "updated_at"
#  end

#
# create sheet/property mapping
#

#  create_table "sheet_properties", :force => true do |t|
#    t.integer  "position_x"
#    t.integer  "position_y"
#    t.integer  "colspan"
#    t.integer  "sheet_id"
#    t.integer  "survey_property_id"
#    t.datetime "created_at"
#    t.datetime "updated_at"
#  end

exit(0)

STR;

	// ファイル生成
	file_put_contents(DST_DIR . $file_id . ".rb", $tgt_script);
}

?>
