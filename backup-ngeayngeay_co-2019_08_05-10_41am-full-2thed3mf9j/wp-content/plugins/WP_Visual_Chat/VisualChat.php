<?php

/*
 * Plugin Name: WP Flat Visual Chat
 * Version: 5.384
 * Plugin URI: http://codecanyon.net/user/loopus/portfolio
 * Description: A unique chat system allowing you to visually guide your clients on the site
 * Author: Biscay Charly (loopus)
 * Author URI: http://www.loopus-plugins.com/
 * Requires at least: 3.7
 * Tested up to: 5.2
 *
 * @package WordPress
 * @author Biscay Charly (loopus)
 * @since 1.0.0
 */


if (!defined('ABSPATH'))
    exit;

register_activation_hook(__FILE__, 'vcht_install');
//register_deactivation_hook(__FILE__, 'vcht_uninstall');
register_uninstall_hook(__FILE__, 'vcht_uninstall');

global $jal_db_version;
$jal_db_version = "1.0";
require_once('includes/vcht_Core.php');
require_once('includes/vcht_Admin.php');

function VisualChat() {
    $version = 5.384;
    vcht_checkDBUpdates($version);
    $instance = vcht_Core::instance(__FILE__, $version);
    if (is_null($instance->menu)) {
        $instance->menu = vcht_Admin::instance($instance);
    }

    return $instance;
}

/**
 * Installation. Runs on activation.
 * @access  public
 * @since   1.0.0
 * @return  void
 */
