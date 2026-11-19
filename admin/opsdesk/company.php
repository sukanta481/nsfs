<?php
/**
 * Company details printed on OpsDesk documents (salary slips, consignor bills).
 * Copied from the existing manifest print header (manifest_print.php). Edit here only.
 */
return array(
    'name'       => 'NORTH SUPER FAST SERVICE',
    'tagline'    => 'Courier & Logistics',
    'address'    => '16/1/H/13/2, Jaharlal Dutta Lane, Kolkata-700067',
    'phones'     => '9933999998 / 7003272240',
    'email'      => 'tanmoy.absh100@gmail.com',
    'gst'        => '19BJPFG8832C1ZT',
    'logo'       => 'images/logo.png',   // relative to nsfs/admin/
    'sac'        => '996812',            // service code printed on consignor bills (from manifest_print.php)
    // Bank details printed on consignor bills. Leave blank to hide the section.
    'bank' => array(
        'account_name' => '',
        'account_no'   => '',
        'ifsc'         => '',
        'bank_name'    => '',
        'branch'       => '',
        'upi'          => '',
    ),
);
