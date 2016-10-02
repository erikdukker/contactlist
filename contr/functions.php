<?php
/**
 * Created by PhpStorm.
 * User: erikd
 * Date: 2-5-2016
 * Time: 10:54
 */

function wl($var) 		{ error_log(var_dump($var)); }
function tn($var) {
    $rw = '';
    if (!empty($var)) {
        foreach ($var as $key => $entry) {
            if (is_array($entry)) {
                $rw .= $key . ": " . implode(',', $entry) . "<br>";
            } else {
                $rw .= $key . ": " . $entry . "<br>";
            }
        }
    }
    echo "value:<br> " . $rw ."<br> ". PHP_EOL;
}
