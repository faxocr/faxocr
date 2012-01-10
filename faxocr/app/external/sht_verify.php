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
	// XXX: $errmsg = "ファイルが読み込めません";
	put_err_page("不正なアクセスです");
	die;
}

//
// ヘッダ処理
//
$header_opt .= "<script type=\"text/javascript\" src=\"/external/js/jquery-1.4.1.min.js\"></script>\n";

include( TMP_HTML_DIR . "tpl.header.html" );

if ($errmsg) {
	// エラーメッセージ処理
	print "<blockquote><font color=\"red\"><strong>";
	print strconv($errmsg);
	print "</strong></font></blockquote>";
}

{
	// ステータス表示
	print "<table width=\"100%\">\n";
	print "<td align=\"right\"></td>\n";
	print "<td align=\"right\"\"  width=\"450px\">";
	put_status();
	print "</td>\n";
	print "</tr></table>\n";
	print "<br />\n";
}

// サンプルファイル表示処理
{
	print "<div align=\"center\" style=\"background-color: lightgray;\">\n";
	print "<BR>\n";
	if (file_exists(DST_DIR . $file_id . ".png")) {
		print "<img src=\"/external/getimg/" . $group_id . "/" . $sheet_id . 
		         "\" width=\"500px\"><br>\n";
		print "<a href=\"/external/download/" . $group_id . "/" . $sheet_id . 
		        "\">[Sample PDF]</a>\n";
	} else {
		print "画像ファイルがありません<br><br>\n";
	}
	print "</div>\n";
	print "<div align=\"right\">\n";
	print "</div>\n";
}

//
// フッタ読み込み
//
include( TMP_HTML_DIR . "tpl.footer.html" );

die;

//
// ステータス操作エリア表示
//
function put_status()
{
	global $file_id;
	global $group_id;
	global $sheet_id;

	print <<< STR
<script type="text/javascript">
<!--
function go_prev() {
	$("#form-status").attr("action", "/external/sht_marker/").submit();
}

function go_next() {
	$("#form-status").attr("action", "/external/sht_commit/").submit();
}

-->
</script>

STR;

	$style = array();
	$style["normal"] = "style=\"border-style:solid;border-width:1px;border-color:#dddddd;background-color:#ffffff;padding:1px;color:gray\"";
	$style["gray"] = "style=\"border-style:solid;border-width:1px;border-color:#dddddd;background-color:#bbbbbb;padding:1px\"";
	$style["lgray"] = "style=\"border-style:solid;border-width:1px;border-color:#dddddd;background-color:#dddddd;padding:1px\"";
	$style["pink"] = "style=\"border-style:solid;border-width:1px;border-color:#dddddd;background-color:#ffdddd;padding:1px\"";

	// XXX
	// action=\"/external\sht_commit/\" 
	print "<form method=\"POST\" id=\"form-status\">\n";
	print "<input type=\"hidden\" name=\"fileid\" value=\"" . $file_id . "\" />\n";
	print "<input type=\"hidden\" name=\"gid\" value=\"" . $group_id . "\" />\n";
	print "<input type=\"hidden\" name=\"sid\" value=\"" . $sheet_id . "\" />\n";

	print "<div style=\"border-style:solid;border-color:#dddddd;border-width:1px;padding:2px;\" class=\"statusMenu\">\n";
	print "<div ${style["gray"]}><span>フィールド指定</span></div>\n";
	print "<div ${style["gray"]}><button id=\"next\" onclick=\"go_prev();\">マーカー指定</button></div>\n";

	print "<div ${style["pink"]}><span>シート確認</span></div>\n";
	print "<div ${style["lgray"]}><button id=\"next\" onclick=\"go_next();\">シート登録</button></div>\n";
	print "</div>\n";

	print "</form>\n";
}

?>
