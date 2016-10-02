<?php
/**
 * Created by PhpStorm.
 * User: erikd
 * Date: 30-4-2016
 * Time: 21:38
 */

/* the form update routine for forms supported bij menu class*/

require_once( 'functions.php' );
require_once("../../../../wp-admin/admin.php");

global $wpdb;
$cl = $wpdb->prefix . 'contactlist';
if(!isset($_SESSION)) { session_start(); }

if (isset($_POST['initcl'])) {
    $tpl = $wpdb->get_row("select * from " . $cl . " where id = '".$_POST['id']."'",ARRAY_A);   // current
    $wpdb->insert( $cl, array( 'ky' => 'current', 'nm' => 'current', 'tp' => 'cu', 'tx'  => $tpl['tx'] ) );
    $tpl = $wpdb->get_row("select * from " . $cl . " where tp = 'st'",ARRAY_A);                 // settings
    $wpdb->insert( $cl, array( 'ky' => '', 'nm' => 'current', 'tp' => 'se', 'tx'  => $tpl['tx']  ));
    $tpl = $wpdb->get_row("select * from " . $cl . " where tp = 'wt'",ARRAY_A);                 // css
    $wpdb->insert( $cl, array( 'ky' => '', 'nm' => 'current', 'tp' => 'ws', 'tx'  => $tpl['tx']  ));
}
if (isset($_POST['setcl'])) {
    $rwcu = $wpdb->get_row("select * from " . $cl . " where tp = 'cu'",ARRAY_A);
  //  var_dump($cur);
    $arr = json_decode($rwcu['tx'], TRUE);
	$arr['settings']['description'] = $_POST['description'];
    $arr['settings']['reply'] = $_POST['reply'];
    $arr['settings']['test'] = $_POST['test'];
    $arr['settings']['perma'] = $_POST['perma'];
    $arr['settings']['logo'] = $_POST['logo'];
    $arr['settings']['check'] = $_POST['check'];
    $arr['settings']['remind'] = $_POST['remind'];
    $arr['settings']['repeat'] = $_POST['repeat'];
    if ( isset($_POST['test'])) {
        $arr['settings']['test'] = true ;
    } else {
        $arr['settings']['test'] = false ;
    }
    if ( isset($_POST['open'])) {
        $arr['settings']['open'] = true ;
    } else {
        $arr['settings']['open'] = false ;
    }
	$tx = json_encode($arr);
    $wpdb->update( $cl, array( 'tx' => $tx ), array( 'id' => $_POST['id'] ), array( '%s' ), array( '%d' ) );
}

if (isset($_POST['delcl'])) {
    if (isset($_POST['confirm'])) {
          $wpdb->delete($cl, array('tp' => 'cu')); // the contat list
          $wpdb->delete($cl, array('tp' => 'sm')); // standard mails
          $wpdb->delete($cl, array('tp' => 'um')); // user mails
          $wpdb->delete($cl, array('tp' => 'se')); // settings
          $wpdb->delete($cl, array('tp' => 'ct')); // contacten
    }
}
//var_dump($_POST);
if (isset($_POST['fld'])) {
    $fieldtx ="";
    $i = 1;
    $rwcu = $wpdb->get_row("select * from " . $cl . " where tp = 'cu'",ARRAY_A);  // get old values: required for sys
    $arr = json_decode($rwcu['tx'], TRUE);

    foreach( $_POST as $key => $val) {
        $parts = str_getcsv($key,'|');
        if ($parts[0] == "short"){
            $field['short'] = trim($_POST['short|'.$parts[1] ]);
            $field['use'] = trim($_POST['use|'.$parts[1] ]);
            $field['name'] = trim($_POST['name|'.$parts[1] ]);
            $field['place'] = trim($_POST['place|'.$parts[1] ]);
            $field['default'] = trim($_POST['default|'.$parts[1] ]);
            $field['type'] = trim($_POST['type|'.$parts[1] ]);
            $field['length'] = trim($_POST['length|'.$parts[1] ]);
            $field['visible'] = trim($_POST['visible|'.$parts[1] ]);
            if ( isset( $_POST['required|'.$parts[1]])) {
                $field['required'] = true ;
            } else {
                $field['required'] = false ;
            }
            if ($field['use'] == 'sys'){
                foreach ($arr['fields'] as $fieldoud){
                    if ($fieldoud['short'] == $field['short']){
                        $field['required'] = $fieldoud['required'];
                    }
                }
            }
            $sortkey = trim($_POST['order|'.$parts[1]]);
            $sortkey = str_pad($sortkey, 3, '0', STR_PAD_LEFT).str_pad($i, 3, '0', STR_PAD_LEFT);
            $fieldsort[ $sortkey] = $field; // adding $i solves aving double keys
          //  var_dump($sortkey);

            $i++;
          }
    }
    ksort ($fieldsort);
    $fields = array();
    foreach( $fieldsort as $field) {
        array_push($fields,$field);
    }
    $rwcu = $wpdb->get_row("select * from " . $cl . " where tp = 'cu'",ARRAY_A);
    $arr = json_decode($rwcu['tx'], TRUE);
    $arr['fields'] = $fields;
    //var_dump($fields);;echo '<br>';;echo '<br>';
    $tx = json_encode($arr);
  //  echo $tx;
    $wpdb->update( $cl, array( 'tx' => $tx ), array( 'id' => $_POST['id'] ), array( '%s' ), array( '%d' ) );
}

