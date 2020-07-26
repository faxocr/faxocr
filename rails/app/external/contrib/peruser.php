<?php
/*Excel_Peruser Ver0.11 beta4  (2008.09.06)
 *  Author:kishiyan
 *
 * Copyright (c) 2006-2008 kishiyan <excelreviser@gmail.com>
 * All rights reserved.
 *
 * Support
 *   URL  http://chazuke.com/forum/
 *
 * Redistribution and use in source, with or without modification, are
 * permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer,
 *    without modification, immediately at the beginning of the file.
 * 2. The name of the author may not be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/*  HISTORY
2007.01.23 1st release
2008.04.26 include a part of Excel_Reviser
2008.04.26 add 'getPropJP' method
2008.05.17 Fix BOOLERR read
2008.06.29 Fix for MAC-binary
2008.07.08 Fix some of date-type
2008.07.10 Fix Default-Column-attibute
2008.07.23 part of memory optimise
*/

//require_once 'reviser.php';
define('This_Class_Name', 'Excel_Peruser');
define('Peruser_Ver', 0.110);
define('Default_CHARSET', 'eucJP-win');
define('Code_BIFF8', 0x600);
define('Code_WorkbookGlobals', 0x5);
define('Code_Worksheet', 0x10);
define('Type_EOF', 0x0a);
define('Type_BOUNDSHEET', 0x85);
define('Type_SST', 0xfc);
define('Type_CONTINUE', 0x3c);
define('Type_EXTSST', 0xff);
define('Type_LABELSST', 0xfd);
define('Type_WRITEACCESS', 0x5c);
define('Type_OBJPROJ', 0xd3);
define('Type_BUTTONPROPERTYSET', 0x1ba);
define('Type_DIMENSION', 0x200);
define('Type_ROW', 0x208);
define('Type_DBCELL', 0xd7);
define('Type_RK', 0x7e);
define('Type_RK2', 0x27e);
define('Type_MULRK', 0xbd);
define('Type_MULBLANK', 0xbe);
define('Type_INDEX', 0x20b);
define('Type_NUMBER', 0x203);
define('Type_FORMULA', 0x406);
define('Type_FORMULA2', 0x6);
define('Type_BOOLERR', 0x205);
define('Type_UNKNOWN', 0xffff);
define('Type_BLANK', 0x201);
define('Type_SharedFormula', 0x4bc);
define('Type_STRING', 0x207);
define('Type_HEADER', 0x14);
define('Type_FOOTER', 0x15);
define('Type_BOF', 0x809);
define('Type_SUPBOOK', 0x1ae);
define('Type_EXTERNSHEET', 0x17);
define('Type_NAME', 0x18);
define('Type_FILEPASS', 0x2f);
define('Type_LABEL', 0x204);
define('Type_FONT', 0x31);
define('Type_FORMAT', 0x041e);
define('Type_XF', 0xe0);
define('Type_DEFAULTROWHEIGHT', 0x225);
define('Type_DEFCOLWIDTH', 0x55);
define('Type_COLINFO', 0x7d);
define('Type_MERGEDCELLS', 0xe5);
define('Type_HLINK', 0x1b8);
define('Const_fontH', 16);


class Excel_Peruser
{
	var $biff_ver=Code_BIFF8;
	var $wbdat='';
	var $globaldat=array();
	var $rowblock=array();
	var $cellblock=array();
	var $sheetbin=array();
	var $boundsheets=array();
	var $eachsst=array();
	var $charset;

	var $recFONT=array();
	var $recFORMAT=array();
	var $recXF=array();
	var $rowheight=array();
	var $colwidth=array();
	var $coldefxf=array();
	var $rowdefxf=array();
	var $celmergeinfo=array();
	var $hlink=array();
	var $maxcell=array();
	var $maxrow=array();
	var $sheetnum;
	var $sheetname=array();
	// save magic_quotes Flag
	var $Flag_Magic_Quotes=False;
	// Error handling method
	var $Flag_Error_Handling=0;
	var $palette=array(
		"000000","FFFFFF","FF0000","00FF00",
		"0000FF","FFFF00","FF00FF","00FFFF",
		"000000","FFFFFF","FF0000","00FF00",
		"0000FF","FFFF00","FF00FF","00FFFF",
		"800000","008000","000080","808000",
		"800080","008080","C0C0C0","808080",
		"9999FF","993366","FFFFCC","CCFFFF",
		"660066","FF8080","0066CC","CCCCFF",
		"000080","FF00FF","FFFF00","00FFFF",
		"800080","800000","008080","0000FF",
		"00CCFF","CCFFFF","CCFFCC","FFFF99",
		"99CCFF","FF99CC","CC99FF","FFCC99",
		"3366FF","33CCCC","99CC00","FFCC00",
		"FF9900","FF6600","666699","969696",
		"003366","339966","003300","333300",
		"993300","993366","333399","333333",
		"000000","FFFFFF"	/* 0x40 */
		);
	var $infilename;
	var $headfoot=array();
	var $oledat;
	var $tag = array(	// Japanese property tags
		1 => array(
			 1 => "コードページ",
			 2 => "タイトル",
			 3 => "サブタイトル",
			 4 => "作成者",
			 5 => "キーワード",
			 6 => "コメント",
			 8 => "最終保存者",
			12 => "作成日時",
			13 => "前回保存日時",
			17 => "プレビューの図を保存する",
			18 => "作成アプリ",
			19 => "セキュリティ"
			),
		2 => array(
			 0 => "ディクショナリ",
			 1 => "コードページ",
			 2 => "分類",
			11 => "拡大/縮小",
			12 => "構成内容",
			13 => "構成部品",
			14 => "管理者",
			15 => "会社名",
			16 => "未更新のリンクオブジェクト"
			)
		);
	var $tag1 = array(	// property tags
		1 => array(
			 1 => "CODEPAGE",
			 2 => "TITLE",
			 3 => "SUBJECT",
			 4 => "AUTHOR",
			 5 => "KEYWORDS",
			 6 => "COMMENTS",
			 8 => "LASTAUTHOR",
			12 => "CREATE_DTM",
			13 => "LASTSAVE_DTM",
			17 => "THUMBNAIL",
			18 => "APPNAME",
			19 => "SECURITY"
			),
		2 => array(
			 0 => "DICTIONARY",
			 1 => "CODEPAGE",
			 2 => "CATEGORY",
			11 => "SCALE",
			12 => "HEADINGPAIR",
			13 => "DOCPARTS",
			14 => "MANAGER",
			15 => "COMPANY",
			16 => "LINKSDIRTY"
			)
		);
	var $siData = array();

	function __construct(){
		$this->charset = Default_CHARSET;
	}

	function Excel_Peruser(){
		self::__construct();
	}


	/*
	** Set(Get) internal charset, if you use multibyte-code.
	** @input  $chrset: charactor-set name(Ex. SJIS)
	** @output current charector-set name
	** @access public
	*/
	function setInternalCharset($chrset=''){
		if (strlen(trim($chrset)) > 2) {
			$this->charset = $chrset;
		}
		return $this->charset;
	}

