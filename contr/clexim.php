<?php

/**
 * Created by PhpStorm.
 * User: erikd
 * Date: 6-5-2016
 * Time: 10:01
 */
class clexim extends clcommon
{
    function __construct()
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=aap');
        header('Pragma: no-cache');
        header("Expires: 0");
        $outstream = fopen("php://output", "w");
    }

    function download_csv_results($results, $name = NULL)
    {
        if( ! $name)
        {
            $name = 'contactlist download.csv';
        }
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename='. $name);
        header('Pragma: no-cache');
        header("Expires: 0");
        $outstream = fopen("php://output", "w");
        foreach($results as $result)
        {
            fputcsv($outstream, $result);
        }
        fclose($outstream);
    }
    public function exct( ) {
        global $wpdb;
        $cl = $wpdb->prefix . 'contactlist';
        $cts = array();
        $rsct = $wpdb->get_results("select * from " . $cl . " where tp = 'ct' ", ARRAY_A);
        if ($rsct) {
            foreach ($rsct as $rwct) {
                $ct = json_decode($rwct['tx'], TRUE);
                $cts [] = $ct;
            }
            $this->download_csv_results($cts, 'your_name_here.csv');
        }
    }
}
