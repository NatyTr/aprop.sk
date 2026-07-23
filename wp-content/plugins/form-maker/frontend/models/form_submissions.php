<?php

/**
 * Class FMModelForm_submissions
 */
class FMModelForm_submissions {
  /**
   * PLUGIN = 2 points to Contact Form Maker
   */
  const PLUGIN = 1;

  /**
   * Get submissions.
   *
   * @param $params
   * @return array
   */
  function showsubmissions( $params = array() ) {
    $form_id = $params['id'];
    $startdate = $params['startdate'];
    $enddate = $params['enddate'];
    $submit_date = $params['submit_date'];
    $submitter_ip = $params['submitter_ip'];
    $username = $params['username'];
    $useremail = $params['useremail'];
    $form_fields = $params['form_fields'];
    $show = $params['show'];
    $show = explode(",", $show);
    $csv = isset($show[0]) ? $show[0] : 0;
    $xml = isset($show[1]) ? $show[1] : 0;
    $title = isset($show[2]) ? $show[2] : 0;
    $search = isset($show[3]) ? $show[3] : 0;
    $ordering = isset($show[4]) ? $show[4] : 0;
    $entries = isset($show[5]) ? $show[5] : 0;
    $views = isset($show[6]) ? $show[6] : 0;
    $conversion_rate = isset($show[7]) ? $show[7] : 0;
    $pagination = isset($show[8]) ? $show[8] : 0;
    $stats = isset($show[9]) ? $show[9] : 0;

    global $wpdb;
    $user = wp_get_current_user();
    $userGroups = $wpdb->get_var($wpdb->prepare("SELECT `user_id_wd` FROM " . $wpdb->prefix . "formmaker WHERE id='%d'", $form_id));
    $users = explode(',', $userGroups);
    $users = array_slice($users, 0, count($users) - 1);
    $show_submits = FALSE;
    if ( !is_user_logged_in() ) {
      if ( !in_array('guest', $users) ) {
        return FALSE;
      }
    }
    else {
      foreach ( $user->roles as $user_role ) {
        if ( in_array($user_role, $users) ) {
          $show_submits = TRUE;
        }
      }
      if ( !$show_submits ) {
        return FALSE;
      }
    }
    $from = $startdate;
    $to = $enddate;
    $filter_order = ( WDW_FM_Library(self::PLUGIN)->get('order_by') != '') ? WDW_FM_Library(self::PLUGIN)->get('order_by') : 'group_id';
    $filter_order_Dir = (WDW_FM_Library(self::PLUGIN)->get('asc_or_desc') == 'asc' || WDW_FM_Library(self::PLUGIN)->get('asc_or_desc') == 'desc') ? WDW_FM_Library(self::PLUGIN)->get('asc_or_desc') : 'desc';
    $where = array();
    $prepareArgs = array();
    $lists['startdate'] = WDW_FM_Library(self::PLUGIN)->get('startdate');
    $lists['enddate'] = WDW_FM_Library(self::PLUGIN)->get('enddate');
    $lists['hide_label_list'] =  WDW_FM_Library(self::PLUGIN)->get('hide_label_list');
    $lists['ip_search'] = WDW_FM_Library(self::PLUGIN)->get('ip_search');
    $lists['username_search'] = WDW_FM_Library(self::PLUGIN)->get('username_search');
    $lists['useremail_search'] = WDW_FM_Library(self::PLUGIN)->get('useremail_search');
    $limit = isset($_POST['page_number']) ? ( WDW_FM_Library(self::PLUGIN)->get('page_number',0,'intval') - 1 ) * 20 : 0;
    if ( $lists['ip_search'] ) {
      $where[] = 'ip LIKE %s ';
      $prepareArgs[] = '%' . esc_sql($lists['ip_search']) . '%';
    }
    if ( $lists['username_search'] ) {
      $where[] = 'user_id_wd IN (SELECT `id` FROM `' . $wpdb->prefix . 'users` WHERE `display_name` LIKE %s )';
      $prepareArgs[] = '%' . esc_sql($lists['username_search']) . '%';
    }
    if ( $lists['useremail_search'] ) {
      $where[] = 'user_id_wd IN (SELECT `id` FROM `' . $wpdb->prefix . 'users` WHERE `user_email` LIKE %s )';
      $prepareArgs[] = '%' . esc_sql($lists['useremail_search']) . '%';
    }
    if ( $from ) {
      if ( $lists['startdate'] != '' ) {
        if ( strtotime($from) > strtotime($lists['startdate']) ) {
          $where[] = "`date` >= %s ";
          $prepareArgs[] = $from .' 00:00:00';
        }
        else {
          $where[] = "`date` >= %s ";
          $prepareArgs[] = $lists['startdate'] .' 00:00:00';
        }
      }
      else {
        $where[] = "`date` >= '%s 00:00:00'";
        $prepareArgs[] = $from;
      }
    }
    else {
      if ( $lists['startdate'] != '' ) {
        $where[] = "  `date` >= %s ";
        $prepareArgs[] = $lists['startdate'] .'  00:00:00';
      }
    }
    if ( $to ) {
      if ( $lists['enddate'] != '' ) {
        if ( strtotime($to) < strtotime($lists['enddate']) ) {
          $where[] = "`date` <= %s ";
          $prepareArgs[] = $to .' 23:59:59';
        }
        else {
          $where[] = "`date` <= %s ";
          $prepareArgs[] = $lists['enddate'] .' 23:59:59';
        }
      }
      else {
        $where[] = "`date` <= %s ";
        $prepareArgs[] = $to .' 23:59:59';
      }
    }
    else {
      if ( $lists['enddate'] != '' ) {
        $where[] = "`date` <= %s ";
        $prepareArgs[] = $lists['enddate'] .' 23:59:59';
      }
    }
    $form_title = $wpdb->get_var( $wpdb->prepare('SELECT `title` FROM ' . $wpdb->prefix . 'formmaker WHERE id = %d ', $form_id) );
    $where[] = 'form_id="' . (int) $form_id . '"';
    $where = (count($where) ? '  ' . implode(' AND ', $where) : '');
    $where = $wpdb->prepare($where, $prepareArgs);
    $orderby = ' ';
    if ( $filter_order == 'id' or $filter_order == 'title' or $filter_order == 'mail' ) {
      $orderby = ' ORDER BY `date` desc';
    }
    else {
      if ( $filter_order == 'group_id' or $filter_order == 'date' or $filter_order == 'ip' ) {
        $orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir . '';
      }
      else {
        if ( $filter_order == 'display_name' or $filter_order == 'user_email' ) {
          $orderby = ' ORDER BY (SELECT `' . $filter_order . '` FROM `' . $wpdb->prefix . 'users` WHERE id=user_id_wd) ' . $filter_order_Dir . '';
        }
      }
    }
    $query = "SELECT distinct element_label FROM " . $wpdb->prefix . "formmaker_submits WHERE " . $where;
    $labels = $wpdb->get_col( $query );
    $total_entries = $wpdb->get_var( $wpdb->prepare('SELECT count(distinct group_id) FROM ' . $wpdb->prefix . 'formmaker_submits where form_id = %d ', $form_id) );
    $sorted_labels_type = array();
    $sorted_labels_id = array();
    $sorted_labels = array();
    $label_titles = array();
    $rows_ord = array();
    $rows = array();
    $total = 0;
    $join_count = '';
    $checked_ids = '';
    $stats_fields = '';
    if ( $labels ) {
      $label_id = array();
      $label_order = array();
      $label_order_original = array();
      $label_type = array();
      $this_form = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "formmaker WHERE id='%d'", $form_id));
      $checked_ids = $this_form->frontend_submit_fields;
      $stats_fields = $this_form->frontend_submit_stat_fields;
      if ( strpos($this_form->label_order, 'type_paypal_') ) {
        $this_form->label_order = $this_form->label_order . "item_total#**id**#Item Total#**label**#type_paypal_payment_total#****#total#**id**#Total#**label**#type_paypal_payment_total#****#0#**id**#Payment Status#**label**#type_paypal_payment_status#****#";
      }
      $ptn = "/[^a-zA-Z0-9_]/";
      $rpltxt = "";
      $label_all = explode('#****#', $this_form->label_order);
      $label_all = array_slice($label_all, 0, count($label_all) - 1);
      foreach ( $label_all as $key => $label_each ) {
        $label_id_each = explode('#**id**#', $label_each);
        array_push($label_id, $label_id_each[0]);
        $label_order_each = explode('#**label**#', $label_id_each[1]);
        array_push($label_order_original, $label_order_each[0]);
        $label_temp = preg_replace($ptn, $rpltxt, $label_order_each[0]);
        array_push($label_order, $label_temp);
        array_push($label_type, $label_order_each[1]);
      }
      $join_query = array();
      $join_where = array();
      $join = '';
      $is_first = TRUE;
      foreach ( $label_id as $key => $label ) {
        if ( in_array($label, $labels) ) {
          array_push($sorted_labels_type, $label_type[$key]);
          array_push($sorted_labels, $label_order[$key]);
          array_push($sorted_labels_id, $label);
          array_push($label_titles, $label_order_original[$key]);
          $search_temp = WDW_FM_Library(self::PLUGIN)->get($form_id . '_' . $label . '_search');
          $search_temp = strtolower($search_temp);
          $lists[$form_id . '_' . $label . '_search'] = $search_temp;
          if ( $search_temp ) {
            $join_query[] = 'search';
            $join_where[] = array( 'label' => $label, 'search' => esc_sql($search_temp) );
          }
        }
      }
      if ( strpos($filter_order, "_field") ) {
        if ( in_array(str_replace("_field", "", $filter_order), $labels) ) {
          $join_query[] = 'sort';
          $join_where[] = array( 'label' => str_replace("_field", "", $filter_order) );
        }
      }
      $cols = 'group_id';
      if ( $filter_order == 'date' or $filter_order == 'ip' ) {
        $cols = 'group_id, date, ip';
      }
      switch ( count($join_query) ) {
        case 0:
          $join = 'SELECT distinct group_id FROM ' . $wpdb->prefix . 'formmaker_submits WHERE ' . $where;
          break;
        case 1:
          if ( $join_query[0] == 'sort' ) {
            $join = $wpdb->prepare('SELECT group_id FROM ' . $wpdb->prefix . 'formmaker_submits WHERE ' . $where . ' AND element_label=%s ', $join_where[0]['label']);
            $join_count = $wpdb->prepare('SELECT count(group_id) FROM ' . $wpdb->prefix . 'formmaker_submits WHERE form_id = %d AND element_label = %s ', esc_sql((int) $form_id), $join_where[0]['label']);
            $orderby = ' ORDER BY `element_value` ' . $filter_order_Dir . '';
          }
          else {
           $join = $wpdb->prepare('SELECT group_id FROM ' . $wpdb->prefix . 'formmaker_submits WHERE element_label = %s AND element_value LIKE %s AND ', $join_where[0]['label'], '%' . $join_where[0]['search'] . '%') . $where;
          }
          break;
        default:
          $join = $wpdb->prepare('SELECT t.group_id FROM (SELECT ' . $cols . '  FROM ' . $wpdb->prefix . 'formmaker_submits WHERE ' . $where . ' AND element_label = %s AND  element_value LIKE %s ) as t ', $join_where[0]['label'], '%' . $join_where[0]['search'] . '%' );
          for ( $key = 1; $key < count($join_query); $key++ ) {
            if ( $join_query[$key] == 'sort' ) {
              $join .= $wpdb->prepare('LEFT JOIN (SELECT group_id as group_id' . $key . ', element_value   FROM ' . $wpdb->prefix . 'formmaker_submits WHERE ' . $where . ' AND element_label = %s ) as t' . $key . ' ON t' . $key . '.group_id' . $key . '=t.group_id ', $join_where[$key]['label']);
              $orderby = ' ORDER BY t' . $key . '.`element_value` ' . $filter_order_Dir . '';
            }
            else {
              $join .= $wpdb->prepare('INNER JOIN (SELECT group_id as group_id' . $key . ' FROM ' . $wpdb->prefix . 'formmaker_submits WHERE ' . $where . ' AND element_label = %s AND  element_value LIKE %s ) as t' . $key . ' ON t' . $key . '.group_id' . $key . '=t.group_id ', $join_where[$key]['label'], '%' . $join_where[$key]['search'] . '%');
            }
          }
          break;
      }
      $pos = strpos($join, 'SELECT t.group_id');
      if ( $pos === FALSE ) {
        $query = str_replace(array(
                               'SELECT group_id',
                               'SELECT distinct group_id',
                             ), array( 'SELECT count(distinct group_id)', 'SELECT count(distinct group_id)' ), $join);
      }
      else {
        $query = str_replace('SELECT t.group_id', 'SELECT count(t.group_id)', $join);
      }
      $total = $wpdb->get_var($query);
      $query = $join . ' ' . $orderby . ($pagination ?  $wpdb->prepare(' LIMIT %d', $limit) . ', 20 ' : '') . ' ';
      $rows_ord = $wpdb->get_col($query);
      $where2 = array();
      $prepareArgs2 = array();
      $where2 [] = "group_id=%d";
      $prepareArgs2[] = 0;
      foreach ( $rows_ord as $rows_ordd ) {
        $where2 [] = 'group_id=%d';
        $prepareArgs2[] = esc_sql($rows_ordd);
      }
      $where2 = (count($where2) ? ' WHERE ' . implode(' OR ', $where2) . '' : '');
      $query = $wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'formmaker_submits ' . $where2, $prepareArgs2);
      $rows = $wpdb->get_results($query);
      if ( $join_count ) {
        $total_sort = $wpdb->get_var($join_count);
        if ( $total_sort != $total_entries ) {
          $join_count = $total_sort;
        }
        else {
          $join_count = '';
        }
      }
    }
    $query = $wpdb->prepare('SELECT views FROM ' . $wpdb->prefix . 'formmaker_views WHERE form_id = %d ', $form_id);
    $total_views = $wpdb->get_var($query);

    $pageNav = "";
    $lists['order_Dir'] = $filter_order_Dir;
    $lists['order'] = $filter_order;
    $lists['total'] = $total;
    $lists['limit'] = (int) ($limit / 20 + 1);

    return array(
      "form_id" => $form_id,
      "startdate" => $startdate,
      "enddate" => $enddate,
      "submit_date" => $submit_date,
      "submitter_ip" => $submitter_ip,
      "username" => $username,
      "useremail" => $useremail,
      "form_fields" => $form_fields,
      "show" => $show,
      "csv" => $csv,
      "xml" => $xml,
      "title" => $title,
      "search" => $search,
      "ordering" => $ordering,
      "entries" => $entries,
      "views" => $views,
      "conversion_rate" => $conversion_rate,
      "pagination" => $pagination,
      "stats" => $stats,
      "rows" => $rows,
      "lists" => $lists,
      "pageNav" => $pageNav,
      "sorted_labels" => $sorted_labels,
      "label_titles" => $label_titles,
      "rows_ord" => $rows_ord,
      "sorted_labels_id" => $sorted_labels_id,
      "sorted_labels_type" => $sorted_labels_type,
      "total_entries" => $total_entries,
      "total_views" => $total_views,
      "join_count" => $join_count,
      "form_title" => $form_title,
      "checked_ids" => $checked_ids,
      "stats_fields" => $stats_fields,
      "is_paypal" => $this->is_paypal($form_id),
    );
  }

  /**
   * Get statistics.
   *
   * @return array
   */
  function show_stats() {
    global $wpdb;
    $form_id = WDW_FM_Library(self::PLUGIN)->get('form_id', 0, 'intval');
    $id = WDW_FM_Library(self::PLUGIN)->get('stat_id', 0, 'intval');
    $from = WDW_FM_Library(self::PLUGIN)->get('startdate');
    $to = WDW_FM_Library(self::PLUGIN)->get('enddate');
    $prepareArgs = array();
    $where = ' AND form_id=%d';
    $prepareArgs[] = $id;
    $prepareArgs[] = $form_id;
    if ( $from != '' ) {
      $where .= ' AND `date` >= %s ';
      $prepareArgs[] = $from . ' 00:00:00 ';
    }
    if ( $to != '' ) {
      $where .= ' AND `date` <= %s ';
      $prepareArgs[] = $to . ' 23:59:59 ';
    }

    $query = $wpdb->prepare('SELECT `element_value` FROM `' . $wpdb->prefix . 'formmaker_submits` WHERE `element_label` = %d '. $where, $prepareArgs);
    $choices = $wpdb->get_col( $query );

    return $choices;
  }

  /**
   * Get PayPal info.
   *
   * @param int $submission_id
   * @return array|null|object|void
   */
  function paypal_info( $submission_id = 0 ) {
    global $wpdb;
    $query = $wpdb->prepare("SELECT * FROM `" . $wpdb->prefix . "formmaker_sessions` WHERE group_id='%d'", $submission_id);

    return $wpdb->get_row($query);
  }

  /**
   * Check if form has a Paypal field.
   *
   * @param $form_id
   * @return bool
   */
  function is_paypal( $form_id = 0 ) {
    global $wpdb;
    $query = $wpdb->prepare("SELECT * FROM `" . $wpdb->prefix . "formmaker_sessions` WHERE form_id='%d'", $form_id);

    return ($wpdb->get_row($query) ? TRUE : FALSE);
  }

  /**
   * Get submissions to export.
   *
   * @return array
   */
  public function submissions_to_export() {
    global $wpdb;
    $user = wp_get_current_user();
    $id = WDW_FM_Library(self::PLUGIN)->get('id', 0, 'intval');
    $userGroups = $wpdb->get_var($wpdb->prepare("SELECT `user_id_wd` FROM " . $wpdb->prefix . "formmaker WHERE id='%d'", $id));
    $users = explode(',', $userGroups);
    $users = array_slice($users, 0, count($users) - 1);
    $allow_export = FALSE;
    $msg = __('You have no permissions to download', WDFMInstance(self::PLUGIN)->prefix);
    if ( !is_user_logged_in() ) {
      if ( !in_array('guest', $users) ) {
        wp_redirect($_SERVER["HTTP_REFERER"], $msg, 'error');
        die($msg);
      }
    }
    else {
      foreach ( $user->roles as $user_role ) {
        if ( in_array($user_role, $users) ) {
          $allow_export = TRUE;
        }
      }
      if ( !$allow_export ) {
        wp_redirect($_SERVER["HTTP_REFERER"], $msg, 'error');
        die($msg);
      }
    }
    $checked_ids = WDW_FM_Library(self::PLUGIN)->get('checked_ids');
    $from = WDW_FM_Library(self::PLUGIN)->get('form');
    $to = WDW_FM_Library(self::PLUGIN)->get('to');
    $form_id = $id;
    $paypal_info_fields = array(
      'currency',
      'ord_last_modified',
      'status',
      'full_name',
      'fax',
      'mobile_phone',
      'email',
      'phone',
      'address',
      'paypal_info',
      'ipn',
      'tax',
      'shipping'
    );
    $paypal_info_labels = array(
      'Currency',
      'Last modified',
      'Status',
      'Full Name',
      'Fax',
      'Mobile phone',
      'Email',
      'Phone',
      'Address',
      'Paypal info',
      'IPN',
      'Tax',
      'Shipping'
    );
    $where_range = ' ';
    $labels = array();
    $prepareArgs = array();
    if ( $from ) {
      $where_range .= ' AND DATE_FORMAT(date, "%Y-%m-%d") >= %s ';
      $prepareArgs[] = $from;
    }
    if ( $to ) {
      $where_range .= ' AND DATE_FORMAT(date, "%Y-%m-%d") <= %s ';
      $prepareArgs[] = $to;
    }
    if ( $checked_ids ) {
      $labels = explode(',', $checked_ids);
      $labels = array_slice($labels, 0, count($labels) - 1);
      $query = 'SELECT id FROM ' . $wpdb->prefix . 'formmaker_submits where form_id = %d ' . $where_range;
      $rows = $wpdb->get_col( $wpdb->prepare($query, $form_id) );
    }
    else {
      $rows = '';
    }
    $query_lable = $wpdb->prepare("SELECT label_order,title FROM " . $wpdb->prefix . "formmaker where id='%d'", $form_id);
    $rows_lable = $wpdb->get_row($query_lable);
    $ptn = "/[^a-zA-Z0-9_]/";
    $rpltxt = "";
    $title = $rows_lable->title;
    $sorted_labels_id = array();
    $sorted_labels = array();
    $label_titles = array();
    if ( $labels ) {
      $label_id = array();
      $label_order = array();
      $label_order_original = array();
      $label_type = array();
      $label_all = explode('#****#', $rows_lable->label_order);
      $label_all = array_slice($label_all, 0, count($label_all) - 1);
      foreach ( $label_all as $key => $label_each ) {
        $label_id_each = explode('#**id**#', $label_each);
        array_push($label_id, $label_id_each[0]);
        $label_oder_each = explode('#**label**#', $label_id_each[1]);
        array_push($label_order_original, $label_oder_each[0]);
        $label_temp = preg_replace($ptn, $rpltxt, $label_oder_each[0]);
        array_push($label_order, $label_temp);
        array_push($label_type, $label_oder_each[1]);
      }
      foreach ( $label_id as $key => $label ) {
        if ( in_array($label, $labels) ) {
          array_push($sorted_labels, $label_order[$key]);
          array_push($sorted_labels_id, $label);
          array_push($label_titles, $label_order_original[$key]);
        }
      }
    }
    $m = count($sorted_labels);
    $group_id_s = array();
    if ( count($rows) > 0 and $checked_ids ) {
      $query = $wpdb->prepare('SELECT distinct group_id FROM ' . $wpdb->prefix . 'formmaker_submits where form_id = %d ', $form_id);
      $group_id_s = $wpdb->get_col($query);
    }
    $data = array();
    for ( $www = 0; $www < count($group_id_s); $www++ ) {
      $data_temp = array();
      $i = $group_id_s[$www];
      $query = 'SELECT `date`, `ip`, `user_id_wd` FROM ' . $wpdb->prefix . 'formmaker_submits where group_id = %d ' . $where_range;
      $f = $wpdb->get_row( $wpdb->prepare($query, $i) );
      $date = get_date_from_gmt( $f->date );
      $ip = $f->ip;
      $user_id = get_userdata($f->user_id_wd);
      $user_name = $user_id ? $user_id->display_name : "";
      $user_email = $user_id ? $user_id->user_email : "";
      if ( in_array('submit_id', $labels) ) {
        $data_temp['ID'] = $i;
      }
      if ( in_array('submit_date', $labels) ) {
        $data_temp['Submit date'] = $date;
      }
      if ( in_array('submitter_ip', $labels) ) {
        $data_temp['Ip'] = $ip;
      }
      if ( in_array('username', $labels) ) {
        $data_temp['Submitter\'s Username'] = $user_name;
      }
      if ( in_array('useremail', $labels) ) {
        $data_temp['Submitter\'s Email Address'] = $user_email;
      }
      for ( $h = 0; $h < $m; $h++ ) {
        if ( isset($data_temp[$label_titles[$h]]) ) {
          $label_titles[$h] .= '(1)';
        }
        $query = 'SELECT * FROM ' . $wpdb->prefix . 'formmaker_submits where group_id = %d AND element_label = %s ' . $where_range;
        $t = $wpdb->get_row( $wpdb->prepare($query, array($i, $sorted_labels_id[$h])) );
        if ( $t ) {
          if ( strpos($t->element_value, "*@@url@@*") ) {
            $new_file = str_replace("*@@url@@*", '', $t->element_value);
            $new_filename = explode('/', $new_file);
            $data_temp[$label_titles[$h]] = $new_file;
          }
          else {
            if ( strpos($t->element_value, "***br***") ) {
              $element_value = str_replace("***br***", ', ', $t->element_value);
              if ( strpos($element_value, "***quantity***") ) {
                $element_value = str_replace("***quantity***", '', $element_value);
              }
              if ( strpos($element_value, "***property***") ) {
                $element_value = str_replace("***property***", '', $element_value);
              }
              if ( substr($element_value, -2) == ', ' ) {
                $data_temp[$label_titles[$h]] = substr($element_value, 0, -2);
              }
              else {
                $data_temp[$label_titles[$h]] = $element_value;
              }
            }
            else {
              if ( strpos($t->element_value, "***map***") ) {
                $data_temp[$label_titles[$h]] = 'Longitude:' . substr(str_replace("***map***", ', Latitude:', $t->element_value), 0, -2);
              }
              else {
                if ( strpos($t->element_value, "@@@") > -1 || $t->element_value == "@@@" || $t->element_value == "@@@@@@@@@" ) {
                  $data_temp[$label_titles[$h]] = str_replace("@@@", ' ', $t->element_value);
                }
                else {
                  if ( $t->element_value == "::" || $t->element_value == ":" || $t->element_value == "--" ) {
                    $data_temp[$label_titles[$h]] = str_replace(array( ":", "-" ), "", $t->element_value);
                  }
                  else {
                    if ( strpos($t->element_value, "***grading***") ) {
                      $element = str_replace("***grading***", '', $t->element_value);
                      $grading = explode(":", $element);
                      $items_count = sizeof($grading) - 1;
                      $items = "";
                      $total = "";
                      for ( $k = 0; $k < $items_count / 2; $k++ ) {
                        $items .= $grading[$items_count / 2 + $k] . ": " . $grading[$k] . ", ";
                        $total += $grading[$k];
                      }
                      $items .= "Total: " . $total;
                      $data_temp[$label_titles[$h]] = $items;
                    }
                    else {
                      if ( strpos($t->element_value, "***matrix***") ) {
                        $element = str_replace("***matrix***", '', $t->element_value);
                        $matrix_value = explode('***', $element);
                        $matrix_value = array_slice($matrix_value, 0, count($matrix_value) - 1);
                        $mat_rows = $matrix_value[0];
                        $mat_columns = $matrix_value[$mat_rows + 1];
                        $matrix = "";
                        $aaa = Array();
                        $var_checkbox = 1;
                        for ( $k = 1; $k <= $mat_rows; $k++ ) {
                          if ( $matrix_value[$mat_rows + $mat_columns + 2] == "radio" ) {
                            if ( $matrix_value[$mat_rows + $mat_columns + 2 + $k] == 0 ) {
                              $checked = "0";
                              $aaa[1] = "";
                            }
                            else {
                              $aaa = explode("_", $matrix_value[$mat_rows + $mat_columns + 2 + $k]);
                            }
                            for ( $l = 1; $l <= $mat_columns; $l++ ) {
                              if ( $aaa[1] == $l ) {
                                $checked = '1';
                              }
                              else {
                                $checked = "0";
                              }
                              $matrix .= '[' . $matrix_value[$k] . ',' . $matrix_value[$mat_rows + 1 + $l] . ']=' . $checked . "; ";
                            }
                          }
                          else {
                            if ( $matrix_value[$mat_rows + $mat_columns + 2] == "checkbox" ) {
                              for ( $l = 1; $l <= $mat_columns; $l++ ) {
                                if ( $matrix_value[$mat_rows + $mat_columns + 2 + $var_checkbox] == 1 ) {
                                  $checked = '1';
                                }
                                else {
                                  $checked = '0';
                                }
                                $matrix .= '[' . $matrix_value[$k] . ',' . $matrix_value[$mat_rows + 1 + $l] . ']=' . $checked . "; ";
                                $var_checkbox++;
                              }
                            }
                            else {
                              if ( $matrix_value[$mat_rows + $mat_columns + 2] == "text" ) {
                                for ( $l = 1; $l <= $mat_columns; $l++ ) {
                                  $text_value = $matrix_value[$mat_rows + $mat_columns + 2 + $var_checkbox];
                                  $matrix .= '[' . $matrix_value[$k] . ',' . $matrix_value[$mat_rows + 1 + $l] . ']=' . $text_value . "; ";
                                  $var_checkbox++;
                                }
                              }
                              else {
                                for ( $l = 1; $l <= $mat_columns; $l++ ) {
                                  $selected_text = $matrix_value[$mat_rows + $mat_columns + 2 + $var_checkbox];
                                  $matrix .= '[' . $matrix_value[$k] . ',' . $matrix_value[$mat_rows + 1 + $l] . ']=' . $selected_text . "; ";
                                  $var_checkbox++;
                                }
                              }
                            }
                          }
                        }
                        $data_temp[$label_titles[$h]] = $matrix;
                      }
                      else {
                        $data_temp[$label_titles[$h]] = ' ' . $t->element_value;
                      }
                    }
                  }
                }
              }
            }
          }
        }
        else {
          $data_temp[$label_titles[$h]] = '';
        }
      }
      if ( in_array('item_total', $labels) ) {
        $query = 'SELECT `element_value` FROM ' . $wpdb->prefix . 'formmaker_submits where group_id = %d AND element_label = %s ' . $where_range;
        $item_total = $wpdb->get_var( $wpdb->prepare($query, array($i, 'item_total') ) );
        $data_temp['Item Total'] = $item_total;
      }
      if ( in_array('total', $labels) ) {
        $query = 'SELECT `element_value` FROM ' . $wpdb->prefix . 'formmaker_submits where group_id = %d AND element_label = %s ' . $where_range;
        $total = $wpdb->get_var( $wpdb->prepare($query, array($i, 'total')) );
        $data_temp['Total'] = $total;
      }
      if ( in_array('0', $labels) ) {
        $query = 'SELECT `element_value` FROM ' . $wpdb->prefix . 'formmaker_submits where group_id = %d AND element_label = %d ' . $where_range;
        $payment_status = $wpdb->get_var( $wpdb->prepare($query, array($i, 0) ) );
        $data_temp['Payment Status'] = $payment_status;
      }
      if ( in_array('payment_info', $labels) ) {
        $query = 'SELECT * FROM ' . $wpdb->prefix . 'formmaker_sessions where group_id= %d ' . $where_range;
        $paypal_info = $wpdb->get_row( $wpdb->prepare($query, $i) );
        foreach ( $paypal_info_fields as $key => $paypal_info_field ) {
          $data_temp['PAYPAL_' . $paypal_info_labels[$key]] = $paypal_info ? $paypal_info->$paypal_info_field : '';
        }
      }
      $data[] = $data_temp;
    }

    return array( 'title' => $title, 'data' => $data, 'id' => $form_id );
  }
}