<?php

/**
 * Class FMViewForm_submissions
 */
class FMViewForm_submissions {
  /**
   * PLUGIN = 2 points to Contact Form Maker
   */
  const PLUGIN = 1;

  /**
   * Display.
   *
   * @param $params
   */
  public function display( $params = array() ) {
    $fm_nonce = wp_create_nonce('fm_ajax_nonce');
    if ( $params === FALSE ) {
      echo WDW_FM_Library(self::PLUGIN)->message(__('You have no permission to view submissions.', WDFMInstance(self::PLUGIN)->prefix), 'fm-notice-success');

      return;
    }
    wp_enqueue_style(WDFMInstance(self::PLUGIN)->handle_prefix . '-jquery-ui');
    wp_enqueue_style(WDFMInstance(self::PLUGIN)->handle_prefix . '-submissions_css');
    wp_enqueue_script('jquery-ui-datepicker');
    if ( function_exists('wp_add_inline_script') ) { // Since Wordpress 4.5.0
      wp_add_inline_script('jquery-ui-datepicker', WDW_FM_Library(self::PLUGIN)->localize_ui_datepicker());
    }
    else {
      echo '<script>' . WDW_FM_Library(self::PLUGIN)->localize_ui_datepicker() . '</script>';
    }

    $form_id = $params['form_id'];
    $startdate = $params['startdate'];
    $enddate = $params['enddate'];
    $submit_date = $params['submit_date'];
    $submitter_ip = $params['submitter_ip'];
    $username = $params['username'];
    $useremail = $params['useremail'];
    $form_fields = $params['form_fields'];
    $csv = $params['csv'];
    $xml = $params['xml'];
    $title = $params['title'];
    $search = $params['search'];
    $ordering = $params['ordering'];
    $entries = $params['entries'];
    $views = $params['views'];
    $conversion_rate = $params['conversion_rate'];
    $pagination = $params['pagination'];
    $stats = $params['stats'];
    $is_paypal = $params['is_paypal'];

    $rows = $params["rows"];
    $n = count($rows);
    $lists = $params["lists"];
    $labels = $params["sorted_labels"];
    $label_titles = $params["label_titles"];
    $group_id_s = $params["rows_ord"];
    $labels_id = $params["sorted_labels_id"];
    $sorted_labels_type = $params["sorted_labels_type"];
    $total_entries = $params["total_entries"];
    $total_views = $params["total_views"];
    $join_count = $params["join_count"];
    $form_title = $params["form_title"];
    $checked_ids = $params["checked_ids"];
    $stats_fields = $params["stats_fields"];
    $current_url = htmlentities($_SERVER['REQUEST_URI']);
    $order_by = WDW_FM_Library(self::PLUGIN)->get('order_by', 'group_id');
    $asc_or_desc = (WDW_FM_Library(self::PLUGIN)->get('asc_or_desc', 'desc') == 'desc' ? 'desc' : 'asc');

    if ( $checked_ids ) {
      $checked_ids = explode(',', $checked_ids);
      $checked_ids = array_slice($checked_ids, 0, count($checked_ids) - 1);
    }
    else {
      $checked_ids = array();
    }
    $label_titles_copy = $label_titles;
    $export_ids = "";
    if ( $form_fields ) {
      $export_ids .= "submit_id,";
    }
    if ( $submit_date ) {
      $export_ids .= "submit_date,";
    }
    if ( $submitter_ip ) {
      $export_ids .= "submitter_ip,";
    }
    if ( $username ) {
      $export_ids .= "username,";
    }
    if ( $useremail ) {
      $export_ids .= "useremail,";
    }
    for ( $i = 0; $i < count($labels); $i++ ) {
      if ( $form_fields && !in_array($labels_id[$i], $checked_ids) ) {
        $export_ids .= $labels_id[$i] . ",";
      }
    }
    add_thickbox();
    ?>
    <script type="text/javascript">
      function fm_form_submit(event, form_id, task, id) {
        if (document.getElementById(form_id)) {
          document.getElementById(form_id).submit();
        }
        if (event.preventDefault) {
          event.preventDefault();
        }
        else {
          event.returnValue = false;
        }
      }

      function remove_all() {
        if (document.getElementById('startdate')) {
          document.getElementById('startdate').value = '';
        }
        if (document.getElementById('enddate')) {
          document.getElementById('enddate').value = '';
        }
        if (document.getElementById('ip_search')) {
          document.getElementById('ip_search').value = '';
        }
        if (document.getElementById('username_search')) {
          document.getElementById('username_search').value = '';
        }
        if (document.getElementById('useremail_search')) {
          document.getElementById('useremail_search').value = '';
        }
        <?php
        for ( $i = 0; $i < count($labels); $i++ ) {
          echo "if(document.getElementById('" . $form_id . '_' . $labels_id[$i] . "_search'))
          document.getElementById('" . $form_id . '_' . $labels_id[$i] . "_search').value='';";
        }
        ?>
      }
      function show_hide_filter() {
        if (document.getElementById('fm-fields-filter').style.display == "none") {
          document.getElementById('fm-fields-filter').style.display = '';
        }
        else {
          document.getElementById('fm-fields-filter').style.display = "none";
        }
      }
      jQuery(function() {
        jQuery(".wd-datepicker").datepicker();
        jQuery(".wd-datepicker").datepicker("option", "dateFormat", "yy-mm-dd");
      });
    </script>
    <form action="<?php echo $current_url; ?>" method="post" name="adminForm" id="adminForm">
      <input type="hidden" name="asc_or_desc" id="asc_or_desc" value="<?php echo $asc_or_desc; ?>" />
      <input type="hidden" name="order_by" id="order_by" value="<?php echo $order_by; ?>" />
      <input type="hidden" id="task" name="task" value="" />
      <div class="submission_params">
        <?php
        if ( isset($form_id) and $form_id > 0 ) {
          if ( $title ) {
            ?>
            <div class="form_title"><strong><?php echo $form_title; ?></strong></div>
            <?php
          }
          ?>
          <div>
            <?php
            if ( $entries ) {
              ?>
              <div class="reports" style="width: 80px;">
                <strong><?php _e('Entries', WDFMInstance(self::PLUGIN)->prefix); ?></strong><br />
                <?php echo $total_entries; ?>
              </div>
              <?php
            }
            if ( $views ) {
              ?>
              <div class="reports" style="width: 80px;">
                <strong><?php _e('Views', WDFMInstance(self::PLUGIN)->prefix); ?></strong><br />
                <?php echo $total_views; ?>
              </div>
              <?php
            }
            if ( $conversion_rate ) {
              ?>
              <div class="reports" style="width: 130px;">
                <strong><?php _e('Conversion Rate', WDFMInstance(self::PLUGIN)->prefix); ?></strong><br />
                <?php
                if ( $total_views ) {
                  echo round((($total_entries / $total_views) * 100), 2) . '%';
                }
                else {
                  echo '0%';
                }
                ?>
              </div>
              <?php
            }
            if ( $csv || $xml ) {
              ?>
              <div <?php echo (($entries || $views || $conversion_rate) ? 'class="csv_xml"' : ''); ?>>
                <?php _e('Export to', WDFMInstance(self::PLUGIN)->prefix); ?>
                <?php
                if ( $csv ) {
                  ?>
                  <input type="button" value="CSV" onclick="window.location='<?php echo add_query_arg(array(
                                                                                                        'action' => 'frontend_generate_csv',
                                                                                                        'page' => 'form_submissions',
                                                                                                        'id' => $form_id,
                                                                                                        'checked_ids' => $export_ids,
                                                                                                        'nonce'=>$fm_nonce,
                                                                                                        'from' => $startdate,
                                                                                                        'to' => $enddate
                                                                                                      ), admin_url('admin-ajax.php')) ?>'" />&nbsp;
                  <?php
                }
                if ( $xml ) {
                  ?>
                  <input type="button" value="XML" onclick="window.location='<?php echo add_query_arg(array(
                                                                                                        'action' => 'frontend_generate_xml',
                                                                                                        'page' => 'form_submissions',
                                                                                                        'id' => $form_id,
                                                                                                        'checked_ids' => $export_ids,
                                                                                                        'nonce'=>$fm_nonce,
                                                                                                        'from' => $startdate,
                                                                                                        'to' => $enddate
                                                                                                      ), admin_url('admin-ajax.php')) ?>'" />&nbsp;
                  <?php
                }
                ?>
              </div>
              <?php
            }
            ?>
          </div>
          <?php
          if ( $search || $pagination ) {
            ?>
            <div class="search_and_pagination">
              <?php
              if ( $search ) {
                ?>
                <div>
                  <input type="hidden" name="hide_label_list" value="<?php echo $lists['hide_label_list']; ?>" />
                  <img id="filter_img" src="<?php echo WDFMInstance(self::PLUGIN)->plugin_url . '/images/filter_show.png'; ?>" width="40" style="vertical-align: middle; cursor: pointer;" onclick="show_hide_filter()" title="<?php _e('Search by fields', WDFMInstance(self::PLUGIN)->prefix); ?>" />
                  <input type="button" onclick="this.form.submit();" style="vertical-align: middle; cursor: pointer;" value="<?php _e('GO', WDFMInstance(self::PLUGIN)->prefix); ?>" />
                  <input type="button" onclick="remove_all();this.form.submit();" style="vertical-align:middle; cursor:pointer" value="<?php _e('Reset', WDFMInstance(self::PLUGIN)->prefix); ?>" />
                </div>
                <div>
                  <?php
                  if ( $join_count ) {
                    echo sprintf(__('%s of %s submissions are not shown, as the field you sorted by is missing in those submissions.', WDFMInstance(self::PLUGIN)->prefix), ($total_entries - $join_count), $total_entries);
                  }
                  ?>
                </div>
                <?php
              }
              if ( $pagination ) {
                ?>
                <div class="tablenav top">
                  <?php WDW_FM_Library(self::PLUGIN)->html_page_nav($lists['total'], $lists['limit'], 'adminForm'); ?>
                </div>
                <?php
              }
              ?>
            </div>
            <?php
          }
        }
        ?>
      </div>
      <div style="overflow-x: scroll;">
        <table class="submissions" width="100%">
          <thead>
          <tr>
            <th width="3%"><?php echo '#'; ?></th>
            <?php
            if ( $form_fields ) {
              echo '<th width="4%" class="submitid_fc"';
              if ( !(strpos($lists['hide_label_list'], '@submitid@') === FALSE) || in_array('submit_id', $checked_ids) ) {
                echo 'style="display:none;"';
              }
              echo '>';
              if ( $ordering ) {
                echo '<a href="" onclick="document.getElementById(\'order_by\').value = \'group_id\'; document.getElementById(\'asc_or_desc\').value = \'' . (($order_by == "group_id" && $asc_or_desc == 'asc') ? 'desc' : 'asc') . '\'; fm_form_submit(event, \'adminForm\');">
                  <span>Id</span>
                  <span>' . ($order_by == "group_id" ? ($asc_or_desc == "asc" ? "&#x25B2;" : "&#x25BC;") : "") . '</span>
                </a>';
              }
              else {
                echo 'Id';
              }
              echo '</th>';
            }
            if ( $submit_date ) {
              echo '<th align="center" class="submitdate_fc wd_front_submissons_th"';
              if ( !(strpos($lists['hide_label_list'], '@submitdate@') === FALSE) ) {
                echo 'style="display:none;"';
              }
              echo '>';
              if ( $ordering ) {
                echo '<a href="" onclick="document.getElementById(\'order_by\').value = \'date\'; document.getElementById(\'asc_or_desc\').value = \'' . (($order_by == "date" && $asc_or_desc == 'asc') ? 'desc' : 'asc') . '\'; fm_form_submit(event, \'adminForm\');">
                  <span>Submit date</span>
                  <span>' . ($order_by == "date" ? ($asc_or_desc == "asc" ? "&#x25B2;" : "&#x25BC;") : "") . '</span>
                </a>';
              }
              else {
                echo 'Submit Date';
              }
              echo '</th>';
            }
            if ( $submitter_ip ) {
              echo '<th align="center" class="submitterip_fc wd_front_submissons_th"';
              if ( !(strpos($lists['hide_label_list'], '@submitterip@') === FALSE) ) {
                echo 'style="display:none;"';
              }
              echo '>';
              if ( $ordering ) {
                echo '<a href="" onclick="document.getElementById(\'order_by\').value = \'ip\'; document.getElementById(\'asc_or_desc\').value = \'' . (($order_by == "ip" && $asc_or_desc == 'asc') ? 'desc' : 'asc') . '\'; fm_form_submit(event, \'adminForm\');">
                  <span>Submitter\'s IP Address</span>
                  <span>' . ($order_by == "ip" ? ($asc_or_desc == "asc" ? "&#x25B2;" : "&#x25BC;") : "") . '</span>
                </a>';
              }
              else {
                echo 'Submitter\'s IP Address';
              }
              echo '</th>';
            }
            if ( $username ) {
              echo '<th class="submitterusername_fc wd_front_submissons_th"';
              if ( !(strpos($lists['hide_label_list'], '@submitterusername@') === FALSE) ) {
                echo 'style="display:none;"';
              }
              echo '>';
              if ( $ordering ) {
                echo '<a href="" onclick="document.getElementById(\'order_by\').value = \'display_name\'; document.getElementById(\'asc_or_desc\').value = \'' . (($order_by == "display_name" && $asc_or_desc == 'asc') ? 'desc' : 'asc') . '\'; fm_form_submit(event, \'adminForm\');">
                  <span>Submitter\'s Username</span>
                  <span>' . ($order_by == "display_name" ? ($asc_or_desc == "asc" ? "&#x25B2;" : "&#x25BC;") : "") . '</span>
                </a>';
              }
              else {
                echo 'Submitter\'s Username';
              }
              echo '</th>';
            }
            if ( $useremail ) {
              echo '<th class="submitteremail_fc wd_front_submissons_th"';
              if ( !(strpos($lists['hide_label_list'], '@submitteremail@') === FALSE) ) {
                echo 'style="display:none;"';
              }
              echo '>';
              if ( $ordering ) {
                echo '<a href="" onclick="document.getElementById(\'order_by\').value = \'user_email\'; document.getElementById(\'asc_or_desc\').value = \'' . (($order_by == "user_email" && $asc_or_desc == 'asc') ? 'desc' : 'asc') . '\'; fm_form_submit(event, \'adminForm\');">
                  <span>Submitter\'s Email Address</span>
                  <span>' . ($order_by == "user_email" ? ($asc_or_desc == "asc" ? "&#x25B2;" : "&#x25BC;") : "") . '</span>
                </a>';
              }
              else {
                echo 'Submitter\'s Email Address';
              }
              echo '</th>';
            }
            $ispaypal = FALSE;
            for ( $i = 0; $i < count($labels); $i++ ) {
              if ( $form_fields && !in_array($labels_id[$i], $checked_ids) ) {
                if ( strpos($lists['hide_label_list'], '@' . $labels_id[$i] . '@') === FALSE ) {
                  $styleStr = '';
                }
                else {
                  $styleStr = 'style="display:none;"';
                }
                $field_title = $label_titles_copy[$i];
                if ( $sorted_labels_type[$i] == 'type_paypal_payment_status' ) {
                  $ispaypal = TRUE;
                }
                echo '<th align="center" class="' . $labels_id[$i] . '_fc wd_front_submissons_th" ' . $styleStr . '>';
                if ( $ordering ) {
                  echo '<a href="" onclick="document.getElementById(\'order_by\').value = \'' . $labels_id[$i] . '_field\'; document.getElementById(\'asc_or_desc\').value = \'' . (($order_by == $labels_id[$i] . '_field' && $asc_or_desc == 'asc') ? 'desc' : 'asc') . '\'; fm_form_submit(event, \'adminForm\');">
                    <span>' . $field_title . '</span>
                    <span>' . ($order_by == $labels_id[$i] . "_field" ? ($asc_or_desc == "asc" ? "&#x25B2;" : "&#x25BC;") : "") . '</span>
                  </a>';
                }
                else {
                  echo $field_title;
                }
                echo '</th>';
              }
            }
            if ( $form_fields && $is_paypal && !in_array('payment_info', $checked_ids) ) {
              if ( strpos($lists['hide_label_list'], '@payment_info@') === FALSE ) {
                $styleStr2 = 'aa';
              }
              else {
                $styleStr2 = 'style="display:none;"';
              }
              echo '<th class="payment_info_fc wd_front_submissons_th" ' . $styleStr2 . '>Payment Info</th>';
            }
            ?>

          </tr>
          <tr id="fm-fields-filter" style="display:none">
            <th width="3%"></th>
            <?php if ( $form_fields ) { ?>
              <th width="4%" class="submitid_fc" <?php if ( !(strpos($lists['hide_label_list'], '@submitid@') === FALSE) || in_array('submit_id', $checked_ids) ) {
                echo 'style="display:none;"';
              } ?> >
			</th>
			<?php } ?>
            <?php if ( $submit_date ): ?>
              <th class="submitdate_fc" style="text-align:left; <?php if ( !(strpos($lists['hide_label_list'], '@submitdate@') === FALSE) ) {
                echo 'display:none;';
              } ?>" align="center">
                <table class="simple_table">
                  <tr>
                    <td><?php echo __('From', WDFMInstance(self::PLUGIN)->prefix); ?>:</td>
                    <td>
                      <input class="inputbox wd-datepicker" type="text" name="startdate" id="startdate" size="10" maxlength="10" value="<?php echo $lists['startdate']; ?>" />
                    </td>
                    <td></td>
                  </tr>
                  <tr>
                    <td><?php echo __('To', WDFMInstance(self::PLUGIN)->prefix); ?>:</td>
                    <td>
                      <input class="inputbox wd-datepicker" type="text" name="enddate" id="enddate" size="10" maxlength="10" value="<?php echo $lists['enddate']; ?>" />
                    </td>
                    <td></td>
                  </tr>
                </table>
              </th>
            <?php endif;
            if ( $submitter_ip ): ?>
              <th class="submitterip_fc" <?php if ( !(strpos($lists['hide_label_list'], '@submitterip@') === FALSE) ) {
                echo 'style="display:none;"';
              } ?>>
                <input class="inputbox" type="text" name="ip_search" id="ip_search" value="<?php echo $lists['ip_search'] ?>" onChange="this.form.submit();" style="width:96%" />
              </th>
            <?php endif;
            if ( $username ): ?>
              <th width="100" class="submitterusername_fc" <?php if ( !(strpos($lists['hide_label_list'], '@submitterusername@') === FALSE) ) {
                echo 'style="display:none;"';
              } ?>>
                <input class="inputbox" type="text" name="username_search" id="username_search" value="<?php echo $lists['username_search'] ?>" onChange="this.form.submit();" style="width:150px" />
              </th>
            <?php endif;
            if ( $useremail ): ?>
              <th width="100" class="submitteremail_fc" <?php if ( !(strpos($lists['hide_label_list'], '@submitteremail@') === FALSE) ) {
                echo 'style="display:none;"';
              } ?>>
                <input class="inputbox" type="text" name="useremail_search" id="useremail_search" value="<?php echo $lists['useremail_search'] ?>" onChange="this.form.submit();" style="width:150px" />
              </th>
            <?php endif;
            $ka_fielderov_search = FALSE;
            if ( $lists['ip_search'] || $lists['startdate'] || $lists['enddate'] || $lists['username_search'] || $lists['useremail_search'] ) {
              $ka_fielderov_search = TRUE;
            }
            for ( $i = 0; $i < count($labels); $i++ ) {
              if ( strpos($lists['hide_label_list'], '@' . $labels_id[$i] . '@') === FALSE ) {
                $styleStr = '';
              }
              else {
                $styleStr = 'style="display:none;"';
              }
              if ( !$ka_fielderov_search ) {
                if ( $lists[$form_id . '_' . $labels_id[$i] . '_search'] ) {
                  $ka_fielderov_search = TRUE;
                }
              }
              if ( $form_fields && !in_array($labels_id[$i], $checked_ids) )
                switch ( $sorted_labels_type[$i] ) {
                  case 'type_mark_map':
                    echo '<th class="' . $labels_id[$i] . '_fc" ' . $styleStr . '>' . '</th>';
                    break;
                  case 'type_paypal_payment_status':
                    echo '<th class="' . $labels_id[$i] . '_fc" ' . $styleStr . '>';
                    ?>
                    <select name="<?php echo $form_id . '_' . $labels_id[$i]; ?>_search" id="<?php echo $form_id . '_' . $labels_id[$i]; ?>_search" onChange="this.form.submit();" value="<?php echo $lists[$form_id . '_' . $labels_id[$i] . '_search']; ?>">
                      <option value=""></option>
                      <option value="canceled"><?php echo __('Canceled', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="cleared"><?php echo __('Cleared', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="cleared by payment review"><?php echo __('Cleared by payment review', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="completed"><?php echo __('Completed', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="denied"><?php echo __('Denied', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="failed"><?php echo __('Failed', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="held"><?php echo __('Held', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="in progress"><?php echo __('In progress', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="on hold"><?php echo __('On hold', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="paid"><?php echo __('Paid', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="partially refunded"><?php echo __('Partially refunded', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="pending verification"><?php echo __('Pending verification', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="placed"><?php echo __('Placed', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="processing"><?php echo __('Processing', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="refunded"><?php echo __('Refunded', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="refused"><?php echo __('Refused', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="removed"><?php echo __('Removed', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="returned"><?php echo __('Returned', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="reversed"><?php echo __('Reversed', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="temporary hold"><?php echo __('Temporary hold', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                      <option value="unclaimed"><?php echo __('Unclaimed', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                    </select>
                    <script>
                      var element = document.getElementById('<?php echo $form_id . '_' . $labels_id[$i]; ?>_search');
                      element.value = '<?php echo $lists[$form_id . '_' . $labels_id[$i] . '_search']; ?>';
                    </script>
                    <?php
                    echo '</th>';
                    break;
                  default :
                    echo '<th class="' . $labels_id[$i] . '_fc" ' . $styleStr . '>' . '<input name="' . $form_id . '_' . $labels_id[$i] . '_search" id="' . $form_id . '_' . $labels_id[$i] . '_search" class="inputbox" type="text" value="' . $lists[$form_id . '_' . $labels_id[$i] . '_search'] . '"  onChange="this.form.submit();" style="width:96%">' . '</th>';
                    break;
                }
            }
            if ( $form_fields && $is_paypal && !in_array('payment_info', $checked_ids) ) {
              if ( strpos($lists['hide_label_list'], '@payment_info@') === FALSE ) {
                $styleStr2 = '';
              }
              else {
                $styleStr2 = 'style="display:none;"';
              }
              echo '<th class="payment_info_fc" ' . $styleStr2 . '></th>';
            }
            ?>
          </tr>
          </thead>
          <?php
          $k = 0;
          $m = count($labels);
          for ( $www = 0, $qqq = count($group_id_s); $www < $qqq; $www++ ) {
            $i = $group_id_s[$www];
            $temp = array();
            for ( $j = 0; $j < $n; $j++ ) {
              $row = &$rows[$j];
              if ( $row->group_id == $i ) {
                array_push($temp, $row);
              }
            }
            $f = $temp[0];
            $date = get_date_from_gmt( $f->date );
            $ip = $f->ip;
            $user_id = get_userdata($f->user_id_wd);
            $user_name = $user_id ? $user_id->display_name : "";
            $user_email = $user_id ? $user_id->user_email : "";
            ?>
            <tr class="<?php echo "row$k"; ?>">
              <td align="center"><?php echo $www + 1 + ($lists['limit'] - 1) * 20; ?></td>
              <?php if ( $form_fields ) { ?>
				<td align="center" class="submitid_fc" <?php if ( !(strpos($lists['hide_label_list'], '@submitid@') === FALSE) || in_array('submit_id', $checked_ids) ) { echo 'style="display:none;"'; } ?>>
				<?php echo $f->group_id; ?>
				</th>
			  <?php } ?>
			  <?php
              if ( $submit_date ) {
                if ( strpos($lists['hide_label_list'], '@submitdate@') === FALSE ) {
                  echo '<td align="center" class="submitdate_fc">' . $date . '</td>';
                }
                else {
                  echo '<td align="center" class="submitdate_fc" style="display:none;">' . $date . '</td>';
                }
              }
              if ( $submitter_ip ) {
                if ( strpos($lists['hide_label_list'], '@submitterip@') === FALSE ) {
                  echo '<td align="center" class="submitterip_fc">' . $ip . '</td>';
                }
                else {
                  echo '<td align="center" class="submitterip_fc" style="display:none;">' . $ip . '</td>';
                }
              }
              if ( $username ) {
                if ( strpos($lists['hide_label_list'], '@submitterusername@') === FALSE ) {
                  echo '<td align="center" class="submitterusername_fc" style="display:table-cell;">' . $user_name . '</td>';
                }
                else {
                  echo '<td align="center" class="submitterusername_fc" style="display:none;">' . $user_name . '</td>';
                }
              }
              if ( $useremail ) {
                if ( strpos($lists['hide_label_list'], '@submitteremail@') === FALSE ) {
                  echo '<td align="center" class="submitteremail_fc" style="display:table-cell;">' . $user_email . '</td>';
                }
                else {
                  echo '<td align="center" class="submitteremail_fc" style="display:none;">' . $user_email . '</td>';
                }
              }
              $ttt = count($temp);
              for ( $h = 0; $h < $m; $h++ ) {
                if ( $form_fields && !in_array($labels_id[$h], $checked_ids) ) {
                  $not_label = TRUE;
                  for ( $g = 0; $g < $ttt; $g++ ) {
                    $t = $temp[$g];
                    if ( strpos($lists['hide_label_list'], '@' . $labels_id[$h] . '@') === FALSE ) {
                      $styleStr = '';
                    }
                    else {
                      $styleStr = 'style="display:none;"';
                    }
                    if ( $t->element_label == $labels_id[$h] ) {
                      if ( strpos($t->element_value, "***map***") ) {
                        $map_params = explode('***map***', $t->element_value);
                        $longit = $map_params[0];
                        $latit = $map_params[1];
                        echo '<td align="center" class="' . $labels_id[$h] . '_fc" ' . $styleStr . '><a class="thickbox-preview" href="' . add_query_arg(array(
                                                                                                                                                           'action' => 'frontend_show_map',
                                                                                                                                                           'page' => 'form_submissions',
                                                                                                                                                           'long' => $longit,
                                                                                                                                                           'lat' => $latit,
                                                                                                                                                           'nonce'=>$fm_nonce,
                                                                                                                                                           'width' => '620',
                                                                                                                                                           'height' => '550',
                                                                                                                                                           'TB_iframe' => '1'
                                                                                                                                                         ), admin_url('admin-ajax.php')) . '" title="' . __('Show on Map', WDFMInstance(self::PLUGIN)->prefix) . '">' . __('Show on Map', WDFMInstance(self::PLUGIN)->prefix) . '</a></td>';
                      }
                      else {
                        if ( strpos($t->element_value, "*@@url@@*") ) {
                          echo '<td align="center" class="' . $labels_id[$h] . '_fc" ' . $styleStr . '>';
                          $new_files = explode("*@@url@@*", $t->element_value);
                          foreach ( $new_files as $new_file ) {
                            if ( $new_file ) {
                              $new_filename = explode('/', $new_file);
                              $new_filename = $new_filename[count($new_filename) - 1];
                              if ( strpos(strtolower($new_filename), 'jpg') !== FALSE or strpos(strtolower($new_filename), 'png') !== FALSE or strpos(strtolower($new_filename), 'gif') !== FALSE or strpos(strtolower($new_filename), 'jpeg') !== FALSE ) {
                                echo '<a href="' . $new_file . '" class="modal">' . $new_filename . "</a></br>";
                              }
                              else {
                                echo '<a target="_blank" href="' . $new_file . '">' . $new_filename . "</a></br>";
                              }
                            }
                          }
                          echo "</td>";
                        }
                        else {
                          if ( strpos($t->element_value, "@@@") > -1 || $t->element_value == "@@@" || $t->element_value == "@@@@@@@@@" || $t->element_value == "::" || $t->element_value == ":" || $t->element_value == "--" ) {
                            echo '<td align="center" class="' . $labels_id[$h] . '_fc" ' . $styleStr . '><pre style="font-family:inherit">' . str_replace(array(
                                                                                                                                                            "@@@",
                                                                                                                                                            ":",
                                                                                                                                                            "-"
                                                                                                                                                          ), " ", $t->element_value) . '</pre></td>';
                          }
                          else {
                            if ( strpos($t->element_value, "***grading***") ) {
                              $new_filename = str_replace("***grading***", '', $t->element_value);
                              $grading = explode(":", $new_filename);
                              $items_count = sizeof($grading) - 1;
                              $items = "";
                              $total = "";
                              for ( $k = 0; $k < $items_count / 2; $k++ ) {
                                $items .= $grading[$items_count / 2 + $k] . ": " . $grading[$k] . "</br>";
                                $total += $grading[$k];
                              }
                              $items .= __('Total', WDFMInstance(self::PLUGIN)->prefix) . ": " . $total;
                              echo '<td align="center" class="' . $labels_id[$h] . '_fc" ' . $styleStr . '><pre style="font-family:inherit">' . $items . '</pre></td>';
                            }
                            else {
                              if ( strpos($t->element_value, "***matrix***") ) {
                                echo '<td align="center" class="' . $labels_id[$h] . '_fc" ' . $styleStr . '><a class="thickbox-preview" href="' . add_query_arg(array(
                                                                                                                                                                   'action' => 'frontend_show_matrix',
                                                                                                                                                                   'page' => 'form_submissions',
                                                                                                                                                                   'matrix_params' => $t->element_value,
                                                                                                                                                                   'nonce'=>$fm_nonce,
                                                                                                                                                                   'width' => '620',
                                                                                                                                                                   'height' => '550',
                                                                                                                                                                   'TB_iframe' => '1'
                                                                                                                                                                 ), admin_url('admin-ajax.php')) . '" title="' . __('Show Matrix', WDFMInstance(self::PLUGIN)->prefix) . '">' . __('Show Matrix', WDFMInstance(self::PLUGIN)->prefix) . '</a></td>';
                              }
                              else {
                                if ( strpos($t->element_value, "***quantity***") ) {
                                  $t->element_value = str_replace("***quantity***", " ", $t->element_value);
                                }
                                if ( strpos($t->element_value, "***property***") ) {
                                  $t->element_value = str_replace("***property***", " ", $t->element_value);
                                }
                                echo '<td align="center" class="' . $labels_id[$h] . '_fc" ' . $styleStr . '><pre style="font-family:inherit; white-space: pre;">' . str_replace("***br***", '<br>', $t->element_value) . '</pre></td>';
                              }
                            }
                          }
                        }
                      }
                      $not_label = FALSE;
                    }
                  }
                  if ( $not_label ) {
                    echo '<td align="center" class="' . $labels_id[$h] . '_fc" ' . $styleStr . '></td>';
                  }
                }
              }
              if ( $form_fields && $is_paypal && !in_array('payment_info', $checked_ids) ) {
                if ( strpos($lists['hide_label_list'], '@payment_info@') === FALSE ) {
                  $styleStr = '';
                }
                else {
                  $styleStr = 'style="display:none;"';
                }
                echo '<td align="center" class="payment_info_fc" ' . $styleStr . '>		
                        <a class="thickbox-preview" href="' . add_query_arg(array(
                                                                       'action' => 'frontend_paypal_info',
                                                                       'page' => 'form_submissions',
                                                                       'id' => $i,
                                                                        'nonce'=>$fm_nonce,
                                                                       'width' => '600',
                                                                       'height' => '500',
                                                                       'TB_iframe' => '1'
                                                                     ), admin_url('admin-ajax.php')) . '">
                           <img src="' . WDFMInstance(self::PLUGIN)->plugin_url . '/images/info.png" />
                        </a>
                        </td>';
              }
              ?>
            </tr>
            <?php
            $k = 1 - $k;
          }
          ?>
        </table>
      </div>
      <?php
      $is_stats = FALSE;
      if ( $stats ) {
        foreach ( $sorted_labels_type as $key => $label_type ) {
          if ( $label_type == "type_checkbox" || $label_type == "type_radio" || $label_type == "type_own_select" || $label_type == "type_country" || $label_type == "type_paypal_select" || $label_type == "type_paypal_radio" || $label_type == "type_paypal_checkbox" || $label_type == "type_paypal_shipping" ) {
            $is_stats = TRUE;
            break;
          }
        }
        if ( $is_stats ) {
          ?>
          <br /><br />
          <h1 style="border-bottom: 1px solid; color:#000 !important;"><?php _e('Statistics', WDFMInstance(self::PLUGIN)->prefix); ?></h1>
          <table class="stats">
            <tr valign="top">
              <td class="key" style="vertical-align: middle;">
                <label> <?php _e('Select a Field', WDFMInstance(self::PLUGIN)->prefix); ?>: </label>
              </td>
              <td>
                <select id="stat_id">
                  <option value=""><?php _e('Select a Field', WDFMInstance(self::PLUGIN)->prefix); ?></option>
                  <?php
                  $stats_fields = explode(',', $stats_fields);
                  $stats_fields = array_slice($stats_fields, 0, count($stats_fields) - 1);
                  foreach ( $sorted_labels_type as $key => $label_type ) {
                    if ( ($label_type == "type_checkbox" || $label_type == "type_radio" || $label_type == "type_own_select" || $label_type == "type_country" || $label_type == "type_paypal_select" || $label_type == "type_paypal_radio" || $label_type == "type_paypal_checkbox" || $label_type == "type_paypal_shipping") && !in_array($labels_id[$key], $stats_fields) ) {
                      echo '<option value="' . $labels_id[$key] . '">' . $label_titles_copy[$key] . '</option>';
                    }
                  }
                  ?>
                </select>
              </td>
            </tr>
            <tr valign="middle">
              <td class="key" style="vertical-align: middle;">
                <label> <?php _e('Select a Date', WDFMInstance(self::PLUGIN)->prefix); ?>: </label>
              </td>
              <td>
                <?php _e('From', WDFMInstance(self::PLUGIN)->prefix); ?>:<input class="inputbox wd-datepicker" type="text" name="startstats" id="startstats" size="10" maxlength="10" />
                <?php _e('To', WDFMInstance(self::PLUGIN)->prefix); ?>:
                <input class="inputbox wd-datepicker" type="text" name="endstats" id="endstats" size="10" maxlength="10" />
              </td>
            </tr>
            <tr valign="top">
              <td class="key" style="vertical-align: middle;" colspan="2">
                <input type="button" onclick="show_stats()" style="vertical-align:middle; cursor:pointer" value="<?php _e('Show', WDFMInstance(self::PLUGIN)->prefix); ?>" />
              </td>
            </tr>
          </table>
          <div id="div_stats"></div>
          <?php
        }
      }
      ?>
    </form>
    <script>
      jQuery(window).on('load', function () {
        fm_popup();
        if (typeof jQuery().fancybox !== 'undefined' && jQuery.isFunction(jQuery().fancybox)) {
          jQuery(".fm_fancybox").fancybox({
            'maxWidth ': 600,
            'maxHeight': 500
          });
        }
      });
      function show_stats() {
        jQuery('#div_stats').html('<div id="saving"><div id="saving_text">Loading</div><div id="fadingBarsG"><div id="fadingBarsG_1" class="fadingBarsG"></div><div id="fadingBarsG_2" class="fadingBarsG"></div><div id="fadingBarsG_3" class="fadingBarsG"></div><div id="fadingBarsG_4" class="fadingBarsG"></div><div id="fadingBarsG_5" class="fadingBarsG"></div><div id="fadingBarsG_6" class="fadingBarsG"></div><div id="fadingBarsG_7" class="fadingBarsG"></div><div id="fadingBarsG_8" class="fadingBarsG"></div></div></div>');
        if (jQuery('#stat_id').val() != "") {
          jQuery('#div_stats').load('<?php echo add_query_arg(array(
                                                                'action' => 'get_frontend_stats',
                                                                'page' => 'form_submissions',
                                                                'nonce'=>$fm_nonce,
                                                              ), admin_url('admin-ajax.php')); ?>', {
            'form_id': '<?php echo $form_id; ?>',
            'stat_id': jQuery('#stat_id').val(),
            'startdate': jQuery('#startstats').val(),
            'enddate': jQuery('#endstats').val()
          });
        }
        else {
          jQuery('#div_stats').html("<?php echo __('Please select the field!', WDFMInstance(self::PLUGIN)->prefix); ?>")
        }
      }
      function fm_popup(id) {
        if (typeof id === 'undefined') {
          var id = '';
        }
        var thickDims, tbWidth, tbHeight;
        thickDims = function () {
          var tbWindow = jQuery('#TB_window'), H = jQuery(window).height(), W = jQuery(window).width(), w, h;
          w = (tbWidth && tbWidth < W - 90) ? tbWidth : W - 40;
          h = (tbHeight && tbHeight < H - 60) ? tbHeight : H - 40;
          if (tbWindow.length) {
            tbWindow.width(w).height(h);
            jQuery('#TB_iframeContent').width(w).height(h - 27);
            tbWindow.css({'margin-left': '-' + parseInt((w / 2), 10) + 'px'});
            if (typeof document.body.style.maxWidth != 'undefined') {
              tbWindow.css({'top': (H - h) / 2, 'margin-top': '0'});
            }
          }
        };
        thickDims();
        jQuery(window).resize(function () {
          thickDims()
        });
        jQuery('a.thickbox-preview' + id).click(function () {
          tb_click.call(this);
          var alink = jQuery(this).parents('.available-theme').find('.activatelink'), link = '',
            href = jQuery(this).attr('href'), url, text;
          if (tbWidth = href.match(/&width=[0-9]+/)) {
            tbWidth = parseInt(tbWidth[0].replace(/[^0-9]+/g, ''), 10);
          }
          else {
            tbWidth = jQuery(window).width() - 120;
          }
          if (tbHeight = href.match(/&height=[0-9]+/)) {
            tbHeight = parseInt(tbHeight[0].replace(/[^0-9]+/g, ''), 10);
          }
          else {
            tbHeight = jQuery(window).height() - 120;
          }
          if (alink.length) {
            url = alink.attr('href') || '';
            text = alink.attr('title') || '';
            link = '&nbsp; <a href="' + url + '" target="_top" class="tb-theme-preview-link">' + text + '</a>';
          }
          else {
            text = jQuery(this).attr('title') || '';
            link = '&nbsp; <span class="tb-theme-preview-link">' + text + '</span>';
          }
          jQuery('#TB_title').css({'background-color': '#222', 'color': '#dfdfdf'});
          jQuery('#TB_closeAjaxWindow').css({'float': 'right'});
          jQuery('#TB_ajaxWindowTitle').css({'float': 'left'}).html(link);
          jQuery('#TB_iframeContent').width('100%');
          thickDims();
          return false;
        });
        jQuery('.theme-detail').click(function () {
          jQuery(this).siblings('.themedetaildiv').toggle();
          return false;
        });
      }
      <?php
      if ($ka_fielderov_search) {
      ?>
        document.getElementById('fm-fields-filter').style.display = '';
        document.getElementById('filter_img').src = '<?php echo WDFMInstance(self::PLUGIN)->plugin_url . '/images/filter_hide.png'; ?>';
      <?php
      }
      ?>
    </script>
    <?php
  }

  /**
   * Show statistics.
   *
   * @param array $choices
   */
  public function show_stats( $choices = array() ) {
    $colors = array( '#D6DEE9', '#F5DFCA' );
    $choices_colors = array( '#6095CB', '#FF9630' );
    $choices_labels = array();
    $choices_count = array();
    $all = count($choices);
    $unanswered = 0;
    foreach ( $choices as $key => $choice ) {
      if ( $choice == '' ) {
        $unanswered++;
      }
      else {
        if ( !in_array($choice, $choices_labels) ) {
          array_push($choices_labels, $choice);
          array_push($choices_count, 0);
        }
        $choices_count[array_search($choice, $choices_labels)]++;
      }
    }
    array_multisort($choices_count, SORT_DESC, $choices_labels);
    ?>
    <table class="adminlist">
      <thead>
        <tr>
          <th width="20%"><?php _e('Choices', WDFMInstance(self::PLUGIN)->prefix); ?></th>
          <th><?php _e('Percentage', WDFMInstance(self::PLUGIN)->prefix); ?></th>
          <th width="10%"><?php _e('Count', WDFMInstance(self::PLUGIN)->prefix); ?></th>
        </tr>
      </thead>
      <?php
      $k = 0;
      foreach ( $choices_labels as $key => $choices_label ) {
        ?>
        <tr>
          <td class="label<?php echo $k; ?>">
            <?php echo str_replace("***br***", '<br>', $choices_label) ?>
          </td>
          <td>
            <div class="bordered" style="width:<?php echo ($choices_count[$key] / ($all - $unanswered)) * 100; ?>%; height:28px; background-color:<?php echo $colors[$key % 2]; ?>; display:inline-block;"></div>
            <div <?php echo($choices_count[$key] / ($all - $unanswered) != 1 ? 'class="bordered' . $k . '"' : "") ?> style="width:<?php echo 100 - ($choices_count[$key] / ($all - $unanswered)) * 100; ?>%; height:28px; background-color:#F2F0F1; display:inline-block;"></div>
          </td>
          <td>
            <div>
              <div style="width: 0; height: 0; border-top: 14px solid transparent;border-bottom: 14px solid transparent; border-right:14px solid <?php echo $choices_colors[$key % 2]; ?>; float:left;"></div>
              <div style="background-color:<?php echo $choices_colors[$key % 2]; ?>; height:28px; width:28px; text-align: center; margin-left:14px; color: #fff;"><?php echo $choices_count[$key] ?></div>
            </div>
          </td>
        </tr>
        <tr>
          <td colspan="3">
          </td>
        </tr>
        <?php
        $k = 1 - $k;
      }
      if ( $unanswered ) {
        ?>
        <tr>
          <td colspan="2" style="text-align: right; color: #000;">
            <?php _e('Unanswered', WDFMInstance(self::PLUGIN)->prefix); ?>
          </td>
          <td>
            <strong style="margin-left: 10px;"><?php echo $unanswered; ?></strong>
          </td>
        </tr>
        <?php
      }
      ?>
      <tr>
        <td colspan="2" style="text-align: right; color: #000;">
          <strong><?php _e('Total', WDFMInstance(self::PLUGIN)->prefix); ?></strong>
        </td>
        <td>
          <strong style="margin-left: 10px;"><?php echo $all; ?></strong>
        </td>
      </tr>
    </table>
    <?php

    die();
  }

  /**
   * Show map.
   *
   * @param string $long
   * @param string $lat
   */
  public function show_map( $long = '', $lat = '' ) {
    wp_print_scripts('google-maps');
    wp_print_scripts(WDFMInstance(self::PLUGIN)->handle_prefix . '-gmap_form_back');
    ?>
    <table style="margin: 0px; padding: 0px;">
      <tr>
        <td>
          <b><?php _e('Address', WDFMInstance(self::PLUGIN)->prefix); ?>:</b>
        </td>
        <td>
          <input type="text" id="addrval0" style="border: 0px; background: none;" size="100" readonly />
        </td>
      </tr>
      <tr>
        <td>
          <b><?php _e('Longitude', WDFMInstance(self::PLUGIN)->prefix); ?>:</b>
        </td>
        <td>
          <input type="text" id="longval0" style="border: 0px; background: none;" size="100" readonly />
        </td>
      </tr>
      <tr>
        <td>
          <b><?php _e('Latitude', WDFMInstance(self::PLUGIN)->prefix); ?>:</b>
        </td>
        <td>
          <input type="text" id="latval0" style="border: 0px; background: none;" size="100" readonly />
        </td>
      </tr>
    </table>
    <div id="0_elementform_id_temp" long="<?php echo $long; ?>" center_x="<?php echo $long; ?>" center_y="<?php echo $lat; ?>" lat="<?php echo $lat; ?>" zoom="8" info="" style="width: 600px; height: 500px;"></div>
    <script>
      if_gmap_init("0");
      add_marker_on_map(0, 0, "<?php echo $long; ?>", "<?php echo $lat; ?>", '');
    </script>
    <?php

    die();
  }

  /**
   * Show matrix.
   *
   * @param array $matrix_params
   */
  public function show_matrix( $matrix_params = array() ) {
    $new_filename = explode('***', $matrix_params);
    $mat_params = array_slice($new_filename, 0, count($new_filename) - 1);
    $mat_rows = $mat_params[0];
    $mat_columns = $mat_params[$mat_rows + 1];
    ?>
    <table>
      <tr>
        <td></td>
        <?php
        for ( $k = 1; $k <= $mat_columns; $k++ ) {
          ?>
          <td style="background-color: #BBBBBB; padding: 5px;"><?php echo $mat_params[$mat_rows + 1 + $k]; ?></td>
          <?php
        }
        ?>
      </tr>
      <?php
      $arr = array();
      $var_checkbox = 1;
      for ( $k = 1; $k <= $mat_rows; $k++ ) {
        ?>
        <tr>
          <td style="background-color: #BBBBBB; padding: 5px; "><?php echo $mat_params[$k]; ?></td>
          <?php
          if ( $mat_params[$mat_rows + $mat_columns + 2] == "radio" ) {
            if ( $mat_params[$mat_rows + $mat_columns + 2 + $k] == 0 ) {
              $arr[1] = "";
            }
            else {
              $arr = explode("_", $mat_params[$mat_rows + $mat_columns + 2 + $k]);
            }
            for ( $l = 1; $l <= $mat_columns; $l++ ) {
              $checked = $arr[1] == $l ? 'checked="checked"' : '';
              ?>
              <td style="text-align: center;">
                <input type="radio" <?php echo $checked; ?> disabled="disabled" />
              </td>
              <?php
            }
          }
          else {
            if ( $mat_params[$mat_rows + $mat_columns + 2] == "checkbox" ) {
              for ( $l = 1; $l <= $mat_columns; $l++ ) {
                $checked = ($mat_params[$mat_rows + $mat_columns + 2 + $var_checkbox] == "1") ? 'checked="checked"' : '';
                ?>
                <td style="text-align: center;">
                  <input type="checkbox" <?php echo $checked; ?> disabled="disabled" />
                </td>
                <?php
                $var_checkbox++;
              }
            }
            else {
              if ( $mat_params[$mat_rows + $mat_columns + 2] == "text" ) {
                for ( $l = 1; $l <= $mat_columns; $l++ ) {
                  $checked = $mat_params[$mat_rows + $mat_columns + 2 + $var_checkbox];
                  ?>
                  <td style="text-align: center;">
                    <input type="text" value="<?php echo $checked; ?>" disabled="disabled" />
                  </td>
                  <?php
                  $var_checkbox++;
                }
              }
              else {
                for ( $l = 1; $l <= $mat_columns; $l++ ) {
                  $checked = $mat_params[$mat_rows + $mat_columns + 2 + $var_checkbox];
                  ?>
                  <td style="text-align: center;"><?php echo $checked; ?></td>
                  <?php
                  $var_checkbox++;
                }
              }
            }
          }
          ?>
        </tr>
        <?php
      }
      ?>
    </table>
    <?php
    die();
  }

  /**
   * Display PayPal info.
   * 
   * @param array $submission
   */
  public function paypal_info( $submission = array() ) {
    if ( !isset($submission->ipn) ) {
      ?>
      <div style="width: 100%; text-align: center; height: 100%; vertical-align: middle;">
        <h1 style="top: 44%; position: absolute; left: 38%; color: #000;">
          <?php _e('No information yet', WDFMInstance(self::PLUGIN)->prefix); ?>
        </h1>
      </div>
      <?php
    }
    else {
      ?>
      <h2><?php _e('Payment Info', WDFMInstance(self::PLUGIN)->prefix); ?></h2>
      <table class="admintable">
        <tr>
          <td class="key"><?php _e('Currency', WDFMInstance(self::PLUGIN)->prefix); ?></td>
          <td><?php echo $submission->currency; ?></td>
        </tr>
        <tr>
          <td class="key"><?php _e('Last modified', WDFMInstance(self::PLUGIN)->prefix); ?></td>
          <td><?php echo $submission->ord_last_modified; ?></td>
        </tr>
        <tr>
          <td class="key"><?php _e('Status', WDFMInstance(self::PLUGIN)->prefix); ?></td>
          <td><?php echo $submission->status; ?></td>
        </tr>
        <tr>
          <td class="key"><?php _e('Full name', WDFMInstance(self::PLUGIN)->prefix); ?></td>
          <td><?php echo $submission->full_name; ?></td>
        </tr>
        <tr>
          <td class="key"><?php _e('Email', WDFMInstance(self::PLUGIN)->prefix); ?></td>
          <td><?php echo $submission->email; ?></td>
        </tr>
        <tr>
          <td class="key"><?php _e('Phone', WDFMInstance(self::PLUGIN)->prefix); ?></td>
          <td><?php echo $submission->phone; ?></td>
        </tr>
        <tr>
          <td class="key"><?php _e('Mobile phone', WDFMInstance(self::PLUGIN)->prefix); ?></td>
          <td><?php echo $submission->mobile_phone; ?></td>
        </tr>
        <tr>
          <td class="key"><?php _e('Fax', WDFMInstance(self::PLUGIN)->prefix); ?></td>
          <td><?php echo $submission->fax; ?></td>
        </tr>
        <tr>
          <td class="key"><?php _e('Address', WDFMInstance(self::PLUGIN)->prefix); ?></td>
          <td><?php echo $submission->address; ?></td>
        </tr>
        <tr>
          <td class="key"><?php _e('Payment info', WDFMInstance(self::PLUGIN)->prefix); ?></td>
          <td><?php echo $submission->paypal_info; ?></td>
        </tr>
        <tr>
          <td class="key"><?php _e('IPN', WDFMInstance(self::PLUGIN)->prefix); ?></td>
          <td><?php echo $submission->ipn; ?></td>
        </tr>
        <tr>
          <td class="key"><?php _e('Tax', WDFMInstance(self::PLUGIN)->prefix); ?></td>
          <td><?php echo $submission->tax; ?></td>
        </tr>
        <tr>
          <td class="key"><?php _e('Shipping', WDFMInstance(self::PLUGIN)->prefix); ?></td>
          <td><?php echo $submission->shipping; ?></td>
        </tr>
        <tr>
          <td class="key"><?php _e('Total', WDFMInstance(self::PLUGIN)->prefix); ?></td>
          <td><b><?php echo $submission->total; ?></b></td>
        </tr>
      </table>
      <?php
    }

    die();
  }

  /**
   * Generate CSV.
   *
   * @param array $form
   */
  public function generate_csv( $form = array() ) {
    $title = $form['title'];
    $ptn = "/[^a-zA-Z0-9_]/";
    $rpltxt = "";
    $title = preg_replace($ptn, $rpltxt, $title);
    $data = $form['data'];
    $filename = $title . "_" . date('Ymd') . ".csv";
    header('Content-Encoding: Windows-1252');
    header('Content-type: text/csv; charset=Windows-1252');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    $flag = FALSE;
    $text = '';
    foreach ( $data as $row ) {
      if ( !$flag ) {
        $text .= '"' . implode('","', str_replace('PAYPAL_', '', array_keys($row)));
        $text .= "\"\r\n";
        $flag = TRUE;
      }
      array_walk($row, array( 'WDW_FM_Library', 'cleanData' ));
      $text .= '"' . implode('","', array_values($row)) . "\"\r\n";
    }
    echo $text;
    die();
  }

  /**
   * Generate XML.
   *
   * @param array $form
   */
  public function generate_xml( $form = array() ) {
    $title = $form['title'];
    $form_id = $form['id'];
    $ptn = "/[^a-zA-Z0-9_]/";
    $rpltxt = "";
    $data = $form['data'];

    define('PHP_TAB', "\t");
    $filename = preg_replace($ptn, $rpltxt, $title) . "_" . date('Ymd') . ".xml";
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Content-Type:text/xml,  charset=utf-8");
    $text = '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL;
    $text .= '<form id="' . $form_id . '" title="' . $title . '">' . PHP_EOL;
    foreach ( $data as $key1 => $value1 ) {
      $text .= PHP_TAB . '<submission id="' . $key1 . '">' . PHP_EOL;
      foreach ( $value1 as $key => $value ) {
        $text .= PHP_TAB . PHP_TAB . '<field title="' . str_replace('PAYPAL_', '', $key) . '">' . PHP_EOL;
        $text .= PHP_TAB . PHP_TAB . PHP_TAB . '<![CDATA[' . $value . ']]>' . PHP_EOL;
        $text .= PHP_TAB . PHP_TAB . '</field>' . PHP_EOL;
      }
      $text .= PHP_TAB . '</submission>' . PHP_EOL;
    }
    $text .= '</form>';
    echo $text;

    die();
  }
}
