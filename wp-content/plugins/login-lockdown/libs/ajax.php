<?php

/**
 * Login Lockdown
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2024, www.webfactoryltd.com
 */

class LoginLockdown_AJAX extends LoginLockdown
{

    /**
     * Run one tool via AJAX call
     *
     * @return null
     */
    static function ajax_run_tool()
    {
        global $wpdb, $current_user;

        if (false === current_user_can('manage_options')) {
            wp_die('Sorry, you have to be an admin to run this action.');
        }

        check_ajax_referer('loginlockdown_run_tool');

        if (!isset($_REQUEST['tool'])) {
            wp_send_json_error(__('Unknown tool.', 'login-lockdown'));
        }

        $tool = sanitize_text_field(wp_unslash($_REQUEST['tool']));

        $options = LoginLockdown_Setup::get_options();

        $update['last_options_edit'] = current_time('mysql', true);
        update_option(LOGINLOCKDOWN_OPTIONS_KEY, array_merge($options, $update));

        if ($tool == 'activity_logs') {
            self::get_activity_logs();
        } else if ($tool == 'locks_logs') {
            self::get_locks_logs();
        } else if ($tool == 'recovery_url') {
            if (!isset($_POST['reset'])) {
                wp_send_json_error('Missing reset field');
            }

            if (sanitize_text_field(wp_unslash($_POST['reset'])) == 'true') {
                sleep(1);
                $options['global_unblock_key'] = 'll' . md5(time() . wp_rand(10000, 9999));
                update_option(LOGINLOCKDOWN_OPTIONS_KEY, array_merge($options, $update));
            }
            wp_send_json_success(array('url' => '<a href="' . site_url('/?loginlockdown_unblock=' . $options['global_unblock_key']) . '">' . site_url('/?loginlockdown_unblock=' . $options['global_unblock_key']) . '</a>'));
        } else if ($tool == 'empty_log') {
            if (!isset($_POST['log'])) {
                wp_send_json_error('Missing log field');
            }
            self::empty_log(sanitize_text_field(wp_unslash($_POST['log'])));
            wp_send_json_success();
        } else if ($tool == 'unlock_lockdown') {
            if (!isset($_POST['lock_id'])) {
                wp_send_json_error('Missing lock ID');
            }
            //phpcs:ignore because we are using a custom table and need to check returned IP
            $lockdown_ip = $wpdb->get_var($wpdb->prepare("SELECT lockdown_IP FROM " . $wpdb->lockdown_lockdowns . " WHERE lockdown_ID = %d", array(intval(wp_unslash($_POST['lock_id']))))); //phpcs:ignore
            //phpcs:ignore because we don't need to cache
            $wpdb->delete( $wpdb->lockdown_login_fails, array('login_attempt_IP' => $lockdown_ip) ); //phpcs:ignore
            $wpdb->update( $wpdb->lockdown_lockdowns, array('unlocked' => 1), array('lockdown_ID' => intval($_POST['lock_id'])) ); //phpcs:ignore
            wp_send_json_success(array('id' => intval($_POST['lock_id'])));
        } else if ($tool == 'delete_lock_log') {
            if (!isset($_POST['lock_id'])) {
                wp_send_json_error('Missing lock ID');
            }
            //phpcs:ignore because we don't need to cache
            $wpdb->delete( $wpdb->lockdown_lockdowns, array('lockdown_ID' => intval($_POST['lock_id'])) ); //phpcs:ignore
            wp_send_json_success(array('id' => intval(wp_unslash($_POST['lock_id']))));
        } else if ($tool == 'delete_fail_log') {
            if (!isset($_POST['fail_id'])) {
                wp_send_json_error('Missing fail ID');
            }
            //phpcs:ignore because we don't need to cache
            $wpdb->delete( $wpdb->lockdown_login_fails, array('login_attempt_ID' => intval(wp_unslash($_POST['fail_id']))) ); //phpcs:ignore
            wp_send_json_success(array('id' => intval($_POST['fail_id'])));
        } else if ($tool == 'loginlockdown_dismiss_pointer') {
            delete_option(LOGINLOCKDOWN_POINTERS_KEY);
            wp_send_json_success();
        } else if ($tool == 'verify_captcha') {
            if (!isset($_POST['captcha_type'])) {
                wp_send_json_error(__('Unknown captcha type.', 'login-lockdown'));
            }
            $captcha_type = sanitize_key(wp_unslash($_POST['captcha_type']));

            if (!isset($_POST['captcha_response'])) {
                wp_send_json_error(__('Unknown response.', 'login-lockdown'));
            }
            $captcha_response = sanitize_text_field(wp_unslash($_POST['captcha_response']));

            if ($captcha_type == 'builtin' && !isset($_POST['captcha_response_token'])) {
                wp_send_json_error(__('Unknown response token.', 'login-lockdown'));
            }

            if (isset($_POST['captcha_response_token'])) {
                $captcha_response_token = sanitize_text_field(wp_unslash($_POST['captcha_response_token']));
            } else {
                $captcha_response_token = '';
            }

            $captcha_result = self::verify_captcha($captcha_type, $captcha_response, $captcha_response_token);
            if (is_wp_error($captcha_result)) {
                wp_send_json_error($captcha_result->get_error_message());
            }
            wp_send_json_success($captcha_result);
        } else if ($tool == 'email_test') {
            $subject  = 'Login Lockdown test email';
            $message  = '<p>This is a test email from ' . get_bloginfo('title') . ' (' . home_url() . '). Since you have received it, emails work! 🎉</p>';

            add_filter('wp_mail_content_type', function () {
                return "text/html";
            });

            if (wp_mail($current_user->user_email, $subject, $message)) {
                wp_send_json_success(array('sent' => true, 'title' => 'Email sent successfully', 'text' => 'An email has been sent to <strong>' . $current_user->user_email . '</strong>. Please check your inbox and spam folder. If you don\'t received the email, there is an issue with the email configuration on this site.'));
            } else {
                wp_send_json_success(array('sent' => false, 'title' => 'Email failed', 'text' => 'We tried to send an email to <strong>' . $current_user->user_email . '</strong> but it appears to have failed. Please check your inbox and spam folder. If you don\'t received the email, there is an issue with the email configuration on this site.'));
            }
        } else {
            wp_send_json_error(__('Unknown tool.', 'login-lockdown'));
        }
        die();
    } // ajax_run_tool

