<?php
session_start();
require 'admin/includes/define.php';
//$PHP_SELF = (isset($_SERVER['PHP_SELF'])? $_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
require  'admin/includes/functions/database.php';
$conn=g_db_connect() or die('Unable to connect to database server!');
require 'admin/includes/functions/sessions.php';
require 'admin/includes/functions/function.php';
require 'admin/includes/classes/class.imguploader.php';
//date_default_timezone_set('Asia/Calcutta');
/*require 'functions/function.php';
require 'functions/bkp__function.php';*/
$img = new Uploader;

function convert_number_to_words($number) {

    $hyphen      = '-';
    $conjunction = ' and ';
    $separator   = ', ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = array(
        0                   => 'Zero',
        1                   => 'One',
        2                   => 'Two',
        3                   => 'Three',
        4                   => 'Four',
        5                   => 'Five',
        6                   => 'Six',
        7                   => 'Seven',
        8                   => 'Eight',
        9                   => 'Nine',
        10                  => 'Ten',
        11                  => 'Eleven',
        12                  => 'Twelve',
        13                  => 'Thirteen',
        14                  => 'Fourteen',
        15                  => 'Fifteen',
        16                  => 'Sixteen',
        17                  => 'Seventeen',
        18                  => 'Eighteen',
        19                  => 'Nineteen',
        20                  => 'Twenty',
        30                  => 'Thirty',
        40                  => 'Fourty',
        50                  => 'Fifty',
        60                  => 'Sixty',
        70                  => 'Seventy',
        80                  => 'Eighty',
        90                  => 'Ninety',
        100                 => 'Hundred',
        1000                => 'Thousand',
        1000000             => 'Million',
        1000000000          => 'Billion',
        1000000000000       => 'Trillion',
        1000000000000000    => 'Quadrillion',
        1000000000000000000 => 'Quintillion'
    );

    if (!is_numeric($number)) {
        return false;
    }

    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . convert_number_to_words(abs($number));
    }

    $string = $fraction = null;

    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = $number / 100;
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . convert_number_to_words($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= convert_number_to_words($remainder);
            }
            break;
    }

    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = array();
        foreach (str_split((string) $fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);
    }

    return $string;
}


function searchForId($id, $array){
		foreach($array as $key=>$values){
			if($key==$id){
				return 1;
				break;
			}
		}
	}

function searchForattr($id,$attr,$attr_val, $array) {
	foreach($array as $key=>$values)
	{
		foreach($values as $keys => $val)
		{
			if($key==$id )
			{
				foreach($val as $k=>$value)
				{
					if($k=='attribute')
					{
						foreach($value as $attr=>$v)
						{
							
							if($key==$id && $v==$attr_val)
							{	
							
							return 1;
							break;
							}
						}
					}
				}			
			}
		}	
	}
}


function alias($challenge){
	$alias = str_replace(' ', '-', $challenge);
	$alias = str_replace('@', '-', $alias);
	$alias = str_replace('®', '-', $alias);
	$alias = str_replace('&', '', $alias);
	$alias = str_replace('(', '', $alias);
	$alias = str_replace(')', '', $alias);
	$alias = str_replace(',', '', $alias);
	$alias = str_replace("'", '', $alias);
	$alias = str_replace('"', '', $alias);
	$alias = str_replace('?', '', $alias);
	$alias = str_replace(':', '-', $alias);
	$alias = str_replace('.', '-', $alias);
	$alias = preg_replace('/[^a-z0-9\s]/i', '-', $alias);
	return $alias;
}

// require_once 'script/enquiry_code.php';
// require_once 'script/regis_code.php';
// require_once 'script/order_return_code.php';
// require_once 'script/order_cancel_code.php';
// require_once 'script/myprofile_code.php';
// require_once 'script/newsletter_code.php';
?>