<?php
/**
 * OpsDesk JSON endpoint for in-page actions (attendance clicks, reversals).
 * Always POST with the CSRF token. Returns {ok:true,...} or {ok:false,error:"..."}.
 */
require 'includes/top.php';   // first: sends logged-out users to login_new.php
require 'check_auth.php';
require 'opsdesk/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

function ops_json($data)
{
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    ops_json(array('ok' => false, 'error' => 'POST only'));
}
ops_check_csrf();

$action = isset($_POST['action']) ? $_POST['action'] : '';
$p = function ($k, $d = '') { return isset($_POST[$k]) ? trim($_POST[$k]) : $d; };

try {
    switch ($action) {
        case 'attendance_set':
            ops_require('ops_attendance_mark');
            $date = ops_date_param('date', '');
            if (!$date) throw new InvalidArgumentException('Invalid date.');
            ops_attendance_set((int)$p('staff_id'), $date, $p('status'), null, $p('shift') !== '' ? $p('shift') : null);
            $row = ops_one('SELECT status, shift, extra_duty FROM tbl_ops_attendance WHERE staff_id = ? AND att_date = ?', array((int)$p('staff_id'), $date));
            ops_json(array('ok' => true, 'row' => $row));

        case 'attendance_extra':
            // Extra Duty (EXD): extra = day | night | '' (clear)
            ops_require('ops_attendance_mark');
            $date = ops_date_param('date', '');
            if (!$date) throw new InvalidArgumentException('Invalid date.');
            ops_attendance_extra((int)$p('staff_id'), $date, $p('extra'));
            ops_json(array('ok' => true));

        case 'attendance_remarks':
            ops_require('ops_attendance_mark');
            $date = ops_date_param('date', '');
            if (!$date) throw new InvalidArgumentException('Invalid date.');
            $n = ops_attendance_remarks((int)$p('staff_id'), $date, mb_substr($p('remarks'), 0, 255));
            ops_json(array('ok' => true, 'saved' => (bool)$n));

        case 'attendance_bulk':
            // Mark the given staff ids (comma list) with one status, skipping anyone already marked.
            ops_require('ops_attendance_mark');
            $date = ops_date_param('date', '');
            if (!$date) throw new InvalidArgumentException('Invalid date.');
            $status = $p('status');
            $ids = array_filter(array_map('intval', explode(',', $p('staff_ids'))));
            $already = ops_attendance_for_date($date);
            $n = 0;
            ops_begin();
            foreach ($ids as $sid) {
                if (isset($already[$sid]) || ops_staff_locked($sid, $date)) continue;
                ops_attendance_set($sid, $date, $status);
                $n++;
            }
            ops_commit();
            ops_json(array('ok' => true, 'count' => $n));

        case 'hand_reverse':
            ops_require('ops_hand_ledger_manage');
            ops_hand_reverse((int)$p('id'), mb_substr($p('reason'), 0, 255));
            ops_flash('Entry reversed.');
            ops_json(array('ok' => true));

        case 'advance_reverse':
            ops_require('ops_advance_manage');
            ops_advance_reverse((int)$p('id'), mb_substr($p('reason'), 0, 255));
            ops_flash('Advance reversed.');
            ops_json(array('ok' => true));

        default:
            ops_json(array('ok' => false, 'error' => 'Unknown action'));
    }
} catch (InvalidArgumentException $e) {
    ops_rollback();
    ops_json(array('ok' => false, 'error' => $e->getMessage()));
} catch (Exception $e) {
    ops_rollback();
    error_log('[OpsDesk] ' . $action . ': ' . $e->getMessage());
    ops_json(array('ok' => false, 'error' => 'Could not save. Please try again.'));
}
