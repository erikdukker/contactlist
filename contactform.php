<?php
/**
 * Created by PhpStorm.
 * User: erikd
 * Date: 17-5-2016
 * Time: 20:04
 */
//base
global $wpdb;
global $wp_query;
$outform = '';
$mess = array();
$cl = $wpdb->prefix . 'contactlist';
$rwcu = $wpdb->get_row("select * from " . $cl . " where tp = 'cu'", ARRAY_A);
if (!$rwcu) {
    die(__('A problem occured : no contact list', 'edcl'));
}
$arrcu = json_decode($rwcu['tx'], TRUE);
$fields = $arrcu['fields'];
$problem = false;
$ready = false;
include_once(EDCL_DIR . "/contr/clcommon.php");
$common = new clcommon;
// to database
if (isset($_POST['cont'])) {
    $form = $_SESSION['form'];
    $id = $_POST['id'];
    $rwct = $wpdb->get_row("select * from " . $cl . " where tp = 'ct' and id = '" . $id . "'", ARRAY_A);
    if ($rwct) {
        $arrc = json_decode($rwct['tx'], TRUE);
        $valsold = $arrc['vals'];
    }
    $mode = $_POST['mode'];
    foreach ($fields as $field) {
        $key = $field['short'];
        if (isset($_POST[$field['short']])) {
            $val = $_POST[$field['short']];
            if ($field['short'] == "EM1") {
                $em = trim(strtolower($val));
            } elseif ($field['short'] == "NM1") {
                $nm = $val;
            }
            if ($field['type'] == 'ch') {
                $vals[$key] = true;
            } else {
                $vals[$key] = $val;
            }
        } elseif ($field['type'] == 'ch') { // check fields are handled different
            $vals[$key] = false;
        } elseif (isset($valsold[$key])) {
            $vals[$key] = $valsold[$key];
        } elseif (isset($field['default']) and $field['default'] != null) { // default ??
            if ($field['type'] != 'ch') {
                $value = " value = '" . $field['default'] . "'";
            } else {
                if ($field['default'] == 'true') {
                    $checked = ' checked ';
                } else {
                    $checked = ' ';
                }
            }
        } else {
            $vals[$key] = '';
        }
    }
    echo "<br>";
    $rsctex = $wpdb->get_results("select * from " . $cl . " where tp = 'ct' and em = '" . $em . "'", ARRAY_A);
    if ($rsctex) {
        if ($mode == 'insert') {
            $mess[] = "e" . __('Email address already known', 'edcl');
            $problem = true;
        } else {
            if ($wpdb->num_rows > 1) {
                $mess[] = "e" . __('Email address already known', 'edcl');
                $problem = true;
            } else {
                $rwctex = $rsctex[0];
                if ($rwctex['id'] != $id) {
                    $mess[] = "e" . __('Email address already known', 'edcl');
                    $problem = true;
                }
            }
        }
    }
    if (!$problem) {
        if ($mode == 'update') { //update
            if (!$rwct) {
                error_log("contact doesn't exist: before update id " . $id);
                die ("contact doesn't exist");
            }
            $arr = json_decode($rwct['tx'], TRUE);
            $arr['vals'] = $vals;
            $st = 'A70';
            $sttm = time();
            $arr['log'][$sttm] = $st;
            $tx = json_encode($arr);
            $wpdb->update($cl, array('tx' => $tx, 'st' => $st, 'sttm' => $sttm, 'nm' => $nm, 'em' => $em), array('id' => $id), array('%s', '%s', '%s', '%s', '%s'), array('%d'));
            $mess[] = "s" . __('Contact data updated', 'edcl');
            $vals = $arr['vals'];
        } elseif ($mode == 'insert') {  // insert
            $arr = array();
            $arr['vals'] = $vals;
            $sttm = time();
            $ky = str_replace(".", "A", microtime(true));
           // die(var_dump($form['parm']));
            if (isset($form['parm']) and strpos($form['parm'], 'open') === false) {  // admin not open
                $st = 'A20'; // admin insert
            } else {
                if (isset($form['parm']) and strpos($form['parm'], 'conf') !== false) {  // conf email
                    $st = 'C10'; // open (registed)
                } else {
                    $st = 'C30'; // open (registered without confirmation)
                }
            }
            $arr['log'][$sttm] = $st;
            $tx = json_encode($arr);
            echo '<br>';
            $wpdb->insert($cl, array('ky' => $ky, 'st' => $st, 'sttm' => $sttm, 'em' => $em, 'nm' => $nm, 'tp' => 'ct', 'tx' => $tx));
            $mess[] = "s" . $form['ins'];
           // die(var_dump($st));
            if ($st == 'C10') {  // confirm email
                $mail = $common->prephtm('smcf');
                $img = "<img alt='contactlist.nl'  src='" . $arrcu['settings']['logo'] . "' > ";
                $mail = str_replace('%LOGO%', $img, $mail);
                $mail = str_replace('%CONTACTLIST%', $arrcu ['settings']['description'], $mail);

                // echo 'voor mail'.var_dump($mail).'na mail';
                $sub = __('Confirm your registration at', 'edcl') . ' ' . $arrcu['settings']['description'];
                $actionlink = $arrcu['settings']['perma'] . '?ky=' . base64_encode('conf' . $ky);
                //var_dump($actionlink);
                //  var_dump($actionlink);
                $aktie = "<a href='" . $actionlink . "' style='font-size:20px;color:#2f48fc'>" . __('Confirm', 'edcl') . '</a>';
                $mail = str_replace('%ACTIONLINK%', $aktie, $mail);
                // var_dump($vals);
                foreach ($vals as $att => $val) {
                    $tag = strtoupper("%" . $att . "%");
                    $mail = str_replace($tag, $val, $mail);
                }
                if (!isset($arrcu['settings']['reply']) or $arrcu['settings']['reply'] == '') {
                    die('reply at settings not filled');
                }
                $to[0] = $arr['vals']['EM1'];
                if ($arrcu['settings']['test']) {
                    $to[0] = $arrcu['settings']['reply'];
                }
                //var_dump($mail);
                // wp_mail ( string|array $to, string $subject, string $message, string|array $headers = '', string|array $attachments = array() )
                wp_mail($to, $sub, $mail);
                $ready = true;
            }
            unset($vals);
        }
    }
}
// start screen
// start screen
// start screen
// start screen