	/*
	** read OLE container
	** @input  $Fname:filename
	** @access private
	*/
	function _oleread($Fname){
		if (strlen(dechex(-1)) != 8){
//			return $this->raiseError("ERROR I cannot work on this OS. 32bits-OS only");
		}
		if(!is_readable($Fname)) {
			return $this->raiseError("ERROR Cannot read file ${Fname} \nProbably there is not reading permission whether there is not a file");
		}
		$this->Flag_Magic_Quotes = get_magic_quotes_runtime();
		if ($this->Flag_Magic_Quotes) {
			if (version_compare(PHP_VERSION, '5.3.0', '<')) {
				set_magic_quotes_runtime(false);
			} else {
				//Doesn't exist in PHP 5.4, but we don't need to check because
				//get_magic_quotes_runtime always returns false in 5.4+
				//so it will never get here
				ini_set('magic_quotes_runtime', false);
			}
		}
		$ole_data = @file_get_contents($Fname);
		if ($this->Flag_Magic_Quotes) {
			if (version_compare(PHP_VERSION, '5.3.0', '<')) {
				set_magic_quotes_runtime($this->Flag_Magic_Quotes);
			} else {
				//Doesn't exist in PHP 5.4, but we don't need to check because
				//get_magic_quotes_runtime always returns false in 5.4+
				//so it will never get here
				ini_set('magic_quotes_runtime', $this->Flag_Magic_Quotes);
			}
		}
		if (!$ole_data) { 
			return $this->raiseError("ERROR Cannot open file ${Fname} \n");
		}
		if (substr($ole_data, 0, 8) != pack("CCCCCCCC",0xd0,0xcf,0x11,0xe0,0xa1,0xb1,0x1a,0xe1)) {
			return $this->raiseError("ERROR Template file(${Fname}) is not EXCEL file.\n");
	   	}
		$numDepots = $this->_get4($ole_data, 0x2c);
		$sStartBlk = $this->_get4($ole_data, 0x3c);
		$ExBlock = $this->_get4($ole_data, 0x44);
		$numExBlks = $this->_get4($ole_data, 0x48);

		$len_ole = strlen($ole_data);
		if ($numDepots > ($len_ole / 65536 +1))
			return $this->raiseError("ERROR file($Fname) is broken (numDepots)");
		if ($sStartBlk > ($len_ole / 512 +1))
			return $this->raiseError("ERROR file($Fname) is broken (sStartBlk)");
		if ($ExBlock > ($len_ole / 512 +1))
			return $this->raiseError("ERROR file($Fname) is broken (ExBlock)");
		if ($numExBlks > ($len_ole / 512 +1))
			return $this->raiseError("ERROR file($Fname) is broken (numExBlks)");

		$DepotBlks = array();
		$pos = 0x4c;
		$dBlks = $numDepots;
		if ($numExBlks != 0) $dBlks = (0x200 - 0x4c)/4;
		for ($i = 0; $i < $dBlks; $i++) {
			$DepotBlks[$i] = $this->_get4($ole_data, $pos);
			$pos += 4;
		}

		for ($j = 0; $j < $numExBlks; $j++) {
			$pos = ($ExBlock + 1) * 0x200;
			$ReadBlks = min($numDepots - $dBlks, 0x200 / 4 - 1);
			for ($i = $dBlks; $i < $dBlks + $ReadBlks; $i++) {
				$DepotBlks[$i] = $this->_get4($ole_data, $pos);
				$pos += 4;
			}   
			$dBlks += $ReadBlks;
			if ($dBlks < $numDepots) $ExBlock = $this->_get4($ole_data, $pos);
		}

		$pos = 0;
		$index = 0;
		$BlkChain = array();
		for ($i = 0; $i < $numDepots; $i++) {
			$pos = ($DepotBlks[$i] + 1) * 0x200;
			for ($j = 0 ; $j < 0x200 / 4; $j++) {
				$BlkChain[$index] = $this->_get4($ole_data, $pos);
				$pos += 4 ;
				$index++;
			}
		}
		$eoc=pack("H*","FEFFFFFF");
		$eoc= $this->_get4($eoc,0);
		$pos = 0;
		$index = 0;
		$sBlkChain = array();
		while ($sStartBlk != $eoc) {
			$pos = ($sStartBlk + 1) * 0x200;
			for ($j = 0; $j < 0x80; $j++) {
				$sBlkChain[$index] = $this->_get4($ole_data, $pos);
				$pos += 4 ;
				$index++;
			}
			$chk[$sStartBlk]=true;
			$sStartBlk = $BlkChain[$sStartBlk];
			if(isset($chk[$sStartBlk])){
	return $this->raiseError("Big Block chain for small-block ERROR 1\nTemplate file is broken");
			}
		}
		unset($chk);
		$block = $this->_get4($ole_data, 0x30);
		$pos = 0;
		$entry = '';
		while ($block != $eoc)  {
			$pos = ($block + 1) * 0x200;
			$entry .= substr($ole_data, $pos, 0x200);
			$chk[$block]=true;
			$block = $BlkChain[$block];
			if(isset($chk[$block])){
	return $this->raiseError("Big Block chain for Entry  ERROR 2\nTemplate file is broken");
			}
		}
		unset($chk);
		$offset = 0;
		$sistartBlock=null;
		$dsistartBlock=null;
		$sisize=0;
		$dsisize=0;
		$rootBlock =$this->_get4($entry, 0x74);
		while ($offset < strlen($entry)) {
			  $d = substr($entry, $offset, 0x80);
			  $name = str_replace("\x00", "", substr($d,0,$this->_get2($d,0x40)));
			if (($name == "Workbook") || ($name == "Book")) {
				$wbstartBlock =$this->_get4($d, 0x74);
				$wbsize = $this->_get4($d, 0x78);
//			} else if ($name == "Root Entry" || $name == "R") {
//				$rootBlock =$this->_get4($d, 0x74);
			} else if ($name =="\x05SummaryInformation"){
				$sistartBlock =$this->_get4($d, 0x74);
				$sisize =$this->_get4($d, 0x78);
			} else if ($name =="\x05DocumentSummaryInformation"){
				$dsistartBlock =$this->_get4($d, 0x74);
				$dsisize =$this->_get4($d, 0x78);
			}
			$offset += 0x80;
		}
		if (! isset($rootBlock)) return $this->raiseError("Unknown OLE-type. Or Cannot find Root-Entry.");
			if ($rootBlock != $eoc){
				$pos = 0;
				$rdata = '';
				while ($rootBlock != $eoc)  {
					$pos = ($rootBlock + 1) * 0x200;
					$rdata .= substr($ole_data, $pos, 0x200);
					$chk[$rootBlock]=true;
					$rootBlock = $BlkChain[$rootBlock];
					if(isset($chk[$rootBlock])){
		return $this->raiseError("Root Block chain for RootBook read ERROR 2\n  Template file is broken");
					}
				}
				unset($chk);
			}
		if ($wbsize < 0x1000) {
			$pos = 0;
			$wbData = '';
			$block = $wbstartBlock;
			while ($block != $eoc) {
				$pos = $block * 0x40;
				$wbData .= substr($rdata, $pos, 0x40);
				$chk[$block]=true;
				$block = $sBlkChain[$block];
				if(isset($chk[$block])){
	return $this->raiseError("Big Block chain for Entry  ERROR 2\nTemplate file is broken");
				}
			}
			unset($chk);
			return $wbData;
		} else {
			$numBlocks = $wbsize / 0x200;
			if ($wbsize % 0x200 != 0) $numBlocks++;
			if ($numBlocks == 0) return '';
			$wbData = '';
			$block = $wbstartBlock;
			$pos = 0;
			while ($block != $eoc) {
				$pos = ($block + 1) * 0x200;
				$wbData .= substr($ole_data, $pos, 0x200);
				$chk[$block]=true;
				$block = $BlkChain[$block];
	                	if(isset($chk[$block])){
	return $this->raiseError("Big Block chain ERROR 3\nTemplate file is broken");
				};
			}
//			return $wbData;
//		}
			$this->siData[1] = '';
			$block = $sistartBlock;
			$pos = 0;
		if ($sisize >= 0x1000) {
			while ($block != $eoc) {
				$pos = ($block + 1) * 0x200;
				$this->siData[1] .= substr($ole_data, $pos, 0x200);
				if (strlen($this->siData[1]) > $sisize + 0x200)
	return $this->raiseError("Big Block chain ERROR 4\nTemplate file is broken");
				$block = $BlkChain[$block];
			}
		} else if ($sisize > 0) {
			while ($block != $eoc) {
				$pos = $block * 0x40;
				$this->siData[1] .= substr($rdata, $pos, 0x40);
				if (strlen($this->siData[1]) > 0x1000)
	return $this->raiseError("Big Block chain ERROR 5\nTemplate file is broken");
				$block = $sBlkChain[$block];
			}
		}

		$this->siData[2] = '';
		$block = $dsistartBlock;
		$pos = 0;
		if ($dsisize >= 0x1000) {
			while ($block != $eoc) {
				$pos = ($block + 1) * 0x200;
				$this->siData[2] .= substr($ole_data, $pos, 0x200);
				if (strlen($this->siData[2]) > $dsisize + 0x200)
	return $this->raiseError("Big Block chain ERROR 6\nTemplate file is broken");
				$block = $BlkChain[$block];
			}
		} else if ($dsisize > 0) {
			while ($block != $eoc) {
				$pos = $block * 0x40;
				$this->siData[2] .= substr($rdata, $pos, 0x40);
				if (strlen($this->siData[2]) > 0x1000)
	return $this->raiseError("Big Block chain ERROR 7\nTemplate file is broken");
				$block = $sBlkChain[$block];
			}
		}
                      return $wbData;
                }
	}

	/*
	** parse sheetblock
	** @access private
	*/
	function _parsesheet(&$dat,$sn,$spos){
		$code = 0;
		$version = $this->_get2($dat,$spos + 4);
		$substreamType = $this->_get2($dat,$spos + 6);
		if ($version != Code_BIFF8) {
//			return $this->raiseError("Contents(included sheet) is not BIFF8 format.\n");
		}
		if ($substreamType != Code_Worksheet) {
			return $this->raiseError("Contents is unknown format.\nCan't find Worksheet.\n");
		}
		$tmp='';
		$dimnum=0;
		$bof_num=0;
		$sposlimit=strlen($dat);
		while($code != Type_EOF) {
			if ($spos > $sposlimit) {
				return $this->raiseError("Sheet $sn Read ERROR\nTemplate file is broken.\n");
			}
			$code = $this->_get2($dat,$spos);
			$length = $this->_get2($dat,$spos + 2);
			if ($code == Type_BOF) $bof_num++;
			if ($bof_num > 1){
				$tmp.=substr($dat, $spos, $length+4);
				while($code != Type_EOF) {
					if ($spos > $sposlimit) {
						return $this->raiseError("Parse-Sheet(${sn}) Error\n");
					}
					$spos += $length+4;
					$code = $this->_get2($dat,$spos);
					$length = $this->_get2($dat,$spos + 2);
					$tmp.=substr($dat, $spos, $length+4);
				}
				$bof_num--;
				$spos += $length+4;
				$code = $this->_get2($dat,$spos);
				$length = $this->_get2($dat,$spos + 2);
				$tmp.=substr($dat, $spos, $length+4);
			} else
			switch ($code) {
//-------
					case Type_DEFAULTROWHEIGHT:
						$defheight=$this->_get2($dat,$spos+6)/15;
						$this->rowheight[$sn][-1]=$defheight;
						break;
					case Type_DEFCOLWIDTH:
						$defwidth=$this->_get2($dat,$spos+4);
						$this->colwidth[$sn][-1]=$defwidth * 9;
						break;
					case Type_COLINFO:
						$st=$this->_get2($dat,$spos+4);
						$en=$this->_get2($dat,$spos+6);
						$wd=$this->_get2($dat,$spos+8);
						$defxf=$this->_get2($dat,$spos + 10);
						for ($i=$st;$i<=$en;$i++){
							$this->colwidth[$sn][$i]=$wd/32;
							$this->coldefxf[$sn][$i]=$defxf;
						}
						break;
					case Type_HEADER:
						if ($this->biff_ver != Code_BIFF8) {
						$this->headfoot[$sn]['header']=$this->getstring5(substr($dat, $spos+4, $length),1);
						} else
						$this->headfoot[$sn]['header']=$this->getstring(substr($dat, $spos+4, $length),2);
						break;
					case Type_FOOTER:
						if ($this->biff_ver != Code_BIFF8) {
						$this->headfoot[$sn]['footer']=$this->getstring5(substr($dat, $spos+4, $length),1);
						} else
						$this->headfoot[$sn]['footer']=$this->getstring(substr($dat, $spos+4, $length),2);
						break;
				case Type_MERGEDCELLS:
					$numrange=$this->_get2($dat,$spos+4);
					for($i=0;$i<$numrange;$i++){
						$rows=$this->_get2($dat,$spos+6+$i*8);
						$rowe=$this->_get2($dat,$spos+8+$i*8);
						$cols=$this->_get2($dat,$spos+10+$i*8);
						$cole=$this->_get2($dat,$spos+12+$i*8);
						for($r=$rows;$r<=$rowe;$r++)
						for($c=$cols;$c<=$cole;$c++){
							$this->celmergeinfo[$sn][$r][$c]['cond']=-1;
						}
						$this->celmergeinfo[$sn][$rows][$cols]['cond']=1;
						$this->celmergeinfo[$sn][$rows][$cols]['rspan']=$rowe-$rows+1;
						$this->celmergeinfo[$sn][$rows][$cols]['cspan']=$cole-$cols+1;
					}
					break;
				case Type_HLINK:
					$hlrow = $this->_get2($dat,$spos+4);
					$hlcol = $this->_get2($dat,$spos+8);
					$hlopt = $this->_get2($dat,$spos+32);
					if(($hlopt & 0x1)==0x1){
						$sspos = ($hlopt & 0x14) ? $this->_get2($dat,$spos+36) * 2 + 4: 0;
						$hlnum = $this->_get4($dat,$spos+52+$sspos)-1;
						if(52+$sspos+$hlnum > $length) break;
						$hypl = mb_convert_encoding(substr($dat,$spos+56+$sspos,$hlnum),$this->charset,'UTF-16LE');
						$hypl = preg_replace('/\x00.*/','',$hypl);
						$this->hlink[$sn][$hlrow][$hlcol] = $hypl;
					}
					break;
//---------

				case Type_HEADER:
					$tmp.=substr($dat, $spos, $length+4);
					break;
				case Type_FOOTER:
					$tmp.=substr($dat, $spos, $length+4);
					break;
				case Type_DIMENSION:
					$tmp.=substr($dat, $spos, $length+4);
	if ($dimnum==0){
					$this->sheetbin[$sn]['preCB']=$tmp;
					$tmp='';
	}
	$dimnum++;
					break;
				case Type_ROW:
					$row=$this->_get2($dat,$spos + 4);
					$this->rowblock[$sn][$row]['col1st']=$this->_get2($dat,$spos + 6);
					$this->rowblock[$sn][$row]['collast']=$this->_get2($dat,$spos + 8);
					$this->rowblock[$sn][$row]['rowfoot']=substr($dat, $spos+10, 10);
					if ($this->_get1($dat,$spos + 16) & 0x80) {
						$this->rowdefxf[$sn][$row]=$this->_get2($dat,$spos + 18) & 0x0FFF;
					}
					break;
				case Type_RK2:
				case Type_LABEL:
				case Type_LABELSST:
				case Type_NUMBER:
				case Type_FORMULA2:
				case Type_BOOLERR:
				case Type_BLANK:
					$row=$this->_get2($dat,$spos + 4);
					$col=$this->_get2($dat,$spos + 6);
					$this->cellblock[$sn][$row][$col]['xf']=$this->_get2($dat,$spos + 8);
					$this->cellblock[$sn][$row][$col]['type']=$code;
					$this->cellblock[$sn][$row][$col]['dat']=substr($dat, $spos+10, $length-6);
					$this->cellblock[$sn][$row][$col]['string']='';
	if ($code == Type_FORMULA2){
		$dispnum = substr($dat, $spos+10, 8);
		$opflag = $this->_get2($dat,$spos + 18) | 0x02; // Calculate on open
		$tokens = substr($dat, $spos+20, $length - 16);
		$this->cellblock[$sn][$row][$col]['dat']=$dispnum . pack("v",$opflag) . $tokens;
		if ($this->_get2($dat,$spos + $length + 4) == Type_SharedFormula){
			$spos += $length + 4;
			$length = $this->_get2($dat,$spos + 2);
			$this->cellblock[$sn][$row][$col]['sharedform']=substr($dat,$spos,$length+4);
		}
		if ($this->_get2($dat,$spos + $length + 4) == Type_STRING){
			$spos += $length + 4;
			$length = $this->_get2($dat,$spos + 2);
			$this->cellblock[$sn][$row][$col]['string']=substr($dat,$spos,$length+4);
		}
	}
	
					break;
				case Type_MULBLANK:
					$muln=($length-6)/2;
					$row=$this->_get2($dat,$spos + 4);
					$col=$this->_get2($dat,$spos + 6);
					$i=-1;
					while(++$i < $muln){
						$this->cellblock[$sn][$row][$i+$col]['xf']=$this->_get2($dat,$spos+8+$i*2);
						$this->cellblock[$sn][$row][$i+$col]['type']=Type_BLANK;
						$this->cellblock[$sn][$row][$i+$col]['dat']='';
					}
					break;
				case Type_MULRK:
					$muln=($length-6)/6;
					$row=$this->_get2($dat,$spos + 4);
					$col=$this->_get2($dat,$spos + 6);
					$i=-1;
					while(++$i < $muln){
						$this->cellblock[$sn][$row][$i+$col]['xf']=$this->_get2($dat,$spos+8+$i*6);
						$this->cellblock[$sn][$row][$i+$col]['type']=Type_RK;
						$this->cellblock[$sn][$row][$i+$col]['dat']=substr($dat, $spos+10+$i*6, 4);
					}
					break;
				case Type_DBCELL:
					break;
				case Type_BUTTONPROPERTYSET:
					break;
				case Type_EOF:
					break;
				default:
					$tmp.= substr($dat, $spos, $length+4);
			}
			$spos += $length+4;
		}
		$this->sheetbin[$sn]['tail']=$tmp;
	}

