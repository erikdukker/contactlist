<?php
/**
 * Created by PhpStorm.
 * User: erikd
 * Date: 6-5-2016
 * Time: 10:01
 */
class clcss extends clcommon
{
    function editcss( ) {
        global $wpdb;
        $cl = $wpdb->prefix . 'contactlist';
        $css = $wpdb->get_row("select * from " . $cl . " where tp = 'ws'", ARRAY_A);
        if (empty($css)) {
            $deletable = false;
            //die('hij komt niet');
            $css = $wpdb->get_row("select * from " . $cl . " where tp = 'wt' ", ARRAY_A);
        }
        $txcss = stripcslashes($css['tx']);
        ?>
        <style>
            .mrk {background-color: #E4E4FF}
        </style>
        <form action=<?php echo $this->action; ?> method='post'>

            <h2><?php echo __( 'Edit layout (css):', 'edcl' ); ?></h2>
            <br>
            <?php echo __( 'This is an expert task but you can start again with reset', 'edcl' ); ?>
            <br><br>
            <textarea id='css' name='css' rows="20" cols="80"><?php echo $txcss; ?></textarea>
             <br>
            <input type="submit" name="edcss" class="button button-primary" value="<?php echo __( 'save', 'edcl' ); ?>" >
            <input type="submit" name="delcss" class="button button-primary" value="<?php echo __( 'reset', 'edcl' ) ?>" >

        </form>
         <?php
    }
}