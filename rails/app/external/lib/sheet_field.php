<?php

/*
 * get col/row span info from $_REQUEST["field-\d+-\d+-\d+-\d+"]
 */
function get_span_info_from_field_ids($REQUEST) {
	// result["colspan"][row][col]
	// result["rowspan"][row][col]
	$result = array();

	$request_keys = array_keys($REQUEST);
	sort($request_keys);
	foreach ($request_keys as $item) {
		if (preg_match("/field-(\d+)-(\d+)-(\d+)-(\d+)/", $item, $loc) != 1) {
			continue;
		}
		if ($loc && $loc[0]) {
			$field_array_row[$loc[2]][$loc[3]] = 0;
			$field_array_col[$loc[3]][$loc[2]] = 0;
		}
	}
	foreach (array_keys($field_array_row) as $row) {
		$span_info = get_spans(array_keys($field_array_row[$row]));
		foreach ($span_info as $span) {
			if ($span[1] === 1) {
				continue;
			}
			$result["colspan"][$row][$span[0]] = $span[1];
		}
	}
	foreach (array_keys($field_array_col) as $col) {
		$span_info = get_spans(array_keys($field_array_col[$col]));
		foreach ($span_info as $span) {
			if ($span[1] === 1) {
				continue;
			}
			$result["rowspan"][$span[0]][$col] = $span[1];
		}
	}

	var_dump($result);
	return $result;
}

function test_get_span_info_from_field_ids() {
	$data = array(
		"field-0-1-1-1" => 0,
	);
	assert(get_span_info_from_field_ids($data) === array(
	));

	$data = array(
		"field-0-1-1-1" => 0,
		"field-0-1-2-1" => 0,
	);
	assert(get_span_info_from_field_ids($data) === array(
		"colspan" => array(
			1 => array(
				1 => 2
				)
			)
		)
	);

	$data = array(
		"field-0-1-1-1" => 0,
		"field-0-2-1-1" => 0,
	);
	assert(get_span_info_from_field_ids($data) === array(
		"rowspan" => array(
			1 => array(
				1 => 2
				)
			)
		)
	);

	$data = array(
		"field-0-1-1-1" => 0,
		"field-0-1-2-1" => 0,
		"field-0-1-3-1" => 0,
	);
	assert(get_span_info_from_field_ids($data) === array(
		"colspan" => array(
			1 => array(
				1 => 3
				)
			)
		)
	);

	$data = array(
		"field-0-0-1-1" => 0,
		"field-0-0-2-1" => 0,
		"field-0-0-3-1" => 0,
		"field-0-1-1-1" => 0,
		"field-0-1-2-1" => 0,
		"field-0-1-3-1" => 0,
	);
	assert(get_span_info_from_field_ids($data) === array(
		"colspan" => array(
			0 => array(
				1 => 3
				)
			),
		"rowspan" => array(
			0 => array(
				1 => 2
				)
			)
		)
	);

	$data = array(
		"field-0-1-1-1" => 0,
		"field-0-1-2-1" => 0,
		"field-0-1-3-1" => 0,

		"field-0-3-2-1" => 0,
		"field-0-3-3-1" => 0,
		"field-0-3-4-1" => 0,
	);
	assert(get_span_info_from_field_ids($data) === array(
		"colspan" => array(
			1 => array(
				1 => 3
				),
			3 => array(
				2 => 3
				)
			)
		)
	);

	$data = array(
		"field-0-3-3-1" => 0,
		"field-0-1-3-1" => 0,
		"field-0-1-1-1" => 0,
		"field-0-1-2-1" => 0,
		"field-0-3-4-1" => 0,
		"field-0-3-2-1" => 0,
	);
	assert(get_span_info_from_field_ids($data) === array(
		"colspan" => array(
			1 => array(
				1 => 3
				),
			3 => array(
				2 => 3
				)
			)
		)
	);
}

/*
 * get a col/row span info from row or col array
 *
 * field_data: array
 */
function get_spans($field_data) {
	$start_x = -1;
	$prev_x = 0;
	$span_value = 1;
	$result = array();

	if ($field_data === array()) {
		return $result;
	}

	foreach ($field_data as $x) {
		if ($start_x === -1) {
			$start_x = $x;
			$prev_x = $x;
			continue;
		}
		if ($x === $prev_x + 1) {
			$span_value += 1;
			$prev_x = $x;
		} else {
			array_push($result, array($start_x, $span_value));
			$start_x = $x;
			$prev_x = $x;
			$span_value = 1;
		}
	}
	array_push($result, array($start_x, $span_value));

	return $result;
}

function test_get_spans() {
	$data = array(0, 1, 2, 3, 4, 6, 8, 9, 10);
	assert(get_spans($data) === array(array(0, 5), array(6, 1), array(8, 3)));

	$data = array();
	assert(get_spans($data) === array());

	$data = array(5);
	assert(get_spans($data) === array(array(5, 1)));

	$data = array(5, 6);
	assert(get_spans($data) === array(array(5, 2)));

	$data = array(5, 6, 7);
	assert(get_spans($data) === array(array(5, 3)));

	$data = array(5, 7);
	assert(get_spans($data) === array(array(5, 1), array(7, 1)));

	$data = array(5, 7, 8);
	assert(get_spans($data) === array(array(5, 1), array(7, 2)));

	$data = array(5, 6, 8);
	assert(get_spans($data) === array(array(5, 2), array(8, 1)));

	$data = array(5, 6, 8, 9);
	assert(get_spans($data) === array(array(5, 2), array(8, 2)));
}

?>