	/*
	** parse sst-record
	** @access private
	*/
	function _parsesst(&$dat, $pos, $length) {
		$sstarray=array();
		$numref=$this->_get4($dat,$pos+8);
		$sspos =12;
		$sstnum=0;
		$limit=$pos + $length +4;
		while ($sstnum < $numref) {
			if ($pos+$sspos+2 > $limit) {
				if ($this->_get2($dat,$limit) == Type_CONTINUE) {
					$pos = $limit;
					$length = $this->_get2($dat,$pos + 2);
					$limit += $length + 4;
					$sspos = 4;
				} else break;
			}
			$slen=$this->_get2($dat,$pos+$sspos);
			$tempsst['len']=$slen;
			$opt=$this->_get1($dat,$pos+$sspos+2);
			$sspos += 3;
			if ($opt & 0x01) $slen *=2;
			if ($opt & 0x04) $optlen =4; else $optlen =0;
			if ($opt & 0x08) {
				$optlen +=2;
				$rtnum = $this->_get2($dat,$pos+$sspos);
				if ($opt & 0x04) $apnum = $this->_get4($dat,$pos+$sspos+2);
				else $apnum = 0;
			} else {
				$rtnum = 0;
				if ($opt & 0x04) $apnum = $this->_get4($dat,$pos+$sspos);
				else $apnum = 0;
			}
			$tempsst['opt']=$opt;
			$tempsst['rtn']=$rtnum;
			$tempsst['apn']=$apnum;
			$sspos += $optlen;
			if ($pos+$sspos+$slen > $limit) {
				$fusoku=($pos+$sspos+$slen)-$limit;
				$slen -= $fusoku;
				$sststr=$this->_to_utf16(substr($dat,$pos+$sspos,$slen),$opt);
				if ($opt & 0x01) $fusoku /=2;
				while ($fusoku >0 ) {
					if ($this->_get2($dat,$pos + $length + 4) == Type_CONTINUE) {
						$pos += $length +4;
						$length = $this->_get2($dat,$pos + 2);
						$opt = $this->_get1($dat,$pos + 4);
						$limit = $pos + $length + 4;
						$sspos = 5;
						if ($opt == 1) $fusoku *= 2;
						if ($pos + $sspos + $fusoku > $limit) {
							$fusoku = ($pos + $sspos+ $fusoku) - $limit;
							$sststr.=$this->_to_utf16(substr($dat,$pos + $sspos,$limit-($pos + $sspos)),$opt);
							if ($opt & 0x01) $fusoku /=2;
						} else {
							$sststr.=$this->_to_utf16(substr($dat,$pos + $sspos,$fusoku),$opt);
							$sspos += $fusoku;
							$fusoku=0;
						}
					} else break 2;
				}
			} else {
				$sststr=$this->_to_utf16(substr($dat,$pos+$sspos,$slen),$opt);
				$sspos += $slen;
			}
			if ($rtnum) {
				if ($pos+$sspos+4*$rtnum > $limit) {
					$fusoku=($pos+$sspos+4*$rtnum)-$limit;
					$rt=substr($dat,$pos+$sspos,4*$rtnum - $fusoku);
					if ($this->_get2($dat,$pos + $length + 4) == Type_CONTINUE) {
						$pos += $length + 4;
						$length =$this->_get2($dat,$pos + 2);
						$limit = $pos + $length + 4;
						$sspos = 4;
						$rt.=substr($dat,$limit + $sspos, $fusoku);
						$sspos += $fusoku;
					} else break;
				} else {
					$rt=substr($dat,$pos+$sspos,4*$rtnum);
					$sspos +=4*$rtnum;
				}
			} else $rt="";
			if ($apnum) {
				if ($pos+$sspos+$apnum > $limit) {
					$fusoku=$pos+$sspos+$apnum-$limit;
					$ap=substr($dat,$pos+$sspos,$apnum-$fusoku);
					if ($this->_get2($dat,$limit) == Type_CONTINUE) {
						$pos = $limit;
						$length = $this->_get2($dat,$pos + 2);
						$limit += $length + 4;
						$sspos = 4;
						$ap.=substr($dat,$pos + $sspos, $fusoku);
						$sspos += $fusoku;
					} else break;
				} else {
						$ap=substr($dat,$pos+$sspos,$apnum);
						$sspos +=$apnum;}
			} else $ap="";
			$sstarray[$sstnum]['str']=$sststr;
			$sstnum++;
		}
		return $sstarray;
	}

	/*
	** convert charset to UTF16
	** @input $str:string,$opt:0=ascii,1=UTF-16
	** @output UTF16 string
	** @access private
	*/
	function _to_utf16(&$str,$opt=0)
	{
		return ($opt & 0x01) ? $str : mb_convert_encoding($str, "UTF-16LE", "ASCII");
	}

	/*
	** convert 1,2,4 bytes string to number
	** @input $d:string,$p:position
	** @output number
	** @access private
	*/
	function _get4(&$d, $p) {
		$x = ord($d[$p]) | (ord($d[$p+1]) << 8) |
			(ord($d[$p+2]) << 16) | (ord($d[$p+3]) << 24);
		if ($x > 0x7FFFFFFF) return $x - 0x100000000;
		else return $x;
	}
	function _get2(&$d, $p) {
		return ord($d[$p]) | (ord($d[$p+1]) << 8);
	}
	function _get1(&$d, $p) {
		return ord($d[$p]);
	}