if (isset($_POST['addfld'])) {
    $field['short'] = "";
    $field['use'] = "user";
    $field['name'] = "";
    $field['place'] = "";
    $field['default'] = "";
    $field['type'] = "txv";
    $field['length'] = "10";
    $field['visible'] = "all";
    $field['required'] = true ;
    $rwcu = $wpdb->get_row("select * from " . $cl . " where tp = 'cu'",ARRAY_A);
    $arr= json_decode($rwcu['tx'], TRUE);
    //  var_dump($arr);
    // echo "<br><br>";
    array_push($arr['fields'],$field);
    $tx = json_encode($arr);
    //var_dump($tx);
    $wpdb->update( $cl, array( 'tx' => $tx ), array( 'id' => $_POST['id'] ), array( '%s' ), array( '%d' ) );
}

if (isset($_POST['edhtml'])) {;
    $tx = trim(stripcslashes($_POST['html']));
    $tp = $_POST['tp']; // type
    $ky = $_POST['ky']; // type
   // tn($_POST);
    switch ($tp) {
        case 'editsm':
            $html = $wpdb->get_row("select * from " . $cl . " where tp = 'sm' and ky = '" . $ky . "'", ARRAY_A);
            if (empty($html)) {
                $sttm = time();
                $nm = $_POST['nm'];
                $wpdb->insert($cl, array('ky' => $ky, 'st' => '', 'sttm' => $sttm,  'em' => '', 'nm' => $nm, 'tp' => 'sm', 'tx' => $tx ));
             } else {
                $wpdb->update($cl, array('tx' => $tx ), array('id' => $_POST['id']), array('%s'), array('%d'));
            }
            break;
        case 'createum':
            $sttm = time();
            $nm = $_POST['nm'];
            $wpdb->insert($cl, array('ky' => 'nl', 'st' => '', 'sttm' => $sttm, 'em' => '', 'nm' => $nm, 'tp' => 'um', 'tx' => $tx ));
            $parts = str_getcsv( wp_get_referer(), '&');
            $goback = add_query_arg( 'settings-updated', 'true', $parts[0] );
            wp_redirect( $goback );
            exit;
        case 'editum':
           // var_dump($_POST);
            $nm = $_POST['nm'];
            $wpdb->update( $cl, array( 'tx' => $tx, 'nm' => $nm ), array( 'id' => $_POST['id'] ), array( '%s', '%s' ), array( '%d' ) );
            break;
    }
    $goback = add_query_arg( 'settings-updated', 'true', $parts[0] );
    wp_redirect( $goback );
    exit;
}
if (isset($_POST['delhtml'])) { //delete html
    $wpdb->delete($cl, array('id' => $_POST['id']),  array('%d'));
    $parts = str_getcsv( wp_get_referer(), '&');
    $goback = add_query_arg( 'settings-updated', 'true', $parts[0] );
    wp_redirect( $goback );
    exit;
}
if (isset($_POST['deltag'])) {
    $rwct = $wpdb->get_row("select * from " . $cl . " where tp = 'se' ", ARRAY_A); //contact
    $arr = json_decode($rwct['tx'], TRUE);
    $tgs = $arr['tgs'];
    $newtgs = array();
    $delkeys = $_POST['tgs'];
   // var_dump($delkeys);
    foreach ($tgs as $key => $val){
        if (!in_array($key,$delkeys)){
            $newtgs[$key] = $val;
        }
    }
    $arr['tgs'] = $newtgs;
    $tx = json_encode($arr);
    //  var_dump($tx);
     $wpdb->update( $cl, array( 'tx' => $tx ), array( 'id' => $rwct['id'] ), array( '%s' ), array( '%d' ) );
}
if (isset($_POST['addtag'])) {
    if ($_POST['name'] != '') {
        $rwct = $wpdb->get_row("select * from " . $cl . " where tp = 'se' ", ARRAY_A); //contact
        $arr = json_decode($rwct['tx'], TRUE);
        $tgs = $arr['tgs'];
        $ok = true;
        foreach($tgs as $key => $val) {
            if (substr($key,0,1) ==  $_POST['tgtp'] and $val ==  $_POST['name']) {
                $ok = false; // no doubles
            }
        }
        if ($ok) {
            $arr['tgcnt'] = $arr['tgcnt'] + 1;
            $key = $_POST['tgtp'] . $arr['tgcnt'];
            $tgs[$key] = $_POST['name'];
            ksort($tgs);
            $arr['tgs'] = $tgs;
            $tx = json_encode($arr);
            $wpdb->update($cl, array('tx' => $tx), array('id' => $rwct['id']), array('%s'), array('%d'));
        }
    }
}
if (isset($_POST['adupd'])) {
    $rwct = $wpdb->get_row("select * from " . $cl . " where id = '" . $_POST['id'] . "' and tp = 'ct'", ARRAY_A);
    $arr = json_decode($rwct['tx'], TRUE);
    $vals = $arr['vals'];
    // var_dump($vals);
    $tgs = '';
    foreach ($_POST['tags'] as $key ){
        $tags .= ';'.$key;
    }
    $vals['TAGS'] = $tags;
    $arr['vals'] = $vals;

    $tx = json_encode($arr);
      //var_dump($tx);
    $wpdb->update( $cl, array( 'tx' => $tx, 'st' => $_POST['status'] ), array( 'id' => $rwct['id'] ), array( '%s', '%s' ), array( '%d' ) );
    $parts = str_getcsv( wp_get_referer(), '&');
    $goback = add_query_arg( 'settings-updated', 'true', $parts[0] );
    wp_redirect( $goback );
    exit;
}

