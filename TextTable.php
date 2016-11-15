<?php
namespace kabachello\phpTextTable;

/**
 *
 * @author Andrej Kabachnik
 *        
 */
class TextTable
{

    const COLUMN_ALIGNMENT_LEFT = 'left';

    const COLUMN_ALIGNMENT_RIGHT = 'right';

    const COLUMN_ALIGNMENT_CENTER = 'center';

    /**
     *
     * @var array The Column index of keys
     */
    private $keys = array();

    /**
     *
     * @var array The array for processing
     */
    private $rows;

    /**
     *
     * @var array The column width settings
     */
    private $column_widths = array();

    /**
     *
     * @var int Max column width (chars)
     */
    private $column_width_max = array();

    /**
     *
     * @var boolean
     */
    private $column_width_auto = true;

    /**
     *
     * @var array
     */
    private $column_alignments = array();

    /**
     *
     * @var int Max row height within a column (lines)
     */
    private $row_height_max = null;

    /**
     *
     * @var array The Row lines settings
     */
    private $row_heights = array();

    /**
     *
     * @var boolean
     */
    private $row_height_auto = true;

    /**
     *
     * @var boolean
     */
    private $print_header = false;

    /**
     *
     * @var string
     */
    private $separator_crossing = "+";

    /**
     *
     * @var string
     */
    private $separator_row = "-";

    /**
     *
     * @var string
     */
    private $separator_column = "|";

    /**
     *
     * @param array $rows            
     */
    public function __construct(array $rows)
    {
        $this->setRows($rows);
    }

    /**
     * Returns an array of keys, which will be used for the header
     *
     * @return array
     */
    public function getKeys()
    {
        return $this->keys;
    }

    /**
     * Sets the header values
     *
     * @param array $array_of_keys            
     * @return \kabachello\phpTextTable\TextTable
     */
    public function setKeys(array $array_of_keys)
    {
        $this->keys = $array_of_keys;
        return $this;
    }

    /**
     * Returns the array to be printed
     *
     * @return array
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Sets the array to be printed
     *
     * @param array $rows            
     * @return \kabachello\phpTextTable\TextTable
     */
    public function setRows(array $rows)
    {
        $this->rows = $rows;
        $this->setKeys(array_keys($this->rows[0]));
        return $this;
    }