	/*
	** Parse Excel file
	** @input  $filename:full path for OLE file
	** @access private
	*/
	function parseFile($filename, $mode=null){
		if ($mode==2) $dat=$filename; else
		$dat = $this->_oleread($filename);
		if ($this->isError($dat)) return $dat;
		if (strlen($dat) < 256) {
			return $this->raiseError("Contents is too small (".strlen($dat).")\nProbably template file is not right Excel file.\n");
		}
		$presheet=1;
		$pos = 0;
		$version = $this->_get2($dat,$pos + 4);
		$substreamType = $this->_get2($dat,$pos + 6);
		if ($version != Code_BIFF8) {
			$this->biff_ver=$version;
		}
		if ($substreamType != Code_WorkbookGlobals) {
			return $this->raiseError("Contents is unknown format.\nCan't find WorkbookGlobal.");
		}
		$code=-1;
		$poslimit=strlen($dat);
		while ($code != Type_EOF){
			if ($pos > $poslimit){
				return $this->raiseError("Global Area Read Error\nTemplate file is broken");
			}
		    $code = $this->_get2($dat,$pos);
		    $length = $this->_get2($dat,$pos+2);
		    switch ($code) {
				case Type_FONT:
					$font['height']=$this->_get2($dat,$pos+4);
					$font['style']=$this->_get2($dat,$pos+6);
					$font['color']= $this->_get2($dat,$pos+8);
					$font['weight']=$this->_get2($dat,$pos+10);
					$font['escapement']=$this->_get2($dat,$pos+12);
					$font['underline']=$this->_get1($dat,$pos+14);
					$font['family']=$this->_get1($dat,$pos+15);
					$font['charset']=$this->_get1($dat,$pos+16);
					if ($this->biff_ver == Code_BIFF8)
					$font['fontname']=$this->getstring(substr($dat,$pos+18,$length-14),1);
					else
					$font['fontname']=$this->getstring5(substr($dat,$pos+18,$length-14),1);
					$this->recFONT[]=$font;
					unset($font);
					break;
				case Type_FORMAT:
				if ($this->biff_ver != Code_BIFF8) {
					$fmt=$this->getstring5(substr($dat,$pos+6,$length-2),1);
				} else {
					$fmt=$this->getstring(substr($dat,$pos+6,$length-2),2);
				}
					$this->recFORMAT[$this->_get2($dat,$pos+4)]=$fmt;
					break;
				case Type_XF:
				if ($this->biff_ver != Code_BIFF8) {
					$xf['attrib']=($this->_get1($dat,$pos+11) & 0xfc) >> 2;
					$xf['stylexf']=($this->_get1($dat,$pos+8) & 0x4) >> 2;
					$oya=($this->_get2($dat,$pos+8) & 0xfff0) >> 4;
					if ($oya != 0xfff) $xf['parent']=$oya;
					$cond = $xf['stylexf'] ? ~$xf['attrib'] : $xf['attrib'];
		//$cond=0xFF;
					if ($cond & 0x2){
						if ($this->_get2($dat,$pos+4) >4) {
						$xf['fontindex']=$this->_get2($dat,$pos+4)-1;
						} else {
						$xf['fontindex']=$this->_get2($dat,$pos+4);
						}
					} else $xf['fontindex']=0;
					if ($cond & 0x1)
						$xf['formindex']=$this->_get2($dat,$pos+6);
					if ($cond & 0x4){
						$xf['halign']=$this->_get1($dat,$pos+10) & 0x7;
						$xf['wrap']=($this->_get1($dat,$pos+10) & 0x8) >> 3;
						$xf['valign']=($this->_get1($dat,$pos+10) & 0x70)>> 4;
						$r=$this->_get1($dat,$pos+11) & 0x03;
						if ($r==0) $xf['rotation']=0;
						else if ($r==1) $xf['rotation']=255;
						else if ($r==2) $xf['rotation']=90;
						else if ($r==3) $xf['rotation']=180;
					}
					if ($cond & 0x8){
						$xf['Lstyle']=($this->_get1($dat,$pos+16) & 0x38) >> 3;
						$xf['Rstyle']=($this->_get2($dat,$pos+16) & 0x1c0) >> 6;
						$xf['Tstyle']=$this->_get1($dat,$pos+16) & 0x07;
						$xf['Bstyle']=($this->_get2($dat,$pos+14) & 0x1c0) >> 6;
						$xf['Lcolor']=$this->_get1($dat,$pos+18) & 0x7f;
						$xf['Rcolor']=($this->_get2($dat,$pos+18) & 0x3f80) >> 7;
//						$xf['diagonalL2R']=0;
//						$xf['diagonalR2L']=0;
						$xf['Tcolor']=($this->_get1($dat,$pos+17) & 0xfe) >> 1;
						$xf['Bcolor']=($this->_get1($dat,$pos+15) & 0xfe) >> 1;
//						$xf['Dcolor']=0;
//						$xf['Dstyle']=0;
					}
					if ($cond & 0x10){
						$xf['fillpattern']=$this->_get1($dat,$pos+14) & 0x3f;
						$xf['PtnFRcolor']=$this->_get1($dat,$pos+12) & 0x7f;
						$xf['PtnBGcolor']=($this->_get2($dat,$pos+12)>> 7) & 0x7f;
					}
				} else {
					$xf['attrib']=($this->_get1($dat,$pos+13) & 0xfc) >> 2;
					$xf['stylexf']=($this->_get1($dat,$pos+8) & 0x4) >> 2;
					$oya=($this->_get2($dat,$pos+8) & 0xfff0) >> 4;
					if ($oya != 0xfff) $xf['parent']=$oya;
					$cond = $xf['stylexf'] ? ~$xf['attrib'] : $xf['attrib'];
		//$cond=0xFF;
					if ($cond & 0x2){
						if ($this->_get2($dat,$pos+4) >4) {
						$xf['fontindex']=$this->_get2($dat,$pos+4)-1;
						} else {
						$xf['fontindex']=$this->_get2($dat,$pos+4);
						}
					} else $xf['fontindex']=0;
					if ($cond & 0x1)
						$xf['formindex']=$this->_get2($dat,$pos+6);
					if ($cond & 0x4){
						$xf['halign']=$this->_get1($dat,$pos+10) & 0x7;
						$xf['wrap']=($this->_get1($dat,$pos+10) & 0x8) >> 3;
						$xf['valign']=($this->_get1($dat,$pos+10) & 0x70)>> 4;
						$xf['rotation']=$this->_get1($dat,$pos+11);
					}
					if ($cond & 0x8){
						$xf['Lstyle']=$this->_get1($dat,$pos+14) & 0x0f;
						$xf['Rstyle']=($this->_get1($dat,$pos+14) & 0xf0) >> 4;
						$xf['Tstyle']=$this->_get1($dat,$pos+15) & 0x0f;
						$xf['Bstyle']=($this->_get1($dat,$pos+15) & 0xf0) >> 4;
						$xf['Lcolor']=$this->_get1($dat,$pos+16) & 0x7f;
						$xf['Rcolor']=($this->_get2($dat,$pos+16) & 0x3f80) >> 7;
						$xf['diagonalL2R']=($this->_get1($dat,$pos+17) & 0x40) >> 6;
						$xf['diagonalR2L']=($this->_get1($dat,$pos+17) & 0x80) >> 7;
						$xf['Tcolor']=$this->_get1($dat,$pos+18) & 0x7f;
						$xf['Bcolor']=($this->_get2($dat,$pos+18) & 0x3f80) >> 7;
						$xf['Dcolor']=($this->_get4($dat,$pos+18) & 0x1fc000) >> 14;
						$xf['Dstyle']=($this->_get2($dat,$pos+20) & 0x1e0) >> 5;
					}
					if ($cond & 0x10){
						$xf['fillpattern']=($this->_get4($dat,$pos+21) & 0xfc) >> 2;
						$xf['PtnFRcolor']=$this->_get1($dat,$pos+22) & 0x7f;
						$xf['PtnBGcolor']=($this->_get2($dat,$pos+22)>> 7) & 0x7f;
					}
				}
					$this->recXF[]=$xf;
					unset($xf);
					break;
			case Type_FILEPASS:
				return $this->raiseError("Cannot read contents. \nThis file is protected.");
				break;
			case Type_SST:
				$this->globaldat['presst']=$this->wbdat;
				$this->wbdat='';
				$this->eachsst = $this->_parsesst($dat, $pos, $length);
				while ($this->_get2($dat,$pos + $length + 4) == Type_CONTINUE){
					$pos += $length + 4;
					$length = $this->_get2($dat,$pos+2);
				}
			    break;
			case Type_EXTSST:
				$exsstbin = '';
				break;
			case Type_OBJPROJ:
			case Type_BUTTONPROPERTYSET:
				break;
			case Type_BOUNDSHEET:
				if ($presheet) {
					$this->globaldat['presheet']=$this->wbdat;
					$this->wbdat='';
					$presheet=0;
				}
				$rec_offset = $this->_get4($dat, $pos+4);
			    $sheetno['code'] = substr($dat, $pos, 2);
			    $sheetno['length'] = substr($dat, $pos+2, 2);
			    $sheetno['offsetbin'] = substr($dat, $pos+4, 4);
			    $sheetno['offset'] = $rec_offset;
			    $sheetno['visible'] = substr($dat, $pos+8, 1);
			    $sheetno['type'] = substr($dat, $pos+9, 1);
			    $sheetno['name'] = substr($dat, $pos+10, $length-6);
			    $this->boundsheets[] = $sheetno;
			    break;
			case Type_SUPBOOK:
				$this->globaldat['presup']=$this->wbdat;
				$this->wbdat='';
				$this->globaldat['supbook']=substr($dat, $pos, $length+4);
				if ($this->_get2($dat, $pos+$length+4)==Type_EXTERNSHEET){
					$pos +=$length+4;
					$length = $this->_get2($dat,$pos+2);
					$this->globaldat['extsheet']=substr($dat, $pos, $length+4);
					$this->globaldat['name']='';
					while($this->_get2($dat, $pos+$length+4)==Type_NAME){
						$pos +=$length+4;
						$length = $this->_get2($dat,$pos+2);
						if ($this->_get2($dat,$pos+12)==0){
							$this->globaldat['name'].=substr($dat, $pos, $length+4);
						} else {
							$this->boundsheets[$this->_get2($dat,$pos+12)-1]['namerecord']=substr($dat, $pos, $length+4);
						}
					}
				}
			    break;
			case Type_EOF:
				$this->globaldat['last']= $this->wbdat . substr($dat, $pos, $length+4);
			    break;
			default:
				$this->wbdat .= substr($dat, $pos, $length+4);
			}
			$pos += $length + 4;
		}
		foreach ($this->boundsheets as $key=>$val){
		    $resultex=$this->_parsesheet($dat,$key,$val['offset']);
			if ($this->isError($resultex)) return $resultex;
		}
	}

	function fileread($filename){
		error_reporting(E_ALL ^ E_NOTICE);
		mb_regex_encoding($this->charset);
		$this->setconst();
		$this->infilename=$filename;
		$this->oledat = $this->_oleread($filename);
		if ($this->isError($this->oledat)) return $this->oledat;
		$resultex=$this->parseFile($this->oledat,2);
		if ($this->isError($resultex)) return $resultex;
		$this->getheightwidth();
		foreach($this->cellblock as $keysheet=>$sheet){
			$maxcol=0;
			foreach($sheet as $keyrow=>$rows){
				foreach($rows as $keycol=>$cell){
					if ($maxcol < $keycol) $maxcol=$keycol;
				}
			}
			$this->maxcell[$keysheet]=$maxcol;
			$this->maxrow[$keysheet]=$keyrow;
		}
		$this->sheetnum=count($this->boundsheets);
		foreach($this->boundsheets as $keysheet=>$sheet){
		  if ($this->biff_ver != Code_BIFF8){
			$name =mb_convert_encoding(substr($sheet['name'],1),$this->charset,'SJIS-WIN');
		  } else {
			$name = substr($sheet['name'],2);
			if (substr($sheet['name'],1,1)!="\x00") {
				$name = mb_convert_encoding($name,$this->charset,'UTF-16LE');
			}
		  }
			$this->sheetname[$keysheet]=$name;
		}
	}

	function getHEADER($sn){
		if (strlen(trim($this->headfoot[$sn]['header'])))
			return $this->sepheadfoot($this->headfoot[$sn]['header'],$sn);
		else
			return NULL;
	}

	function getFOOTER($sn){
		if (strlen(trim($this->headfoot[$sn]['footer'])))
			return $this->sepheadfoot($this->headfoot[$sn]['footer'],$sn);
		else
			return NULL;
	}

	function getColWidth($sn,$col){
		return (isset($this->colwidth[$sn][$col]))? $this->colwidth[$sn][$col]:$this->colwidth[$sn][-1];
	}

	function getRowHeight($sn,$row){
		return (isset($this->rowheight[$sn][$row]))? $this->rowheight[$sn][$row]:$this->rowheight[$sn][-1];
	}

	function getAttribute($sn,$row,$col){
		$tmp=array();
		if (isset($this->cellblock[$sn][$row][$col]['xf'])){
			$xfno=$this->cellblock[$sn][$row][$col]['xf'];
			$dt=$this->cellblock[$sn][$row][$col]['type'];
		} else {$xfno = $this->_getdefxf($sn,$row,$col); $dt = 0;}
		if ($xfno !== null) {
			$tmp=$this->recXF[$xfno];
			$tmp['type']=$dt;
			$tmp['xf']=$xfno;
			if (isset($this->recXF[$xfno]['formindex']))
			if (isset($this->recFORMAT[$this->recXF[$xfno]['formindex']]))
			$tmp['format']=$this->recFORMAT[$this->recXF[$xfno]['formindex']];
			$tmp['font']=$this->recFONT[$this->recXF[$xfno]['fontindex']];
			return $tmp;
		} else return null;
	}

