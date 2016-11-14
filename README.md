# phpTextTable
Easily convert PHP arrays into plain text tables or strings with a fixed field length for each column

## Installation
```
composer require kabachello/phptexttable:*
```

## Quick start
```php
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
$text_table->set_column_alignments(array('Pop. 1950' => 'right', 'Pop. 2015' => 'right'));

// Set the numer columns to the same fixed with
$text_table->set_column_width_max(array('Pop. 1950' => 11, 'Pop. 2015' => 11));

// Disable automatic width adjustment (only affects columns with max width)
$text_table->set_column_width_auto(false);

// Print the table
print $text_table->print($row_key);
```

will print the following:
```
+---------+------------------------+-------------+-------------+
|  CITY   |         STATE          |  POP. 1950  |  POP. 2015  |
+---------+------------------------+-------------+-------------+
| Berlin  | Berlin                 |     3336026 |     3520031 |
| Hamburg | Hamburg                |     1605606 |     1787408 |
| Munich  | Bavaria                |      831937 |     1450381 |
| Cologne | North Rhine-Westphalia |      594941 |     1060582 |
+---------+------------------------+-------------+-------------+
```

## Credits
This library was inspired by [ArrayToTextTable](https://gist.github.com/tony-landis/31477)