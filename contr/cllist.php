<?php

/**
 * Created by PhpStorm.
 * User: erikd
 * Date: 6-5-2016
 * Time: 10:01
 */
class cllist extends clcommon
{
    public function newcontact()
    {
        $this->generateform();
    }

    public function contactform()
    {
        global $wp_query;
        if (isset($wp_query->query_vars['ky'])) {
            $ky = $wp_query->query_vars['ky'];
        }
        $ky = base64_decode($ky);
        $this->generateform($ky);
    }

    public function ctlist()
    { //console
        global $wpdb;
        $cl = $wpdb->prefix . 'contactlist';
        $rwcu = $this->getcur();
        if (!isset($rwcu['setok'])) {
            ?>
            <h2>Please maintain settings first</h2>
            <?php
        } else {
            if (isset($_GET["s"])) {
                $action = str_getcsv(base64_decode($_GET["s"]), '|');
                switch ($action[0]) {
                    case 'proc':
                        $this->proc();
                        break;
                    case 'tost':
                        $rwct = $wpdb->get_row("select * from " . $cl . " where id = '" . $action[1] . "' and tp = 'ct'", ARRAY_A);
                        $arr = json_decode($rwct['tx'], TRUE);
                        $stnw = $action[2];
                        $sttm = time();
                        $arr['log'][$sttm] = $stnw;
                        $tx = json_encode($arr);
                       $wpdb->update($cl, array('tx' => $tx, 'st' => $stnw, 'sttm' => $sttm), array('id' => $action[1]), array('%s', '%s', '%s'), array('%d'));
                        $this->condash();
                        break;
                    case 'edit':
                        $this->generateform($action[1]);
                        break;
                    case 'vlog':
                        $this->vlog($action[1]);
                        break;
                }
            } else {
                $this->selection();
            }
        }
    }

    public function selection()
    { //console
        global $wpdb;
        $cl = $wpdb->prefix . 'contactlist';
        $rwcu = $this->getcur();
        $fields = $rwcu['fields'];
       //var_dump($fields);
        foreach ($fields as $field) {
            if ($field['use'] != 'sys') {
                $names[$field['short']] = $field['name'];
            }
        }
        $rwse = $wpdb->get_row("select * from " . $cl . " where tp = 'se' ", ARRAY_A); //settings
        $arr = json_decode($rwse['tx'], TRUE);
        $tags = $arr['tgs'];
        $type = $arr['tgtp'];
        $rsct = $wpdb->get_results("select * from " . $cl . " where tp = 'ct' and st <> 'Z99' order by st", ARRAY_A);
        // var_dump($rsct);
        ?>
        <style>
            .vals { padding-left:25px;}
        </style>
        <table>
        <?php
        if ($rsct) {
            foreach ($rsct as $rwct) {
                $arrct = json_decode($rwct['tx']);
                $vals = $arrct['vals'];
                ?>
                <tr>
                    <td>
                        <table>
                            <tr>
                                <td>
                                    <?php echo $vals['NM1'] . ' ' . $vals['EM1'] . ' ';
                                    if (isset($vals['TAGS'])) {
	                                    $parts = str_getcsv($vals['TAGS'], ';');
	                             //       var_dump($vals['TAGS']);
	                              //      var_dump($parts);
	                                    foreach ($parts as $tag){
	                                        if ($tag != '') {
	                              //              var_dump($tag);
	                                            echo '(' . $type[substr($tag, 0, 1)] . ') ';
	                                            echo $tags[$tag];
	                                        }
	                                    }
                                    }
                                    ?>
                                </td>  <td style="display: none">
                                    <?php
                                    if (isset($vals['TAGS'])) echo $vals['TAGS'];
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="vals">
                                    <?php
                                    $voor = '';
                                    foreach ($names as $name => $val) {
                                        if ($field['use'] != 'sys' and $vals[$name] != '') {
                                            echo $voor . $val . ': ' . $vals[$name];
                                            $voor = '|';
                                        }
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <?php
            }
        }
        ?>
        </table>
        <?php
    }
}