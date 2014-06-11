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


require_once 'lib/sheet.php';


///////////////////////////////////////////////////////////////////////////////
//
// マーカーがセルにジャストフィットするシートクラス
//
class SheetCellFitMarker {
    // original sheet
    public $sheet;  // object to Sheet

    // シート情報
    public $tblwidth;       // sheet width:  inner marker
    public $tblheight;      // sheet height: inner marker
    public $cells_width;    // arrray: contains inner/outer marker
    public $cells_height;   // arrray: contains inner/outer marker
    public $row_count; // セル数(縦): inner marker
    public $col_count; // セル数(横): inner marker

    // マーカー情報
    // 3 marker objects: disp scale
    public $topLeftMarker;  // object to MarkerOnSheet
    public $topRightMarker;
    public $bottomLeftMarker;

    // 表示シート情報
    public $disp_sheet;     // object to DispSheetCellFitMarker

    public $marker_scale;

    function __construct($xls, $marker_window_web_ui) {
        $this->sheet = new Sheet($xls);

        $marker_size_on_sheet_scale = $marker_window_web_ui->marker_size / $marker_window_web_ui->scale;

        // create 3 marker objects
        $topLeftMarkerPositionX = $marker_window_web_ui->position_x + floor($marker_window_web_ui->marker_size/2);
        $topLeftMarkerPositionY = $marker_window_web_ui->position_y + floor($marker_window_web_ui->marker_size/2);
        $this->topLeftMarker = new MarkerOnSheet($marker_size_on_sheet_scale, $marker_size_on_sheet_scale, $this->sheet);
        $this->topLeftMarker->colNum = $this->sheet->disp->getCellNumX($topLeftMarkerPositionX);
        $this->topLeftMarker->rowNum = $this->sheet->disp->getCellNumY($topLeftMarkerPositionY);

        $topRightMarkerPositionX = $marker_window_web_ui->position_x + $marker_window_web_ui->width - floor($marker_window_web_ui->marker_size/2);
        $topRightMarkerPositionY = $marker_window_web_ui->position_y + floor($marker_window_web_ui->marker_size/2);
        $this->topRightMarker = new MarkerOnSheet($marker_size_on_sheet_scale, $marker_size_on_sheet_scale, $this->sheet);
        $this->topRightMarker->colNum = $this->sheet->disp->getCellNumX($topRightMarkerPositionX);
        $this->topRightMarker->rowNum = $this->sheet->disp->getCellNumY($topRightMarkerPositionY);

        $bottomLeftMarkerPositionX = $marker_window_web_ui->position_x + floor($marker_window_web_ui->marker_size/2);
        $bottomLeftMarkerPositionY = $marker_window_web_ui->position_y + $marker_window_web_ui->height - floor($marker_window_web_ui->marker_size/2);
        $this->bottomLeftMarker = new MarkerOnSheet($marker_size_on_sheet_scale, $marker_size_on_sheet_scale, $this->sheet);
        $this->bottomLeftMarker->colNum = $this->sheet->disp->getCellNumX($bottomLeftMarkerPositionX);
        $this->bottomLeftMarker->rowNum = $this->sheet->disp->getCellNumY($bottomLeftMarkerPositionY);

        // set or reset cell size with marker
        $this->cells_width = $this->sheet->cells_width;
        $this->cells_height =$this->sheet->cells_height;
        $this->set_cell_size_on_which_marker_is();
        $this->cells_width = array_map("round2", $this->cells_width);
        $this->cells_height = array_map("round2", $this->cells_height);

        // set various sheet info with marker
        $this->tblwidth = $this->calc_tblwidth();
        $this->tblheight = $this->calc_tblheight();
        $this->col_count =  $this->topRightMarker->colNum - $this->topLeftMarker->colNum - 1;
        $this->row_count =  $this->bottomLeftMarker->rowNum - $this->topLeftMarker->rowNum - 1;


        $this->marker_scale = get_scaling($this->tblwidth, $this->tblheight, 940);

        $this->disp_sheet = new DispSheetCellFitMarker($this);
    }