function vcht_install() {
    global $wpdb;
    global $jal_db_version;
    require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

    // create settings table
    $db_table_name = $wpdb->prefix . "vcht_settings";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                purchaseCode  VARCHAR(250) NOT NULL DEFAULT '',
                enableChat BOOL NOT NULL DEFAULT 1,
                enableGeolocalization BOOL NOT NULL DEFAULT 1,
                enableVisitorsTracking BOOL NOT NULL DEFAULT 1,
                trackingDelay FLOAT NOT NULL DEFAULT 20,
                ajaxCheckDelay FLOAT NOT NULL DEFAULT 8,
                allowFilesFromOperators BOOL NOT NULL DEFAULT 1,
                allowFilesFromCustomers BOOL NOT NULL DEFAULT 1,
                filesMaxSize SMALLINT(5) NOT NULL DEFAULT 5,
                allowedFiles VARCHAR(250) NOT NULL DEFAULT '.png,.jpg,.jpeg,.gif,.zip,.rar',
                operatorsFullHistory BOOL NOT NULL DEFAULT 1,
                enableLoginPanel BOOL NOT NULL DEFAULT 0,
                enableLoggedVisitorsOnly BOOL NOT NULL DEFAULT 0,
                defaultUsername VARCHAR(250) NOT NULL DEFAULT 'Visitor',
                enableContactForm BOOL NOT NULL DEFAULT 1,
                googleFont VARCHAR(250) NOT NULL DEFAULT 'Lato',
                color_btnBg VARCHAR(7) NOT NULL DEFAULT '#1abc9c',
                color_btnTexts VARCHAR(7) NOT NULL DEFAULT '#ffffff',
                color_bg VARCHAR(7) NOT NULL DEFAULT '#ffffff',
                color_texts VARCHAR(7) NOT NULL DEFAULT '#34495e',
                color_headerBg VARCHAR(7) NOT NULL DEFAULT '#1abc9c',
                color_headerTexts VARCHAR(7) NOT NULL DEFAULT '#ffffff',
                color_headerBtnBg VARCHAR(7) NOT NULL DEFAULT '#34495e',
                color_headerBtnTexts VARCHAR(7) NOT NULL DEFAULT '#ffffff',
                color_operatorBubbleBg VARCHAR(7) NOT NULL DEFAULT '#1abc9c',
                color_operatorBubbleTexts VARCHAR(7) NOT NULL DEFAULT '#ffffff',
                color_customerBubbleBg VARCHAR(7) NOT NULL DEFAULT '#ecf0f1',
                color_customerBubbleTexts VARCHAR(7) NOT NULL DEFAULT '#bdc3c7',
                color_shining VARCHAR(7) NOT NULL DEFAULT '#1abc9c',
                color_loaderBg  VARCHAR(7) NOT NULL DEFAULT '#1abc9c',
                color_loader  VARCHAR(7) NOT NULL DEFAULT '#ffffff',
                color_icons  VARCHAR(7) NOT NULL DEFAULT '#1abc9c',
                color_scrollBg  VARCHAR(7) NOT NULL DEFAULT '#ecf0f1',
                color_scroll  VARCHAR(7) NOT NULL DEFAULT '#bdc3c7',
                color_labels VARCHAR(7) NOT NULL DEFAULT '#bdc3c7',
                color_fieldsBg VARCHAR(7) NOT NULL DEFAULT '#ffffff',
                color_fields VARCHAR(7) NOT NULL DEFAULT '#bdc3c7',
                color_fieldsBorder VARCHAR(7) NOT NULL DEFAULT '#bdc3c7',
                color_fieldsBorderFocus VARCHAR(7) NOT NULL DEFAULT '#1abc9c',
                color_showCircleBg  VARCHAR(7) NOT NULL DEFAULT '#ecf0f1',     
                color_tooltipBg VARCHAR(7) NOT NULL DEFAULT '#34495e',
                color_tooltip VARCHAR(7) NOT NULL DEFAULT '#FFFFFF',
                color_btnSecBg VARCHAR(7) NOT NULL DEFAULT '#bdc3c7',
                color_btnSecTexts VARCHAR(7) NOT NULL DEFAULT '#ffffff',
                panelShadow BOOL NOT NULL DEFAULT 1,
                playSoundOperator BOOL NOT NULL DEFAULT 1,
                playSoundCustomer BOOL NOT NULL DEFAULT 0,
                chatPosition VARCHAR(7) NOT NULL,
                bounceFx BOOL NOT NULL,
                rolesAllowed LONGTEXT NOT NULL,
                defaultImgAvatar VARCHAR(250) NOT NULL,
                customerImgAvatar VARCHAR(250) NOT NULL,
                chatLogo VARCHAR(250) NOT NULL,
                widthPanel SMALLINT(5) NOT NULL DEFAULT 280,
                heightPanel SMALLINT(5) NOT NULL DEFAULT 410,
                borderRadius SMALLINT(5) NOT NULL DEFAULT 5,
                showCloseBtn BOOL NOT NULL DEFAULT 1,
                showFullscreenBtn BOOL NOT NULL DEFAULT 1,
                showMinifyBtn BOOL NOT NULL DEFAULT 1,
                contactFormIcon VARCHAR(250) NOT NULL DEFAULT 'fa-envelope',
                loginFormIcon VARCHAR(250) NOT NULL DEFAULT 'fa-user-circle',
                emailAdmin VARCHAR(250) NOT NULL DEFAULT 'my@email.here',
                emailSubject VARCHAR(250) NOT NULL DEFAULT 'New message from your website',
                usePoFile BOOL NOT NULL DEFAULT 0,
                UNIQUE KEY id (id)
                ) $charset_collate;";
        dbDelta($sql);

        $wpdb->insert($db_table_name, array('id' => 1,
            'enableGeolocalization' => 1,
            'trackingDelay' => 20, 'enableVisitorsTracking' => 1, 'ajaxCheckDelay' => 8, 'operatorsFullHistory' => 1,
            'defaultUsername' => 'Visitor', 'enableContactForm' => 1, 'enableLoginPanel' => 1,
            'googleFont' => 'Lato',
            'color_btnBg' => '#1abc9c',
            'color_btnTexts' => '#ffffff',
            'color_bg' => '#ffffff',
            'color_texts' => '#bdc3c7',
            'color_headerBg' => '#1abc9c',
            'color_headerTexts' => '#ffffff',
            'color_headerBtnBg' => '#34495e',
            'color_headerBtnTexts' => '#ffffff',
            'color_operatorBubbleBg' => '#1abc9c',
            'color_operatorBubbleTexts' => '#ffffff',
            'color_customerBubbleBg' => '#ecf0f1',
            'color_customerBubbleTexts' => '#bdc3c7',
            'color_loaderBg' => '#1abc9c',
            'color_loader' => '#ffffff',
            'color_shining' => '#1abc9c',
            'color_icons' => '#1abc9c',
            'color_scrollBg' => '#ecf0f1',
            'color_scroll' => '#bdc3c7',
            'chatPosition' => 'right',
            'bounceFx' => 0,
            'enableChat' => 1,
            'widthPanel' => 280,
            'heightPanel' => 410,
            'borderRadius' => 5,
            'showCloseBtn' => 1,
            'showFullscreenBtn' => 1,
            'showMinifyBtn' => 1,
            'contactFormIcon' => 'fa-envelope',
            'loginFormIcon' => 'fa-user-circle',
            'emailAdmin' => 'my@email.here',
            'emailSubject' => 'New message from your website',
            'usePoFile' => 0,
            'customerImgAvatar' => esc_url(trailingslashit(plugins_url('/assets/', __FILE__))) . 'img/guest-128.png',
            'defaultImgAvatar' => esc_url(trailingslashit(plugins_url('/assets/', __FILE__))) . 'img/administrator-2-128.png',
            'chatLogo' => esc_url(trailingslashit(plugins_url('/assets/', __FILE__))) . 'img/chat-4-64.png'));
    }

    // create users table
    $db_table_name = $wpdb->prefix . "vcht_users";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                userID MEDIUMINT(9) NOT NULL DEFAULT 0,
                clientID VARCHAR(64) NOT NULL DEFAULT '',
                lastActivity DATETIME NOT NULL,
                username VARCHAR(250) NOT NULL DEFAULT 'Anonymous',
                email VARCHAR(250) NOT NULL DEFAULT '',
                isOperator BOOL NOT NULL DEFAULT 0,
                isOnline BOOL NOT NULL DEFAULT 0,
                imgAvatar VARCHAR(250)NOT NULL DEFAULT '',
                uploadFolderName VARCHAR(128) NOT NULL DEFAULT '',
                currentPage VARCHAR(250) NOT NULL DEFAULT '',
                ip VARCHAR(128) NOT NULL DEFAULT '',                
                country VARCHAR(128) NOT NULL,
                city VARCHAR(128) NOT NULL,
                currentOperator SMALLINT(5) NOT NULL DEFAULT 0,
                fieldsJson LONGTEXT NOT NULL,             
		UNIQUE KEY id (id)
		) $charset_collate;";
        dbDelta($sql);
    }

    // create fields table
    $db_table_name = $wpdb->prefix . "vcht_fields";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                inLoginPanel BOOL NOT NULL DEFAULT 1,
                ordersort SMALLINT(5) NOT NULL,
                title VARCHAR(128) NOT NULL,
                backendTitle VARCHAR(128) NOT NULL,
                type VARCHAR(32) NOT NULL,
                isRequired BOOL NOT NULL DEFAULT 1,
                icon VARCHAR(64) NOT NULL DEFAULT 'fa fa-info-circle',
                iconPosition BOOL NOT NULL DEFAULT 0,
                placeholder VARCHAR(250) NOT NULL,
                validation VARCHAR(64) NOT NULL DEFAULT '',
                validationMin SMALLINT(5) NOT NULL,
                validationMax SMALLINT(5) NOT NULL,
                validationCaracts VARCHAR(250) NOT NULL DEFAULT '',
                showInDetails BOOL NOT NULL DEFAULT 1,
                optionsValues TEXT NOT NULL,
                valueMin SMALLINT(9) NOT NULL DEFAULT 0,
                valueMax SMALLINT(9) NOT NULL DEFAULT 100,
                defaultValue VARCHAR(250) NOT NULL DEFAULT '',
                infoType VARCHAR(64) NOT NULL DEFAULT '',
		UNIQUE KEY id (id)
		) $charset_collate;";
        dbDelta($sql);
        $wpdb->insert($db_table_name, array('infoType' => 'email', 'title' => 'Your email', 'backendTitle' => 'Email', 'type' => 'textfield', 'validation' => 'email', 'icon' => 'fa fa-envelope', 'isRequired' => 1, 'showInDetails' => 1, 'inLoginPanel' => 0, 'icon' => 'fa-envelope-o', 'iconPosition' => 0, 'placeholder' => 'My email'));
        $wpdb->insert($db_table_name, array('title' => 'Your message', 'backendTitle' => 'Message', 'type' => 'textarea', 'isRequired' => 1, 'inLoginPanel' => 0));
        $wpdb->insert($db_table_name, array('infoType' => 'email', 'title' => 'Your email', 'backendTitle' => 'Email', 'type' => 'textfield', 'validation' => 'email', 'icon' => 'fa fa-envelope', 'isRequired' => 1, 'showInDetails' => 1, 'inLoginPanel' => 1, 'icon' => 'fa-envelope-o', 'iconPosition' => 0, 'placeholder' => 'My email'));
    }

    // create fields table
    $db_table_name = $wpdb->prefix . "vcht_messages";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                type VARCHAR(32) NOT NULL DEFAULT 'message',
                msgDate DATETIME NOT NULL,
                senderID MEDIUMINT(9) NOT NULL DEFAULT 0,
                receiverID MEDIUMINT(9) NOT NULL DEFAULT 0,
                content LONGTEXT NOT NULL,
                files LONGTEXT NOT NULL,
                domElement VARCHAR(250) NOT NULL DEFAULT '',
                page VARCHAR(250) NOT NULL DEFAULT '',
                transferUsername VARCHAR(255) NOT NULL DEFAULT '',
		transferID MEDIUMINT(9) DEFAULT 0,
		UNIQUE KEY id (id)
		) $charset_collate;";
        dbDelta($sql);
    }


    // create canned Messages table
    $db_table_name = $wpdb->prefix . "vcht_cannedMessages";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                keyB VARCHAR(16) NOT NULL DEFAULT 'shift',
                title VARCHAR(64) NOT NULL,
                content TEXT NOT NULL,
                shortcut VARCHAR(32) NOT NULL,
                createdByAdmin BOOL NOT NULL DEFAULT 0,
		UNIQUE KEY id (id)
		) $charset_collate;";
        dbDelta($sql);
    }

    // create texts table
    $db_table_name = $wpdb->prefix . "vcht_texts";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
		 id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                original TEXT NOT NULL,
                content TEXT NOT NULL,
                isTextarea BOOL NOT NULL,
		UNIQUE KEY id (id)
		) $charset_collate;";
        dbDelta($sql);

        $text = "Need Help ?";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "Start";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = 'Hello! How can we help you ?';
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text, 'isTextarea' => true));
        $textA = "This discussion is finished.";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $textA, 'content' => $textA));
        $text = "Sorry, there is currently no operator online. Feel free to contact us by using the form below.";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text, 'isTextarea' => true));
        $text = "Send this message";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = 'Thank you.\nYour message has been sent.\nWe will contact you soon.';
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text, 'isTextarea' => true));
        $text = "There was an error while transferring the file";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "The selected file exceeds the authorized size";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "The selected type of file is not allowed";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "Drop files to upload here";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "You can not upload any more files";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "New message from your website";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "Yes";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "No";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "Shows an element of the website";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "[username] stopped the chat";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "Confirm";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "Transfer some files";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "[username1] tranfers the chat to [username2]";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
    }


    // Create chat operator role
    global $wp_roles;
    $wp_roles->add_cap('administrator', 'visual_chat');
    add_role('chat_operator', 'Chat Operator', array(
        'visual_chat' => true,
        'read' => true,
        'edit_posts' => true,
        'delete_posts' => false)
    );
    $wp_roles->add_cap('chat_operator', 'visual_chat');
    
    update_option("vcht_version", 5.384);
}

