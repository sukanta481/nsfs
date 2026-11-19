<?php
/**
 * OpsDesk unit tests (no framework needed).
 * Run:  php admin/opsdesk/tests/run_tests.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../lib/money.php';
require __DIR__ . '/../lib/payroll_calc.php';
require __DIR__ . '/../lib/billing_calc.php';

$fails = 0;
$count = 0;
function eq($label, $expected, $actual)
{
    global $fails, $count;
    $count++;
    if ($expected === $actual) {
        echo "  ok   $label\n";
    } else {
        $fails++;
        echo "  FAIL $label\n       expected " . var_export($expected, true) . "\n       got      " . var_export($actual, true) . "\n";
    }
}
function throws($label, $fn)
{
    global $fails, $count;
    $count++;
    try {
        $fn();
        $fails++;
        echo "  FAIL $label (no exception)\n";
    } catch (InvalidArgumentException $e) {
        echo "  ok   $label\n";
    }
}

echo "Month length\n";
eq('Feb 2026 = 28', 28, ops_days_in_month('2026-02-01'));
eq('Feb 2028 (leap) = 29', 29, ops_days_in_month('2028-02'));
eq('Apr = 30', 30, ops_days_in_month('2026-04-15'));
eq('Oct = 31', 31, ops_days_in_month('2026-10-01'));

echo "Full attendance always equals fixed salary\n";
foreach (array(28, 29, 30, 31) as $d) {
    foreach (array(1500000, 1650000, 1099999, 1) as $sal) {
        eq("salary $sal, $d/$d days", $sal, ops_earned_paise($sal, $d, $d));
    }
}

echo "Partial months (rounded once, half up)\n";
eq('15000 x 29/31 = 14032.26', 1403226, ops_earned_paise(1500000, 29, 31));
eq('15000 x 27/28 = 14464.29', 1446429, ops_earned_paise(1500000, 27, 28));
eq('15000 x 28/29 = 14482.76', 1448276, ops_earned_paise(1500000, 28, 29));
eq('15000 x 29/30 = 14500.00', 1450000, ops_earned_paise(1500000, 29, 30));
eq('10500 x 30/31 = 10161.29', 1016129, ops_earned_paise(1050000, 30, 31));
eq('0 payable days = 0', 0, ops_earned_paise(1500000, 0, 31));
eq('half-up: 1 paise x 1/2 days -> 1', 1, ops_earned_paise(1, 1, 28) + ops_earned_paise(14, 1, 28)); // 0.0357 -> 0, 0.5 -> 1

echo "Same absences cost different amounts in different months\n";
eq('1 day absent in Feb (28) costs 535.71', 1500000 - 1446429, 1500000 - ops_earned_paise(1500000, 27, 28));
eq('1 day absent in Oct (31) costs 483.87', 1500000 - 1451613, 1500000 - ops_earned_paise(1500000, 30, 31));

echo "Advance recovery\n";
$r = ops_payroll_calc(1600000, 31, 31, 200000);
eq('advance 2000 fully recovered', 200000, $r['advance_deduction']);
eq('net = 16000 - 2000', 1400000, $r['net']);
eq('nothing carried', 0, $r['advance_carry']);
$r = ops_payroll_calc(1050000, 30, 31, 1200000);
eq('advance > earned: deduct only earned', 1016129, $r['advance_deduction']);
eq('net never below zero', 0, $r['net']);
eq('rest carries forward', 1200000 - 1016129, $r['advance_carry']);
$r = ops_payroll_calc(1500000, 31, 31, 0);
eq('no advance -> net = salary', 1500000, $r['net']);
$r = ops_payroll_calc(1500000, 31, 31, -500);
eq('negative outstanding treated as 0', 1500000, $r['net']);

echo "Attendance counting (unmarked = absent)\n";
$c = ops_count_attendance(array(1 => 'present', 2 => 'week_off', 3 => 'paid_leave', 4 => 'absent'), 30);
eq('present 1', 1, $c['present']);
eq('payable 3', 3, $c['payable']);
eq('unmarked 26', 26, $c['unmarked']);
eq('absent total 27', 27, $c['absent_total']);
$all = array();
for ($i = 1; $i <= 29; $i++) {
    $all[$i] = $i % 7 === 1 ? 'week_off' : 'present';
}
$c = ops_count_attendance($all, 29);
eq('leap Feb fully marked -> payable 29', 29, $c['payable']);

echo "Guards\n";
throws('days 27 rejected', function () { ops_earned_paise(100, 1, 27); });
throws('payable > days rejected', function () { ops_earned_paise(100, 32, 31); });
throws('negative salary rejected', function () { ops_earned_paise(-1, 1, 31); });

echo "Money parsing and display\n";
eq('"1,800" -> 180000', 180000, ops_paise_from_input('1,800'));
eq('"1800.5" -> 180050', 180050, ops_paise_from_input('1800.5'));
eq('"₹ 25.05" -> 2505', 2505, ops_paise_from_input('₹ 25.05'));
eq('"-5" invalid', null, ops_paise_from_input('-5'));
eq('"1.234" invalid', null, ops_paise_from_input('1.234'));
eq('"abc" invalid', null, ops_paise_from_input('abc'));
eq('db "1800.50" -> 180050', 180050, ops_paise_from_db('1800.50'));
eq('db "-20.5" -> -2050', -2050, ops_paise_from_db('-20.5'));
eq('to db 180050 -> "1800.50"', '1800.50', ops_paise_to_db(180050));
eq('to db 5 -> "0.05"', '0.05', ops_paise_to_db(5));
eq('inr 1234567 rupees', '₹12,34,567', ops_inr(123456700));
eq('inr with paise', '₹14,032.26', ops_inr(1403226));
eq('inr negative', '−₹200', ops_inr(-20000));
eq('inr small', '₹999', ops_inr(99900));

echo "
Extra duty (EXD)
";
$x = ops_payroll_calc(1600000, 30, 30, 0, 2);            // full month + 2 EXD
eq('exd full month earned = salary', 1600000, $x['earned']);
eq('exd 2 days pay = 2 x 533.33', 106667, $x['extra_pay']);
eq('exd gross', 1706667, $x['gross']);
eq('exd net', 1706667, $x['net']);
$x = ops_payroll_calc(1100000, 26, 31, 0, 3);            // 11000 x 29/31 = 10290.32
eq('exd gross rounded once', 1029032, $x['gross']);
eq('exd earned part', 922581, $x['earned']);
eq('exd extra = gross - earned', 106451, $x['extra_pay']);
$x = ops_payroll_calc(1600000, 10, 30, 600000, 1);       // advance bigger than earned, EXD helps
eq('exd advance limited by gross', 586667, $x['advance_deduction']);
eq('exd net never below 0', 0, $x['net']);
eq('exd carry', 13333, $x['advance_carry']);
$x = ops_payroll_calc(1600000, 28, 30, 0);               // no EXD: unchanged behaviour
eq('no exd extra 0', 0, $x['extra_pay']);
eq('no exd gross = earned', $x['earned'], $x['gross']);
throws('exd negative rejected', function () { ops_payroll_calc(1600000, 30, 30, 0, -1); });
throws('exd more than days rejected', function () { ops_payroll_calc(1600000, 30, 30, 0, 31); });
$c = ops_count_attendance(array(1 => 'present', 2 => 'present'), 30, 1);
eq('count carries exd', 1, $c['extra_duty']);

echo "
Hand balance settled through salary
";
$x = ops_payroll_calc(1600000, 28, 30, 200000, 0, 50000);   // 14933.33 - adv 2000 - cash 500
eq('hand: absent deduction = salary - earned', 106667, $x['absent_deduction']);
eq('hand: cash held deducted', 50000, $x['hand_settlement']);
eq('hand: net', 1243333, $x['net']);
eq('hand: nothing carried', 0, $x['hand_carry']);
$x = ops_payroll_calc(1100000, 31, 31, 0, 0, -12000);        // company owes 120 -> added
eq('hand: owed amount added (negative settle)', -12000, $x['hand_settlement']);
eq('hand: net includes owed', 1112000, $x['net']);
$x = ops_payroll_calc(1600000, 10, 30, 300000, 0, 700000);   // earned 5333.33, adv 3000 -> 2333.33 left, cash 7000
eq('hand: settle limited to what is left', 233333, $x['hand_settlement']);
eq('hand: net never below 0', 0, $x['net']);
eq('hand: rest carried in hand balance', 466667, $x['hand_carry']);
$x = ops_payroll_calc(1600000, 30, 30, 0, 2, 0);             // no hand balance: unchanged
eq('hand: zero balance no settle', 0, $x['hand_settlement']);
eq('hand: zero balance net = gross', $x['gross'], $x['net']);
$x = ops_payroll_calc(1600000, 30, 30, 0);
eq('hand: full attendance absent deduction 0', 0, $x['absent_deduction']);
$y = ops_payroll_calc(1600000, 26, 30, 0, 2);
eq('breakdown adds up: fixed + extra - absent = gross', $y['gross'], 1600000 + $y['extra_pay'] - $y['absent_deduction']);

echo "
Consignor billing
";
$t = ops_bill_totals(array(50000, 50000, 75000), 0, 'none');      // 500 + 500 + 750
eq('bill: subtotal', 175000, $t['subtotal']);
eq('bill: no gst total = subtotal', 175000, $t['total']);
eq('bill: no gst round off 0', 0, $t['round_off']);
$t = ops_bill_totals(array(100000), 18, 'cgst_sgst');               // 1000 + 9% + 9%
eq('bill: cgst 9%', 9000, $t['cgst']);
eq('bill: sgst 9%', 9000, $t['sgst']);
eq('bill: igst 0 for same state', 0, $t['igst']);
eq('bill: total 1180', 118000, $t['total']);
$t = ops_bill_totals(array(100000), 18, 'igst');
eq('bill: igst 18%', 18000, $t['igst']);
eq('bill: igst total', 118000, $t['total']);
$t = ops_bill_totals(array(33333), 5, 'cgst_sgst');                 // 333.33 + 8.33 + 8.33 = 349.99 -> 350
eq('bill: cgst 2.5% rounded', 833, $t['cgst']);
eq('bill: round off +0.01', 1, $t['round_off']);
eq('bill: total rounded to rupee', 35000, $t['total']);
$t = ops_bill_totals(array(12345), 12, 'igst');                     // 123.45 + 14.81 = 138.26 -> 138
eq('bill: round off negative', -26, $t['round_off']);
eq('bill: total 138', 13800, $t['total']);
$t = ops_bill_totals(array(100000), 0, 'igst');
eq('bill: 0% forces no tax', 'none', $t['tax_type']);
throws('bill: bad gst rate', function () { ops_bill_totals(array(1000), 7, 'igst'); });
throws('bill: negative line', function () { ops_bill_totals(array(-1), 0, 'none'); });
eq('line: 3 boxes x 40', 12000, ops_bill_line_amount(4000, 3));
eq('line: 1 box x 12.50', 1250, ops_bill_line_amount(1250, 1));
eq('line: boxes as text', 9000, ops_bill_line_amount(4500, '2'));
throws('line: 0 boxes rejected', function () { ops_bill_line_amount(4000, 0); });
throws('line: fractional boxes rejected', function () { ops_bill_line_amount(4000, '1.5'); });
throws('line: zero rate rejected', function () { ops_bill_line_amount(0, 2); });
eq('fy: november', '2026-27', ops_fin_year('2026-11-19'));
eq('fy: february', '2026-27', ops_fin_year('2027-02-01'));
eq('fy: 1 april starts new', '2027-28', ops_fin_year('2027-04-01'));
eq('fy: 31 march', '2026-27', ops_fin_year('2027-03-31'));
eq('bill number', 'NSFS/2026-27/0007', ops_bill_number('2026-27', 7));
eq('tax suggest WB', 'cgst_sgst', ops_suggest_tax_type('19ABCDE1234F1Z5'));
eq('tax suggest other state', 'igst', ops_suggest_tax_type('27ABCDE1234F1Z5'));
eq('tax suggest no gstin', 'cgst_sgst', ops_suggest_tax_type(''));
eq('gstin valid', true, ops_valid_gstin('19BJPFG8832C1ZT'));
eq('gstin empty ok', true, ops_valid_gstin(''));
eq('gstin invalid', false, ops_valid_gstin('19BJPFG8832'));

echo "
Amount in words
";
eq('words zero', 'Rupees Zero Only', ops_amount_words(0));
eq('words 15000', 'Rupees Fifteen Thousand Only', ops_amount_words(1500000));
eq('words 9225.81', 'Rupees Nine Thousand Two Hundred Twenty-Five and Eighty-One Paise Only', ops_amount_words(922581));
eq('words 12933.33', 'Rupees Twelve Thousand Nine Hundred Thirty-Three and Thirty-Three Paise Only', ops_amount_words(1293333));
eq('words 1,00,000', 'Rupees One Lakh Only', ops_amount_words(10000000));
eq('words 12,34,567.05', 'Rupees Twelve Lakh Thirty-Four Thousand Five Hundred Sixty-Seven and Five Paise Only', ops_amount_words(123456705));
eq('words 2 crore 110', 'Rupees Two Crore One Hundred Ten Only', ops_amount_words(2000011000));

echo "\n" . ($count - $fails) . "/$count passed\n";
exit($fails ? 1 : 0);
