<?php

/**
 * Class WD_FM_MAILCHIMP_controller
 */
class WD_FM_MAILCHIMP_controller {
  private $api_key;
  private $dc;
  private $api_url;
  private $data;
  private $form_id;
  private $list_id;

  private $model;
  private $view;
  private $address_fields_required;
  private $address_fields;

  function __construct($form_id) {
    require_once 'model.php';
    require_once 'view.php';

    $this->model = new WD_FM_MAILCHIMP_model();
    $this->view  = new WD_FM_MAILCHIMP_view();

    $this->data = $this->model->get_data($form_id);
    $this->form_id = $form_id;
    $this->list_id = $this->data->mailchimp_listid;
    $this->api_key = $this->data->mailchimp_apikey;
    $this->dc = substr($this->api_key, strpos($this->api_key, '-') + 1);
    $this->api_url = 'https://' . $this->dc . '.api.mailchimp.com/3.0/';
    $this->address_fields_required = array('city', 'state', 'zip');
    $this->address_fields = array(
								'address_line_2' => 'Address Line 2',
								'city' => 'City',
								'state' => 'State',
								'zip' => 'Zip',
								'country' => 'Country',
							  );
	}

  /**
   * Execute.
   *
   * @param string $task
   */
  function execute( $task = '' ) {
    if ( $task ) {
      if ( method_exists($this, $task) ) {
        $this->$task();
      }
    }
  }

  /**
   * Display.
   */
	public function display() {
		$mcapi_lists = array();
		$correspondence_fields = array();
		if ( !empty($this->api_key) ) {
			$mcapi_lists = $this->get_lists();
			if ( !empty($this->list_id) ) {
				$correspondence_fields = $this->get_merge_fields();
			}
		}

		$params = array();
		$params['data'] = $this->data;
		$params['mcapi_lists'] = $mcapi_lists;
		$params['correspondence_fields'] = $correspondence_fields;

		$params['label_all'] = $this->model->get_label_all($this->form_id);
		$params['filter_types'] = array("type_submit_reset", "type_map", "type_editor", "type_captcha", "type_recaptcha", "type_button", "type_paypal_total", "type_send_copy");

		$params['address_fields_required'] = $this->address_fields_required;
		$params['address_fields'] = $this->address_fields;

		$params['mailchimp_ajax_url'] = add_query_arg( array('action' => 'WD_FM_MAILCHIMP_init', 'form_id' => $this->form_id ), admin_url('admin-ajax.php') );

		$this->view->display($params);
	}

  /**
   * Save.
   */
  function save() {
    $this->model->save($this->form_id);
  }

  /**
   * Save.
   *
   * @param  int $id
   * @param  int $new_id
   * @return bool
   */
  public function duplicate( $id, $new_id ) {
    $this->model->duplicate($id, $new_id);
  }

  /**
   * Delete.
   */
  function delete() {
    $this->model->delete($this->form_id);
  }

  /**
   * Frontend.
   *
   * @param array $params
   *
   * @return bool
   */
  function frontend( $params = array() ) {
    if ( !empty($this->data) ) {
      if ( $this->data->use_mailchimp == 1
        and $this->data->mailchimp_apikey
        and $this->data->mailchimp_listid
        and $this->data->mailchimp_mergevars ) {
        $fields = array();
        $fields_arr = explode('***var***', $this->data->mailchimp_mergevars);

        foreach ( $fields_arr as $field ) {
          if ( $field ) {
            $tag = explode('***tag***', $field);
            if ( strpos($tag[1], '***type***') !== FALSE ) {
              $type = explode('***type***', $tag[1]);
              $fields[] = array(
                'value' => isset($params['fvals']['{' . $type[1] . '}']) ? $params['fvals']['{' . $type[1] . '}'] : '',
                'tag' => $tag[0],
                'type' => $type[0],
              );
            }
            else {
              // For old versions, where is no type saved in DB.
              $fields[] = array(
                'value' => isset($params['fvals']['{' . $tag[1] . '}']) ? $params['fvals']['{' . $tag[1] . '}'] : '',
                'tag' => $tag[0],
                'type' => strtolower($tag[0]),
              );
            }
          }
        }

        $res = $this->edit_member($fields);

        if ( $res['status'] == 'error' ) {
          $this->error_msg = $res['message'];
          add_filter( 'fm_output_error_from_add_ons', array( $this, 'set_error_message' ), 10, 3 );

          return FALSE;
        }
      }
    }
  }

  /**
   * Connecting.
   */
  function connecting() {
    $this->save();
    $this->api_key = WDW_FM_Library::get('mailchimp_apikey', '');
    $this->dc = substr($this->api_key, strpos($this->api_key, '-') + 1);
    $this->api_url = 'https://' . $this->dc . '.api.mailchimp.com/3.0/';

    echo json_encode($this->get_lists());

    exit();
  }

  /**
   * Get merge fields after ajax.
   */
  function merge_vars() {
    $this->api_key = WDW_FM_Library::get('api_key', '');
    $this->dc = substr($this->api_key, strpos($this->api_key, '-') + 1);
    $this->api_url = 'https://' . $this->dc . '.api.mailchimp.com/3.0/';
    $this->list_id = WDW_FM_Library::get('listid', '');
    echo json_encode($this->get_merge_fields());
    exit();
  }

