<?php
/**
 * Created by PhpStorm.
 * User: erikd
 * Date: 5-5-2016
 * Time: 22:54
 */
function custom_adminbar_menu( $meta = TRUE ) {
    global $wpdb;
    $cl = $wpdb->prefix . 'contactlist';
    global $wp_admin_bar;
    if ( !is_user_logged_in() ) return;
    if ( !is_super_admin() || !is_admin_bar_showing() )return;
    $wp_admin_bar->add_menu( array(
            'id' => 'contactlist',
            'title' => __( 'Contactlist' , 'edcl' ) )
    );
    $wp_admin_bar->add_menu(array(
        'parent' => 'contactlist',
        'id' => 'settings',
        'title' => __('Settings', 'edcl'),
        'href' => '#',
    ));
    $ok = false;
    //die (var_dump(get_option('dg')));
    if (get_option('trm')) {
        $dgn = intval(time() / 8640);
        if (get_option('trm') > $dgn) {
            $ok = true;
        }
    }
    if (!$ok) {
        $wp_admin_bar->add_menu(array(
            'parent' => 'settings',
            'id' => 'lic',
            'title' => __('License', 'edcl'),
            'href' => admin_url('admin.php?page=license'),
        ));
        $wp_admin_bar->add_menu(array(
            'parent' => 'contactlist',
            'id' => 'about',
            'title' => __('About contactlist', 'edcl'),
            'href' => admin_url('admin.php?page=about'),
        ));
    } else {
        $wp_admin_bar->add_menu(array(
            'parent' => 'settings',
            'id' => 'clist',
            'title' => __('List', 'edcl'),
            'href' => admin_url('admin.php?page=lists'),
        ));
        $wp_admin_bar->add_menu(array(
            'parent' => 'settings',
            'id' => 'fields',
            'title' => __('Fields', 'edcl'),
            'href' => admin_url('admin.php?page=fields'),
        ));
        $wp_admin_bar->add_menu(array(
            'parent' => 'settings',
            'id' => 'tags',
            'title' => __('Tags', 'edcl'),
            'href' => admin_url('admin.php?page=tags'),
        ));
        $wp_admin_bar->add_menu(array(
            'parent' => 'settings',
            'id' => 'forms',
            'title' => __('Forms', 'edcl'),
            'href' => admin_url('admin.php?page=forms'),
        ));
        $wp_admin_bar->add_menu(array(
            'parent' => 'settings',
            'id' => 'events',
            'title' => __('Events', 'edcl'),
            'href' => admin_url('admin.php?page=events'),
        ));
        $wp_admin_bar->add_menu(array(
            'parent' => 'settings',
            'id' => 'stmails',
            'title' => __('Standard mails', 'edcl'),
            'href' => admin_url('admin.php?page=stmails'),
        ));
        $wp_admin_bar->add_menu(array(
            'parent' => 'settings',
            'id' => 'css',
            'title' => __('Layout (css)', 'edcl'),
            'href' => admin_url('admin.php?page=css'),
        ));
        $wp_admin_bar->add_menu(array(
            'parent' => 'settings',
            'id' => 'export',
            'title' => __('Export', 'edcl'),
            'href' => admin_url('admin.php?page=export'),
        ));
        $wp_admin_bar->add_menu(array(
            'parent' => 'settings',
            'id' => 'import',
            'title' => __('Import', 'edcl'),
            'href' => admin_url('admin.php?page=import'),
        ));

        $rwcu = $wpdb->get_row("select * from " . $cl . " where tp = 'cu'", ARRAY_A);
        if ($rwcu) {
            $arr = json_decode($rwcu['tx'], TRUE);
            if (!empty($arr['settings']['perma'])) {
                $wp_admin_bar->add_menu(array(
                    'parent' => 'contactlist',
                    'id' => 'newcontact',
                    'title' => __('New full contact', 'edcl'),
                    'href' => $arr['settings']['perma'],
                ));
            }
        }
        $wp_admin_bar->add_menu(array(
            'parent' => 'contactlist',
            'id' => 'console',
            'title' => __('Console', 'edcl'),
            'href' => admin_url('admin.php?page=console'),
        ));
        $wp_admin_bar->add_menu(array(
            'parent' => 'contactlist',
            'id' => 'tagging',
            'title' => __('Tagging', 'edcl'),
            'href' => admin_url('admin.php?page=tagging'),
        ));
        $wp_admin_bar->add_menu(array(
            'parent' => 'contactlist',
            'id' => 'umail',
            'title' => __('Mails', 'edcl'),
            'href' => admin_url('admin.php?page=umails'),
        ));
        $wp_admin_bar->add_menu(array(
            'parent' => 'contactlist',
            'id' => 'select',
            'title' => __('Make selection', 'edcl'),
            'href' => admin_url('admin.php?page=select'),
        ));
        $wp_admin_bar->add_menu(array(
            'parent' => 'contactlist',
            'id' => 'mailer',
            'title' => __('Mailer', 'edcl'),
            'href' => admin_url('admin.php?page=mailer'),
        ));
        $wp_admin_bar->add_menu(array(
            'parent' => 'contactlist',
            'id' => ' about',
            'title' => __('About contactlist', 'edcl'),
            'href' => admin_url('admin.php?page=about'),
        ));
    }
}
add_action( 'admin_bar_menu', 'custom_adminbar_menu', 90 );

