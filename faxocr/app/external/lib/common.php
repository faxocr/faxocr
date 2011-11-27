<?php

require_once 'config.php';

function put_css($xls) {
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

function strconv($str) {
	global $charset;

	$str = htmlentities($str, ENT_QUOTES, $charset);
	$str = str_replace('&conint;', mb_convert_encoding("∮", $charset, "utf-8"), $str);
	$str = str_replace('&ang90;', mb_convert_encoding("∟", $charset, "utf-8"), $str);
	$str = str_replace('&becaus;', mb_convert_encoding("∵", $charset, "utf-8"), $str);

	return $str;
}

function put_err_page($msg) {
	print "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
	print "<html>\n";
	print "<head>\n";
	print "<meta http-equiv=\"content-type\" content=\"text/html;charset=utf-8\">\n";
	print "</head>\n";
	print "<body>\n";
	print $msg;
	print "</body>\n";
	print "</html>\n";
}

function is_referer_err() {
	// リファラチェック用変数
	$referer_check_values = array();
	$referer_check_values["form-commit.php"] = array("form-setup.php");

	if (!isset($_SERVER["HTTP_REFERER"])) {
		put_err_page("不正なアクセスです");
		die;
	}

	$script_name = basename($_SERVER['SCRIPT_NAME']);
	$referer_script_name = basename($_SERVER['HTTP_REFERER']);
	$referer_target_values = $referer_check_values[$script_name];

	if (is_array($referer_target_values)) {
		$err_flg = true;
		// 正しい画面遷移かのチェック
		foreach ($referer_target_values as $value) {
			if (strpos($referer_script_name, $value) >= 0) {
				$err_flg = false;
			}
		}
		if ($err_flg) {
			put_err_page("不正なアクセスです");
			die;
		}
	}
}

?>
