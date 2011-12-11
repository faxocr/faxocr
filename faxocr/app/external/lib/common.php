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

define("ERROR", -1);
define("NOERR", 0);
define("FILEDB_HANDLER", "db4");
define("ID_LENGTH", 20);

// モード名ラベル
$mode_label = array();
$mode_label["anon-single"] = "一回報告様式";
$mode_label["anon-multi"] = "複数報告様式";
$mode_label["ident-single"] = "一回報告様式/特定対象";
$mode_label["ident-multi"] = "複数報告様式/特定対象";

//
// ファイルIDを作成する。
//
function get_id($filename)
{
	if (file_exists(FILEDB_PATH)) {
		if (!($id = dba_open(FILEDB_PATH, "wd", FILEDB_HANDLER))) {
			return ERROR;
		}
	} else {
		if (!($id = dba_open(FILEDB_PATH, "c",  FILEDB_HANDLER))) {
			return ERROR;
		}
	}

	do {
 		$key = genUniqueId();
	} while (dba_exists($key, $id));

	dba_replace($key, $filename, $id);

	dba_close($id);

	return $key;
}

//
// ファイルIDの存在チェックを行う
//
function is_exists_key($key)
{
	if (!($id = dba_open(FILEDB_PATH, "wd", FILEDB_HANDLER))) {
		return ERROR;
	}

	$result = dba_exists($key, $id);

	dba_close($id);

	return $result;
}

//
// DBAデータベースの登録、更新処理
//
function replace_string($key, $value)
{
	if (!($id = dba_open(FILEDB_PATH, "wd", FILEDB_HANDLER))) {
		return ERROR;
	}

	dba_replace($key, $value, $id);
	dba_close($id);

	return NOERR;
}

//
// ファイル名を取得する
//
function get_string($key)
{
	if (!($id = dba_open(FILEDB_PATH, "wd", FILEDB_HANDLER))) {
		return ERROR;
	}

	if (dba_exists($key, $id)) {
		$str = dba_fetch($key, $id);
	} else {
		$str = ERROR;
	}

 	dba_close($id);

 	return $str;
}

//
// DBAデータベースの削除処理
//
function del_entry($key)
{
	$error = NOERR;

	if (!($id = dba_open(FILEDB_PATH, "wd", FILEDB_HANDLER))) {
		return ERROR;
	}

	if (dba_delete($key, $id)) {
		$error = ERROR;
	}

	dba_close($id);

	return $error;
}

//
// ファイルID一覧を取得する
//
function list_keys()
{
	$res = array();

	if (!($id = dba_open(FILEDB_PATH, "wd", FILEDB_HANDLER))) {
		return ERROR;
	}

	for ($key = dba_firstkey($id); $key != false; $key = dba_nextkey($id)) {
		array_push($res, array($key, dba_fetch($key, $id)));
	}

	dba_close($id);

	return $res;
}

//
// 共有用のファイルIDを作成する
//
function get_reverse_id($file_id)
{
	if (file_exists(FILE_REVERSE_DB_PATH)) {
		if (!($id = dba_open(FILE_REVERSE_DB_PATH, "wd", FILEDB_HANDLER))) {
			return ERROR;
		}
	} else {
		if (!($id = dba_open(FILE_REVERSE_DB_PATH, "c",  FILEDB_HANDLER))) {
			return ERROR;
		}
	}

	do {
		$key = genUniqueId();
	} while (dba_exists($key, $id));

	dba_replace($key, $file_id, $id);

	dba_close($id);

	return $key;
}

//
// 共有用のファイルIDからファイル名を取得する
//
function get_reverse_string($key)
{
	if (!($id = dba_open(FILE_REVERSE_DB_PATH, "wd", FILEDB_HANDLER))) {
		return ERROR;
	}

	if (dba_exists($key, $id)) {
		$str = dba_fetch($key, $id);
	} else {
		$str = ERROR;
	}

	dba_close($id);

	return $str;
}

//
// 現在時刻のハッシュ値から20桁のIDを作成する
//
function genUniqueId()
{
	$chars = array(
		'0', '1', '2', '3', '4', '5', '6', '7', '8', '9',

		'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
		'K', 'L', 'M', 'N',      'P', 'Q', 'R', 'S', 'T',
		'U', 'V', 'W', 'X', 'Y', 'Z',

		'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j',
		'k',      'm', 'n',      'p', 'q', 'r', 's', 't',
 		'u', 'v', 'w', 'x', 'y', 'z',
	);

	$base = count($chars);

	$tmp = unpack("C*", sha1(microtime(), TRUE));

	foreach($tmp as $v) {
		$str[] = $chars[$v % $base];
	}

	$str = array_splice($str, 0, ID_LENGTH);

	$key = implode ($str);

	return $key;
}