    public function get_cells_width_array_with_marker() {
        return array_slice_supporting_minus_offset(
            $this->cells_width,
            $this->topLeftMarker->colNum,
            $this->topRightMarker->colNum - ($this->topLeftMarker->colNum) + 1
        );
    }

    public function get_cells_height_array_with_marker() {
        return array_slice_supporting_minus_offset(
            $this->cells_height,
            $this->topLeftMarker->rowNum,
            $this->bottomLeftMarker->rowNum - ($this->topLeftMarker->rowNum) + 1
        );
    }

//// private functions ////////////////////////////////////////////////

    private function calc_tblwidth() {
        $width = $this->topLeftMarker->width;
        foreach (range($this->topLeftMarker->colNum + 1, $this->topRightMarker->colNum - 1) as $i) {
            $width += $this->cells_width[$i];
        }
        $width += $this->topRightMarker->width;

        return $width;
    }

    private function calc_tblheight() {
        $height = $this->topLeftMarker->height;
        foreach (range($this->topLeftMarker->rowNum + 1, $this->bottomLeftMarker->rowNum - 1) as $i) {
            $height += $this->cells_height[$i];
        }
        $height += $this->topRightMarker->height;

        return $height;
    }

    private function set_cell_size_on_which_marker_is() {
        // top left
        if ($this->topLeftMarker->colNum < 0) {
            $this->cells_width[-1] = $this->topLeftMarker->width;
        }
        else {
            // if the marker is inner of the sheet
            $this->cells_width[$this->topLeftMarker->colNum] = $this->topLeftMarker->width;
        }
        if ($this->topLeftMarker->rowNum < 0) {
            $this->cells_height[-1] = $this->topLeftMarker->height;
        }
        else {
            // if the marker is inner of the sheet
            $this->cells_height[$this->topLeftMarker->rowNum] = $this->topLeftMarker->height;
        }

        // top right
        $this->cells_width[$this->topRightMarker->colNum] = $this->topRightMarker->width;

        // bottom left
        $this->cells_height[$this->bottomLeftMarker->rowNum] = $this->bottomLeftMarker->height;
    }
}


//
// マーカーがセルにジャストフィットするシートクラス(表示用)
//
class DispSheetCellFitMarker {
    public $marker_window;      // object to DispMarkerWindow
    public $sheetOnHtmlTable;   // object to sheetOnHtmlTable

    public $size_of_marker;
    private $cells_properties;

    function __construct($sheet_marker) {
        $this->sheetOnHtmlTable = new sheetOnHtmlTable();
        foreach ($sheet_marker->cells_width as $i => $size) {
            $this->sheetOnHtmlTable->cells_width[$i] = floor($size * $sheet_marker->marker_scale);
        }
        foreach ($sheet_marker->cells_height as $i => $size) {
            $this->sheetOnHtmlTable->cells_height[$i] = floor($size * $sheet_marker->marker_scale);
        }
        $this->set_border_for_cells_properties($sheet_marker);
        $this->adjust_width($sheet_marker);
        $this->adjust_height($sheet_marker);
        $this->sheetOnHtmlTable->width = $this->calc_tblwidth($sheet_marker) + $this->sum_of_col_borders($sheet_marker);;
        $this->sheetOnHtmlTable->height = $this->calc_tblheight($sheet_marker) + $this->sum_of_row_borders($sheet_marker);

        $this->size_of_marker = $this->sheetOnHtmlTable->cells_height[$sheet_marker->topLeftMarker->rowNum];

        // マーカーウインドウ
        $this->marker_window = new DispMarkerWindow($this->sheetOnHtmlTable->width, $this->sheetOnHtmlTable->height, 0, 0);

        $this->marker_window->topLeftMarker = new MarkerOnWindow($this->size_of_marker, $this->size_of_marker, 0, 0);
        $this->marker_window->topRightMarker = new MarkerOnWindow($this->size_of_marker, $this->size_of_marker, $this->sheetOnHtmlTable->width - $this->size_of_marker, 0);
        $this->marker_window->bottomLeftMarker = new MarkerOnWindow($this->size_of_marker, $this->size_of_marker, 0, 0);

        $this->marker_window->position_of_sheet_id_from_left_side = floor($this->size_of_marker * 2);
    }

