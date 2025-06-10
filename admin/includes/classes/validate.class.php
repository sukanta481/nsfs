<?php
/**
* Base validation class
*
* @author    Sven Wagener <sven.wagener@intertribe.de>
* @copyright Sven Wagener
* @include 	 Funktion:_include_
*/
class validate{
	var $allow_letters;
	var $allow_letters_de;
	var $allow_nums;
	var $allow_punctations;
	var $allow_specialchars;
	var $allow_sql;
	var $allow_whitespaces;
	
	var $min_length;
	var $max_length;
	
	var $punctations="\.\,\:\;\?\!\(\)\"\'"; // Allowed punctation marks
	var $special_chars="-\+\*\~\#\^\$\�\%\�"; // Allowed special chars
	var $format_chars=" 0123456789-LN.,:;@/\\=_()"; // Chars which are allowed in format pattern
	
	var $format;
	var $is_format=false;
	var $format_regex;
	var $regex;
	
	var $permitted_chars="^��`*~'#";
	
	var $permitted_words=array();
	
	var $country_chars=array(
	'de'=>'a-zA-Z�������',
	'fr'=>'a-zA-Z����������'
	);
	
	var $sql_alerts=array(
	'select',
	'delete',
	'update',
	'insert',
	'into',
	'drop',
	'from',
	'where'
	);
	
	var $sql_statements=array(
	'select from',
	'delete from',
	'update set',
	'insert into',
	'drop table'
	);
	
	
	/**
	* The constructor of the validation class
	* @desc The constructor of the validation class
	*/
	function __construct(){
		$this->letters_off();
		$this->nums_off();
		$this->punctations_off();
		$this->specialchars_off();
		$this->sql_off();
		$this->whitespaces_off();
	}
	
	/**
	* Allow letters
	* @desc Use function to allow Letters in string
	*/
	function letters_on(){
		$this->allow_letters=true;
	}
	
	/**
	* Disallow letters
	* @desc Use function to disallow Letters in string
	*/
	function letters_off(){
		$this->allow_letters=false;
	}
	
	/**
	* Allow letters
	* @desc Use function to allow Letters in string
	*/
	function letters_de_on(){
		$this->allow_letters_de=true;
	}
	
	/**
	* Disallow letters
	* @desc Use function to disallow Letters in string
	*/
	function letters_de_off(){
		$this->allow_letters_de=false;
	}
	
	/**
	* Allow numbers
	* @desc Use function to allow Letters in string
	*/
	function nums_on(){
		$this->allow_nums=true;
	}
	
	/**
	* Disallow numbers
	* @desc Use function to disallow Letters in string
	*/
	function nums_off(){
		$this->allow_nums=false;
	}
	
	/**
	* Allow punctation marks
	* @desc Use function to allow punctation marks in string
	*/
	function punctations_on(){
		$this->allow_punctations=true;
	}
	
	/**
	* Disallow punctation marks
	* @desc Use function to disallow punctation marks in string
	*/
	function punctations_off(){
		$this->allow_punctations=false;
	}
	
	/**
	* Allow special chars
	* @desc Use function to allow special chars in string
	*/
	function specialchars_on(){
		$this->allow_specialchars=true;
	}
	
	/**
	* Disallow special chars
	* @desc Use function to disallow special chars in string
	*/
	function specialchars_off(){
		$this->allow_specialchars=false;
	}
	
	/**
	* Allow sql statements
	* @desc Use function to allow sql statements in string
	*/
	function sql_on(){
		$this->allow_sql=true;
	}
	
	/**
	* Disallow sql statements
	* @desc Use function to disallow sql statements in string
	*/
	function sql_off(){
		$this->allow_sql=false;
	}
	
	/**
	* Allow whitespaces
	* @desc Use function to allow whitespaces in string
	*/	
	function whitespaces_on(){
		$this->allow_whitespaces=true;
	}
	
	/**
	* Disallow whitespaces
	* @desc Use function to disallow whitespaces in string
	*/		
	function whitespaces_off(){
		$this->allow_whitespaces=false;
	}
	
	/**
	* Set length of string
	* @param int $min Minimum length of string
	* @param int $max Maximum length of string
	* @desc Set length of string
	*/
	function length($min=0,$max=""){
		$this->min_length=$min;
		$this->max_length=$max;
	}
	
