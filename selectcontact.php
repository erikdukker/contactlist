<?php
/**
 * Created by PhpStorm.
 * User: erikd
 * Date: 17-5-2016
 * Time: 20:04
 */
global $wpdb;
$outsel = '';
$cl = $wpdb->prefix . 'contactlist';
include_once(EDCL_DIR . "/contr/clcommon.php");
$common = new clcommon;
if (current_user_can('manage_options')) {
    $admin = true;
} else {
    $admin = false;
}
$i = 0;
$search = '';
unset($pseltags);
$rwse = $wpdb->get_row("select * from " . $cl . " where tp = 'se' ", ARRAY_A); //setting
$arrt = json_decode($rwse['tx'], TRUE);
$tags = $arrt['tgs'];
$type = $arrt['tgtp'];
$rwcu = $wpdb->get_row("select * from " . $cl . " where tp = 'cu'", ARRAY_A);
$arrl = json_decode($rwcu['tx'], TRUE);
$fields = $arrl['fields'];
foreach ($fields as $field) {
    $fldtype[$field['short']] = $field['type'];
}
$txsel = false;
if (isset($_POST['psort'])) { // psort allways filled
    $sort = $_POST['psort'];
    if (isset($_POST['psearch']) and $_POST['psearch'] != NULL) {
        $search = trim($_POST['psearch']);
        $txsel = true;
    }
    if (isset($_POST['pseltags'])) {
        $pseltags = $_POST['pseltags'];
    } else {
        unset($pseltags);
    }
    $rsct = $wpdb->get_results("select * from " . $cl . " where tp = 'ct' and st <> 'Z99'", ARRAY_A);
    if ($rsct) {
        foreach ($rsct as $rwct) {
            $arrc = json_decode($rwct['tx'], TRUE);
            $vals = $arrc['vals'];
            $vals['id'] = $rwct['id'];
            /* txt selection */
            $txfnd = true;
            if ($txsel) {
                $valstr = '';
                foreach ($vals as $key => $val) {
                    $valstr .= $val . '|';
                }
                $valstr = strtolower($valstr);
                $search = strtolower($search);
                $parts = str_getcsv($search, ' ');
                foreach ($parts as $part) {
                    if (!strpos(' ' . $valstr, $part)) {
                        $txfnd = false;
                    }
                }
            }
            //tags selection
            $tagsel = true;
            if ($txfnd) {
                if (empty($pseltags) and $admin) {
                    $tagsel = true; // extra
                } else {
                    $tagsel = false;
                    foreach ($pseltags as $seltag) {
                        if (strpos($vals['TAGS'], $seltag) > 0) {
                            $tagsel = true;
                        }
                    }
                }
            }
            // add to selection
            if ($txfnd and $tagsel) {
                // decode tags
                if (isset($vals['TAGS']) and $vals['TAGS'] != null) {
                    $parts = str_getcsv($vals['TAGS'], ';');
                    foreach ($parts as $tag) {
                        if ($tag != '') {
                            $vals['TAGS'] = '(' . $type[substr($tag, 0, 1)] . ')';
                        }
                    }
                    if (isset($vals[$sort])) {
                        $sel[$vals[$sort]] = $vals;
                    } else {
                        $sel['a'] = $vals;
                    }
                    $i++;
                } else {
                    $vals['TAGS'] = ' ';
                }
            }
        }
    } else {
        $mess[] = "w" . __('No contact selected', 'edcl');
    }
    if ($i > 0) {
        $mess[] = 'm' . $i . " " . __('contacts selected', 'edcl');
    }
}
// selection screen
// selection screen
// selection screen
global $wp_query;
$mode = '';
if (base64_decode(strrev(get_option('lc'))) < time()) {
    delete_option('lc');
}
if (isset($wp_query->query_vars['ky'])) { // if with key get auths + tags
    $ky = $wp_query->query_vars['ky'];
    $key = base64_decode($ky);
    if (substr($key, 0, 5) == 'unsub') {
        $mode = 'unsub';
    } elseif (substr($key, 0, 4) == 'conf') {
        $mode = 'conf';
    } else {
        $mode = 'cont';
    }
}
if ($admin or ($arrl['settings']['open'] and $mode == 'cont')) {   // list allowed
    foreach ($fields as $field) {
        if ($field['short'] == 'TAGS') {
            // nothing
        } else {
            if ($admin or $field['visible'] == 'all') {
                $names[$field['short']] = $field['name'];
                $sortfld[$field['short']] = $field['name'];
            }
        }
    }
    $seltags = array();
    if (!$admin) { // if with key get auths + tags
        $rwct = $wpdb->get_row("select * from " . $cl . " where tp = 'ct' and ky = '" . $key . "'", ARRAY_A); //admn
        if ($rwct) {
            $id = $rwct['id'];
        } else {
            error_log("contact doesn't exist: before building tag list " . $key);
            die (__('contact doesn\'t exist', 'edcl'));
        }
        $arrc = json_decode($rwct['tx'], TRUE);
        $vals = $arrc['vals'];
        $parts = str_getcsv($vals['TAGS'], ';');
        foreach ($parts as $tag) {
            if (substr($tag, 0, 1) == 's') {
                $seltags[] = $tag;
            }
        }
        // die(var_dump($seltags));
    } else {
        foreach ($tags as $tag => $name) {
            if (substr($tag, 0, 1) == 'g') {
                $seltags[] = $tag;
            }
        }
    }
    $link = add_query_arg('settings-updated', 'true', wp_get_referer());
    if (strpos($link, 'wp-admin') > 0) {
        global $wp;
        $link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }
    $outsel .= $common->getstyle();
    $outsel .= "<form action=" . $link . " method='post'>" . PHP_EOL;
    $outsel .= "<input type='hidden' name='admin[]' value=" . $admin . ">" . PHP_EOL;
    if (isset($mess)) {
        foreach ($mess as $mes) {
            if (substr($mes, 0, 1) == 'e') $color = 'red';
            if (substr($mes, 0, 1) == 'w') $color = 'blue';
            if (substr($mes, 0, 1) == 'm') $color = 'limegreen';
            $outsel .= "<p style='color:$color;'>" . substr($mes, 1) . "</p>" . PHP_EOL;
        }
    }
    $outsel .= "<div style='display: inline-block; vertical-align: text-top;'>" . PHP_EOL;
    $outsel .= __('tags', 'edcl') . "<br>" . PHP_EOL;

    if (isset($pseltags)) {
        $txpseltags = " " . implode(" ", $pseltags);
    } else {
        $txpseltags = " ";
    }
    $outsel .= "<select name='pseltags[]' multiple required>" . PHP_EOL;
    foreach ($seltags as $tag) {
        if (isset($txpseltags) and strpos($txpseltags, $tag) > 0) {
            $select = 'selected';
        } else {
            $select = '';
        }
        $outsel .= "<option " . $select . " value='" . $tag . "'>(" . $type[substr($tag, 0, 1)] . ') ' . $tags[$tag] . "</option>" . PHP_EOL;
    }
    $outsel .= "</select>" . PHP_EOL;
    $outsel .= "<input type='hidden' name='aseltags'  value='" . serialize($seltags) . "'>" . PHP_EOL;
    $outsel .= "</div>" . PHP_EOL;
    $outsel .= "<div style='display: inline-block; vertical-align: text-top;'>" . PHP_EOL;
    $outsel .= "<span class='dashicons dashicons-search'></span><br>" . PHP_EOL;
    $outsel .= "<input type='text' title='type search arguments eg: erik amsterdam'
                       name='psearch' size=40 value='" . $search . "'>" . PHP_EOL;
    $outsel .= "</div>" . PHP_EOL;
    $outsel .= "<div style='display: inline-block;vertical-align: text-top;'>" . PHP_EOL;
    $outsel .= __('sort on', 'edcl') . "<br>" . PHP_EOL;
    $outsel .= "<select name='psort' required>" . PHP_EOL;
    foreach ($sortfld as $short => $name) {
        $outsel .= "<option value='" . $short . "'>" . $name . "</option>" . PHP_EOL;
    }
    $outsel .= "</select>" . PHP_EOL;
    $outsel .= "</div>" . PHP_EOL;
    $outsel .= "<div>" . PHP_EOL;
    $outsel .= "<br><input type='submit' name='list' id='stat' class='edcl-button'
                   value='" . __('find contacts', 'edcl') . "'>" . PHP_EOL;
    if ($admin) {
        $outsel .= "<input type='submit' name='select' id='stat' class='edcl-button'
                       value='" . __('save selection as', 'edcl') . "'>" . PHP_EOL;
        $outsel .= "<input type='text' name='nm' size='20'  placeholder='" . __('selection name', 'edcl') . "'' style='
                        width: 150px;margin:4px;' >" . PHP_EOL;
    }
    $outsel .= "</div>" . PHP_EOL;
    $outsel .= "<br>" . PHP_EOL;
    $outsel .= "</form>" . PHP_EOL;
    $outsel .= "<table>" . PHP_EOL;
    if (isset($_POST['list']) and $i > 0) {
        $outsel .= '<tr>';
        foreach ($names as $short => $name) {
            $outsel .= '<td><b>' . $name . '</b></td>' . PHP_EOL;
        }
        $outsel .= '</tr>';
        foreach ($sel as $vals) {
            $outsel .= '<tr>';
            foreach ($names as $short => $name) {
                $outsel .= "<td>" . PHP_EOL;
                if ($short == 'TAGS') {
                    // nothing
                } elseif ($short == 'id') {
                    // nothing
                } else {
                    if (isset($vals[$short])) {

                        if ($fldtype[$short] == 'ch') {
                            if ($vals[$short]) {
                                $outsel .= __('set', 'edcl') . PHP_EOL;
                            } else {
                                $outsel .= __('not set', 'edcl') . PHP_EOL;
                            }

                        } else {
                            $outsel .= $vals[$short];
                        }
                    }
                }
                $outsel .= "</td>" . PHP_EOL;
            }
            $outsel .= "</tr>" . PHP_EOL;
        }
    }
    if (isset($_POST['select']) and isset($_POST['select']) and $i > 0) {
        $ids = array();
        foreach ($sel as $vals) {
            $ids[] = $vals['id'];
        }
        $sttm = time();
        $nm = $_POST['nm'];
        $tx = json_encode($ids);
        $wpdb->insert($cl, array('ky' => 'sl', 'st' => '', 'sttm' => $sttm, 'em' => '', 'nm' => $nm, 'tp' => 'sl', 'tx' => $tx));
        $outsel .= "<br>" . __('selection saved as', 'edcl') . " " . $nm . PHP_EOL;
    }
    $outsel .= "</table>" . PHP_EOL;
}   //list allowed
