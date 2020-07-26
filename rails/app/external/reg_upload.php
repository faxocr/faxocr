<?php
/*
 * Shinsai FaxOCR
 *
 * Copyright (C) 2009-2013 National Institute of Public Health, Japan.
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
// require_once "lib/file_conf.php";
require_once "contrib/peruser.php";

//
// ファイルハンドリング
//
if (isset($file_id) && $file_id) {
	$tgt_file = DST_DIR . $file_id . LIST_EXT;
} else {
	$errmsg = "ファイルが読み込めません";
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

// ターゲット要素解析
if ($xls) {
	if (isset($xls->maxcell[0]))
		$ncolumn = $xls->maxcell[0];

	if (isset($xls->maxrow[0]))
		$nrows = $xls->maxrow[0];

	if (isset($xls->boundsheets))
		$nsheets = count($xls->boundsheets);

	$tgt_script = "";
	if ($ncolumn >= 3 && $nsheets == 1) {


		# target
		$tgt_items = "";
		for ($r = 0; $r <= $nrows; $r++) {
			$item_name = trim(strconv($xls->dispcell(0, $r, 0)));
			$item_id = trim(strconv($xls->dispcell(0, $r, 1)));

			$item_tel = strconv($xls->dispcell(0, $r, 2));
			$item_tel = preg_replace("/[^0-9]/", "", $item_tel);
			$item_tel = trim($item_tel);

			$item_fax = strconv($xls->dispcell(0, $r, 3));
			$item_fax = preg_replace("/[^0-9]/", "", $item_fax);
			$item_fax = trim($item_fax);

			if ($item_name && $item_id && $item_tel && $item_fax) {
				$tgt_items .= "props << ['${item_name}', '${item_id}'," .
					       "'${item_tel}', '${item_fax}']\n";
			}
		}

		#
		# ruby script
		#
		$tgt_script = <<< "STR"

#!/usr/bin/ruby
# -*- coding: utf-8 -*-

require "rubygems"
require "active_record"
require "yaml"
require "erb"

rails_prefix = ARGV[0] || "./"
group = ARGV[1] || exit(0)

config_db = rails_prefix + "/config/database.yml"
db_env = "{$rails_env}".intern

ActiveRecord::Base.configurations = YAML.load(ERB.new(Pathname.new(config_db).read).result)
ActiveRecord::Base.establish_connection(db_env)
Dir.glob(rails_prefix + '/app/models/*.rb').each do |model|
  load model
end

props = []

{$tgt_items}

props.each do |prop|

  # preprocess
  @group = Group.find(group)
  @candidate = @group.candidates.build    

  # object building
  @candidate.group_id = group # integer
  @candidate.candidate_name = prop[0] # string
  @candidate.candidate_code = "%05d" % prop[1] # string
  @candidate.tel_number = prop[2] # string
  @candidate.fax_number = prop[3] # string

  # save
  if @candidate.save
    print  prop[0] + " success\n"
  else
    print  prop[0] + " fail\n"
  end
end
exit(0)

STR;

	} else {
		$xls = null;

		if ($nsheets != 1)
			$errmsg .= "ファイルにシートが多すぎます (" . $nsheets . "シート)";
		else
			$errmsg .= "ファイルに列が足りません (" . ($ncolumn + 1) . "列)";
	}

	// ファイル生成
	file_put_contents(DST_DIR . $file_id . ".rb", $tgt_script);
}

//
// ヘッダ処理
//
// $header_opt .= "<link rel=\"stylesheet\" href=\"/css/simpletabs.css\" type=\"text/css\" />\n";
// $header_opt .= "<link rel=\"stylesheet\" href=\"/css/jqdialog.css\" type=\"text/css\" />\n";
$header_opt .= "<script type=\"text/javascript\" src=\"/external/js/jquery-1.4.1.min.js\"></script>\n";
// $header_opt .= "<script type=\"text/javascript\" src=\"/js/simpletabs_1.3.js\"></script>\n";
// $header_opt .= "<script type=\"text/javascript\" src=\"/js/jqdialog.js\"></script>\n";
include( TMP_HTML_DIR . "tpl.header.html" );

// エラーメッセージ処理
if ($errmsg) {
	print "<blockquote><font color=\"red\"><strong>";
	print strconv($errmsg);
	print "</strong></font></blockquote>\n";
}

{
	// 対象ファイル再設定
	print "<br />\n";
	print "<form enctype=\"multipart/form-data\" method=\"POST\" " .
	  "action=\"/external/reg_upload/\">\n";

	print "対象ファイル： <input id=\"file_upfile\" type=\"file\" name=\"file[upfile]\" size=\"60\" />\n";
	print "<input id=\"gid\" name=\"gid\" type=\"hidden\" value=\"" . 
	      $group_id . "\" />";
	print "<input type=\"submit\" value=\"再読み込み\">\n";
	print "</form>\n";
	print "<br />\n";
}

// Excelファイル表示処理
if ($xls) {
	put_css($xls);
	put_excel($xls);
}

// 登録実行ボタン
if ($xls) {
	print "<br>\n";
	print "<center>\n";
	print "<form enctype=\"multipart/form-data\" method=\"POST\" " .
	  "action=\"/external/reg_exec/\">\n";

	print "<input id=\"gid\" name=\"gid\" type=\"hidden\" value=\"" . 
	      $group_id . "\" />\n";
	print "<input id=\"file\" name=\"file\" type=\"hidden\" value=\"" . 
	      $file_id . "\" />\n";
	print "<input type=\"submit\" value=\"登録実行\">\n";
	print "</form>\n";
	print "</center>\n";
	print "<br />\n";
}

// フッタ読み込み
include( TMP_HTML_DIR . "tpl.footer.html" );

die;

//
// ファイル表示エリア
//
function put_excel($xls)
{
	// will be ignored... (remove?)
	global $flag_cc, $flag_cb, $flag_bc, $flag_bb;

	// シート表示
	for ($sn = 0; $sn < $xls->sheetnum; $sn++) {
		$fnum = 0;

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
			$hd["left"] = (isset($hd["left"])) ? strconv($hd["left"]) : "";
			$hd["center"] = (isset($hd["center"])) ? strconv($hd["center"]) : "";
			$hd["right"] = (isset($hd["right"])) ? strconv($hd["right"]) : "";

			print <<< STR

<table width="${w}" border="0" cellpadding="0" cellspacing="1" bordercolor="#CCCCCC" bgcolor="#CCCCCC">
<tr>
	<td width="30" nowrap><font size="1">ヘッダ</font></td>
	<td bgcolor="#FFFFFF"><div align="left"> ${hd["left"]} </div></td>
	<td bgcolor="#FFFFFF"><div align="center"> ${hd["center"]} </div></td>
	<td bgcolor="#FFFFFF"><div align="right"> ${hd["right"]} </div></td>
</tr></table>
STR;
		}

		// シートテーブル表示
		print <<< STR
<table id="tablefix-${sn}" class="sheet" border="0" cellpadding="0" cellspacing="0" width="${w}" bgcolor="#FFFFFF" style="border-collapse: collapse;">
  <tr bgcolor="#CCCCCC">
	<th class="XF" bgcolor="#CCCCCC" scope="col" width="32">&nbsp;</th>
STR;
		for ($i = 0; $i <= $xls->maxcell[$sn]; $i++) {
			$tdwidth = $xls->getColWidth($sn, $i);
			print "    <th class=\"XF\" bgcolor=\"#CCCCCC\" scope=\"col\" width=\"";
			print $tdwidth . "\">" . $i . "</th>" . "\n";
		}
		print "  </tr>\n";
		if (!isset($xls->maxrow[$sn]))
			$xls->maxrow[$sn] = 0;
		for ($r = 0; $r <= $xls->maxrow[$sn]; $r++) {
			print '  <tr height="' .
			  $xls->getRowHeight($sn, $r) . '">' . "\n";
			print "   <th class=\"XF\" bgcolor=\"#CCCCCC\" scope=\"row\">" . $r . "</th>\n";
			for ($i = 0; $i <= $xls->maxcell[$sn]; $i++) {
				$tdwidth = $xls->getColWidth($sn, $i);
				$dispval = $xls->dispcell($sn, $r, $i);
				$dispval = strconv($dispval);
				if (isset($xls->hlink[$sn][$r][$i])) {
					$dispval = "<a href=\"" . $xls->hlink[$sn][$r][$i] . "\">" . $dispval . "</a>";
				}

				$xf = $xls->getAttribute($sn, $r, $i);
				if (isset($xf["wrap"]) && $xf["wrap"])
					$dispval = preg_replace("/(\r\n|\n)/", "<br />", $dispval);

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
					else if ($xf["type"] == Type_FORMULA && ($dispval=="TRUE" || $dispval=="FALSE")) $align = " align=\"center\"";
					else if ($xf["type"] == Type_FORMULA2 && ($dispval=="TRUE" || $dispval=="FALSE")) $align = " align=\"center\"";
					else if ($xf["type"] == Type_BOOLERR) $align = " align=\"center\"";
					else $align= '';
					if ($xf["format"] == "@") $align = "";
				} else {
					$align = "";
				}

				if (substr($dispval,0,1) == "'") $dispval = substr($dispval, 1);
				if (substr($dispval,0,6) == "&#039;") $dispval = substr($dispval, 6);

				// セル表示
				$bgcolor = ($xf["fillpattern"] == 1);
				$enabled = (!$header_colum && !$header_row) ||
				  ($header_column - 1 <= $i && $header_row - 1 <= $r);

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
						$class = " class=\"XFs" . $sn . "r" . $r . "c" . $i . "\"";
						$id = " id=\"". $sn . "-" . $r ."-" . $i . "\"";
						if ($enabled &&
								(($flag_cc && $dispval && $bgcolor) ||
								($flag_cb && $dispval && !$bgcolor) ||
								($flag_bc && !$dispval && $bgcolor) ||
								($flag_bb && !$dispval && !$bgcolor))) {
							$name = " name=\"" . $sn . "-" . $fnum++. "\"";
							print " <td $class $id $name $rcspan $align>$dispval</td>\n";
						} else {
							print " <td $class $rcspan $align>$dispval</td>\n";
						}
					}
				} else {
					$class = " class=\"XF" . $xfno . "\" ";
					$id = " id=\"". $sn . "-" . $r ."-" . $i . "\"";
					$width = "width=\"" . $tdwidth . "\" ";

					if ($enabled &&
							(($flag_cc && $dispval && $bgcolor) ||
							($flag_cb && $dispval && !$bgcolor) ||
							($flag_bc && !$dispval && $bgcolor) ||
							($flag_bb && !$dispval && !$bgcolor))) {
						$name = " name=\"" . $sn . "-" . $fnum++. "\"";
						print " <td $class $id $name $width $align>$dispval</td>\n";
					} else {
						print " <td $class $width $align>$dispval</td>\n";
					}
				}
			}
			print "</tr>\n";
		}
		print "</table>\n"; // シートテーブル終了

		// フッタ表示
		if ($ft !== null) {
			$ft["left"] = (isset($ft["left"])) ? strconv($ft["left"]) : "";
			$ft["center"] = (isset($ft["center"])) ? strconv($ft["center"]) : "";
			$ft["right"] = (isset($ft["right"])) ? strconv($ft["right"]) : "";

			print <<< STR
<table width="${w}" border="0" cellpadding="0" cellspacing="1" bordercolor="#CCCCCC" bgcolor="#CCCCCC"><tr>
	<td width="30" nowrap><font size="1">フッタ</font></td>
	<td bgcolor="#FFFFFF"><div align="left">${ft["left"]} </div></td>
	<td bgcolor="#FFFFFF"><div align="center">${ft["center"]}</div></td>
	<td bgcolor="#FFFFFF"><div align="right">${ft["right"]}</div></td>
</tr></table>
STR;
		}
	}
}

?>