    public function get_cells_width_array_with_marker($sheet_marker) {
        return array_slice_supporting_minus_offset(
            $this->sheetOnHtmlTable->cells_width,
            $sheet_marker->topLeftMarker->colNum,
            $sheet_marker->topRightMarker->colNum - ($sheet_marker->topLeftMarker->colNum) + 1
        );
    }

    public function get_cells_height_array_with_marker($sheet_marker) {
        return array_slice_supporting_minus_offset(
            $this->sheetOnHtmlTable->cells_height,
            $sheet_marker->topLeftMarker->rowNum,
            $sheet_marker->bottomLeftMarker->rowNum - ($sheet_marker->topLeftMarker->rowNum) + 1
        );
    }


//// private functions ////////////////////////////////////////////////

    private function calc_tblwidth($sheet_marker) {
        $width = $this->sheetOnHtmlTable->cells_width[$sheet_marker->topLeftMarker->colNum];
        foreach (range($sheet_marker->topLeftMarker->colNum + 1, $sheet_marker->topRightMarker->colNum - 1) as $i) {
            $width += $this->sheetOnHtmlTable->cells_width[$i];
        }
        $width += $this->sheetOnHtmlTable->cells_width[$sheet_marker->topRightMarker->colNum];

        return $width;
    }

    private function calc_tblheight($sheet_marker) {
        $height = $this->sheetOnHtmlTable->cells_height[$sheet_marker->topLeftMarker->rowNum];
        foreach (range($sheet_marker->topLeftMarker->rowNum + 1, $sheet_marker->bottomLeftMarker->rowNum - 1) as $i) {
            $height += $this->sheetOnHtmlTable->cells_height[$i];
        }
        $height += $this->sheetOnHtmlTable->cells_height[$sheet_marker->bottomLeftMarker->rowNum];

        return $height;
    }

    //
    // http://www.w3.org/TR/CSS2/tables.html#collapsing-borders
    //
    private function sum_of_col_borders($sheet_marker) {
        $left_border_width = $this->half_border_width($sheet_marker);
        $width = 0.0;
        foreach (range($sheet_marker->topLeftMarker->colNum + 1, $sheet_marker->topRightMarker->colNum) as $i) {
            $width += ceil(($left_border_width[$i] + $left_border_width[$i + 1]) / 2);
        }
        return $width;
    }
    private function adjust_width($sheet_marker) {
        $left_border_width = $this->half_border_width($sheet_marker);
        foreach ($sheet_marker->cells_width as $i => $size) {
            $sheetOnHtmlTable->cells_width[$i] = $sheetOnHtmlTable->cells_width[$i] - ceil(($left_border_width[$i] + $left_border_width[$i + 1]) / 2);
        }
    }
    private function half_border_width($sheet_marker) {
        $left_border_width = $this->calc_max_border_width($sheet_marker);
        foreach ($left_border_width as $index => $width) {
            $left_border_width[$index] += ($left_border_width[$index] % 2); // make it even value before be half
            $left_border_width[$index] *= 0.5;
        }
        return $left_border_width;
    }
    private function calc_max_border_width($sheet_marker) {
        $first_col_flag = true;
        foreach ($sheet_marker->cells_height as $row_num => $row_size) {
            $left_border_width[$row_num] = 0;
            foreach ($sheet_marker->cells_width as $col_num => $col_size) {
                if ($first_col_flag == true) {
                    $first_col_flag = false;
                    $left_border_width[$col_num] = max(
                        $left_border_width[$col_num],
                        $this->cells_properties['border_left_width'][$row_num][$col_num]
                    );
                } else {
                    $left_border_width[$col_num] = max(
                        $left_border_width[$col_num],
                        $this->cells_properties['border_right_width'][$row_num][$col_num - 1] +
                        $this->cells_properties['border_left_width'][$row_num][$col_num]
                    );
                }
            }
        }
        $left_border_width[$col_num + 1] = 0;
        foreach ($sheet_marker->cells_width as $col_num => $col_size) {
            $left_border_width[$col_num + 1] = max(
                $left_border_width[$col_num + 1],
                $this->cells_properties['border_right_width'][$row_num][$col_num]
            );
        }
        return $left_border_width;
    }

