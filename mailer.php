<?php
/**
 * Created by PhpStorm.
 * User: erikd
 * Date: 17-5-2016
 * Time: 20:04
 */

global $wpdb;
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
if (isset($_POST['dlsl']) and isset($_POST['psl'])) { //
    $wpdb->delete($cl, array('id' => $_POST['psl']), array('%d'));
}
if (isset($_POST['dlum']) and isset($_POST['pum'])) { //
    $wpdb->delete($cl, array('id' => $_POST['pum']), array('%d'));
}
unset($sls);
$rssl = $wpdb->get_results("select * from " . $cl . " where tp = 'sl' ", ARRAY_A); //selections
foreach ($rssl as $sl) {
    $sls [$sl['id']] = date("Y-m-d G:i", $sl['sttm']) . " " . $sl['nm'];
}
unset($nls);
$rsnl = $wpdb->get_results("select * from " . $cl . " where tp = 'um' ", ARRAY_A); //emails
foreach ($rsnl as $nl) {
    $nls [$nl['id']] = date("Y-m-d G:i", $nl['sttm']) . " " . $nl['nm'];
}
$rwcu = $wpdb->get_row("select * from " . $cl . " where tp = 'cu'", ARRAY_A);
$arrcu = json_decode($rwcu['tx'], TRUE);
$fields = $arrcu['fields'];
//var_dump($_POST);

if (isset($_POST['mail']) and isset($_POST['pum']) and isset($_POST['psl']) and get_option('lc')) { //
    $rwml = $wpdb->get_row("select * from " . $cl . " where  id = '" . $_POST['pum'] . "'", ARRAY_A);
    $rwsl = $wpdb->get_row("select * from " . $cl . " where  id = '" . $_POST['psl'] . "'", ARRAY_A);
    $psls = json_decode($rwsl['tx'], TRUE);
    //var_dump($psls);
    $mail = $common->prephtm($_POST['pum']);
    $mail = str_replace('%CONTACTLIST%', $arrcu ['settings']['description'], $mail);
    echo '<table>' . PHP_EOL;
   // var_dump($sl);
    foreach ($psls as $sl) {
        $rwct = $wpdb->get_row("select * from " . $cl . " where id = '" . $sl . "'", ARRAY_A);
        $arrc = json_decode($rwct['tx'], TRUE);
        $vals = $arrc['vals'];
        $sel = true;
        if ($_POST['selmtg'] != '' and strpos($vals['TAGS'],$_POST['selmtg']) == 0 ) {
            $sel = false;
        }
        if ($_POST['addmtg'] != '' and strpos($vals['TAGS'],$_POST['addmtg']) != 0 ) {
            $sel = false;
        }
        if ($rwct['st'] == 'C20') { // send email if not C20 unsubscribed
           echo $arrc['vals']['NM1'] . '</td><td>' . $arrc['vals']['EM1'] . '</td><td>' . __('don\'t want', 'edcl') . '</td><td>' . $rwml['nm'];
        } elseif (!$sel) {
            echo $arrc['vals']['NM1'] . '</td><td>' . $arrc['vals']['EM1'] . '</td><td>' . __('already recieved', 'edcl') . '</td><td>' . $rwml['nm'];
        } else {
           // var_dump($rwct['st']);
            if (substr($rwct['st'],0,1) == 'B' or substr($rwct['st'],0,1) == 'C') {
                $unsubscribe = $arrcu['settings']['perma'] . '?ky=' . base64_encode('unsub' . $rwct['ky']);
                $aktie = "<a href='" . $unsubscribe . "' style='font-size:13px;color:#2f48fc'>" . __('Unsubscribe', 'edcl') . '</a>';
            } else {
                $aktie = " "; // members can't unsubscibe
            }
            $mail = str_replace('%UNSUBSCRIBE%', $aktie, $mail);
            $img = "<img alt='contactlist.nl'  src='".$arrcu['settings']['logo']."' > ";
            $mail = str_replace('%LOGO%', $img, $mail);
            foreach ($arrc['vals'] as $att => $val) {
                $fld = strtoupper("%" . $att . "%");
                $mail = str_replace($fld, $val, $mail);
            }
            $to[0] = $arrc['vals']['EM1'];
            if ($arrcu['settings']['test']) {
                $to[0] = $arrcu['settings']['reply'];
            }
            $sub = $rwml['nm'];
            echo '<tr><td>' . PHP_EOL;
            if (wp_mail($to, $sub, $mail)) {
                echo $arrc['vals']['NM1'] . '</td><td>' . $arrc['vals']['EM1'] . '</td><td>' . __('received', 'edcl') . '</td><td>' . $rwml['nm'];
            } else {
                echo $arrc['vals']['NM1'] . '</td><td>' . $arrc['vals']['EM1'] . '</td><td>' . __('did not receive', 'edcl') . '</td><td>' . $rwml['nm'];
            }
            echo '</td></tr>' . PHP_EOL;
            $sttm = time();
            $arrc['log'][$sttm] = "email :" . $rwml['nm'];
            $arrc['vals']['TAGS'] .= ";" . $_POST['addmtg'];
            $tx = json_encode($arrc);
            $wpdb->update($cl, array('tx' => $tx), array('id' => $rwct['id']), array('%s'), array('%d'));
        }
        echo '</tr><tr>' . PHP_EOL;
    }
    echo '</tr></table>' . PHP_EOL;

}
// selection screen
global $wp_query;

