<?php
/**
 * @package   Toret QR platby
 * @author    toret.cz
 * @license   GPL-2.0+
 * @link      https://toret.cz
 * @copyright 2021 Toret.cz
 */

?>

<div class="wrap">

    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

    <?php
    $curl = curl_init('https://toret.cz/wp-content/plugins/export-data.php');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    $response = curl_exec($curl);
    curl_close($curl);

    $data = unserialize($response);

    $lang = get_locale();

    $plugins = get_option( 'active_plugins' );

    $Aktivni = array();

    if ( ! function_exists( 'get_plugin_data' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    foreach ( $plugins as $plugin ) {
        $plugin_data = get_plugin_data( ABSPATH . 'wp-content/plugins/' . $plugin );
        if ( strtolower( strip_tags( $plugin_data['Author'] ) ) == 'toret.cz' ) {

            $titles = explode('/', $plugin);
            $title = $titles[0];

            $Aktivni[] = constant(strtoupper(str_replace('-', '', $title)));
            $Settings[constant(strtoupper(str_replace('-', '', $title)))] = constant(strtoupper(str_replace('-', '', $title)) . 'SETTINGS');
            $Version[constant(strtoupper(str_replace('-', '', $title)))] = $plugin_data['Version'];
            $Lic[constant(strtoupper(str_replace('-', '', $title)))] = get_option(constant(strtoupper(str_replace('-', '', $title)) . 'LIC'), '');
        }

    }

    $PlugID = $data['pluginid'];

    $poradi = array();

    foreach ($PlugID as $Pid){
        if( isset($data['active-' . $Pid])){
            $poradi[$data['poradi-' . $Pid]] = $Pid;
        }
    }


    ksort($poradi);

    if( ( isset($data['banner-akce']) ) || ( isset($data['banner-akce-en'] ) )){
        ?>
        <div class="banner-akce">
            <?php
            if( ($lang == 'cs_CZ') || ($lang == 'sk_SK') ){
                if( isset($data['banner-akce-url']) ){
                    echo '<a href="' . $data['banner-akce-url'] . '" target="_blank">';
                }else{
                    echo '<a href="' . $data['banner-akce-url-en'] . '" target="_blank">';
                }
            }else{
                echo '<a href="' . $data['banner-akce-url-en'] . '" target="_blank">';
            }
            ?>
            <img src="<?php
            if( ($lang == 'cs_CZ') || ($lang == 'sk_SK') ){
                if( isset($data['banner-akce']) ){
                    echo $data['banner-akce'];
                }else{
                    echo $data['banner-akce-en'];
                }
            }else{
                echo $data['banner-akce-en'];
            }
            ?>"
            />
            <?php echo '</a>'; ?>
        </div>
        <?php
    }
    if(!empty($Aktivni)){ ?>
        <div class="toret-aktivni">
            <h3><?php _e('Installed plugins', constant('WOOCOMGATESLUG')); ?></h3>
            <table class="toret-instal-plug">
                <thead>
                <tr>
                    <th><?php _e('Plugin name', constant('WOOCOMGATESLUG')); ?></th>
                    <th><?php _e('Version', constant('WOOCOMGATESLUG')); ?></th>
                    <th><?php _e('Current version', constant('WOOCOMGATESLUG')); ?></th>
                    <th><?php _e('Licence', constant('WOOCOMGATESLUG')); ?></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($poradi as $por){
                    if(in_array( $por , $Aktivni)){ ?>
                        <tr>
                            <td>
                                <?php
                                if( ($lang == 'cs_CZ') || ($lang == 'sk_SK') ) {
                                    echo $data['nazev-' . $por];
                                }else{
                                    echo $data['nazev-en-' . $por];
                                }
                                ?>
                            </td>
                            <td>
                                <?php echo $data['verze-' . $por]; ?>
                            </td>
                            <td>
                                <?php
                                if($data['verze-' . $por] == $Version[$por]){
                                    _e('Yes', constant('WOOCOMGATESLUG'));
                                }else{
                                    _e('No', constant('WOOCOMGATESLUG'));
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if('active' == $Lic[$por]){
                                    _e('Yes', constant('WOOCOMGATESLUG'));
                                }else{
                                    _e('No', constant('WOOCOMGATESLUG'));
                                }
                                ?>
                            </td>
                            <td class="toret-tri-odkazy">
                                <?php
                                if( isset($Settings[$por]) ){
                                    echo '<a href="' . admin_url() . $Settings[$por] . '" target="_blank">' . __('Settings', constant('WOOCOMGATESLUG')) . '</a>';
                                }
                                ?>
                                <?php
                                if( isset($data['dokumentace-' . $por]) ){
                                    if( ($lang == 'cs_CZ') || ($lang == 'sk_SK') ){
                                        echo '<a href="' . $data['dokumentace-' . $por] . '" target="_blank">' . __('Documentation', constant('WOOCOMGATESLUG')) . '</a>';
                                    }else{
                                        echo '<a href="' . $data['dokumentace-en-' . $por] . '" target="_blank">' . __('Documentation', constant('WOOCOMGATESLUG')) . '</a>';
                                    }
                                }
                                ?>
                                <?php
                                if( isset($data['changelog-' . $por]) ){
                                    if( ($lang == 'cs_CZ') || ($lang == 'sk_SK') ){
                                        echo '<a href="' . $data['changelog-' . $por] . '" target="_blank">' . __('Changelog', constant('WOOCOMGATESLUG')) . '</a>';
                                    }else{
                                        echo '<a href="' . $data['changelog-en-' . $por] . '" target="_blank">' . __('Changelog', constant('WOOCOMGATESLUG')) . '</a>';
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                    <?php }
                } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
    <div class="toret-tlacitka">
        <a class="toret-muj-ucet" href="<?php
        if( ($lang == 'cs_CZ') || ($lang == 'sk_SK') ){
            echo $data['muj-ucet'];
        }else{
            echo $data['muj-ucet-en'];
        }
        ?>" target="_blank"><?php _e('VIEW MY ACCOUNT', constant('WOOCOMGATESLUG')); ?></a>
        <a class="toret-podpora" href="<?php
        if( ($lang == 'cs_CZ') || ($lang == 'sk_SK') ){
            echo $data['podpora'];
        }else{
            echo $data['podpora-en'];
        }
        ?>" target="_blank"><?php _e('CONTACT SUPPORT', constant('WOOCOMGATESLUG')); ?></a>
    </div>
    <?php

    if( ( isset($data['banner-vzdy']) ) || ( isset($data['banner-vzdy-en'] ) )){
        ?>
        <div class="banner-vzdy">
            <?php
            if( ($lang == 'cs_CZ') || ($lang == 'sk_SK') ){
                if( isset($data['banner-vzdy-url']) ){
                    echo '<a href="' . $data['banner-vzdy-url'] . '" target="_blank">';
                }else{
                    echo '<a href="' . $data['banner-vzdy-url-en'] . '" target="_blank">';
                }
            }else{
                echo '<a href="' . $data['banner-vzdy-url-en'] . '" target="_blank">';
            }
            ?>
            <img src="<?php
            if( ($lang == 'cs_CZ') || ($lang == 'sk_SK') ){
                if( isset($data['banner-vzdy']) ){
                    echo $data['banner-vzdy'];
                }else{
                    echo $data['banner-vzdy-en'];
                }
            }else{
                echo $data['banner-vzdy-en'];
            }
            ?>"
            />
            <?php echo '</a>'; ?>
        </div>
        <?php
    }
    ?>

    <div class="toret-vsechny">
        <h3><?php _e('Boost your e-shop with our other plugins', constant('WOOCOMGATESLUG')); ?></h3>
        <table class="toret-next-plug">
            <thead>
            <tr>
                <th><?php _e('Plugin name', constant('WOOCOMGATESLUG')); ?></th>
                <th><?php _e('Description', constant('WOOCOMGATESLUG')); ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($poradi as $por){
                if(!in_array( $por , $Aktivni)){ ?>
                    <tr>
                        <td>
                            <?php
                            if( isset($data['detail-' . $por]) ){
                                if( ($lang == 'cs_CZ') || ($lang == 'sk_SK') ){
                                    echo '<a href="' . $data['detail-' . $por] . '" target="_blank">' . $data['nazev-' . $por] . '</a>';
                                }else{
                                    echo '<a href="' . $data['detail-en-' . $por] . '" target="_blank">' . $data['nazev-en-' . $por] . '</a>';
                                }
                            }?>
                        </td>
                        <td>
                            <?php
                            if( isset($data['popis-' . $por]) ){
                                if( ($lang == 'cs_CZ') || ($lang == 'sk_SK') ){
                                    echo $data['popis-' . $por];
                                }else{
                                    echo $data['popis-en-' . $por];
                                }
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if( isset($data['koupit-' . $por]) ){
                                if( ($lang == 'cs_CZ') || ($lang == 'sk_SK') ){
                                    echo '<a href="' . $data['koupit-' . $por] . '" target="_blank" class="toret-buy">' . __('BUY&nbsp;PLUGIN', constant('WOOCOMGATESLUG')) . '</a>';
                                }else{
                                    echo '<a href="' . $data['koupit-en-' . $por] . '" target="_blank" class="toret-buy">' . __('BUY&nbsp;PLUGIN', constant('WOOCOMGATESLUG')) . '</a>';
                                }
                            }
                            ?>
                        </td>
                    </tr>
                <?php }
            } ?>
            </tbody>
        </table>
    </div>
</div>

