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
    public $tblwidth;
    public $tblheight;
    public $cells_width;
    public $cells_height;
    public $row_count; // セル数(縦)
    public $col_count; // セル数(横)

    // 最小のセルのサイズ
    public $min_cell_width;
    public $min_cell_height;

    // 表示シート情報
    public $disp_tblwidth;
    public $disp_tblheight;
    public $disp_cells_width;
    public $disp_cells_height;

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

        $this->row_count = $this->xls->maxrow[$this->sn];
        $this->col_count = $this->xls->maxcell[$this->sn];

		$this->scale = get_scaling($this->tblwidth, $this->tblheight, 940);

        // 表示サイズ取得
        $this->disp_tblwidth = 0;
        $this->disp_tblheight = 0;
        for ($i = 0; $i <= $this->xls->maxcell[$this->sn]; $i++) {
            $this->disp_cells_width[$i] = floor($this->cells_width[$i] * $this->scale);
            $this->disp_tblwidth += $this->disp_cells_width[$i];
        }

        for ($i = 0; $i <= $this->xls->maxrow[$this->sn]; $i++) {
            $this->disp_cells_height[$i] = floor($this->cells_height[$i] * $this->scale);
            $this->disp_tblheight += $this->disp_cells_height[$i];
        }

		//$this->disp_tblwidth = floor($this->tblwidth * $this->scale);
		//$this->disp_tblheight = floor($this->tblheight * $this->scale);

        $this->marker_size = $this->disp_cells_width[0];
        if ($this->disp_cells_height[0] > $this->disp_cells_width[0]) {
            $this->marker_size = $this->disp_cells_height[0];
        }
    }

    public function get_row_size($row) {
        return $this->cells_height[$row];
    }

    public function get_col_size($col) {
        return $this->cells_width[$col];
    }

    public function get_disp_row_size($row) {
        return $this->disp_cells_height[$row];
    }

    public function get_disp_col_size($col) {
        return $this->disp_cells_width[$col];
    }

    public function get_disp_size($size) {
        return floor($size * $this->scale);
    }

}

?>
