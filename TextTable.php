<?php namespace kabachello\phpTextTable;

/**
 * 
 * @author Andrej Kabachnik
 *
 */
class TextTable {
	const COLUMN_ALIGNMENT_LEFT = 'left';
	const COLUMN_ALIGNMENT_RIGHT = 'right';
	const COLUMN_ALIGNMENT_CENTER = 'center';
	
	/**
	 * @var array The Column index of keys
	 */
	private $keys = array();
    /** 
     * @var array The array for processing
     */
    private $rows;
    /** 
     * @var array The column width settings
     */
    private $column_widths = array();
    /**
     * @var int Max column width (chars)
     */
    private $column_width_max = array();
    /**
     * @var boolean
     */
    private $column_width_auto = true;
    /**
     * @var array
     */
    private $column_alignments = array();
    /**
     * @var int Max row height within a column (lines)
     */
    private $row_height_max = null; 
    /**
     * @var array The Row lines settings
     */
    private $row_heights = array();
    /**
     * @var boolean 
     */
    private $row_height_auto = true;
    /**
     * @var boolean
     */
    private $print_header  = false;
    /**
     * @var string
     */
    private $separator_crossing  = "+";
    /**
     * @var string
     */
    private $separator_row  = "-";
    /**
     * @var string
     */
    private $separator_column  = "|";
    
    
    /**
     * 
     * @param array $rows
     */
    public function __construct(array $rows) {
        $this->set_rows($rows);
    }
    
    /**
     * Returns an array of keys, which will be used for the header
     * @return array
     */
    public function get_keys() {
    	return $this->keys;
    }
    
    /**
     * Sets the header values
     * @param array $array_of_keys
     * @return \kabachello\phpTextTable\TextTable
     */
    public function set_keys(array $array_of_keys) {
    	$this->keys = $array_of_keys;
    	return $this;
    }
    
    /**
     * Returns the array to be printed
     * @return array
     */
    public function get_rows(){
    	return $this->rows;
    }
    
    /**
     * Sets the array to be printed
     * @param array $rows
     * @return \kabachello\phpTextTable\TextTable
     */
    public function set_rows(array $rows){
    	$this->rows = $rows;
    	$this->set_keys(array_keys($this->rows[0]));
    	return $this;
    }
    
    /**
     * Calculates the dimensions of the table
     * @return TextTable
     */
    protected function calculate_dimensions(){
    	foreach ($this->get_rows() as $x => $row){
    		foreach ($row as $y => $value){
    			$this->calculate_dimensions_for_cell($x, $y, $value);
    		}
    	}  
    	return $this;
    }
    
    /**
     * 
     * @return boolean
     */
    public function get_print_header() {
    	return $this->print_header;
    }
    
    /**
     * 
     * @param boolean $value
     * @return \kabachello\phpTextTable\TextTable
     */
    public function set_print_header($value) {
    	$this->print_header = $value ? true : false;
    	return $this;
    }   
    
    /**
     * Prints the data to a text table
     * 
     * @return string
     */
    public function print($row_key) {
    	$this->calculate_dimensions();
    	$result = '';
    	
    	if ($this->get_print_header()){
	        $result .= $this->print_header();
    	} else {
    		$result .= $this->print_row_line();
    	}
        
        if (!is_null($row_key)){
        	$result .= $this->print_row($row_key, $this->get_rows()[$row_key]);
        } else {
        	foreach ($this->get_rows() as $key => $data){
        		$result .= $this->print_row($key, $data);
        	}
        }
        
        $result .= $this->print_row_line(false);
        
        return $result;
    }
    
    protected function print_row_line($append_newline = true) {
    	$result = '';
        $result .= $this->get_separator_crossing();
        foreach($this->get_column_widths() as $key => $val){
            $result .= $this->get_separator_row() .
                str_pad('', $val, $this->get_separator_row(), STR_PAD_RIGHT) .
                $this->get_separator_row() .
                $this->get_separator_crossing();
        }
        
        if($append_newline) $result .= "\n";
        
        return $result;
    }
    
    protected  function print_header() {
    	$result = '';
    	
    	// Render the header contents
    	$header_data = array();
    	foreach($this->get_keys() as $value) {
    		$this->calculate_dimensions_for_cell(false, $value, $value);
    		$header_data[$value] = strtoupper($value);
    	}
        
    	$result .= $this->print_row_line();
        $result .= $this->get_separator_column();
        foreach($this->get_column_widths() as $key => $val){
            $result .= ' '.
                str_pad($header_data[$key], $val, ' ', STR_PAD_BOTH) .
                ' ' .
                $this->get_separator_column();
        }
        $result .= "\n";
        $result .= $this->print_row_line();
        
        return $result;
    }
    
