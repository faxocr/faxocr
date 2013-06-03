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

// ===========================================================================
// 設定情報操作
// ===========================================================================

require_once "config.php";
require_once "lib/common.php";

//
// 設定情報操作クラス
//
class FileConf {

	protected $FileConfObj;

	function __construct($file_id)
	{
		if (FILE_CONF_STORAGE_DEVICE == "filedb") {
			$this->FileConfObj = new FileConfFileDb($file_id);
		} else {
			$this->FileConfObj = new FileConfFile($file_id);
		}
	}

	public function get($item)
	{
		return $this->FileConfObj->get($item);
	}

	public function set($item, $value)
	{
		return $this->FileConfObj->set($item, $value);
	}

	public function commit()
	{
		return $this->FileConfObj->commit();
	}

	public function array_destroy($item)
	{
		return $this->FileConfObj->array_destroy($item);
	}

	public function array_set($item, $confs)
	{
		return $this->FileConfObj->array_set($item, $confs);
	}

	public function array_getall($item)
	{
		return $this->FileConfObj->array_getall($item);
	}

	public function array_commit()
	{
		return $this->FileConfObj->array_commit();
	}
}

//
// 共有設定(fileデバイス)操作クラス
//
class FileConfFile {

	protected $file_id;

	protected $get_confs;
	protected $set_confs;
	protected $b_exist_get_confs;

	protected $set_array_items;
	protected $set_array_confs;

	function __construct($file_id)
	{
		$this->file_id = $file_id;

		$this->get_confs = array();
		$this->set_confs = array();
		$this->b_exist_get_conf = false;

		$this->set_array_items = array();
		$this->set_array_confs = array();
	}

	//
	// キー項目に対する値を取得する
	//
	public function get($item)
	{
		if (!$this->b_exist_get_confs) {

			$conf_path = DST_DIR . $this->file_id . CONF_EXT;

			$lines = file($conf_path);

			for ($cnt = 0; $cnt < count($lines); $cnt++) {
				$line = explode(",", $lines[$cnt], 2);
				$this->get_confs[$line[0]] = trim($line[1]);
			}

			$this->b_exist_get_confs = true;
		}

		$result = (isset($this->get_confs[$item])) ? $this->get_confs[$item] : "";

		switch ($item) {
			case "flag-cc":
			case "flag-bc":
			case "flag-cb":
			case "flag-bb":
			case "append_row":
			case "append_column":
				$result = ($result != "") ? true : false;
				break;
		}

		return $result;
	}

	//
	// キー項目に値を設定する
	//
	public function set($item, $value)
	{
		$this->set_confs[$item] = $value;

		return NOERR;
	}

	//
	// 設定値を保存する
	//
	public function commit()
	{
		$conf_path = DST_DIR . $this->file_id . CONF_EXT;

		$lines = @file($conf_path);
		if (!$lines) {
			$lines = array();
		}

		foreach ($this->set_confs as $conf_key => $conf_value) {
			$bUpdated = false;

			for ($cnt = 0; $cnt < count($lines); $cnt++) {
				$line = explode(",", $lines[$cnt], 2);
				if ($conf_key == $line[0]) {
					$lines[$cnt] = "{$conf_key},{$conf_value}\n";
					$bUpdated = true;
					break;
				}
			}

			if (!$bUpdated) {
				$lines[$cnt] = "{$conf_key},{$conf_value}\n";
			}
		}

		$fp = fopen($conf_path, "w");

		for ($cnt = 0; $cnt < count($lines); $cnt++) {
			fwrite($fp, $lines[$cnt]);
		}

		fclose($fp);

		foreach ($this->set_confs as $conf_key => $conf_value) {
			$this->get_confs[$conf_key] = $conf_value;
		}
		$this->set_confs = array();

		return NOERR;
	}

	//
	// キー項目の値を削除する
	//
	public function array_destroy($item)
	{
		$conf_path = DST_DIR . $this->file_id . ARRAY_CONF_EXT;

		if (file_exists($conf_path)) {
			$lines = file($conf_path);
		} else {
			$lines = array();
		}

		$fp = fopen($conf_path, "w");

		for ($cnt = 0; $cnt < count($lines); $cnt++) {
			$line = explode(",", $lines[$cnt]);
			$items = explode("=", $line[0]);
			if ($items[0] != $item) {
				fwrite($fp, $lines[$cnt]);
			}
		}

		fclose($fp);

		return NOERR;
	}

	//
	// 配列用confのキー項目に対する値を設定する
	//
	public function array_set($item, $confs)
	{
		$b_exists = false;
		if (count($this->set_array_items) > 0) {
			foreach ($this->set_array_items as $exists_item) {
				if ($exists_item == $item) {
					$b_exists = true;
					break;
				}
			}
		}
		if (!$b_exists) {
			$this->set_array_items[] = $item;
		}
		$this->set_array_confs[$item][] = $confs;

		return NOERR;
	}

	//
	// 配列用confのキー項目に対する値を取得する
	//
	public function array_getall($item)
	{
		$result = array();

		$conf_path = DST_DIR . $this->file_id . ARRAY_CONF_EXT;

		$lines = @file($conf_path);
		if (!$lines) {
			$lines = array();
		}

		$result_cnt = 0;
		for ($cnt = 0; $cnt < count($lines); $cnt++) {
			$lines[$cnt] = trim($lines[$cnt]);
			$line = explode("," , $lines[$cnt]);

 			$sep_key_value = explode("=", $line[0]);
 			if ($sep_key_value[0] == $item) {

				foreach ($line as $key_value) {
					$sep_key_value = explode("=", $key_value);
					if ($sep_key_value[0] != $item) {
						$result[$result_cnt][$sep_key_value[0]] = $sep_key_value[1];
					}
				}
				$result_cnt++;
 			}
		}

		return $result;
	}

