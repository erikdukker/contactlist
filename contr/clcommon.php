<?php
/**
 * Created by PhpStorm.
 * User: erikd
 * Date: 30-4-2016
 * Time: 21:41
 */
class clcommon
{
    public $action = EDCL_URL . '/contr/uppages.php';
    public $from = '';

    public function getcur() {
        global $wpdb;
        $cl = $wpdb->prefix . 'contactlist';
        $back = array ();
        $rwcu = $wpdb->get_row("select * from " . $cl . " where tp = 'cu'", ARRAY_A);
        if ($rwcu) {
         //   var_dump($rwcu);
            $back = json_decode($rwcu['tx'], TRUE);
            if ($back == NULL) die('template not correct JSON');
         //   var_dump($back);
            $back['id'] = $rwcu['id'];
            if (!empty($back['settings']['description']) ) {
                $back['setok'] = true;
            }
        }
        return ($back);
    }

    public function instvalct()
    {
        $per['60'] = __('1 min', 'edcl');
        $per['180'] = __('5 min', 'edcl');
        $per['25920'] = __('3 days', 'edcl');
        $per['60480'] = __('week', 'edcl');
        $per['120960'] = __('2 weeks', 'edcl');
        $per['259200'] = __('1 month', 'edcl');
        $per['527040'] = __('2 months', 'edcl');
        $per['794880'] = __('3 months', 'edcl');
        $per['1572480'] = __('half year', 'edcl');
        $per['3153600'] = __('year', 'edcl');
        $per['6307200'] = __('2 years', 'edcl');
        $per['63072000'] = __('never', 'edcl');

        $type['txv'] = __('text variable length', 'edcl');
        $type['txf'] = __('text fixed length', 'edcl');
        $type['eml'] = __('email', 'edcl');
        $type['url'] = __('url', 'edcl');
        $type['ch'] = __('checkbox', 'edcl');

        $vis['admin'] = __('admin', 'edcl');
        $vis['private'] = __('admin, contact', 'edcl');
        $vis['all'] = __('all contacts', 'edcl');

        $sm['smcf'] = __('confirm register', 'edcl');
        $sm['smch'] = __('invitation to check', 'edcl');

        $um['um1c'] = __('1 column', 'edcl');
        $um['um2c'] = __('2 columns', 'edcl');

        $back = array('per' => $per, 'type' => $type, 'vis' => $vis, 'sm' => $sm, 'um' => $um );
        if (base64_decode(strrev(get_option('lc'))) < time()) { delete_option('lc'); }
        return $back;
    }

    public function instvalev()
    {
        $rep['8640'] = __('day', 'edcl');
        $rep['60480'] = __('week', 'edcl');
        $rep['120960'] = __('2 weeks', 'edcl');
        $rep['259200'] = __('1 month', 'edcl');
        $rep['527040'] = __('2 months', 'edcl');
        $rep['794880'] = __('3 months', 'edcl');
        $rep['1572480'] = __('half year', 'edcl');
        $rep['63072000'] = __('never', 'edcl');

        $dur['60'] = __('minutes', 'edcl');
        $dur['360'] = __('hours', 'edcl');
        $dur['8640'] = __('days', 'edcl');

        $back = array('rep' => $rep, 'dur' => $dur);
        if (base64_decode(strrev(get_option('lc'))) < time()) { delete_option('lc'); }
        return $back;
    }


    public function inststat()
    {
        $stdefct = array(
            'A20' => array ( 'des' => __( 'invite' , 'edcl' ),  'bttx' => __( 'no action' , 'edcl' ),  'btst' => 'A60'),
            'A30' => array ( 'des' => __( 'wait for check' , 'edcl' ),  'bttx' => __( 'remind' , 'edcl' ),  'btst' => 'A40'),
            'A40' => array ( 'des' => __( 'remind' , 'edcl' ),  'bttx' => __( 'no action' , 'edcl' ),  'btst' => 'A60'),
            'A50' => array ( 'des' => __( 'wait after remind' , 'edcl' ),  'bttx' => __( 'no action' , 'edcl' ),  'btst' => 'A60'),
            'A60' => array ( 'des' => __( 'no action' , 'edcl' ),  'bttx' => __( 'invite' , 'edcl' ),  'btst' => 'A20'),
            'A70' => array ( 'des' => __( 'checked' , 'edcl' ),  'bttx' => __( 'invite' , 'edcl' ),  'btst' => 'A20'),
            'C10' => array ( 'des' => __( 'registered' , 'edcl' ),  'bttx' => __( 'unsubscribe' , 'edcl' ),  'btst' => 'C20'),
            'C20' => array ( 'des' => __( 'registered confirmed' , 'edcl' ),  'bttx' => __( 'unsubscribe' , 'edcl' ),  'btst' => 'C20'),
            'C30' => array ( 'des' => __( 'registered without confirmation' , 'edcl' ),  'bttx' => __( 'unsubscribe' , 'edcl' ),  'btst' => 'C20'),
            'C90' => array ( 'des' => __( 'unsubscribed' , 'edcl' ),  'bttx' => __( 'subscribe' , 'edcl' ),  'btst' => 'C10'),
            'Z80' => array ( 'des' => __( 'no respons' , 'edcl' ),  'bttx' => __( 'invite' , 'edcl' ),  'btst' => 'A20'),
            'Z90' => array ( 'des' => __( 'maintained by admin' , 'edcl' ),  'bttx' => __( 'invite' , 'edcl' ),  'btst' => 'A20'),
            'Z99' => array ( 'des' => __( 'trashed' , 'edcl' ),  'bttx' => __( 'invite' , 'edcl' ),  'btst' => 'A20'),
        );
        return $stdefct;
    }

    public function val($var) 		{ echo "<script> alert('voor>".$var."<na'); </script>"; }

    public function wl($var) 		{ error_log(var_dump($var)); }

    public function prephtm($htmky)
    {
        global $wpdb;
        $cl = $wpdb->prefix . 'contactlist';
        $html = $wpdb->get_row("select * from " . $cl . " where ky = '".$htmky."' and tp = 'sm'", ARRAY_A);
        if (!$html) {
            $html = $wpdb->get_row("select * from " . $cl . " where id = '".$htmky."'", ARRAY_A);
            if (!$html) {
                die(__( 'mail not maintained', 'edcl' ));
            }
        }
        $htmtmpl = '<html><head></head><body bgcolor= "#ccccc" style="margin:0;padding:0;width:100%;">';
        $htmtmpl .= '<table width="100%" border="0" align="center" padding="0" cellspacing="0" cellspacing="0" style="max-width: 800px;"><tr> ';
        $htmtmpl .= '<td width="25"></td><td>';
        $htmtmpl .=  $html['tx'];
        $htmtmpl .= '</td><td width="25"></td>';
        $htmtmpl .=  '</tr></table></body></html>';
        $htmtmpl = stripcslashes($htmtmpl);
        $htmtmpl = str_replace('contenteditable="true" class="mrk cke_editable cke_editable_inline cke_contents_ltr cke_show_borders"', '', $htmtmpl);
        $htmtmpl = str_replace('spellcheck="false"', '', $htmtmpl);
        $htmtmpl = str_replace('role="textbox"', '', $htmtmpl);
        return $htmtmpl;
    }
    public function getstyle()
    {
        global $wpdb;
        $cl = $wpdb->prefix . 'contactlist';
        $css = $wpdb->get_row("select * from " . $cl . " where tp = 'ws'", ARRAY_A);
        $csstx = '<style>';
        $csstx .= $css['tx'];
        $csstx .= '</style>';
        return $csstx;
    }

}


?>