	function getstring(&$chars,$len){
		if (strlen($chars)<1) return '';
		if ($len==1) {
			$strpos=2;
			$opt=$this->_get1($chars,1);
		} elseif ($len==2){
			$strpos=3;
			$opt=$this->_get1($chars,2);
		} else return substr($chars,2);
		if ($opt)
			return mb_convert_encoding(substr($chars,$strpos),$this->charset,'UTF-16LE');
		else
			return substr($chars,$strpos);
	}

	function getstring5(&$chars,$len){
		return mb_convert_encoding(substr($chars,$len),$this->charset,'SJIS-WIN');
	}

	function getstyle($num){
		switch ($num) {
			case 0: $style='none;';	break;
			case 1: $style='solid;'; break;
			case 2: $style='solid;'; break;
			case 3: $style='dashed;'; break;
			case 4: $style='dotted;'; break;
			case 5: $style='solid;'; break;
			case 6: $style='double;'; break;
			case 7: $style='solid;'; break;
			case 8: $style='dashed;'; break;
			case 9: $style='dashed;'; break;
			case 10: $style='dashed;'; break;
			case 11: $style='dashed;'; break;
			case 12: $style='dashed;'; break;
			case 13: $style='dashed;'; break;
		}
		return $style;
	}

	function getwidth($num){
		switch ($num) {
			case 0: $style='0px;'; break;
			case 1: $style='thin;'; break;
			case 2: $style='medium;'; break;
			case 3: $style='thin;'; break;
			case 4: $style='thin;'; break;
			case 5: $style='thick;'; break;
			case 6: $style='3px;'; break;
			case 7: $style='1px;'; break;
			case 8: $style='medium;'; break;
			case 9: $style='thin;'; break;
			case 10: $style='medium;'; break;
			case 11: $style='thin;'; break;
			case 12: $style='medium;'; break;
			case 13: $style='medium;'; break;
		}
		if ($style=='thin;') $style='1px;';
		if ($style=='medium;') $style='2px;';
		if ($style=='thick;') $style='3px;';
		return $style;
	}

	function setconst(){
		$this->recFORMAT[0]='';
		$this->recFORMAT[1]='0';
		$this->recFORMAT[2]='0.00';
		$this->recFORMAT[3]='#,##0';
		$this->recFORMAT[4]='#,##0.00';
		$this->recFORMAT[5]='"$"#,##0_);("$"#,##0)';
		$this->recFORMAT[6]='"$"#,##0_);[Red]("$"#,##0)';
		$this->recFORMAT[7]='"$"#,##0.00_);("$"#,##0.00)';
		$this->recFORMAT[8]='"$"#,##0.00_);[Red]("$"#,##0.00)';
		$this->recFORMAT[9]='0%';
		$this->recFORMAT[10]='0.00%';
		$this->recFORMAT[11]='0.00E+00';
		//$this->recFORMAT[12]='# ?/?';
		//$this->recFORMAT[13]='# ??/??';
		//$this->recFORMAT[14]='M/D/YY';
		$this->recFORMAT[14]='YYYY/M/D';
		$this->recFORMAT[15]='D-MMM-YY';
		$this->recFORMAT[16]='D-MMM';
		$this->recFORMAT[17]='MMM-YY 49 Text @';
		$this->recFORMAT[18]='h:mm AM/PM';
		$this->recFORMAT[19]='h:mm:ss AM/PM';
		$this->recFORMAT[20]='h:mm';
		$this->recFORMAT[21]='h:mm:ss';
		$this->recFORMAT[22]='M/D/YY h:mm';
		$this->recFORMAT[37]='_(#,##0_);(#,##0)';
		$this->recFORMAT[38]='_(#,##0_);[Red](#,##0)';
		$this->recFORMAT[39]='_(#,##0.00_);(#,##0.00)';
		$this->recFORMAT[40]='_(#,##0.00_);[Red](#,##0.00)';
		$this->recFORMAT[41]='_("$"* #,##0_);_("$"* (#,##0);_("$"* "-"_);_(@_)';
		$this->recFORMAT[42]='_(* #,##0_);_(* (#,##0);_(* "-"_);_(@_)';
		$this->recFORMAT[43]='_("$"* #,##0.00_);_("$"* (#,##0.00);_("$"* "-"??_);_(@_)';
		$this->recFORMAT[44]='_(* #,##0.00_);_(* (#,##0.00);_(* "-"??_);_(@_)';
		$this->recFORMAT[45]='mm:ss';
		$this->recFORMAT[46]='[h]:mm:ss';
		$this->recFORMAT[47]='mm:ss.0';
		$this->recFORMAT[48]='##0.0E+0';
		$this->recFORMAT[49]='@';
		$this->recFORMAT[50]='[$-0411]GE.M.D';
		$this->recFORMAT[51]=mb_convert_encoding('[$-0411]GGGE年M月D日',$this->charset,'EUC-JP');
		$this->recFORMAT[52]=mb_convert_encoding('[$-0411]YYYY年M月',$this->charset,'EUC-JP');
		$this->recFORMAT[53]=mb_convert_encoding('[$-0411]M月D日',$this->charset,'EUC-JP');
		$this->recFORMAT[54]=mb_convert_encoding('[$-0411]GGGE年M月D日',$this->charset,'EUC-JP');
		$this->recFORMAT[55]=mb_convert_encoding('[$-0411]YYYY年M月',$this->charset,'EUC-JP');
		$this->recFORMAT[56]=mb_convert_encoding('[$-0411]M月D日',$this->charset,'EUC-JP');
		$this->recFORMAT[57]='[$-0411]GE.M.D';
		$this->recFORMAT[58]=mb_convert_encoding('[$-0411]GGGE年M月D日',$this->charset,'EUC-JP');
	}

	function fontdeco($opt){
		if ($opt & 0x04) $tmp =' underline'; else $tmp='';
		if ($opt & 0x08) $tmp .=' line-through';
		return $tmp;
	}

	function getCssBR($val, $key=-1) {
		// XXX
		if (defined('border_sw')) {
			$border_sw = 1;
		} else {
			$border_sw = 0;
		}
		$tmp='';

		if (isset($val['Bstyle'])){
			if ($val['Bstyle']!=0){
				$tmp.='border-bottom-width: '. $this->getwidth($val['Bstyle']) . ";\n";
				$tmp.='border-bottom-style: '. $this->getstyle($val['Bstyle']) . ";\n";
			} else {
				$tmp .= "border-bottom-width: 1px;\n";
				$tmp .= "border-bottom-style: dotted;\n";
			}
		}
		if (isset($val['Bcolor'])){
			if ($val['Bcolor']!=0 && $val['Bcolor']<56){
				$tmp.='border-bottom-color: #'. $this->palette[$val['Bcolor']] . ";\n";
			} else {
				$tmp.='border-bottom-color: #'. $this->palette[0] . ";\n";
			}
		}

		// XXX
		if (!isset($val['Bstyle']) && !isset($val['Bcolor'])) {

			if ($border_sw) {
				$tmp .= "border-bottom-width: 1px;\n";
				$tmp .= "border-bottom-style: solid;\n";
				$tmp .= "border-bottom-color: #ffffff;\n";
			} else {
				$tmp .= "border-bottom-width: 1px;\n";
				$tmp .= "border-bottom-style: dotted;\n";
				$tmp .= "border-bottom-color: #000000;\n";
			}
		}

		if (isset($val['Rstyle'])){
			if ($val['Rstyle']!=0){
				$tmp.='border-right-width: '. $this->getwidth($val['Rstyle']) . ";\n";
				$tmp.='border-right-style: '. $this->getstyle($val['Rstyle']) . ";\n";
			} else {
				$tmp .= "border-right-width: 1px;\n";
				$tmp .= "border-right-style: dotted;\n";
			}
		}
		if (isset($val['Rcolor'])){
			if ($val['Rcolor']!=0 && $val['Rcolor']<56){
				$tmp.='border-right-color: #'. $this->palette[$val['Rcolor']] . ";\n";
			} else { 
				$tmp.='border-right-color: #'. $this->palette[0] . ";\n";
			}
		}

		// XXX
		if (!isset($val['Rstyle']) && !isset($val['Rcolor'])) {
			if ($border_sw) {
				$tmp .= "border-right-width: 1px;\n";
				$tmp .= "border-right-style: solid;\n";
				$tmp .= "border-right-color: #ffffff;\n";
			} else {
				$tmp .= "border-right-width: 1px;\n";
				$tmp .= "border-right-style: dotted;\n";
				$tmp .= "border-right-color: #000000;\n";
			}
		}

		return $tmp;
	}