if (isset($_POST['addel'])) {
    $wpdb->delete( $cl, array( 'id' =>  $_POST['id'] ), array( '%d' ) );
    $parts = str_getcsv( wp_get_referer(), '&');
    $goback = add_query_arg( 'settings-updated', 'true', $parts[0] );
    wp_redirect( $goback );
    exit;
}
if (isset($_POST['settags'])) {
    $rwct = $wpdb->get_row("select * from " . $cl . " where id = '" . $_POST['id'] . "' and tp = 'ct'", ARRAY_A);
    $arr = json_decode($rwct['tx'], TRUE);
    $vals = $arr['vals'];
    // var_dump($vals);
    $tags = '';
    foreach ($_POST['tags'] as $key ){
        if ($key != 'none') {
            $tags .= $key . ';';
        }
    }
    $vals['TAGS'] = $tags;
    $arr['vals'] = $vals;

    $tx = json_encode($arr);
    //var_dump($tx);
    $wpdb->update( $cl, array( 'tx' => $tx ), array( 'id' => $rwct['id'] ), array( '%s', '%s' ), array( '%d' ) );
    $parts = str_getcsv( wp_get_referer(), '&');
    $goback = add_query_arg( 'settings-updated', 'true', $parts[0] );
    wp_redirect( $goback );
    exit;
}
if (isset($_POST['upfrm'])) {
    $rwct = $wpdb->get_row("select * from " . $cl . " where tp = 'se' ", ARRAY_A); //contact
    $arr = json_decode($rwct['tx'], TRUE);
    $frms = $arr['frms'];
    //die(var_dump($_POST));
    //die(var_dump($frms));
    foreach ($_POST as $par => $val) {
        $parts = str_getcsv( $par, '|');

        //die(var_dump($parts));
        if ($parts[0] == "name"
            or $parts[0] == "instr"
            or $parts[0] == "fields"
            or $parts[0] == "parm"
            or $parts[0] == "but1"
            or $parts[0] == "but2"
            or $parts[0] == "ins"
            or $parts[0] == "conf"
            or $parts[0] == "unsub") {
            $frms[$parts[1]][$parts[0]] = $val;
        }
    }

   // die(var_dump($frms));
    $arr['frms'] = $frms;
    $tx = json_encode($arr);
    $wpdb->update($cl, array('tx' => $tx), array('id' => $rwct['id']), array('%s'), array('%d'));
}
if (isset($_POST['tagplus'])) {
    foreach ($_POST as $par => $val) {
        if (substr($par,0,2) == 'id' and $par != 'idnone') {
           // die($par);
            $id = substr($par,2);
            $rwct = $wpdb->get_row("select * from " . $cl . " where id = '" . $id . "' and tp = 'ct'", ARRAY_A);
            $arr = json_decode($rwct['tx'], TRUE);
            $vals = $arr['vals'];
            $tags = $vals['TAGS'];
            $tagsarr = str_getcsv($tags, ';');
            $tagsarr[] = $_POST['tag'];
            $tagsarr = array_unique($tagsarr);
            sort($tagsarr);
            var_dump($tagsarr);
            $tags = '';
            foreach ($tagsarr as $key ){
                $tags .= $key.';';
            }
            $vals['TAGS'] = str_replace(';;',';',$tags);
            $tags;
            $arr['vals'] = $vals;
            $tx = json_encode($arr);
            //var_dump($tx);
            $wpdb->update( $cl, array( 'tx' => $tx ), array( 'id' => $rwct['id'] ), array( '%s', '%s' ), array( '%d' ) );
        }
    }
    $parts = str_getcsv( wp_get_referer(), '&');
    $goback = add_query_arg( 'settings-updated', 'true', $parts[0] );
  //  wp_redirect( $goback );
  //  exit;
}
if (isset($_POST['tagmin'])) {
    foreach ($_POST as $par => $val) {
        if (substr($par,0,2) == 'id'  and $par != 'idnone') {
            $id = substr($par,2);
            $rwct = $wpdb->get_row("select * from " . $cl . " where id = '" . $id . "' and tp = 'ct'", ARRAY_A);
            $arr = json_decode($rwct['tx'], TRUE);
            $vals = $arr['vals'];
            $vals['TAGS'] = str_replace($_POST['tag'].';','',$vals['TAGS']);
            $arr['vals'] = $vals;
            $tx = json_encode($arr);
            $wpdb->update( $cl, array( 'tx' => $tx ), array( 'id' => $rwct['id'] ), array( '%s', '%s' ), array( '%d' ) );
        }
    }
    $parts = str_getcsv( wp_get_referer(), '&');
    $goback = add_query_arg( 'settings-updated', 'true', $parts[0] );
  //  wp_redirect( $goback );
  //  exit;
}