//
// 配列をカンマ区切りに変更する
//
function genCsvData($rows)
{
	$result_csv = "";

	foreach ($rows as $cols) {
		$cols_csv = "";
		foreach ($cols as $col) {
			$sp_cnt = substr_count($col, " ");

			$cols_csv .= (($cols_csv != "") ? "," : "")
					. (($sp_cnt > 0) ? "\"" : "")
					. $col
					. (($sp_cnt > 0) ? "\"" : "");
		}
		$result_csv .= $cols_csv . "\n";
	}

	return $result_csv;
}

//
// CSSを設定する
//
function put_css($xls)
{
	$css = $xls->makecss();
	print <<< _CSS
<style type="text/css">
<!--
body,td,th {
	font-size: normal;
}

.XF {
border-top-width: 1px;
border-top-style: solid;
border-top-color: #444444;
border-left-width: 1px;
border-left-style: solid;
border-left-color: #444444;
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #444444;
border-right-width: 1px;
border-right-style: solid;
border-right-color: #444444;
}

${css}
-->
</style>
_CSS;
}

//
// エスケープ処理
//
function strconv($str)
{
	global $charset;

	$str = htmlentities($str, ENT_QUOTES, $charset);
	$str = str_replace('&conint;', mb_convert_encoding("∮", $charset, "utf-8"), $str);
	$str = str_replace('&ang90;', mb_convert_encoding("∟", $charset, "utf-8"), $str);
	$str = str_replace('&becaus;', mb_convert_encoding("∵", $charset, "utf-8"), $str);

	return $str;
}

//
// エラーメッセージをPOST値に設定し、指定の画面にリダイレクトする
//
function put_reject($msg, $file_id = null, $return_url)
{
	print "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
	print "<html>\n";
	print "<head>\n";
	print "<meta http-equiv=\"content-type\" content=\"text/html;charset=utf-8\">\n";
	print "<script type=\"text/javascript\">\n";
	print "<!--\n";
	print "window.onload = function() {\n";
	print "   frm = document.getElementById('redirect');\n";
	print "   frm.submit();\n";
	print "};\n";
	print "-->\n";
	print "</script>\n";
	print "</head>\n";
	print "<body>\n";
	print "<form id=\"redirect\" action=\"" . $return_url . "\" method=\"POST\">\n";
	print "<input type=\"hidden\" name=\"msg\" value=\"${msg}\" />\n";
	if ($file_id != null) {
		print "<input type=\"hidden\" name=\"file\" value=\"" . $file_id . "\" />\n";
	}
	print "</form>\n";
	print "</body>\n";
	print "</html>\n";
}

//
// エラーメッセージをエラー画面を表示する
//
function put_err_page($msg)
{
	include( TMP_HTML_DIR . "tpl.header.html" );
	print "<blockquote><font color=\"red\"><strong>";
	print strconv($msg);
	print "</strong></font></blockquote>";
	include( TMP_HTML_DIR . "tpl.footer.html" );
}


//
// パスワード入力画面を表示する
//
function put_password_page($file_id = null, $return_url)
{
	print "<link rel=\"stylesheet\" href=\"./css/jqdialog.css\" type=\"text/css\" />\n";
	print "<script type=\"text/javascript\" src=\"./js/jquery-1.4.1.min.js\"></script>\n";
	print "<script type=\"text/javascript\" src=\"./js/jqdialog.js\"></script>\n";
	print "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
	print "<html>\n";
	print "<head>\n";
	print "<meta http-equiv=\"content-type\" content=\"text/html;charset=utf-8\">\n";
	print "<script type=\"text/javascript\">\n";
	print "<!--\n";
	print "window.onload = function() {\n";
	print "    $.jqDialog.password(\"パスワードを入力して下さい\",";
	print "        function(data) {";
	print "	           $(\"#passwd\").val(data);";
	print "	           $(\"#redirect\").submit();";
	print "        }";
	print "     );";
	print "};\n";

	// to enable enter key
	print "document.onkeydown = function(e) {\n";
	print "	var keycode;\n";
	print "\n";
	print "	if (e != null) {\n";
	print "		// Mozilla(Firefox, NN) and Opera\n";
	print "		keycode = e.which;\n";
	print "	} else {\n";
	print "		// Internet Explorer\n";
	print "		keycode = event.keyCode;\n";
	print "	}\n";
	print "\n";
	print "	// Enter\n";
	print "	if (keycode == '13') {\n";
	print "		a = document.activeElement.parentNode.nextSibling;\n";
	print "		a.click();\n";
	print "	}\n";
	print "};\n";

	print "-->\n";
	print "</script>\n";
	print "</head>\n";
	print "<body>\n";
	print "<form id=\"redirect\" action=\"" . $return_url . "?file=" . $file_id  . "\" method=\"POST\">\n";
	print "<input type=\"hidden\" name=\"password\"id=\"passwd\" />\n";
	print "</form>\n";
	print "</body>\n";
	print "</html>\n";
}

?>