	function getCssTL($val,$key=-1){
		// XXX
		if (defined('border_sw')) {
			$border_sw = 1;
		} else {
			$border_sw = 0;
		}
		$tmp='';

		if (isset($val['Tstyle'])){
			if ($val['Tstyle']!=0){
				$tmp.='border-top-width: '. $this->getwidth($val['Tstyle']) . ";\n";
				$tmp.='border-top-style: '. $this->getstyle($val['Tstyle']) . ";\n";
			} else {
				$tmp .= "border-top-width: 1px;\n";
				$tmp .= "border-top-style: dotted;\n";
			}
		}
		if (isset($val['Tcolor'])){

			if ($val['Tcolor']!=0 && $val['Tcolor']<56){
				$tmp.='border-top-color: #'. $this->palette[$val['Tcolor']] . ";\n";
			} else {
				$tmp.='border-top-color: #'. $this->palette[0] . ";\n";
			}
		}

		// XXX
		if (!isset($val['Tstyle']) && !isset($val['Tcolor'])) {
			if ($border_sw) {
				$tmp .= "border-top-width: 1px;\n";
				$tmp .= "border-top-style: solid;\n";
				$tmp .= "border-top-color: #ffffff;\n";
			} else {
				$tmp .= "border-top-width: 1px;\n";
				$tmp .= "border-top-style: dotted;\n";
				$tmp .= "border-top-color: #000000;\n";
			}
		}

		if (isset($val['Lstyle'])){
			if ($val['Lstyle']!=0){
				$tmp.='border-left-width: '. $this->getwidth($val['Lstyle']) . ";\n";
				$tmp.='border-left-style: '. $this->getstyle($val['Lstyle']) . ";\n";
			} else {
				$tmp .= "border-left-width: 1px;\n";
				$tmp .= "border-left-style: dotted;\n";
			}
		}
		if (isset($val['Lcolor'])){
			if ($val['Lcolor']!=0 && $val['Lcolor']<56){
				$tmp.='border-left-color: #'. $this->palette[$val['Lcolor']] . ";\n";
			} else {
				$tmp.='border-left-color: #'. $this->palette[0] . ";\n";
			}
		}

		// XXX
		if (!isset($val['Lstyle']) && !isset($val['Lcolor'])) {
			if ($border_sw) {
				$tmp .= "border-left-width: 1px;\n";
				$tmp .= "border-left-style: solid;\n";
				$tmp .= "border-left-color: #ffffff;\n";
			} else {
				$tmp .= "border-left-width: 1px;\n";
				$tmp .= "border-left-style: dotted;\n";
				$tmp .= "border-left-color: #000000;\n";
			}
		}

		$ftmp='';
		if ($key==0) $val['fontindex']=0;
		if (isset($val['fontindex'])){
			if ($val['fontindex']>=0){
				if ($this->recFONT[$val['fontindex']]['color'] < 56)
					$tmp.='color: #'. $this->palette[$this->recFONT[$val['fontindex']]['color']] . ";\n";
				if($this->recFONT[$val['fontindex']]['style'] & 0x2) $ftmp.=' italic';
				if($this->recFONT[$val['fontindex']]['style'] & 0x1) $ftmp.=' bold';
				$ftmp.=' '.($this->recFONT[$val['fontindex']]['height']/Const_fontH)."px";
				$ftmp.=' "'.$this->recFONT[$val['fontindex']]['fontname'].'"';
				$tmp.='font: '. $ftmp . ";\n";
				if($this->recFONT[$val['fontindex']]['style'] & 0xc)
					$tmp.='text-decoration:'.$this->fontdeco($this->recFONT[$val['fontindex']]['style']) . ";\n";
			}
		} else $tmp.='font: '.($this->recFONT[0]['height']/Const_fontH)."px".";\n";

		// XXX
		if (!$border_sw && isset($val['fillpattern'])){
			if($val['fillpattern']==1){
				$tmp.='background-color: #'. $this->palette[$val['PtnFRcolor']] . ";\n";
			} elseif ($val['fillpattern'] <=18 && $val['fillpattern'] >1){
				$tmp .='background-color: #'. $this->palette[$val['PtnBGcolor']] . ";\n";
				$tmp .= 'background-image:URL("'. $_SERVER['PHP_SELF'] .'?ptn=' . $val['fillpattern'] . '&fc=' . $val['PtnFRcolor'] . "\");\n";
			}
		} elseif (!$border_sw && isset($val['PtnBGcolor'])){
			if ($val['PtnBGcolor'] < 65 && $val['PtnBGcolor'] > 0)
				$tmp.='background-color: #'. $this->palette[$val['PtnBGcolor']] . ";\n";
		}
// XXX
//		if (!isset($val['wrap'])) $tmp.='white-space: nowrap;'."\n";
		if (isset($val['valign'])){
			if ($val['halign']==1) $tmp.='text-align: left;'."\n";
			if ($val['halign']==2) $tmp.='text-align: center;'."\n";
			if ($val['halign']==3) $tmp.='text-align: right;'."\n";
			if ($val['halign']==5) $tmp.='text-align: justify;'."\n";
			if ($val['valign']==1) $tmp.='vertical-align: middle;'."\n";
			if ($val['valign']==2) $tmp.='vertical-align: bottom;'."\n";
			if ($val['valign']==0) $tmp.='vertical-align: top;'."\n";
		}
		return $tmp;
	}

	function getCssBR_($val,$key=-1){
		$tmp='';
		if (isset($val['Bstyle'])){
			if ($val['Bstyle']!=0){
				$tmp.='border-bottom-width: '. $this->getwidth($val['Bstyle']) . "\n";
				$tmp.='border-bottom-style: '. $this->getstyle($val['Bstyle']) . "\n";
			}
		}
		if (isset($val['Bcolor'])){
			if ($val['Bcolor']!=0 && $val['Bcolor']<56){
				$tmp.='border-bottom-color: #'. $this->palette[$val['Bcolor']] . ";\n";
			} else $tmp.='border-bottom-color: #'. $this->palette[0] . ";\n";
		}
		if (isset($val['Rstyle'])){
			if ($val['Rstyle']!=0){
				$tmp.='border-right-width: '. $this->getwidth($val['Rstyle']) . "\n";
				$tmp.='border-right-style: '. $this->getstyle($val['Rstyle']) . "\n";
			}
		}
		if (isset($val['Rcolor'])){
			if ($val['Rcolor']!=0 && $val['Rcolor']<56){
				$tmp.='border-right-color: #'. $this->palette[$val['Rcolor']] . ";\n";
			} else $tmp.='border-right-color: #'. $this->palette[0] . ";\n";
		}
		return $tmp;
	}

	function getCssTL_($val,$key=-1){
		$tmp='';
		if (isset($val['Tstyle'])){
			if ($val['Tstyle']!=0){
				$tmp.='border-top-width: '. $this->getwidth($val['Tstyle']) . "\n";
				$tmp.='border-top-style: '. $this->getstyle($val['Tstyle']) . "\n";
			}
		}
		if (isset($val['Tcolor'])){
			if ($val['Tcolor']!=0 && $val['Tcolor']<56){
				$tmp.='border-top-color: #'. $this->palette[$val['Tcolor']] . ";\n";
			} else $tmp.='border-top-color: #'. $this->palette[0] . ";\n";
		}
		if (isset($val['Lstyle'])){
			if ($val['Lstyle']!=0){
				$tmp.='border-left-width: '. $this->getwidth($val['Lstyle']) . "\n";
				$tmp.='border-left-style: '. $this->getstyle($val['Lstyle']) . "\n";
			}
		}
		if (isset($val['Lcolor'])){
			if ($val['Lcolor']!=0 && $val['Lcolor']<56){
				$tmp.='border-left-color: #'. $this->palette[$val['Lcolor']] . ";\n";
			} else $tmp.='border-left-color: #'. $this->palette[0] . ";\n";
		}
		$ftmp='';
		if ($key==0) $val['fontindex']=0;
		if (isset($val['fontindex'])){
			if ($val['fontindex']>=0){
				if ($this->recFONT[$val['fontindex']]['color'] < 56)
					$tmp.='color: #'. $this->palette[$this->recFONT[$val['fontindex']]['color']] . ";\n";
				if($this->recFONT[$val['fontindex']]['style'] & 0x2) $ftmp.=' italic';
				if($this->recFONT[$val['fontindex']]['style'] & 0x1) $ftmp.=' bold';
				$ftmp.=' '.($this->recFONT[$val['fontindex']]['height']/Const_fontH)."px";
				$ftmp.=' "'.$this->recFONT[$val['fontindex']]['fontname'].'"';
				$tmp.='font: '. $ftmp . ";\n";
				if($this->recFONT[$val['fontindex']]['style'] & 0xc)
					$tmp.='text-decoration:'.$this->fontdeco($this->recFONT[$val['fontindex']]['style']) . ";\n";
			}
		} else $tmp.='font: '.($this->recFONT[0]['height']/Const_fontH)."px".";\n";
		if (isset($val['fillpattern'])){
			if($val['fillpattern']==1){
//					$tmp.='background-color: #'. $this->palette[$val['PtnFRcolor']] . ";\n";
			} elseif ($val['fillpattern'] <=18 && $val['fillpattern'] >1){
				$tmp .='background-color: #'. $this->palette[$val['PtnBGcolor']] . ";\n";
				$tmp .= 'background-image:URL("'. $_SERVER['PHP_SELF'] .'?ptn=' . $val['fillpattern'] . '&fc=' . $val['PtnFRcolor'] . "\");\n";
			}
		} elseif(isset($val['PtnBGcolor'])){
			if ($val['PtnBGcolor'] < 65 && $val['PtnBGcolor'] > 0)
				$tmp.='background-color: #'. $this->palette[$val['PtnBGcolor']] . ";\n";
		}
		if (!isset($val['wrap'])) $tmp.='white-space: nowrap;'."\n";
		if (isset($val['valign'])){
			if ($val['halign']==1) $tmp.='text-align: left;'."\n";
			if ($val['halign']==2) $tmp.='text-align: center;'."\n";
			if ($val['halign']==3) $tmp.='text-align: right;'."\n";
			if ($val['halign']==5) $tmp.='text-align: justify;'."\n";
			if ($val['valign']==1) $tmp.='vertical-align: middle;'."\n";
			if ($val['valign']==2) $tmp.='vertical-align: bottom;'."\n";
			if ($val['valign']==0) $tmp.='vertical-align: top;'."\n";
		}
		return $tmp;
	}
		
	function makecss() {
		$tmp = '';
		if (defined('sht_config')) {
			foreach ($this->recXF as $key => $val) {
				$tmp .= ".XF". $key . " {\n";
				$tmp .= $this->getCssBR_($val, $key);
				$tmp .= $this->getCssTL_($val, $key);
				$tmp .= "}\n";
			}
			$tmp .= $this->makecssMG_();
		}
		else {
			foreach ($this->recXF as $key => $val) {
				//  print $key . ", " . var_dump($val) . "<BR>\n";
				$tmp .= ".XF". $key . " {\n";
				$tmp .= $this->getCssBR($val, $key);
				$tmp .= $this->getCssTL($val, $key);
				$tmp .= "}\n";
			}
			$tmp .= $this->makecssMG();
		}
		return $tmp."\n";
	}


	function makecssMG(){
		$tmp='';
		foreach($this->celmergeinfo as $sn => $sval){
			foreach($sval as $row=> $rval){
				foreach($rval as $col=> $val){
					if ($val['cond']==1){
					$xfst=$this->cellblock[$sn][$row][$col]['xf'];
					$xfen=$this->cellblock[$sn][$row+$val['rspan']-1][$col+$val['cspan']-1]['xf'];
					$valst=$this->recXF[$xfst];
					$valen=$this->recXF[$xfen];
					$tmp.=".XFs". $sn . "r" . $row . "c" . $col . " {\n";
					$tmp.=$this->getCssBR($valen);
					$tmp.=$this->getCssTL($valst);
					$tmp.="}\n";
					}
				}
			}
		}
		return $tmp;
	}

	function makecssMG_(){
		$tmp='';
		foreach($this->celmergeinfo as $sn => $sval){
			foreach($sval as $row=> $rval){
				foreach($rval as $col=> $val){
					if ($val['cond']==1){
					$xfst=$this->cellblock[$sn][$row][$col]['xf'];
					$xfen=$this->cellblock[$sn][$row+$val['rspan']-1][$col+$val['cspan']-1]['xf'];
					$valst=$this->recXF[$xfst];
					$valen=$this->recXF[$xfen];
					$tmp.=".XFs". $sn . "r" . $row . "c" . $col . " {\n";
					$tmp.=$this->getCssBR_($valen);
					$tmp.=$this->getCssTL_($valst);
					$tmp.="}\n";
					}
				}
			}
		}
		return $tmp;
	}
	