/**
 * Update database
 * @access  public
 * @since   2.0
 * @return  void
 */
function vcht_checkDBUpdates($version) {
    global $wpdb;
    global $jal_db_version;
    require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

    $installed_ver = get_option("vcht_version");


    if (!$installed_ver || $installed_ver < 5.369) {

        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $table_name = $wpdb->prefix . "vcht_fields";
        $sql = "CREATE TABLE $table_name (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                inLoginPanel BOOL NOT NULL DEFAULT 1,
                ordersort SMALLINT(5) NOT NULL,
                title VARCHAR(128) NOT NULL,
                backendTitle VARCHAR(128) NOT NULL,
                type VARCHAR(32) NOT NULL,
                isRequired BOOL NOT NULL DEFAULT 1,
                icon VARCHAR(64) NOT NULL DEFAULT 'fa fa-info-circle',
                iconPosition BOOL NOT NULL DEFAULT 0,
                placeholder VARCHAR(250) NOT NULL,
                validation VARCHAR(64) NOT NULL DEFAULT '',
                validationMin SMALLINT(5) NOT NULL,
                validationMax SMALLINT(5) NOT NULL,
                validationCaracts VARCHAR(250) NOT NULL DEFAULT '',
                showInDetails BOOL NOT NULL DEFAULT 1,
                optionsValues TEXT NOT NULL,
                valueMin SMALLINT(9) NOT NULL DEFAULT 0,
                valueMax SMALLINT(9) NOT NULL DEFAULT 100,
                defaultValue VARCHAR(250) NOT NULL DEFAULT '',
                infoType VARCHAR(64) NOT NULL DEFAULT '',
		UNIQUE KEY id (id)
		) $charset_collate;";
        dbDelta($sql);
        $wpdb->insert($table_name, array('infoType' => 'email', 'title' => 'Your email', 'backendTitle' => 'Email', 'type' => 'textfield', 'validation' => 'email', 'icon' => 'fa fa-envelope', 'isRequired' => 1, 'showInDetails' => 1, 'inLoginPanel' => 0, 'icon' => 'fa-envelope-o', 'iconPosition' => 0, 'placeholder' => 'My email'));
        $wpdb->insert($table_name, array('title' => 'Your message', 'backendTitle' => 'Message', 'type' => 'textarea', 'isRequired' => 1, 'inLoginPanel' => 0));
        $wpdb->insert($table_name, array('infoType' => 'email', 'title' => 'Your email', 'backendTitle' => 'Email', 'type' => 'textfield', 'validation' => 'email', 'icon' => 'fa fa-envelope', 'isRequired' => 1, 'showInDetails' => 1, 'inLoginPanel' => 1, 'icon' => 'fa-envelope-o', 'iconPosition' => 0, 'placeholder' => 'My email'));

        $table_name = $wpdb->prefix . "vcht_messages";
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        $sql = "CREATE TABLE $table_name (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                type VARCHAR(32) NOT NULL DEFAULT 'message',
                msgDate DATETIME NOT NULL,
                senderID MEDIUMINT(9) NOT NULL DEFAULT 0,
                receiverID MEDIUMINT(9) NOT NULL DEFAULT 0,
                content LONGTEXT NOT NULL,
                files LONGTEXT NOT NULL,
                domElement VARCHAR(250) NOT NULL DEFAULT '',
                page VARCHAR(250) NOT NULL DEFAULT '',
                transferUsername VARCHAR(255) NOT NULL DEFAULT '',
		transferID MEDIUMINT(9) DEFAULT 0,
		UNIQUE KEY id (id)
		) $charset_collate;";
        dbDelta($sql);

        $table_name = $wpdb->prefix . "vcht_users";
        $wpdb->query("DROP TABLE IF EXISTS $table_name");

        $sql = "CREATE TABLE $table_name (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                userID MEDIUMINT(9) NOT NULL DEFAULT 0,
                clientID VARCHAR(64) NOT NULL DEFAULT '',
                lastActivity DATETIME NOT NULL,
                username VARCHAR(250) NOT NULL DEFAULT 'Anonymous',
                email VARCHAR(250) NOT NULL DEFAULT '',
                isOperator BOOL NOT NULL DEFAULT 0,
                isOnline BOOL NOT NULL DEFAULT 0,
                imgAvatar VARCHAR(250)NOT NULL DEFAULT '',
                uploadFolderName VARCHAR(128) NOT NULL DEFAULT '',
                currentPage VARCHAR(250) NOT NULL DEFAULT '',
                ip VARCHAR(128) NOT NULL DEFAULT '',                
                country VARCHAR(128) NOT NULL,
                city VARCHAR(128) NOT NULL,
                currentOperator SMALLINT(5) NOT NULL DEFAULT 0,
                fieldsJson LONGTEXT NOT NULL,    
		UNIQUE KEY id (id)
		) $charset_collate;";
        dbDelta($sql);


        $table_name = $wpdb->prefix . "vcht_settings";
        $settings = $wpdb->get_results("SELECT * FROM $table_name  LIMIT 1");
        $settings = $settings[0];

        $newSettings = array();
        $newSettings['enableChat'] = 1;
        $newSettings['enableVisitorsTracking'] = $settings->enableInitiate;
        $newSettings['trackingDelay'] = $settings->visitorsDetectionDelay;
        $newSettings['ajaxCheckDelay'] = $settings->messagesDelay;
        $newSettings['allowFilesFromOperators'] = $settings->allowFileUploadAdmin;
        $newSettings['allowFilesFromCustomers'] = $settings->allowFileUpload;
        $newSettings['filesMaxSize'] = $settings->fileMaxSize;
        $newSettings['allowedFiles'] = $settings->allowedFiles;
        $newSettings['operatorsFullHistory'] = 1;
        $newSettings['enableLoginPanel'] = 1;
        $newSettings['defaultUsername'] = 'Visitor';
        $newSettings['enableContactForm'] = 1;
        $newSettings['googleFont'] = 'Lato';
        $newSettings['color_btnBg'] = $settings->colorA;
        $newSettings['color_btnTexts'] = $settings->colorE;
        $newSettings['color_bg'] = '#ffffff';
        $newSettings['color_texts'] = $settings->colorB;
        $newSettings['color_headerBg'] = $settings->colorA;
        $newSettings['color_headerTexts'] = '#ffffff';
        $newSettings['color_headerBtnBg'] = '#34495e';
        $newSettings['color_headerBtnTexts'] = '#ffffff';
        $newSettings['color_operatorBubbleBg'] = $settings->colorC;
        $newSettings['color_operatorBubbleTexts'] = $settings->colorF;
        $newSettings['color_customerBubbleBg'] = $settings->colorA;
        $newSettings['color_customerBubbleTexts'] = $settings->colorE;
        $newSettings['color_shining'] = $settings->shineColor;
        $newSettings['color_loaderBg'] = $settings->colorA;
        $newSettings['color_loader'] = '#ffffff';
        $newSettings['color_icons'] = $settings->colorA;
        $newSettings['color_scrollBg'] = '#ecf0f1';
        $newSettings['color_scroll'] = '#bdc3c7';
        $newSettings['color_labels'] = '#bdc3c7';
        $newSettings['color_fieldsBg'] = '#ffffff';
        $newSettings['color_fields'] = '#bdc3c7';
        $newSettings['color_fieldsBorder'] = '#bdc3c7';
        $newSettings['color_fieldsBorderFocus'] = $settings->colorA;
        $newSettings['color_showCircleBg'] = '#ecf0f1';
        $newSettings['color_tooltipBg'] = '#34495e';
        $newSettings['color_tooltip'] = '#ffffff';
        $newSettings['color_btnSecBg'] = '#bdc3c7';
        $newSettings['color_btnSecTexts'] = '#ecf0f1';
        $newSettings['panelShadow'] = 1;
        $newSettings['playSoundOperator'] = $settings->playSound;
        $newSettings['playSoundCustomer'] = $settings->playSound;
        $newSettings['chatPosition'] = $settings->chatPosition;
        $newSettings['bounceFx'] = $settings->bounceFx;
        $newSettings['rolesAllowed'] = $settings->rolesAllowed;
        $newSettings['defaultImgAvatar'] = $settings->chatLogo;
        $newSettings['customerImgAvatar'] = $settings->chatDefaultPic;
        $newSettings['widthPanel'] = 280;
        $newSettings['heightPanel'] = 410;
        $newSettings['borderRadius'] = 5;
        $newSettings['showCloseBtn'] = $settings->enableCloseBtn;
        $newSettings['showFullscreenBtn'] = 1;
        $newSettings['showMinifyBtn'] = 1;
        $newSettings['contactFormIcon'] = 'fa-envelope';
        $newSettings['loginFormIcon'] = 'fa-user-circle';
        $newSettings['emailAdmin'] = $settings->adminEmail;
        $newSettings['emailSubject'] = $settings->emailSubject;
        $newSettings['usePoFile'] = $settings->usePoFile;
        $newSettings['enableGeolocalization'] = 1;

        $wpdb->query("DROP TABLE IF EXISTS $table_name");

        $sql = "CREATE TABLE $table_name (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                enableChat BOOL NOT NULL DEFAULT 1,
                enableVisitorsTracking BOOL NOT NULL DEFAULT 1,
                enableGeolocalization BOOL NOT NULL DEFAULT 1,
                trackingDelay FLOAT NOT NULL DEFAULT 20,
                ajaxCheckDelay FLOAT NOT NULL DEFAULT 8,
                allowFilesFromOperators BOOL NOT NULL DEFAULT 1,
                allowFilesFromCustomers BOOL NOT NULL DEFAULT 1,
                filesMaxSize SMALLINT(5) NOT NULL DEFAULT 5,
                allowedFiles VARCHAR(250) NOT NULL DEFAULT '.png,.jpg,.jpeg,.gif,.zip,.rar',
                operatorsFullHistory BOOL NOT NULL DEFAULT 1,
                enableLoginPanel BOOL NOT NULL DEFAULT 0,
                enableLoggedVisitorsOnly BOOL NOT NULL DEFAULT 0,
                defaultUsername VARCHAR(250) NOT NULL DEFAULT 'Visitor',
                enableContactForm BOOL NOT NULL DEFAULT 1,
                googleFont VARCHAR(250) NOT NULL DEFAULT 'Lato',
                color_btnBg VARCHAR(7) NOT NULL DEFAULT '#1abc9c',
                color_btnTexts VARCHAR(7) NOT NULL DEFAULT '#ffffff',
                color_bg VARCHAR(7) NOT NULL DEFAULT '#ffffff',
                color_texts VARCHAR(7) NOT NULL DEFAULT '#34495e',
                color_headerBg VARCHAR(7) NOT NULL DEFAULT '#1abc9c',
                color_headerTexts VARCHAR(7) NOT NULL DEFAULT '#ffffff',
                color_headerBtnBg VARCHAR(7) NOT NULL DEFAULT '#34495e',
                color_headerBtnTexts VARCHAR(7) NOT NULL DEFAULT '#ffffff',
                color_operatorBubbleBg VARCHAR(7) NOT NULL DEFAULT '#1abc9c',
                color_operatorBubbleTexts VARCHAR(7) NOT NULL DEFAULT '#ffffff',
                color_customerBubbleBg VARCHAR(7) NOT NULL DEFAULT '#ecf0f1',
                color_customerBubbleTexts VARCHAR(7) NOT NULL DEFAULT '#bdc3c7',
                color_shining VARCHAR(7) NOT NULL DEFAULT '#1abc9c',
                color_loaderBg  VARCHAR(7) NOT NULL DEFAULT '#1abc9c',
                color_loader  VARCHAR(7) NOT NULL DEFAULT '#ffffff',
                color_icons  VARCHAR(7) NOT NULL DEFAULT '#1abc9c',
                color_scrollBg  VARCHAR(7) NOT NULL DEFAULT '#ecf0f1',
                color_scroll  VARCHAR(7) NOT NULL DEFAULT '#bdc3c7',
                color_labels VARCHAR(7) NOT NULL DEFAULT '#bdc3c7',
                color_fieldsBg VARCHAR(7) NOT NULL DEFAULT '#ffffff',
                color_fields VARCHAR(7) NOT NULL DEFAULT '#bdc3c7',
                color_fieldsBorder VARCHAR(7) NOT NULL DEFAULT '#bdc3c7',
                color_fieldsBorderFocus VARCHAR(7) NOT NULL DEFAULT '#1abc9c',                
                color_showCircleBg  VARCHAR(7) NOT NULL DEFAULT '#ecf0f1',        
                color_tooltipBg VARCHAR(7) NOT NULL DEFAULT '#34495e',
                color_tooltip VARCHAR(7) NOT NULL DEFAULT '#FFFFFF',
                color_btnSecBg VARCHAR(7) NOT NULL DEFAULT '#bdc3c7',
                color_btnSecTexts VARCHAR(7) NOT NULL DEFAULT '#ffffff',
                panelShadow BOOL NOT NULL DEFAULT 1,
                playSoundOperator BOOL NOT NULL DEFAULT 1,
                playSoundCustomer BOOL NOT NULL DEFAULT 0,
                chatPosition VARCHAR(7) NOT NULL,
                bounceFx BOOL NOT NULL,
                rolesAllowed LONGTEXT NOT NULL,
                defaultImgAvatar VARCHAR(250) NOT NULL,
                customerImgAvatar VARCHAR(250) NOT NULL,
                chatLogo VARCHAR(250) NOT NULL,
                widthPanel SMALLINT(5) NOT NULL DEFAULT 280,
                heightPanel SMALLINT(5) NOT NULL DEFAULT 380,
                borderRadius SMALLINT(5) NOT NULL DEFAULT 5,
                showCloseBtn BOOL NOT NULL DEFAULT 1,
                showFullscreenBtn BOOL NOT NULL DEFAULT 1,
                showMinifyBtn BOOL NOT NULL DEFAULT 1,
                contactFormIcon VARCHAR(250) NOT NULL DEFAULT 'fa-envelope',
                loginFormIcon VARCHAR(250) NOT NULL DEFAULT 'fa-user-circle',
                emailAdmin VARCHAR(250) NOT NULL DEFAULT 'my@email.here',
                emailSubject VARCHAR(250) NOT NULL DEFAULT 'New message from your website',
                usePoFile BOOL NOT NULL DEFAULT 1,
                UNIQUE KEY id (id)
                ) $charset_collate;";
        dbDelta($sql);
        
        $wpdb->insert($table_name, $newSettings);
        
        // create canned Messages table
        $db_table_name = $wpdb->prefix . "vcht_cannedMessages";
        if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
            if (!empty($wpdb->charset))
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if (!empty($wpdb->collate))
                $charset_collate .= " COLLATE $wpdb->collate";

            $sql = "CREATE TABLE $db_table_name (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                keyB VARCHAR(16) NOT NULL DEFAULT 'shift',
                title VARCHAR(64) NOT NULL,
                content TEXT NOT NULL,
                shortcut VARCHAR(32) NOT NULL,
                createdByAdmin BOOL NOT NULL DEFAULT 0,
		UNIQUE KEY id (id)
		) $charset_collate;";
            dbDelta($sql);
        }


        $table_name = $wpdb->prefix . "vcht_sentences";
        $cannedMsgs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id ASC");
        foreach ($cannedMsgs as $msg) {
            $newMsg = array();
            $newMsg['keyB'] = 'shift';
            $newMsg['title'] = $msg->title;
            $newMsg['content'] = $msg->content;
            $newMsg['shortcut'] = $msg->shortcut;
            $newMsg['createdByAdmin'] = 1;
            $wpdb->insert($wpdb->prefix . 'vcht_cannedMessages', $newMsg);
        }


        $db_table_name = $wpdb->prefix . "vcht_texts";
        $texts = $wpdb->get_results("SELECT * FROM $db_table_name ORDER BY id ASC");
        foreach ($texts as $text) {
            if ($text->original == 'Email') {
                $wpdb->update($wpdb->prefix . 'vcht_fields', array('backendTitle' => $text->content, 'title' => $text->content, 'placeholder' => $text->content), array('infoType' => 'email'));
            }
            if ($text->original == 'Username') {
                $wpdb->update($wpdb->prefix . 'vcht_fields', array('backendTitle' => $text->content, 'title' => $text->content, 'placeholder' => $text->content), array('infoType' => 'username'));
            }
            if ($text->original == 'Write your message here') {
                $wpdb->update($wpdb->prefix . 'vcht_fields', array('backendTitle' => $text->content, 'title' => $text->content, 'placeholder' => $text->content), array('title' => 'Your message'));
            }
        }

        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "Drop files to upload here";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "You can not upload any more files";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "New message from your website";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "Yes";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "No";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "Shows an element of the website";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "[username] stopped the chat";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "Confirm";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "Transfer some files";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "Send the message";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "[username1] tranfers the chat to [username2]";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
                
        $wpdb->delete($db_table_name, array('original' => 'Enter your name'));
        $wpdb->delete($db_table_name, array('original' => 'Enter your email here'));
        $wpdb->delete($db_table_name, array('original' => 'Write your message here'));

        $wpdb->update($wpdb->prefix . 'vcht_texts', array('original' => 'Hello :)\nHow can we help you ?'),array('id' => 4));
        $wpdb->update($wpdb->prefix . 'vcht_texts', array('original' => 'Sorry, there is currently no operator online. Feel free to contact us by using the form below.'),array('id' => 6));
        
    }
    if (!$installed_ver || $installed_ver < 5.370) {            
        $table_name = $wpdb->prefix . "vcht_settings";
        $sql = "ALTER TABLE " . $table_name . " ADD purchaseCode  VARCHAR(250) NOT NULL DEFAULT '';";
        $wpdb->query($sql);

    }
    if (!$installed_ver || $installed_ver < 5.372) {        
        $table_name = $wpdb->prefix . "vcht_messages";
         $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table_name' AND column_name = 'transferID'"  );

         if(empty($row)){
            $sql = "ALTER TABLE " . $table_name . " ADD transferUsername VARCHAR(255) NOT NULL DEFAULT '';";
            $wpdb->query($sql);             
            $sql = "ALTER TABLE " . $table_name . " ADD transferID MEDIUMINT(9) DEFAULT 0;";
            $wpdb->query($sql);             
         }
    }
    if (!$installed_ver || $installed_ver < 5.376) { 
        $wpdb->update($wpdb->prefix . 'vcht_texts', array('original' => 'Hello! How can we help you ?'),array('id' => 4));       
        
    }
    if (!$installed_ver || $installed_ver < 5.383) {
        
        $upload_dir = trailingslashit(dirname(__FILE__)) . 'uploads';
        
        $rows = $wpdb->get_results('SELECT uploadFolderName FROM ' . $wpdb->prefix . 'vcht_users');
        foreach($rows as $row){   
            $fp = fopen($upload_dir . '/' . $row->uploadFolderName.'/.htaccess', 'w+');
            fwrite($fp, '<FilesMatch "\.(htaccess|htpasswd|ini|phps?|fla|psd|log|sh|zip|exe|pl|jsp|asp|htm|pht|phar|sh|cgi|py|php|php\.)$">'."\n");
            fwrite($fp, 'Order Allow,Deny'."\n");
            fwrite($fp, 'Deny from all'."\n");
            fwrite($fp, '</FilesMatch>');
            fclose($fp);  
        }

    }

    update_option("vcht_version", $version);
}

/**
 * Uninstallation.
 * @access  public
 * @since   1.0.0
 * @return  void
 */
function vcht_uninstall() {
    global $wpdb;
    global $jal_db_version;
    session_unset();

    $table_name = $wpdb->prefix . "vcht_settings";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "vcht_users";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "vcht_fields";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "vcht_messages";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "vcht_cannedMessages";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "vcht_texts";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");

    global $wp_roles;
    $wp_roles->remove_cap('administrator', 'visual_chat');
    remove_role('chat_operator');
}

VisualChat();
?>