	//
	// 配列用の設定値を保存する
	//
	public function array_commit()
	{
		$conf_path = DST_DIR . $this->file_id . ARRAY_CONF_EXT;

		$lines = @file($conf_path);
		if (!$lines) {
			$lines = array();
		}

		$fp = fopen($conf_path, "w");

		foreach ($lines as $line) {
			fwrite($fp, $line);
		}

		foreach ($this->set_array_items as $item) {
			for ($cnt = 0; $cnt < count($this->set_array_confs[$item]); $cnt++) {
				$confs = $this->set_array_confs[$item][$cnt];

				do {
					$b_exists = FALSE;
					$array_conf_id = genUniqueId();
					foreach ($lines as $line) {
						$items = explode(",", $line);
						$item  = explode("=", $items[0]);
						if ($item[1] == $array_conf_id) {
							$b_exists = TRUE;
							break;
						}
					}
				} while ($b_exists);

				$line = $item . "=" . $array_conf_id;
				foreach ($confs as $key => $value) {
					$line .= "," . $key . "=" . $value;
				}
				$line .= "\n";
				fwrite($fp, $line);
			}
		}

		fclose($fp);

		$set_array_items = array();
		$set_array_confs = array();

		return NOERR;
	}
}

//
// 共有設定(filedbデバイス)操作クラス
//
class FileConfFileDb
{

	protected $file_id;

	protected $set_confs;

	protected $set_array_items;
	protected $set_array_confs;

	function __construct($file_id)
	{
		$this->file_id = $file_id;

		$this->set_confs = array();

		$set_array_items = array();
		$set_array_confs = array();
	}

	//
	// キー項目に対する値を取得する
	//
	public function get($item)
	{
		$file_db_key = $this->file_id . "-" . $item;
		$result = get_string($file_db_key);
		if ($result == ERROR) {
			$result = "";
		}

		switch ($item) {
			case "flag-cc":
			case "flag-bc":
			case "flag-cb":
			case "flag-bb":
			case "append_row":
			case "append_column":
				break;
				$result = ($result != "") ? true : false;
		}

		return $result;
	}

	//
	// キー項目に値を設定する
	//
	public function set($item, $value)
	{
		$this->set_confs[$item] = $value;

		return NOERR;
	}

	//
	// 設定値を保存する
	//
	public function commit() {

		foreach ($this->set_confs as $key => $value) {
			$file_db_key = $this->file_id . "-" . $key;
			replace_string($file_db_key, $value);
		}
		$this->set_confs = array();

		return NOERR;
	}

	//
	// キー項目の値を削除する
	//
	public function array_destroy($item)
	{
		$file_db_list_key = $this->file_id . "-" . $item;
		$item_list_str = get_string($file_db_list_key);
		if ($item_list_str == ERROR) {
			return NOERR;
		}

		$item_id_list = explode(",", $item_list_str);

		for ($cnt = 0; $cnt < count($item_id_list); $cnt++) {
			$del_entry($item_id_list[$cnt]);
		}

		$del_entry($file_db_list_key);

		return NOERR;
	}

	//
	// 配列用confのキー項目に対する値を設定する
	//
	public function array_set($item, $confs)
	{
		$b_exists = false;
		if (count($this->set_array_items) > 0) {
			foreach ($this->set_array_items as $exists_item) {
				if ($exists_item == $item) {
					$b_exists = true;
					break;
				}
			}
		}
		if (!$b_exists) {
			$this->set_array_items[] = $item;
		}
		$this->set_array_confs[$item][] = $confs;

		return NOERR;
	}

	//
	// 配列用confのキー項目に対する値を取得する
	//
	public function array_getall($item)
	{
		$result = array();

		$file_db_list_key = $this->file_id . "-" . $item;
		$item_list_str = get_string($file_db_list_key);
		if ($item_list_str == ERROR) {
			return $result;
		}

		$item_id_list = explode(",", $item_list_str);

		$result_cnt = 0;
		for ($cnt = 0; $cnt < count($item_id_list); $cnt++) {

			$values = get_string($item_id_list[$cnt]);

			if ($values != ERROR) {
				$line = explode("," , $values);
 				foreach ($line as $key_value) {
					$sep_key_value = explode("=", $key_value);
					$result[$result_cnt][$sep_key_value[0]] = $sep_key_value[1];
				}
				$result_cnt++;
 			}
		}

		return $result;
	}

	//
	// 配列用の設定値を保存する
	//
	public function array_commit()
	{
		foreach ($this->set_array_items as $item) {

			$file_db_list_key = $this->file_id . "-" . $item;
			$item_list_str = get_string($file_db_list_key);
			if ($item_list_str == ERROR) {
				$item_list_str = "";
			}

			for ($cnt = 0; $cnt < count($this->set_array_confs[$item]); $cnt++) {
				$confs = $this->set_array_confs[$item][$cnt];

				do {
					$array_conf_id = genUniqueId();
					$b_exists = is_exists_key($array_conf_id);
					if ($b_exists == ERROR) break;
				} while ($b_exists);

				if ($b_exists == ERROR) {
					return ERROR;
				}

				$line = "";
				foreach ($confs as $key => $value) {
					if ($line != "") $line .= ",";
					$line .= $key . "=" . $value;
				}
				replace_string($array_conf_id, $line);

				if ($item_list_str != "") {
					$item_list_str .= ",";
				}
				$item_list_str .= $array_conf_id;
			}

			replace_string($file_db_list_key, $item_list_str);
		}

		$set_array_items = array();
		$set_array_confs = array();

		return NOERR;
	}
}

?>
