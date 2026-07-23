<?php

/**
 * Class WD_FM_REG_controller
 */
class WD_FM_REG_controller {

  private $model;
  private $view;

  function __construct() {
    require_once 'model.php';
    require_once 'view.php';
    $this->model = new WD_FM_REG_model();
    $this->view  = new WD_FM_REG_view();
  }

  /**
   * Frontend.
   *
   * @param array $params
   */
  public function frontend( $params = array() ) {
    // create user
    $fvals = $params[ 'fvals' ];
    $form_id = $params[ 'form_id' ];
    $custom_fields = $params[ 'custom_fields' ];
    $reg = $this->model->get_data( $form_id );
    if ( $reg->use_reg ) {
      $row = array(
        "username" => '',
        "email" => '',
        "first_name" => '',
        "last_name" => '',
        "role" => '',
        "info" => '',
        "website" => '',
        "password" => ''
      );
      foreach ( $row as $key => $tuft ) {
        $row[ $key ] = $reg->$key;
        foreach ( $fvals as $fval_key => $fval ) {
          $row[ $key ] = str_replace( $fval_key, $fval, $row[ $key ] );
        }
        foreach ( $custom_fields as $custom_key => $custom_val ) {
          if ( (strpos( $row[ $key ], '%' . $custom_key . '%' ) > -1) || (strpos( $row[ $key ], '{' . $custom_key . '}' ) > -1) ) {
            $key_replace = array( '%' . $custom_key . '%', '{' . $custom_key . '}' );
            $row[ $key ] = str_replace( $key_replace, $custom_val, $row[ $key ] );
          }
        }
      }
      if ( $row[ "role" ] == "" ) {
        $row[ "role" ] = "**conds**";
      }
      $r = explode( '**conds**', $row[ "role" ] );
      $row[ "role" ] = $r[ 0 ];
      $r_params = $r[ 1 ];

      if ( strpos( $r_params, "**reg_conds**" ) !== false ) {
        $reg_arr = explode( '**reg_conds**', $r_params );
        $reg_arr = array_slice( $reg_arr, 0, count( $reg_arr ) - 1 );
        foreach ( $reg_arr as $kk => $t ) {
          $t_arr = explode( '****', $t );

          if ( $fvals[ '{' . $t_arr[ 0 ] . '}' ] == $t_arr[ 1 ] ) {
            $row[ "role" ] = $t_arr[ 2 ];
            break;
          }
        }
      }
      if ( username_exists( $row[ "username" ] ) == null ) {
        $show_pass = 0;
        if ( !$row[ "password" ] ) {
          $show_pass = 1;
          $row[ "password" ] = wp_generate_password( 12, false );
        }
        $userdata = array(
          'user_login' => $row[ "username" ],
          'user_pass' => $row[ "password" ],
          'user_url' => $row[ "website" ],
          'user_nicename' => $row[ "username" ],
          'user_email' => $row[ "email" ],
          'first_name' => $row[ "first_name" ],
          'last_name' => $row[ "last_name" ],
          'description' => $row[ "info" ],
          'role' => $row[ "role" ]
        );
        $user_id = wp_insert_user( $userdata );
        if (!is_wp_error($user_id)) {
          if ( $show_pass ) {
            $this->msg = __( 'Your password is', WDFM()->prefix ) . ' <b>' . $row[ "password" ] . '</b>';
            $this->error = false;
            add_filter( 'fm_output_error_from_add_ons', array( $this, 'set_error_message' ), 10, 3 );
          }
          $another_params_array = json_decode( stripslashes( trim( htmlspecialchars_decode( $reg->another_params ), '"' ) ) );
          foreach ( $another_params_array as $key => $another_param ) {
            foreach ( $fvals as $fval_key => $fval ) {
              $another_param = str_replace( $fval_key, $fval, $another_param );
            }
            foreach ( $custom_fields as $custom_key => $custom_val ) {
              if ( (strpos( $another_param, '%' . $custom_key . '%' ) > -1) || (strpos( $another_param, '{' . $custom_key . '}' ) > -1) ) {
                $key_replace = array( '%' . $custom_key . '%', '{' . $custom_key . '}' );
                $another_param = str_replace( $key_replace, $custom_val, $another_param );
              }
            }
            if ( !add_user_meta( $user_id, $key, $another_param, true ) ) {
              add_user_meta( $user_id, $key, $another_param );
            }
          }
        }
        else {
          $this->msg = __( 'Unexpected error occurred.', WDFM()->prefix );
          $this->error = true;
          add_filter( 'fm_output_error_from_add_ons', array( $this, 'set_error_message' ), 10, 3 );
        }
      }
      else {
        $this->msg = __( 'This username is already registered. Please choose another one.', WDFM()->prefix );
        $this->error = true;
        add_filter( 'fm_output_error_from_add_ons', array( $this, 'set_error_message' ), 10, 3 );

      }
    }
  }

  public function set_error_message( $values = array() ) {
    if ( $this->error ) {
      $values['error']['WD_FM_REG'] = TRUE;
    }
    $values['message']['WD_FM_REG'] = $this->msg;
    return $values;
  }

  /**
   * Display.
   *
   * @param array $params
   */
  public function display( $params = array() ) {
    $form_id = $params['form_id'];
    $data = $this->model->get_data( $form_id );
    $params['data'] = $data;
    $label_all = $this->model->get_label_all( $form_id );
    $params['label_all'] = $label_all;
    $params['reg_params'] = array('username', 'password', 'email', 'first_name', 'last_name', 'role', 'info', 'website');
    $params['reg_params_name'] = array(__('Username (required)', WDFM()->prefix), __('Password', WDFM()->prefix), __('Email', WDFM()->prefix), __('First Name', WDFM()->prefix), __('Last Name', WDFM()->prefix), __('Role', WDFM()->prefix), __('Biographical Info', WDFM()->prefix), __('Website', WDFM()->prefix));

	$this->view->display($params);
  }

  /**
   * Save.
   *
   * @param int $id
   * @return bool
   */
  public function save( $id = 0 ) {
    $this->model->save($id);
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
   *
   * @param int $id
   * @return bool
   */
  public function delete( $id = 0) {
    $this->model->delete($id);
  }
}
