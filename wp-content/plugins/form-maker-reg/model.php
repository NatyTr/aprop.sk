<?php

/**
 * Class WD_FM_REG_model
 */
class WD_FM_REG_model {
  /**
   * Save add-on options.
   *
   * @param $form_id
   */
	static function save( $form_id = 0 ) {
    global $wpdb;
    $use_reg = WDW_FM_Library::get('use_reg');
    $check_show_other_params = WDW_FM_Library::get('check_show_other_params');
    $username = WDW_FM_Library::get('username');
    $email = WDW_FM_Library::get('email');
    $first_name = WDW_FM_Library::get('first_name');
    $last_name = WDW_FM_Library::get('last_name');
    $info = WDW_FM_Library::get('info');
    $website = WDW_FM_Library::get('website');
    $role = WDW_FM_Library::get('role');
    $password = WDW_FM_Library::get('password');
    $additional_data = WDW_FM_Library::get('additional_data', '{}', false);
    $save = $wpdb->replace($wpdb->prefix . 'formmaker_reg', array(
      'form_id' => $form_id,
      'use_reg' => $use_reg,
      'show_other_params' => $check_show_other_params,
      'username' => $username,
      'email' => $email,
      'first_name' => $first_name,
      'last_name' => $last_name,
      'info' => $info,
      'website' => $website,
      'role' => $role,
      'password' => $password,
      'another_params' => json_encode($additional_data)
    ));
  }
  /**
   * Duplicate.
   *
   * @param  int $id
   * @param  int $new_id
   *
   * @return bool
   */
  public function duplicate( $id, $new_id ) {
    global $wpdb;
    $data = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'formmaker_reg WHERE form_id="%d"', $id), ARRAY_A);
    if (null != $data) {
      $data['form_id'] = $new_id;
      $wpdb->insert($wpdb->prefix . 'formmaker_reg', $data);
    }
  }

  /**
   * Get add-on options.
   *
   * @param $id
   * @return array|null|object|void
   */
	static function get_data( $id = 0 ) {
    global $wpdb;
    $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'formmaker_reg WHERE form_id="%d"', $id));
    if ($row) {
      return $row;
    }
    $row = (object)array(
      "form_id" => "",
      "use_reg" => 0,
      "show_other_params" => 0,
      "username" => "",
      "email" => "",
      "first_name" => "",
      "last_name" => "",
      "info" => "",
      "website" => "",
      "role" => "",
      "password" => "",
      "another_params" => "{}"
    );
    return $row;
  }

  /**
   * Get form labels.
   *
   * @param $id
   * @return array|null|string
   */
	static function get_label_all( $id = 0 ) {
    global $wpdb;
    $label_all = $wpdb->get_var($wpdb->prepare('SELECT label_order_current FROM ' . $wpdb->prefix . 'formmaker WHERE id="%d"', $id));
    $label_all = explode('#****#', $label_all);
    $label_all = array_slice($label_all, 0, count($label_all) - 1);
    return $label_all;
  }

  /*
	* Delete.
	*
	* @param  int $id
	*
	* @return bool
	*/
  public function delete( $id = 0 ) {
    global $wpdb;
    $delete = $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'formmaker_reg WHERE form_id="%d"', $id));
    return $delete;
  }
}
