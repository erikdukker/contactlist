<?php

/**
 * Created by PhpStorm.
 * User: erikd
 * Date: 6-5-2016
 * Time: 10:01
 */
class clconsole extends clcommon
{
    public function console()
    { //console
        global $wpdb;
        $cl = $wpdb->prefix . 'contactlist';
        $rwcu = $this->getcur();
        if (!isset($rwcu['setok'])) {
            ?>
            <h2><?php echo __( 'Please maintain settings first', 'edcl' ); ?></h2>
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
                    case 'admin':
                        $this->admin($action[1]);
                        break;
                }
            } else {
                $this->condash();
            }
        }
    }

    public function condash()
    { //console
        global $wpdb;
        $cl = $wpdb->prefix . 'contactlist';
        $stdefct = $this->inststat();
        $rwcu = $this->getcur();
        $chkbef = time() - $rwcu['settings']['check'];
        $rembef = time() - $rwcu['settings']['remind'];
        $perma = $rwcu['settings']['perma'];
        $this->from = $rwcu['settings']['reply'];
        if (substr($perma, -1) == '/') {
            $perma = substr($perma, 0, strlen($perma) - 1);
        }
        $stold = '';
        $rsct = $wpdb->get_results("select * from " . $cl . " where tp = 'ct' order by st", ARRAY_A);
        if ($rsct) {
            foreach ($rsct as $rwct) {
                if ($rwct['st'] != $stold) {                // de level break
                    $stold = $rwct['st'];
                }
                switch ($rwct['st']) {
                    case 'A30'  : // mail verstuurd
                        if ($rwcu['settings']['repeat'] > 0 and $rwct['sttm'] < $chkbef) { // herinneren
                            $stnw = 'A40';
                        }
                        break;
                    case 'A50'  : // herinnerd : vervolg herinneringen
                        if ($rwcu['settings']['repeat'] > 1 and $rwct['sttm'] < $rembef) { // herinneren
                            $hertel = 0;
                            $arr = json_decode($rwct['tx'], TRUE);

                            $logs = $arr['log'];
                            foreach ($logs as $sttm => $st) {
                                if ($st == 'A50') {
                                    $hertel++;
                                } else {
                                    if ($st != 'A40') {
                                        $hertel = 0;
                                    }
                                }
                            }
                            if ($hertel >= $rwcu['settings']['repeat']) {
                                $stnw = 'A60';
                            } else {
                                $stnw = 'A40';
                            }
                        }
                        break;
                    case 'A90'  : // 'gecontroleerd
                        if ($rwct->sttm < $convoor) { // herinneren
                            $stnw = 'A20';
                        }
                        break;

                }
                if (isset($stnw)) {
                    $arr = json_decode($rwct['tx'], TRUE);
                    $arr['vals'] = $vals;
                    $sttm = time();
                    $arr['log'][$sttm] = $stnw;
                    $tx = json_encode($arr);
                    $wpdb->update($cl, array('tx' => $tx, st => $stnw, sttm => $sttm), array('id' => $_POST['id']), array('%s', '%s', '%s'), array('%d'));
                }
            }
        }
        // opbouw lijst
        $stold = '';
        $cts = array();
        $url = $_SERVER["REQUEST_URI"];
        $proc = '&s=' . base64_encode('proc|');
        $rsct = $wpdb->get_results("select * from " . $cl . " where tp = 'ct' order by st", ARRAY_A);
        foreach ($rsct as $rwct) {
            if ($rwct['st'] != $stold) {                // de level break
                $ct['des'] = $stdefct[$rwct['st']]['des'];
                $stold = $rwct['st'];
            }
            $ct['nm'] = $rwct['nm'];
            $ct['em'] = $rwct['em'];
            if (isset($stdefct[$stold]['btst'])) {
                $ct['btst'] = $stdefct[$stold]['btst'];
                $ct['bttx'] = $stdefct[$stold]['bttx'];
            }
            $ct['tost'] = $url . '&s=' . base64_encode('tost|' . $rwct['id'] . '|' . $ct['btst']);
            $ct['edit'] = $perma . '?ky=' . base64_encode('editcont|' . $rwct['id']);
            $ct['vlog'] = $url . '&s=' . base64_encode('vlog|' . $rwct['id']);
            $ct['admin'] = $url . '&s=' . base64_encode('admin|' . $rwct['id']);
            $cts[] = $ct;
            unset($ct);
        }
        if (isset($cts)) {
            $link = $url . $proc;
            ?>
            <h2><?php echo __( 'Console', 'edcl' ); ?></h2>

            <h4><?php  echo __( 'Check proposed actions, change if needed. Then', 'edcl' );
                echo '<a href="'.$link.'"> '. __( 'Process', 'edcl' ); ?> </a></h4>
            <table>
                <?php
                foreach ($cts as $ct) {
                    if (isset($ct['des'])) {
                        echo "</table ><h4>" . $ct['des'] . "</h4><table>";
                    }
                    ?>
                    <tr>
                        <td><?php echo $ct['nm'] ?></td>
                        <td><?php echo $ct['em'] ?></td>
                        <td><a href="<?php echo $ct['tost'] . '" >' . $ct['bttx'] ?></a></td>
                        <td><a href="<?php echo $ct['edit'] ?> " ><?php  echo __( 'edit', 'edcl' ); ?></a></td>
                        <td><a href="<?php echo $ct['vlog'] ?> "><?php  echo __( 'view log', 'edcl' ); ?></a></td>
                        <td><a href="<?php echo $ct['admin'] ?> "><?php  echo __( 'admin', 'edcl' ); ?></a></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php
        } else {
            ?>
            <h2><?php echo __( 'Console', 'edcl' ); ?></h2>

            <h4><?php echo __( 'No contact found', 'edcl' ); ?></h4>
            <?php
        }
    }

    public function proc()
    {
        global $wpdb;
        $cl = $wpdb->prefix . 'contactlist';
        $rwcu = $this->getcur();
        $mailtmpl = $this->prephtm('smch');
        $_SESSION['from'] = $rwcu['settings']['reply'];
        //  klaar zetten acties
        ?>
        <h2><?php echo __( 'Console', 'edcl' ); ?></h2>


        <h4><?php echo __( 'Send mails', 'edcl' ); ?></h4>

        <table>
        <?php
        $rsct = $wpdb->get_results("select * from " . $cl . " where tp = 'ct' and (st = 'A20' or st = 'A40') order by st", ARRAY_A);
        if ($rsct) {
            foreach ($rsct as $rwct) {
                $arr = json_decode($rwct['tx'], TRUE);
                unset($mail);
                switch ($rwct['st']) {
                    case 'A20'  : // uitnodigen
                        $mail = str_replace('%REMIND%', ' ', $mailtmpl);
                        $stnw = 'A30';
                        $msg = __( 'an invitation to check contact data', 'edcl' )." ";
                        break;
                    case 'A40'  : // herinneren
                        $mail = str_replace('%REMIND%', __( 'Reminder', 'edcl' ), $mailtmpl);
                        $stnw = 'A50';
                        $msg =  __( 'an reminder to check contact data', 'edcl' )." ";
                        break;
                }
                if (isset($mail)) {
                    $mail = str_replace('%CONTACTLIST%', $rwcu['settings']['description'], $mail);
                    if (isset($arrcu['settings']['logo'])){
                        $img = "<img alt='contactlist.nl'  src='".$arrcu['settings']['logo']."' > ";
                        $mail = str_replace('%LOGO%', $img, $mail);
                    }
                    $sub =  __( 'Check contact data with ', 'edcl' ).' '. $rwcu['settings']['description'];
                    $actionlink= $rwcu['settings']['perma'].'?ky=' . base64_encode($rwct['ky']);
                    //  var_dump($actionlink);
                    $aktie = '<a href="' . $actionlink . '" style="font-size:20p;color:red">Controleer gegevens</a>';
                    $mail = str_replace('%ACTIONLINK%', $aktie, $mail);
                    foreach ($arr['vals'] as $att => $val) {
                        $tag = strtoupper("%" . $att . "%");
                        $mail = str_replace($tag, $val, $mail);
                    }
                    if (!isset($rwcu['settings']['reply']) or $rwcu['settings']['reply'] == '') {
                        die('reply at settings not filled');
                    }
                    //         var_dump($arr['vals']['EM1']);
                    $to[0] = $arr['vals']['EM1'];
                    if ($rwcu['settings']['test']) {
                        $to[0] = $rwcu['settings']['reply'];
                    }
                    // wp_mail ( string|array $to, string $subject, string $message, string|array $headers = '', string|array $attachments = array() )
                    wp_mail($to, $sub, $mail);
                    $sttm = time();
                    $arr['log'][$sttm] = $stnw;
                    $tx = json_encode($arr);
                    $wpdb->update($cl, array('tx' => $tx, 'st' => $stnw, 'sttm' => $sttm), array('id' => $rwct['id']), array('%s', '%s', '%s'), array('%d'));
                    echo '<tr><td>';
                    echo $arr['vals']['NM1'] . '</td><td>' . $arr['vals']['EM1'] .'</td><td>'.  __( 'received', 'edcl' ) .'</td><td>'. $msg;
                    echo '</td></tr>';
                }
            }
        }
        echo '</table>'.PHP_EOL;
    }

    public function vlog($id = "0")
    {
        global $wpdb;
        $cl = $wpdb->prefix . 'contactlist';
        $stdefct = $this->inststat();
        $rwct = $wpdb->get_row("select * from " . $cl . " where id = '" . $id . "' and tp = 'ct'", ARRAY_A);
        $arr = json_decode($rwct['tx'], TRUE);
        $logs = $arr['log'];
        ?>
        <h2><?php echo __( 'Log', 'edcl' ); ?></h2>


        <?php echo __( 'What happend when? :', 'edcl' )." ".$rwct['nm'] . ' (' . $rwct['em'] . ')<br><br>' ?>

        <?php
        foreach ($logs as $time => $status) {
            //var_dump($log);
            echo date("Y-m-d G:i:s", $time) . '  ' . $status . ' ';
            if (isset($stdefct[$status]['des'])) {
                echo $stdefct[$status]['des'] . '<br>';
            }
        }
    }



    public function admin($id = "0")
    {
        global $wpdb;
        $cl = $wpdb->prefix . 'contactlist';
        $rwse = $wpdb->get_row("select * from " . $cl . " where tp = 'se' ", ARRAY_A); //settings
        $arr = json_decode($rwse['tx'], TRUE);
        // var_dump($arr);
        $tags = $arr['tgs'];
        $type = $arr['tgtp'];
        $rwct = $wpdb->get_row("select * from " . $cl . " where tp = 'ct' and id = '" . $id . "'", ARRAY_A); //contact
        $arr = json_decode($rwct['tx'], TRUE);
        $vals = $arr['vals'];
        $rwcu = $this->getcur();
        $stdefct = $this->inststat();
        // var_dump($vals);
        ?>
        <form action=<?php echo $this->action; ?> method='post'>
            <input type='hidden' name='id' value="<?php echo $rwct['id']; ?>">
            <h4><?php echo __( 'Admin contact', 'edcl' ); ?></h4>

            <?php echo __( 'Contact :', 'edcl' ).' '.$vals['NM1'] . ' ' . $vals['EM1'] ?><br><br>

            <?php echo __( 'Status : ', 'edcl' ); ?> <select name="status">
                <?php
                foreach ($stdefct as $stat => $atts) {
                    if ($stat == $rwct['st']) {
                        $sel = 'selected';
                    } else {
                        $sel = '';
                    }
                    echo "<option " . $sel . " value='" . $stat . "'>" . $atts['des'] . "</option>" . PHP_EOL;
                }
                ?>
            </select>
            <h4><?php echo __( 'Tags', 'edcl' ); ?></h4>

            <?php echo __( 'Tags : ', 'edcl' ); ?> <select name="tags[]" multiple>
                <?php
                foreach ($tags as $tag => $name) {
                    if (isset($vals['TAGS']) and strpos($vals['TAGS'],$tag) > 0) {
                        $sel = 'selected';
                    } else {
                        $sel = '';
                    }
                    echo "<option " . $sel ." value='" . $tag . "'>(" .$type[substr($tag,0,1)].') '.$name . "</option>" . PHP_EOL;
                }
                ?>
            </select></td>
            <p class="submit">
                <input type="submit" name="adupd" class="button button-primary" value="<?php echo __( 'save', 'edcl' ); ?>">
                <input type="submit" name="addel" class="button button-primary" value="<?php echo __( 'delete contact', 'edcl' ); ?>">
            </p>
        </form>
        <?php
    }
    public function set_from() {
        if(isset($this->from)) {
            return $this->from;

        }
        error_log('common ');
        return $e;
    }

}
