<?php

/**
 * Created by PhpStorm.
 * User: erikd
 * Date: 5-5-2016
 * Time: 10:42
 */
class initdb
{
    public function __construct ()
    {
        global $wpdb;
        $cl = $wpdb->prefix . 'contactlist';
        $data_table = $wpdb->prefix . 'data';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE if not exists $cl (
              id mediumint(9) NOT NULL AUTO_INCREMENT,
              cur mediumint(9) NOT NULL,
              ky varchar(30) NOT NULL,
              tp varchar(2) NOT NULL,
              st varchar(5) NOT NULL,
              sttm int NOT NULL,
              nm varchar(50) NOT NULL,
              em varchar(50) NOT NULL,
              tx text,
              UNIQUE KEY (id),
              INDEX (ky)
            ) $charset_collate;";
        $wpdb->get_results( $sql);
    }

    public function loadrow($tp,$file,$nm) {
        global $wpdb;
        error_log($tp.' 1 '.$file);

        $cl = $wpdb->prefix . 'contactlist';
        $rwtp = $wpdb->get_row( "SELECT * FROM $cl WHERE tp = '".$tp."' and ky = '".$file."'", ARRAY_A);
        if ( isset($rwtp)) {
            $wpdb->delete($cl, array('id' => $rwtp['id']));
        }
        $filenm = substr(get_bloginfo('language'),0,2)."-".$file;
        require_once( EDCL_DIR.'/ini/'.$filenm.'.php' );
        $wpdb->insert( $cl,
            array(
                'ky' => $file,
                'nm' => $nm,
                'tp' => $tp,
                'tx'  => $data
            ),
            array( '%s', '%s', '%s', '%s' ));
   //     var_dump($data);
        error_log($tp.' 2 '.$file);
    }
}