    private function sum_of_row_borders($sheet_marker) {
        $top_border_height = $this->half_border_height($sheet_marker);
        $height = 0.0;
        foreach (range($sheet_marker->topLeftMarker->rowNum + 1, $sheet_marker->bottomLeftMarker->rowNum) as $i) {
            $height += ceil(($top_border_height[$i] + $top_border_height[$i + 1]) / 2);
        }
        return $height;
    }
    private function adjust_height($sheet_marker) {
        $top_border_height = $this->half_border_height($sheet_marker);
        foreach ($sheet_marker->cells_height as $i => $size) {
            $sheetOnHtmlTable->cells_height[$i] = $sheetOnHtmlTable->cells_height[$i] - ceil(($top_border_height[$i] + $top_border_height[$i + 1]) / 2);
        }
    }
    private function half_border_height($sheet_marker) {
        $top_border_height = $this->calc_max_border_height($sheet_marker);
        foreach ($top_border_height as $index => $height) {
            $top_border_height[$index] += ($top_border_height[$index] % 2); // make it even value before be half
            $top_border_height[$index] *= 0.5;
        }
        return $top_border_height;
    }
    private function calc_max_border_height($sheet_marker) {
        $first_row_flag = true;
        foreach ($sheet_marker->cells_height as $row_num => $row_size) {
            $top_border_height[$row_num] = 0;
            foreach ($sheet_marker->cells_width as $col_num => $col_size) {
                if ($first_row_flag == true) {
                    $first_row_flag = false;
                    $top_border_height[$row_num] = max(
                        $top_border_height[$row_num],
                        $this->cells_properties['border_top_width'][$row_num][$col_num]
                    );
                } else {
                    $top_border_height[$row_num] = max(
                        $top_border_height[$row_num],
                        $this->cells_properties['border_bottom_width'][$row_num - 1][$col_num] +
                        $this->cells_properties['border_top_width'][$row_num][$col_num]
                    );
                }
            }
        }
        $top_border_height[$row_num + 1] = 0;
        foreach ($sheet_marker->cells_width as $col_num => $col_size) {
            $top_border_height[$row_num + 1] = max(
                $top_border_height[$row_num + 1],
                $this->cells_properties['border_bottom_width'][$row_num][$col_num]
            );
        }
        return $top_border_height;
    }

    private function set_border_for_cells_properties($sheet_marker) {
        foreach ($sheet_marker->cells_height as $row_num => $row_size) {
            foreach ($sheet_marker->cells_width as $col_num => $col_size) {
                $this->cells_properties['border_top_width'][$row_num][$col_num] = $this->get_border_width($sheet_marker->sheet->xls, 0, $row_num, $col_num, 'top');
                $this->cells_properties['border_bottom_width'][$row_num][$col_num] = $this->get_border_width($sheet_marker->sheet->xls, 0, $row_num, $col_num, 'bottom');
                $this->cells_properties['border_left_width'][$row_num][$col_num] = $this->get_border_width($sheet_marker->sheet->xls, 0, $row_num, $col_num, 'left');
                $this->cells_properties['border_right_width'][$row_num][$col_num] = $this->get_border_width($sheet_marker->sheet->xls, 0, $row_num, $col_num, 'right');
            }
        }
    }

