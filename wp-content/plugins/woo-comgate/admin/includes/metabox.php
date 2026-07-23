<?php 

function order_comgate_log_meta_box( $object, $box ) {
    global $post;
  
    ?>
    <p><a href="<?php echo admin_url() . 'admin.php?page=comgate-log&order_id='.$post->ID; ?>" target="_blank"><?php _e('Zobrazit záznamy logu pro tuto objednávku','woo-comgate'); ?></a></p>
  
  <?php 

}