if (!$admin) {   // list allowed
    die(__('Only for admin', 'edcl'));
}
$rwse = $wpdb->get_row("select * from " . $cl . " where tp = 'se' ", ARRAY_A); //rwse
$arr = json_decode($rwse['tx'], TRUE);

$type = $arr['tgtp'];
// var_dump($arr);
$tags = $arr['tgs'];
// var_dump($tags);
$mtg[''] = __('none', 'edcl');
foreach ($tags as $tag => $name) {
    if (substr($tag, 0, 1) == 'm') {
        $mtg[$tag] = $name;
    }
}
?>
<form action="<?php echo get_permalink(); ?>" method='post'>

    <h2>Mailer</h2>
    <input type='hidden' name='admin[}' value="<?php echo $admin; ?>">
    <?php
    if (isset($mess)) {
        foreach ($mess as $mes) {
            if (substr($mes, 0, 1) == 'e') $color = 'red';
            if (substr($mes, 0, 1) == 'w') $color = 'blue';
            if (substr($mes, 0, 1) == 'm') $color = 'limegreen';
            echo "<p style='color:$color;'>" . substr($mes, 1) . "</p><br>";
        }
    }

    ?>
    <table>
        <tr>
            <td>
                <?php echo __('user mail', 'edcl'); ?>
            </td>
            <td>
                <select name='pum'>
                    <?php
                    foreach ($nls as $id => $name) {
                        echo "<option value='" . $id . "'> " . $name . "</option>" . PHP_EOL;
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <?php echo __('selections', 'edcl'); ?>
            </td>
            <td>
                <select name='psl'>
                    <?php
                    foreach ($sls as $id => $name) {
                        echo "<option value='" . $id . "'> " . $name . "</option>" . PHP_EOL;
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <?php echo __('extra selection', 'edcl'); ?>
            </td>
            <td>
                <select name="selmtg">
                    <?php
                    foreach ($mtg as $tag => $name) {
                        echo "<option " . $tag . " value='" . $tag . "'>(" . $type[substr($tag, 0, 1)] . ') ' . $name . "</option>" . PHP_EOL;
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <?php echo __('new mail tag', 'edcl'); ?>
            </td>
            <td>
                <select name="addmtg">
                    <?php
                    foreach ($mtg as $tag => $name) {
                        echo "<option " . $tag . " value='" . $tag . "'>(" . $type[substr($tag, 0, 1)] . ') ' . $name . "</option>" . PHP_EOL;
                    }
                    ?>
                </select>
                <?php echo __('contacts wont receive mail if tag is already set' , 'edcl'); ?>
            </td>
        </tr>     <tr>
        </tr>

    </table>
    <br>
    <input type="submit" name="mail" id="stat" class="button button-primary"
           value="<?php echo __('send mail to selection', 'edcl'); ?>">
    <br>
    <?php
    if ($arrcu ['settings']['test']) {
        echo '<br><span style="color:red">' . __('Test mode is switched on', 'edcl') . '</span>';
    }
    ?>

    </div>
    <div>
      <br><br>
        <input type="submit" name="dlum" id="stat" class="button button-primary"
               value="<?php echo __('delete user mail', 'edcl'); ?>">
      <br><br>
        <input type="submit" name="dlsl" id="stat" class="button button-primary"
               value="<?php echo __('delete selection', 'edcl'); ?>">


    </div>
</form>