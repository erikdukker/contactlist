<?php

/**
 * Created by PhpStorm.
 * User: erikd
 * Date: 6-5-2016
 * Time: 10:01
 */
class clsetting extends clcommon
{
public function license( ) {
    ?>
    <form action=<?php echo $this->action; ?> method='post'>
        <h2><?php echo __( 'License', 'edcl' ); ?></h2>

        <table>
            <tr>
                <td><?php echo __( 'Your license', 'edcl' ); ?></td>
                <td><input type='text' name='lc' size=40 required >
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="setlc" class="button button-primary" value="<?php echo __( 'check', 'edcl' ); ?>">
        </p>
     </form>
    <?php
}
public function contactlist( ) {
    global $wpdb;
    $cl = $wpdb->prefix . 'contactlist';
    $tpls = $wpdb->get_results("select * from " . $cl . " where tp = 'lt'",ARRAY_A);
    $rwcu  = $this->getcur();
    ?>
    <form action=<?php echo $this->action; ?> method='post'>

        <h2><?php echo __( 'Contactlist', 'edcl' ); ?></h2>

        <?php
        if (!isset($rwcu['id'])) {
            ?>
            <h4><?php echo __( 'Create contactlist', 'edcl' ); ?></h4>

            <?php echo __( 'Choose the template that fit\'s best', 'edcl' ); ?>
            <select name = "id">
                <?php
                foreach ($tpls as $tpl) {
                    echo "<option value='" . $tpl['id'] . "'>" . $tpl['nm'] . "</option>" . PHP_EOL;
                }
                ?>
            </select>
            <p class="submit">
                <input type="submit" name="initcl" class="button button-primary" value="<?php echo __( 'save', 'edcl' ); ?>">
            </p>
            <?php
        } else {
            $instvalct = $this->instvalct();
            $per = $instvalct['per'];
            $set = $rwcu['settings'];
            if ($set['test']) {$test = "checked";} else {$test = "";}
            if ($set['open']) {$open = "checked";} else {$open = "";}
           ?>
            <input type='hidden' name='id' value="<?php echo $rwcu['id']; ?>">
            <h4><?php echo __( 'Contactlist settings', 'edcl' ); ?></h4>

            <table>
                <tr>
                    <td><?php echo __( 'Description', 'edcl' ); ?></td>
                    <td><input type='text' pattern='[a-zA-Z0-9.,:;/ ]{1,40}' name='description' size=40 required
                                <?php echo " value= '".$set['description']."' title='".__( 'max 40 positions', 'edcl' )."'>" ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo __( 'Reply to', 'edcl' ); ?></td>
                    <td><input type='email' name='reply' size=25 required
                        <?php echo " value= '".$set['reply']."' title='".__( 'email address', 'edcl' )."'>" ?>
                    </td>

                    </td>
                </tr>
                <tr>
                    <td><?php echo __( 'Email test', 'edcl' ); ?></td>
                    <td><input type='checkbox'  name='test' <?php echo $test; ?> >
                        <?php echo __( 'if set all emails are sent to reply', 'edcl' ) ?>
                    </td>
                </tr>
                <tr>
                    <td valign="top"><?php echo __( 'Permalink [contactform]', 'edcl' ); ?></td>
                    <td><?php echo __( 'Insert the shortcode <b>[contactform name = "volledig"]</b> in a page. Fill here the permalink (url)
                        of the page', 'edcl' ); ?><br>
                        <input type='text' name='perma'
                         value='<?php echo $set['perma']; ?>' size="60" required >
                    </td>
                </tr>
                <tr>
                    <td valign="top"><?php echo __( 'Url of logo', 'edcl' ); ?></td>
                    <td><?php echo __( 'Get this url in the media library', 'edcl' ); ?><br>
                        <input type='text' name='logo'
                         value='<?php echo $set['logo']; ?>' size="60"  >
                    </td>
                </tr>
                <tr>
                    <td><?php echo __( 'Check every', 'edcl' ); ?></td>
                    <td>
                        <select name = "check">
                            <?php
                            foreach ($per as $key => $oms) {
                                if ($key == $set['check']) { $sel = 'selected';} else {$sel = '';}
                                echo "<option ".$sel."  value='" . $key . "'>" . $oms . "</option>" . PHP_EOL;
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?php echo __( 'Remind after', 'edcl' ); ?></td>
                    <td>
                        <select name = "remind">
                            <?php
                            foreach ($per as $key => $oms) {
                                if ($key == $set['remind']) { $sel = 'selected';} else {$sel = '';}
                                echo "<option ".$sel." value='" . $key . "'>" . $oms . "</option>" . PHP_EOL;
                            }
                            ?>
                        </select></td>
                </tr>
                <tr>
                    <td><?php echo __( 'Max reminders', 'edcl' ); ?></td>
                    <td><input type="number" name="repeat" min="0" max="5" value="<?php echo $set['repeat']; ?>" required></td>
                </tr>
                <tr>
                    <td><?php echo __( 'Contactlist visible for contacts', 'edcl' ); ?></td>
                    <td><input type="checkbox" name="open" <?php echo $open; ?>></td>
                </tr>
                <tr>
                    <td><?php echo __( 'Confirm deletion', 'edcl' ); ?></td>
                    <td><input type="checkbox" name="confirm">
                        <b><?php echo __( 'all data will be lost!', 'edcl' ); ?></b>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="setcl" class="button button-primary" value="<?php echo __( 'save', 'edcl' ); ?>">
                <input type="submit" name="delcl" class="button button-primary"
                       value="<?php echo __( 'delete contactlist', 'edcl' ); ?>">
            </p>
            <?php
        }
        ?>
    </form>
    <?php
}
function fields(  ) {
    $rwcu  = $this->getcur();
    //var_dump($cur['setok']);
    if (!isset($rwcu['setok'])) {
        ?>
        <h2><?php echo __( 'Please maintain settings first', 'edcl' ); ?></h2>
        <?php
    } else {
        $instvalct = $this->instvalct();
        $per = $instvalct['per'];
        $vis = $instvalct['vis'];
        $type = $instvalct['type'];
        $fields = $rwcu['fields'];
        ?>
        <form action=<?php echo $this->action; ?> method='post'>

            <h2><?php echo __( 'Field list', 'edcl' ); ?></h2>

            <input type='hidden' name='id' value="<?php echo $rwcu['id']; ?>">
            <table class="table">
                <tr>
                    <td><b><?php echo __( 'short', 'edcl' ); ?></b></td>
                    <td><b><?php echo __( 'name', 'edcl' ); ?></b></td>
                    <td><b><?php echo __( 'example', 'edcl' ); ?></b></td>
                    <td><b><?php echo __( 'default', 'edcl' ); ?></b></td>
                    <td><b><?php echo __( 'type', 'edcl' ); ?></b></td>
                    <td><b><?php echo __( 'length', 'edcl' ); ?></b></td>
                    <td><b><?php echo __( 'visible by', 'edcl' ); ?></b></td>
                    <td><b><?php echo __( 'rq', 'edcl' ); ?></b></td>
                    <td><b><?php echo __( 'order', 'edcl' ); ?></b></td>
                </tr>
                <?php
                $i = 0;
                foreach ($fields as $field) {
                    if ($field['required']) {
                        $required = " checked ";
                    } else {
                        $required = " ";
                    }
                    if ($field['use'] == "sys") {
                        $readonly = "readonly";
                    } else {
                        $readonly = "";
                    }
                    foreach ($field as $key => $val) {
                        if ($field[$key] == 'empty') {
                            $field[$key] = "";
                        }
                    }
                    ?>
                    <tr>
                        <td><input type='text' pattern='[A-Z0-9]{1,4}' title='max 4 A-Z or 0-9'
                                   name='short|<?php echo $i . "' " . $readonly; ?> size=4 required value='<?php echo $field['short']; ?>'>
                            <input type='hidden' name='use|<?php echo $i ?>' value='<?php echo $field['use']; ?>'></td>
                        <td><input type='text' pattern='[a-zA-Z0-9.,;/()+ ]{1,40}' title='max 40 '
                                   name='name|<?php echo $i ?>' size=22 required
                                   value='<?php echo $field['name']; ?>'></td>
                        <td><input type='text' pattern='[a-zA-Z0-9.,;/+ ]{1,40}' title='max 40'
                                   name='place|<?php echo $i ?>' size=15
                                   value='<?php echo $field['place']; ?> ' ></td>
                        <td><input type='text' pattern='[a-zA-Z0-9.,;/+ ]{1,40}' title='max 40'
                                   name='default|<?php echo $i ?>' size=15
                                   value='<?php echo $field['default']; ?> ' ></td>
                        <td>
                            <?php
                            //var_dump($field);
                            if ($field['use'] != "sys") {
                                ?>
                                <select name='type|<?php echo $i ?>'>
                                    <?php
                                    foreach ($type as $key => $oms) {
                                        if ($key == $field['type']) {
                                            $sel = 'selected';
                                        } else {
                                            $sel = '';
                                        }
                                        echo "<option " . $sel . "  value='" . $key . "'>" . $oms . "</option>" . PHP_EOL;
                                    }
                                    ?>
                                </select>
                                <?php
                            } else {
                                ?>
                                <input type="hidden" name='type|<?php echo $i ?>' value="<?php echo $field['type']; ?>"  >
                                <input type="text"  value="<?php echo $type[$field['type']]; ?>"  size="17" readonly >
                                <?php
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if (substr($field['type'], 0, 2) == 'tx') {
                                ?>
                                <input type='text' pattern='[0-9]{1,2}' title='lengte 0 tot 99'
                                       name='length|<?php echo $i ?>' size=2 required
                                       value="<?php echo $field['length'].'" '.$readonly; ?>>
                                <?php
                           } else {
                                ?>
                                <input type="hidden" name='length|<?php echo $i ?>' value="<?php echo $field['length']; ?>"
                                <?php
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($field['use'] != "sys") {
                                ?>
                                <select name='visible|<?php echo $i  ?>'>
                                    <?php
                                    foreach ($vis as $key => $oms) {
                                        if ($key == $field['visible']) {
                                            $sel = 'selected';
                                        } else {
                                            $sel = '';
                                        }
                                        echo "<option " . $sel . "  value='" . $key . "'>" . $oms . "</option>" . PHP_EOL;
                                    }
                                    ?>
                                </select>
                                <?php
                            } else {
                                ?>
                                <input type="hidden" name='visible|<?php echo $i ?>' value="<?php echo $field['visible']; ?> "  >
                                <input type="text"  value="<?php echo $vis[$field['visible']]; ?> " readonly size=13 >
                                <?php
                            }
                            ?>
                        </td>
                        <td><input type="checkbox" name='required|<?php echo $i ?>' <?php echo $required . $readonly ?> >
                        <td><input type="number" name='order|<?php echo $i ?>' min="0" max="<?php echo count($fields)+1; ?>"
                                   value="<?php echo $i + 1; ?>" required></td>
                    </tr>
                    <?php
                    $i++;
                }
                ?>
            </table>
            <p class="submit">
                <input type="submit" name="fld" id="fld" class="button button-primary" value="<?php echo __( 'save', 'edcl' ); ?>">
                <input type="submit" name="addfld" class="button button-primary" value="<?php echo __( 'add field', 'edcl' ); ?>">
            </p>
        </form>
        <?php
        }
    }

    public function tags()
    {
        global $wpdb;
        $cl = $wpdb->prefix . 'contactlist';
        $rwct = $wpdb->get_row("select * from " . $cl . " where tp = 'se' ", ARRAY_A); //contact
        $arr = json_decode($rwct['tx'], TRUE);
        $tgs = $arr['tgs'];
        $type = $arr['tgtp'];
        ?>
        <form action=<?php echo $this->action; ?> method='post'>
            <h2><?php echo __( 'Maintain tags', 'edcl' ); ?></h2>

            <?php echo __( 'Remove selected tags ', 'edcl' ); ?> <select name="tgs[]" multiple>
                <?php
                foreach ($tgs as $stat => $name) {
                   echo "<option value='" . $stat . "'>(" .$type[substr($stat,0,1)].') '.$name . "</option>" . PHP_EOL;
                }
                ?>
            </select><br>
            <p class="submit">
                <input type="submit" name="deltag" id="stat" class="button button-primary" value="<?php echo __( 'delete selected tags', 'edcl' ); ?>">
            </p><br><br>

            <?php echo __( 'Add tag', 'edcl' ); ?> <select name="tgtp" style="vertical-align: bottom">
                <?php
                foreach ($type as $stat => $atts) {
                    echo "<option value='" . $stat . "'>" . $atts . "</option>" . PHP_EOL;
                }
                ?>
            </select>
            <input type='text' pattern='[a-zA-Z0-9 ]{2,15}' title='min 2 max 15 positions'
                   name='name' size=9 ?><br>
            <p class="submit">
                <input type="submit" name="addtag" id="stat" class="button button-primary" value="<?php echo __( 'add tag', 'edcl' ); ?>">
            </p><br><br>

        </form>
        <?php
    }
  public function forms()
    {
        global $wpdb;
        $cl = $wpdb->prefix . 'contactlist';
        $rwct = $wpdb->get_row("select * from " . $cl . " where tp = 'se' ", ARRAY_A); //contact
        $arr = json_decode($rwct['tx'], TRUE);
        $tgs = $arr['tgs'];
        $type = $arr['tgtp'];
        $forms = $arr['frms'];

        ?>
        <form action=<?php echo $this->action; ?> method='post'>
            <h2><?php echo __( 'Maintain forms', 'edcl' ); ?></h2>

            <?php
            $i = 0;
            foreach ($forms as $form) { ?>
            <table>
                <tr>
                    <td><b><?php echo __('setting', 'edcl'); ?></b></td>
                    <td><b><?php echo __('value', 'edcl'); ?></b></td>
                </tr>
                <tr>
                    <td><?php echo __('Name', 'edcl'); ?></td>
                    <td><input type='text' pattern='[a-zA-Z0-9.,:;/ ]{1,15}' name='name|<?php echo $i; ?>' size=15 required
                        <?php echo " value= '" . $form['name'] . "' title='" . __('max 40 positions', 'edcl') . "'>" ?>
                    </td>
                </tr>
                <?php if (isset($form['instr'])) { ?>
                    <tr>
                        <td><?php echo __('Instruction', 'edcl'); ?></td>
                        <td><input type='text' pattern='[a-zA-Z0-9.,:;/ ]{1,120}' name='instr|<?php echo $i; ?>' size=120
                            <?php echo " value= '" . $form['instr'] . "' title='" . __('max 120 positions', 'edcl') . "'>" ?>
                        </td>
                    </tr>
                <?php }
                if (isset($form['fields'])) { ?>
                    <tr>
                        <td><?php echo __('Velden', 'edcl'); ?></td>
                        <td><input type='text' pattern='[a-zA-Z0-9.,:;/ ]{1,60}' name='fields|<?php echo $i; ?>' size=60
                            <?php echo " value= '" . $form['fields'] . "' title='" . __('max 60 positions', 'edcl') . "'>";
                            echo __('when emptie: all fields', 'edcl'); ?>
                        </td>
                    </tr>
                <?php }
                if (isset($form['parm'])) { ?>
                    <tr>
                        <td><?php echo __('Parm', 'edcl'); ?></td>
                        <td><input type='text' pattern='[a-zA-Z0-9.,:;/ ]{1,60}' name='parm|<?php echo $i; ?>' size=60
                            <?php echo " value= '" . $form['parm'] . "' title='" . __('max 60 positions', 'edcl') . "'>";
                                  echo __('options: open,hidden,conf', 'edcl'); ?>

                        </td>
                    </tr>
                <?php }
                if (isset($form['but1'])) { ?>
                    <tr>
                        <td><?php echo __('Button open', 'edcl'); ?></td>
                        <td><input type='text' pattern='[a-zA-Z0-9.,:;/ ]{1,60}' name='but1|<?php echo $i; ?>' size=60
                            <?php echo " value= '" . $form['but1'] . "' title='" . __('max 60 positions', 'edcl') . "'>" ?>
                        </td>
                    </tr>
                <?php }
                if (isset($form['but2'])) { ?>
                    <tr>
                        <td><?php echo __('Button end', 'edcl'); ?></td>
                        <td><input type='text' pattern='[a-zA-Z0-9.,:;/ ]{1,60}' name='but2|<?php echo $i; ?>' size=60 required
                            <?php echo " value= '" . $form['but2'] . "' title='" . __('max 60 positions', 'edcl') . "'>" ?>
                        </td>
                    </tr>
                <?php }
                if (isset($form['ins'])) { ?>
                    <tr>
                        <td><?php echo __('Insert confirmed', 'edcl'); ?></td>
                        <td><input type='text' pattern='[a-zA-Z0-9.,:;/() ]{1,120}' name='ins|<?php echo $i; ?>' size=120 required
                            <?php echo " value= '" . $form['ins'] . "' title='" . __('max 120 positions', 'edcl') . "'>" ?>
                        </td>
                    </tr>
                <?php }
                if (isset($form['conf'])) { ?>
                    <tr>
                        <td><?php echo __('Confirmation by email', 'edcl'); ?></td>
                        <td><input type='text' pattern='[a-zA-Z0-9.,:;/() ]{1,120}' name='conf|<?php echo $i; ?>' size=120 required
                            <?php echo " value= '" . $form['conf'] . "' title='" . __('max 120 positions', 'edcl') . "'>" ?>
                        </td>
                    </tr>
                <?php }
                if (isset($form['unsub'])) { ?>
                    <tr>
                        <td><?php echo __('Unsubscribbed', 'edcl'); ?></td>
                        <td><input type='text' pattern='[a-zA-Z0-9.,:;/() ]{1,120}' name='unsub|<?php echo $i; ?>' size=120 required
                            <?php echo " value= '" . $form['unsub'] . "' title='" . __('max 120 positions', 'edcl') . "'>" ?>
                        </td>
                    </tr>
                <?php }
                $i++;
                echo "</table><br><br>";
            }
            ?>
            <p class="submit">
                <input type="submit" name="upfrm" id="stat" class="button button-primary" value="<?php echo __( 'update', 'edcl' ); ?>">
            </p>
        </form>
        <?php
    }

    public function events( ) {
        global $wpdb;
        $cl = $wpdb->prefix . 'contactlist';
        $tpls = $wpdb->get_results("select * from " . $cl . " where tp = 'lt'",ARRAY_A);
        $rwcu  = $this->getcur();
        ?>
        <form action=<?php echo $this->action; ?> method='post'>

            <h2><?php echo __( 'Contactlist', 'edcl' ); ?></h2>

            <?php
            $instvalct = $this->instvalct();
            $per = $instvalct['per'];
            $set = $rwcu['settings'];
            ?>
            <input type='hidden' name='id' value="<?php echo $rwcu['id']; ?>">
            <h4><?php echo __( 'Event settings', 'edcl' ); ?></h4>
            THIS FUNCTIONALITY ISN'T AVAILABLE YET
<?PHP if ("a"== "b") { ?>
    <table>
        <tr>
            <td><b><?php echo __('setting', 'edcl'); ?></b></td>
            <td><b><?php echo __('value', 'edcl'); ?></b></td>
        </tr>
        <tr>
            <td><?php echo __('Expires after', 'edcl'); ?></td>
            <td><input type="number" name="quantity" min="1" max="24" required
                <?php echo " value= '" . $evt['expire'] . "' >" ?>
            </td>
        </tr>
        <tr>
            <td><?php echo __('Organizer', 'edcl'); ?></td>
            <td><input type='email' name='reply' size=25 required
                <?php echo " value= '" . $set['Organizer'] . "' '>" ?>
            </td>
        </tr>
    </table>
    <p class="submit">
        <input type="submit" name="setcl" class="button button-primary" value="<?php echo __('save', 'edcl'); ?>">
    </p>
    <?php
}
            ?>
        </form>
        <?php
    }
    public function export()
    {
        ?>
        <form action=<?php echo $this->action; ?> method='post'>
            <h2>Export </h2>
            <?php echo __( 'download file', 'edcl' ); ?>
            <input type='text' name='filename' size=26>
            <br><br>
            <input type="submit" name="exct" class="button button-primary" value="<?php echo __( 'export contacts', 'edcl' ); ?>">
            <input type="submit" name="exst" class="button button-primary" value="<?php echo __( 'export settings', 'edcl' ); ?>">
        </form>
        <?php
    }
    public function import()
    {
        ?>
        <form action=<?php echo $this->action; ?> method='post'  enctype='multipart/form-data'>
            <h2>Import </h2>
            <?php echo __( 'upload file', 'edcl' ); ?>
            <input name="uploadfile" type="file" /><br />
            <br>
            <br>
            <input type="submit" name="imct" class="button button-primary" value="<?php echo __( 'import contacts', 'edcl' ); ?>">
            <input type="submit" name="imst" class="button button-primary" value="<?php echo __( 'import settings', 'edcl' ); ?>">
        </form>
        <br>
        <?php
       if (isset( $_SESSION['log'])) {
           echo $_SESSION['log'];
           unset ($_SESSION['log']);
       }
}
}