    /**
     * Calculates the dimensions of the table
     *
     * @return TextTable
     */
    protected function calculateDimensions()
    {
        foreach ($this->getRows() as $x => $row) {
            foreach ($row as $y => $value) {
                $this->calculateDimensionsForCell($x, $y, $value);
            }
        }
        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function getPrintHeader()
    {
        return $this->print_header;
    }

    /**
     *
     * @param boolean $value            
     * @return \kabachello\phpTextTable\TextTable
     */
    public function setPrintHeader($value)
    {
        $this->print_header = $value ? true : false;
        return $this;
    }

    /**
     * Prints the data to a text table
     *
     * @return string
     */
    public function print($row_key)
    {
        $this->calculateDimensions();
        $result = '';
        
        if ($this->getPrintHeader()) {
            $result .= $this->printHeader();
        } else {
            $result .= $this->printRowLine();
        }
        
        if (! is_null($row_key)) {
            $result .= $this->printRow($row_key, $this->getRows()[$row_key]);
        } else {
            foreach ($this->getRows() as $key => $data) {
                $result .= $this->printRow($key, $data);
            }
        }
        
        $result .= $this->printRowLine(false);
        
        return $result;
    }

    protected function printRowLine($append_newline = true)
    {
        $result = '';
        if ($this->getSeparatorRow()) {
            $result .= $this->getSeparatorCrossing();
            foreach ($this->getColumnWidths() as $val) {
                $result .= $this->getSeparatorRow() . str_pad('', $val, $this->getSeparatorRow(), STR_PAD_RIGHT) . $this->getSeparatorRow() . $this->getSeparatorCrossing();
            }
        }
        
        if ($append_newline)
            $result .= "\n";
        
        return $result;
    }

    protected function printHeader()
    {
        $result = '';
        
        // Render the header contents
        $header_data = array();
        foreach ($this->getKeys() as $value) {
            $this->calculateDimensionsForCell(false, $value, $value);
            $header_data[$value] = strtoupper($value);
        }
        
        $result .= $this->printRowLine();
        $result .= $this->getSeparatorColumn();
        foreach ($this->getColumnWidths() as $key => $val) {
            $result .= ' ' . str_pad($header_data[$key], $val, ' ', STR_PAD_BOTH) . ' ' . $this->getSeparatorColumn();
        }
        $result .= "\n";
        $result .= $this->printRowLine();
        
        return $result;
    }

    protected function printRow($row_key, array $row_data)
    {
        $result = '';
        for ($line = 1; $line <= $this->row_heights[$row_key]; $line ++) {
            $result .= $this->getSeparatorColumn();
            foreach ($row_data as $field => $value) {
                switch (mb_strtolower($this->getColumnAlignments()[$field])) {
                    case self::COLUMN_ALIGNMENT_CENTER:
                        $strpad_options = STR_PAD_BOTH;
                        break;
                    case self::COLUMN_ALIGNMENT_RIGHT:
                        $strpad_options = STR_PAD_LEFT;
                        break;
                    default:
                        $strpad_options = STR_PAD_RIGHT;
                }
                $result .= " ";
                $result .= str_pad(substr($value, ($this->getColumnWidths()[$field] * ($line - 1)), $this->getColumnWidths()[$field]), $this->getColumnWidths()[$field], ' ', $strpad_options);
                $result .= " " . $this->getSeparatorColumn();
            }
            $result .= "\n";
        }
        return $result;
    }

    protected function calculateDimensionsForCell($row_key, $column_key, $value)
    {
        $width = mb_strlen($value);
        $width_max = $this->getColumnWidthMax()[$column_key];
        $height = 1;
        if (! is_null($width_max)) {
            if ($width > $width_max) {
                $height = is_null($width_max) ? 1 : ceil($width % $width_max);
                if ($height > $this->getRowHeightMax()) {
                    $height = $this->getRowHeightMax();
                }
                $width = $width_max;
            } else {
                if (! $this->getColumnWidthAuto()) {
                    $width = $width_max;
                }
            }
        }
        
        if (! isset($this->column_widths[$column_key]) || $this->column_widths[$column_key] < $width) {
            $this->column_widths[$column_key] = $width;
        }
        
        if ($row_key !== false && (! isset($this->row_heights[$row_key]) || ($this->getRowHeightAuto() && $this->row_heights[$row_key] < $height))) {
            $this->row_heights[$row_key] = $height;
        }
        return $this;
    }

    public function getColumnWidthMax()
    {
        return $this->column_width_max;
    }

    public function getColumnWidthAuto()
    {
        return $this->column_width_auto;
    }

    public function setColumnWidthAuto($value)
    {
        $this->column_width_auto = $value;
        return $this;
    }

    public function getRowHeightAuto()
    {
        return $this->row_height_auto;
    }

    public function setRowHeightAuto($value)
    {
        $this->row_height_auto = $value;
        return $this;
    }

    public function getColumnWidths()
    {
        return $this->column_widths;
    }

    public function setColumnWidthMax($array_or_int)
    {
        if (is_array($array_or_int)) {
            $this->column_width_max = array_merge($this->column_width_max, $array_or_int);
        } else {
            foreach ($this->getKeys() as $key) {
                $this->column_width_max[$key] = $array_or_int;
            }
        }
        return $this;
    }

    public function getColumnAlignments()
    {
        return $this->column_alignments;
    }

    public function setColumnAlignments(array $array)
    {
        $this->column_alignments = array_merge($this->column_alignments, $array);
        return $this;
    }

    public function getRowHeights()
    {
        return $this->row_heights;
    }

    public function setRowHeights($array_or_int)
    {
        if (is_array($array_or_int)) {
            $this->row_heights = array_merge($this->row_heights, $array_or_int);
        } else {
            foreach (array_keys($this->getRows()) as $key) {
                $this->row_heights[$key] = $array_or_int;
            }
        }
        return $this;
    }

    public function getRowHeightMax()
    {
        return $this->row_height_max;
    }

    public function setRowHeightMax($value)
    {
        $this->row_height_max = $value;
        return $this;
    }

    public function getSeparatorCrossing()
    {
        return $this->separator_crossing;
    }

    public function setSeparatorCrossing($value)
    {
        $this->separator_crossing = $value;
        return $this;
    }

    public function getSeparatorRow()
    {
        return $this->separator_row;
    }

    public function setSeparatorRow($value)
    {
        $this->separator_row = $value;
        return $this;
    }

    public function getSeparatorColumn()
    {
        return $this->separator_column;
    }

    public function setSeparatorColumn($value)
    {
        $this->separator_column = $value;
        return $this;
    }
}

?>
