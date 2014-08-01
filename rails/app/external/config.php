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

// 文字エンコーディング
//
// utf-8以外を使用する場合は、以下を変更してください。
// 文字コードによっては機種依存文字が化ける場合があります。
//
$charset = "utf-8";

// 最大登録ファイルサイズ
$max_upload_size = 1024 * 128;

// パスワード義務化 (1: yes, 0: no)
$password_required = 0;

// ファイル同一性閾値
//
// formモードでファイルをアップロードする際、正統なテンプレートを
// 利用していると見なす閾値
//
$proximity_threshold = 0.025;

// 任意リンクURL
//
// 画面下部に、フィードバックページなど、任意のリンクを挿入するこ
// とが出来ます。
//
$link_url = "";

// 任意リンクラベル
//
$link_label = "";

// マーカー色指定 (マーカーモード用)
define("COLOR_FILL", 22);

// 調査シートの最大/最小サイズ(セルの数)
define("MAX_SHEET_WIDTH", 18);
define("MAX_SHEET_HEIGHT", 30);
define("MIN_SHEET_WIDTH", 6);
define("MIN_SHEET_HEIGHT", 10);

// 調査シートの最小サイズ(px)
define("MIN_CELL_WIDTH", 30);
define("MIN_CELL_HEIGHT", 30);


///////////////////////////////////////////////////////
// ディレクトリ設定
//

// 登録ファイル保存ディレクトリ
define("DST_DIR", dirname(__FILE__) . "/../../files/");

// アップロードXLS保存ディレクトリ
define("XLS_DIR", dirname(__FILE__) . "/../orig/");

// 一時ファイル保存ディレクトリ
define("TMP_DIR", "/tmp/");

// HTMLスキンディレクトリ
define("TMP_HTML_DIR", dirname(__FILE__) . "/skin/");

// XLSテンプレートディレクトリ
define("TMP_XLS_DIR", "./xls-template/");

///////////////////////////////////////////////////////
// 拡張子・ロックファイル設定
//

// ターゲットXLSデータ用拡張子 (システムが修正しうる)
define("DATA_EXT", ".dat");

// オリジナルXLSデータ用拡張子 (システムは修正しない)
define("ORIG_EXT", ".orig");

// 設定ファイル用拡張子
define("CONF_EXT", ".conf");

// 配列型設定ファイル用拡張子 (XXX: 将来的にCONF_EXTと統合)
define("ARRAY_CONF_EXT", ".acnf");

// ログファイル用拡張子
define("LOG_EXT", ".log");

// 報告対象リスト用拡張子
define("LIST_EXT", ".lst");

// 報告対象セレクタ用拡張子
define("SELECT_EXT", ".html");

// 書き込みロックファイル
define("LOCK_FILE", TMP_DIR . "excel_lock_");

///////////////////////////////////////////////////////
// XLSテンプレート
//

// XLS出力用テンプレート
//
// reviser.phpは、xlsファイルの生成に際して種となるxlsファイルを要します
define("TMP_XLS", TMP_XLS_DIR . "template.xls");

// 報告対象テンプレート：都道府県
define("LIST_PREF_XLS", TMP_XLS_DIR . "list-pref.xls");

// 報告対象テンプレート：政令指定都市 (H22版?)
define("LIST_CITIES_XLS", TMP_XLS_DIR . "list-cities.xls");

// 報告対象テンプレート：保健所 (H22版?)
define("LIST_PHC_XLS", TMP_XLS_DIR . "list-phc.xls");

///////////////////////////////////////////////////////
// XLSファイル毎設定
//

// 設定保存形式 {file, filedb}
define("FILE_CONF_STORAGE_DEVICE", "file");

// 対象ファイルディレクトリ
define("FILEDB_DIR", "../files/");

// ファイルデータベースファイル
define("FILEDB_PATH", "../files/file.db");

// ファイル逆引きデータベースファイル
define("FILE_REVERSE_DB_PATH", "../files/file_reverse.db");

///////////////////////////////////////////////////////
// MySQL接続情報
//

// MySQLサーバ
define("MYSQL_DB_SERVER_NAME", "localhost");

// データベース名
define("MYSQL_DB_NAME", "ezcloud");

// アクセスユーザ名
define("MYSQL_DB_USER_NAME", "cloud");

// アクセスパスワード
define("MYSQL_DB_USER_PASS", "cloudpass");

?>