if (!isset($form)) {
    $rwse = $wpdb->get_row("select * from " . $cl . " where tp = 'se' ", ARRAY_A);
   // die( var_dump($rwse));

    $arrse = json_decode($rwse['tx'], TRUE);
    //die( var_dump($arrse));
    foreach ($arrse['frms'] as $frm) {
        //   die($frm['name'].' '. $params['name']);
        if ($frm['name'] == $params['name']) {
            $form = $frm;
        }
    }
    if (!isset($form)) {
        error_log("form not found name:" . $params['name']);
        echo "form not found";
    }
    $_SESSION['form'] = $form;
} elseif (isset($_SESSION['form'])) {
    $form = $_SESSION['form'];
}
//die($params['name']);
//die(var_dump($form));
if (isset($wp_query->query_vars['ky'])) { //call with parameter
    $ky = $wp_query->query_vars['ky'];
    $key = base64_decode($ky);
    if (substr($key, 0, 5) == 'unsub') {
        $key = substr($key, 5);
        $mode = 'unsub';
    } elseif (substr($key, 0, 4) == 'conf') {
        $key = substr($key, 4);
        $mode = 'conf';
    } else {
        //die($key);
        $mode = 'update';
        $rwct = $wpdb->get_row("select * from " . $cl . " where tp = 'ct' and ky = '" . $key . "'", ARRAY_A); //rwctact
        if ($rwct) {
            $id = $rwct['id'];
        } else {
            $parts = str_getcsv($key, '|');
            $rwct = $wpdb->get_row("select * from " . $cl . " where tp = 'ct' and id = '" . $parts[1] . "'", ARRAY_A); //admn
            if ($rwct) {
                $id = $rwct['id'];
            } else {
                error_log("contact doesn't exist: before building form id " . $parts[1]. " key " . $key);
                die ("contact doesn't exist");
            }
        }
        $arrct = json_decode($rwct['tx'], TRUE);
        $vals = $arrct['vals'];
    }
} elseif (isset($_POST['mode'])) {
    $mode = $_POST['mode'];
    // keep $vals
} elseif (!$problem) {
    if (isset($form['parm']) and strpos($form['parm'], 'open') !== false) {  // admin
        $mode = 'insert';
        unset($vals);
    } elseif (current_user_can('manage_options')) {  // admin
        $mode = 'insert';
        unset($vals);
    } else {
        $mode ='none';
        //die(__('not available: use link', 'edcl'));
    }
}
if (isset($form['but1']) and $form['but1'] != '') {  // button text show form
    $but1 = $form['but1'];
} else {
    $but1 = '';
}
if (isset($form['instr']) and $form['instr'] != '') {  // button text show form
    $instr = $form['instr'];
}
if (isset($form['but2']) and $form['but2'] != '') {  // button text save form
    $but2 = $form['but2'];
} else {
    $but2 = __('send', 'edcl');
}