	/**
	* Setting format for string
	* @param string $format
	* @return boolean $ok Returns false if formatting string isn't correct
	* @desc Setting format for for string. L=Letters N=Numbers E.g. 3-20L means 3 till 20 Letters. 5N means 5 numbers (e.g. german postal code).
	*/
	function format($format){
		$this->is_format=true;
		$regnum=0;
		$counter="";
		
		// Run each chars of format string
		$strlen=strlen($format);
		for($i=0;$i<$strlen;$i++){
			$char=$format[$i]; // Getting actual char
			
			switch($char){
				// Casing for LETTERS
				case 'L':
				$type="LETTER";
				if($last_type=="" || $last_type=="LETTER" || $last_type=="NUMBER"){
					$this->format_regex[$regnum++]="[a-zA-Z]{1,1}";
				}else if($last_type=="COUNTER"){
					if(!$fromtill){
						$this->format_regex[$regnum++]="[a-zA-Z]\{$counter_from,$counter_from}";
					}else{
						$this->format_regex[$regnum++]="[a-zA-Z]\{$counter_from,$counter_till}";
						$fromtill=false;
					}
				}
				break;
				// Casing for NUMBERS
				case 'N':
				$type="NUMBER";
				if($last_type=="" || $last_type=="NUMBER" || $last_type=="LETTER"){
					$this->format_regex[$regnum++]="[0-9]{1,1}";
				}else if($last_type=="COUNTER"){
					if(!$fromtill){
						$this->format_regex[$regnum++]="[0-9]\{$counter_from,$counter_from}";
					}else{
						$this->format_regex[$regnum++]="[0-9]\{$counter_from,$counter_till}";
						$fromtill=false;
					}
				}
				break;
				
				// Casing for FROMTILL
				case '-':
				$type="FROMTILL";
				if($last_type!="COUNTER"){
					$last_type="";
					$this->format_regex[$regnum++]="[\-]{1,1}";
				}/*else{
				$this->format_regex[$regnum++]="[\-]\{$counter_from,$counter_till}";
				}*/
				break;
				
				
				// Otherwise do this
				default:
				// If char is allowed in formatting string
				if($this->is_format_char($char)){
					
					// If char is a number
					if(is_numeric($char)){
						$type="COUNTER";
						if($last_type=="COUNTER"){
							$counter.=$char;
							if(!$fromtill){
								$counter_from=$counter;
							}else{
								$counter_till=$counter;
							}
						}else if($last_type=="FROMTILL"){
							$counter=$char;
							$counter_till=$counter;
							$fromtill=true;
							
						}else{
							$counter=$char;
							$counter_from=$counter;
						}
						
						break;
					}else{
						$type="CHAR";
						if($last_type=="" || $last_type=="NUMBER" || $last_type=="LETTER" || $last_type== "CHAR"){
							$this->format_regex[$regnum++]="[\$char]{1,1}";
						}else if($last_type=="COUNTER"){
							if(!$fromtill){
								$this->format_regex[$regnum++]="[\$char]\{$counter_from,$counter_from}";
							}else{
								$this->format_regex[$regnum++]="[\$char]\{$counter_from,$counter_till}";
								$fromtill=false;
							}
						}
						break;
					}
				}else{
					return false;
					break;
				}
				
			}
			//echo '<pre>';
			//print_r($this->format_regex);
			//echo '</pre>';
			//echo $last_type;
			$last_type=$type;
			
			
		}
	}
	
	/**
	* Checks if char is is allowed in formatting string
	* @param char $char Char which have to be checked
	* @return boolean $ok Returns true if char is allowed, otherwise false
	* @desc Checks if char is is allowed in formatting string
	*/
	function is_format_char($char){
		if(strlen($char)>1){
			return false;
		}
		// Checking if char is wrong
		$char_matched=false;
		for($j=0;$j<strlen($this->format_chars);$j++){
			$format_char=$this->format_chars[$j];
			if($format_char==$char){
				$char_matched=true;
			}
		}
		//echo  $char_matched;
		return $char_matched;
	}
	
	/**
	* Checks a string by the set rules
	* @param char $string String which have to be checked
	* @return boolean $ok Returns true if char is allowed, otherwise false
	* @desc Checks a string by the set rules
	*/
	function check($string){
		
		if($this->is_format){
			//echo $this->get_regex();
			return preg_match($this->get_regex(),$string);
		}else{
			if($this->allow_nums){
				$regex.="0-9";
			}
			if($this->allow_letters){
				$regex.="a-zA-Z";
			}
			if($this->allow_letters_de){
				$regex.="�������";
			}
			if($this->allow_punctations){
				$regex.=$this->punctations;
			}
			if($this->allow_specialchars){
				$regex.=$this->special_chars;
			}
			if($this->allow_whitespaces){
				$regex.="\ ";
			}
			$regex.="\r\n";
			
			$found_sql=false;
			if(!$this->allow_sql){
				// Searching for SQL statements
				$string_array=explode("[\ ]",strtolower($string));
				$j=0;
				// Check ing all words of string
				for($i=0;$i<count($string_array);$i++){
					// If word is in sql blacklist
					if(in_array($string_array[$i],$this->sql_alerts)){
						$sql_words[$j++]=$string_array[$i];
						
						// Checking if get words could be an sql statement
						$sql="";
						for($k=0;$k<count($sql_words);$k++){
							if($k==0){
								$sql.=$sql_words[$k];
							}else{
								$sql.=" ".$sql_words[$k];
							}
							if(in_array($sql,$this->sql_statements)){
								$found_sql=true;
								$sql="";
							}
						}
					}
				}
			}
			
			if($found_sql){
				return false;
			}else{
				if($this->min_length!="" && $this->max_length!=""){
					$regex="^[$regex]{".$this->min_length.",".$this->max_length."}$^";
				}else if($this->min_length!=""){
					$regex="^[$regex]{".$this->min_length.",}$^";
				}else if($this->max_length!=""){
					$regex="^[$regex]{0,".$this->max_length."}$^";
				}else{
					$regex="^[$regex]*$^";
				}
			}
			
			return preg_match($regex,$string);
		}
	}
	
	
	/**
	* Returns the regex pattern string of set rules
	* @return string $regex The regex pattern string
	* @desc Returns the regex pattern string
	*/
	function get_regex(){
		$regex="^";
		$numreg=count($this->format_regex);
		for($i=0;$i<$numreg;$i++){
			
			$regex.=$this->format_regex[$i];
		}
		$regex.="$^";
		$reg=stripslashes($regex);
		return $reg;
	}
	
}
?>