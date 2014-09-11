<?php
/*
 * Shinsai FaxOCR
 *
 * Copyright (C) 2009-2014 National Institute of Public Health, Japan.
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
// シート関連情報情報Class
// ===========================================================================

require_once "config.php";
require_once "lib/common.php";

//
// シートクラス
//
class Sheet {
    public $xls;

    // シート数
    public $sn = 0;

    public $scale;

    // シート情報
    public $tblwidth;       // width of the sheet
    public $tblheight;      // height of the sheet
    public $cells_width;    // arrray
    public $cells_height;   // arrray
    public $cells_colrowspan;  // hash
    public $row_count; // セル数(縦)
    public $col_count; // セル数(横)

    // 最小のセルのサイズ
    public $min_cell_width;
    public $min_cell_height;

    // 表示シート情報
    public $disp;       // object to DispSheet

    // マーカーサイズ
    public $marker_size;

    function __construct($xls) {
        $this->xls = $xls;

        // サイズ取得
        $this->tblwidth = 0;
        $this->tblheight = 0;
        $this->min_cell_width = 0;
        $this->min_cell_height = 0;
        for ($i = 0; $i <= $this->xls->maxcell[$this->sn]; $i++) {
            $this->cells_width[$i] = $this->xls->getColWidth($this->sn, $i);
            $this->tblwidth += $this->xls->getColWidth($this->sn, $i);

            if ($this->min_cell_width == 0 || $this->min_cell_width > $this->xls->getColWidth($this->sn, $i)) {
                $this->min_cell_width = $this->xls->getColWidth($this->sn, $i);
            }
        }

        for ($i = 0; $i <= $this->xls->maxrow[$this->sn]; $i++) {
            $this->cells_height[$i] = $this->xls->getRowHeight($this->sn, $i);
            $this->tblheight += $this->xls->getRowHeight($this->sn, $i);

            if ($this->min_cell_height == 0 || $this->min_cell_height > $this->xls->getRowHeight($this->sn, $i)) {
                $this->min_cell_height = $this->xls->getRowHeight($this->sn, $i);
            }
        }

        for ($col = 0; $col <= $this->xls->maxcell[$this->sn]; $col++) {
            for ($row = 0; $row <= $this->xls->maxrow[$this->sn]; $row++) {
                $colspan = $this->xls->celmergeinfo[$this->sn][$row][$col]['cspan'];
                if (isset($colspan) && $colspan > 1) {
			        $this->cells_colrowspan['colspan'][$row][$col] = $colspan;
                }
                $rowspan = $this->xls->celmergeinfo[$this->sn][$row][$col]['rspan'];
                if (isset($rowspan) && $rowspan > 1) {
			        $this->cells_colrowspan['rowspan'][$row][$col] = $rowspan;
                }
            }
        }

        $this->row_count = $this->xls->maxrow[$this->sn];
        $this->col_count = $this->xls->maxcell[$this->sn];

        $this->scale = get_scaling($this->tblwidth, $this->tblheight, 940);

        // 表示サイズ取得
        $this->disp = new DispSheet($this);

        // A1 cell の大きさをマーカーサイズとする
        $this->marker_size = $this->disp->cells_width[0];
        if ($this->disp->cells_height[0] > $this->disp->cells_width[0]) {
            $this->marker_size = $this->disp->cells_height[0];
        }
    }

    public function get_row_size($row) {
        return $this->cells_height[$row];
    }

    public function get_col_size($col) {
        return $this->cells_width[$col];
    }

    public function get_disp_size($size) {
        return floor($size * $this->scale);
    }
}

//
// シート(表示)クラス
//
class DispSheet {
    // 表示シート情報: 表示用にスケーリングした値を保持
    public $tblwidth;
    public $tblheight;
    public $cells_width;    // array
    public $cells_height;   // array

    function __construct($sheet) {
        $this->tblwidth = 0;
        $this->tblheight = 0;
        for ($i = 0; $i <= $sheet->xls->maxcell[$sheet->sn]; $i++) {
            $this->cells_width[$i] = floor($sheet->cells_width[$i] * $sheet->scale);
            $this->tblwidth += $this->cells_width[$i];
        }

        for ($i = 0; $i <= $sheet->xls->maxrow[$sheet->sn]; $i++) {
            $this->cells_height[$i] = floor($sheet->cells_height[$i] * $sheet->scale);
            $this->tblheight += $this->cells_height[$i];
        }

        //$this->tblwidth = floor($sheet->tblwidth * $sheet->scale);
        //$this->tblheight = floor($sheet->tblheight * $sheet->scale);
    }

    public function get_row_size($row) {
        return $this->cells_height[$row];
    }

    public function get_col_size($col) {
        return $this->cells_width[$col];
    }

    // return: from -1 to maxcel + 1
    public function getCellNumX($position) {
        return $this->getCellNum($this->cells_width, $position);
    }
    public function getCellNumY($position) {
        return $this->getCellNum($this->cells_height, $position);
    }
    protected function getCellNum($cells, $position) {
        if ($position < 0) {
            return -1;
        }
        $width = 0;
        foreach ($cells as $cellNum => $len) {
            $width += $len;
            if ($width > $position) {
                return $cellNum;
            }
        }
        return $cellNum + 1;
    }
}

/* vim: set et fenc=utf-8 ff=unix sts=4 sw=4 ts=4 : */
?>