    private function get_border_width($xls, $sheet_no, $row_num, $col_num, $position) {
        $position_table = array(
            'top'    => 'Tstyle',
            'bottom' => 'Bstyle',
            'left'   => 'Lstyle',
            'right'  => 'Rstyle',
        );
        if (!isset($position_table[$position])) {
            throw new Exception("undefined parameter: $position_table does not have $position keyword");
        }
        $xf = $xls->getAttribute($sheet_no, $row_num, $col_num);
        $xfno = ($xf['xf'] > 0) ? $xf['xf'] : 0;
        $cell = $xls->recXF[$xfno];
        return isset($cell[$position_table[$position]]) ? str_replace('px;', '', $xls->getwidth($cell[$position_table[$position]])) : 0;
    }
}


class Marker {
    public $width;
    public $height;

    function __construct($width, $height) {
        $this->width = $width;
        $this->height = $height;
    }
}

class MarkerOnSheet extends Marker {
    public $colNum; // cell number on which the marker is
    public $rowNum; // cell number on which the marker is

    public $sheet;
    public $disp;   // object to DispMarkerOnSheet

    function __construct($width, $height, $sheet) {
        parent::__construct($width, $height);

        $this->sheet = $sheet;
        $this->disp = new DispMarkerOnSheet($this);
    }
}

class DispMarkerOnSheet {
    public $width;
    public $height;

    function __construct($marker) {
        //$this->width  = floor($marker->width * $marker->sheet->scale);
        //$this->height = floor($marker->height * $marker->sheet->scale);
        $this->width  = $marker->width;
        $this->height = $marker->height;
    }
}

class MarkerOnWindow extends Marker {
    public $position_x;
    public $position_y;

    function __construct($width, $height, $x, $y) {
        parent::__construct($width, $height);

        $this->position_x = $x;
        $this->position_y = $y;
    }
}

// Marker Window
class DispMarkerWindow {    // disp scale
    public $width;
    public $height;
    public $position_x; //top left corner
    public $position_y; //top left corner

    // 3 marker objects
    public $topLeftMarker;  // object to MarkerOnWindow: position assumes top-left corner
    public $topRightMarker; // object to MarkerOnWindow: position assumes top-right corner
    public $bottomLeftMarker;// object to MarkerOnWindow: position assumes bottom-left corner

    // position of IDs
    public $position_of_sheet_id_from_left_side;


    function __construct($width, $height, $position_x, $position_y) {
        $this->width = $width;
        $this->height = $height;
        $this->position_x = $position_x;
        $this->position_y = $position_y;
    }
}

// Marker Window Info from Web UI
class MarkerWindowWebUI {    // disp scale
    public $width;
    public $height;
    public $position_x;
    public $position_y;

    public $marker_size;    // assumes square
    public $scale;

    function __construct($width, $height, $position_x, $position_y, $marker_size, $scale) {
        $this->width = $width;
        $this->height = $height;
        $this->position_x = $position_x;
        $this->position_y = $position_y;
        $this->marker_size = $marker_size;
        $this->scale = $scale;
    }
}

class sheetOnHtmlTable {
    public $width;
    public $height;

    public $cells_width;    //array
    public $cells_height;   //array

    function __construct() {
    }

    public function get_row_size($row) {
        return $this->cells_height[$row];
    }

    public function get_col_size($col) {
        return $this->cells_width[$col];
    }

}

//////////////////////////////////////////////////////////////////////
//
// utility functions
//

function round2($val) {
        return round($val, 2);
}

function array_slice_supporting_minus_offset($a, $offset, $length) {
    $result = array();
    $remaining_length = $length;
    $current_position = $offset;
    if ($offset < 0 && $length > 0) {
        for (;;) {
            if ($remaining_length <= 0) {
                break;
            }
            array_push($result, $a[$current_position]);
            $current_position++;
            $remaining_length--;
        }
    }
    if ($remaining_length > 0) {
        $result = array_merge($result, array_slice($a, $current_position, $remaining_length));
    }
    return $result;
}

/* vim: set et fenc=utf-8 ff=unix sts=4 sw=4 ts=4 : */
?>
