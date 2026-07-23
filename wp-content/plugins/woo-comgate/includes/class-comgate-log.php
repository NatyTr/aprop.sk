<?php 

/**
 * @package   Woo Comgate 
 * @author    toret.cz
 * @license   GPL-2.0+
 * @link      https://toret.cz
 * @copyright 2019 Toret.cz
 */

class Woo_Comgate_Log {


	/**
	 * Instance of this class.
	 *
	 * @since    1.2.4
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Log table name.
	 *
	 * @since    1.2.4
	 *
	 * @var      string
	 */
	protected $table_name = 'comgate_log';

    /**
     * Plugin slug.
     *
     * @since    1.2.4
     *
     * @var      string
     */
    protected $plugin_slug = 'woo-comgate';

	/**
	 * Limit
	 *
	 * @since    1.2.4
	 *
	 * @var      string
	 */
	protected $limit = 100;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.2.4
	 */
	private function __construct() {

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.2.4
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {


		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
  	 * Get logs for table
  	 *
  	 * @since 1.2.4
  	 */
  	public function get_logs(){

  		global $wpdb;

        if( isset( $_GET['offset'] ) && $_GET['offset'] > 1 ){

            $offset = esc_attr( $_GET['offset'] );
            $start = ( $offset * $this->limit ) - $this->limit;

            $logs = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.$this->table_name." ORDER BY datetime DESC LIMIT " . $this->limit . " OFFSET ".$start."" );

        }else{
            
            $logs = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.$this->table_name." ORDER BY datetime DESC LIMIT " . $this->limit );

        }

        if( !empty( $logs ) ){  
        	
        	return $logs; 

        }else{

        	return false;

        }

    }

    /**
     * Get logs for order
     *
     * @since 1.2.4
     */
    public function get_order_logs( $order_id ){

        global $wpdb;

        $logs = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.$this->table_name." WHERE order_id = '".$order_id."' ORDER BY date DESC " );
        
        if( !empty( $logs ) ){  
            
            return $logs; 

        }else{

            return false;

        }

    }

    /**
  	 * Render table
  	 *
  	 * @since 1.2.4
  	 */
  	public function render_table(){

  		if( !empty( $_GET['order_id'] ) ){

  			$logs = $this->get_order_logs( $_GET['order_id'] );

  		}else{

  			$logs = $this->get_logs();
  		
  		}

  		if( false === $logs ){

  			$html = '<p>'.__( 'Nenalezeny žádné záznamy', $this->plugin_slug ) .'</p>';

  		}else{

  			$html = '<table class="table-bordered" style="table-layout:fixed;">';

  			$html .= $this->table_head();

  			foreach( $logs as $log ){

  				$html .= $this->render_table_line( $log );

  			}

  			$html .= '</table>';

  		}

  		return $html;

    }

    /**
  	 * Render table head
  	 *
  	 * @since 1.2.4
  	 */
  	public function table_head(){

    	$html = '
    		<tr>
              <th>' . __('Id objednávky', $this->plugin_slug) . '</th>
              <th>' . __('Datum', $this->plugin_slug) . '</th>
              <th>' . __('Kontext', $this->plugin_slug) . '</th>
            </tr>
    	';

    	return $html;
    
  	}

    /**
     * Render table line
     *
     * @since 1.2.4
     */
    public function render_table_line( $log ){

        $html = '
            <tr>
              <td style="word-wrap:break-word;font-weight:bold;background:#f3f2f2;">' . $log->order_id . '</td>
              <td style="word-wrap:break-word;font-weight:bold;background:#f3f2f2;">' . $log->date . '</td>
              <td style="word-wrap:break-word;font-weight:bold;background:#f3f2f2;">' . $log->context . '</td>
            </tr>
            <tr>
              <td colspan="3" style="word-wrap:break-word;">' . $log->log . '</td>
            </tr>
        ';

        return $html;
    
    }

    /**
     * Save log
     *
     * @since 1.2.4
     */
    public function save_log( $data ){

        if( empty( $data['order_id'] ) ){
            return;
        }

        if( !empty( $data['status'] ) ){
            $status = $data['status'];
        }else{
            $status = '---';
        }
        if( !empty( $data['context'] ) ){
            $context = $data['context'];
        }else{
            $context = '---';
        }
        if( !empty( $data['note'] ) ){
            $note = $data['note'];
        }else{
            $note = '---';
        }

        $data = array(
            'order_id'  => $data['order_id'],
            'date'      => date('D, d M Y H:i:s'),
            'datetime'  => time(),
            'log'       => $data['log'],
            'status'    => $status,
            'context'   => $context,
            'note'      => $note
        );

        global $wpdb;
        
        $insert = $wpdb->insert( $wpdb->prefix.$this->table_name, $data ); 
        
        return $wpdb->last_query;
    
    }

    /**
  	 * Empty table
  	 *
  	 * @since 1.2.4
  	 */
  	public function delete_logs(){

    	global $wpdb;
    
    	$wpdb->query( 'TRUNCATE TABLE '.$wpdb->prefix.$this->table_name );
    
  	}

    /**
     * Pagination
     *
     * @since 1.2.4
     */
    public function pagination(){
    
        global $wpdb;

        if( !empty( $_GET['order_id'] ) ){
            $order_id = sanitize_text_field( $_GET['order_id'] );
            $logs = $this->get_order_logs( $order_id );
        }else{
            $logs = $wpdb->get_results( "SELECT ID FROM ".$wpdb->prefix.$this->table_name." ORDER BY date DESC" );
        }


        $all = count( $logs );
        $pages = ceil($all / $this->limit);
        if(!empty($_GET['offset'])){
            $current = $_GET['offset'];
        }else{
            $current = 1;
        }
     
        $html = '';
        $html .= '<div class="log-pagination">';
    
        $query_string = $_SERVER['QUERY_STRING'];
        
        if( $pages != 1 ){
     
            for ($i=1; $i <= $pages; $i++){
                if($current == $i){
                    $html .= '<span class="btn btn-default">'.$i.'</span>';
                }else{
                    $html .= '<a class="btn btn-primary" href="'.admin_url().'admin.php?'.$query_string.'&offset='.$i.'">'.$i.'</a>';
                }
            }
     
        }
     
        $html .= '</div>';
     
        return $html;

    } 


}//End class