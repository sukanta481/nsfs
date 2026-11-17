<?php
/**
 * OpsDesk money helpers.
 * All arithmetic is done in integer paise. The DB stores DECIMAL(12,2).
 * PHP 7.2 compatible (live server).
 */

/** "1,800.5" / "1800" / "₹ 1,800.50" -> 180050. Returns null when not a valid positive amount. */
function ops_paise_from_input($raw)
{
    $s = str_replace(array(',', '₹', ' '), '', trim((string)$raw));
    if ($s === '' || !preg_match('/^\d{1,10}(\.\d{1,2})?$/', $s)) {
        return null;
    }
    $parts = explode('.', $s);
    $rupees = (int)$parts[0];
    $paise = isset($parts[1]) ? (int)str_pad($parts[1], 2, '0') : 0;
    return $rupees * 100 + $paise;
}

/** DECIMAL string from DB ("1800.50") -> 180050 */
function ops_paise_from_db($dec)
{
    if ($dec === null || $dec === '') {
        return 0;
    }
    $neg = (strpos((string)$dec, '-') === 0);
    $s = ltrim((string)$dec, '-');
    $parts = explode('.', $s);
    $v = (int)$parts[0] * 100 + (isset($parts[1]) ? (int)str_pad(substr($parts[1], 0, 2), 2, '0') : 0);
    return $neg ? -$v : $v;
}

/** 180050 -> "1800.50" for INSERT into DECIMAL(12,2) */
function ops_paise_to_db($paise)
{
    $paise = (int)$paise;
    $neg = $paise < 0;
    $paise = abs($paise);
    return ($neg ? '-' : '') . intdiv($paise, 100) . '.' . str_pad((string)($paise % 100), 2, '0', STR_PAD_LEFT);
}

/** Indian grouping: 1234567 rupees -> "12,34,567" */
function ops_group_in($n)
{
    $n = (string)$n;
    if (strlen($n) <= 3) {
        return $n;
    }
    $last3 = substr($n, -3);
    $rest = substr($n, 0, -3);
    $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
    return $rest . ',' . $last3;
}

/** 180050 -> "₹1,800.50", 180000 -> "₹1,800". $sign adds "+" for positives. */
function ops_inr($paise, $sign = false)
{
    $paise = (int)$paise;
    $prefix = $paise < 0 ? '−' : ($sign && $paise > 0 ? '+' : '');
    $abs = abs($paise);
    $str = '₹' . ops_group_in((string)intdiv($abs, 100));
    if ($abs % 100) {
        $str .= '.' . str_pad((string)($abs % 100), 2, '0', STR_PAD_LEFT);
    }
    return $prefix . $str;
}

/** Whole number in words, Indian system (crore / lakh / thousand). 0 -> "Zero". */
function ops_number_words($n)
{
    $n = (int)$n;
    $ones = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve',
                  'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');
    $tens = array('', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety');
    $two = function ($x) use ($ones, $tens) {
        if ($x < 20) return $ones[$x];
        return $tens[intdiv($x, 10)] . ($x % 10 ? '-' . $ones[$x % 10] : '');
    };
    $three = function ($x) use ($ones, $two) {
        $h = intdiv($x, 100); $r = $x % 100;
        return trim(($h ? $ones[$h] . ' Hundred' : '') . ($h && $r ? ' ' : '') . ($r ? $two($r) : ''));
    };
    if ($n === 0) return 'Zero';
    $parts = array();
    $crore = intdiv($n, 10000000); $n %= 10000000;
    $lakh = intdiv($n, 100000); $n %= 100000;
    $thousand = intdiv($n, 1000); $n %= 1000;
    if ($crore) $parts[] = ops_number_words($crore) . ' Crore';
    if ($lakh) $parts[] = $two($lakh) . ' Lakh';
    if ($thousand) $parts[] = $two($thousand) . ' Thousand';
    if ($n) $parts[] = $three($n);
    return implode(' ', $parts);
}

/** "Rupees Nine Thousand Two Hundred Twenty-Five and Eighty-One Paise Only" */
function ops_amount_words($paise)
{
    $paise = abs((int)$paise);
    $r = intdiv($paise, 100); $p = $paise % 100;
    return 'Rupees ' . ops_number_words($r) . ($p ? ' and ' . ops_number_words($p) . ' Paise' : '') . ' Only';
}