if ($mode == 'unsub' or $mode == 'conf') {
    $rwse = $wpdb->get_row("select * from " . $cl . " where tp = 'se' ", ARRAY_A);
    $arrse = json_decode($rwse['tx'], TRUE);
    foreach ($arrse['frms'] as $frm) {
        if ($frm['name'] == 'messages') {
            $form = $frm;
        }
    }
}
if ($mode == 'unsub') {
    $rwct = $wpdb->get_row("select * from " . $cl . " where tp = 'ct' and ky = '" . $key . "'", ARRAY_A);
    if ($rwct) {
        $arrc = json_decode($rwct['tx'], TRUE);
        $st = 'C90';
        $sttm = time();
        $arrc['log'][$sttm] = $st;
        $tx = json_encode($arrc);
        $wpdb->update($cl, array('tx' => $tx, 'st' => $st, 'sttm' => $sttm), array('ky' => $key), array('%s', '%s', '%s'), array('%s'));
        $outform .= $common->getstyle();
        $outform .= "<br><h2>" . $form['unsub'] . "</h2>" . PHP_EOL;
    }
} elseif ($mode == 'conf') {
    $rwct = $wpdb->get_row("select * from " . $cl . " where tp = 'ct' and ky = '" . $key . "'", ARRAY_A);
    if ($rwct) {
        $arr = json_decode($rwct['tx'], TRUE);
        $st = 'C20';
        $sttm = time();
        $arr['log'][$sttm] = $st;
        $tx = json_encode($arr);
        $wpdb->update($cl, array('tx' => $tx, 'st' => $st, 'sttm' => $sttm), array('ky' => $key), array('%s', '%s', '%s'), array('%s'));
        $outform .= $common->getstyle();
        $outform .= "<br><h2>" . $form['conf'] . "</h2>" . PHP_EOL;
    }
} elseif ($mode == 'none') {
    // do nothing
} else {
   // die('fun');
   // die($outform);
    if (!isset($id)) { //mode == insert
        $id = 0;
    };
    if (isset($form['fields']) and strpos($form['fields'],';') !== false) {
        $selfields = str_getcsv($form['fields'], ';');
    } else {
        foreach ($fields as $field) {
            $selfields[] = $field['short'];
        }
    }
    foreach ($fields as $field) {
        $fieldshort[$field['short']] = $field;
    }
    $outform .= $common->getstyle();
    //die($form['parm']);
    // die(isset($mess));
    if (sizeof($mess)  > 0) {
        foreach ($mess as $mes) {
            if (substr($mes, 0, 1) == 'e') $color = 'red';
            if (substr($mes, 0, 1) == 'w') $color = 'blue';
            if (substr($mes, 0, 1) == 's') $color = 'limegreen';
            $outform .= "<p style='color:" . $color . "'>" . substr($mes, 1) . "</p>" . PHP_EOL;
        }
    } else {
        if (strpos($form['parm'], 'hidden') > 0 and sizeof($mess) == 0 ) {  // hidden form
            $outform .= "<script> function show(){ document.getElementById('bt1'.toString()).style.display='none'";
            $outform .= ";document.getElementById('cf'.toString()).style.display='inline' } </script>";
            $outform .= '<div class="edcl-button b2" id="bt1"
                            onclick="show()">' . $but1 . '</div>';
            $display = "none";
        } else {
            $display = "inline";
        }

        $outform .= "<div role='form'  id='cf' lang='nl-NL' dir='ltr' style='display:" . $display . ";'>" . PHP_EOL;
        if (isset($instr)) { // hidden form
            $outform .= "<br>" . $instr . "<br><br>";
        }
       // die($outform);
        $outform .= "<form action=" . get_permalink() . " method='post' class='edcl-form'>" . PHP_EOL;
        $outform .= "<div style='display: none;'>" . PHP_EOL;
        $outform .= "<input type='hidden' name='id' value='" . $id . "'>" . PHP_EOL;
        $outform .= "<input type='hidden' name='mode' value='" . $mode . "'>" . PHP_EOL;
        $outform .= "</div>" . PHP_EOL;

        $reqcnt = 0;
        if (!$ready) {
            //die(var_dump($selfields));
            foreach ($selfields as $selfield) {
                if (!isset($fieldshort[$selfield])) {
                    die(__('field doesn \'t exist', 'edcl') . ': ' . $selfield);
                }
                if ($selfield == "EM1" or $selfield == "NM1") {
                    $reqcnt++;
                }
                $field = $fieldshort[$selfield];
                if (isset($vals[$selfield])) {
                    if ($field['type'] != 'ch') {
                        $value = " value = '" . $vals[$selfield] . "'";
                    } else {
                        if ($vals[$selfield]) {
                            $checked = ' checked ';
                        } else {
                            $checked = ' ';
                        }
                    }
                } elseif (isset($field['default']) and $field['default'] != null) { // default ??
                    if ($field['type'] != 'ch') {
                        $value = " value = '" . $field['default'] . "'";
                    } else {
                        if ($field['default'] == 'true') {
                            $checked = ' checked ';
                        } else {
                            $checked = ' ';
                        }
                    }
                } else {
                    $value = "";
                    $checked = ' ';
                }
                // echo $outform;
                if ($field['visible'] != 'hide' and $selfield != 'TAGS') {
                    $outform .= "<p>" . PHP_EOL;
                    if (($field['required']) and !($mode == 'insert') or $field['use'] == 'sys') { // easy insert
                        $required = ' required ';
                    } else {
                        $required = '';
                    }
                    $place = " placeholder = '" . $field['place'] . "' ";
                    switch ($field['type']) {
                        case 'txv':
                            $outform .= $field['name'] . '<br>' . PHP_EOL;
                            $outform .= "<span class='edcl-wrap' >" . PHP_EOL;
                            $outform .= "<input type='text' pattern='.{0," . $field['length'] . "}'
                                                title='max " . $field['length'] . " " . __('positions', 'edcl') . "' name=" . $selfield . $place .
                                " class='edcl-field' size=" . $field['length'] . $required . $value . " >" . PHP_EOL;
                            break;
                        case 'txf':
                            $outform .= $field['name'] . '<br>' . PHP_EOL;
                            $outform .= "<span class='edcl-wrap' >" . PHP_EOL;
                            $outform .= "<input type='text' pattern='.{" . $field['length'] . "}'
                                                title=' " . $field['length'] . " " . __('positions', 'edcl') . "'  name=" . $selfield . $place .
                                " class='edcl-field' size=" . $field['length'] . $required . $value . " >" . PHP_EOL;
                            break;
                        case 'eml':
                            $outform .= $field['name'] . '<br>';
                            $outform .= "<span class='edcl-wrap' >" . PHP_EOL;
                            $outform .= "<input type='email' name=" . $selfield . " size='40'
                                                       class='edcl-field' " . $required . $value . " >" . PHP_EOL;
                            break;
                        case 'url':
                            $outform .= $field['name'] . '<br>' . PHP_EOL;
                            $outform .= "<span class='edcl-wrap' >" . PHP_EOL;
                            $outform .= "<input type='text' pattern='[a-zA-Z0-9-.]+\.[a-zA-Z]{2,10}(/\S*)?$'
                                                title='url' name=" . $selfield . $place .
                                "class='edcl-field'  size=" . $field['length'] . $required . $value . " >" . PHP_EOL;
                            break;
                        case 'ch':
                            $outform .= "<span class='edcl-wrap' >" . PHP_EOL;
                            $outform .= '<br>' . PHP_EOL;
                            $outform .= "<input type='checkbox' title='check' name=" . $selfield . $checked . " >" . PHP_EOL;
                            $outform .= ' ' . $field['name'] . PHP_EOL;
                            break;
                    }
                    $outform .= "</span>";
                    $outform .= "</p>";
                }
            }
            if ($reqcnt != 2) {
                die(__('email and name are required in the form', 'edcl'));
            }
            $outform .= "";
            $outform .= "<input type='submit' name='cont' class='edcl-button b2' value='" . $but2 . "'>";
            $outform .= "";
        }
        $outform .= "</form>";
        $outform .= "</div>";
    }
}