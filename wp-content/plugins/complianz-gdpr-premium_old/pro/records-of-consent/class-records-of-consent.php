<?php
defined( 'ABSPATH' ) or die( "you do not have access to this page!" );

if ( ! class_exists( "cmplz_records_of_consent" ) ) {
	class cmplz_records_of_consent {
		private static $_this;
		function __construct() {
			if ( isset( self::$_this ) ) {
				wp_die( sprintf( '%s is a singleton class and you cannot create a second instance.',
					get_class( $this ) ) );
			}
			self::$_this = $this;
			if ( cmplz_get_value('records_of_consent') === 'yes' ) {
				add_action( 'cmplz_admin_menu', array( $this, 'menu_item' ), 10 );
				add_action( 'wp_ajax_cmplz_delete_record', array( $this, 'ajax_delete_record' ) );
				add_action( 'wp_ajax_cmplz_export_roc_to_csv', array( $this, 'ajax_export_roc_to_csv' ) );
				add_action( 'cmplz_after_proof_of_consent_generation', array( $this, 'update_users_without_snapshot' ) );
			}
		}

		static function this() {
			return self::$_this;
		}

		/**
		 * Delete a snapshot
		 */

		public function ajax_delete_record() {

			if ( ! cmplz_user_can_manage() ) {
				return;
			}

			if ( isset( $_POST['record_id'] ) ) {
				$record_id = intval($_POST['record_id']);
				$this->delete_record( $record_id );
				$response   = json_encode( array(
					'success' => true,
				) );
				header( "Content-Type: application/json" );
				echo $response;
				exit;
			}
		}

		/**
		 * Export all records in the current selection to a csv file
		 */

		public function ajax_export_roc_to_csv(){
			$error = false;
			$progress = 0;
			$page_batch = 1000;
			if ( ! cmplz_user_can_manage() ) {
				$error = true;
			}

			$offset = get_option('cmplz_current_poc_export_offset') ? get_option('cmplz_current_poc_export_offset') : 0;
			if ( $offset == 0 ) {
				//cleanup old file
				$file = $this->filepath();
				if (file_exists($file)){
					unlink($file);
				}
				$args = array(
					'number' => $page_batch,
					'offset' => $offset,
				);

				$args['search']  = !empty( $_GET['s'] ) ? urldecode( trim( sanitize_text_field( $_GET['s'] ) ) ) : false;
				$args['order']   = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC';
				$args['orderby'] = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'time';
				if ( isset( $_GET['cmplz_month_select'] ) && isset( $_GET['cmplz_year_select'] ) && $_GET['cmplz_month_select'] != 0 && $_GET['cmplz_year_select'] != 0 ) {
					$args['start_date'] = COMPLIANZ::$proof_of_consent->get_time_stamp_for_date( intval($_GET['cmplz_year_select']), intval($_GET['cmplz_month_select']), 'start_date' );
					$args['end_date']   = COMPLIANZ::$proof_of_consent->get_time_stamp_for_date( intval($_GET['cmplz_year_select']), intval($_GET['cmplz_month_select']), 'end_date' );
				}

				update_option('cmplz_roc_export_args', $args);
			} else {
				$args = get_option('cmplz_roc_export_args');
				$args['offset'] = $offset;
			}

			foreach ($args as $key => $value ) {
				if ($value === 'false') {
					$args[$key]= false;
				}
			}

			if ( !$error ) {
				$args['offset'] = $offset * $page_batch;
				$offset++;
				$pages_completed = ( $offset ) * $page_batch;
				update_option('cmplz_current_poc_export_offset', $offset );
				$total = $this->get_consent_records( $args, true );
				if ($total>0) {
					$data = $this->get_consent_records($args);
					$add_header = $offset==1;
					$this->create_csv_file( $data, $add_header);
					$progress = 100 * ($pages_completed/$total);
					$progress = $progress>100 ? 100 : $progress;
				} else {
					$progress = 100;
				}
			}

			if ( $progress == 100 ) {
				delete_option('cmplz_current_poc_export_offset' );
				delete_option('cmplz_roc_export_args');
			}

			$response   = json_encode( array(
				'success' => true,
				'progress' => round($progress, 0),
				'link' => '<a class="button button-primary cmplz-header-btn" href="'.$this->fileurl()."?token=".time().'">'.__("Download", "complianz-gdpr").'</a>',
			) );
			header( "Content-Type: application/json" );
			echo $response;
			exit;
		}

		/**
		 * create csv file from array
		 *
		 * @param array $data
		 * @param bool $add_header
		 * @throws Exception
		 */

		private function create_csv_file($data, $add_header = true ){
			$delimiter=",";
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			$uploads = wp_upload_dir();
			$upload_dir = $uploads['basedir'];
			if (!file_exists($upload_dir)) {
				mkdir($upload_dir);
			}

			if (!file_exists($upload_dir . "/complianz")) {
				mkdir($upload_dir . "/complianz");
			}

			//generate random filename for storage
			if (!get_option('cmplz_roc_file_name')) {
				$token = str_shuffle ( time() );
				update_option('cmplz_roc_file_name', $token);
			}
			$filename = get_option('cmplz_roc_file_name');

			//set the path
			$file = $upload_dir . "/complianz/".$filename.".csv";

			//'a' creates file if not existing, otherwise appends.
			$csv_handle = fopen ($file,'a');

			//create a line with headers
			if ($add_header) {
				$headers = $this->parse_headers_from_array( $data );
				fputcsv( $csv_handle, $headers, $delimiter );
			}
			$data = array_map(array($this, 'localize_date') , $data);
			foreach ($data as $line) {
				$line = array_map('sanitize_text_field', $line);
				fputcsv($csv_handle, $line, $delimiter);
			}
			fclose ($csv_handle);
		}

		/**
		 * Get headers from an array
		 * @param array $array
		 *
		 * @return array|bool
		 */

		private function parse_headers_from_array($array){
			if (!isset($array[0])) return array();
			$array = $array[0];
			$array[__("Date", "complianz-gdpr")] = 1;
			return array_keys($array);
		}

		/**
		 * Get a localized date for this row
		 * @param $row
		 *
		 * @return mixed
		 */
		public function localize_date($row){
			if (isset($row['time'])) {
				$row['nice_time'] = sprintf("%s at %s", date( str_replace( 'F', 'M', get_option('date_format')), $row['time']  ), date( get_option('time_format'), $row['time'] ) );
			}
			return $row;
		}

		/**
		 * Get a filepath
		 * @return string
		 */

		private function filepath(){
			$uploads = wp_upload_dir();
			$upload_dir = $uploads['basedir'];
			return $upload_dir . "/complianz/".get_option('cmplz_roc_file_name').".csv";
		}

		/**
		 * Get a file URL
		 * @return string
		 */

		private function fileurl(){
			$uploads = wp_upload_dir();
			return $uploads['baseurl'] . "/complianz/".get_option('cmplz_roc_file_name').".csv";
		}

		/**
		 * The last pdf in time before this record is the one belonging to this record.
		 * The next pdf in time after the poc belongs to the next record
		 * If there are no other records between these two pdf's in time, we can delete the pdf.
		 *
		 * @param int $id
		 */

		public function delete_record( $id ) {
			global $wpdb;
			$delete_file = true;
			$record_id = intval($id);
			$record = $wpdb->get_row("select * from {$wpdb->prefix}cmplz_statistics where ID = $record_id" );
			if ( $record ) {
				$poc_url = $record->poc_url;
				if ( !empty($poc_url) ) {
					//get count of other records with this url
					$other_records_count = $wpdb->get_var($wpdb->prepare("select count(*) from {$wpdb->prefix}cmplz_statistics where ID != %s AND poc_url = %s", $record_id, $poc_url ) );
					if ( $other_records_count>0 ) {
						$delete_file = false;
					}

					if ( $delete_file ) {
						$uploads    = wp_upload_dir();
						$upload_url = $uploads['baseurl'];
						$url        = $upload_url . '/complianz/snapshots/';
						$file_name = str_replace($url, '', $poc_url);
						COMPLIANZ::$proof_of_consent->delete_snapshot( $file_name );
					}
				}

				$wpdb->delete(
						$wpdb->prefix.'cmplz_statistics',
						array('ID' => $record_id)
				);
			}
		}

		/**
		 * Get poc pdf file belonging to this record
		 * @param int $record_time_stamp
		 * @param string $region
		 * @return false|array
		 */

		public function get_poc_for_record( $record_time_stamp, $region ){
			if (empty($region)) $region = COMPLIANZ::$company->get_default_region();
			$args = array(
				'number'  => 1,
				'start_date'    => 0,
				'end_date'      => $record_time_stamp,
				'region'      => $region,
			);

			$files      = COMPLIANZ::$proof_of_consent->get_cookie_snapshot_list( $args );
			if (!is_array($files)) {
				return false;
			}
			$file = reset($files);
			if ($file) {
				$uploads    = wp_upload_dir();
				$upload_dir = $uploads['basedir'];
				$upload_url = $uploads['baseurl'];
				$file['url']= str_replace( $upload_dir, $upload_url, $file['path'] );
				return $file;
			} else {
				return false;
			}
		}

		/**
		 * @param int $record_time_stamp
		 * @param string $region
		 *
		 * @return false|array
		 */

		public function get_next_poc( $record_time_stamp, $region ){
			if (empty($region)) $region = COMPLIANZ::$company->get_default_region();
			$args = array(
				'number' => 1,
				'order'  => 'ASC',
				'start_date'    => $record_time_stamp,
				'region'    => $region,
			);

			$files      = COMPLIANZ::$proof_of_consent->get_cookie_snapshot_list( $args );
			if (isset($files[0])) {
				$file = $files[0];
				$uploads    = wp_upload_dir();
				$upload_dir = $uploads['basedir'];
				$upload_url = $uploads['baseurl'];
				$file['url']= str_replace( $upload_dir, $upload_url, $file['path'] );
				return $file;
			} else {
				return false;
			}
		}


		/**
		 * Add submenu items
		 */

		public function menu_item() {
			//if (!cmplz_user_can_manage()) return;
			add_submenu_page(
				'complianz',
				__( 'Records of consent', 'complianz-gdpr' ),
				__( 'Records of consent', 'complianz-gdpr' ),
				'manage_options',
				"cmplz-proof-of-consent",
				array( $this, 'records_of_consent_overview' )
			);
		}

		/**
		 * Render records of consent table
		 */

		public function records_of_consent_overview() {
			include( cmplz_path . 'pro/records-of-consent/class-records-of-consent-table.php' );
			$snapshots_table = new cmplz_Records_Of_Consent_Table();
			$snapshots_table->prepare_items();
			?>
			<script>
				jQuery(document).ready(function ($) {
					$(document).on('click', '.cmplz-delete-record', function (e) {
						e.preventDefault();
						var btn = $(this);
						btn.closest('tr').css('background-color', 'red');
						var delete_record_id = btn.data('id');
						$.ajax({
							type: "POST",
							url: '<?php echo admin_url( 'admin-ajax.php' )?>',
							dataType: 'json',
							data: ({
								action: 'cmplz_delete_record',
								record_id: delete_record_id
							}),
							success: function (response) {
								if (response.success) {
									btn.closest('tr').remove();
								}
							}
						});

					});
				});
			</script>

			<div id="cookie-policy-snapshots" class="wrap cookie-snapshot">
				<form id="cmplz-cookiestatement-snapshot-generate" method="POST" action="">
					<h1 class="wp-heading-inline"><?php _e( "Records of consent", 'complianz-gdpr' ) ?></h1>

					<?php echo wp_nonce_field( 'cmplz_generate_snapshot', 'cmplz_nonce' ); ?>
					<input type="submit" class="button button-primary cmplz-header-btn"
					       name="cmplz_generate_snapshot"
					       value="<?php _e( "Generate proof of consent file", "complianz-gdpr" ) ?>"/>
					<button class="button button-primary cmplz_export_roc_to_csv cmplz-header-btn"><?php _e( "Export to csv", "complianz-gdpr" ) ?></button>
					<a href="https://complianz.io/records-of-consent/" target="_blank" class="button button-default cmplz-header-btn"><?php _e( "Read more", "complianz-gdpr" ) ?></a>

				</form>
				<?php
				if ( isset( $_POST['cmplz_generate_snapshot_error'] ) ) {
					cmplz_notice( __( "Proof of consent generation failed. Check your write permissions in the uploads directory",
							"complianz-gdpr" ), 'warning' );
				}
				?>
				<form id="cmplz-cookiestatement-snapshot-filter" method="get" action="">

					<?php
					$snapshots_table->search_box( __( 'Filter',
						'complianz-gdpr' ), 'cmplz-cookiesnapshot' );
					$snapshots_table->date_select();

					$snapshots_table->display();
					?>
					<input type="hidden" name="page"
					       value="cmplz-proof-of-consent"/>

				</form>
				<?php do_action( 'cmplz_after_cookiesnapshot_list' ); ?>
			</div>

			<?php
		}

		/**
		 * Add the latest snapshot file to all users who haven't been updated since the last cookie policy snapshot generation was scheduled.
		 * @param int|bool $generation_scheduled_time //time at which moment the new pdf was scheduled to be generated in the next 24 hours
		 */

		public function update_users_without_snapshot( $generation_scheduled_time ){
			//if it's forced, don't update
			if ( !$generation_scheduled_time ) return;
			$generation_scheduled_time = intval($generation_scheduled_time);
			$regions = cmplz_get_regions();
			foreach ($regions as $region => $label) {
				//get last poc pdf file, counting back from now.
				$file = COMPLIANZ::$records_of_consent->get_poc_for_record( time(), $region );
				//file has path, url, file, time
				if ( $file ) {
					//for all users without a file since generation update time, set this file
					global $wpdb;
					$sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}cmplz_statistics SET poc_url = %s where poc_url='' AND region = %s AND time>=$generation_scheduled_time", $file['url'], $region ) ;
					$wpdb->query($sql);
				}
			}
		}

		/**
		 * @param array $args
		 * @param bool $count
		 *
		 * @return array|false
		 */

		public function get_consent_records( $args = array(), $count = false ) {
			$defaults = array(
					'number'     => false,
					'offset'     => 0,
					'order'      => 'DESC',
					'orderby'    => 'time',
					'start_date' => 0,
					'end_date'   => false,
					'search'	=> false,
			);

			$args       = wp_parse_args( array_filter($args), $defaults );
			global $wpdb;
			$where     = " where time> '0' ";
			$where     .= $args['end_date'] ? $wpdb->prepare( " AND time> %s AND time < %s", $args['start_date'], $args['end_date'] ) : "";
			$where     .= $args['search'] ? $wpdb->prepare( " AND ID=%s", $args['search'] ) : "";
			$total = $wpdb->get_var( "SELECT count(*) from {$wpdb->prefix}cmplz_statistics $where" );

			if ( !$count ) {
				$limit   = (int) $args['number'];
				$orderby = sanitize_title( $args['orderby'] );
				$order   = sanitize_title( $args['order'] );
				$limit   = $limit > 0 ? $limit : 1;
				$offset  = $args['offset'];
				$limit_sql = $args['number'] ? "limit $limit offset $offset" : '';
				$sql = "SELECT * from {$wpdb->prefix}cmplz_statistics $where ORDER BY $orderby $order " . $limit_sql;
				$records = $wpdb->get_results( $sql );
				$records = json_decode(json_encode($records), true);
				if ( empty( $records ) ) {
					return false;
				}

				return $records;
			} else {
				return $total;
			}
		}

	}
} //class closure
