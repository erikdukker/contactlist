<?php

/**
 * Created by PhpStorm.
 * User: erikd
 * Date: 6-5-2016
 * Time: 10:01
 */
class cltagging extends clcommon
{
    public function tagging()
    { //tagging
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
                     case 'tag':
                        $this->tag($action[1]);
                        break;
                }
            } else {
                $this->tagdash();
            }
        }
    }

    public function tagdash()
    { //tagging
        global $wpdb;
        $cl = $wpdb->prefix . 'contactlist';
        $stdefct = $this->inststat();
        $rwcu = $this->getcur();
        $url = $_SERVER["REQUEST_URI"];
        $rwct = $wpdb->get_row("select * from " . $cl . " where tp = 'se' ", ARRAY_A); //contact
        $arr = json_decode($rwct['tx'], TRUE);
        $tags = $arr['tgs'];
        $type = $arr['tgtp'];
        $rsct = $wpdb->get_results("select * from " . $cl . " where tp = 'ct' order by st", ARRAY_A);
        ?>
        <h2><?php echo __( 'Tagging', 'edcl' ); ?></h2>

        <form action=<?php echo $this->action; ?> method='post'>
        <select name="tag">
            <?php
            echo "<option  value='none'>".__( 'Choose tag', 'edcl' )."</option>" . PHP_EOL;
            foreach ($tags as $tag => $name) {
                echo "<option  value='" . $tag . "'>(" .$type[substr($tag,0,1)].') '.$name . "</option>" . PHP_EOL;
            }
            ?>
        </select>
        <input type="submit" name="tagplus"  class="button button-primary" value="<?php echo __( 'add tag', 'edcl' ); ?>">
        <input type="submit" name="tagmin"  class="button button-primary" value="<?php echo __( 'delete tag', 'edcl' ); ?>">

        <table><tr><td></td><td></td><td><?php echo __( 'Name', 'edcl' );  ?></td>
        <td><?php echo __( 'email', 'edcl' );  ?></td>
        <td><?php echo __( 'tags', 'edcl' );  ?></td></tr>

            <?php
        foreach ($rsct as $rwct) {
            $arrc = json_decode($rwct['tx'], TRUE);
            $vals = $arrc['vals'];
           ?>
            <tr>
                <td><input type='checkbox' name='id<?php echo $rwct['id']; ?>' ></input></td>
                <td><a href="<?php echo $url . '&s=' . base64_encode('tag|' . $rwct['id']); ?> "><?php  echo __( 'edit', 'edcl' ); ?></a></td>
                <td><?php echo $rwct['nm'] ?></td>
                <td><?php echo $rwct['em'] ?></td>
                <td>
                <?php
                    $tagged = false;
                    $type_old = '1';
                    if (isset($vals['TAGS'])) {
                        $parts  = str_getcsv( $vals['TAGS'], ';' );
                        foreach ( $parts as $tag ) {
                            if ( $tag != '' and isset($tags[ $tag ]) ) {
                                if (substr( $tag, 0, 1 ) != $type_old ) {
                                    echo '(' . $type[ substr( $tag, 0, 1 ) ] . ') ';
                                    $type_old = substr( $tag, 0, 1 );
                                } else {
                                    echo ', ';
                                }
                                echo $tags[ $tag ] . ' ';
                                $tagged = true;
                            }
                        }
                    }
                    if (!$tagged) { echo 'no tags'; }
                ?>
                </td>
            </tr>
            <?php
        }
        ?>
        </table>
        </form>
        <?php
    }

    public function tag($id = "0")
    {
        global $wpdb;
        $cl = $wpdb->prefix . 'contactlist';
        $rwcu = $wpdb->get_row("select * from " . $cl . " where tp = 'cu'", ARRAY_A);
        if (!$rwcu) { die('a problem occured : no contact list'); }
        $arrcu = json_decode($rwcu['tx'], TRUE);
        $fields = $arrcu['fields'];
        $rwct = $wpdb->get_row("select * from " . $cl . " where tp = 'se' ", ARRAY_A); //rwctact
        $arr = json_decode($rwct['tx'], TRUE);
       // var_dump($arr);
        $tags = $arr['tgs'];
       // var_dump($tags);
       // echo '<br>';
       // var_dump($lst);
        $type = $arr['tgtp'];
        $rwct = $wpdb->get_row("select * from " . $cl . " where tp = 'ct' and id = '" . $id . "'", ARRAY_A); //contact
        $arr = json_decode($rwct['tx'], TRUE);
        $vals = $arr['vals'];
       // var_dump($vals);
        ?>
        <form action=<?php echo $this->action; ?> method='post'>
            <input type='hidden' name='id' value="<?php echo $rwct['id']; ?>">
            <h4><?php echo __( 'tag contact', 'edcl' ); ?></h4>

            <?php echo __( 'Contact :', 'edcl' ).' '.$vals['NM1'] . ' ' . $vals['EM1'] ?><br><br>

            <table>
                <tr><td>
                        <?php echo __( 'Tags', 'edcl' ); ?>
                   </td><td> </td><td>
                        <?php echo __( 'Info', 'edcl' ); ?>
                    </td>
                </tr>
                <tr><td>

                    <select name="tags[]" multiple style="height:400px">
                        <?php
                        echo "<option  value='none'>>" .__( 'none', 'edcl' ). "<</option>" . PHP_EOL;
                        foreach ($tags as $tag => $name) {
                            if (strpos(':'.$vals['TAGS'],$tag) > 0) { // add extra to avoid 0 when found
                                $sel = 'selected';
                            } else {
                                $sel = '';
                            }
                            echo "<option " . $sel ." value='" . $tag . "'>(" .$type[substr($tag,0,1)].') '.$name . "</option>" . PHP_EOL;
                        }
                        ?>
                    </select>
                </td><td></td><td valign="top">
                        <?php
                        $output = '';
                        $output .= '<table>' . PHP_EOL;
                        foreach ($fields as $field) {
                            if ($field['short'] != 'TAGS') {

                                $output .= '<tr><td>' . PHP_EOL;
                                if ($field['type'] != 'ch') {
                                    $value = " value = '" . $vals[$field['short']] . "' ";
                                } else {
                                    if ($vals[$field['short']] == 'true') {
                                        $checked = ' checked ';
                                    } else {
                                        $checked = ' ';
                                    }
                                }
                                switch ($field['type']) {
                                    case 'txv':
                                        $output .= $field['name'] . '</td><td>' . PHP_EOL;
                                        $output .= "<span class='edcl-wrap' >" . PHP_EOL;
                                        $output .= "<input type='text' pattern='[a-zA-Z0-9 .,;/-+]{1," . $field['length'] . "}'
                                                        title='max " . $field['length'] . " " . __('positions', 'edcl') .
                                            " class='edcl-field' size=" . $field['length'] . ' readonly ' . $value . " >" . PHP_EOL;
                                        break;
                                    case 'txf':
                                        $output .= $field['name'] . '</td><td>' . PHP_EOL;
                                        $output .= "<span class='edcl-wrap' >" . PHP_EOL;
                                        $output .= "<input type='text' pattern='[a-zA-Z0-9 .,;/-+]{" . $field['length'] . "}'
                                                        title=' " . $field['length'] . " " . __('positions', 'edcl') .
                                            " class='edcl-field' size=" . $field['length'] . ' readonly ' . $value . " >" . PHP_EOL;
                                        break;
                                    case 'eml':
                                        $output .= $field['name'] . '</td><td>';
                                        $output .= "<span class='edcl-wrap' >" . PHP_EOL;
                                        $output .= "<input type='email' size='40 class='edcl-field' " . " readonly " . $value . " >" . PHP_EOL;
                                        break;
                                    case 'url':
                                        $output .= $field['name'] . '</td><td>' . PHP_EOL;
                                        $output .= "<span class='edcl-wrap' >" . PHP_EOL;
                                        $output .= "<input type='text' pattern='[a-zA-Z0-9-.]+\.[a-zA-Z]{2,10}(/\S*)?$'
                                                           title='url' " .
                                            "class='edcl-field'  size=" . $field['length'] . ' readonly ' . $value . " >" . PHP_EOL;
                                        break;
                                    case 'ch':
                                        $output .= "<span class='edcl-wrap' >" . PHP_EOL;
                                        $output .= '<br>' . PHP_EOL;
                                        $output .= "<input type='checkbox' title='check' " . $checked . " readonly >" . PHP_EOL;
                                        $output .= ' </td><td> ' . $field['name'] . PHP_EOL;
                                        break;
                                }
                                $output .= '</td></tr>' . PHP_EOL;
                            }
                        }
                        $output .= '</table>' . PHP_EOL;
                        echo $output;
                        ?>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="settags" class="button button-primary" value="<?php echo __( 'save', 'edcl' ); ?>">
            </p>
        </form>
        <?php
    }
}