function edcl_add_admin_menu() {
    add_menu_page( null, 'Contactlist', 'manage_options', 'Contactlist', 'settings');
    $ok = false;
    if (get_option('trm')) {
        $dgn = intval(time() / 8640);
        if (get_option('trm') > $dgn) {
            $ok = true;
        }
    }
    if (!$ok) {
        add_submenu_page('Contactlist', __('License', 'edcl'), __('license', 'edcl'), 'manage_options', 'license', 'license');
        add_submenu_page('Contactlist', __('About contactlist', 'edcl'), __('About', 'edcl'), 'manage_options', 'about', 'about');
    } else {
        add_submenu_page ('Contactlist', __( 'List', 'edcl'), __( 'List', 'edcl'), 'manage_options', 'lists', 'lists' );
        add_submenu_page ('Contactlist', __( 'Fields', 'edcl'), __( 'Fields', 'edcl'), 'manage_options', 'fields', 'fields' );
        add_submenu_page ('Contactlist', __( 'Tags', 'edcl'), __( 'Tags', 'edcl'), 'manage_options', 'tags', 'tags' );
        add_submenu_page ('Contactlist', __( 'Forms', 'edcl'), __( 'Forms', 'edcl'), 'manage_options', 'forms', 'forms' );
        add_submenu_page ('Contactlist', __( 'Events', 'edcl'), __( 'Events', 'edcl'), 'manage_options', 'events', 'events' );
        add_submenu_page ('Contactlist', __( 'Console', 'edcl'), __( 'Console', 'edcl'), 'manage_options', 'console', 'console' );
        add_submenu_page ('Contactlist', __( 'Tagging', 'edcl'), __( 'Tagging', 'edcl'), 'manage_options', 'tagging', 'tagging' );
        add_submenu_page ('Contactlist', __( 'Standard mails', 'edcl'),  __( 'Standard mails', 'edcl'),  'manage_options', 'stmails', 'stmails' );
        add_submenu_page ('Contactlist', __( 'Css', 'edcl'),  __( 'Layout (css expert)', 'edcl'),  'manage_options', 'css', 'css' );
        add_submenu_page ('Contactlist', __( 'Export', 'edcl'),  __( 'Export', 'edcl'),  'manage_options', 'export', 'export' );
        add_submenu_page ('Contactlist', __( 'Import', 'edcl'),  __( 'Import', 'edcl'),  'manage_options', 'import', 'import' );
        add_submenu_page ('Contactlist', __( 'Mails', 'edcl'),  __( 'Mails', 'edcl'),  'manage_options', 'umails', 'umails' );
        add_submenu_page ('Contactlist', __( 'Select', 'edcl'),  __( 'Select', 'edcl'),  'manage_options', 'select', 'select' );
        add_submenu_page ('Contactlist', __( 'Mailer', 'edcl'),  __( 'Mailer', 'edcl'),  'manage_options', 'mailer', 'mailer' );
        add_submenu_page('Contactlist', __('About contactlist', 'edcl'), __('About', 'edcl'), 'manage_options', 'about', 'about');
    }
}
add_action( 'admin_menu', 'edcl_add_admin_menu' );

function lists() {
    include_once(EDCL_DIR."/contr/clsetting.php");
    $setting  = new clsetting;
    $setting->contactlist();
}
function license() {
    include_once(EDCL_DIR."/contr/clsetting.php");
    $setting  = new clsetting;
    $setting->license();
}
function about() {
    aboutcontent();
}
function fields() {
    include_once(EDCL_DIR."/contr/clsetting.php");
    $setting  = new clsetting;
    $setting->fields();
}
function tags() {
    include_once(EDCL_DIR."/contr/clsetting.php");
    $setting  = new clsetting;
    $setting->tags();
}
function forms() {
    include_once(EDCL_DIR."/contr/clsetting.php");
    $setting  = new clsetting;
    $setting->forms();
}
function events() {
    include_once(EDCL_DIR."/contr/clsetting.php");
    $setting  = new clsetting;
    $setting->events();
}
function export() {
    include_once(EDCL_DIR."/contr/clsetting.php");
    $setting  = new clsetting;
    $setting->export();
}
function import() {
    include_once(EDCL_DIR."/contr/clsetting.php");
    $setting  = new clsetting;
    $setting->import();
}
function console() {
    include_once(EDCL_DIR."/contr/clconsole.php");
    $console  = new clconsole;
    $console->console();
}
function tagging() {
    include_once(EDCL_DIR."/contr/cltagging.php");
    $tagging  = new cltagging;
    $tagging->tagging();
}
function stmails() {
    include_once(EDCL_DIR."/contr/clmail.php");
    $mail  = new clmail;
    $mail->mainthtml('st');
}
function umails() {
    include_once(EDCL_DIR."/contr/clmail.php");
    $mail  = new clmail;
    $mail->mainthtml('u');
}
function css() {
    include_once(EDCL_DIR."/contr/clcss.php");
    $css  = new clcss;
    $css->editcss();
}
function select() {
    include_once(EDCL_DIR . "/selectcontact.php");
    echo  $outsel;
}
function mailer() {
    include_once(EDCL_DIR."/mailer.php");
}