	function dispcell($sn,$row,$col,$mode=0){
	  if (isset($this->cellblock[$sn][$row][$col])){
		$cell=$this->cellblock[$sn][$row][$col];
if (!isset($this->recXF[$cell['xf']]['formindex'])) $this->recXF[$cell['xf']]['formindex']=0;
		switch ($cell['type']) {
			case Type_LABELSST:
				$strnum=$this->_get2($cell['dat'],0);
				$sstr=$this->eachsst[$strnum]['str'];
				$desc=mb_convert_encoding($sstr,$this->charset,'UTF-16LE');
				break;
			case Type_LABEL:
				$desc=mb_convert_encoding(substr($cell['dat'],2),$this->charset,'SJIS-WIN');
				break;
			case Type_RK:
			case Type_RK2:
				$desc=$this->Getrknum($this->_get4($cell['dat'],0));
				if (isset($this->recXF[$cell['xf']]['formindex']))
				if (isset($this->recFORMAT[$this->recXF[$cell['xf']]['formindex']]))
				$desc=$this->dispf($desc,$this->recFORMAT[$this->recXF[$cell['xf']]['formindex']],$mode);
				break;
			case Type_NUMBER:
				$strnum=unpack("d",$cell['dat']);
				$desc=$this->dispf($strnum[1],$this->recFORMAT[$this->recXF[$cell['xf']]['formindex']],$mode);
				break;
			case Type_FORMULA:
			case Type_FORMULA2:
				$result=substr($cell['dat'],0,8);
				if (substr($result,6,2)=="\xFF\xFF"){
					switch (substr($result,0,1)) {
					case "\x00":
						if ($this->biff_ver != Code_BIFF8) {
						$desc=$this->getstring5(substr($cell['string'],4),1);
						} else {
						$desc=$this->getstring(substr($cell['string'],4),2);
						}
						break;
					case "\x01":
						$desc=(substr($result,2,1)=="\x01")? "TRUE":"FALSE";
						break;
					case "\x02": $desc='#ERROR!';
						break;
					case "\x03": $desc='';
						break;
					}
				} else {
					$desc0=unpack("d",$result);
					if (!isset($this->recXF[$cell['xf']]['formindex'])) $desc=$desc0[1];
					else if (!isset($this->recFORMAT[$this->recXF[$cell['xf']]['formindex']])) $desc=$desc0[1];
					else $desc=$this->dispf($desc0[1],$this->recFORMAT[$this->recXF[$cell['xf']]['formindex']],$mode);
				}
				break;
			case Type_BOOLERR:
				$desc=($this->_get2($cell['dat'],0)==0)?'FALSE':'TRUE';
				break;
			case Type_BLANK:
				$desc=''; // XXX $desc = ' '; 
				break;
			default:
				$desc=''; // XXX $desc = ' '; 
		}
	  } else $desc='';
	  return $desc;
	}

	function Getrknum($rknum){
		if (($rknum & 0x02) != 0) {
			$value = $rknum >> 2;
		} else {
			$sign = ($rknum & 0x80000000) >> 31;
			$exp = ($rknum & 0x7ff00000) >> 20;
			$mantissa = (0x100000 | ($rknum & 0x000ffffc));
			$value = $mantissa / pow( 2 , (20- ($exp - 1023)));
			if ($sign) {$value = -1 * $value;}
		}
		if (($rknum & 0x01) != 0) $value /= 100;
		return $value;
	}

	function getheightwidth(){
		$snum=count($this->sheetbin);
		for ($sno=0;$sno<$snum;$sno++){
			if(isset($this->rowblock[$sno]))
			foreach($this->rowblock[$sno] as $key=>$val){
				$ph=$this->_get2($val['rowfoot'],0)/15;
				if ($ph & 0x8000) $this->rowheight[$sno][$key]=$defheight;
				else $this->rowheight[$sno][$key]=$ph;
			}
		}
	}

	function dispf($val,$form,$mode=0){
		if (strlen(trim($form))==0 || $mode==1) return $val;
		$form = mb_ereg_replace ("\;.*$","",$form);
		if (preg_match('/\"(.*?)\".*[0#]/', $form, $mtc)) $punit = $mtc[1];else $punit="";
		if (preg_match('/[0#].*\"(.*?)\"/', $form, $mtc)) $bunit = $mtc[1];else $bunit="";
		$form =stripslashes($form);
		$form = mb_ereg_replace ("\[.+\]","",$form);
		if (mb_ereg("[0#]",mb_ereg_replace ("\"(.*?)\"","",$form))){
			$sr= $this->numform($val,$form,$punit,$bunit);
		} elseif (mb_ereg("[MDYhmsGg]",$form)){
                        $sr= $this->dtform($val,$form);
		} elseif ($form=="@"){
			$sr=$val;
		} else $sr= " unknown type [".$form."]  ".$val;
		return $sr;
	}

	function numform($val,$form,$punit=null,$bunit=null){
		if (substr($form,0,1)=='$') {$punit .= '$';}
		$percent = (strpos($form,"%")!== FALSE) ? TRUE: FALSE;
		$form =str_replace("%","",$form);
		$exp = (strpos($form,'E+')!== FALSE) ? TRUE: FALSE;
		$numformat = (strpos($form,'#')!== FALSE) ? TRUE: FALSE;
		if (mb_ereg("^.*0\.0*.*$",$form)){
			$num = strlen(mb_ereg_replace("^.*0(\.0*).*$",'\\1',$form));
			$rnum = strlen(mb_ereg_replace('^.*?(0+)\..*$','\\1',$form));
		} else {$num=0;$rnum=1;}
		$num -=1;
		if ($num <0) $num=0;
		$val = ($percent) ? $val * 100: $val;
		if ($numformat) {
			$result = number_format($val,$num);
		} else {
			if ($exp){
				$result=sprintf('%.'.($num)."e", $val);
			} else if ($rnum == 1){
				$result=sprintf('%01.'.$num."f", $val);
			} else {
				if ($num>0) $rnum += $num+1;
				$result=sprintf('%0'.$rnum.'.'.$num."f", $val);
			}
		}
		$result = $punit.$result.$bunit;
		if ($percent) $result = $result . '%';
		return $result;
	}

	function dtform($val,$form){
		$form = (mb_ereg("dddd",$form)) ? mb_convert_encoding(pack("H*","5900590059005900745e4d0008674400e565"),$this->charset,'UTF-16LE'): $form;
		$form = mb_eregi_replace ('M/D/YY','yyyy/m/d',$form);
		$form = mb_ereg_replace ('[\\\"]','',$form);
		$ut=$this->ms2unixtime($val);
		$ge=$this->towareki($ut[0]);
		if (mb_eregi("AM/PM",$form)) {
			$form=mb_eregi_replace (' ?am/pm','',$form);
			if ($ut['hours'] > 12) {
				$ut['hours'] -= 12;
				$ap=" PM";
			} else $ap=" AM";
			$ampm = true;
		} else  $ampm = false;
		$result=mb_eregi_replace ('yyyy',$ut['year'],$form);
		$result=mb_eregi_replace ('mmmmm','xxxx',$result);
		$result=mb_eregi_replace ('mmmm','xxx',$result);
		$result=mb_eregi_replace ('mmm','xx',$result);
		$result=mb_eregi_replace ('mm','x',$result);
		$result=mb_eregi_replace ('ss',$ut['seconds'],$result);
		$result=mb_eregi_replace ('h',$ut['hours'],$result);
		$result=mb_eregi_replace ('m',$ut['mon'],$result);
		$result=mb_eregi_replace ('d+',$ut['mday'],$result);
		$result=mb_eregi_replace ('ggg',$ge['gg'],$result);
		$result=mb_eregi_replace ('g',$ge['ga'],$result);
		$result=mb_eregi_replace ('e',$ge['ge'],$result);
		$result=mb_eregi_replace ('yy',substr("".$ut['year'],-2),$result);
                $result=mb_eregi_replace ('xxxx',substr($ut['month'],0,1),$result);
                $result=mb_eregi_replace ('xxx',$ut['month'],$result);
                $result=mb_eregi_replace ('xx',substr($ut['month'],0,3),$result);
                $result=mb_eregi_replace ('x',$ut['minutes'],$result);
		return ($ampm) ? $result .$ap: $result;
	}

	function towareki($dt) {
	    $ge = array();
	    $tm = getdate($dt);
	    if ($dt < -1812186000){
	        $ge['gg'] = "0e66bb6c";
	        $ge['ga'] = "M";
	        $ge['ge'] = $tm["year"] - 1867;
	    } elseif ($dt < -1357635600) {
	        $ge['gg'] = "2759636b";
	        $ge['ga'] = "T";
	        $ge['ge'] = $tm["year"] - 1911;
	    } elseif ($dt < 600188400) {
	        $ge['gg'] = "2d668c54";
	        $ge['ga'] = "S";
	        $ge['ge'] = $tm["year"] - 1925;
	    } else {
	        $ge['gg'] = "735e1062";
	        $ge['ga'] = "H";
	        $ge['ge'] = $tm["year"] - 1988;
	    }
		$ge['gg'] = mb_convert_encoding(pack("H*",$ge['gg']),$this->charset,'UTF-16LE');
	    return $ge;
	}

	function ms2unixtime($timevalue,$offset1904 = 0){
		if ($timevalue > 1)
			$timevalue -= ($offset1904 ? 24107 : 25569);
		return getdate(round(($timevalue * 24 -9) * 60 * 60));
	}

	function sepheadfoot($str,$sn){
		$str=mb_ereg_replace ('&&', '&amp;' ,$str);
		$str=mb_ereg_replace ('&P', ($sn+1) ,$str);
		$str=mb_ereg_replace ('&N', $this->sheetnum ,$str);
		$str=mb_ereg_replace ('&D', date("Y/m/d") ,$str);
		$str=mb_ereg_replace ('&T', date("H:i:s") ,$str);
		$fname=mb_ereg_replace (".*\/", "" ,$this->infilename);
		$path=mb_ereg_replace ("^(.*\/).+", "\\1" ,$this->infilename);
		$str=mb_ereg_replace ('&A', $this->sheetname[$sn] ,$str);
		$str=mb_ereg_replace ('&F', $fname ,$str);
		$str=mb_ereg_replace ('&Z', $path ,$str);
		$str=mb_ereg_replace ('&G', '' ,$str);
		if (preg_match('/.*&R(.*)$/',$str)){
			$s['right'] = mb_ereg_replace ('.*&R(.*)$', "\\1" ,$str);
			$str= mb_ereg_replace ("&R.*$", "" ,$str);
		}
		if (preg_match("/.*&C(.*)$/",$str)){
			$s['center'] = mb_ereg_replace ('.*&C(.*)$', "\\1" ,$str);
			$str= mb_ereg_replace ('&C.*$', '' ,$str);
		}
		$s['left'] = mb_ereg_replace ('&L(.*)$', "\\1" ,$str);
		return $s;
	}

