<?php

/**
 * Created by PhpStorm.
 * User: erikd
 * Date: 6-5-2016
 * Time: 10:01
 */
class clmail extends clcommon
{
    function mainthtml($mode) {
        global $wpdb;
        $cl = $wpdb->prefix . 'contactlist';
        $url = $_SERVER["REQUEST_URI"];
        $handled = false;
        if (isset($_GET["s"])) {
            $action = str_getcsv(base64_decode($_GET["s"]), '|');
       //   var_dump($action);
            switch ($action[0]) {
                case 'editsm':
                    $handled = true;
                    $this->edithtml($action[1],$action[0]);
                break;
                case 'createum':
                    $handled = true;
                    $this->edithtml($action[1],$action[0]);
                break;
                case 'editum':
                    $handled = true;
                    $this->edithtml('',$action[0],$action[1]);
                break;
            }
        }
        if (!$handled) {
            if ($mode == 'st') {
                ?>
                <h2><?php echo __('Standard mails', 'edcl'); ?></h2>
                <form action=<?php echo $this->action; ?> method='post'>
                    <table>
                        <?php
                        $instvalct = $this->instvalct();
                        $sm = $instvalct['sm'];

                        foreach ($sm as $mail => $des) {
                            echo "<tr><td>" . $des . "</td><td>";
                            $html = $wpdb->get_row("select * from " . $cl . " where tp = 'sm' and ky = '" . $mail . "'", ARRAY_A);
                            //var_dump($mail);
                            if ($html) {
                                echo __('maintained', 'edcl')."</td><td>";
                            } else {
                                echo __('not maintained', 'edcl')."</td><td>";
                            }
                            $link = $url . '&s=' . base64_encode('editsm|' . $mail);
                            echo "<a href=" . $link . ">".__('edit', 'edcl')."</a></td></tr>";
                        }
                        ?>
                    </table>
                </form><br>
                <?php
            } elseif ($mode == 'u') {
                ?>
                <h2><?php echo __('User mails', 'edcl'); ?></h2>

                <form action=<?php echo $this->action; ?> method='post'>
                    <?php echo __('User mails template:', 'edcl'); ?>
                    <table>
                        <?php
                        $instvalct = $this->instvalct();
                        $um = $instvalct['um'];
                        foreach ($um  as $new => $des) {
                            echo "<tr><td>" . $des . "</td><td>";
                            $link = $url . '&s=' . base64_encode('createum|' . $new);
                            echo "<a href=" . $link . ">".__('create mail', 'edcl')."</a></td></tr>";
                        }
                        ?>
                    </table>
                </form><br><br>
                <form action=<?php echo $this->action; ?> method='post'>
                    <?php echo __('User mails:', 'edcl'); ?>
                    <table>
                        <?php
                        $news = $wpdb->get_results("select * from " . $cl . " where tp = 'um' ", ARRAY_A);
                        if ($news) {
                            foreach ($news as $new) {
                               // var_dump($new);
                               // echo '<br>';
                                echo "<tr><td>" . $new['nm'] . "</td><td>";
                                $link = $url . '&s=' . base64_encode('editum|' . $new['id']);
                                echo "<a href=" . $link . ">".__('edit', 'edcl')."</a></td></tr>";
                            }
                        }
                        ?>
                    </table>
                </form>
            <?php
            }
        }
    }
    function edithtml($ky = 'smcf',$tp='sm', $id='' ) {
        global $wpdb;
      //  die('key '.$ky);
       // die('type '.$tp);
        $cl = $wpdb->prefix . 'contactlist';
        if ($tp == 'editsm') {
            $deletable = true;
            $html = $wpdb->get_row("select * from " . $cl . " where tp = 'sm' and ky = '" . $ky . "'", ARRAY_A);
            if (empty($html)) {
                $deletable = false;
                //die('hij komt niet');
                $html = $wpdb->get_row("select * from " . $cl . " where tp = 'st' and ky = '" . $ky . "'", ARRAY_A);
             //   var_dump($html);
            }
        }
        if ($tp == 'createum') {
            $html = $wpdb->get_row("select * from " . $cl . " where tp = 'ut' and ky = '" . $ky . "'", ARRAY_A);
        }
        if ($tp == 'editum') {
                $html = $wpdb->get_row("select * from " . $cl . " where id = '" . $id . "'", ARRAY_A);
        }
        //var_dump($html);
        ?>
        <style>
            .mrk {background-color: #E4E4FF}
        </style>
        <form action=<?php echo $this->action; ?> method='post'>

            <h2><?php echo __( 'Edit standard / user mail:', 'edcl' ); ?></h2>

            <?php
            if ($tp != 'editmail') {
                echo __( 'Name :', 'edcl' );
                ?>
                <input type='text' name='nm'
                       value='<?php echo $html['nm']; ?>' size="60" required><br><br>
                <?php
            }
            ?>
            <input id='html' name='html' type='hidden'  >
            <input id='ky' name='ky' type='hidden' value="<?php echo $ky ?>"  >
            <input id='tp' name='tp' type='hidden' value="<?php echo $tp ?>"  >
            <div id="ahtml" style="border: 2px solid #aaa; max-width: 780px;">
                <?php echo stripcslashes($html['tx']); ?>
            </div>
            <input id='id' name='id' type='hidden' value='<?php echo $html['id']; ?>'><br><br>
            <input type="submit" name="edhtml" class="button button-primary" value="<?php echo __( 'save', 'edcl' ); ?>"
                   onclick="document.getElementById('html'.toString()).value = document.getElementById('ahtml'.toString()).innerHTML;" >
            <?php
            if ($tp == 'editsm' and $deletable) {
                ?>
                <input type="submit" name="delhtml" class="button button-primary" value="<?php echo __( 'reset', 'edcl' ).'">';
            }
            if ($tp == 'editnews') {
                ?>
                <input type="submit" name="delhtml" class="button button-primary" value="<?php echo __( 'delete', 'edcl' ).'">';
            }
            ?>
            <br><br>
            <?php echo __( '
            click on marked text to edit. Variables are enclosed by %. You can use every field (short) as a variable.<br>
            Test email first by sending to test email address
            ', 'edcl' ); ?>
        </form>
        <script src="http://cdn.ckeditor.com/4.5.7/standard-all/ckeditor.js"></script>
        <?php
    }
}