    /**
     * Get rule row html
     *
     * @return string row HTML
     *
     * @param array $data with rule settings
     */
    static function get_date_time($timestamp)
    {
        $interval = current_time('timestamp') - $timestamp;
        return '<span class="loginlockdown-dt-small">' . self::humanTiming($interval, true) . '</span><br />' . gmdate('Y/m/d', $timestamp) . '<br><span class="loginlockdown-dt-small">' . gmdate('h:i:s A', $timestamp) . '</span>';
    }

    /**
     * Get human readable timestamp like 2 hours ago
     *
     * @return int time
     *
     * @param string timestamp
     */
    static function humanTiming($time)
    {
        $tokens = array(
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        if ($time < 1) {
            return 'just now';
        }
        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '') . ' ago';
        }
    }

    static function empty_log($log)
    {
        global $wpdb;

        if ($log == 'fails') {
            $wpdb->query('TRUNCATE TABLE ' . $wpdb->lockdown_login_fails);
        } else {
            $wpdb->query('TRUNCATE TABLE ' . $wpdb->lockdown_lockdowns);
        }
    }



    /**
     * Fetch activity logs and output JSON for datatables
     *
     * @return null
     */
    static function get_locks_logs()
    {
        global $wpdb;
        check_ajax_referer('loginlockdown_run_tool');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You are not allowed to run this action.', 'login-lockdown'));
        }

        $aColumns = array('lockdown_ID', 'unlocked', 'lockdown_date', 'release_date', 'reason', 'lockdown_IP');
        
        $sLimit = '';
        if (isset($_GET['iDisplayStart']) && isset($_GET['iDisplayLength']) && $_GET['iDisplayLength'] != '-1') {
            $limit_offset = intval($_GET['iDisplayStart']);
            $limit_count = intval($_GET['iDisplayLength']);
            $sLimit = $wpdb->prepare(" LIMIT %d, %d", $limit_offset, $limit_count);
        }

        $sOrder = '';
        $order_clauses = [];
        if (isset($_GET['iSortCol_0']) && isset($_GET['iSortingCols'])) {
            for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
                $iSortCol = isset($_GET['iSortCol_' . $i]) ? intval($_GET['iSortCol_' . $i]) : 0;
                $sSortDir = isset($_GET['sSortDir_' . $i]) ? sanitize_key($_GET['sSortDir_' . $i]) : 'asc';

                if (isset($_GET['bSortable_' . $iSortCol]) && $_GET['bSortable_' . $iSortCol] === "true") {
                    $column = $aColumns[$iSortCol];
                    $dir = ($sSortDir === 'desc') ? 'DESC' : 'ASC';
                    $order_clauses[] = "`$column` $dir";
                }
            }

            if (!empty($order_clauses)) {
                $sOrder = "ORDER BY " . implode(', ', $order_clauses);
            }
        }

        $sWhere = '';
        $where_clauses = [];
        $query_vars = [];

        if (!empty($_GET['sSearch'])) {
            $search_term = '%' . $wpdb->esc_like(sanitize_text_field(wp_unslash($_GET['sSearch']))) . '%'; //sanitize_text_field is used to sanitize according to WordPress PCP
            $sub_clauses = [];

            foreach ($aColumns as $col) {
                $sub_clauses[] = "`$col` LIKE %s";
                $query_vars[] = $search_term;
            }

            $where_clauses[] = '(' . implode(' OR ', $sub_clauses) . ')';
        }

        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] === "true" && !empty($_GET['sSearch_' . $i])) {
                $search_term = '%' . $wpdb->esc_like(sanitize_text_field(wp_unslash($_GET['sSearch_' . $i]))) . '%';
                $where_clauses[] = "`" . $aColumns[$i] . "` LIKE %s";
                $query_vars[] = $search_term;
            }
        }

        if (!empty($where_clauses)) {
            $sWhere = "WHERE " . implode(' AND ', $where_clauses);
        }

        $sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $aColumns) .
            " FROM `{$wpdb->lockdown_lockdowns}` $sWhere $sOrder";

        if (!empty($sLimit)) {
            $sql .= $sLimit;
        }

        if (!empty($query_vars)) {
            $prepared_sql = $wpdb->prepare($sql, $query_vars); //phpcs:ignore
        } else {
            $prepared_sql = $sql;
        }

        $rResult = $wpdb->get_results($prepared_sql); //phpcs:ignore

        $iFilteredTotal = $wpdb->get_var("SELECT FOUND_ROWS()"); //phpcs:ignore

        $iTotal = $wpdb->get_var("SELECT COUNT(`lockdown_ID`) FROM `{$wpdb->lockdown_lockdowns}`"); //phpcs:ignore

        $output = array(
            "sEcho" => isset($_GET['sEcho']) ? intval($_GET['sEcho']) : '',
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        foreach ($rResult as $aRow) {
            $row = array();
            $row['DT_RowId'] = $aRow->lockdown_ID;

            if (strtotime($aRow->release_date) < time()) {
                $row['DT_RowClass'] = 'lock_expired';
            }

            foreach ($aColumns as $col) {
                if ($col === 'unlocked') {
                    $unblocked = $aRow->$col;
                    if ($unblocked == 0 && strtotime($aRow->release_date) > time()) {
                        $row[] = '<div class="tooltip unlock_accesslock" data-lock-id="' . $aRow->lockdown_ID . '" title="Unlock"><i class="loginlockdown-icon loginlockdown-lock"></i></div>';
                    } else {
                        $row[] = '<div class="tooltip unlocked_accesslock" title="Unlock"><i class="loginlockdown-icon loginlockdown-unlock"></i></div>';
                    }
                } elseif ($col === 'lockdown_date') {
                    $row[] = self::get_date_time(strtotime($aRow->$col));
                } elseif ($col === 'reason') {
                    $row[] = $aRow->$col;
                } elseif ($col === 'lockdown_IP') {
                    $row[] = '<a href="#" class="open-pro-dialog pro-feature" data-pro-feature="access-log-user-location">Available in PRO</a>';
                    $row[] = $aRow->lockdown_IP;
                    $row[] = '<a href="#" class="open-pro-dialog pro-feature" data-pro-feature="access-log-user-agent">Available in PRO</a>';
                }
            }

            $row[] = '<div data-lock-id="' . $aRow->lockdown_ID . '" class="tooltip delete_lock_entry" title="Delete Access Lock?" data-msg-success="Access Lock deleted" data-btn-confirm="Delete Access Lock" data-title="Delete Access Lock?" data-wait-msg="Deleting. Please wait." data-name=""><i class="loginlockdown-icon loginlockdown-trash"></i></div>';
            $output['aaData'][] = $row;
        }

        @ob_end_clean();
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        echo json_encode($output);
        die();
    }


    /**
     * Fetch activity logs and output JSON for datatables
     *
     * @return null
     */
    static function get_activity_logs()
    {
        global $wpdb;
        check_ajax_referer('loginlockdown_run_tool');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You are not allowed to run this action.', 'login-lockdown'));
        }

        $options = LoginLockdown_Setup::get_options();

        $aColumns = array('login_attempt_ID', 'login_attempt_date', 'failed_user', 'login_attempt_IP', 'reason');

        $sLimit = '';
        if (isset($_GET['iDisplayStart']) && isset($_GET['iDisplayLength']) && $_GET['iDisplayLength'] != '-1') {
            $sLimit = "LIMIT %d, %d";
            $limit_offset = intval($_GET['iDisplayStart']);
            $limit_count = intval($_GET['iDisplayLength']);
        }

        $sOrder = '';
        $order_clauses = [];
        if (isset($_GET['iSortCol_0']) && isset($_GET['iSortingCols'])) {
            for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
                $iSortCol = isset($_GET['iSortCol_' . $i]) ? intval($_GET['iSortCol_' . $i]) : 0;
                $sSortDir = isset($_GET['sSortDir_' . $i]) ? sanitize_key($_GET['sSortDir_' . $i]) : 'asc';

                if (isset($_GET['bSortable_' . $iSortCol]) && $_GET['bSortable_' . $iSortCol] === "true") {
                    $column = $aColumns[$iSortCol];
                    $dir = ($sSortDir === 'desc') ? 'DESC' : 'ASC';
                    $order_clauses[] = "`$column` $dir";
                }
            }

            if (!empty($order_clauses)) {
                $sOrder = "ORDER BY " . implode(', ', $order_clauses);
            }
        }

        // filtering
        $sWhere = '';
        $where_clauses = [];
        $query_vars = [];

        if (!empty($_GET['sSearch'])) {
            $search_term = '%' . $wpdb->esc_like(sanitize_text_field(wp_unslash($_GET['sSearch']))) . '%'; //sanitize_text_field is used to sanitize according to WordPress PCP
            $sub_clauses = [];

            foreach ($aColumns as $col) {
                $sub_clauses[] = "`$col` LIKE %s";
                $query_vars[] = $search_term;
            }

            $where_clauses[] = '(' . implode(' OR ', $sub_clauses) . ')';
        }

        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] === "true" && !empty($_GET['sSearch_' . $i])) {
                $search_term = '%' . $wpdb->esc_like(sanitize_text_field(wp_unslash($_GET['sSearch_' . $i]))) . '%';
                $where_clauses[] = "`" . $aColumns[$i] . "` LIKE %s";
                $query_vars[] = $search_term;
            }
        }

        if (!empty($where_clauses)) {
            $sWhere = "WHERE " . implode(' AND ', $where_clauses);
        }

        // build query
        $sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $aColumns) .
            " FROM " . $wpdb->lockdown_login_fails . " $sWhere $sOrder";

        if (!empty($sLimit)) {
            $sql .= " " . $wpdb->prepare("LIMIT %d, %d", $limit_offset, $limit_count); //phpcs:ignore
        }

        //phpcs:ignore because the query parts are dynamic based on the number of columns
        if (!empty($query_vars)) {
            $prepared_sql = $wpdb->prepare($sql, $query_vars); //phpcs:ignore
        } else {
            $prepared_sql = $sql;
        }

        $rResult = $wpdb->get_results($prepared_sql); //phpcs:ignore

        // filtered count
        $iFilteredTotal = $wpdb->get_var("SELECT FOUND_ROWS()"); //phpcs:ignore

        // total count
        //phpcs: no need to prepare, $sIndexColumn
        $iTotal = $wpdb->get_var("SELECT COUNT(`login_attempt_ID`) FROM {$wpdb->lockdown_login_fails}"); //phpcs:ignore

        // output formatting
        $output = array(
            "sEcho" => isset($_GET['sEcho']) ? intval($_GET['sEcho']) : '',
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        foreach ($rResult as $aRow) {
            $row = array();
            $row['DT_RowId'] = $aRow->login_attempt_ID;

            foreach ($aColumns as $col) {
                if ($col == 'login_attempt_date') {
                    $row[] = self::get_date_time(strtotime($aRow->$col));
                } elseif ($col == 'failed_user') {
                    $failed_login = '<strong>User:</strong> ' . htmlspecialchars($aRow->failed_user) . '<br />';
                    $row[] = $failed_login;
                } elseif ($col == 'login_attempt_IP') {
                    $row[] = '<a href="#" class="open-pro-dialog pro-feature" data-pro-feature="fail-log-user-location">Available in PRO</a>';
                    $row[] = $aRow->login_attempt_IP;
                    $row[] = '<a href="#" class="open-pro-dialog pro-feature" data-pro-feature="fail-log-user-agent">Available in PRO</a>';
                } elseif ($col == 'reason') {
                    $row[] = LoginLockdown_Functions::pretty_fail_errors($aRow->$col);
                }
            }

            $row[] = '<div data-failed-id="' . $aRow->login_attempt_ID . '" class="tooltip delete_failed_entry" title="Delete failed login attempt log entry" data-msg-success="Failed login attempt log entry deleted" data-btn-confirm="Delete failed login attempt log entry" data-title="Delete failed login attempt log entry" data-wait-msg="Deleting. Please wait." data-name=""><i class="loginlockdown-icon loginlockdown-trash"></i></div>';

            $output['aaData'][] = $row;
        }

        @ob_end_clean();
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        echo json_encode($output);
        die();
    }

    static function verify_captcha($type, $response, $captcha_response_token = false)
    {
        if ($type == 'builtin') {
            if (wp_hash($response) === $captcha_response_token) {
                return true;
            } else {
                return new WP_Error('loginlockdown_builtin_captcha_failed', __("<strong>ERROR</strong>: captcha verification failed.<br /><br />Please try again.", 'login-lockdown'));
            }
        }
    }
} // class