	function getProperty($dat){
		if ($this->_get2($dat, 0) != 0xFFFE) return;
		if ($this->_get2($dat, 2) != 0) return;
		$res['osver'] = $this->_get2($dat, 4);
		$tmp = $this->_get2($dat, 6);
		if ($tmp == 0) $res['OS']='Win16'; else
		if ($tmp == 1) $res['OS']='Macintosh'; else
		if ($tmp == 2) $res['OS']='Win32'; else
		$res['OS']='unknown';
		if ($res['OS']=='Macintosh') {
			$pchar="UTF-8";
		} else {
			$pchar="SJIS-win";
		}
		$res['CLSID']=bin2hex(substr($dat, 8, 16));
		$res['cSections'] = $this->_get4($dat, 0x18);
		if ($res['cSections'] < 1) return;
		$pos=0x1C;
		for ($i = 0; $i < $res['cSections']; $i++){
			$sec['FMTID']= bin2hex(substr($dat, $pos, 16));
			$sec['offset']= $this->_get4($dat, $pos + 16);
			$res['section'][$i]=$sec;
			$pos += 20;

			$spos=$sec['offset'];
			$res['section'][$i]['cbSection']=$this->_get4($dat, $spos);
			$res['section'][$i]['cProperties']=$this->_get4($dat, $spos+4);
			for ($j=0; $j < $res['section'][$i]['cProperties']; $j++) {
				$prop['propid']=$this->_get4($dat, $spos + 8 + $j*8);
				$prop['dwOffset']=$this->_get4($dat, $spos + 12 + $j*8);
				$ppos=$sec['offset']+$prop['dwOffset'];
				$prop['type']=$this->_get4($dat, $ppos);
				if ($prop['propid']==0){ //dictionary
					$dpos=0;;
					$numdic=$prop['type'];
					for ($k=0; $k<$numdic; $k++){
						$dlen=$this->_get4($dat, $ppos+$dpos+8);
						$cusname=mb_convert_encoding(substr($dat,$ppos+$dpos+12,$dlen), $this->charset,$pchar);
						$dprop[$this->_get4($dat, $ppos+$dpos+4)]=trim($cusname);
						$dpos+=$dlen+8;
					}
					$res['section'][$i]['dic']=$dprop;
					unset($dprop);
				} else {

				switch ($this->_get4($dat, $ppos)) {
				case 2:	//
					$prop['val']=$this->_get2($dat, $ppos+4);
					if ($prop['propid']==1){
						if ($prop['val']==65001) {
							$pchar="UTF-8";
						} else {
							$pchar="SJIS-win";
						}
					}
					break;
				case 3:	//
					$prop['val']=$this->_get4($dat, $ppos+4);
					break;
				case 6:	//
					$prop['val']='&H'.bin2hex(substr($dat, $ppos+4,4));
					break;
				case 11:	//
					$prop['val']=($this->_get2($dat, $ppos+4)==0)? 0:-1;
					break;
				case 30:	//
					$len=$this->_get4($dat, $ppos+4);
					$prop['val']=mb_convert_encoding(substr($dat,$ppos+8,$len), $this->charset, $pchar);
					break;
				case 64:	//
					$uTime = $this->_get4($dat, $ppos+8) * 429.4967296 + $this->_get2($dat, $ppos+6) * 0.0065536 + $this->_get2($dat, $ppos+4)/10000000;
					$uTime = floor($uTime) - 11644473600;
					$prop['val'] = date('Y/m/d H:i:s', $uTime);
					break;
				case 65:	//
					if ($res['section'][$i]['dic'][$prop['propid']]=='_PID_HLINKS'){
						$dpos=0;
						$numdic=$this->_get4($dat, $ppos+8);
						for ($k=0; $k<$numdic; $k++){
							switch ($this->_get4($dat, $ppos+$dpos+12)){
							case 31:
								$dlen=((($this->_get4($dat, $ppos+$dpos+16)+1)>>1)<<1) * 2;
								if ($this->_get4($dat, $ppos+$dpos+16) > 1)
								$pida[]=trim(mb_convert_encoding(substr($dat,$ppos+$dpos+20,$dlen-2), $this->charset,$pchar));
								$dpos+=$dlen+8;
								break;
							case 3:
								$dpos+=8;
								break;
							default:
								$dpos+=8;
							}
						}
						$prop['val']=$pida;
					} else {
						$len=$this->_get4($dat, $ppos+4);
						$prop['val']=mb_convert_encoding(substr($dat,$ppos+8,$len), $this->charset, "UTF-16LE");
					}
					break;
				case 71:	//
					$prop['val']=$this->_get4($dat, $ppos+4);
					break;
				case 4126:	//
					$dpos=0;
					$numdic=$this->_get4($dat, $ppos+4);
					for ($k=0; $k<$numdic; $k++){
						$dlen=$this->_get4($dat, $ppos+$dpos+8);
						$tparts[$k]=mb_convert_encoding(substr($dat,$ppos+$dpos+12,$dlen), $this->charset,$pchar);
						$dpos+=$dlen+4;
					}
					$prop['val']=$tparts;
					break;
				case 4108:	//
					$dpos=0;
					$numdic=$this->_get4($dat, $ppos+4);
					for ($k=0; $k<$numdic; $k++){
						switch ($this->_get4($dat, $ppos+$dpos+8)){
						case 30:
							$dlen=$this->_get4($dat, $ppos+$dpos+12);
							$heading=mb_convert_encoding(substr($dat,$ppos+$dpos+16,$dlen), $this->charset,$pchar);
							$dpos+=$dlen+8;
							break;
						case 3:
							$dpos+=8;
							break;
						default:
							$dpos+=8;
						}
					}
					$prop['val']=$heading;
					break;
				default:
					$prop['val']='&H'.bin2hex(substr($dat, $ppos+4,16));
					$dpos+=8;
				}
				$res['section'][$i]['property'][]=$prop;
				}
			}
		}
		return $res;
	}

	function getPropJP(){
		$tmp=array();
		foreach($this->siData as $key => $val){
			$props=$this->getProperty($val);
			$tmp['OS']= $props['OS'] . " " . $props['osver'];
			if (isset($props['section']))
			foreach($props['section'] as $skey => $sval){
				foreach($sval['property'] as $pkey => $pval){
					if (isset($sval['dic'][$pval['propid']])) {
						$name=trim($sval['dic'][$pval['propid']]);
					} else if (isset($this->tag[$key][$pval['propid']])) {
						$name=mb_convert_encoding(trim($this->tag[$key][$pval['propid']]), $this->charset, "EUC-JP");
					} else {
	//					$name="NA(${key}-${pval['propid']})";
						continue;
					}
					if (is_array($pval['val'])){
						$i=0;
						$tmp[$name]='';
						foreach($pval['val'] as $parts){
							if ($i++) $tmp[$name].="<br />";
							$tmp[$name] .= $parts;
						}
					} else {
						$tmp[$name]=$pval['val'];
					}
				}
			}
		}
		return $tmp;
	}


	function getPropEN(){
		$tmp=array();
		foreach($this->siData as $key => $val){
			$props=$this->getProperty($val);
			$tmp['OS']= $props['OS'] . " " . $props['osver'];
			if (isset($props['section']))
			foreach($props['section'] as $skey => $sval){
				foreach($sval['property'] as $pkey => $pval){
					if (isset($sval['dic'][$pval['propid']])) {
						$name=trim($sval['dic'][$pval['propid']]);
					} else if (isset($this->tag1[$key][$pval['propid']])) {
						$name=trim($this->tag1[$key][$pval['propid']]);
					} else {
						$name="NA(${key}-${pval['propid']})";
						continue;
					}
					if (is_array($pval['val'])){
						$i=0;
						$tmp[$name]='';
						foreach($pval['val'] as $parts){
							if ($i++) $tmp[$name].="<br />";
							$tmp[$name] .= $parts;
						}
					} else {
						$tmp[$name]=$pval['val'];
					}
				}
			}
		}
		return $tmp;
	}

	/**
	* Set(Get) Error-Handling Method.
	* @param integer $mode error handling method(default 0)
	* @return integer error handling method
	* @access public
	*/
	function setErrorHandling($mode=''){
		if (is_numeric($mode)) {
			$this->Flag_Error_Handling = $mode;
		}
		return $this->Flag_Error_Handling;
	}

	/**
	* @param mixed $data object
	* @return boolean True:The error occurred
	* @access public
	*/
    function isError($data){return is_a($data, 'ErrMess');}

	/**
	* @access private
	*/
    function raiseError($message = ''){
		if ($this->Flag_Error_Handling == 0){
			die($message);
		}
		return new ErrMess($message);
	}

    /**
    * @access private
    */
    function _getdefxf($sn,$row,$col) {
		if (isset($this->rowdefxf[$sn][$row])){
			$cxf = $this->rowdefxf[$sn][$row];
		} elseif (isset($this->coldefxf[$sn][$col])){
			$cxf = $this->coldefxf[$sn][$col];
		} else {
			$cxf = 0x0f;
		}
		return $cxf;
	}
}

/**
* error class
*/
class ErrMess {
    var $message = '';
    function __construct($message){$this->message = $message;}
    function ErrMess($message){self::__construct($message);}
    function getMessage() {return ($this->message);}
}

function makeptn($nn,$fc){
  $frcolor=array(
	"000000","FFFFFF","FF0000","00FF00","0000FF","FFFF00","FF00FF","00FFFF",
	"000000","FFFFFF","FF0000","00FF00","0000FF","FFFF00","FF00FF","00FFFF",
	"800000","008000","000080","808000","800080","008080","C0C0C0","808080",
	"9999FF","993366","FFFFCC","CCFFFF","660066","FF8080","0066CC","CCCCFF",
	"000080","FF00FF","FFFF00","00FFFF","800080","800000","008080","0000FF",
	"00CCFF","CCFFFF","CCFFCC","FFFF99","99CCFF","FF99CC","CC99FF","FFCC99",
	"3366FF","33CCCC","99CC00","FFCC00","FF9900","FF6600","666699","969696",
	"003366","339966","003300","333300","993300","993366","333399","333333",
	);
	if (($nn<2) || ($nn>18)) exit;
	if ($fc<0 || $fc >64) $fc=0;
	$fillptn0="47494638396104000400f00000";
	$frcolor= $frcolor[$fc];
	$fillptn1="ffffff21f90401000001002c000000000400040000080";
	$fillptn3[2]="c000104103870a04082070302003b";
	$fillptn3[3]="d000300183850a0408200040604003b";
	$fillptn3[4]="d000104183850a0408201040604003b";
	$fillptn3[5]="c0001081c283080c183060302003b";
	$fillptn3[6]="e0001000810402041830507060808003";
	$fillptn3[7]="e00010008405020c1000207220c08003";
	$fillptn3[8]="e000304000060604182020b0e0c08003";
	$fillptn3[9]="e000100081040204182060b020808003";
	$fillptn3[10]="d000100081040a04082060d0604003b";
	$fillptn3[11]="c0001080410a0a0c183050302003b";
	$fillptn3[12]="d000104182890e0c00005030404003b";
	$fillptn3[13]="d00010418184020418303010404003b";
	$fillptn3[14]="c0003080430b0600082020302003b";
	$fillptn3[15]="e0001081418a0208082010e160c08003b";
	$fillptn3[16]="d00010410383040418206010404003b";
	$fillptn3[17]="b000104184890a0c0820101003b";
	$fillptn3[18]="90001041848b0a0c180003b";
	$patern=$fillptn0.$frcolor.$fillptn1.$fillptn3[$nn];
	header("Content-type: image/gif");
	print pack("H*",$patern);
	exit;
}
?>
