<?php

require_once '..' . DIRECTORY_SEPARATOR . 'TextTable.php';
error_reporting(E_ALL & ~E_NOTICE);

$rows = array(
		array('City' => 'Berlin', 'State' => 'Berlin', 'Pop. 1950' => 3336026, 'Pop. 2015' => 3520031),
		array('City' => 'Hamburg', 'State' => 'Hamburg', 'Pop. 1950' => 1605606, 'Pop. 2015' => 1787408),
		array('City' => 'Munich', 'State' => 'Bavaria', 'Pop. 1950' => 831937, 'Pop. 2015' => 1450381),
		array('city' => 'Cologne', 'State' => 'North Rhine-Westphalia', 'Pop. 1950' => 594941, 'Pop. 2015' => 1060582)
);

// Initialize the table
$text_table = new kabachello\phpTextTable\TextTable($rows);

// Set right alignment for the numeric columns
$text_table->setColumnAlignments(array('Pop. 1950' => 'right', 'Pop. 2015' => 'right'));

// Set the numer columns to the same fixed with
$text_table->setColumnWidthMax(array('Pop. 1950' => 11, 'Pop. 2015' => 11));

// Disable automatic width adjustment (only affects columns with max width)
$text_table->setColumnWidthAuto(false);

// Print the table
print $text_table->print($row_key);

?>