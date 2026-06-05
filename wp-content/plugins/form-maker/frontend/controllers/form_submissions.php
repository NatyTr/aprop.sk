<?php

/**
 * Class FMControllerForm_submissions
 */
class FMControllerForm_submissions {
  /**
   * PLUGIN = 2 points to Contact Form Maker
   */
  const PLUGIN = 1;

  private $view;
  private $model;

  /**
   * FMControllerVerify_email constructor.
   */
  public function __construct() {
    require_once WDFMInstance(self::PLUGIN)->plugin_dir . "/frontend/models/form_submissions.php";
    $this->model = new FMModelForm_submissions();

    require_once WDFMInstance(self::PLUGIN)->plugin_dir . "/frontend/views/form_submissions.php";
    $this->view = new FMViewForm_submissions();
  }

  /**
   * Execute.
   *
   * @param array $params
   */
  public function execute( $params = array() ) {
    $task = WDW_FM_Library(self::PLUGIN)->get('action');
    if ( method_exists($this, $task) && $task != 'display' ) {
      return $this->$task();
    }
    else {
      return $this->display($params);
    }
  }

  /**
   * Display.
   *
   * @param $params
   */
  public function display( $params = array() ) {
    // Get submissions.
    $data = $this->model->showsubmissions($params);
    return $this->view->display($data);
  }

  /**
   * Show statistics.
   */
  public function get_frontend_stats() {
    // Get statistics.
    $statistics = $this->model->show_stats();
    $this->view->show_stats($statistics);
  }

  /**
   * Show map.
   */
  public function frontend_show_map() {
    $long = WDW_FM_Library(self::PLUGIN)->get('long', 0, 'floatval');
    $lat = WDW_FM_Library(self::PLUGIN)->get('lat', 0, 'floatval');

    $this->view->show_map($long, $lat);
  }

  /**
   * Show matrix.
   */
  public function frontend_show_matrix() {
    $matrix_params = WDW_FM_Library(self::PLUGIN)->get('matrix_params');

    $this->view->show_matrix($matrix_params);
  }

  /**
   * PayPal info.
   */
  public function frontend_paypal_info() {
    $submission_id = WDW_FM_Library(self::PLUGIN)->get('id', 0, 'intval');
    $submission = $this->model->paypal_info( $submission_id );

    $this->view->paypal_info($submission);
  }

  /**
   * Generate CSV.
   */
  public function frontend_generate_csv() {
    $this->view->generate_csv($this->model->submissions_to_export());
  }

  /**
   * Generate XML.
   */
  public function frontend_generate_xml() {
    $this->view->generate_xml($this->model->submissions_to_export());
  }
}