  private function edit_member( $fields ) {
    $merge_fields = new stdClass();
    $email = '';
    $address_field_tag = '';
    $default_field = new stdClass();
    foreach ( $fields as $field ) {
      if ( $field['type'] == 'email' ) {
        $email = $field['value'];
      }
      elseif ( $field['type'] == 'address' && $field['value'] !== '' ) {
        if ( $field['tag'] == 'CITY' ) {
          $default_field->city = $field['value'];
        }
        elseif ( $field['tag'] == 'STATE' ) {
          $default_field->state = $field['value'];
        }
        elseif ( $field['tag'] == 'ZIP' ) {
          $default_field->zip = $field['value'];
        }
        elseif ( $field['tag'] == 'ADDRESS_LINE_2' ) {
          $default_field->addr2 = $field['value'];
        }
        elseif ( $field['tag'] == 'COUNTRY' ) {
          $default_field->country = $field['value'];
        }
        else {
          $address_field_tag = $field['tag'];
          $default_field->addr1 = $field['value'];
        }
      }
      elseif ( $field['value'] !== '' ) {
        $merge_fields->{$field['tag']} = $field['value'];
      }
    }
    if ( $address_field_tag ) {
      $merge_fields->$address_field_tag = $default_field;
    }

    $data = array(
      'email_address' => $email,
      'email_type' => $this->data->mailchimp_email_type,
      'status' => $this->data->mailchimp_action == 1 ? 'subscribed' : 'unsubscribed',
      'merge_fields' => $merge_fields,
    );

    $url = $this->api_url . 'lists/' . $this->list_id . '/members/' . md5(strtolower($email));

    $response = $this->mailchimp_curl_connect( $url, 'PUT', $this->api_key, $data);

    $status = 'success';
    $message = __('Success', WDFM()->prefix);
    if ( isset($response->status) && $response->status == 400 ) {
      $status = 'error';
      if ( isset($response->errors) ) {
        $message = "";
        foreach ( $response->errors as $error ) {
          if ( isset($error->message) ) {
            $message .= $error->message . "<br>";
          }
        }
        $message = trim($message, "<br>");
      }
      else {
        $message = $response->detail;
      }
    }

    return array('status' => $status, 'message' => $message);
  }

  /**
   * Get Mailchimp lists.
   *
   * @param bool $decode
   *
   * @return mixed
   */
  private function get_lists($decode = TRUE) {
    $url = $this->api_url . 'lists';

    $response = $this->mailchimp_curl_connect( $url, 'GET', $this->api_key, array('fields' => 'total_items'), $decode);

    if ( isset($response->status) && $response->status == 401 ) {
      return array('status' => 'error', 'message' => $response->detail);
    }

    if ( !isset($response->total_items) || !$response->total_items ) {
      return array();
    }

    $data = array(
      'fields' => 'lists',
      'count' => $response->total_items,
      'sort_field' => 'date_created',
      'sort_dir' => 'ASC',
    );

    $response = $this->mailchimp_curl_connect( $url, 'GET', $this->api_key, $data, $decode);

    return (isset($response->lists) ? $response->lists : array());
  }

  /**
   * Get merge fields.
   *
   * @param bool $decode
   *
   * @return mixed
   */
  function get_merge_fields($decode = TRUE) {
    $url = $this->api_url . 'lists/' . $this->list_id . '/merge-fields';

    $response = $this->mailchimp_curl_connect( $url, 'GET', $this->api_key, array(), $decode);

    if ( isset($response->merge_fields) ) {
      // Add default required Email field to merge fields list.
      $default_field = new stdClass();
      $default_field->tag = 'EMAIL';
      $default_field->type = 'email';
      $default_field->name = 'Email Address';
      array_unshift($response->merge_fields, $default_field);

      return $response->merge_fields;
    }
    else {
      return array();
    }
  }

  private function mailchimp_curl_connect( $url, $request_type, $api_key, $data = array(), $decode = TRUE ) {
    if ( $request_type == 'GET' ) {
      $url .= '?' . http_build_query($data);
    }

    $mch = curl_init();
    $headers = array(
      'Content-Type: application/json',
      'Authorization: Basic '.base64_encode( 'user:'. $api_key )
    );
    curl_setopt($mch, CURLOPT_URL, $url );
    curl_setopt($mch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($mch, CURLOPT_RETURNTRANSFER, true); // do not echo the result, write it into variable
    curl_setopt($mch, CURLOPT_CUSTOMREQUEST, $request_type); // according to MailChimp API: POST/GET/PATCH/PUT/DELETE
    curl_setopt($mch, CURLOPT_TIMEOUT, 10);
    curl_setopt($mch, CURLOPT_SSL_VERIFYPEER, false); // certificate verification for TLS/SSL connection

    if ( $request_type != 'GET' ) {
      curl_setopt($mch, CURLOPT_POST, true);
      curl_setopt($mch, CURLOPT_POSTFIELDS, json_encode($data) ); // send data in json
    }

    if ( $decode ) {
      return json_decode(curl_exec($mch));
    }
    else {
      return curl_exec($mch);
    }
  }

  /**
   * Set error message.
   *
   * @param array $values
   * @return array $values
   */
  public function set_error_message( $values = array() ) {
    $values['error']['WD_FM_MAILCHIMP'] = true;
    $values['message']['WD_FM_MAILCHIMP'] = $this->error_msg;

    return $values;
  }
}
