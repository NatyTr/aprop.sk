<?php

/**
 * Class WD_FM_MAILCHIMP_model
 */
class WD_FM_MAILCHIMP_model {

  /**
   * Delete.
   *
   * @param int $form_id
   */
  public function delete( $form_id = 0 ) {
    global $wpdb;
    $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'formmaker_mailchimp WHERE form_id="%d"', $form_id));
  }

  /**
   * Save.
   *
   * @param int $form_id
   */
  public function save( $form_id = 0 ) {
	global $wpdb;
    $id = $wpdb->get_var($wpdb->prepare('SELECT form_id FROM ' . $wpdb->prefix . 'formmaker_mailchimp WHERE form_id="%d"', $form_id));
	if ( WDW_FM_Library::get('addon_task', '') == 'connecting' ) {
      $data = array(
        'mailchimp_apikey' => WDW_FM_Library::get('mailchimp_apikey'),
        'mailchimp_listid' => '',
        'mailchimp_mergevars' => '',
      );
    }
    else {
		  $data = array(
        'form_id' => (int) $form_id,
        'use_mailchimp' => (int) WDW_FM_Library::get('use_mailchimp'),
        'mailchimp_apikey' => WDW_FM_Library::get('mailchimp_apikey'),
        'mailchimp_action' => (int) WDW_FM_Library::get('mailchimp_action'),
        'mailchimp_listid' => WDW_FM_Library::get('mailchimp_listid'),
        'mailchimp_email_type' => WDW_FM_Library::get('mailchimp_email_type'),
        'mailchimp_mergevars' => WDW_FM_Library::get('mailchimp_mergevars'),
      );
    }
	if ( $id ) {
      $wpdb->update($wpdb->prefix . 'formmaker_mailchimp', $data, array( 'form_id' => $form_id ));
    }
    else {
      $wpdb->insert($wpdb->prefix . 'formmaker_mailchimp', $data);
    }
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
    $data = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'formmaker_mailchimp WHERE form_id="%d"', $id), ARRAY_A);
    if (null != $data) {
      $data['form_id'] = $new_id;
      $wpdb->insert($wpdb->prefix . 'formmaker_mailchimp', $data);
    }
  }

	/**
   * Get data.
   *
   * @param  int 	$id
   * @return object $row
   */
	public function get_data( $id = 0 ) {
		global $wpdb;		
		$row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'formmaker_mailchimp WHERE form_id="%d"', $id));

		if ( empty($row) ) {
		  $row = new stdClass();
		  $row->form_id = '';
		  $row->use_mailchimp = 0;
		  $row->mailchimp_apikey = '';
		  $row->mailchimp_action = 1;
		  $row->mailchimp_listid = '';
		  $row->mailchimp_email_type = '';
		  $row->mailchimp_mergevars = '';
		}

		return $row;
	}

	/**
   * Get form field labels.
   *
   * @param  int 	$id
   * @return array $label_all
   */
	public function get_label_all( $id = 0 ) {
		global $wpdb;
		$label_all = $wpdb->get_var($wpdb->prepare('SELECT label_order_current FROM ' . $wpdb->prefix . 'formmaker WHERE id="%d"', $id));
		$label_all = explode('#****#', $label_all);
		$label_all = array_slice($label_all, 0, count($label_all) - 1);

		return $label_all;
	}
}
