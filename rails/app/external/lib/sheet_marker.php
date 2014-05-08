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
// シート(マーカー)関連情報情報Class
// ===========================================================================

require_once "config.php";
require_once "lib/common.php";
require_once "lib/sheet.php";

//
// マーカー付きシートクラス
//
class SheetMarker extends Sheet {
    // マーカー情報
    public $marker_block_width;
    public $marker_block_height;
    public $marker_offset_x;
    public $marker_offset_y;
    public $size_of_marker;

    // 表示シート情報
    public $disp_sheet;

    // シート(マーカー付き)情報
    public $sheet_width;
    public $sheet_height;

    public $marker_scale;

    function __construct($xls, $marker_block_width, $marker_block_height, $marker_offset_x, $marker_offset_y, $marker_size) {
        parent::__construct($xls);

        // args as disp scale(without marker window)
        $this->marker_block_width = $marker_block_width;
        $this->marker_block_height = $marker_block_height;
        $this->marker_offset_x = $marker_offset_x;
        $this->marker_offset_y = $marker_offset_y;
        $this->size_of_marker = $marker_size;

        // calc sheet size
        $this->sheet_width = 0;
        $this->sheet_height = 0;

        $width_marker_window_without_offset = $this->marker_block_width + $this->marker_offset_x;
        if ($this->marker_offset_x < 0) {
            // add offset
            $this->sheet_width += abs($this->marker_offset_x);

            $width_marker_window_without_offset = $this->marker_block_width;
        }

        if ($this->disp->tblwidth > $width_marker_window_without_offset) {
            $this->sheet_width += $this->disp->tblwidth;
        } else {
            $this->sheet_width += $width_marker_window_without_offset;
        }

        $height_marker_window_without_offset = $this->marker_block_height + $this->marker_offset_y;
        if ($this->marker_offset_y < 0) {
            // add offset
            $this->sheet_height += abs($this->marker_offset_y);

            $height_marker_window_without_offset = $this->marker_block_height;
        }

        if ($this->disp->tblheight > $height_marker_window_without_offset) {
            $this->sheet_height += $this->disp->tblheight;
        } else {
            $this->sheet_height += $height_marker_window_without_offset;
        }

        // 縮小/拡大率
        $this->marker_scale = get_scaling($this->sheet_width, $this->sheet_height, 960);

        // 表示サイズ取得
        $this->disp_sheet = new DispSheetMarker($this);

    }
}

//
// マーカー付きシートクラス
//
class DispSheetMarker {
    // シート情報
    public $sheet_width;
    public $sheet_height;

    public $size_of_marker;

    // 表示テーブルサイズ
    public $tblwidth;
    public $tblheight;

    public $cells_width;
    public $cells_height;

    public $marker_offset_x;
    public $marker_offset_y;

    public $offset_top_table;
    public $offset_left_table;

    public $position_top_marker;
    public $position_left_marker;
    public $position_bottom_marker;
    public $position_right_marker;

    public $position_of_sheet_id_from_left_side;

    function __construct($sheet_marker) {
        // シートウインドウ
        $this->sheet_width = floor($sheet_marker->sheet_width * $sheet_marker->marker_scale);
        $this->sheet_height = floor($sheet_marker->sheet_height * $sheet_marker->marker_scale);

        $this->marker_offset_x = floor($sheet_marker->marker_offset_x * $sheet_marker->marker_scale);
        $this->marker_offset_y = floor($sheet_marker->marker_offset_y * $sheet_marker->marker_scale);

        // テーブルウインドウ
        $this->tblwidth = 0;
        $this->tblheight = 0;
        for ($i = 0; $i <= $sheet_marker->xls->maxcell[$sheet_marker->sn]; $i++) {
            $this->cells_width[$i] = floor($sheet_marker->cells_width[$i] * $sheet_marker->scale * $sheet_marker->marker_scale);
            $this->tblwidth += $this->cells_width[$i];
        }

        for ($i = 0; $i <= $sheet_marker->xls->maxrow[$sheet_marker->sn]; $i++) {
            $this->cells_height[$i] = floor($sheet_marker->cells_height[$i] * $sheet_marker->scale * $sheet_marker->marker_scale);
            $this->tblheight += $this->cells_height[$i];
        }

        $this->offset_top_table = $this->marker_offset_y < 0 ? abs($this->marker_offset_y) : 0;
        $this->offset_left_table = $this->marker_offset_x < 0 ? abs($this->marker_offset_x) : 0;

        // マーカーウインドウ
        $this->marker_block_width = floor($sheet_marker->marker_block_width * $sheet_marker->marker_scale);
        $this->marker_block_height = floor($sheet_marker->marker_block_height * $sheet_marker->marker_scale);

        $this->size_of_marker = floor($sheet_marker->size_of_marker * $sheet_marker->marker_scale);

        $this->position_of_sheet_id_from_left_side = $this->marker_offset_x < 0 ? floor($this->size_of_marker * 2) : floor($this->size_of_marker * 2 + abs($this->marker_offset_x));

        $this->position_top_marker = $this->marker_offset_y < 0 ? 0 : abs($this->marker_offset_y);
        $this->position_left_marker = $this->marker_offset_x < 0 ? 0 : abs($this->marker_offset_x);
        $this->position_bottom_marker = $this->position_top_marker + $this->marker_block_height - $this->size_of_marker; // from top
        $this->position_right_marker = $this->position_left_marker + $this->marker_block_width - $this->size_of_marker; // from left


/*
        // Spec is not fixed that assigning position of marker by users from web UI.
        // the following variable can control which feature is preferred
        $feature_fixed_marker_window = 0;
        if ($debug_mode === 'true') {
            $feature_fixed_marker_window = 1;
        }
        if ($feature_fixed_marker_window == 1) {
            // set marker size from the size of A1 cell
            $this->marker_scale = 1;

            $size_of_marker = $this->marker_size * $this->scale);
            $tblwidth = $this->disp_tblwidth;
            $tblheight = $this->disp_tblheight;

            // シートウインドウ
            $this->disp_sheet_width = $tblwidth;
            $this->disp_sheet_height = $tblheight;
            $this->marker_offset_x = 0;
            $this->marker_offset_y = 0;
            // テーブルウインドウ
            $this->offset_top_table = 0;
            $this->offset_left_table = 0;
            // マーカーウインドウ
            $this->marker_block_width = $tblwidth;
            $this->marker_block_height = $tblheight;

            $position_of_sheet_id_from_left_side = floor($size_of_marker * 2);
            $this->position_top_marker = 0;
            $this->position_left_marker = 0;
            $this->position_bottom_marker = $this->marker_block_height - $size_of_marker; // from top
            $this->position_right_marker = $this->marker_block_width - $size_of_marker; // from left
        }
*/
    }

    public function get_row_size($row) {
        return $this->cells_height[$row];
    }

    public function get_col_size($col) {
        return $this->cells_width[$col];
    }

}

/* vim: set et fenc=utf-8 ff=unix sts=4 sw=4 ts=4 : */
?>
