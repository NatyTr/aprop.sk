<?php
/**
 * @package   Woo Comgate
 * @author    toret.cz
 * @license   GPL-2.0+
 * @link      http://toret.cz
 * @copyright 2015 Toret.cz
 */

if(isset($_POST['update'])){
    control_woo_comgate_licence($_POST['licence']);
}


$licence_key  = get_option('woo-comgate-licence-key', '');
$licence_info = get_option('woo-comgate-info', '');

?>

<div class="wrap">

    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

    <div class="toret-box box-info">
        <div class="box-header">

        </div>
        <div class="box-body">
            <?php if(!empty($licence_info)){ ?>
                <p><strong><?php echo $licence_info; ?></strong></p>
                <?php
            }
            ?>
            <form method="post">
                <input type="text" name="licence" id="licence" style="width:400px;" value="<?php if(!empty($licence_key)){ echo $licence_key; } ?>" />
                <input type="hidden" name="update" value="ok" />
                <input type="submit" class="toret-aktivovat" value="Ověřit licenci" />
            </form>
        </div>
    </div>

    <div class="clear"></div>

    <div class="toret-box box-info">
        <div class="box-body">
            <p>
                URL adresy pro nastavení v účtu Comgate:<br /><br />
                <b>URL zaplacený:</b><br />
                <code>
                    <?php echo home_url(); ?>/?comgate=paid&id=${id}&refId=${refId}
                </code><br /><br />
                <b>URL zrušený:</b><br />
                <code>
                    <?php echo home_url(); ?>/?comgate=delete&id=${id}&refId=${refId}
                </code><br /><br />
                <b>URL nevyřízený:</b><br />
                <code>
                    <?php echo home_url(); ?>/?comgate=failed&id=${id}&refId=${refId}
                </code><br /><br />
                <b>URL pro předání výsledku platby:</b><br />
                <code>
                    <?php echo home_url(); ?>/?comgate=notify
                </code><br /><br />
            </p>
        </div>
    </div>

</div>