    protected function print_row($row_key, array $row_data) {
        $result = '';
        for($line = 1; $line <= $this->row_heights[$row_key]; $line++) {
            $result .= $this->get_separator_column(); 
            foreach ($row_data as $field => $value){
            	switch (mb_strtolower($this->get_column_alignments()[$field])){
            		case self::COLUMN_ALIGNMENT_CENTER: $strpad_options = STR_PAD_BOTH; break;
            		case self::COLUMN_ALIGNMENT_RIGHT: $strpad_options = STR_PAD_LEFT; break;
            		default: $strpad_options = STR_PAD_RIGHT;
            	}
                $result .= " ";
                $result .= str_pad(substr($value, ($this->get_column_widths()[$field] * ($line-1)), $this->get_column_widths()[$field]), $this->get_column_widths()[$field], ' ', $strpad_options);
                $result .= " " . $this->get_separator_column();          
            }  
            $result .=  "\n";
        }
        return $result;
    }
    
    protected function calculate_dimensions_for_cell($row_key, $column_key, $value) { 
        $width = mb_strlen($value);
        $width_max = $this->get_column_width_max()[$column_key];
        $height = 1;
        if (!is_null($width_max)){
        	if($width > $width_max) {
	            $height = is_null($width_max) ? 1 : ceil($width % $width_max);
	            if($height > $this->get_row_height_max()) {
	            	$height = $this->get_row_height_max();
	            }
	            $width = $width_max;
        	} else {
        		if (!$this->get_column_width_auto()){
        			$width = $width_max;
        		}
        	}
        }
 
        if(!isset($this->column_widths[$column_key]) || $this->column_widths[$column_key] < $width){
        	$this->column_widths[$column_key] = $width;
        }
  
        if($row_key !== false && (!isset($this->row_heights[$row_key]) || ($this->get_row_height_auto() && $this->row_heights[$row_key] < $height))){
        	$this->row_heights[$row_key] = $height;
        }
        return $this;
    }
    
    public function get_column_width_max() {
    	return $this->column_width_max;
    }
    
    public function get_column_width_auto() {
    	return $this->column_width_auto;
    }
    
    public function set_column_width_auto($value) {
    	$this->column_width_auto = $value;
    	return $this;
    }  
    
    public function get_row_height_auto() {
    	return $this->row_height_auto;
    }
    
    public function set_row_height_auto($value) {
    	$this->row_height_auto = $value;
    	return $this;
    }
    
    public function get_column_widths() {
    	return $this->column_widths;
    }
    
    public function set_column_width_max($array_or_int) {
    	if (is_array($array_or_int)){
    		$this->column_width_max = array_merge($this->column_width_max, $array_or_int);
    	} else {
    		foreach ($this->get_keys() as $key){
    			$this->column_width_max[$key] = $array_or_int;
    		}
    	}
    	return $this;
    }
    
    public function get_column_alignments(){
    	return $this->column_alignments;
    }
    
    public function set_column_alignments(array $array) {
    	$this->column_alignments = array_merge($this->column_alignments, $array);
    	return $this;
    }
    
    public function get_row_heights() {
    	return $this->row_heights;
    }
    
    public function set_row_heights($array_or_int) {
    	if (is_array($array_or_int)){
    		$this->row_heights = array_merge($this->row_heights, $array_or_int);
    	} else {
    		foreach (array_keys($this->get_rows()) as $key){
    			$this->row_heights[$key] = $array_or_int;
    		}
    	}
    	return $this;
    } 
    
    public function get_row_height_max() {
    	return $this->row_height_max;
    }
    
    public function set_row_height_max($value) {
    	$this->row_height_max = $value;
    	return $this;
    }  
    
    public function get_separator_crossing() {
    	return $this->separator_crossing;
    }
    
    public function set_separator_crossing($value) {
    	$this->separator_crossing = $value;
    	return $this;
    }
    
    public function get_separator_row() {
    	return $this->separator_row;
    }
    
    public function set_separator_row($value) {
    	$this->separator_row = $value;
    	return $this;
    }
    
    public function get_separator_column() {
    	return $this->separator_column;
    }
    
    public function set_separator_column($value) {
    	$this->separator_column = $value;
    	return $this;
    }    
      
}

?>