if (isset($_POST['edcss'])) {
    $tx = trim(stripcslashes($_POST['css']));
    $tx = str_replace('<p>','' , $tx);
    //var_dump($tx);

    $css = $wpdb->get_row("select * from " . $cl . " where tp = 'ws'", ARRAY_A);
    if (empty($css)) {
        $sttm = time();
        $wpdb->insert($cl, array('st' => '', 'sttm' => $sttm,  'em' => '', 'tp' => 'ws', 'tx' => $tx ));
    } else {
        $wpdb->update($cl, array('tx' => $tx), array('tp' => 'ws'), array('%s'), array('%s'));
    }
}
if (isset($_POST['delcss'])) { //delete css
    $wpdb->delete($cl, array('tp' => 'ws'),  array('%s'));
}
if (isset($_POST['setlc'])) { //delete css
    add_option( 'lc', $_POST['lc']);
    wp_redirect( site_url() );
    exit;
}

if (isset($_POST['exct']))  {
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header('Content-Description: File Transfer');
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename={$_POST['filename']}");
    header("Expires: 0");
    header("Pragma: public");
    $fh = @fopen( 'php://output', 'w' );

    $top = array(); // id row
    $top[] = 'ct';
    $top[] = 'contacts';
    $top[] = date('Y-m-d H:i:s');
    $top[] = site_url();
    $def = array(); // label row
    if (isset($_POST['exct'])) { // button for contacts
        fputcsv($fh, $top);
        $rwcu = $wpdb->get_row("select * from " . $cl . " where tp = 'cu'", ARRAY_A);
        $arrcu = json_decode($rwcu['tx'], TRUE);
        $fields = $arrcu['fields'];
        foreach ($fields as $field){
            $def[] = $field['short'];
        }
        $def[] = 'log';
	    $def[] = 'id';
	    $def[] = 'ky';
		$def[] = 'st';
	    $def[] = 'sttm';

        fputcsv($fh, $def);


        $rsct = $wpdb->get_results("select * from " . $cl . " where tp = 'ct' ", ARRAY_A);
        foreach ($rsct as $rwct) {
            $arrct = json_decode($rwct['tx'], TRUE);
            $vals = $arrct['vals'];
            $val = array();

            foreach ($fields as $field){
                if(isset($vals[$field['short']])) {
                    $val[] = $vals[$field['short']];
                } else {
                    $val[] = '';
                }
            }

            $val[] = json_encode($arrct['log']);
	        $val[] = $rwct['id'];
	        $val[] = $rwct['ky'];
	        $val[] = $rwct['st'];
	        $val[] = $rwct['sttm'];
            fputcsv($fh, $val);
        }
        fclose($fh);
    }
	exit();
}
if (isset($_POST['exst']))  {
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header('Content-Description: File Transfer');
    header("Content-type: text/plain");
    header("Content-Disposition: attachment; filename={$_POST['filename']}");
    header("Expires: 0");
    header("Pragma: public");
    $fh = @fopen( 'php://output', 'w' );
    $rwse = $wpdb->get_row("select * from " . $cl . " where tp = 'se' ", ARRAY_A);
    fputs($fh, $rwse['tx'] );
    fclose($fh);
	exit();
}
if (isset($_POST['imct'])) { // button for import
	$uploads = wp_upload_dir();
    $upload_path = $uploads['path'];
    $target_file = $upload_path . basename($_FILES["uploadfile"]["name"]);
    $uploadOk = true;
    $logrow = '';
    $check = getimagesize($_FILES["uploadfile"]["tmp_name"]);
    if ($_FILES["uploadfile"]["size"] > 500000) {
        $logrow .= __( 'Sorry, your file is too large.', 'edcl' ).'<br>';
        $uploadOk = false;
    }
    if ($uploadOk) {
	    if ( move_uploaded_file( $_FILES["uploadfile"]["tmp_name"], $target_file ) ) {
		    $logrow .= __( 'The file ' . basename( $_FILES["uploadfile"]["name"] ) . ' has been uploaded.', 'edcl' ) . '<br>';
	    } else {
		    $logrow .= __( 'Sorry, there was an error uploading your file.', 'edcl' ) . '<br>';
		    $uploadOk = false;
	    }
    }
	if ($uploadOk) {
	    $file = fopen($target_file,"r");
	    $rowtel = 1;
        while(! feof($file)) {
	        $row = fgetcsv( $file );
	        if ( $rowtel == 1 ) { // id row
		        $type = trim($row[0]);
	        } elseif ( $rowtel == 2 ) { // label row
		        $header = $row;
           //     die(var_dump($header));
		        if ( $type == 'ct' ) { //contacts
			        $def = array(); // label row
			        $valdef = array(); // label row in  vals
			        $def[] = 'id';
			        $def[] = 'ky';
			        $def[] = 'st';
			        $def[] = 'sttm';
			        $rwcu = $wpdb->get_row("select * from " . $cl . " where tp = 'cu'", ARRAY_A);
			        $arrcu = json_decode($rwcu['tx'], TRUE);
			        $fields = $arrcu['fields'];
			        foreach ($fields as $field){
				        $def[] = $field['short'];
				        $valdef[] = $field['short'];
                        $valsinit[$field['short']] = '';
			        }
			        for ( $i = 0; !isset( $header[ $i ]) or $header[ $i ] == ''; $i ++ ) {
				        $header[ $i ] = trim ($header[ $i ]);
				        if ( !in_array($header[ $i ],$def )) {
				            $logrow .= __( 'Column not in definition.', 'edcl' ).' '.$header[ $i ].'<br>';
   				        } else {
					        $header[ $i ] = 'ignore';
				        }
			        }
		       }
	        } elseif ($row == null) {
	        } else {
		        if ( $type == 'ct' ) { //contacts
			        $mode = 'insert';
			        for ( $i = 0;  isset( $header[ $i ]) and $header[ $i ] != ''; $i++ )  {
                        if (isset( $row[ $i ] ) and  trim($row[ $i ]) != '' ) {
                            $row[$i] = trim($row[$i]);
                        } else {
                            $row[$i] = '';
                        }
				        if ( $header[ $i ] == 'id' and isset( $row[ $i ] ) and  trim($row[ $i ]) != '' ){
					        $rwct = $wpdb->get_row( "select * from " . $cl . " where id = '" . $row[ $i ] . "' and tp = 'ct'", ARRAY_A );
					        if ( $rwct ) {
						        $arr = json_decode( $rwct['tx'], true );
						        $mode = 'update';
					        } else {
						        $logrow .= __( 'Id not found.', 'edcl' ) . ' ' . $rowtel . '<br>';
					        }
				        }
			        }
			        if ($mode == 'update') {
				        $vals = $arr['vals'];
				        $log = $arr['log'];
				        for ( $i = 0; isset( $header[ $i ]) and $header[ $i ] != ''; $i++ )  {
					        if ( $header[ $i ] != 'ignore' and isset($row[$i]) and trim($row[$i]) != '' ) {
								if ( in_array ( $header[ $i ] , $valdef )){
									$vals[$header[ $i ]] = trim($row[$i]);
								} elseif ($header[ $i ] == 'log') {
									$log = trim($row[$i]);
								} else {
									$rwct[$header[ $i ]] = trim($row[$i]);
								}
					        } else {
						        $rwct[ $header[ $i ] ] = '';
					        }
				        }
				        $rwct['em'] = $vals['EM1'];
				        $rwct['nm'] = $vals['NM1'];
				        $arr['vals'] = $vals;
				        $arr['log'] = $log;
				        $tx = json_encode( $arr, true );
				        $wpdb->update($cl, array('ky' => $rwct['ky'],'st' => $rwct['st'],'sttm' => $rwct['sttm'],
				                                 'nm' => $rwct['nm'], 'em' => $rwct['em'], 'tx' => $tx ),
					                        array('id' => $id), array('%s', '%s', '%d', '%s', '%s', '%s'), array('%d'));
				        $logrow .= __( 'Update. Row:', 'edcl' ).' '.$rowtel.'<br>';
			        } else { //insert
				        $rwct = array();
				        $insok = true;
                        $vals = $valsinit;
				        $EM1ok = false;
				        $NM1ok = false;
				        for ( $i = 0; isset( $header[ $i ]) and $header[ $i ] != ''; $i++ )  {
					        if ( $header[ $i ] != 'ignore' ) {
						        if ( in_array ( $header[ $i ] , $valdef )){
							        $vals[$header[ $i ]] = $row[$i];
							        if ($header[ $i ] == 'NM1')  $NM1ok = true;
							        if ($header[ $i ] == 'EM1') {
                                        $EM1ok = true;
								        $rsctex = $wpdb->get_results("select * from " . $cl . " where tp = 'ct' and em = '" . $row[$i] . "'", ARRAY_A);
								        //var_dump($rsct);
								        if ($rsctex) {
									        $logrow .= __( 'Email already known.', 'edcl' ).' '.$rowtel.' '.$row[$i].'<br>';
									        $insok = false;
								        }
							        }
						        } elseif ($header[ $i ] == 'log') {
							        $log = $row[$i];
						        } else {
							        $rwct[$header[ $i ]] = $row[$i];
						                                        }
					        }
				        }
                       // die(var_dump($rwct));
				        if (!$EM1ok) {
					        $logrow .= __( 'EM1 is required. Row: ', 'edcl' ).' '.$rowtel.'<br>';
					        $insok = false;
				        }
                        if (!$NM1ok) {
					        $logrow .= __( 'NM1is required. Row: ', 'edcl' ).' '.$rowtel.'<br>';
					        $insok = false;
				        }
				        if (!$insok) {
					        $logrow .= __( 'Not inserted. Row:', 'edcl' ).' '.$rowtel.'<br>';
				        } else {
					        $arr = array();
					        $arr['vals'] = $vals;
					        $rwct['ky'] = str_replace(".", "A", microtime(true));
					        if (!isset($rwct['st']) or $rwct['st'] == '') $rwct['st'] = 'C10';
					        if (!isset($rwct['sttm']) or $rwct['sttm'] == '') $rwct['sttm'] = time();
					        $rwct['em'] = $vals['EM1'];
					        $rwct['nm'] = $vals['NM1'];
					        $arr['log'][$rwct['sttm']] = $rwct['st'];
					        $tx = json_encode($arr);
					        $wpdb->insert($cl, array('tp' => 'ct', 'ky' => $rwct['ky'],'st' => $rwct['st'], 'sttm' => $rwct['sttm'],
					                                 'nm' => $rwct['nm'], 'em' => $rwct['em'], 'tx' => $tx ),
						                    array('%s', '%s', '%s', '%d', '%s', '%s', '%s'));
					        $logrow .= __( 'Inserted. Row:', 'edcl' ).' '.$rowtel.' '.$rwct['nm'].' '.$rwct['em'].'<br>';
				        }
			        }
		        }
	        }
            $rowtel++;
	        unset($vals,$log);
        }
        fclose($file);
    }
	$_SESSION['log'] = $logrow;
}
if (isset($_POST['imst'])) { // button for import
	$uploads = wp_upload_dir();
    $upload_path = $uploads['path'];
    $target_file = $upload_path . basename($_FILES["uploadfile"]["name"]);
    $uploadOk = true;
    $logrow = '';
    $check = getimagesize($_FILES["uploadfile"]["tmp_name"]);
    if ($_FILES["uploadfile"]["size"] > 500000) {
        $logrow .= __( 'Sorry, your file is too large.', 'edcl' ).'<br>';
        $uploadOk = false;
    }
    if ($uploadOk) {
	    if ( move_uploaded_file( $_FILES["uploadfile"]["tmp_name"], $target_file ) ) {
		    $logrow .= __( 'The file ' . basename( $_FILES["uploadfile"]["name"] ) . ' has been uploaded.', 'edcl' ) . '<br>';
	    } else {
		    $logrow .= __( 'Sorry, there was an error uploading your file.', 'edcl' ) . '<br>';
		    $uploadOk = false;
	    }
    }
	if ($uploadOk) {
	    $file = fopen($target_file,"r");
	    $tx = fgets( $file );
      //  die($tx);
        $wpdb->update($cl, array('tx' => $tx ), array('tp' => 'se'), array('%s'), array('%s'));
        fclose($file);
    }
	$_SESSION['log'] = $logrow;
}


$goback = add_query_arg( 'settings-updated', 'true', wp_get_referer() );
wp_redirect( $goback );
//exit;
