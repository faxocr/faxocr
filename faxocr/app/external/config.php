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

// 最大ファイルサイズ
$max_upload_size = 1024 * 128;

// utf-8以外の文字エンコーディングを使用する場合は、
// 以下のutf-8を変更してください。文字コードによっては
// 機種依存文字が化ける場合があります
$charset='utf-8';

// Dst Dir Name
define("DST_DIR", dirname(__FILE__) . "/../../ext-files/");

// Tmp Dir Name
define("TMP_DIR", "/tmp/");

// Obj Data Ext
define("DATA_EXT", ".dat");

// Obj Data Ext
define("ORIG_EXT", ".orig");

// Lockfile
define("LOCK_FILE", TMP_DIR . "excel_lock_");

// Tmp HTML Dir Name
define("TMP_HTML_DIR", dirname(__FILE__) . "/html-template/");

// Template XLS Dir Name
define("TMP_XLS_DIR", "./xls-template/");

// Template XLS Path Name
define("TMP_XLS", TMP_XLS_DIR . "template.xls");

// for maker view
define("COLOR_FILL", 22);

?>
