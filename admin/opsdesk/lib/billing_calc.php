<?php
/**
 * OpsDesk consignor billing maths. Pure functions, no DB, unit tested.
 * All money in integer paise.
 *   subtotal  = sum of line amounts (amount = rate per docket)
 *   CGST/SGST = round(subtotal × gst% / 2) each   (same state)
 *   IGST      = round(subtotal × gst%)            (other state)
 *   total     = subtotal + tax, rounded to the nearest rupee; the difference is "round off"
 */

const OPS_GST_RATES = array(0, 5, 12, 18);
const OPS_TAX_TYPES = array('none', 'cgst_sgst', 'igst');
const OPS_HOME_STATE_CODE = '19'; // West Bengal (first two digits of our GSTIN)

/** round(a / b) half up for non-negative integers */
function ops_div_round($a, $b)
{
    return intdiv(2 * (int)$a + (int)$b, 2 * (int)$b);
}

/**
 * @param int[] $amounts line amounts in paise
 * @param int   $gstRate whole percent: 0, 5, 12 or 18
 * @param string $taxType none | cgst_sgst | igst
 */
function ops_bill_totals(array $amounts, $gstRate, $taxType)
{
    $gstRate = (int)$gstRate;
    if (!in_array($gstRate, OPS_GST_RATES, true)) throw new InvalidArgumentException('GST must be 0, 5, 12 or 18 %.');
    if (!in_array($taxType, OPS_TAX_TYPES, true)) throw new InvalidArgumentException('Unknown tax type.');
    if ($gstRate === 0) $taxType = 'none';
    if ($taxType === 'none') $gstRate = 0;
    $sub = 0;
    foreach ($amounts as $a) {
        if ((int)$a < 0) throw new InvalidArgumentException('Amounts cannot be negative.');
        $sub += (int)$a;
    }
    $cgst = $sgst = $igst = 0;
    if ($taxType === 'cgst_sgst') {
        $cgst = ops_div_round($sub * $gstRate, 200);
        $sgst = $cgst;
    } elseif ($taxType === 'igst') {
        $igst = ops_div_round($sub * $gstRate, 100);
    }
    $before = $sub + $cgst + $sgst + $igst;
    $total = ops_div_round($before, 100) * 100; // nearest rupee
    return array(
        'subtotal' => $sub, 'gst_rate' => $gstRate, 'tax_type' => $taxType,
        'cgst' => $cgst, 'sgst' => $sgst, 'igst' => $igst,
        'round_off' => $total - $before, 'total' => $total,
    );
}

/** Indian financial year for a date: 2026-11-19 -> '2026-27', 2027-02-01 -> '2026-27' */
function ops_fin_year($date)
{
    $y = (int)substr($date, 0, 4);
    $m = (int)substr($date, 5, 2);
    $start = $m >= 4 ? $y : $y - 1;
    return $start . '-' . substr((string)($start + 1), 2, 2);
}

function ops_bill_number($fy, $seq)
{
    return 'NSFS/' . $fy . '/' . str_pad((string)(int)$seq, 4, '0', STR_PAD_LEFT);
}

/** Suggest CGST+SGST for a West Bengal (or unknown) GSTIN, IGST for other states. */
function ops_suggest_tax_type($gstin)
{
    $g = strtoupper(trim((string)$gstin));
    if ($g === '' || strlen($g) < 2) return 'cgst_sgst';
    return substr($g, 0, 2) === OPS_HOME_STATE_CODE ? 'cgst_sgst' : 'igst';
}

/** Basic GSTIN format check (15 chars: 2 digits state, 10 PAN, entity, Z, check). Empty is allowed. */
function ops_valid_gstin($gstin)
{
    $g = strtoupper(trim((string)$gstin));
    return $g === '' || (bool)preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][0-9A-Z]Z[0-9A-Z]$/', $g);
}
