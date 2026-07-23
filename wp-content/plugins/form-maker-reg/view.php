<?php

/**
 * Class WD_FM_REG_view
 */
class WD_FM_REG_view {
  /**
   * @param array $params
   */
	static function display( $params = array() ) {
    $id = $params['form_id'];
    $reg = $params['data'];
    $reg_params = $params['reg_params'];
    $reg_params_name = $params['reg_params_name'];

	  $label_all = $params['label_all'];
    $filter_types = array("type_submit_reset", "type_map", "type_editor", "type_captcha", "type_recaptcha", "type_button", "type_paypal_total", "type_send_copy");
    $label_id = array();
    $label_order = array();
    $label_order_original = array();
    $label_type = array();

    $reg_select = '';
    foreach ($label_all as $key => $label_each) {
      $label_id_each = explode('#**id**#', $label_each);
      $label_order_each = explode('#**label**#', $label_id_each[1]);
      if (in_array($label_order_each[1], $filter_types)) {
        continue;
      }
      $reg_select .= '<option value="' . addslashes($label_id_each[0]) . '">' . addslashes($label_order_each[0]) . '</option>';
      array_push($label_id, $label_id_each[0]);
      array_push($label_order_original, $label_order_each[0]);
      $ptn = "/[^a-zA-Z0-9_]/";
      $rpltxt = "";
      $label_temp = preg_replace($ptn, $rpltxt, $label_order_each[0]);
      array_push($label_order, $label_temp);
      array_push($label_type, $label_order_each[1]);
    }
	$roles = '';
    ?>
    <fieldset id="WD_FM_REG_fieldset" class="adminform fm_fieldset_deactive">
      <script>
        var fm_reg_select = '<?php echo $reg_select; ?>';
        var fm_reg_roles = '<?php echo $roles ?>';
        var fm_another_params = '<?php echo stripslashes(trim($reg->another_params, '"')); ?>';
        var fm_admin_url_profile = '<?php echo admin_url('profile.php'); ?>';
      </script>
      <div class="wd-table">
        <div class="wd-table-col-100">
          <div class="wd-box-section">
            <div class="wd-box-content">
              <div class="wd-group">
                <label class="wd-label"><?php _e('Enable', WDFM()->prefix); ?></label>
                <input class="wd-label" type="radio" name="use_reg" id="use_regyes" value="1" onchange="onEnableChange('WD_FM_REG_fieldset', 'reg_fieldset_options', '1');" <?php echo ($reg->use_reg) ? "checked" : ""; ?> /><label class="wd-label-radio" for="use_regyes"><?php _e('Yes', WDFM()->prefix); ?></label>
                <input class="wd-label" type="radio" name="use_reg" id="use_regno" value="0" onchange="onEnableChange('WD_FM_REG_fieldset', 'reg_fieldset_options', '0');" <?php echo (!$reg->use_reg) ? "checked" : ""; ?> /><label class="wd-label-radio" for="use_regno"><?php _e('No', WDFM()->prefix); ?></label>
                <p class="description"><?php _e('Allow users to create accounts at your website with Form maker.', WDFM()->prefix); ?></p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="reg_fieldset_options" class="adminform">
        <div class="wd-table">
          <div class="wd-table-col-100">
            <div class="wd-box-section">
              <div class="wd-box-content">
                <?php
                foreach ($reg_params as $key => $reg_param) {
                  if ($reg_param != "role") {
                    ?>
                  <div class="wd-group wd-has-placeholder">
                    <label class="wd-label" for="<?php echo $reg_param; ?>"><?php echo $reg_params_name[$key]; ?></label>
                    <span class="dashicons dashicons-list-view" data-id="<?php echo $reg_param; ?>" title="<?php _e('Add placeholder', WDFM()->prefix); ?>"></span>
                    <input autocomplete="off" type="text" id="<?php echo $reg_param; ?>" name="<?php echo $reg_param; ?>" value="<?php echo $reg->$reg_param; ?>"<?php
                           if ( $reg_param == 'username' ) {
                             ?>class="fm-validate" data-type="required" data-callback="fm_register_validation_username" data-tab-id="WD_FM_REG" data-content-id="WD_FM_REG_fieldset"<?php
                           } elseif ( $reg_param == 'email' ) {
                             ?>class="fm-validate" data-type="email" data-callback="fm_validate_email" data-callback-parameter="" data-tab-id="WD_FM_REG" data-content-id="WD_FM_REG_fieldset"<?php
                           } ?> />
                  </div>
                    <?php
                  }
                  else {
                    if (!$reg->role) {
                      $reg->role = "**conds**";
                    }
                    $r = explode('**conds**', $reg->role);
                    $r_prim = $r[0];
                    $reg->role = $r[1];
                    $all_roles = get_editable_roles();
                    $roles = '<option value="">' . __('Select Role', WDFM()->prefix) . '</option>';
                    foreach ($all_roles as $key2 => $all_role) {
                      $roles .= '<option value="' . $key2 . '"' . ($key2 == $r_prim ? ' selected="selected"' : '') . '>' . $all_role['name'] . '</option>';
                    }
                    $reg_conds = "";
                    if (strpos($reg->role, "**reg_conds**") !== false) {
                      $reg_arr = explode('**reg_conds**', $reg->role);
                      $reg_arr = array_slice($reg_arr, 0, count($reg_arr) - 1);
                      foreach ($reg_arr as $kk => $t) {
                        $t_arr = explode('****', $t);
                        $reg_conds .= '<div class="conds">If <select class="cond_sel_field" id="cond' . $kk . '_1"><option value="">' .  __('Select field', WDFM()->prefix) . '</option>' . $reg_select . '</select>' .  __(' equals to ', WDFM()->prefix) . '<input type="text" class="cond_if" value="' . $t_arr[1] . '"/>' .  __(' then set role ', WDFM()->prefix) . '<select class="cond_role" id="cond' . $kk . '_2">' . $roles . '</select><span class="dashicons dashicons-trash" onclick="delete_cond(this)"></span></div>';
                        $reg_conds .= '<script>jQuery("#cond' . $kk . '_1").val("' . $t_arr[0] . '");jQuery("#cond' . $kk . '_2").val("' . $t_arr[2] . '");</script>';
                      }
                    }
                    ?>
                  <div class="wd-group">
                    <label class="wd-label"><?php echo $reg_params_name[$key]; ?></label>
                    <select id="<?php echo $reg_param; ?>"><?php echo $roles; ?></select> Or <a href="javascript:add_role_cond()"><?php _e('Add Condition', WDFM()->prefix); ?></a><?php echo $reg_conds; ?>
                    <input type="hidden" name="<?php echo $reg_param; ?>" />
                    <div id="role_params"></div>
                  </div>
                    <?php
                  }
                }
                ?>
                <input type="hidden" id="other_params" data-params = '<?php echo ($reg->another_params != '{}' && $reg->another_params != '') ? json_decode($reg->another_params) : ""; ?>'>
                <div class="wd-group" id="div_show_other_params">
                  <label for="show_other_params"><?php _e('Show other registration options', WDFM()->prefix); ?></label>
                  <input type="checkbox" id="show_other_params" name="show_other_params" <?php if ($reg->show_other_params == 1) { echo "checked='checked'";} ?> />
                  <input type="hidden" id="check_show_other_params" name="check_show_other_params" value="<?php echo $reg->show_other_params == 1 ? 1 : 0 ?>"/>
                </div>
                <div id="div_user" style="display:<?php echo $reg->show_other_params == 1 ? "block;" : "none;" ?>"></div>
                <input type="hidden" id="additional_data" name="additional_data" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </fieldset>
    <?php
  }
}
