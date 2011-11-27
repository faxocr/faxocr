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

// Encoding setting
mb_internal_encoding($charset);

//
// Initialization
//
$header_opt = "";
$body_opt = "";
$errmsg = "";
$xls = null;

if (isset($_REQUEST['file']))
	$file_id = $_REQUEST['file'];

?>
