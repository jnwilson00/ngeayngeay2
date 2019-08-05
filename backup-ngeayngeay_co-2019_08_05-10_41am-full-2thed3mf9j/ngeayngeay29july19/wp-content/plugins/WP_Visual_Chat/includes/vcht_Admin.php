<?php
if (!defined('ABSPATH'))
    exit;

class vcht_Admin {

    /**
     * The single instance
     * @var    object
     * @access  private
     * @since    1.0.0
     */
    private static $_instance = null;

    /**
     * The main plugin object.
     * @var    object
     * @access  public
     * @since    1.0.0
     */
    public $parent = null;

    /**
     * Prefix for plugin settings.
     * @var     string
     * @access  public
     *
     * @since   1.0.0
     */
    public $base = '';

    /**
     * Available settings for plugin.
     * @var     array
     * @access  public
     * @since   1.0.0
     */
    public $settings = array();

    /**
     * The version number.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_version;

    /**
     * The token.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_token;

    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $file;

    /**
     * The main plugin directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $dir;

    /**
     * The plugin assets directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_dir;

    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_url;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $templates_url;

    public function __construct($parent) {
        $this->_token = 'vcht';
        $this->parent = $parent;
        $this->dir = dirname($parent->file);
        $this->uploads_dir = trailingslashit($this->dir) . 'uploads';
        $this->assets_dir = trailingslashit($this->dir) . 'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $parent->file)));
        $this->uploads_url = esc_url(trailingslashit(plugins_url('/uploads/', $parent->file)));

        add_action('admin_menu', array($this, 'addMenuItem'));
        add_action('admin_init', array($this, 'checkRoles'));
        add_action('admin_init', array($this, 'checkAutomaticUpdates'));

        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'), 10, 1);
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueStyles'), 10, 1);
        add_action('plugins_loaded', array($this, 'initLocalization'));
        add_action('wp_ajax_nopriv_vcht_operatorGetOnlineVisitors', array($this, 'ajax_operatorGetOnlineVisitors'));
        add_action('wp_ajax_vcht_operatorGetOnlineVisitors', array($this, 'ajax_operatorGetOnlineVisitors'));
        add_action('wp_ajax_nopriv_vcht_operatorLogIn', array($this, 'ajax_operatorLogIn'));
        add_action('wp_ajax_vcht_operatorLogIn', array($this, 'ajax_operatorLogIn'));
        add_action('wp_ajax_nopriv_vcht_operatorLogOut', array($this, 'ajax_operatorLogOut'));
        add_action('wp_ajax_vcht_operatorLogOut', array($this, 'ajax_operatorLogOut'));
        add_action('wp_ajax_nopriv_vcht_getUsersInfos', array($this, 'ajax_getUsersInfos'));
        add_action('wp_ajax_vcht_getUsersInfos', array($this, 'ajax_getUsersInfos'));
        add_action('wp_ajax_nopriv_vcht_sendMessage', array($this, 'ajax_sendMessage'));
        add_action('wp_ajax_vcht_sendMessage', array($this, 'ajax_sendMessage'));
        add_action('wp_ajax_nopriv_vcht_operatorGetNewMessages', array($this, 'ajax_operatorGetNewMessages'));
        add_action('wp_ajax_vcht_operatorGetNewMessages', array($this, 'ajax_operatorGetNewMessages'));
        add_action('wp_ajax_nopriv_vcht_loadSettings', array($this, 'ajax_loadSettings'));
        add_action('wp_ajax_vcht_loadSettings', array($this, 'ajax_loadSettings'));
        add_action('wp_ajax_nopriv_vcht_uploadFile', array($this, 'ajax_uploadFile'));
        add_action('wp_ajax_vcht_uploadFile', array($this, 'ajax_uploadFile'));
        add_action('wp_ajax_nopriv_vcht_getLastHistory', array($this, 'ajax_getLastHistory'));
        add_action('wp_ajax_vcht_getLastHistory', array($this, 'ajax_getLastHistory'));
        add_action('wp_ajax_nopriv_vcht_saveSettings', array($this, 'ajax_saveSettings'));
        add_action('wp_ajax_vcht_saveSettings', array($this, 'ajax_saveSettings'));
        add_action('wp_ajax_nopriv_vcht_getUserHistory', array($this, 'ajax_getUserHistory'));
        add_action('wp_ajax_vcht_getUserHistory', array($this, 'ajax_getUserHistory'));
        add_action('wp_ajax_nopriv_vcht_getFullHistory', array($this, 'ajax_getFullHistory'));
        add_action('wp_ajax_vcht_getFullHistory', array($this, 'ajax_getFullHistory'));
        add_action('wp_ajax_nopriv_vcht_deleteAllLogs', array($this, 'ajax_deleteAllLogs'));
        add_action('wp_ajax_vcht_deleteAllLogs', array($this, 'ajax_deleteAllLogs'));
        add_action('wp_ajax_nopriv_vcht_saveCannedMessage', array($this, 'ajax_saveCannedMessage'));
        add_action('wp_ajax_vcht_saveCannedMessage', array($this, 'ajax_saveCannedMessage'));
        add_action('wp_ajax_nopriv_vcht_getCannedMsgs', array($this, 'ajax_getCannedMsgs'));
        add_action('wp_ajax_vcht_getCannedMsgs', array($this, 'ajax_getCannedMsgs'));
        add_action('wp_ajax_nopriv_vcht_removeCannedMsg', array($this, 'ajax_removeCannedMsg'));
        add_action('wp_ajax_vcht_removeCannedMsg', array($this, 'ajax_removeCannedMsg'));
        add_action('wp_ajax_nopriv_vcht_getFields', array($this, 'ajax_getFields'));
        add_action('wp_ajax_vcht_getFields', array($this, 'ajax_getFields'));
        add_action('wp_ajax_nopriv_vcht_removeField', array($this, 'ajax_removeField'));
        add_action('wp_ajax_vcht_removeField', array($this, 'ajax_removeField'));
        add_action('wp_ajax_nopriv_vcht_changeFieldsOrders', array($this, 'ajax_changeFieldsOrders'));
        add_action('wp_ajax_vcht_changeFieldsOrders', array($this, 'ajax_changeFieldsOrders'));
        add_action('wp_ajax_nopriv_vcht_saveField', array($this, 'ajax_saveField'));
        add_action('wp_ajax_vcht_saveField', array($this, 'ajax_saveField'));
        add_action('wp_ajax_nopriv_vcht_saveAllowedRoles', array($this, 'ajax_saveAllowedRoles'));
        add_action('wp_ajax_vcht_saveAllowedRoles', array($this, 'ajax_saveAllowedRoles'));
        add_action('wp_ajax_nopriv_vcht_saveTexts', array($this, 'ajax_saveTexts'));
        add_action('wp_ajax_vcht_saveTexts', array($this, 'ajax_saveTexts'));
        add_action('wp_ajax_nopriv_vcht_acceptChat', array($this, 'ajax_acceptChat'));
        add_action('wp_ajax_vcht_acceptChat', array($this, 'ajax_acceptChat'));
        add_action('wp_ajax_nopriv_vcht_closeChat', array($this, 'ajax_closeChat'));
        add_action('wp_ajax_vcht_closeChat', array($this, 'ajax_closeChat'));
        add_action('wp_ajax_nopriv_vcht_saveUserAccount', array($this, 'ajax_saveUserAccount'));
        add_action('wp_ajax_vcht_saveUserAccount', array($this, 'ajax_saveUserAccount'));
        add_action('wp_ajax_nopriv_vcht_geolocalize', array($this, 'ajax_geolocalize'));
        add_action('wp_ajax_vcht_geolocalize', array($this, 'ajax_geolocalize'));
        add_action('wp_ajax_nopriv_vcht_transferChat', array($this, 'ajax_transferChat'));
        add_action('wp_ajax_vcht_transferChat', array($this, 'ajax_transferChat'));
        add_action('wp_ajax_nopriv_vcht_removeUserLogs', array($this, 'ajax_removeUserLogs'));
        add_action('wp_ajax_vcht_removeUserLogs', array($this, 'ajax_removeUserLogs'));
    }

    /*
     * Check roles capabilities
     */

    public function checkRoles() {
        global $wp_roles;
        $settings = $this->getSettings();
        $rolesNew = explode(',', $settings->rolesAllowed);
        foreach ($wp_roles->roles as $key => $role) {
            if (strtolower($role['name']) != 'administrator') {
                $name = strtolower($role['name']);
                $name = str_replace(" ", "_", $name);
                if (in_array($key, $rolesNew) || $role['name'] == "Chat Operator") {
                    $wp_roles->add_cap($key, 'visual_chat');
                } else {
                    $wp_roles->remove_cap($key, 'visual_chat');
                }
            }
        }
    }

    /**
     * Return settings.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function getSettings() {
        global $wpdb;
        $table_name = $wpdb->prefix . "vcht_settings";
        $settings = $wpdb->get_results("SELECT * FROM $table_name WHERE id=1 LIMIT 1");
        if ($settings[0]) {
            return $settings[0];
        } else {
            $wpdb->insert($table_name, array('id' => 1,
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
            'customerImgAvatar' => $this->assets_url . 'img/guest-128.png',
            'defaultImgAvatar' => $this->assets_url. 'img/administrator-2-128.png',
            'chatLogo' => $this->assets_url. 'img/chat-4-64.png'));
            $settings = $wpdb->get_results("SELECT * FROM $table_name WHERE id=1 LIMIT 1");
            return $settings[0];
            
        }
    }

    /**
     * Add menu to admin
     * @return void
     */
    public function addMenuItem() {
        add_menu_page(__('Visual Chat', 'WP_Visual_Chat'), 'Visual Chat', 'visual_chat', 'vcht-console', array($this, 'viewChatBackend'), 'dashicons-format-chat');
    }

    /*
     * Check for  updates
     */

    function checkAutomaticUpdates() {
        $settings = $this->getSettings();
        if ($settings && $settings->purchaseCode != "") {
            require_once('plugin_update_check.php');
            $updateChecker = new PluginUpdateChecker_2_0(
                    'https://kernl.us/api/v1/updates/56b8bc85201012a97c245f16/', $this->parent->file, 'vcht', 1
            );
            $updateChecker->purchaseCode = $settings->purchaseCode;
        }
    }

    /**
     * Load admin CSS files
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function adminEnqueueStyles($hook = '') {
        if (isset($_GET['page']) && $_GET['page'] == 'vcht-console') {
            wp_register_style($this->_token . '-bootstrap', esc_url($this->assets_url) . 'css/bootstrap.min.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-bootstrap');
            wp_register_style($this->_token . '-flat-ui', esc_url($this->assets_url) . 'css/flat-ui-pro.min.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-flat-ui');
            wp_register_style($this->_token . '-fontawesome', esc_url($this->assets_url) . 'css/font-awesome.min.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-fontawesome');
            wp_register_style($this->_token . '-customScrollbar', esc_url($this->assets_url) . 'css/jquery.mCustomScrollbar.min.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-customScrollbar');
            wp_register_style($this->_token . '-dropzone', esc_url($this->assets_url) . 'css/dropzone.min.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-dropzone');
            wp_register_style($this->_token . '-colpick', esc_url($this->assets_url) . 'css/colpick.min.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-colpick');
            wp_enqueue_style('thickbox');
            wp_register_style($this->_token . '-admin', esc_url($this->assets_url) . 'css/admin.min.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-admin');
        }
    }

    /**
     * Load admin JS files
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function adminEnqueueScripts($hook = '') {

        if (isset($_GET['page']) && $_GET['page'] == 'vcht-console') {
            global $wpdb;

            $settings = $this->getSettings();

            wp_register_script($this->_token . '-flat-ui', esc_url($this->assets_url) . 'js/flat-ui-pro.min.js', array(), $this->_version);
            wp_enqueue_script($this->_token . '-flat-ui');
            wp_register_script($this->_token . '-datatable', esc_url($this->parent->assets_url) . 'js/jquery.dataTables.min.js', array(), $this->_version);
            wp_enqueue_script($this->_token . '-datatable');
            wp_register_script($this->_token . '-bootstrap-datatable', esc_url($this->assets_url) . 'js/dataTables.bootstrap.min.js', array($this->_token . '-datatable'), $this->_version);
            wp_enqueue_script($this->_token . '-bootstrap-datatable');
            wp_register_script($this->_token . '-customScrollbar', esc_url($this->assets_url) . 'js/jquery.mCustomScrollbar.concat.min.js', array('jquery'));
            wp_enqueue_script($this->_token . '-customScrollbar');
            wp_register_script($this->_token . '-dropzone', esc_url($this->assets_url) . 'js/dropzone.min.js', array('jquery'));
            wp_enqueue_script($this->_token . '-dropzone');
            wp_register_script($this->_token . '-colpick', esc_url($this->assets_url) . 'js/colpick.min.js', array('jquery'), $this->_version);
            wp_enqueue_script($this->_token . '-colpick');
            wp_register_script($this->_token . '-mousetrap', esc_url($this->assets_url) . 'js/mousetrap.min.js', 'jquery', $this->_version);
            wp_enqueue_script($this->_token . '-mousetrap');
            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');
            wp_register_script($this->_token . '-admin', esc_url($this->assets_url) . 'js/admin.min.js', array('jquery',
                'jquery-ui-core',
                'jquery-ui-mouse',
                'jquery-ui-position',
                'jquery-ui-droppable',
                'jquery-ui-draggable',
                'jquery-ui-resizable',
                'jquery-ui-sortable',
                'jquery-effects-core',
                'jquery-effects-drop',
                'jquery-effects-fade',
                'jquery-effects-bounce',
                'jquery-effects-slide',
                'jquery-effects-blind'), $this->_version);
            wp_enqueue_script($this->_token . '-admin');

            $texts = array(
                'Yes' => __('Yes', 'WP_Visual_Chat'),
                'No' => __('No', 'WP_Visual_Chat'),
                'Online' => __('Online', 'WP_Visual_Chat'),
                'Offline' => __('Offline', 'WP_Visual_Chat'),
                'Log in' => __('Log in', 'WP_Visual_Chat'),
                'Log out' => __('Log out', 'WP_Visual_Chat'),
                'Drop files to upload here' => __('Drop files to upload here', 'WP_Visual_Chat'),
                'File is too big (max size: {{maxFilesize}}MB)' => __('File is too big (max size: {{maxFilesize}}MB)', 'WP_Visual_Chat'),
                'Invalid file type' => __('Invalid file type', 'WP_Visual_Chat'),
                'You can not upload any more files' => __('You can not upload any more files', 'WP_Visual_Chat'),
                'Shows an element of the website' => __('Shows an element of the website', 'WP_Visual_Chat'),
                'remove' => __('Remove', 'WP_Visual_Chat'),
                'display' => __('Display', 'WP_Visual_Chat'),
                'search' => __('Search', 'WP_Visual_Chat'),
                'showingPage' => sprintf(__('Showing page %1$s of %2$s', 'WP_Visual_Chat'), '_PAGE_', '_PAGES_'),
                'filteredFrom' => sprintf(__('- filtered from %1$s documents', 'WP_Visual_Chat'), '_MAX_'),
                'noRecords' => __('No documents to display', 'WP_Visual_Chat'),
                'Do you want to reply ?' => __('Do you want to reply ?', 'WP_Visual_Chat'),
                'New chat request from' => __('New chat request from', 'WP_Visual_Chat'),
                'Another operator has already answered' => __('Another operator has already answered', 'WP_Visual_Chat'),
                '[username] stopped the chat' => __('[username] stopped the chat', 'WP_Visual_Chat'),
                'Current operator' => __('Current operator', 'WP_Visual_Chat'),
                '[username1] tranfers the chat to [username2]' => __('[username1] tranfers the chat to [username2]', 'WP_Visual_Chat'),
            );
            global $wpdb;
            $userID = 0;
            $userWP = wp_get_current_user();
            $rows = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_users WHERE userID=" . $userWP->ID);
            $user = array();
            if (count($rows) == 0) {
                $uploadFolderName = md5(uniqid());
                $user = array(
                    'userID' => $userWP->ID,
                    'clientID' => md5(uniqid()),
                    'username' => $userWP->display_name,
                    'email' => $userWP->user_email,
                    'uploadFolderName' => $uploadFolderName,
                    'lastActivity' => date('Y-m-d H:i:s'),
                    'isOnline' => true, 'isOperator' => true,
                    'imgAvatar' => $this->assets_url . 'img/administrator-48.png',
                    'ip' => $_SERVER['REMOTE_ADDR']);

                $wpdb->insert($wpdb->prefix . 'vcht_users', $user);
                $user = (object) $user;
                $userID = $wpdb->insert_id;
            } else {
                $userID = $rows[0]->id;
            }
            wp_localize_script($this->_token . '-admin', 'vcht_data', array(
                'websiteUrl' => get_home_url(),
                'uploadsUrl' => $this->uploads_url,
                'assetsUrl' => $this->assets_url,
                'operatorID' => $user->ID,
                'trackingDelay' => $settings->trackingDelay,
                'ajaxCheckDelay' => $settings->ajaxCheckDelay,
                'allowFilesFromOperators' => $settings->allowFilesFromOperators,
                'filesMaxSize' => $settings->filesMaxSize,
                'allowedFiles' => $settings->allowedFiles,
                'isAdmin' => current_user_can('manage_options'),
                'enableGeolocalization' => $settings->enableGeolocalization,
                'userID' => $userID,
                'enableVisitorsTracking' => $settings->enableVisitorsTracking,
                'texts' => $texts));
        }
    }


    /*
     * Plugin init localization
     */

    public function initLocalization() {
        $moFiles = scandir(trailingslashit($this->dir) . 'languages/');
        foreach ($moFiles as $moFile) {
            if (strlen($moFile) > 3 && substr($moFile, -3) == '.mo' && strpos($moFile, get_locale()) > -1) {
                load_textdomain('WP_Visual_Chat', trailingslashit($this->dir) . 'languages/' . $moFile);
            }
        }
    }

    private function getIconsOptionsList() {
        return '<li><a href="javascript:" data-icon=""><i style="width: 22px; height: 22px;"></i></a></li><li><a href="javascript:" data-icon="fa-500px"> <i class="fa fa-500px"></i></a></li><li><a href="javascript:" data-icon="fa-address-book"> <i class="fa fa-address-book"></i></a></li><li><a href="javascript:" data-icon="fa-address-book-o"> <i class="fa fa-address-book-o"></i></a></li><li><a href="javascript:" data-icon="fa-address-card"> <i class="fa fa-address-card"></i></a></li><li><a href="javascript:" data-icon="fa-address-card-o"> <i class="fa fa-address-card-o"></i></a></li><li><a href="javascript:" data-icon="fa-adjust"> <i class="fa fa-adjust"></i></a></li><li><a href="javascript:" data-icon="fa-adn"> <i class="fa fa-adn"></i></a></li><li><a href="javascript:" data-icon="fa-align-center"> <i class="fa fa-align-center"></i></a></li><li><a href="javascript:" data-icon="fa-align-justify"> <i class="fa fa-align-justify"></i></a></li><li><a href="javascript:" data-icon="fa-align-left"> <i class="fa fa-align-left"></i></a></li><li><a href="javascript:" data-icon="fa-align-right"> <i class="fa fa-align-right"></i></a></li><li><a href="javascript:" data-icon="fa-amazon"> <i class="fa fa-amazon"></i></a></li><li><a href="javascript:" data-icon="fa-ambulance"> <i class="fa fa-ambulance"></i></a></li><li><a href="javascript:" data-icon="fa-american-sign-language-interpreting"> <i class="fa fa-american-sign-language-interpreting"></i></a></li><li><a href="javascript:" data-icon="fa-anchor"> <i class="fa fa-anchor"></i></a></li><li><a href="javascript:" data-icon="fa-android"> <i class="fa fa-android"></i></a></li><li><a href="javascript:" data-icon="fa-angellist"> <i class="fa fa-angellist"></i></a></li><li><a href="javascript:" data-icon="fa-angle-double-down"> <i class="fa fa-angle-double-down"></i></a></li><li><a href="javascript:" data-icon="fa-angle-double-left"> <i class="fa fa-angle-double-left"></i></a></li><li><a href="javascript:" data-icon="fa-angle-double-right"> <i class="fa fa-angle-double-right"></i></a></li><li><a href="javascript:" data-icon="fa-angle-double-up"> <i class="fa fa-angle-double-up"></i></a></li><li><a href="javascript:" data-icon="fa-angle-down"> <i class="fa fa-angle-down"></i></a></li><li><a href="javascript:" data-icon="fa-angle-left"> <i class="fa fa-angle-left"></i></a></li><li><a href="javascript:" data-icon="fa-angle-right"> <i class="fa fa-angle-right"></i></a></li><li><a href="javascript:" data-icon="fa-angle-up"> <i class="fa fa-angle-up"></i></a></li><li><a href="javascript:" data-icon="fa-apple"> <i class="fa fa-apple"></i></a></li><li><a href="javascript:" data-icon="fa-archive"> <i class="fa fa-archive"></i></a></li><li><a href="javascript:" data-icon="fa-area-chart"> <i class="fa fa-area-chart"></i></a></li><li><a href="javascript:" data-icon="fa-arrow-circle-down"> <i class="fa fa-arrow-circle-down"></i></a></li><li><a href="javascript:" data-icon="fa-arrow-circle-left"> <i class="fa fa-arrow-circle-left"></i></a></li><li><a href="javascript:" data-icon="fa-arrow-circle-o-down"> <i class="fa fa-arrow-circle-o-down"></i></a></li><li><a href="javascript:" data-icon="fa-arrow-circle-o-left"> <i class="fa fa-arrow-circle-o-left"></i></a></li><li><a href="javascript:" data-icon="fa-arrow-circle-o-right"> <i class="fa fa-arrow-circle-o-right"></i></a></li><li><a href="javascript:" data-icon="fa-arrow-circle-o-up"> <i class="fa fa-arrow-circle-o-up"></i></a></li><li><a href="javascript:" data-icon="fa-arrow-circle-right"> <i class="fa fa-arrow-circle-right"></i></a></li><li><a href="javascript:" data-icon="fa-arrow-circle-up"> <i class="fa fa-arrow-circle-up"></i></a></li><li><a href="javascript:" data-icon="fa-arrow-down"> <i class="fa fa-arrow-down"></i></a></li><li><a href="javascript:" data-icon="fa-arrow-left"> <i class="fa fa-arrow-left"></i></a></li><li><a href="javascript:" data-icon="fa-arrow-right"> <i class="fa fa-arrow-right"></i></a></li><li><a href="javascript:" data-icon="fa-arrow-up"> <i class="fa fa-arrow-up"></i></a></li><li><a href="javascript:" data-icon="fa-arrows"> <i class="fa fa-arrows"></i></a></li><li><a href="javascript:" data-icon="fa-arrows-alt"> <i class="fa fa-arrows-alt"></i></a></li><li><a href="javascript:" data-icon="fa-arrows-h"> <i class="fa fa-arrows-h"></i></a></li><li><a href="javascript:" data-icon="fa-arrows-v"> <i class="fa fa-arrows-v"></i></a></li><li><a href="javascript:" data-icon="fa-asl-interpreting"> <i class="fa fa-asl-interpreting"></i></a></li><li><a href="javascript:" data-icon="fa-assistive-listening-systems"> <i class="fa fa-assistive-listening-systems"></i></a></li><li><a href="javascript:" data-icon="fa-asterisk"> <i class="fa fa-asterisk"></i></a></li><li><a href="javascript:" data-icon="fa-at"> <i class="fa fa-at"></i></a></li><li><a href="javascript:" data-icon="fa-audio-description"> <i class="fa fa-audio-description"></i></a></li><li><a href="javascript:" data-icon="fa-automobile"> <i class="fa fa-automobile"></i></a></li><li><a href="javascript:" data-icon="fa-backward"> <i class="fa fa-backward"></i></a></li><li><a href="javascript:" data-icon="fa-balance-scale"> <i class="fa fa-balance-scale"></i></a></li><li><a href="javascript:" data-icon="fa-ban"> <i class="fa fa-ban"></i></a></li><li><a href="javascript:" data-icon="fa-bandcamp"> <i class="fa fa-bandcamp"></i></a></li><li><a href="javascript:" data-icon="fa-bank"> <i class="fa fa-bank"></i></a></li><li><a href="javascript:" data-icon="fa-bar-chart"> <i class="fa fa-bar-chart"></i></a></li><li><a href="javascript:" data-icon="fa-bar-chart-o"> <i class="fa fa-bar-chart-o"></i></a></li><li><a href="javascript:" data-icon="fa-barcode"> <i class="fa fa-barcode"></i></a></li><li><a href="javascript:" data-icon="fa-bars"> <i class="fa fa-bars"></i></a></li><li><a href="javascript:" data-icon="fa-bath"> <i class="fa fa-bath"></i></a></li><li><a href="javascript:" data-icon="fa-bathtub"> <i class="fa fa-bathtub"></i></a></li><li><a href="javascript:" data-icon="fa-battery"> <i class="fa fa-battery"></i></a></li><li><a href="javascript:" data-icon="fa-battery-0"> <i class="fa fa-battery-0"></i></a></li><li><a href="javascript:" data-icon="fa-battery-1"> <i class="fa fa-battery-1"></i></a></li><li><a href="javascript:" data-icon="fa-battery-2"> <i class="fa fa-battery-2"></i></a></li><li><a href="javascript:" data-icon="fa-battery-3"> <i class="fa fa-battery-3"></i></a></li><li><a href="javascript:" data-icon="fa-battery-4"> <i class="fa fa-battery-4"></i></a></li><li><a href="javascript:" data-icon="fa-battery-empty"> <i class="fa fa-battery-empty"></i></a></li><li><a href="javascript:" data-icon="fa-battery-full"> <i class="fa fa-battery-full"></i></a></li><li><a href="javascript:" data-icon="fa-battery-half"> <i class="fa fa-battery-half"></i></a></li><li><a href="javascript:" data-icon="fa-battery-quarter"> <i class="fa fa-battery-quarter"></i></a></li><li><a href="javascript:" data-icon="fa-battery-three-quarters"> <i class="fa fa-battery-three-quarters"></i></a></li><li><a href="javascript:" data-icon="fa-bed"> <i class="fa fa-bed"></i></a></li><li><a href="javascript:" data-icon="fa-beer"> <i class="fa fa-beer"></i></a></li><li><a href="javascript:" data-icon="fa-behance"> <i class="fa fa-behance"></i></a></li><li><a href="javascript:" data-icon="fa-behance-square"> <i class="fa fa-behance-square"></i></a></li><li><a href="javascript:" data-icon="fa-bell"> <i class="fa fa-bell"></i></a></li><li><a href="javascript:" data-icon="fa-bell-o"> <i class="fa fa-bell-o"></i></a></li><li><a href="javascript:" data-icon="fa-bell-slash"> <i class="fa fa-bell-slash"></i></a></li><li><a href="javascript:" data-icon="fa-bell-slash-o"> <i class="fa fa-bell-slash-o"></i></a></li><li><a href="javascript:" data-icon="fa-bicycle"> <i class="fa fa-bicycle"></i></a></li><li><a href="javascript:" data-icon="fa-binoculars"> <i class="fa fa-binoculars"></i></a></li><li><a href="javascript:" data-icon="fa-birthday-cake"> <i class="fa fa-birthday-cake"></i></a></li><li><a href="javascript:" data-icon="fa-bitbucket"> <i class="fa fa-bitbucket"></i></a></li><li><a href="javascript:" data-icon="fa-bitbucket-square"> <i class="fa fa-bitbucket-square"></i></a></li><li><a href="javascript:" data-icon="fa-bitcoin"> <i class="fa fa-bitcoin"></i></a></li><li><a href="javascript:" data-icon="fa-black-tie"> <i class="fa fa-black-tie"></i></a></li><li><a href="javascript:" data-icon="fa-blind"> <i class="fa fa-blind"></i></a></li><li><a href="javascript:" data-icon="fa-bluetooth"> <i class="fa fa-bluetooth"></i></a></li><li><a href="javascript:" data-icon="fa-bluetooth-b"> <i class="fa fa-bluetooth-b"></i></a></li><li><a href="javascript:" data-icon="fa-bold"> <i class="fa fa-bold"></i></a></li><li><a href="javascript:" data-icon="fa-bolt"> <i class="fa fa-bolt"></i></a></li><li><a href="javascript:" data-icon="fa-bomb"> <i class="fa fa-bomb"></i></a></li><li><a href="javascript:" data-icon="fa-book"> <i class="fa fa-book"></i></a></li><li><a href="javascript:" data-icon="fa-bookmark"> <i class="fa fa-bookmark"></i></a></li><li><a href="javascript:" data-icon="fa-bookmark-o"> <i class="fa fa-bookmark-o"></i></a></li><li><a href="javascript:" data-icon="fa-braille"> <i class="fa fa-braille"></i></a></li><li><a href="javascript:" data-icon="fa-briefcase"> <i class="fa fa-briefcase"></i></a></li><li><a href="javascript:" data-icon="fa-btc"> <i class="fa fa-btc"></i></a></li><li><a href="javascript:" data-icon="fa-bug"> <i class="fa fa-bug"></i></a></li><li><a href="javascript:" data-icon="fa-building"> <i class="fa fa-building"></i></a></li><li><a href="javascript:" data-icon="fa-building-o"> <i class="fa fa-building-o"></i></a></li><li><a href="javascript:" data-icon="fa-bullhorn"> <i class="fa fa-bullhorn"></i></a></li><li><a href="javascript:" data-icon="fa-bullseye"> <i class="fa fa-bullseye"></i></a></li><li><a href="javascript:" data-icon="fa-bus"> <i class="fa fa-bus"></i></a></li><li><a href="javascript:" data-icon="fa-buysellads"> <i class="fa fa-buysellads"></i></a></li><li><a href="javascript:" data-icon="fa-cab"> <i class="fa fa-cab"></i></a></li><li><a href="javascript:" data-icon="fa-calculator"> <i class="fa fa-calculator"></i></a></li><li><a href="javascript:" data-icon="fa-calendar"> <i class="fa fa-calendar"></i></a></li><li><a href="javascript:" data-icon="fa-calendar-check-o"> <i class="fa fa-calendar-check-o"></i></a></li><li><a href="javascript:" data-icon="fa-calendar-minus-o"> <i class="fa fa-calendar-minus-o"></i></a></li><li><a href="javascript:" data-icon="fa-calendar-o"> <i class="fa fa-calendar-o"></i></a></li><li><a href="javascript:" data-icon="fa-calendar-plus-o"> <i class="fa fa-calendar-plus-o"></i></a></li><li><a href="javascript:" data-icon="fa-calendar-times-o"> <i class="fa fa-calendar-times-o"></i></a></li><li><a href="javascript:" data-icon="fa-camera"> <i class="fa fa-camera"></i></a></li><li><a href="javascript:" data-icon="fa-camera-retro"> <i class="fa fa-camera-retro"></i></a></li><li><a href="javascript:" data-icon="fa-car"> <i class="fa fa-car"></i></a></li><li><a href="javascript:" data-icon="fa-caret-down"> <i class="fa fa-caret-down"></i></a></li><li><a href="javascript:" data-icon="fa-caret-left"> <i class="fa fa-caret-left"></i></a></li><li><a href="javascript:" data-icon="fa-caret-right"> <i class="fa fa-caret-right"></i></a></li><li><a href="javascript:" data-icon="fa-caret-square-o-down"> <i class="fa fa-caret-square-o-down"></i></a></li><li><a href="javascript:" data-icon="fa-caret-square-o-left"> <i class="fa fa-caret-square-o-left"></i></a></li><li><a href="javascript:" data-icon="fa-caret-square-o-right"> <i class="fa fa-caret-square-o-right"></i></a></li><li><a href="javascript:" data-icon="fa-caret-square-o-up"> <i class="fa fa-caret-square-o-up"></i></a></li><li><a href="javascript:" data-icon="fa-caret-up"> <i class="fa fa-caret-up"></i></a></li><li><a href="javascript:" data-icon="fa-cart-arrow-down"> <i class="fa fa-cart-arrow-down"></i></a></li><li><a href="javascript:" data-icon="fa-cart-plus"> <i class="fa fa-cart-plus"></i></a></li><li><a href="javascript:" data-icon="fa-cc"> <i class="fa fa-cc"></i></a></li><li><a href="javascript:" data-icon="fa-cc-amex"> <i class="fa fa-cc-amex"></i></a></li><li><a href="javascript:" data-icon="fa-cc-diners-club"> <i class="fa fa-cc-diners-club"></i></a></li><li><a href="javascript:" data-icon="fa-cc-discover"> <i class="fa fa-cc-discover"></i></a></li><li><a href="javascript:" data-icon="fa-cc-jcb"> <i class="fa fa-cc-jcb"></i></a></li><li><a href="javascript:" data-icon="fa-cc-mastercard"> <i class="fa fa-cc-mastercard"></i></a></li><li><a href="javascript:" data-icon="fa-cc-paypal"> <i class="fa fa-cc-paypal"></i></a></li><li><a href="javascript:" data-icon="fa-cc-stripe"> <i class="fa fa-cc-stripe"></i></a></li><li><a href="javascript:" data-icon="fa-cc-visa"> <i class="fa fa-cc-visa"></i></a></li><li><a href="javascript:" data-icon="fa-certificate"> <i class="fa fa-certificate"></i></a></li><li><a href="javascript:" data-icon="fa-chain"> <i class="fa fa-chain"></i></a></li><li><a href="javascript:" data-icon="fa-chain-broken"> <i class="fa fa-chain-broken"></i></a></li><li><a href="javascript:" data-icon="fa-check"> <i class="fa fa-check"></i></a></li><li><a href="javascript:" data-icon="fa-check-circle"> <i class="fa fa-check-circle"></i></a></li><li><a href="javascript:" data-icon="fa-check-circle-o"> <i class="fa fa-check-circle-o"></i></a></li><li><a href="javascript:" data-icon="fa-check-square"> <i class="fa fa-check-square"></i></a></li><li><a href="javascript:" data-icon="fa-check-square-o"> <i class="fa fa-check-square-o"></i></a></li><li><a href="javascript:" data-icon="fa-chevron-circle-down"> <i class="fa fa-chevron-circle-down"></i></a></li><li><a href="javascript:" data-icon="fa-chevron-circle-left"> <i class="fa fa-chevron-circle-left"></i></a></li><li><a href="javascript:" data-icon="fa-chevron-circle-right"> <i class="fa fa-chevron-circle-right"></i></a></li><li><a href="javascript:" data-icon="fa-chevron-circle-up"> <i class="fa fa-chevron-circle-up"></i></a></li><li><a href="javascript:" data-icon="fa-chevron-down"> <i class="fa fa-chevron-down"></i></a></li><li><a href="javascript:" data-icon="fa-chevron-left"> <i class="fa fa-chevron-left"></i></a></li><li><a href="javascript:" data-icon="fa-chevron-right"> <i class="fa fa-chevron-right"></i></a></li><li><a href="javascript:" data-icon="fa-chevron-up"> <i class="fa fa-chevron-up"></i></a></li><li><a href="javascript:" data-icon="fa-child"> <i class="fa fa-child"></i></a></li><li><a href="javascript:" data-icon="fa-chrome"> <i class="fa fa-chrome"></i></a></li><li><a href="javascript:" data-icon="fa-circle"> <i class="fa fa-circle"></i></a></li><li><a href="javascript:" data-icon="fa-circle-o"> <i class="fa fa-circle-o"></i></a></li><li><a href="javascript:" data-icon="fa-circle-o-notch"> <i class="fa fa-circle-o-notch"></i></a></li><li><a href="javascript:" data-icon="fa-circle-thin"> <i class="fa fa-circle-thin"></i></a></li><li><a href="javascript:" data-icon="fa-clipboard"> <i class="fa fa-clipboard"></i></a></li><li><a href="javascript:" data-icon="fa-clock-o"> <i class="fa fa-clock-o"></i></a></li><li><a href="javascript:" data-icon="fa-clone"> <i class="fa fa-clone"></i></a></li><li><a href="javascript:" data-icon="fa-close"> <i class="fa fa-close"></i></a></li><li><a href="javascript:" data-icon="fa-cloud"> <i class="fa fa-cloud"></i></a></li><li><a href="javascript:" data-icon="fa-cloud-download"> <i class="fa fa-cloud-download"></i></a></li><li><a href="javascript:" data-icon="fa-cloud-upload"> <i class="fa fa-cloud-upload"></i></a></li><li><a href="javascript:" data-icon="fa-cny"> <i class="fa fa-cny"></i></a></li><li><a href="javascript:" data-icon="fa-code"> <i class="fa fa-code"></i></a></li><li><a href="javascript:" data-icon="fa-code-fork"> <i class="fa fa-code-fork"></i></a></li><li><a href="javascript:" data-icon="fa-codepen"> <i class="fa fa-codepen"></i></a></li><li><a href="javascript:" data-icon="fa-codiepie"> <i class="fa fa-codiepie"></i></a></li><li><a href="javascript:" data-icon="fa-coffee"> <i class="fa fa-coffee"></i></a></li><li><a href="javascript:" data-icon="fa-cog"> <i class="fa fa-cog"></i></a></li><li><a href="javascript:" data-icon="fa-cogs"> <i class="fa fa-cogs"></i></a></li><li><a href="javascript:" data-icon="fa-columns"> <i class="fa fa-columns"></i></a></li><li><a href="javascript:" data-icon="fa-comment"> <i class="fa fa-comment"></i></a></li><li><a href="javascript:" data-icon="fa-comment-o"> <i class="fa fa-comment-o"></i></a></li><li><a href="javascript:" data-icon="fa-commenting"> <i class="fa fa-commenting"></i></a></li><li><a href="javascript:" data-icon="fa-commenting-o"> <i class="fa fa-commenting-o"></i></a></li><li><a href="javascript:" data-icon="fa-comments"> <i class="fa fa-comments"></i></a></li><li><a href="javascript:" data-icon="fa-comments-o"> <i class="fa fa-comments-o"></i></a></li><li><a href="javascript:" data-icon="fa-compass"> <i class="fa fa-compass"></i></a></li><li><a href="javascript:" data-icon="fa-compress"> <i class="fa fa-compress"></i></a></li><li><a href="javascript:" data-icon="fa-connectdevelop"> <i class="fa fa-connectdevelop"></i></a></li><li><a href="javascript:" data-icon="fa-contao"> <i class="fa fa-contao"></i></a></li><li><a href="javascript:" data-icon="fa-copy"> <i class="fa fa-copy"></i></a></li><li><a href="javascript:" data-icon="fa-copyright"> <i class="fa fa-copyright"></i></a></li><li><a href="javascript:" data-icon="fa-creative-commons"> <i class="fa fa-creative-commons"></i></a></li><li><a href="javascript:" data-icon="fa-credit-card"> <i class="fa fa-credit-card"></i></a></li><li><a href="javascript:" data-icon="fa-credit-card-alt"> <i class="fa fa-credit-card-alt"></i></a></li><li><a href="javascript:" data-icon="fa-crop"> <i class="fa fa-crop"></i></a></li><li><a href="javascript:" data-icon="fa-crosshairs"> <i class="fa fa-crosshairs"></i></a></li><li><a href="javascript:" data-icon="fa-css3"> <i class="fa fa-css3"></i></a></li><li><a href="javascript:" data-icon="fa-cube"> <i class="fa fa-cube"></i></a></li><li><a href="javascript:" data-icon="fa-cubes"> <i class="fa fa-cubes"></i></a></li><li><a href="javascript:" data-icon="fa-cut"> <i class="fa fa-cut"></i></a></li><li><a href="javascript:" data-icon="fa-cutlery"> <i class="fa fa-cutlery"></i></a></li><li><a href="javascript:" data-icon="fa-dashboard"> <i class="fa fa-dashboard"></i></a></li><li><a href="javascript:" data-icon="fa-dashcube"> <i class="fa fa-dashcube"></i></a></li><li><a href="javascript:" data-icon="fa-database"> <i class="fa fa-database"></i></a></li><li><a href="javascript:" data-icon="fa-deaf"> <i class="fa fa-deaf"></i></a></li><li><a href="javascript:" data-icon="fa-deafness"> <i class="fa fa-deafness"></i></a></li><li><a href="javascript:" data-icon="fa-dedent"> <i class="fa fa-dedent"></i></a></li><li><a href="javascript:" data-icon="fa-delicious"> <i class="fa fa-delicious"></i></a></li><li><a href="javascript:" data-icon="fa-desktop"> <i class="fa fa-desktop"></i></a></li><li><a href="javascript:" data-icon="fa-deviantart"> <i class="fa fa-deviantart"></i></a></li><li><a href="javascript:" data-icon="fa-diamond"> <i class="fa fa-diamond"></i></a></li><li><a href="javascript:" data-icon="fa-digg"> <i class="fa fa-digg"></i></a></li><li><a href="javascript:" data-icon="fa-dollar"> <i class="fa fa-dollar"></i></a></li><li><a href="javascript:" data-icon="fa-dot-circle-o"> <i class="fa fa-dot-circle-o"></i></a></li><li><a href="javascript:" data-icon="fa-download"> <i class="fa fa-download"></i></a></li><li><a href="javascript:" data-icon="fa-dribbble"> <i class="fa fa-dribbble"></i></a></li><li><a href="javascript:" data-icon="fa-drivers-license"> <i class="fa fa-drivers-license"></i></a></li><li><a href="javascript:" data-icon="fa-drivers-license-o"> <i class="fa fa-drivers-license-o"></i></a></li><li><a href="javascript:" data-icon="fa-dropbox"> <i class="fa fa-dropbox"></i></a></li><li><a href="javascript:" data-icon="fa-drupal"> <i class="fa fa-drupal"></i></a></li><li><a href="javascript:" data-icon="fa-edge"> <i class="fa fa-edge"></i></a></li><li><a href="javascript:" data-icon="fa-edit"> <i class="fa fa-edit"></i></a></li><li><a href="javascript:" data-icon="fa-eercast"> <i class="fa fa-eercast"></i></a></li><li><a href="javascript:" data-icon="fa-eject"> <i class="fa fa-eject"></i></a></li><li><a href="javascript:" data-icon="fa-ellipsis-h"> <i class="fa fa-ellipsis-h"></i></a></li><li><a href="javascript:" data-icon="fa-ellipsis-v"> <i class="fa fa-ellipsis-v"></i></a></li><li><a href="javascript:" data-icon="fa-empire"> <i class="fa fa-empire"></i></a></li><li><a href="javascript:" data-icon="fa-envelope"> <i class="fa fa-envelope"></i></a></li><li><a href="javascript:" data-icon="fa-envelope-o"> <i class="fa fa-envelope-o"></i></a></li><li><a href="javascript:" data-icon="fa-envelope-open"> <i class="fa fa-envelope-open"></i></a></li><li><a href="javascript:" data-icon="fa-envelope-open-o"> <i class="fa fa-envelope-open-o"></i></a></li><li><a href="javascript:" data-icon="fa-envelope-square"> <i class="fa fa-envelope-square"></i></a></li><li><a href="javascript:" data-icon="fa-envira"> <i class="fa fa-envira"></i></a></li><li><a href="javascript:" data-icon="fa-eraser"> <i class="fa fa-eraser"></i></a></li><li><a href="javascript:" data-icon="fa-etsy"> <i class="fa fa-etsy"></i></a></li><li><a href="javascript:" data-icon="fa-eur"> <i class="fa fa-eur"></i></a></li><li><a href="javascript:" data-icon="fa-euro"> <i class="fa fa-euro"></i></a></li><li><a href="javascript:" data-icon="fa-exchange"> <i class="fa fa-exchange"></i></a></li><li><a href="javascript:" data-icon="fa-exclamation"> <i class="fa fa-exclamation"></i></a></li><li><a href="javascript:" data-icon="fa-exclamation-circle"> <i class="fa fa-exclamation-circle"></i></a></li><li><a href="javascript:" data-icon="fa-exclamation-triangle"> <i class="fa fa-exclamation-triangle"></i></a></li><li><a href="javascript:" data-icon="fa-expand"> <i class="fa fa-expand"></i></a></li><li><a href="javascript:" data-icon="fa-expeditedssl"> <i class="fa fa-expeditedssl"></i></a></li><li><a href="javascript:" data-icon="fa-external-link"> <i class="fa fa-external-link"></i></a></li><li><a href="javascript:" data-icon="fa-external-link-square"> <i class="fa fa-external-link-square"></i></a></li><li><a href="javascript:" data-icon="fa-eye"> <i class="fa fa-eye"></i></a></li><li><a href="javascript:" data-icon="fa-eye-slash"> <i class="fa fa-eye-slash"></i></a></li><li><a href="javascript:" data-icon="fa-eyedropper"> <i class="fa fa-eyedropper"></i></a></li><li><a href="javascript:" data-icon="fa-fa"> <i class="fa fa-fa"></i></a></li><li><a href="javascript:" data-icon="fa-facebook"> <i class="fa fa-facebook"></i></a></li><li><a href="javascript:" data-icon="fa-facebook-f"> <i class="fa fa-facebook-f"></i></a></li><li><a href="javascript:" data-icon="fa-facebook-official"> <i class="fa fa-facebook-official"></i></a></li><li><a href="javascript:" data-icon="fa-facebook-square"> <i class="fa fa-facebook-square"></i></a></li><li><a href="javascript:" data-icon="fa-fast-backward"> <i class="fa fa-fast-backward"></i></a></li><li><a href="javascript:" data-icon="fa-fast-forward"> <i class="fa fa-fast-forward"></i></a></li><li><a href="javascript:" data-icon="fa-fax"> <i class="fa fa-fax"></i></a></li><li><a href="javascript:" data-icon="fa-feed"> <i class="fa fa-feed"></i></a></li><li><a href="javascript:" data-icon="fa-female"> <i class="fa fa-female"></i></a></li><li><a href="javascript:" data-icon="fa-fighter-jet"> <i class="fa fa-fighter-jet"></i></a></li><li><a href="javascript:" data-icon="fa-file"> <i class="fa fa-file"></i></a></li><li><a href="javascript:" data-icon="fa-file-archive-o"> <i class="fa fa-file-archive-o"></i></a></li><li><a href="javascript:" data-icon="fa-file-audio-o"> <i class="fa fa-file-audio-o"></i></a></li><li><a href="javascript:" data-icon="fa-file-code-o"> <i class="fa fa-file-code-o"></i></a></li><li><a href="javascript:" data-icon="fa-file-excel-o"> <i class="fa fa-file-excel-o"></i></a></li><li><a href="javascript:" data-icon="fa-file-image-o"> <i class="fa fa-file-image-o"></i></a></li><li><a href="javascript:" data-icon="fa-file-movie-o"> <i class="fa fa-file-movie-o"></i></a></li><li><a href="javascript:" data-icon="fa-file-o"> <i class="fa fa-file-o"></i></a></li><li><a href="javascript:" data-icon="fa-file-pdf-o"> <i class="fa fa-file-pdf-o"></i></a></li><li><a href="javascript:" data-icon="fa-file-photo-o"> <i class="fa fa-file-photo-o"></i></a></li><li><a href="javascript:" data-icon="fa-file-picture-o"> <i class="fa fa-file-picture-o"></i></a></li><li><a href="javascript:" data-icon="fa-file-powerpoint-o"> <i class="fa fa-file-powerpoint-o"></i></a></li><li><a href="javascript:" data-icon="fa-file-sound-o"> <i class="fa fa-file-sound-o"></i></a></li><li><a href="javascript:" data-icon="fa-file-text"> <i class="fa fa-file-text"></i></a></li><li><a href="javascript:" data-icon="fa-file-text-o"> <i class="fa fa-file-text-o"></i></a></li><li><a href="javascript:" data-icon="fa-file-video-o"> <i class="fa fa-file-video-o"></i></a></li><li><a href="javascript:" data-icon="fa-file-word-o"> <i class="fa fa-file-word-o"></i></a></li><li><a href="javascript:" data-icon="fa-file-zip-o"> <i class="fa fa-file-zip-o"></i></a></li><li><a href="javascript:" data-icon="fa-files-o"> <i class="fa fa-files-o"></i></a></li><li><a href="javascript:" data-icon="fa-film"> <i class="fa fa-film"></i></a></li><li><a href="javascript:" data-icon="fa-filter"> <i class="fa fa-filter"></i></a></li><li><a href="javascript:" data-icon="fa-fire"> <i class="fa fa-fire"></i></a></li><li><a href="javascript:" data-icon="fa-fire-extinguisher"> <i class="fa fa-fire-extinguisher"></i></a></li><li><a href="javascript:" data-icon="fa-firefox"> <i class="fa fa-firefox"></i></a></li><li><a href="javascript:" data-icon="fa-first-order"> <i class="fa fa-first-order"></i></a></li><li><a href="javascript:" data-icon="fa-flag"> <i class="fa fa-flag"></i></a></li><li><a href="javascript:" data-icon="fa-flag-checkered"> <i class="fa fa-flag-checkered"></i></a></li><li><a href="javascript:" data-icon="fa-flag-o"> <i class="fa fa-flag-o"></i></a></li><li><a href="javascript:" data-icon="fa-flash"> <i class="fa fa-flash"></i></a></li><li><a href="javascript:" data-icon="fa-flask"> <i class="fa fa-flask"></i></a></li><li><a href="javascript:" data-icon="fa-flickr"> <i class="fa fa-flickr"></i></a></li><li><a href="javascript:" data-icon="fa-floppy-o"> <i class="fa fa-floppy-o"></i></a></li><li><a href="javascript:" data-icon="fa-folder"> <i class="fa fa-folder"></i></a></li><li><a href="javascript:" data-icon="fa-folder-o"> <i class="fa fa-folder-o"></i></a></li><li><a href="javascript:" data-icon="fa-folder-open"> <i class="fa fa-folder-open"></i></a></li><li><a href="javascript:" data-icon="fa-folder-open-o"> <i class="fa fa-folder-open-o"></i></a></li><li><a href="javascript:" data-icon="fa-font"> <i class="fa fa-font"></i></a></li><li><a href="javascript:" data-icon="fa-font-awesome"> <i class="fa fa-font-awesome"></i></a></li><li><a href="javascript:" data-icon="fa-fonticons"> <i class="fa fa-fonticons"></i></a></li><li><a href="javascript:" data-icon="fa-fort-awesome"> <i class="fa fa-fort-awesome"></i></a></li><li><a href="javascript:" data-icon="fa-forumbee"> <i class="fa fa-forumbee"></i></a></li><li><a href="javascript:" data-icon="fa-forward"> <i class="fa fa-forward"></i></a></li><li><a href="javascript:" data-icon="fa-foursquare"> <i class="fa fa-foursquare"></i></a></li><li><a href="javascript:" data-icon="fa-free-code-camp"> <i class="fa fa-free-code-camp"></i></a></li><li><a href="javascript:" data-icon="fa-frown-o"> <i class="fa fa-frown-o"></i></a></li><li><a href="javascript:" data-icon="fa-futbol-o"> <i class="fa fa-futbol-o"></i></a></li><li><a href="javascript:" data-icon="fa-gamepad"> <i class="fa fa-gamepad"></i></a></li><li><a href="javascript:" data-icon="fa-gavel"> <i class="fa fa-gavel"></i></a></li><li><a href="javascript:" data-icon="fa-gbp"> <i class="fa fa-gbp"></i></a></li><li><a href="javascript:" data-icon="fa-ge"> <i class="fa fa-ge"></i></a></li><li><a href="javascript:" data-icon="fa-gear"> <i class="fa fa-gear"></i></a></li><li><a href="javascript:" data-icon="fa-gears"> <i class="fa fa-gears"></i></a></li><li><a href="javascript:" data-icon="fa-genderless"> <i class="fa fa-genderless"></i></a></li><li><a href="javascript:" data-icon="fa-get-pocket"> <i class="fa fa-get-pocket"></i></a></li><li><a href="javascript:" data-icon="fa-gg"> <i class="fa fa-gg"></i></a></li><li><a href="javascript:" data-icon="fa-gg-circle"> <i class="fa fa-gg-circle"></i></a></li><li><a href="javascript:" data-icon="fa-gift"> <i class="fa fa-gift"></i></a></li><li><a href="javascript:" data-icon="fa-git"> <i class="fa fa-git"></i></a></li><li><a href="javascript:" data-icon="fa-git-square"> <i class="fa fa-git-square"></i></a></li><li><a href="javascript:" data-icon="fa-github"> <i class="fa fa-github"></i></a></li><li><a href="javascript:" data-icon="fa-github-alt"> <i class="fa fa-github-alt"></i></a></li><li><a href="javascript:" data-icon="fa-github-square"> <i class="fa fa-github-square"></i></a></li><li><a href="javascript:" data-icon="fa-gitlab"> <i class="fa fa-gitlab"></i></a></li><li><a href="javascript:" data-icon="fa-gittip"> <i class="fa fa-gittip"></i></a></li><li><a href="javascript:" data-icon="fa-glass"> <i class="fa fa-glass"></i></a></li><li><a href="javascript:" data-icon="fa-glide"> <i class="fa fa-glide"></i></a></li><li><a href="javascript:" data-icon="fa-glide-g"> <i class="fa fa-glide-g"></i></a></li><li><a href="javascript:" data-icon="fa-globe"> <i class="fa fa-globe"></i></a></li><li><a href="javascript:" data-icon="fa-google"> <i class="fa fa-google"></i></a></li><li><a href="javascript:" data-icon="fa-google-plus"> <i class="fa fa-google-plus"></i></a></li><li><a href="javascript:" data-icon="fa-google-plus-circle"> <i class="fa fa-google-plus-circle"></i></a></li><li><a href="javascript:" data-icon="fa-google-plus-official"> <i class="fa fa-google-plus-official"></i></a></li><li><a href="javascript:" data-icon="fa-google-plus-square"> <i class="fa fa-google-plus-square"></i></a></li><li><a href="javascript:" data-icon="fa-google-wallet"> <i class="fa fa-google-wallet"></i></a></li><li><a href="javascript:" data-icon="fa-graduation-cap"> <i class="fa fa-graduation-cap"></i></a></li><li><a href="javascript:" data-icon="fa-gratipay"> <i class="fa fa-gratipay"></i></a></li><li><a href="javascript:" data-icon="fa-grav"> <i class="fa fa-grav"></i></a></li><li><a href="javascript:" data-icon="fa-group"> <i class="fa fa-group"></i></a></li><li><a href="javascript:" data-icon="fa-h-square"> <i class="fa fa-h-square"></i></a></li><li><a href="javascript:" data-icon="fa-hacker-news"> <i class="fa fa-hacker-news"></i></a></li><li><a href="javascript:" data-icon="fa-hand-grab-o"> <i class="fa fa-hand-grab-o"></i></a></li><li><a href="javascript:" data-icon="fa-hand-lizard-o"> <i class="fa fa-hand-lizard-o"></i></a></li><li><a href="javascript:" data-icon="fa-hand-o-down"> <i class="fa fa-hand-o-down"></i></a></li><li><a href="javascript:" data-icon="fa-hand-o-left"> <i class="fa fa-hand-o-left"></i></a></li><li><a href="javascript:" data-icon="fa-hand-o-right"> <i class="fa fa-hand-o-right"></i></a></li><li><a href="javascript:" data-icon="fa-hand-o-up"> <i class="fa fa-hand-o-up"></i></a></li><li><a href="javascript:" data-icon="fa-hand-paper-o"> <i class="fa fa-hand-paper-o"></i></a></li><li><a href="javascript:" data-icon="fa-hand-peace-o"> <i class="fa fa-hand-peace-o"></i></a></li><li><a href="javascript:" data-icon="fa-hand-pointer-o"> <i class="fa fa-hand-pointer-o"></i></a></li><li><a href="javascript:" data-icon="fa-hand-rock-o"> <i class="fa fa-hand-rock-o"></i></a></li><li><a href="javascript:" data-icon="fa-hand-scissors-o"> <i class="fa fa-hand-scissors-o"></i></a></li><li><a href="javascript:" data-icon="fa-hand-spock-o"> <i class="fa fa-hand-spock-o"></i></a></li><li><a href="javascript:" data-icon="fa-hand-stop-o"> <i class="fa fa-hand-stop-o"></i></a></li><li><a href="javascript:" data-icon="fa-handshake-o"> <i class="fa fa-handshake-o"></i></a></li><li><a href="javascript:" data-icon="fa-hard-of-hearing"> <i class="fa fa-hard-of-hearing"></i></a></li><li><a href="javascript:" data-icon="fa-hashtag"> <i class="fa fa-hashtag"></i></a></li><li><a href="javascript:" data-icon="fa-hdd-o"> <i class="fa fa-hdd-o"></i></a></li><li><a href="javascript:" data-icon="fa-header"> <i class="fa fa-header"></i></a></li><li><a href="javascript:" data-icon="fa-headphones"> <i class="fa fa-headphones"></i></a></li><li><a href="javascript:" data-icon="fa-heart"> <i class="fa fa-heart"></i></a></li><li><a href="javascript:" data-icon="fa-heart-o"> <i class="fa fa-heart-o"></i></a></li><li><a href="javascript:" data-icon="fa-heartbeat"> <i class="fa fa-heartbeat"></i></a></li><li><a href="javascript:" data-icon="fa-history"> <i class="fa fa-history"></i></a></li><li><a href="javascript:" data-icon="fa-home"> <i class="fa fa-home"></i></a></li><li><a href="javascript:" data-icon="fa-hospital-o"> <i class="fa fa-hospital-o"></i></a></li><li><a href="javascript:" data-icon="fa-hotel"> <i class="fa fa-hotel"></i></a></li><li><a href="javascript:" data-icon="fa-hourglass"> <i class="fa fa-hourglass"></i></a></li><li><a href="javascript:" data-icon="fa-hourglass-1"> <i class="fa fa-hourglass-1"></i></a></li><li><a href="javascript:" data-icon="fa-hourglass-2"> <i class="fa fa-hourglass-2"></i></a></li><li><a href="javascript:" data-icon="fa-hourglass-3"> <i class="fa fa-hourglass-3"></i></a></li><li><a href="javascript:" data-icon="fa-hourglass-end"> <i class="fa fa-hourglass-end"></i></a></li><li><a href="javascript:" data-icon="fa-hourglass-half"> <i class="fa fa-hourglass-half"></i></a></li><li><a href="javascript:" data-icon="fa-hourglass-o"> <i class="fa fa-hourglass-o"></i></a></li><li><a href="javascript:" data-icon="fa-hourglass-start"> <i class="fa fa-hourglass-start"></i></a></li><li><a href="javascript:" data-icon="fa-houzz"> <i class="fa fa-houzz"></i></a></li><li><a href="javascript:" data-icon="fa-html5"> <i class="fa fa-html5"></i></a></li><li><a href="javascript:" data-icon="fa-i-cursor"> <i class="fa fa-i-cursor"></i></a></li><li><a href="javascript:" data-icon="fa-id-badge"> <i class="fa fa-id-badge"></i></a></li><li><a href="javascript:" data-icon="fa-id-card"> <i class="fa fa-id-card"></i></a></li><li><a href="javascript:" data-icon="fa-id-card-o"> <i class="fa fa-id-card-o"></i></a></li><li><a href="javascript:" data-icon="fa-ils"> <i class="fa fa-ils"></i></a></li><li><a href="javascript:" data-icon="fa-image"> <i class="fa fa-image"></i></a></li><li><a href="javascript:" data-icon="fa-imdb"> <i class="fa fa-imdb"></i></a></li><li><a href="javascript:" data-icon="fa-inbox"> <i class="fa fa-inbox"></i></a></li><li><a href="javascript:" data-icon="fa-indent"> <i class="fa fa-indent"></i></a></li><li><a href="javascript:" data-icon="fa-industry"> <i class="fa fa-industry"></i></a></li><li><a href="javascript:" data-icon="fa-info"> <i class="fa fa-info"></i></a></li><li><a href="javascript:" data-icon="fa-info-circle"> <i class="fa fa-info-circle"></i></a></li><li><a href="javascript:" data-icon="fa-inr"> <i class="fa fa-inr"></i></a></li><li><a href="javascript:" data-icon="fa-instagram"> <i class="fa fa-instagram"></i></a></li><li><a href="javascript:" data-icon="fa-institution"> <i class="fa fa-institution"></i></a></li><li><a href="javascript:" data-icon="fa-internet-explorer"> <i class="fa fa-internet-explorer"></i></a></li><li><a href="javascript:" data-icon="fa-intersex"> <i class="fa fa-intersex"></i></a></li><li><a href="javascript:" data-icon="fa-ioxhost"> <i class="fa fa-ioxhost"></i></a></li><li><a href="javascript:" data-icon="fa-italic"> <i class="fa fa-italic"></i></a></li><li><a href="javascript:" data-icon="fa-joomla"> <i class="fa fa-joomla"></i></a></li><li><a href="javascript:" data-icon="fa-jpy"> <i class="fa fa-jpy"></i></a></li><li><a href="javascript:" data-icon="fa-jsfiddle"> <i class="fa fa-jsfiddle"></i></a></li><li><a href="javascript:" data-icon="fa-key"> <i class="fa fa-key"></i></a></li><li><a href="javascript:" data-icon="fa-keyboard-o"> <i class="fa fa-keyboard-o"></i></a></li><li><a href="javascript:" data-icon="fa-krw"> <i class="fa fa-krw"></i></a></li><li><a href="javascript:" data-icon="fa-language"> <i class="fa fa-language"></i></a></li><li><a href="javascript:" data-icon="fa-laptop"> <i class="fa fa-laptop"></i></a></li><li><a href="javascript:" data-icon="fa-lastfm"> <i class="fa fa-lastfm"></i></a></li><li><a href="javascript:" data-icon="fa-lastfm-square"> <i class="fa fa-lastfm-square"></i></a></li><li><a href="javascript:" data-icon="fa-leaf"> <i class="fa fa-leaf"></i></a></li><li><a href="javascript:" data-icon="fa-leanpub"> <i class="fa fa-leanpub"></i></a></li><li><a href="javascript:" data-icon="fa-legal"> <i class="fa fa-legal"></i></a></li><li><a href="javascript:" data-icon="fa-lemon-o"> <i class="fa fa-lemon-o"></i></a></li><li><a href="javascript:" data-icon="fa-level-down"> <i class="fa fa-level-down"></i></a></li><li><a href="javascript:" data-icon="fa-level-up"> <i class="fa fa-level-up"></i></a></li><li><a href="javascript:" data-icon="fa-life-bouy"> <i class="fa fa-life-bouy"></i></a></li><li><a href="javascript:" data-icon="fa-life-buoy"> <i class="fa fa-life-buoy"></i></a></li><li><a href="javascript:" data-icon="fa-life-ring"> <i class="fa fa-life-ring"></i></a></li><li><a href="javascript:" data-icon="fa-life-saver"> <i class="fa fa-life-saver"></i></a></li><li><a href="javascript:" data-icon="fa-lightbulb-o"> <i class="fa fa-lightbulb-o"></i></a></li><li><a href="javascript:" data-icon="fa-line-chart"> <i class="fa fa-line-chart"></i></a></li><li><a href="javascript:" data-icon="fa-link"> <i class="fa fa-link"></i></a></li><li><a href="javascript:" data-icon="fa-linkedin"> <i class="fa fa-linkedin"></i></a></li><li><a href="javascript:" data-icon="fa-linkedin-square"> <i class="fa fa-linkedin-square"></i></a></li><li><a href="javascript:" data-icon="fa-linode"> <i class="fa fa-linode"></i></a></li><li><a href="javascript:" data-icon="fa-linux"> <i class="fa fa-linux"></i></a></li><li><a href="javascript:" data-icon="fa-list"> <i class="fa fa-list"></i></a></li><li><a href="javascript:" data-icon="fa-list-alt"> <i class="fa fa-list-alt"></i></a></li><li><a href="javascript:" data-icon="fa-list-ol"> <i class="fa fa-list-ol"></i></a></li><li><a href="javascript:" data-icon="fa-list-ul"> <i class="fa fa-list-ul"></i></a></li><li><a href="javascript:" data-icon="fa-location-arrow"> <i class="fa fa-location-arrow"></i></a></li><li><a href="javascript:" data-icon="fa-lock"> <i class="fa fa-lock"></i></a></li><li><a href="javascript:" data-icon="fa-long-arrow-down"> <i class="fa fa-long-arrow-down"></i></a></li><li><a href="javascript:" data-icon="fa-long-arrow-left"> <i class="fa fa-long-arrow-left"></i></a></li><li><a href="javascript:" data-icon="fa-long-arrow-right"> <i class="fa fa-long-arrow-right"></i></a></li><li><a href="javascript:" data-icon="fa-long-arrow-up"> <i class="fa fa-long-arrow-up"></i></a></li><li><a href="javascript:" data-icon="fa-low-vision"> <i class="fa fa-low-vision"></i></a></li><li><a href="javascript:" data-icon="fa-magic"> <i class="fa fa-magic"></i></a></li><li><a href="javascript:" data-icon="fa-magnet"> <i class="fa fa-magnet"></i></a></li><li><a href="javascript:" data-icon="fa-mail-forward"> <i class="fa fa-mail-forward"></i></a></li><li><a href="javascript:" data-icon="fa-mail-reply"> <i class="fa fa-mail-reply"></i></a></li><li><a href="javascript:" data-icon="fa-mail-reply-all"> <i class="fa fa-mail-reply-all"></i></a></li><li><a href="javascript:" data-icon="fa-male"> <i class="fa fa-male"></i></a></li><li><a href="javascript:" data-icon="fa-map"> <i class="fa fa-map"></i></a></li><li><a href="javascript:" data-icon="fa-map-marker"> <i class="fa fa-map-marker"></i></a></li><li><a href="javascript:" data-icon="fa-map-o"> <i class="fa fa-map-o"></i></a></li><li><a href="javascript:" data-icon="fa-map-pin"> <i class="fa fa-map-pin"></i></a></li><li><a href="javascript:" data-icon="fa-map-signs"> <i class="fa fa-map-signs"></i></a></li><li><a href="javascript:" data-icon="fa-mars"> <i class="fa fa-mars"></i></a></li><li><a href="javascript:" data-icon="fa-mars-double"> <i class="fa fa-mars-double"></i></a></li><li><a href="javascript:" data-icon="fa-mars-stroke"> <i class="fa fa-mars-stroke"></i></a></li><li><a href="javascript:" data-icon="fa-mars-stroke-h"> <i class="fa fa-mars-stroke-h"></i></a></li><li><a href="javascript:" data-icon="fa-mars-stroke-v"> <i class="fa fa-mars-stroke-v"></i></a></li><li><a href="javascript:" data-icon="fa-maxcdn"> <i class="fa fa-maxcdn"></i></a></li><li><a href="javascript:" data-icon="fa-meanpath"> <i class="fa fa-meanpath"></i></a></li><li><a href="javascript:" data-icon="fa-medium"> <i class="fa fa-medium"></i></a></li><li><a href="javascript:" data-icon="fa-medkit"> <i class="fa fa-medkit"></i></a></li><li><a href="javascript:" data-icon="fa-meetup"> <i class="fa fa-meetup"></i></a></li><li><a href="javascript:" data-icon="fa-meh-o"> <i class="fa fa-meh-o"></i></a></li><li><a href="javascript:" data-icon="fa-mercury"> <i class="fa fa-mercury"></i></a></li><li><a href="javascript:" data-icon="fa-microchip"> <i class="fa fa-microchip"></i></a></li><li><a href="javascript:" data-icon="fa-microphone"> <i class="fa fa-microphone"></i></a></li><li><a href="javascript:" data-icon="fa-microphone-slash"> <i class="fa fa-microphone-slash"></i></a></li><li><a href="javascript:" data-icon="fa-minus"> <i class="fa fa-minus"></i></a></li><li><a href="javascript:" data-icon="fa-minus-circle"> <i class="fa fa-minus-circle"></i></a></li><li><a href="javascript:" data-icon="fa-minus-square"> <i class="fa fa-minus-square"></i></a></li><li><a href="javascript:" data-icon="fa-minus-square-o"> <i class="fa fa-minus-square-o"></i></a></li><li><a href="javascript:" data-icon="fa-mixcloud"> <i class="fa fa-mixcloud"></i></a></li><li><a href="javascript:" data-icon="fa-mobile"> <i class="fa fa-mobile"></i></a></li><li><a href="javascript:" data-icon="fa-mobile-phone"> <i class="fa fa-mobile-phone"></i></a></li><li><a href="javascript:" data-icon="fa-modx"> <i class="fa fa-modx"></i></a></li><li><a href="javascript:" data-icon="fa-money"> <i class="fa fa-money"></i></a></li><li><a href="javascript:" data-icon="fa-moon-o"> <i class="fa fa-moon-o"></i></a></li><li><a href="javascript:" data-icon="fa-mortar-board"> <i class="fa fa-mortar-board"></i></a></li><li><a href="javascript:" data-icon="fa-motorcycle"> <i class="fa fa-motorcycle"></i></a></li><li><a href="javascript:" data-icon="fa-mouse-pointer"> <i class="fa fa-mouse-pointer"></i></a></li><li><a href="javascript:" data-icon="fa-music"> <i class="fa fa-music"></i></a></li><li><a href="javascript:" data-icon="fa-navicon"> <i class="fa fa-navicon"></i></a></li><li><a href="javascript:" data-icon="fa-neuter"> <i class="fa fa-neuter"></i></a></li><li><a href="javascript:" data-icon="fa-newspaper-o"> <i class="fa fa-newspaper-o"></i></a></li><li><a href="javascript:" data-icon="fa-object-group"> <i class="fa fa-object-group"></i></a></li><li><a href="javascript:" data-icon="fa-object-ungroup"> <i class="fa fa-object-ungroup"></i></a></li><li><a href="javascript:" data-icon="fa-odnoklassniki"> <i class="fa fa-odnoklassniki"></i></a></li><li><a href="javascript:" data-icon="fa-odnoklassniki-square"> <i class="fa fa-odnoklassniki-square"></i></a></li><li><a href="javascript:" data-icon="fa-opencart"> <i class="fa fa-opencart"></i></a></li><li><a href="javascript:" data-icon="fa-openid"> <i class="fa fa-openid"></i></a></li><li><a href="javascript:" data-icon="fa-opera"> <i class="fa fa-opera"></i></a></li><li><a href="javascript:" data-icon="fa-optin-monster"> <i class="fa fa-optin-monster"></i></a></li><li><a href="javascript:" data-icon="fa-outdent"> <i class="fa fa-outdent"></i></a></li><li><a href="javascript:" data-icon="fa-pagelines"> <i class="fa fa-pagelines"></i></a></li><li><a href="javascript:" data-icon="fa-paint-brush"> <i class="fa fa-paint-brush"></i></a></li><li><a href="javascript:" data-icon="fa-paper-plane"> <i class="fa fa-paper-plane"></i></a></li><li><a href="javascript:" data-icon="fa-paper-plane-o"> <i class="fa fa-paper-plane-o"></i></a></li><li><a href="javascript:" data-icon="fa-paperclip"> <i class="fa fa-paperclip"></i></a></li><li><a href="javascript:" data-icon="fa-paragraph"> <i class="fa fa-paragraph"></i></a></li><li><a href="javascript:" data-icon="fa-paste"> <i class="fa fa-paste"></i></a></li><li><a href="javascript:" data-icon="fa-pause"> <i class="fa fa-pause"></i></a></li><li><a href="javascript:" data-icon="fa-pause-circle"> <i class="fa fa-pause-circle"></i></a></li><li><a href="javascript:" data-icon="fa-pause-circle-o"> <i class="fa fa-pause-circle-o"></i></a></li><li><a href="javascript:" data-icon="fa-paw"> <i class="fa fa-paw"></i></a></li><li><a href="javascript:" data-icon="fa-paypal"> <i class="fa fa-paypal"></i></a></li><li><a href="javascript:" data-icon="fa-pencil"> <i class="fa fa-pencil"></i></a></li><li><a href="javascript:" data-icon="fa-pencil-square"> <i class="fa fa-pencil-square"></i></a></li><li><a href="javascript:" data-icon="fa-pencil-square-o"> <i class="fa fa-pencil-square-o"></i></a></li><li><a href="javascript:" data-icon="fa-percent"> <i class="fa fa-percent"></i></a></li><li><a href="javascript:" data-icon="fa-phone"> <i class="fa fa-phone"></i></a></li><li><a href="javascript:" data-icon="fa-phone-square"> <i class="fa fa-phone-square"></i></a></li><li><a href="javascript:" data-icon="fa-photo"> <i class="fa fa-photo"></i></a></li><li><a href="javascript:" data-icon="fa-picture-o"> <i class="fa fa-picture-o"></i></a></li><li><a href="javascript:" data-icon="fa-pie-chart"> <i class="fa fa-pie-chart"></i></a></li><li><a href="javascript:" data-icon="fa-pied-piper"> <i class="fa fa-pied-piper"></i></a></li><li><a href="javascript:" data-icon="fa-pied-piper-alt"> <i class="fa fa-pied-piper-alt"></i></a></li><li><a href="javascript:" data-icon="fa-pied-piper-pp"> <i class="fa fa-pied-piper-pp"></i></a></li><li><a href="javascript:" data-icon="fa-pinterest"> <i class="fa fa-pinterest"></i></a></li><li><a href="javascript:" data-icon="fa-pinterest-p"> <i class="fa fa-pinterest-p"></i></a></li><li><a href="javascript:" data-icon="fa-pinterest-square"> <i class="fa fa-pinterest-square"></i></a></li><li><a href="javascript:" data-icon="fa-plane"> <i class="fa fa-plane"></i></a></li><li><a href="javascript:" data-icon="fa-play"> <i class="fa fa-play"></i></a></li><li><a href="javascript:" data-icon="fa-play-circle"> <i class="fa fa-play-circle"></i></a></li><li><a href="javascript:" data-icon="fa-play-circle-o"> <i class="fa fa-play-circle-o"></i></a></li><li><a href="javascript:" data-icon="fa-plug"> <i class="fa fa-plug"></i></a></li><li><a href="javascript:" data-icon="fa-plus"> <i class="fa fa-plus"></i></a></li><li><a href="javascript:" data-icon="fa-plus-circle"> <i class="fa fa-plus-circle"></i></a></li><li><a href="javascript:" data-icon="fa-plus-square"> <i class="fa fa-plus-square"></i></a></li><li><a href="javascript:" data-icon="fa-plus-square-o"> <i class="fa fa-plus-square-o"></i></a></li><li><a href="javascript:" data-icon="fa-podcast"> <i class="fa fa-podcast"></i></a></li><li><a href="javascript:" data-icon="fa-power-off"> <i class="fa fa-power-off"></i></a></li><li><a href="javascript:" data-icon="fa-print"> <i class="fa fa-print"></i></a></li><li><a href="javascript:" data-icon="fa-product-hunt"> <i class="fa fa-product-hunt"></i></a></li><li><a href="javascript:" data-icon="fa-puzzle-piece"> <i class="fa fa-puzzle-piece"></i></a></li><li><a href="javascript:" data-icon="fa-qq"> <i class="fa fa-qq"></i></a></li><li><a href="javascript:" data-icon="fa-qrcode"> <i class="fa fa-qrcode"></i></a></li><li><a href="javascript:" data-icon="fa-question"> <i class="fa fa-question"></i></a></li><li><a href="javascript:" data-icon="fa-question-circle"> <i class="fa fa-question-circle"></i></a></li><li><a href="javascript:" data-icon="fa-question-circle-o"> <i class="fa fa-question-circle-o"></i></a></li><li><a href="javascript:" data-icon="fa-quora"> <i class="fa fa-quora"></i></a></li><li><a href="javascript:" data-icon="fa-quote-left"> <i class="fa fa-quote-left"></i></a></li><li><a href="javascript:" data-icon="fa-quote-right"> <i class="fa fa-quote-right"></i></a></li><li><a href="javascript:" data-icon="fa-ra"> <i class="fa fa-ra"></i></a></li><li><a href="javascript:" data-icon="fa-random"> <i class="fa fa-random"></i></a></li><li><a href="javascript:" data-icon="fa-ravelry"> <i class="fa fa-ravelry"></i></a></li><li><a href="javascript:" data-icon="fa-rebel"> <i class="fa fa-rebel"></i></a></li><li><a href="javascript:" data-icon="fa-recycle"> <i class="fa fa-recycle"></i></a></li><li><a href="javascript:" data-icon="fa-reddit"> <i class="fa fa-reddit"></i></a></li><li><a href="javascript:" data-icon="fa-reddit-alien"> <i class="fa fa-reddit-alien"></i></a></li><li><a href="javascript:" data-icon="fa-reddit-square"> <i class="fa fa-reddit-square"></i></a></li><li><a href="javascript:" data-icon="fa-refresh"> <i class="fa fa-refresh"></i></a></li><li><a href="javascript:" data-icon="fa-registered"> <i class="fa fa-registered"></i></a></li><li><a href="javascript:" data-icon="fa-remove"> <i class="fa fa-remove"></i></a></li><li><a href="javascript:" data-icon="fa-renren"> <i class="fa fa-renren"></i></a></li><li><a href="javascript:" data-icon="fa-reorder"> <i class="fa fa-reorder"></i></a></li><li><a href="javascript:" data-icon="fa-repeat"> <i class="fa fa-repeat"></i></a></li><li><a href="javascript:" data-icon="fa-reply"> <i class="fa fa-reply"></i></a></li><li><a href="javascript:" data-icon="fa-reply-all"> <i class="fa fa-reply-all"></i></a></li><li><a href="javascript:" data-icon="fa-resistance"> <i class="fa fa-resistance"></i></a></li><li><a href="javascript:" data-icon="fa-retweet"> <i class="fa fa-retweet"></i></a></li><li><a href="javascript:" data-icon="fa-rmb"> <i class="fa fa-rmb"></i></a></li><li><a href="javascript:" data-icon="fa-road"> <i class="fa fa-road"></i></a></li><li><a href="javascript:" data-icon="fa-rocket"> <i class="fa fa-rocket"></i></a></li><li><a href="javascript:" data-icon="fa-rotate-left"> <i class="fa fa-rotate-left"></i></a></li><li><a href="javascript:" data-icon="fa-rotate-right"> <i class="fa fa-rotate-right"></i></a></li><li><a href="javascript:" data-icon="fa-rouble"> <i class="fa fa-rouble"></i></a></li><li><a href="javascript:" data-icon="fa-rss"> <i class="fa fa-rss"></i></a></li><li><a href="javascript:" data-icon="fa-rss-square"> <i class="fa fa-rss-square"></i></a></li><li><a href="javascript:" data-icon="fa-rub"> <i class="fa fa-rub"></i></a></li><li><a href="javascript:" data-icon="fa-ruble"> <i class="fa fa-ruble"></i></a></li><li><a href="javascript:" data-icon="fa-rupee"> <i class="fa fa-rupee"></i></a></li><li><a href="javascript:" data-icon="fa-s15"> <i class="fa fa-s15"></i></a></li><li><a href="javascript:" data-icon="fa-safari"> <i class="fa fa-safari"></i></a></li><li><a href="javascript:" data-icon="fa-save"> <i class="fa fa-save"></i></a></li><li><a href="javascript:" data-icon="fa-scissors"> <i class="fa fa-scissors"></i></a></li><li><a href="javascript:" data-icon="fa-scribd"> <i class="fa fa-scribd"></i></a></li><li><a href="javascript:" data-icon="fa-search"> <i class="fa fa-search"></i></a></li><li><a href="javascript:" data-icon="fa-search-minus"> <i class="fa fa-search-minus"></i></a></li><li><a href="javascript:" data-icon="fa-search-plus"> <i class="fa fa-search-plus"></i></a></li><li><a href="javascript:" data-icon="fa-sellsy"> <i class="fa fa-sellsy"></i></a></li><li><a href="javascript:" data-icon="fa-send"> <i class="fa fa-send"></i></a></li><li><a href="javascript:" data-icon="fa-send-o"> <i class="fa fa-send-o"></i></a></li><li><a href="javascript:" data-icon="fa-server"> <i class="fa fa-server"></i></a></li><li><a href="javascript:" data-icon="fa-share"> <i class="fa fa-share"></i></a></li><li><a href="javascript:" data-icon="fa-share-alt"> <i class="fa fa-share-alt"></i></a></li><li><a href="javascript:" data-icon="fa-share-alt-square"> <i class="fa fa-share-alt-square"></i></a></li><li><a href="javascript:" data-icon="fa-share-square"> <i class="fa fa-share-square"></i></a></li><li><a href="javascript:" data-icon="fa-share-square-o"> <i class="fa fa-share-square-o"></i></a></li><li><a href="javascript:" data-icon="fa-shekel"> <i class="fa fa-shekel"></i></a></li><li><a href="javascript:" data-icon="fa-sheqel"> <i class="fa fa-sheqel"></i></a></li><li><a href="javascript:" data-icon="fa-shield"> <i class="fa fa-shield"></i></a></li><li><a href="javascript:" data-icon="fa-ship"> <i class="fa fa-ship"></i></a></li><li><a href="javascript:" data-icon="fa-shirtsinbulk"> <i class="fa fa-shirtsinbulk"></i></a></li><li><a href="javascript:" data-icon="fa-shopping-bag"> <i class="fa fa-shopping-bag"></i></a></li><li><a href="javascript:" data-icon="fa-shopping-basket"> <i class="fa fa-shopping-basket"></i></a></li><li><a href="javascript:" data-icon="fa-shopping-cart"> <i class="fa fa-shopping-cart"></i></a></li><li><a href="javascript:" data-icon="fa-shower"> <i class="fa fa-shower"></i></a></li><li><a href="javascript:" data-icon="fa-sign-in"> <i class="fa fa-sign-in"></i></a></li><li><a href="javascript:" data-icon="fa-sign-language"> <i class="fa fa-sign-language"></i></a></li><li><a href="javascript:" data-icon="fa-sign-out"> <i class="fa fa-sign-out"></i></a></li><li><a href="javascript:" data-icon="fa-signal"> <i class="fa fa-signal"></i></a></li><li><a href="javascript:" data-icon="fa-signing"> <i class="fa fa-signing"></i></a></li><li><a href="javascript:" data-icon="fa-simplybuilt"> <i class="fa fa-simplybuilt"></i></a></li><li><a href="javascript:" data-icon="fa-sitemap"> <i class="fa fa-sitemap"></i></a></li><li><a href="javascript:" data-icon="fa-skyatlas"> <i class="fa fa-skyatlas"></i></a></li><li><a href="javascript:" data-icon="fa-skype"> <i class="fa fa-skype"></i></a></li><li><a href="javascript:" data-icon="fa-slack"> <i class="fa fa-slack"></i></a></li><li><a href="javascript:" data-icon="fa-sliders"> <i class="fa fa-sliders"></i></a></li><li><a href="javascript:" data-icon="fa-slideshare"> <i class="fa fa-slideshare"></i></a></li><li><a href="javascript:" data-icon="fa-smile-o"> <i class="fa fa-smile-o"></i></a></li><li><a href="javascript:" data-icon="fa-snapchat"> <i class="fa fa-snapchat"></i></a></li><li><a href="javascript:" data-icon="fa-snapchat-ghost"> <i class="fa fa-snapchat-ghost"></i></a></li><li><a href="javascript:" data-icon="fa-snapchat-square"> <i class="fa fa-snapchat-square"></i></a></li><li><a href="javascript:" data-icon="fa-snowflake-o"> <i class="fa fa-snowflake-o"></i></a></li><li><a href="javascript:" data-icon="fa-soccer-ball-o"> <i class="fa fa-soccer-ball-o"></i></a></li><li><a href="javascript:" data-icon="fa-sort"> <i class="fa fa-sort"></i></a></li><li><a href="javascript:" data-icon="fa-sort-alpha-asc"> <i class="fa fa-sort-alpha-asc"></i></a></li><li><a href="javascript:" data-icon="fa-sort-alpha-desc"> <i class="fa fa-sort-alpha-desc"></i></a></li><li><a href="javascript:" data-icon="fa-sort-amount-asc"> <i class="fa fa-sort-amount-asc"></i></a></li><li><a href="javascript:" data-icon="fa-sort-amount-desc"> <i class="fa fa-sort-amount-desc"></i></a></li><li><a href="javascript:" data-icon="fa-sort-asc"> <i class="fa fa-sort-asc"></i></a></li><li><a href="javascript:" data-icon="fa-sort-desc"> <i class="fa fa-sort-desc"></i></a></li><li><a href="javascript:" data-icon="fa-sort-down"> <i class="fa fa-sort-down"></i></a></li><li><a href="javascript:" data-icon="fa-sort-numeric-asc"> <i class="fa fa-sort-numeric-asc"></i></a></li><li><a href="javascript:" data-icon="fa-sort-numeric-desc"> <i class="fa fa-sort-numeric-desc"></i></a></li><li><a href="javascript:" data-icon="fa-sort-up"> <i class="fa fa-sort-up"></i></a></li><li><a href="javascript:" data-icon="fa-soundcloud"> <i class="fa fa-soundcloud"></i></a></li><li><a href="javascript:" data-icon="fa-space-shuttle"> <i class="fa fa-space-shuttle"></i></a></li><li><a href="javascript:" data-icon="fa-spinner"> <i class="fa fa-spinner"></i></a></li><li><a href="javascript:" data-icon="fa-spoon"> <i class="fa fa-spoon"></i></a></li><li><a href="javascript:" data-icon="fa-spotify"> <i class="fa fa-spotify"></i></a></li><li><a href="javascript:" data-icon="fa-square"> <i class="fa fa-square"></i></a></li><li><a href="javascript:" data-icon="fa-square-o"> <i class="fa fa-square-o"></i></a></li><li><a href="javascript:" data-icon="fa-stack-exchange"> <i class="fa fa-stack-exchange"></i></a></li><li><a href="javascript:" data-icon="fa-stack-overflow"> <i class="fa fa-stack-overflow"></i></a></li><li><a href="javascript:" data-icon="fa-star"> <i class="fa fa-star"></i></a></li><li><a href="javascript:" data-icon="fa-star-half"> <i class="fa fa-star-half"></i></a></li><li><a href="javascript:" data-icon="fa-star-half-empty"> <i class="fa fa-star-half-empty"></i></a></li><li><a href="javascript:" data-icon="fa-star-half-full"> <i class="fa fa-star-half-full"></i></a></li><li><a href="javascript:" data-icon="fa-star-half-o"> <i class="fa fa-star-half-o"></i></a></li><li><a href="javascript:" data-icon="fa-star-o"> <i class="fa fa-star-o"></i></a></li><li><a href="javascript:" data-icon="fa-steam"> <i class="fa fa-steam"></i></a></li><li><a href="javascript:" data-icon="fa-steam-square"> <i class="fa fa-steam-square"></i></a></li><li><a href="javascript:" data-icon="fa-step-backward"> <i class="fa fa-step-backward"></i></a></li><li><a href="javascript:" data-icon="fa-step-forward"> <i class="fa fa-step-forward"></i></a></li><li><a href="javascript:" data-icon="fa-stethoscope"> <i class="fa fa-stethoscope"></i></a></li><li><a href="javascript:" data-icon="fa-sticky-note"> <i class="fa fa-sticky-note"></i></a></li><li><a href="javascript:" data-icon="fa-sticky-note-o"> <i class="fa fa-sticky-note-o"></i></a></li><li><a href="javascript:" data-icon="fa-stop"> <i class="fa fa-stop"></i></a></li><li><a href="javascript:" data-icon="fa-stop-circle"> <i class="fa fa-stop-circle"></i></a></li><li><a href="javascript:" data-icon="fa-stop-circle-o"> <i class="fa fa-stop-circle-o"></i></a></li><li><a href="javascript:" data-icon="fa-street-view"> <i class="fa fa-street-view"></i></a></li><li><a href="javascript:" data-icon="fa-strikethrough"> <i class="fa fa-strikethrough"></i></a></li><li><a href="javascript:" data-icon="fa-stumbleupon"> <i class="fa fa-stumbleupon"></i></a></li><li><a href="javascript:" data-icon="fa-stumbleupon-circle"> <i class="fa fa-stumbleupon-circle"></i></a></li><li><a href="javascript:" data-icon="fa-subscript"> <i class="fa fa-subscript"></i></a></li><li><a href="javascript:" data-icon="fa-subway"> <i class="fa fa-subway"></i></a></li><li><a href="javascript:" data-icon="fa-suitcase"> <i class="fa fa-suitcase"></i></a></li><li><a href="javascript:" data-icon="fa-sun-o"> <i class="fa fa-sun-o"></i></a></li><li><a href="javascript:" data-icon="fa-superpowers"> <i class="fa fa-superpowers"></i></a></li><li><a href="javascript:" data-icon="fa-superscript"> <i class="fa fa-superscript"></i></a></li><li><a href="javascript:" data-icon="fa-support"> <i class="fa fa-support"></i></a></li><li><a href="javascript:" data-icon="fa-table"> <i class="fa fa-table"></i></a></li><li><a href="javascript:" data-icon="fa-tablet"> <i class="fa fa-tablet"></i></a></li><li><a href="javascript:" data-icon="fa-tachometer"> <i class="fa fa-tachometer"></i></a></li><li><a href="javascript:" data-icon="fa-tag"> <i class="fa fa-tag"></i></a></li><li><a href="javascript:" data-icon="fa-tags"> <i class="fa fa-tags"></i></a></li><li><a href="javascript:" data-icon="fa-tasks"> <i class="fa fa-tasks"></i></a></li><li><a href="javascript:" data-icon="fa-taxi"> <i class="fa fa-taxi"></i></a></li><li><a href="javascript:" data-icon="fa-telegram"> <i class="fa fa-telegram"></i></a></li><li><a href="javascript:" data-icon="fa-television"> <i class="fa fa-television"></i></a></li><li><a href="javascript:" data-icon="fa-tencent-weibo"> <i class="fa fa-tencent-weibo"></i></a></li><li><a href="javascript:" data-icon="fa-terminal"> <i class="fa fa-terminal"></i></a></li><li><a href="javascript:" data-icon="fa-text-height"> <i class="fa fa-text-height"></i></a></li><li><a href="javascript:" data-icon="fa-text-width"> <i class="fa fa-text-width"></i></a></li><li><a href="javascript:" data-icon="fa-th"> <i class="fa fa-th"></i></a></li><li><a href="javascript:" data-icon="fa-th-large"> <i class="fa fa-th-large"></i></a></li><li><a href="javascript:" data-icon="fa-th-list"> <i class="fa fa-th-list"></i></a></li><li><a href="javascript:" data-icon="fa-themeisle"> <i class="fa fa-themeisle"></i></a></li><li><a href="javascript:" data-icon="fa-thermometer"> <i class="fa fa-thermometer"></i></a></li><li><a href="javascript:" data-icon="fa-thermometer-0"> <i class="fa fa-thermometer-0"></i></a></li><li><a href="javascript:" data-icon="fa-thermometer-1"> <i class="fa fa-thermometer-1"></i></a></li><li><a href="javascript:" data-icon="fa-thermometer-2"> <i class="fa fa-thermometer-2"></i></a></li><li><a href="javascript:" data-icon="fa-thermometer-3"> <i class="fa fa-thermometer-3"></i></a></li><li><a href="javascript:" data-icon="fa-thermometer-4"> <i class="fa fa-thermometer-4"></i></a></li><li><a href="javascript:" data-icon="fa-thermometer-empty"> <i class="fa fa-thermometer-empty"></i></a></li><li><a href="javascript:" data-icon="fa-thermometer-full"> <i class="fa fa-thermometer-full"></i></a></li><li><a href="javascript:" data-icon="fa-thermometer-half"> <i class="fa fa-thermometer-half"></i></a></li><li><a href="javascript:" data-icon="fa-thermometer-quarter"> <i class="fa fa-thermometer-quarter"></i></a></li><li><a href="javascript:" data-icon="fa-thermometer-three-quarters"> <i class="fa fa-thermometer-three-quarters"></i></a></li><li><a href="javascript:" data-icon="fa-thumb-tack"> <i class="fa fa-thumb-tack"></i></a></li><li><a href="javascript:" data-icon="fa-thumbs-down"> <i class="fa fa-thumbs-down"></i></a></li><li><a href="javascript:" data-icon="fa-thumbs-o-down"> <i class="fa fa-thumbs-o-down"></i></a></li><li><a href="javascript:" data-icon="fa-thumbs-o-up"> <i class="fa fa-thumbs-o-up"></i></a></li><li><a href="javascript:" data-icon="fa-thumbs-up"> <i class="fa fa-thumbs-up"></i></a></li><li><a href="javascript:" data-icon="fa-ticket"> <i class="fa fa-ticket"></i></a></li><li><a href="javascript:" data-icon="fa-times"> <i class="fa fa-times"></i></a></li><li><a href="javascript:" data-icon="fa-times-circle"> <i class="fa fa-times-circle"></i></a></li><li><a href="javascript:" data-icon="fa-times-circle-o"> <i class="fa fa-times-circle-o"></i></a></li><li><a href="javascript:" data-icon="fa-times-rectangle"> <i class="fa fa-times-rectangle"></i></a></li><li><a href="javascript:" data-icon="fa-times-rectangle-o"> <i class="fa fa-times-rectangle-o"></i></a></li><li><a href="javascript:" data-icon="fa-tint"> <i class="fa fa-tint"></i></a></li><li><a href="javascript:" data-icon="fa-toggle-down"> <i class="fa fa-toggle-down"></i></a></li><li><a href="javascript:" data-icon="fa-toggle-left"> <i class="fa fa-toggle-left"></i></a></li><li><a href="javascript:" data-icon="fa-toggle-off"> <i class="fa fa-toggle-off"></i></a></li><li><a href="javascript:" data-icon="fa-toggle-on"> <i class="fa fa-toggle-on"></i></a></li><li><a href="javascript:" data-icon="fa-toggle-right"> <i class="fa fa-toggle-right"></i></a></li><li><a href="javascript:" data-icon="fa-toggle-up"> <i class="fa fa-toggle-up"></i></a></li><li><a href="javascript:" data-icon="fa-trademark"> <i class="fa fa-trademark"></i></a></li><li><a href="javascript:" data-icon="fa-train"> <i class="fa fa-train"></i></a></li><li><a href="javascript:" data-icon="fa-transgender"> <i class="fa fa-transgender"></i></a></li><li><a href="javascript:" data-icon="fa-transgender-alt"> <i class="fa fa-transgender-alt"></i></a></li><li><a href="javascript:" data-icon="fa-trash"> <i class="fa fa-trash"></i></a></li><li><a href="javascript:" data-icon="fa-trash-o"> <i class="fa fa-trash-o"></i></a></li><li><a href="javascript:" data-icon="fa-tree"> <i class="fa fa-tree"></i></a></li><li><a href="javascript:" data-icon="fa-trello"> <i class="fa fa-trello"></i></a></li><li><a href="javascript:" data-icon="fa-tripadvisor"> <i class="fa fa-tripadvisor"></i></a></li><li><a href="javascript:" data-icon="fa-trophy"> <i class="fa fa-trophy"></i></a></li><li><a href="javascript:" data-icon="fa-truck"> <i class="fa fa-truck"></i></a></li><li><a href="javascript:" data-icon="fa-try"> <i class="fa fa-try"></i></a></li><li><a href="javascript:" data-icon="fa-tty"> <i class="fa fa-tty"></i></a></li><li><a href="javascript:" data-icon="fa-tumblr"> <i class="fa fa-tumblr"></i></a></li><li><a href="javascript:" data-icon="fa-tumblr-square"> <i class="fa fa-tumblr-square"></i></a></li><li><a href="javascript:" data-icon="fa-turkish-lira"> <i class="fa fa-turkish-lira"></i></a></li><li><a href="javascript:" data-icon="fa-tv"> <i class="fa fa-tv"></i></a></li><li><a href="javascript:" data-icon="fa-twitch"> <i class="fa fa-twitch"></i></a></li><li><a href="javascript:" data-icon="fa-twitter"> <i class="fa fa-twitter"></i></a></li><li><a href="javascript:" data-icon="fa-twitter-square"> <i class="fa fa-twitter-square"></i></a></li><li><a href="javascript:" data-icon="fa-umbrella"> <i class="fa fa-umbrella"></i></a></li><li><a href="javascript:" data-icon="fa-underline"> <i class="fa fa-underline"></i></a></li><li><a href="javascript:" data-icon="fa-undo"> <i class="fa fa-undo"></i></a></li><li><a href="javascript:" data-icon="fa-universal-access"> <i class="fa fa-universal-access"></i></a></li><li><a href="javascript:" data-icon="fa-university"> <i class="fa fa-university"></i></a></li><li><a href="javascript:" data-icon="fa-unlink"> <i class="fa fa-unlink"></i></a></li><li><a href="javascript:" data-icon="fa-unlock"> <i class="fa fa-unlock"></i></a></li><li><a href="javascript:" data-icon="fa-unlock-alt"> <i class="fa fa-unlock-alt"></i></a></li><li><a href="javascript:" data-icon="fa-unsorted"> <i class="fa fa-unsorted"></i></a></li><li><a href="javascript:" data-icon="fa-upload"> <i class="fa fa-upload"></i></a></li><li><a href="javascript:" data-icon="fa-usb"> <i class="fa fa-usb"></i></a></li><li><a href="javascript:" data-icon="fa-usd"> <i class="fa fa-usd"></i></a></li><li><a href="javascript:" data-icon="fa-user"> <i class="fa fa-user"></i></a></li><li><a href="javascript:" data-icon="fa-user-circle"> <i class="fa fa-user-circle"></i></a></li><li><a href="javascript:" data-icon="fa-user-circle-o"> <i class="fa fa-user-circle-o"></i></a></li><li><a href="javascript:" data-icon="fa-user-md"> <i class="fa fa-user-md"></i></a></li><li><a href="javascript:" data-icon="fa-user-o"> <i class="fa fa-user-o"></i></a></li><li><a href="javascript:" data-icon="fa-user-plus"> <i class="fa fa-user-plus"></i></a></li><li><a href="javascript:" data-icon="fa-user-secret"> <i class="fa fa-user-secret"></i></a></li><li><a href="javascript:" data-icon="fa-user-times"> <i class="fa fa-user-times"></i></a></li><li><a href="javascript:" data-icon="fa-users"> <i class="fa fa-users"></i></a></li><li><a href="javascript:" data-icon="fa-vcard"> <i class="fa fa-vcard"></i></a></li><li><a href="javascript:" data-icon="fa-vcard-o"> <i class="fa fa-vcard-o"></i></a></li><li><a href="javascript:" data-icon="fa-venus"> <i class="fa fa-venus"></i></a></li><li><a href="javascript:" data-icon="fa-venus-double"> <i class="fa fa-venus-double"></i></a></li><li><a href="javascript:" data-icon="fa-venus-mars"> <i class="fa fa-venus-mars"></i></a></li><li><a href="javascript:" data-icon="fa-viacoin"> <i class="fa fa-viacoin"></i></a></li><li><a href="javascript:" data-icon="fa-viadeo"> <i class="fa fa-viadeo"></i></a></li><li><a href="javascript:" data-icon="fa-viadeo-square"> <i class="fa fa-viadeo-square"></i></a></li><li><a href="javascript:" data-icon="fa-video-camera"> <i class="fa fa-video-camera"></i></a></li><li><a href="javascript:" data-icon="fa-vimeo"> <i class="fa fa-vimeo"></i></a></li><li><a href="javascript:" data-icon="fa-vimeo-square"> <i class="fa fa-vimeo-square"></i></a></li><li><a href="javascript:" data-icon="fa-vine"> <i class="fa fa-vine"></i></a></li><li><a href="javascript:" data-icon="fa-vk"> <i class="fa fa-vk"></i></a></li><li><a href="javascript:" data-icon="fa-volume-control-phone"> <i class="fa fa-volume-control-phone"></i></a></li><li><a href="javascript:" data-icon="fa-volume-down"> <i class="fa fa-volume-down"></i></a></li><li><a href="javascript:" data-icon="fa-volume-off"> <i class="fa fa-volume-off"></i></a></li><li><a href="javascript:" data-icon="fa-volume-up"> <i class="fa fa-volume-up"></i></a></li><li><a href="javascript:" data-icon="fa-warning"> <i class="fa fa-warning"></i></a></li><li><a href="javascript:" data-icon="fa-wechat"> <i class="fa fa-wechat"></i></a></li><li><a href="javascript:" data-icon="fa-weibo"> <i class="fa fa-weibo"></i></a></li><li><a href="javascript:" data-icon="fa-weixin"> <i class="fa fa-weixin"></i></a></li><li><a href="javascript:" data-icon="fa-whatsapp"> <i class="fa fa-whatsapp"></i></a></li><li><a href="javascript:" data-icon="fa-wheelchair"> <i class="fa fa-wheelchair"></i></a></li><li><a href="javascript:" data-icon="fa-wheelchair-alt"> <i class="fa fa-wheelchair-alt"></i></a></li><li><a href="javascript:" data-icon="fa-wifi"> <i class="fa fa-wifi"></i></a></li><li><a href="javascript:" data-icon="fa-wikipedia-w"> <i class="fa fa-wikipedia-w"></i></a></li><li><a href="javascript:" data-icon="fa-window-close"> <i class="fa fa-window-close"></i></a></li><li><a href="javascript:" data-icon="fa-window-close-o"> <i class="fa fa-window-close-o"></i></a></li><li><a href="javascript:" data-icon="fa-window-maximize"> <i class="fa fa-window-maximize"></i></a></li><li><a href="javascript:" data-icon="fa-window-minimize"> <i class="fa fa-window-minimize"></i></a></li><li><a href="javascript:" data-icon="fa-window-restore"> <i class="fa fa-window-restore"></i></a></li><li><a href="javascript:" data-icon="fa-windows"> <i class="fa fa-windows"></i></a></li><li><a href="javascript:" data-icon="fa-won"> <i class="fa fa-won"></i></a></li><li><a href="javascript:" data-icon="fa-wordpress"> <i class="fa fa-wordpress"></i></a></li><li><a href="javascript:" data-icon="fa-wpbeginner"> <i class="fa fa-wpbeginner"></i></a></li><li><a href="javascript:" data-icon="fa-wpexplorer"> <i class="fa fa-wpexplorer"></i></a></li><li><a href="javascript:" data-icon="fa-wpforms"> <i class="fa fa-wpforms"></i></a></li><li><a href="javascript:" data-icon="fa-wrench"> <i class="fa fa-wrench"></i></a></li><li><a href="javascript:" data-icon="fa-xing"> <i class="fa fa-xing"></i></a></li><li><a href="javascript:" data-icon="fa-xing-square"> <i class="fa fa-xing-square"></i></a></li><li><a href="javascript:" data-icon="fa-y-combinator"> <i class="fa fa-y-combinator"></i></a></li><li><a href="javascript:" data-icon="fa-y-combinator-square"> <i class="fa fa-y-combinator-square"></i></a></li><li><a href="javascript:" data-icon="fa-yahoo"> <i class="fa fa-yahoo"></i></a></li><li><a href="javascript:" data-icon="fa-yc"> <i class="fa fa-yc"></i></a></li><li><a href="javascript:" data-icon="fa-yc-square"> <i class="fa fa-yc-square"></i></a></li><li><a href="javascript:" data-icon="fa-yelp"> <i class="fa fa-yelp"></i></a></li><li><a href="javascript:" data-icon="fa-yen"> <i class="fa fa-yen"></i></a></li><li><a href="javascript:" data-icon="fa-yoast"> <i class="fa fa-yoast"></i></a></li><li><a href="javascript:" data-icon="fa-youtube"> <i class="fa fa-youtube"></i></a></li><li><a href="javascript:" data-icon="fa-youtube-play"> <i class="fa fa-youtube-play"></i></a></li><li><a href="javascript:" data-icon="fa-youtube-square"> <i class="fa fa-youtube-square"></i></a></li>';
    }

    public function viewChatBackend() {
        global $wpdb;
        $settings = $this->getSettings();
        $userWP = wp_get_current_user();
        $rows = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_users WHERE userID=" . $userWP->ID);
        $user = array();
        $user = $rows[0];
        ?>
        <audio id="vcht_audioMsg" controls data-enable="<?php
        if ($settings->playSoundOperator) {
            echo 'true';
        } else {
            echo 'false';
        }
        ?>">
            <source src="<?php echo $this->assets_url; ?>sound/message.ogg" type="audio/ogg">
            <source src="<?php echo $this->assets_url; ?>sound/message.mp3" type="audio/mpeg">
        </audio>
        <div id="vcht_mainWrapper" class="vcht_bootstrap">
            <div id="vcht_loader">
                <div class="vcht_loaderCt">
                    <div class="vcht_loaderDot"></div>
                    <div class="vcht_loaderDot"></div>
                    <div class="vcht_loaderDot"></div>
                </div>
            </div>
            <nav id="vcht_mainHeader" class="navbar navbar-inverse navbar-embossed" role="navigation">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-01">
                        <span class="sr-only">Toggle navigation</span>
                    </button>
                    <a class="navbar-brand" href="javascript:" id="vcht_mainLogo"><img src="<?php echo $this->assets_url . 'img/chat-4-64.png'; ?>" alt="WP Flat Visual Chat" /> WP Flat Visual Chat</a>
                </div>
                <div class="" id="vcht_headerNavbarLeft">
                    <ul class="nav navbar-nav navbar-left">
                        <?php
                        if (current_user_can('manage_options')) {
                            ?>
                            <li>
                                <a href="javascript:" onclick="vcht_showMainSettingsPanel();" title="<?php echo __('Settings', 'WP_Visual_Chat'); ?>" data-toggle="tooltip">
                                    <span class="fa fa-cogs"></span>
                                </a>
                            </li>
                            <?php
                        }
                        ?>
                        <li>
                            <a href="javascript:" id="vcht_btnCannedMsgs" onclick="vcht_showCannedMsgsPanel();" title="<?php echo __('Canned messages', 'WP_Visual_Chat'); ?>" data-toggle="tooltip">
                                <span class="fa fa-keyboard-o"></span>                                
                            </a>
                        </li>
                        <?php if (current_user_can('manage_options') || $settings->operatorsFullHistory) { ?>
                            <li>
                                <a href="javascript:" id="vcht_btnViewLogs" onclick="vcht_openFullHistoryPanel();" title="<?php echo __('Chats history', 'WP_Visual_Chat'); ?>" data-toggle="tooltip">
                                    <span class="fa fa-book"></span>                                
                                </a>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                    <form class="navbar-form navbar-right" action="#">
                        <div id="vcht_operatorHeaderPanel">
                            <table>
                                <tr>
                                    <td><a id="vcht_operatorHeaderPic" href="javascript:"><img src="<?php echo $user->imgAvatar; ?>" alt="<?php echo $user->username; ?>"/></a></td>
                                    <td>
                                        <a id="vcht_operatorHeaderUsername" href="javascript:"><?php echo $user->username; ?></a>
                                        <span id="vcht_operatorHeaderStatus"><?php echo __('You are currently', 'WP_Visual_Chat') . ' <strong>' . __('Offline', 'WP_Visual_Chat') . '</strong>'; ?></span>
                                    </td>
                                    <td>
                                        <a id="vcht_operatorBtnConnect" href="javascript:" class="btn btn-lg btn-primary"><span class="glyphicon glyphicon-ok"></span><?php echo __('Log in', 'WP_Visual_Chat'); ?></a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </form>
                </div><!-- /.navbar-collapse -->
            </nav><!-- /navbar -->

            <div id="vcht_usersListPanel" class="vcht_collapsed">
                <div id="vcht_currentChatsList" class="vcht_panel">
                    <div class="vcht_panelHeader">
                        <span class="fa fa-weixin"></span> <?php echo __('Current chats', 'WP_Visual_Chat'); ?>
                    </div>
                    <div class="vcht_panelBody">
                        <div class="vcht_userListItem">
                            <span class="fa fa-info-circle"></span>
                            <?php echo __('There is currently no chat', 'WP_Visual_Chat'); ?>
                        </div>
                    </div>
                </div>
                <div id="vcht_onlineVisitorsList" class="vcht_panel">
                    <div class="vcht_panelHeader">
                        <span class="fa fa-users"></span> <?php echo __('Online users', 'WP_Visual_Chat'); ?>
                    </div>
                    <div class="vcht_panelBody">
                        <div class="vcht_userListItem">
                            <span class="fa fa-info-circle"></span>
                            <?php echo __('There is currently no visitor online', 'WP_Visual_Chat'); ?>
                        </div>
                    </div>
                </div>
                <div id="vcht_onlineOperatorsList" class="vcht_panel">
                    <div class="vcht_panelHeader">
                        <span class="fa fa-user-o"></span> <?php echo __('Online operators', 'WP_Visual_Chat'); ?>
                    </div>
                    <div class="vcht_panelBody">
                    </div>
                </div>
            </div>


            <div id="vcht_backgroundPanel" class="vcht_fullPanel">
                <h2 id="vcht_backgroundPanelTitle"><?php echo __('You are currently', 'WP_Visual_Chat') . ' <strong>' . __('Offline', 'WP_Visual_Chat') . '</strong>'; ?></h2>
                <p id="vcht_backgroundPanelTxt"><?php echo __('Do you want to login as chat operator ?', 'WP_Visual_Chat'); ?></p>
                <p><a href="javascript:" id="vcht_backgroundPanelLoginBtn" onclick="vcht_toggleLogin();" class="btn btn-primary btn-large"><span class="glyphicon glyphicon-ok"></span><?php echo __('Log in', 'WP_Visual_Chat'); ?></a></p>
            </div>

            <div id="vcht_chatPanel" class="vcht_hidden">
                <div id="vcht_chatPanelHeader">
                    <div id="vcht_chatPanelHeaderRightTb">
                        <a href="javascript:" id="vcht_chatPanelMinifyBtn" onclick="vcht_changeSizeChatPanel('minify');" class="btn btn-circle btn-default"><span class="fa fa-window-minimize"></span></a>
                        <a href="javascript:" id="vcht_chatPanelFullscreenBtn" onclick="vcht_changeSizeChatPanel('fullscreen');" class="btn btn-circle btn-default"><span class="fa fa-window-maximize"></span></a>
                        <a href="javascript:" id="vcht_chatPanelCloseBtn" class="btn btn-circle btn-danger"><span class="fa fa-times"></span></a>
                    </div>
                    <img id="vcht_chatPanelHeaderImg" />
                    <span id="vcht_chatPanelHeaderName"></span>
                    <span class="vcht_alertPoint"></span>
                    <div class="clearfix"></div>
                </div>
                <div class="vcht_chatPanelBody">
                    <div id="vcht_chatPanelDetails" class="vcht_scrollbar">
                        <?php if (current_user_can('manage_options') || $settings->operatorsFullHistory) { ?>
                            <p>
                                <a href="javascript:" class="btn btn-primary" id="vcht_viewUserHistoryBtn"><span class="fa fa-book"></span><?php echo __('View history', 'WP_Visual_Chat'); ?></a>
                            </p>
                        <?php } ?>
                        <p>
                            <select class="form-control" id="vcht_transferChatSelect">
                                <option value="" disabled selected><?php echo __('Transfer the chat', 'WP_Visual_Chat'); ?></option>
                            </select>
                        </p>
                        <ul id="vcht_fieldsInfos">
                        </ul>
                    </div>
                    <div id="vcht_chatPanelContent">
                        <div id="vcht_chatPanelHistory" class="vcht_scrollbar"></div>
                        <div id="vcht_chatPanelWriteContainer">
                            <textarea id="vcht_chatPanelMsgArea" placeholder="<?php echo __('Write your message here', 'WP_Visual_Chat'); ?>" class="form-control"></textarea>
                            <div id="vcht_chatPanelBtnsTb">
                                <a id="vcht_chatPanelCannedMsgBtn"  href="javascript:" class="" data-toggle="tooltip" title="<?php echo __('Use a canned message', 'WP_Visual_Chat'); ?>">
                                    <span class="fa fa-keyboard-o"></span>
                                </a>
                                <a id="vcht_chatPanelFilesBtn"  href="javascript:" class="" data-toggle="tooltip" title="<?php echo __('Transfer some files', 'WP_Visual_Chat'); ?>">
                                    <span class="fa fa-paperclip"></span>
                                    <span class="badge">0</span>
                                </a>
                                <a id="vcht_chatPanelShowElementBtn"  href="javascript:" class="" data-toggle="tooltip" title="<?php echo __('Show an element', 'WP_Visual_Chat'); ?>">
                                    <span class="fa fa-eye"></span>
                                </a>
                                <a id="vcht_chatPanelSendBtn"  href="javascript:" class="btn btn-primary btn-circle" data-toggle="tooltip" title="<?php echo __('Send', 'WP_Visual_Chat'); ?>"><span class="fa fa-paper-plane"></span></a>
                            </div>
                        </div>
                        <div id="vcht_chatReqAnswerContainer">
                            <div>                         
                                <a href="javascript:" onclick="vcht_acceptChat();" class="btn btn-primary"><span class="fa fa-check"></span><?php echo __('Reply', 'WP_Visual_Chat'); ?></a>
                                <a href="javascript:" onclick="vcht_declineChat();" class="btn btn-warning"><span class="fa fa-times"></span><?php echo __('Decline', 'WP_Visual_Chat'); ?></a>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>




            <?php if (current_user_can('manage_options')) { ?>
                <div id="vcht_adminSettingsPanel" class="vcht_fullPanel">
                    <div class="vcht_fullPanelHeader">
                        <div class="vcht_fullPanelTb">
                            <a href="javascript:"  class="btn btn-circle btn-danger vcht_closeBtn"><span class="fa fa-times"></span></a>
                        </div>
                        <span class="fa fa-cogs"></span>
                        <?php echo __('Settings', 'WP_Visual_Chat'); ?></a>
                        <div class="clearfix"></div>
                    </div>
                    <div id="" class="vcht_fullPanelBody">

                        <div role="tabpanel">
                            <ul class="nav nav-tabs" role="tablist" >
                                <li role="presentation" class="active" ><a href="#vcht_tabSettingsGeneral" aria-controls="General" role="tab" data-toggle="tab" >
                                        <span class="fa fa-cogs"></span><?php echo __('General', 'WP_Visual_Chat'); ?></a>
                                </li>
                                <li role="presentation" class="" ><a href="#vcht_tabSettingsLogin" aria-controls="Login panel" role="tab" data-toggle="tab" >
                                        <span class="fa fa-key"></span><?php echo __('Login panel', 'WP_Visual_Chat'); ?></a>
                                </li>
                                <li role="presentation" class="" ><a href="#vcht_tabSettingsContactForm" aria-controls="Contact form" role="tab" data-toggle="tab" >
                                        <span class="fa fa-envelope-o"></span><?php echo __('Contact form', 'WP_Visual_Chat'); ?></a>
                                </li>
                                <li role="presentation" class="" ><a href="#vcht_tabSettingsRoles" aria-controls="Operators roles" role="tab" data-toggle="tab" >
                                        <span class="fa fa-user-circle-o"></span><?php echo __('Operators roles', 'WP_Visual_Chat'); ?></a>
                                </li>
                                <li role="presentation" class="" ><a href="#vcht_tabSettingsDesign" aria-controls="Design', " role="tab" data-toggle="tab" >
                                        <span class="fa fa-paint-brush"></span><?php echo __('Design', 'WP_Visual_Chat'); ?></a>
                                </li>
                                <li role="presentation" class="" ><a href="#vcht_tabSettingsTexts" aria-controls="Texts" role="tab" data-toggle="tab" >
                                        <span class="fa fa-font"></span><?php echo __('Texts', 'WP_Visual_Chat'); ?></a>
                                </li>
                                <li class="clearfix"></li>
                            </ul>
                            <div class="clearfix"></div>

                            <div class="vcht_scrollbar">
                                <div class="tab-content" >
                                    <div role="tabpanel" class="tab-pane active " id="vcht_tabSettingsGeneral" >
                                        <div class="container-fluid">
                                            <div class="col-md-6">

                                                <div class="form-group">
                                                    <label><?php echo __('Activate the chat', 'WP_Visual_Chat'); ?></label>
                                                    <input type="checkbox" name="enableChat" data-toggle="switch" />
                                                    <small><?php echo __('This option allows you to fully disable or enable the chat system', 'WP_Visual_Chat'); ?></small>
                                                </div>

                                                <div class="form-group">
                                                    <label><?php echo __('Enable chat only for logged users ?', 'WP_Visual_Chat'); ?></label>
                                                    <input type="checkbox" name="enableLoggedVisitorsOnly" data-toggle="switch" />
                                                    <small><?php echo __('Only logged users will see the chat panel', 'WP_Visual_Chat'); ?></small>
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo __('View online visitors in realtime', 'WP_Visual_Chat'); ?></label>
                                                    <input type="checkbox" name="enableVisitorsTracking" data-toggle="switch" />
                                                    <small><?php echo __('This option can be disabled to preserve server network resources', 'WP_Visual_Chat'); ?></small>
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo __('Users detection delay (in seconds)', 'WP_Visual_Chat'); ?></label>
                                                    <input class="form-control" type="number" step="0.1" name="trackingDelay" />
                                                    <small><?php echo __('The shorter the delay limit, the more network resources will be used'); ?></small>
                                                </div>

                                                <div class="form-group">
                                                    <label><?php echo __('Messages detection delay (in seconds)', 'WP_Visual_Chat'); ?></label>
                                                    <input class="form-control" type="number" step="0.1" name="ajaxCheckDelay" />
                                                    <small><?php echo __('The shorter the delay limit, the more network resources will be used'); ?></small>
                                                </div>

                                                <div class="form-group">
                                                    <label><?php echo __('Operators can see the users history', 'WP_Visual_Chat'); ?></label>
                                                    <input type="checkbox" name="operatorsFullHistory" data-toggle="switch" />
                                                    <small><?php echo __('Disable it to allow only administrators to see the logs', 'WP_Visual_Chat'); ?></small>
                                                </div>

                                                <div class="form-group">
                                                    <label><?php echo __('Play sound notifications for operators', 'WP_Visual_Chat'); ?></label>
                                                    <input type="checkbox" name="playSoundOperator" data-toggle="switch" />
                                                    <small><?php echo __('The operator will be warned by a sound when there is a new message', 'WP_Visual_Chat'); ?></small>
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo __('Play sound notifications for customers', 'WP_Visual_Chat'); ?></label>
                                                    <input type="checkbox" name="playSoundCustomer" data-toggle="switch" />
                                                    <small><?php echo __('The customer will be warned by a sound when there is a new message', 'WP_Visual_Chat'); ?></small>
                                                </div>

                                            </div>
                                            <div class="col-md-6">

                                                <div class="form-group">
                                                    <label><?php echo __('Activate users geolocalization', 'WP_Visual_Chat'); ?></label>
                                                    <input type="checkbox" name="enableGeolocalization" data-toggle="switch" />
                                                    <small><?php echo __('The plugin will detect the city and country of the users', 'WP_Visual_Chat'); ?></small>
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo __('Allow operators to send files', 'WP_Visual_Chat'); ?></label>
                                                    <input type="checkbox" name="allowFilesFromOperators" data-toggle="switch" />
                                                    <small><?php echo __('The operators will be able to send files to the customers', 'WP_Visual_Chat'); ?></small>
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo __('Allow customers to send files', 'WP_Visual_Chat'); ?></label>
                                                    <input type="checkbox" name="allowFilesFromCustomers" data-toggle="switch" />
                                                    <small><?php echo __('The customers will be able to send files to the operators', 'WP_Visual_Chat'); ?></small>
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo __('Maximum size of files sent', 'WP_Visual_Chat'); ?></label>
                                                    <input class="form-control" type="number" step="0.1" name="filesMaxSize" />
                                                    <small><?php echo __('Fill the maximum allowed size for uploaded files in MB', 'WP_Visual_Chat'); ?></small>
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo __('Allowed extensions for files', 'WP_Visual_Chat'); ?></label>
                                                    <textarea class="form-control" name="allowedFiles"></textarea>
                                                    <small><?php echo __('Enter the allowed extensions separated by commas', 'WP_Visual_Chat'); ?></small>
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo __('Show a close button', 'WP_Visual_Chat'); ?></label>
                                                    <input type="checkbox" name="showCloseBtn" data-toggle="switch" />
                                                    <small><?php echo __('It will add a button in the chat panel that allows the customer to close the chat', 'WP_Visual_Chat'); ?></small>
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo __('Show a fullscreen button', 'WP_Visual_Chat'); ?></label>
                                                    <input type="checkbox" name="showFullscreenBtn" data-toggle="switch" />
                                                    <small><?php echo __('It will add a button in the chat panel that allows the customer to show the chat in fullscreen', 'WP_Visual_Chat'); ?></small>
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo __('Purchase code', 'WP_Visual_Chat'); ?></label>
                                                    <input class="form-control" type="text" step="0.1" name="purchaseCode" />
                                                </div>

                                            </div>
                                            <div class="clearfix"></div>
                                            <p style="text-align: center;">
                                                <a href="javascript:" class="btn btn-primary" onclick="vcht_saveSettings();"><span class="fa fa-floppy-o"></span> <?php echo __('Save', 'WP_Visual_Chat'); ?></a>
                                            </p>
                                        </div>
                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="vcht_tabSettingsLogin" >
                                        <div class="container-fluid">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><?php echo __('Show a login panel', 'WP_Visual_Chat'); ?></label>
                                                    <input type="checkbox" name="enableLoginPanel" data-toggle="switch" />
                                                    <small><?php echo __('The visitor will directly access to the chat panel if this option is disabled', 'WP_Visual_Chat'); ?></small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group" >
                                                    <label><?php echo __('Icon', 'WP_Visual_Chat'); ?></label>
                                                    <input type="hidden" class="form-control vcht_iconField" name="loginFormIcon"  />
                                                    <div class="btn-group vcht_btnGroupDrop">
                                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
                                                            <span class="vcht_name"></span><span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu vcht_iconslist" role="menu" >
                                                            <?php echo $this->getIconsOptionsList(); ?>
                                                        </ul></div>
                                                    <small>  <?php echo __('Select an icon', 'WP_Visual_Chat'); ?> </small>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                            <div class="col-md-12" id="vcht_loginFieldsContainer">
                                                <p style="text-align: right;margin-bottom: 4px;">
                                                    <a href="javascript:" onclick="vcht_editField(0, 1);" class="btn btn-default">
                                                        <span class="fa fa-plus"></span>
                                                        <?php echo __('Add a field', 'WP_Visual_Chat'); ?>

                                                    </a>
                                                </p>
                                                <div class="table-responsive">
                                                    <table id="vcht_loginFieldsTable" class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th><?php echo __('Title', 'WP_Visual_Chat'); ?></th>
                                                                <th><?php echo __('Title on backend', 'WP_Visual_Chat'); ?></th>
                                                                <th><?php echo __('Type', 'WP_Visual_Chat'); ?></th>
                                                                <th><?php echo __('Is required', 'WP_Visual_Chat'); ?></th>
                                                                <th class="vcht_actionTh"><?php echo __('Action', 'WP_Visual_Chat'); ?></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                            <p style="text-align: center;">
                                                <a href="javascript:" class="btn btn-primary" onclick="vcht_saveSettings();"><span class="fa fa-floppy-o"></span> <?php echo __('Save', 'WP_Visual_Chat'); ?></a>
                                            </p>
                                        </div>
                                    </div>

                                    <div role="tabpanel" class="tab-pane" id="vcht_tabSettingsContactForm" >
                                        <div class="container-fluid">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><?php echo __('Show a contact form when there is no operator online', 'WP_Visual_Chat'); ?></label>
                                                    <input type="checkbox" name="enableContactForm" data-toggle="switch" />
                                                    <small><?php echo __('If this option is disabled, the chat panel will be hidden when there is no operator online', 'WP_Visual_Chat'); ?></small>
                                                </div>
                                                <div class="form-group" >
                                                    <label><?php echo __('Icon', 'WP_Visual_Chat'); ?></label>
                                                    <input type="hidden" class="form-control vcht_iconField" name="contactFormIcon"  />
                                                    <div class="btn-group vcht_btnGroupDrop">
                                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
                                                            <span class="vcht_name"></span><span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu vcht_iconslist" role="menu" >
                                                            <?php echo $this->getIconsOptionsList(); ?>
                                                        </ul></div>
                                                    <small>  <?php echo __('Select an icon', 'WP_Visual_Chat'); ?> </small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><?php echo __('Admin email', 'WP_Visual_Chat'); ?></label>
                                                    <input type="text" name="emailAdmin" class="form-control" />
                                                    <small><?php echo __('You can add several emails separated by commas', 'WP_Visual_Chat'); ?></small>
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo __('Email subject', 'WP_Visual_Chat'); ?></label>
                                                    <input type="text" name="emailSubject" class="form-control" />
                                                    <small><?php echo __('It will be the subject of the received email', 'WP_Visual_Chat'); ?></small>
                                                </div>

                                            </div>
                                            <div class="clearfix"></div>
                                            <div class="col-md-12" id="vcht_contactFieldsContainer">
                                                <p style="text-align: right;margin-bottom: 4px;">
                                                    <a href="javascript:" onclick="vcht_editField(0, 0);" class="btn btn-default">
                                                        <span class="fa fa-plus"></span>
                                                        <?php echo __('Add a field', 'WP_Visual_Chat'); ?>

                                                    </a>
                                                </p>
                                                <div class="table-responsive">
                                                    <table id="vcht_contactFieldsTable" class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th><?php echo __('Title', 'WP_Visual_Chat'); ?></th>
                                                                <th><?php echo __('Title on backend', 'WP_Visual_Chat'); ?></th>
                                                                <th><?php echo __('Type', 'WP_Visual_Chat'); ?></th>
                                                                <th><?php echo __('Is required', 'WP_Visual_Chat'); ?></th>
                                                                <th class="vcht_actionTh"><?php echo __('Action', 'WP_Visual_Chat'); ?></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                            <p style="text-align: center;">
                                                <a href="javascript:" class="btn btn-primary" onclick="vcht_saveSettings();"><span class="fa fa-floppy-o"></span> <?php echo __('Save', 'WP_Visual_Chat'); ?></a>
                                            </p>
                                        </div>
                                    </div>

                                    <div role="tabpanel" class="tab-pane vcht_scrollbar" id="vcht_tabSettingsRoles" >
                                        <div class="container-fluid">
                                            <table class="table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th><?php echo __('Role', 'WP_Visual_Chat'); ?></th>
                                                        <th><?php echo __('Allowed to be chat operator', 'WP_Visual_Chat'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    global $wp_roles;
                                                    $selected = '';
                                                    foreach ($wp_roles->roles as $key => $role) {
                                                        echo '<tr data-role="' . $key . '">';
                                                        echo '<td>' . $role['name'] . '</td>';
                                                        echo '<td><input type="checkbox" data-toggle="switch" name="' . $key . '" ' . $selected . '  /></td>';
                                                        echo '</tr>';
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                            <div class="clearfix"></div>
                                            <p style="text-align: center;">
                                                <a href="javascript:" class="btn btn-primary" onclick="vcht_saveAllowedRoles();"><span class="fa fa-floppy-o"></span> <?php echo __('Save', 'WP_Visual_Chat'); ?></a>
                                            </p>
                                        </div>
                                    </div>

                                    <div role="tabpanel" class="tab-pane" id="vcht_tabSettingsDesign" >
                                        <div class="container-fluid">

                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-group" >
                                                    <label > <?php echo __('Google font name', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="googleFont"  class="form-control" style="max-width: 160px;"  />
                                                    <a href="https://www.google.com/fonts" id="vcht_btnGoogleFont" target="_blank" class="btn btn-default btn-circle"><span class="glyphicon glyphicon-search"></span></a>

                                                    <small> <?php echo __('ex', 'WP_Visual_Chat'); ?> : Lato</small>
                                                </div>


                                                <div class="form-group" >
                                                    <label> <?php echo __('Chat panel position', 'WP_Visual_Chat'); ?>  </label >
                                                    <select name="chatPosition" class="form-control">
                                                        <option value="left"><?php echo __('Left', 'WP_Visual_Chat'); ?></option>
                                                        <option value="right"><?php echo __('Right', 'WP_Visual_Chat'); ?></option>
                                                    </select>
                                                    <small> <?php echo __('Choose the corner where the chat panel will be displayed', 'WP_Visual_Chat'); ?></small>
                                                </div>
                                                <div class="form-group" style="margin-top: 18px;">
                                                    <label><?php echo __('Bounce Fx', 'WP_Visual_Chat'); ?></label>
                                                    <input type="checkbox" name="bounceFx" data-toggle="switch" />
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo __('Main logo', 'WP_Visual_Chat'); ?></label>
                                                    <input type="text" name="chatLogo" class="form-control " style="max-width: 166px; margin-right: 10px;display: inline-block;" />
                                                    <a class="btn btn-default btn-circle imageBtn" style=" display: inline-block;"><span class="fa fa-pencil"></span></a>
                                                    <small> <?php echo __('Select an image', 'WP_Visual_Chat'); ?> </small>
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo __('Default operator avatar', 'WP_Visual_Chat'); ?></label>
                                                    <input type="text" name="defaultImgAvatar" class="form-control " style="max-width: 166px; margin-right: 10px;display: inline-block;" />
                                                    <a class="btn btn-default btn-circle imageBtn" style=" display: inline-block;"><span class="fa fa-pencil"></span></a>
                                                    <small> <?php echo __('Select an image', 'WP_Visual_Chat'); ?> </small>
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo __('Customer avatar', 'WP_Visual_Chat'); ?></label>
                                                    <input type="text" name="customerImgAvatar" class="form-control " style="max-width: 166px; margin-right: 10px;display: inline-block;" />
                                                    <a class="btn btn-default btn-circle imageBtn" style=" display: inline-block;"><span class="fa fa-pencil"></span></a>
                                                    <small> <?php echo __('Select an image', 'WP_Visual_Chat'); ?> </small>
                                                </div>

                                                <div class="form-group" >
                                                    <label> <?php echo __('Border radius of the chat panel', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="number" name="borderRadius" class="form-control" step="1" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : 5'; ?></small>
                                                </div>
                                                <div class="form-group" >
                                                    <label> <?php echo __('Width of the chat panel', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="number" name="widthPanel" class="form-control" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : 280'; ?></small>
                                                </div>
                                                <div class="form-group" >
                                                    <label> <?php echo __('Height of the chat panel', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="number" name="heightPanel" class="form-control" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : 380'; ?></small>
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo __('Panel shadow', 'WP_Visual_Chat'); ?></label>
                                                    <input type="checkbox" name="panelShadow" data-toggle="switch" />
                                                    <small> <?php echo __('It will show or hide the shadow effect behind the chat panel', 'WP_Visual_Chat') . ' : 380'; ?></small>
                                                </div>

                                            </div>
                                            <div class="col-md-6 col-lg-4">

                                                <div class="form-group" >
                                                    <label> <?php echo __('Shining elements color', 'WP_Visual_Chat'); ?>  </label >
                                                    <input type="text" name="color_shining" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #1abc9c'; ?></small>
                                                </div>

                                                <div class="form-group" >
                                                    <label> <?php echo __('Background color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_bg" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #ffffff'; ?></small>
                                                </div>
                                                <div class="form-group" >
                                                    <label> <?php echo __('Texts color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_texts" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #bdc3c7'; ?></small>
                                                </div>
                                                <div class="form-group" >
                                                    <label> <?php echo __('Labels color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_labels" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #bdc3c7'; ?></small>
                                                </div>


                                                <div class="form-group" >
                                                    <label> <?php echo __('Main icons color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_icons" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #1abc9c'; ?></small>
                                                </div>

                                                <div class="form-group" >
                                                    <label> <?php echo __('Header background color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_headerBg" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #1abc9c'; ?></small>
                                                </div>
                                                <div class="form-group" >
                                                    <label> <?php echo __('Header text color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_headerTexts" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #ffffff'; ?></small>
                                                </div>
                                                <div class="form-group" >
                                                    <label> <?php echo __('Header buttons background color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_headerBtnBg" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #1abc9c'; ?></small>
                                                </div>
                                                <div class="form-group" >
                                                    <label> <?php echo __('Header buttons text color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_headerBtnTexts" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #ffffff'; ?></small>
                                                </div>


                                                <div class="form-group" >
                                                    <label> <?php echo __('Scrollbar background color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_scrollBg" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #ecf0f1'; ?></small>
                                                </div>
                                                <div class="form-group" >
                                                    <label> <?php echo __('Scrollbar color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_scroll" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #bdc3c7'; ?></small>
                                                </div>
                                                <div class="form-group" >
                                                    <label> <?php echo __('Show element avatar background color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_showCircleBg" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #ecf0f1'; ?></small>
                                                </div>

                                                <div class="form-group" >
                                                    <label> <?php echo __('Tooltips background color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_tooltipBg" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #34495e'; ?></small>
                                                </div>                                                
                                                <div class="form-group" >
                                                    <label> <?php echo __('Tooltips text color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_tooltip" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #ffffff'; ?></small>
                                                </div>


                                            </div>
                                            <div class="col-md-6 col-lg-4">


                                                <div class="form-group" >
                                                    <label> <?php echo __('Operator bubbles background color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_operatorBubbleBg" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #ffffff'; ?></small>
                                                </div>
                                                <div class="form-group" >
                                                    <label> <?php echo __('Operator bubbles text color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_operatorBubbleTexts" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #ffffff'; ?></small>
                                                </div>
                                                <div class="form-group" >
                                                    <label> <?php echo __('Customer bubbles background color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_customerBubbleBg" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #ecf0f1'; ?></small>
                                                </div>
                                                <div class="form-group" >
                                                    <label> <?php echo __('Customer bubbles text color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_customerBubbleTexts" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #bdc3c7'; ?></small>
                                                </div>

                                                <div class="form-group" >
                                                    <label> <?php echo __('Main buttons background color', 'WP_Visual_Chat'); ?>  </label >
                                                    <input type="text" name="color_btnBg" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #1abc9c'; ?></small>
                                                </div>
                                                <div class="form-group" >
                                                    <label> <?php echo __('Main buttons text color', 'WP_Visual_Chat'); ?>  </label >
                                                    <input type="text" name="color_btnTexts" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #ffffff'; ?></small>
                                                </div>

                                                <div class="form-group" >
                                                    <label> <?php echo __('Secondary buttons background color', 'WP_Visual_Chat'); ?>  </label >
                                                    <input type="text" name="color_btnSecBg" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #bdc3c7'; ?></small>
                                                </div>
                                                <div class="form-group" >
                                                    <label> <?php echo __('Secondary buttons text color', 'WP_Visual_Chat'); ?>  </label >
                                                    <input type="text" name="color_btnSecTexts" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #ffffff'; ?></small>
                                                </div>

                                                <div class="form-group" >
                                                    <label> <?php echo __('Fields background color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_fieldsBg" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #ffffff'; ?></small>
                                                </div>
                                                <div class="form-group" >
                                                    <label> <?php echo __('Fields texts color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_fields" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #bdc3c7'; ?></small>
                                                </div>
                                                <div class="form-group" >
                                                    <label> <?php echo __('Fields border color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_fieldsBorder" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #bdc3c7'; ?></small>
                                                </div>
                                                <div class="form-group" >
                                                    <label> <?php echo __('Active field border color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_fieldsBorderFocus" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #1abc9c'; ?></small>
                                                </div>

                                                <div class="form-group" >
                                                    <label> <?php echo __('Loader background color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_loaderBg" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #1abc9c'; ?></small>
                                                </div>
                                                <div class="form-group" >
                                                    <label> <?php echo __('Loader color', 'WP_Visual_Chat'); ?> </label >
                                                    <input type="text" name="color_loader" class="form-control colorpick" />
                                                    <small> <?php echo __('ex', 'WP_Visual_Chat') . ' : #ffffff'; ?></small>
                                                </div>

                                            </div>
                                            <div class="clearfix"></div>
                                            <p style="text-align: center;">
                                                <a href="javascript:" class="btn btn-primary" onclick="vcht_saveSettings();"><span class="fa fa-floppy-o"></span> <?php echo __('Save', 'WP_Visual_Chat'); ?></a>
                                            </p>
                                            <div class="clearfix"></div>
                                        </div>
                                    </div>

                                    <div role="tabpanel" class="tab-pane" id="vcht_tabSettingsTexts" >
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label><?php echo __('Use po file/WPML ?', 'WP_Visual_Chat'); ?></label>
                                                <input type="checkbox" name="usePoFile" data-toggle="switch" />
                                                <small><?php echo __('Disable this option to edit the texts directly from this panel', 'WP_Visual_Chat'); ?></small>
                                            </div>
                                        </div>
                                        <table id="vcht_settingsTextsTable" class="table  table-striped">
                                            <thead>
                                                <tr>
                                                    <th style="width: 50%;"><?php echo __('Default text', 'WP_Visual_Chat'); ?></th>
                                                    <th><?php echo __('Final text', 'WP_Visual_Chat'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $table_name = $wpdb->prefix . "vcht_texts";
                                                $texts = $wpdb->get_results('SELECT * FROM ' . $table_name . ' ORDER BY id ASC');
                                                foreach ($texts as $text) {
                                                    if ($text->content == '') {
                                                        $text->content = $text->original;
                                                    }
                                                    echo '<tr data-id="' . $text->id . '">';
                                                    echo '<td>' . $text->original . '</td>';
                                                    echo '<td>';
                                                    echo '<div class="form-group">';
                                                    if ($text->isTextarea) {
                                                        echo '<textarea class="form-control">' . $text->content . '</textarea>';
                                                    } else {
                                                        echo '<input type="text" class="form-control" value="' . $text->content . '"/>';
                                                    }
                                                    echo '<small>' . $text->original . '</small>';
                                                    echo '</div>';
                                                    echo '</td>';
                                                    echo '</tr>';
                                                }
                                                ?>

                                            </tbody>
                                        </table>
                                        <div class="clearfix"></div>
                                        <p style="text-align: center;">
                                            <a href="javascript:" class="btn btn-primary" onclick="vcht_saveTexts();"><span class="fa fa-floppy-o"></span> <?php echo __('Save', 'WP_Visual_Chat'); ?></a>
                                        </p>
                                        <div class="clearfix"></div>
                                    </div>

                                </div>
                            </div>

                        </div>


                    </div>
                </div>
            <?php } ?>
            <div id="vcht_winUserAccount"class="modal" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <a href="javascript:" class="vcht_modalCloseBtn"  data-dismiss="modal"><span class="fa fa-times"></span></a>
                            <h4 class="modal-title"><?php echo __('My informations', 'WP_Visual_Chat'); ?></h4>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label><?php echo __('Username', 'WP_Visual_Chat'); ?> :</label>
                                <input type="text" class="form-control" name="username" value="<?php echo $user->username; ?>"/>
                            </div>
                            <div class="form-group">
                                <label><?php echo __('Email', 'WP_Visual_Chat'); ?> :</label>
                                <input type="email" class="form-control" name="email" value="<?php echo $user->email; ?>"/>
                            </div>
                            <div class="form-group">
                                <label><?php echo __('Avatar', 'WP_Visual_Chat'); ?> :</label>
                                <input type="text" name="imgAvatar" class="form-control" style="max-width: 180px; margin-right: 10px;display: inline-block;"  value="<?php echo $user->imgAvatar; ?>" />
                                <a class="btn btn-default btn-circle imageBtn" style=" display: inline-block;"><span class="fa fa-pencil"></span></a>
                                <small> <?php echo __('Select an image', 'WP_Visual_Chat'); ?> </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="javascript:" onclick="vcht_saveUserAccount();" class="btn btn-primary">
                                <span class="fa fa-check"></span>
                                <?php echo __('Confirm', 'WP_Visual_Chat'); ?>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
            <div id="vcht_winPickCannedMsg"class="modal" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <a href="javascript:" class="vcht_modalCloseBtn"  data-dismiss="modal"><span class="fa fa-times"></span></a>
                            <h4 class="modal-title"><?php echo __('Choose a canned message', 'WP_Visual_Chat'); ?></h4>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <select class="form-control" id="vcht_pickCannedMsgSelect"></select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="javascript:" onclick="vcht_pickCannedMsg();" class="btn btn-primary" data-dismiss="modal">
                                <span class="fa fa-check"></span>
                                <?php echo __('Confirm', 'WP_Visual_Chat'); ?>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <div id="vcht_winFilesUpload"class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <a href="javascript:" class="vcht_modalCloseBtn"  data-dismiss="modal"><span class="fa fa-times"></span></a>
                            <h4 class="modal-title"><?php echo __('Send files', 'WP_Visual_Chat'); ?></h4>
                        </div>
                        <div class="modal-body">
                            <div id="vcht_uploadFilesField" class="vcht_dropzone dropzone"></div>
                        </div>
                        <div class="modal-footer">
                            <a href="javascript:" onclick="vcht_validFilesUpload();" class="btn btn-primary" data-dismiss="modal">
                                <span class="fa fa-check"></span>
                                <?php echo __('Add to my message', 'WP_Visual_Chat'); ?>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <iframe id="vcht_webFrame" class="vcht_fullPanel"></iframe>
            <div id="vcht_loaderFrame" class="vcht_fullPanel">
                <div class="vcht_loaderCt">
                    <div class="vcht_loaderDot"></div>
                    <div class="vcht_loaderDot"></div>
                    <div class="vcht_loaderDot"></div>
                </div>
            </div>


            <div id="vcht_selectionInfoPanel">
                <a href="javascript:" class="vcht_closeBtn" onclick="vcht_cancelSelectElement();"><span class="fa fa-times"></span></a>
                <div data-step="0">
                    <p>
                        <span class="fa fa-info-circle"></span>
                        <?php echo __('Navigate to the desired page and click the button below', 'WP_Visual_Chat'); ?>
                    </p>
                    <p style="text-align: center;">
                        <a href="javascript:" class="btn btn-primary" onclick="vcht_selectAnElement();">
                            <span class="fa fa-hand-o-up"></span>
                            <?php echo __('Select the element', 'WP_Visual_Chat'); ?>
                        </a>
                    </p>
                </div>
                <div data-step="1">
                    <p>
                        <span class="fa fa-info-circle"></span>
                        <?php echo __('Click the desired element on the page', 'WP_Visual_Chat'); ?>
                    </p>
                    <p style="text-align: center;">
                        <a href="javascript:" class="btn btn-warning"  onclick="vcht_startShowElement();">
                            <span class="fa fa-ban"></span>
                            <?php echo __('Cancel', 'WP_Visual_Chat'); ?>
                        </a>
                    </p>
                </div>
                <div data-step="2">
                    <p>
                        <span class="fa fa-question-circle"></span>
                        <?php echo __('Do you want to show the item that is highlighted ?', 'WP_Visual_Chat'); ?>
                    </p>
                    <p style="text-align: center;">
                        <a href="javascript:" class="btn btn-primary" onclick="vcht_confirmSelectElement();">
                            <span class="fa fa-check"></span>
                            <?php echo __('Yes', 'WP_Visual_Chat'); ?>
                        </a>
                        <a href="javascript:" class="btn btn-warning" onclick="vcht_selectAnElement();">
                            <span class="fa fa-ban"></span>
                            <?php echo __('No', 'WP_Visual_Chat'); ?>
                        </a>
                    </p>
                </div>
            </div>

            <div id="vcht_fullHistoryPanel" class="vcht_fullPanel">
                <div class="vcht_fullPanelHeader">
                    <div class="vcht_fullPanelTb">
                        <a href="javascript:"  class="btn btn-circle btn-danger vcht_closeBtn"><span class="fa fa-times"></span></a>
                    </div>
                    <span class="fa fa-book"></span>
                    <?php echo __('Chats history', 'WP_Visual_Chat'); ?> <span></span></a>
                    <div class="clearfix"></div>
                </div>
                <div class="vcht_fullPanelBody vcht_scrollbar">
                    <div class="table-responsive" style="padding-left: 18px; padding-right: 18px;" >
                        <p style="text-align: right;">
                            <a href="javascript:" id="vcht_btnDeleteAllLogs" onclick="vcht_deleteAllLogs();" class="btn btn-warning">
                                <span class="fa fa-trash"></span>
                                <?php echo __('Delete all logs', 'WP_Visual_Chat'); ?>
                            </a>
                        </p>
                        <table id="vcht_fullHistoryTable" class="table  table-striped">
                            <thead>
                            <th><?php echo __('User', 'WP_Visual_Chat'); ?></th>
                            <th><?php echo __('Email', 'WP_Visual_Chat'); ?></th>
                            <th><?php echo __('Is operator', 'WP_Visual_Chat'); ?></th>
                            <?php if ($settings->enableGeolocalization) { ?>
                                <th><?php echo __('City', 'WP_Visual_Chat'); ?></th>
                                <th><?php echo __('Country', 'WP_Visual_Chat'); ?></th>
                            <?php } ?>
                            <th><?php echo __('IP', 'WP_Visual_Chat'); ?></th>
                            <th><?php echo __('Last activity', 'WP_Visual_Chat'); ?></th>
                            <th></th>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <div id="vcht_userHistoryPanel" class="vcht_fullPanel">
                <div class="vcht_fullPanelHeader">
                    <div class="vcht_fullPanelTb">
                        <a href="javascript:"  class="btn btn-circle btn-danger vcht_closeBtn"><span class="fa fa-times"></span></a>
                    </div>
                    <span class="fa fa-book"></span>
                    <?php echo __('History of the user', 'WP_Visual_Chat'); ?> <span></span></a>
                    <div class="clearfix"></div>
                </div>
                <div class="vcht_fullPanelBody vcht_scrollbar">
                    <div class="table-responsive" style="padding-left: 18px; padding-right: 18px;" >
                        <table id="vcht_userHistoryTable" class="table  table-striped">
                            <thead>
                            <th><?php echo __('Date', 'WP_Visual_Chat'); ?></th>
                            <th><?php echo __('Time', 'WP_Visual_Chat'); ?></th>
                            <?php if ($settings->enableGeolocalization) { ?>
                                <th><?php echo __('City', 'WP_Visual_Chat'); ?></th>
                                <th><?php echo __('Country', 'WP_Visual_Chat'); ?></th>
                            <?php } ?>
                            <th><?php echo __('IP', 'WP_Visual_Chat'); ?></th>
                            <th><?php echo __('Email', 'WP_Visual_Chat'); ?></th>
                            <th><?php echo __('User', 'WP_Visual_Chat'); ?></th>
                            <th><?php echo __('Message', 'WP_Visual_Chat'); ?></th>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <div id="vcht_cannedMsgsPanel" class="vcht_fullPanel">
                <div class="vcht_fullPanelHeader">
                    <div class="vcht_fullPanelTb">
                        <a href="javascript:" class="btn btn-circle btn-danger vcht_closeBtn"><span class="fa fa-times"></span></a>
                    </div>
                    <span class="fa fa-keyboard-o"></span>
                    <?php echo __('Canned messages', 'WP_Visual_Chat'); ?> <span></span></a>
                    <div class="clearfix"></div>
                </div>
                <div class="vcht_fullPanelBody vcht_scrollbar">
                    <p style="text-align: right;padding-left: 18px; padding-right: 18px;">
                        <a href="javascript:" onclick="vcht_editCannedMsg(0);" class="btn btn-default">
                            <span class="fa fa-plus"></span>
                            <?php echo __('Add a new message', 'WP_Visual_Chat'); ?>
                        </a>
                    </p>
                    <div class="table-responsive" style="padding-left: 18px; padding-right: 18px;" >
                        <table id="vcht_cannedMsgsTable" class="table table-striped">
                            <thead>
                            <th><?php echo __('Title', 'WP_Visual_Chat'); ?></th>
                            <th><?php echo __('Text', 'WP_Visual_Chat'); ?></th>
                            <th><?php echo __('Shortcut', 'WP_Visual_Chat'); ?></th>
                            <th><?php echo __('Actions', 'WP_Visual_Chat'); ?></th>
                            </thead>
                            <tbody>
                                <?php
                                $table_name = $wpdb->prefix . "vcht_cannedMessages";
                                $rows = $wpdb->get_results("SELECT * FROM " . $table_name . " ORDER BY Title ASC");
                                foreach ($rows as $shortcut) {
                                    echo '<tr data-id="' . $shortcut->id . '">';
                                    echo '<td><a href="javascript:" onclick="vcht_editCannedMsg(' . $shortcut->id . ');">' . $shortcut->title . '</a></td>';
                                    echo '<td>' . $shortcut->content . '</td>';
                                    echo '<td>' . $shortcut->shortcut . '</td>';
                                    echo '<td>';
                                    echo '<a href="javascript:" onclick="vcht_editCannedMsg(' . $shortcut->id . ');" class="btn btn-circle btn-primary"><span class="fa fa-pencil"></span></a>';
                                    if (!$shortcut->createdByAdmin || current_user_can('manage_options')) {
                                        echo '<a href="javascript:" class="btn btn-circle btn-danger"><span class="fa fa-trash"></span></a>';
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <div id="vcht_winCannedMessage"class="modal" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <a href="javascript:" class="vcht_modalCloseBtn"  data-dismiss="modal"><span class="fa fa-times"></span></a>
                            <h4 class="modal-title"><?php echo __('Edit a canned message', 'WP_Visual_Chat'); ?></h4>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label><?php echo __('Keyboard shortcut', 'WP_Visual_Chat'); ?> :</label>
                                <select name="keyB" class="form-control" >
                                    <option value="shift">SHIFT</option>
                                    <!--<option value="control">CONTROL</option>-->
                                    <option value="alt">ALT</option>
                                </select>
                                <span style=" margin-left: 18px; font-size: 16px;">+</span>
                                <input type="text" name="shortcut" maxlength="1" class="form-control" style="width: 48px;" />
                            </div>
                            <div class="form-group">
                                <label><?php echo __('Title', 'WP_Visual_Chat'); ?> :</label>
                                <input type="text" name="title" maxlength="20" class="form-control" />
                            </div>
                            <div class="form-group">
                                <label><?php echo __('Text', 'WP_Visual_Chat'); ?> :</label>
                                <textarea name="content" class="form-control" style="min-height: 66px;"></textarea>
                            </div>
                            <div style="padding: 14px; background-color: #bdc3c7; color: #FFF; font-size: 16px; text-align: center;">
                                <p>
                                    <?php echo __('Shortcodes that can be used in the text', 'WP_Visual_Chat'); ?> :
                                </p>
                                <ul>
                                    <li><strong>[user]</strong> : <?php echo __('Displays the username', 'WP_Visual_Chat'); ?></li>
                                    <li><strong>[operator]</strong> : <?php echo __('Displays the operator name', 'WP_Visual_Chat'); ?></li>
                                    <li><strong>[siteurl]</strong> : <?php echo __('Displays the website url', 'WP_Visual_Chat'); ?></li>
                                </ul>
                            </div>
                            <div class="form-group" style="margin-top: 18px;">
                                <label><?php echo __('Add to all operators', 'WP_Visual_Chat'); ?></label>
                                <input type="checkbox" name="createdByAdmin" data-toggle="switch" />
                            </div>

                        </div>
                        <div class="modal-footer">
                            <a href="javascript:" onclick="vcht_saveCannedMessage();" class="btn btn-primary">
                                <span class="fa fa-check"></span>
                                <?php echo __('Save', 'WP_Visual_Chat'); ?>
                            </a>
                        </div>
                    </div>

                </div>
            </div>


            <div id="vcht_editFieldPanel" class="vcht_fullPanel">
                <div class="vcht_fullPanelHeader">
                    <div class="vcht_fullPanelTb">
                        <a href="javascript:"  class="btn btn-circle btn-danger vcht_closeBtn"><span class="fa fa-times"></span></a>
                    </div>
                    <span class="fa fa-cogs"></span>
                    <?php echo __('Edit a field', 'WP_Visual_Chat'); ?></a>
                    <div class="clearfix"></div>
                </div>
                <div id="" class="vcht_fullPanelBody vcht_scrollbar">
                    <div class="container-fluid">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo __('Label', 'WP_Visual_Chat'); ?></label>
                                <input class="form-control" type="text" name="title" />
                                <small><?php echo __('The title that will be displayed on frontend'); ?></small>
                            </div>
                            <div class="form-group">
                                <label><?php echo __('Show on the backend chat panel ?', 'WP_Visual_Chat'); ?></label>
                                <input type="checkbox" name="showInDetails" data-toggle="switch" />
                                <small><?php echo __('Do you want to show this information in the chat panel for operators ?', 'WP_Visual_Chat'); ?></small>
                            </div>
                            <div class="form-group">
                                <label><?php echo __('Backend label', 'WP_Visual_Chat'); ?></label>
                                <input class="form-control" type="text"  name="backendTitle" />
                                <small><?php echo __('The title that will be displayed on backend'); ?></small>
                            </div>

                            <div class="form-group">
                                <label><?php echo __('Type', 'WP_Visual_Chat'); ?></label>
                                <select class="form-control"   name="type">
                                    <option value="checkbox">Checkbox</option>
                                    <option value="dropdown">Dropdown</option>
                                    <option value="numberfield">Numberfield</option>
                                    <option value="textarea">Textarea</option>
                                    <option value="textfield">Textfield</option>
                                </select>
                                <small><?php echo __('Choose a type for this field'); ?></small>
                            </div>

                            <div class="form-group">
                                <label><?php echo __('Type of information', 'WP_Visual_Chat'); ?></label>
                                <select class="form-control"   name="infoType">
                                    <option value="">None</option>
                                    <option value="email">Email</option>
                                    <option value="username">Username</option>
                                </select>
                                <small><?php echo __('Choose a type for this field'); ?></small>
                            </div>


                            <div class="form-group">
                                <label><?php echo __('Is required ?', 'WP_Visual_Chat'); ?></label>
                                <input type="checkbox" name="isRequired" data-toggle="switch" />
                                <small><?php echo __('Choose if the user must fill the field to continue', 'WP_Visual_Chat'); ?></small>
                            </div>


                        </div>
                        <div class="col-md-6">
                            <div id="vcht_itemOptionsValuesPanel" class="form-group">
                                <table id="vcht_itemOptionsValues" class="table">
                                    <thead>
                                        <tr>
                                            <th colspan="2"><?php echo __('Options of the dropdown', 'WP_Visual_Chat'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                       
                                    </tbody>
                                    <tfoot>
                                         <tr class="static">
                                            <th><div class="form-group" style="top: 10px;"><input type="text" id="option_new_value" class="form-control" value="" placeholder="<?php echo __('Option value', 'WP_Visual_Chat'); ?>"></div></th>
                                            <th style="width: 200px;"><a href="javascript:" onclick="vcht_addOptionDropdown();" class="btn btn-default"><span class="glyphicon glyphicon-plus" style="margin-right:8px;"></span><?php echo __('Add a new option', 'WP_Visual_Chat'); ?></a></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="form-group">
                                <label><?php echo __('Placeholder', 'WP_Visual_Chat'); ?></label>
                                <input class="form-control" type="text" name="placeholder" />
                                <small><?php echo __('Set the placeholder text for this field'); ?></small>
                            </div>


                            <div class="form-group" >
                                <label> <?php echo __('Default value', 'WP_Visual_Chat'); ?></label >
                                <input type="text" name="defaultValue" class="form-control" />
                                <small>  <?php echo __('Defines the default value of this field', 'WP_Visual_Chat'); ?> </small>
                            </div>

                            <div class="form-group" >
                                <label> <?php echo __('Validation', 'WP_Visual_Chat'); ?> </label >
                                <select name="validation" class="form-control">
                                    <option value=""><?php echo __('None', 'WP_Visual_Chat'); ?></option>
                                    <option value="phone"><?php echo __('Phone', 'WP_Visual_Chat'); ?></option>
                                    <option value="email"><?php echo __('Email', 'WP_Visual_Chat'); ?></option>
                                    <option value="fill"><?php echo __('Must be filled', 'WP_Visual_Chat'); ?></option>
                                    <option value="custom"><?php echo __('Custom', 'WP_Visual_Chat'); ?></option>
                                </select>
                                <small> <?php echo __('Select a validation method', 'WP_Visual_Chat'); ?> </small>
                            </div>

                            <div class="form-group" >
                                <label> <?php echo __('Characters required for validation', 'WP_Visual_Chat'); ?> </label >
                                <input type="text" name="validationCaracts" class="form-control" />
                                <small> <?php echo __('Fill the required characters separated by commas', 'WP_Visual_Chat'); ?> </small>
                            </div>

                            <div class="form-group" >
                                <label> <?php echo __('Min length', 'WP_Visual_Chat'); ?> </label >
                                <input type="number" name="validationMin" class="form-control" />
                                <small> <?php echo __('Enter the minimum required length', 'WP_Visual_Chat'); ?> </small>
                            </div>

                            <div class="form-group" >
                                <label> <?php echo __('Max length', 'WP_Visual_Chat'); ?> </label >
                                <input type="number" name="validationMax" class="form-control" />
                                <small> <?php echo __('Enter the maximum required length', 'WP_Visual_Chat'); ?> </small>
                            </div>

                            <div class="form-group" >
                                <label>  <?php echo __('Icon', 'WP_Visual_Chat'); ?></label>
                                <input type="hidden" class="form-control vcht_iconField" name="icon"  />
                                <div class="btn-group vcht_btnGroupDrop">
                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
                                        <span class="vcht_name"></span><span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu vcht_iconslist" role="menu" >
                                        <?php echo $this->getIconsOptionsList(); ?>
                                    </ul></div>
                                <small>  <?php echo __('Select an icon', 'WP_Visual_Chat'); ?> </small>
                            </div>

                            <div class="form-group" >
                                <label>  <?php echo __('Icon position', 'WP_Visual_Chat'); ?> </label >
                                <select name="iconPosition" class="form-control">
                                    <option value="0"> <?php echo __('Left', 'WP_Visual_Chat'); ?></option>
                                    <option value="1"> <?php echo __('Right', 'WP_Visual_Chat'); ?></option>
                                </select>
                                <small>  <?php echo __('Select the position of the icon', 'WP_Visual_Chat'); ?> </small>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <p style="text-align: center;">
                            <a href="javascript:" class="btn btn-primary" onclick="vcht_saveField();"><span class="fa fa-floppy-o"></span> <?php echo __('Save', 'WP_Visual_Chat'); ?></a>
                        </p>
                        <div class="clearfix"></div>

                    </div>
                </div>
            </div>



        </div>
        <?php
    }

    public function ajax_operatorLogIn() {
        if (current_user_can('visual_chat')) {
            global $wpdb;

            $user = wp_get_current_user();
            $table_name = $wpdb->prefix . "vcht_users";
            $rows = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_users WHERE userID=" . $user->ID);
            $wpdb->update($table_name, array('lastActivity' => date('Y-m-d H:i:s'), 'isOperator' => true, 'isOnline' => true, 'username' => $user->display_name), array('userID' => $user->ID));
        }
        die();
    }

    public function ajax_operatorLogOut() {
        if (current_user_can('visual_chat')) {
            global $wpdb;
            $user = wp_get_current_user();
            $table_name = $wpdb->prefix . "vcht_users";
            $wpdb->update($table_name, array('isOnline' => false), array('userID' => $user->ID));
        }
        die();
    }

    public function ajax_operatorGetOnlineVisitors() {
        if (current_user_can('visual_chat')) {
            $settings = $this->getSettings();
            global $wpdb;
            $user = wp_get_current_user();

            $table_name = $wpdb->prefix . "vcht_users";

            $time = strtotime(date("Y-m-d H:i:s"));
            $time = $time - ($settings->trackingDelay + 10);
            $date = date("Y-m-d H:i:s", $time);
            $operatorCondition = '';
            if (!$settings->enableVisitorsTracking) {
                //$operatorCondition = ' AND isOperator=1 ';
            }
            $rows = $wpdb->get_results('SELECT id,username,isOperator,currentPage,currentOperator,country,city,isOnline,lastActivity,userID,imgAvatar FROM ' . $table_name . ' WHERE isOnline=1 ' . $operatorCondition . ' AND lastActivity> "' . $date . '"');
            $rep = array();
            foreach ($rows as $row) {
                $dataObj = new stdClass();
                $dataObj->id = $row->id;
                $dataObj->username = $row->username;
                $dataObj->isOperator = $row->isOperator;
                $dataObj->isCurrentUser = false;
                $dataObj->currentPage = $row->currentPage;
                $dataObj->currentOperator = $row->currentOperator;
                $dataObj->country = $row->country;
                $dataObj->imgAvatar = $row->imgAvatar;
                $dataObj->city = $row->city;
                if ($user->ID == $row->userID && $row->isOperator == 1) {
                    $dataObj->isCurrentUser = true;
                }
                $rep[] = $dataObj;
            }

            $user = wp_get_current_user();
            $wpdb->update($table_name, array('lastActivity' => date('Y-m-d H:i:s'), 'isOperator' => true), array('userID' => $user->ID));
            echo json_encode($rep, true);
        }
        die();
    }

    public function ajax_getUsersInfos() {
        if (current_user_can('visual_chat')) {
            global $wpdb;
            $rep = array();
            $table_name = $wpdb->prefix . "vcht_users";

            $usersIDs = stripslashes(sanitize_text_field($_POST['usersIDs']));
            $usersIDs = json_decode($usersIDs, true);
            foreach ($usersIDs as $userID) {
                if ($userID != "") {
                    $rows = $wpdb->get_results($wpdb->prepare('SELECT id,username,imgAvatar,isOperator,uploadFolderName,fieldsJson,city,country,currentOperator FROM ' . $wpdb->prefix . 'vcht_users WHERE id=%s', $userID));
                    if (count($rows) > 0) {
                        $userObj = new stdClass();
                        $userObj->id = $rows[0]->id;
                        $userObj->username = $rows[0]->username;
                        $userObj->avatar = $rows[0]->imgAvatar;
                        $userObj->isOperator = $rows[0]->isOperator;
                        $userObj->uploadFolderName = $rows[0]->uploadFolderName;
                        $userObj->fields = stripslashes($rows[0]->fieldsJson);
                        $userObj->city = $rows[0]->city;
                        $userObj->country = $rows[0]->country;
                        if ($userObj->country == '-' || $userObj->country == ' - ') {
                            $userObj->country = '';
                        }
                        $userObj->currentOperator = $rows[0]->currentOperator;
                        $rep[] = $userObj;
                    }
                }
            }
            $repJson = json_encode($rep, true);

            echo $repJson;
        }
        die();
    }

    public function ajax_sendMessage() {
        if (current_user_can('visual_chat')) {
            global $wpdb;
            $user = wp_get_current_user();
            $table_name = $wpdb->prefix . "vcht_users";
            $users = $wpdb->get_results("SELECT userID,id FROM $table_name WHERE userID=" . $user->ID . " LIMIT 1");
            if (count($users) > 0) {
                $user = $users[0];

                $receiverID = stripslashes(sanitize_text_field($_POST['receiverID']));
                $content = implode("<br/>", array_map('sanitize_text_field', explode("<br/>", $_POST['content'])));
                $files = stripslashes(sanitize_text_field($_POST['files']));
                $domElement = stripslashes(sanitize_text_field($_POST['domElement']));
                $page = stripslashes(sanitize_text_field($_POST['page']));

                $table_name = $wpdb->prefix . "vcht_users";
                $wpdb->update($table_name, array('currentOperator' => $user->id), array('id' => $receiverID));

                $table_name = $wpdb->prefix . "vcht_messages";
                $wpdb->insert($table_name, array('msgDate' => date("Y-m-d H:i:s"), 'senderID' => $user->id, 'receiverID' => $receiverID,
                    'content' => stripslashes($content),
                    'files' => $files,
                    'domElement' => $domElement,
                    'page' => $page,
                    'transferID' => 0));
                echo $wpdb->insert_id;
            } else {
                
            }
        }
//die();
    }

    public function ajax_operatorGetNewMessages() {
        if (current_user_can('visual_chat')) {
            global $wpdb;
            $user = wp_get_current_user();
            $table_name = $wpdb->prefix . "vcht_users";
            $users = $wpdb->get_results("SELECT userID,id FROM $table_name WHERE userID=" . $user->ID . " LIMIT 1");
            if (count($users) > 0) {
                $settings = $this->getSettings();
                $user = $users[0];
                $table_name = $wpdb->prefix . "vcht_messages";

                $time = strtotime(date("Y-m-d H:i:s"));
                $time = $time - $settings->ajaxCheckDelay * 2;
                $date = date("Y-m-d H:i:s", $time);

                $rep = array();

                $table_name = $wpdb->prefix . "vcht_messages";
                $messages = $wpdb->get_results("SELECT * FROM $table_name WHERE (receiverID=" . $user->id . " OR receiverID=0) AND msgDate>'" . $date . "'");

                foreach ($messages as $message) {
                    $msgObj = new stdClass();
                    $msgObj->id = $message->id;
                    $msgObj->senderID = $message->senderID;
                    $msgObj->receiverID = $message->receiverID;
                    $msgObj->date = $message->msgDate;
                    $msgObj->content = $message->content;
                    $msgObj->files = $message->files;
                    $msgObj->domElement = $message->domElement;
                    $msgObj->page = $message->page;
                    $msgObj->type = $message->type;
                    $msgObj->transferUsername = $message->transferUsername;
                    $msgObj->transferID = $message->transferID;


                    if ($message->receiverID == "-1") {
                        $table_name = $wpdb->prefix . "vcht_users";
                        $sender = $wpdb->get_results("SELECT id,username FROM $table_name WHERE id=" . $message->senderID . " LIMIT 1");
                        if (count($sender) > 0) {
                            $sender = $sender[0];
                            $msgObj->username = $sender->username;
                        }
                    }

                    $rep[] = $msgObj;
                }
                $table_name = $wpdb->prefix . "vcht_users";
                $wpdb->update($table_name, array('lastActivity' => date('Y-m-d H:i:s'), 'isOperator' => true), array('id' => $user->id));
                echo json_encode($rep, true);
            }
        }
        die();
    }

    public function ajax_loadSettings() {
        if (current_user_can('manage_options')) {
            global $wpdb;
            $rep = new stdClass();
            $rep->settings = $this->getSettings();
            $table_name = $wpdb->prefix . "vcht_fields";
            $rep->fields = $wpdb->get_results('SELECT * FROM ' . $table_name . ' ORDER BY ordersort ASC');

            echo json_encode($rep, true);
        }
        die();
    }

    public function ajax_uploadFile() {
        if (current_user_can('visual_chat')) {
            global $wpdb;

            $receiverID = sanitize_text_field($_POST['receiverID']);
            $table_name = $wpdb->prefix . "vcht_users";
            $users = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'vcht_users WHERE id=%s LIMIT 1', $receiverID));
            if (count($users) > 0) {
                $settings = $this->getSettings();
                $user = $users[0];

                $maxSize = 25;
                if ($settings->filesMaxSize > 0) {
                    $maxSize = $settings->filesMaxSize;
                }
                if (count($rows) > 0) {
                    $maxSize = $rows[0]->fileSize;
                }
                if ($maxSize == 0) {
                    $maxSize = 25;
                }
                $maxSize = $maxSize * pow(1024, 2);

                foreach ($_FILES as $key => $value) {
                    if ($value["error"] > 0) {
                        echo "error";
                    } else {
                        if (strlen($value["name"]) > 4 &&
                                $value['size'] < $maxSize &&
                                strpos(strtolower($value["name"]), '.php') === false &&
                                strpos(strtolower($value["name"]), '.js') === false &&
                                strpos(strtolower($value["name"]), '.html') === false &&
                                strpos(strtolower($value["name"]), '.phtml') === false &&
                                strpos(strtolower($value["name"]), '.pl') === false &&
                                strpos(strtolower($value["name"]), '.py') === false &&
                                strpos(strtolower($value["name"]), '.jsp') === false &&
                                strpos(strtolower($value["name"]), '.asp') === false &&
                                strpos(strtolower($value["name"]), '.htm') === false &&
                                strpos(strtolower($value["name"]), '.shtml') === false &&
                                strpos(strtolower($value["name"]), '.sh') === false &&
                                strpos(strtolower($value["name"]), '.cgi') === false
                        ) {
                            $fileName = str_replace(' ', '_', $value["name"]);

                            if (!is_dir($this->uploads_dir . '/' . $user->uploadFolderName)) {
                                mkdir($this->uploads_dir . '/' . $user->uploadFolderName);
                                chmod($this->uploads_dir . '/' . $user->uploadFolderName, 0747);
                                 $fp = fopen($this->uploads_dir . '/' . $user->uploadFolderName.'/.htaccess', 'w+');
                                fwrite($fp, '<FilesMatch "\.(htaccess|htpasswd|ini|phps?|fla|psd|log|sh|zip|exe|pl|jsp|asp|htm|pht|phar|sh|cgi|py|php|php\.)$">'."\n");
                                fwrite($fp, 'Order Allow,Deny'."\n");
                                fwrite($fp, 'Deny from all'."\n");
                                fwrite($fp, '</FilesMatch>');
                                fclose($fp);  
            
                            }
                            move_uploaded_file($value["tmp_name"], $this->uploads_dir . '/' . $user->uploadFolderName . '/' . $fileName);
                            chmod($this->uploads_dir . '/' . $user->uploadFolderName . '/' . $fileName, 0644);
                        }
                    }
                }
            }
        }
        die();
    }

    public function ajax_getLastHistory() {
        if (current_user_can('visual_chat')) {
            global $wpdb;

            $rep = array();
            $userID = sanitize_text_field($_POST['userID']);
            $table_name = $wpdb->prefix . "vcht_messages";

            $time = strtotime(date("Y-m-d H:i:s"));
            $time = $time - (60 * 60);
            $date = date("Y-m-d H:i:s", $time);

            $messages = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $table_name . ' WHERE (receiverID=%s OR senderID=%s) AND msgDate >%s  ORDER BY id DESC LIMIT 10', $senderID, $senderID, $date));

            if (count($messages) > 0) {
                $settings = $this->getSettings();
                $message = $messages[0];
                $rep[] = $message;
            }

            echo json_encode($rep, true);
        }
        die();
    }

    public function ajax_saveSettings() {
        if (current_user_can('manage_options')) {
            global $wpdb;
            $sqlDatas = array();
            $purchaseCode = sanitize_text_field($_POST['vcht_purchaseCode']);
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'vcht_') === 0 && $key != 'vcht_purchaseCode') {
                    $sqlDatas[substr($key, 5)] = stripslashes($value);
                }
            }
            $table_name = $wpdb->prefix . "vcht_settings";
            $wpdb->update($table_name, $sqlDatas, array('id' => 1));
            
            $settings = $this->getSettings();
            if($purchaseCode != $settings->purchaseCode ){
                $this->checkLicenseCall($purchaseCode);
            }


            die();
        }
    }
    private function checkLicenseCall($purchaseCode) {
        if (current_user_can('manage_options')) {
            global $wpdb;
            try {

                $url = 'http://www.loopus-plugins.com/updates/update.php?checkCode=8329900&code=' . $purchaseCode;
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $rep = curl_exec($ch);
                echo $rep;
                if ($rep != '0410') {
                    $table_name = $wpdb->prefix . "vcht_settings";
                    $wpdb->update($table_name, array('purchaseCode' => $purchaseCode), array('id' => 1));
                } else {
                    $table_name = $wpdb->prefix . "vcht_settings";
                    $wpdb->update($table_name, array('purchaseCode' => ''), array('id' => 1));
                }
            } catch (Throwable $t) {
                $table_name = $wpdb->prefix . "vcht_settings";
                $wpdb->update($table_name, array('purchaseCode' => $purchaseCode), array('id' => 1));
            } catch (Exception $e) {
                $table_name = $wpdb->prefix . "vcht_settings";
                $wpdb->update($table_name, array('purchaseCode' =>$purchaseCode), array('id' => 1));
            }
        }
    }

    public function ajax_getFullHistory() {
        if (current_user_can('visual_chat')) {
            global $wpdb;
            $rep = array();
            $table_name = $wpdb->prefix . "vcht_users";
            $users = $wpdb->get_results('SELECT id,username,country,city,lastActivity,isOperator,ip,email FROM ' . $table_name . ' ORDER BY lastActivity DESC');
            foreach ($users as $user) {
                $table_name = $wpdb->prefix . "vcht_messages";
                $messages = $wpdb->get_results($wpdb->prepare('SELECT receiverID,senderID FROM ' . $table_name . ' WHERE receiverID=%s OR senderID=%s AND type="message" LIMIT 1', $user->id, $user->id));
                if (count($messages) > 0) {
                    $rep[] = $user;
                }
            }

            echo json_encode($rep, true);
            die();
        }
    }

    public function ajax_getUserHistory() {
        if (current_user_can('visual_chat')) {
            global $wpdb;

            $rep = array();
            $userID = sanitize_text_field($_POST['userID']);
            $table_name = $wpdb->prefix . "vcht_messages";
            $messages = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $table_name . ' WHERE receiverID=%s OR senderID=%s ORDER BY id DESC', $userID, $userID));

            foreach ($messages as $message) {
                if ($message != 'receiveTransfer') {
                    $data = new stdClass();
                    $data->id = $message->id;


                    $table_name = $wpdb->prefix . "vcht_users";
                    $users = $wpdb->get_results($wpdb->prepare('SELECT id,username,country,city,uploadFolderName,isOperator,ip,email FROM ' . $table_name . ' WHERE id=%s LIMIT 1', $message->senderID));

                    $sender = null;
                    $chkSender = false;
                    if (count($users) > 0) {
                        $chkSender = true;
                        $sender = $users[0];
                        $data->username = $users[0]->username;
                        $data->ip = $users[0]->ip;
                        $data->country = $users[0]->country;
                        $data->city = $users[0]->city;
                        $data->email = $users[0]->email;
                    } else {
                        $data->username = __('A visitor', 'WP_Visual_Chat');
                        $data->country = '';
                        $data->city = '';
                        $data->ip = '';
                        $data->email = '';
                    }
                    $uploadPath = '';


                    if ($message->receiverID == $userID) {
                        $uploadPath = $this->uploads_url . $users[0]->uploadFolderName . '/';
                    } else {
                        if ($chkSender && !$sender->isOperator) {
                            $uploadPath = $this->uploads_url . $sender->uploadFolderName . '/';
                        } else {
                            $table_name = $wpdb->prefix . "vcht_users";
                            $receivers = $wpdb->get_results($wpdb->prepare('SELECT id,uploadFolderName FROM ' . $table_name . ' WHERE id=%s LIMIT 1', $message->receiverID));
                            if (count($receivers) > 0) {
                                $uploadPath = $this->uploads_url . $receivers[0]->uploadFolderName . '/';
                            } else {
                                $uploadPath = '[noFile]';
                            }
                        }
                    }

                    $date = new DateTime($message->msgDate);
                    $data->date = $date->format('Y-m-d');
                    $data->time = $date->format('h:m');
                    $data->content = $message->content;
                    $data->files = $message->files;
                    $data->uploadUrl = $uploadPath;
                    $data->page = $message->page;
                    $data->type = $message->type;
                    $data->domElement = $message->domElement;

                    $rep[] = $data;
                }
            }


            echo json_encode($rep, true);
        }
        die();
    }

    public function ajax_deleteAllLogs() {
        if (current_user_can('manage_options')) {
            global $wpdb;
            $table_name = $wpdb->prefix . "vcht_messages";
            $wpdb->query("TRUNCATE TABLE $table_name");
        }
        die();
    }

    public function ajax_getCannedMsgs() {
        if (current_user_can('visual_chat')) {
            global $wpdb;
            $rep = array();
            $table_name = $wpdb->prefix . "vcht_cannedMessages";
            $rep = $wpdb->get_results("SELECT * FROM $table_name ORDER BY title ASC");
            echo json_encode($rep, true);
        }
        die();
    }

    public function ajax_removeCannedMsg() {
        if (current_user_can('visual_chat')) {
            global $wpdb;
            $id = sanitize_text_field($_POST['id']);
            $rep = array();
            $table_name = $wpdb->prefix . "vcht_cannedMessages";
            $rep = $wpdb->get_results("SELECT * FROM $table_name WHERE id=$id LIMIT 1");
            if (count($rep) > 0) {
                $msg = $rep[0];
                if (!$msg->createdByAdmin || current_user_can('manage_options')) {
                    $wpdb->delete($table_name, array('id' => $id));
                }
            }
        }
        die();
    }

    public function ajax_saveCannedMessage() {
        if (current_user_can('visual_chat')) {
            global $wpdb;
            $id = sanitize_text_field($_POST['id']);
            $title = sanitize_text_field($_POST['title']);
            $content = implode("\n", array_map('sanitize_text_field', explode("\n", $_POST['content'])));
            $keyB = sanitize_text_field($_POST['keyB']);
            $shortcut = sanitize_text_field($_POST['shortcut']);
            $createdByAdmin = sanitize_text_field($_POST['createdByAdmin']);

            if (!current_user_can('manage_options')) {
                $createdByAdmin = 0;
            }
            $data = array('title' => $title, 'content' => $content, 'shortcut' => $shortcut, 'keyB' => $keyB, 'createdByAdmin' => $createdByAdmin);

            $table_name = $wpdb->prefix . "vcht_cannedMessages";
            if ($id > 0) {
                $wpdb->update($table_name, $data, array('id' => $id));
            } else {
                $wpdb->insert($table_name, $data);
            }
        }
        die();
    }

    public function ajax_getFields() {
        if (current_user_can('manage_options')) {

            global $wpdb;
            $table_name = $wpdb->prefix . "vcht_fields";
            echo json_encode($wpdb->get_results('SELECT * FROM ' . $table_name . ' ORDER BY ordersort ASC'), true);
        }
        die();
    }

    public function ajax_removeField() {
        if (current_user_can('manage_options')) {
            global $wpdb;
            $id = sanitize_text_field($_POST['id']);
            $rep = array();
            $table_name = $wpdb->prefix . "vcht_fields";
            $wpdb->delete($table_name, array('id' => $id));
            if (function_exists('icl_register_string')) {
                icl_register_string('VisualChat Frontend', 'field_' . $id . '_label', $sqlDatas['title']);
                icl_register_string('VisualChat Frontend', 'field_' . $id . '_placeholder', $sqlDatas['placeholder']);
            }
        }
        die();
    }

    public function ajax_changeFieldsOrders() {
        if (current_user_can('manage_options')) {
            global $wpdb;
            $fields = sanitize_text_field($_POST['fields']);
            $fields = explode(',', $fields);
            $table_name = $wpdb->prefix . "vcht_fields";
            foreach ($fields as $key => $value) {
                $wpdb->update($table_name, array('ordersort' => $key), array('id' => $value));
            }
        }
        die();
    }

    public function ajax_saveField() {
        if (current_user_can('manage_options')) {
            global $wpdb;

            $id = sanitize_text_field($_POST['id']);

            $sqlDatas = array();
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'vcht_') === 0) {
                    $sqlDatas[substr($key, 5)] = stripslashes($value);
                }
            }

            if (function_exists('icl_register_string')) {
                icl_register_string('VisualChat Frontend', 'field_' . $id . '_label', $sqlDatas['title']);
                icl_register_string('VisualChat Frontend', 'field_' . $id . '_placeholder', $sqlDatas['placeholder']);
            }


            $table_name = $wpdb->prefix . "vcht_fields";
            if ($id > 0) {
                $wpdb->update($table_name, $sqlDatas, array('id' => $id));
            } else {
                $wpdb->insert($table_name, $sqlDatas);
            }
        }
        die();
    }

    public function ajax_saveAllowedRoles() {
        if (current_user_can('manage_options')) {
            global $wpdb;
            $roles = sanitize_text_field($_POST['roles']);
            $table_name = $wpdb->prefix . "vcht_settings";
            $wpdb->update($table_name, array('rolesAllowed' => $roles), array('id' => 1));
        }
        die();
    }

    public function ajax_acceptChat() {
        if (current_user_can('visual_chat')) {
            global $wpdb;

            $userID = sanitize_text_field($_POST['userID']);
            $user = wp_get_current_user();
            $table_name = $wpdb->prefix . "vcht_users";
            $users = $wpdb->get_results("SELECT * FROM $table_name WHERE userID=" . $user->ID . " LIMIT 1");
            if (count($users) > 0) {
                $user = $users[0];

                $table_name = $wpdb->prefix . "vcht_users";
                $rows = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'vcht_users WHERE id=%s', $userID));
                if (count($rows) > 0) {
                    $client = $rows[0];
                    if ($client->currentOperator == 0) {
                        $table_name = $wpdb->prefix . "vcht_users";
                        $wpdb->update($table_name, array('currentOperator' => $user->id), array('id' => $client->id));
                    } else {
                        if ($client->currentOperator != $user->id) {
                            echo 'op';
                        }
                    }
                }
            }
            die();
        }
    }

    public function ajax_closeChat() {
        if (current_user_can('visual_chat')) {
            global $wpdb;
            $user = wp_get_current_user();
            $table_name = $wpdb->prefix . "vcht_users";
            $users = $wpdb->get_results("SELECT * FROM $table_name WHERE userID=" . $user->ID . " LIMIT 1");
            if (count($users) > 0) {
                $user = $users[0];

                $receiverID = stripslashes(sanitize_text_field($_POST['receiverID']));

                $receivers = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'vcht_users WHERE id=%s', $receiverID));
                $isOnline = 0;
                if (count($receivers) > 0) {
                    $receiver = $receivers[0];
                    if ($receiver->isOperator) {
                        $isOnline = 1;
                    }
                }

                $table_name = $wpdb->prefix . "vcht_messages";
                $wpdb->insert($table_name, array('msgDate' => date("Y-m-d H:i:s"), 'senderID' => $user->id, 'receiverID' => $receiverID,
                    'type' => 'close'));
                $table_name = $wpdb->prefix . "vcht_users";
                $wpdb->update($table_name, array('currentOperator' => 0, 'isOnline' => $isOnline, 'lastActivity' => date('Y-m-d H:i:s')), array('id' => $receiverID));
            }
            die();
        }
    }

    public function ajax_saveTexts() {
        if (current_user_can('manage_options')) {
            global $wpdb;
            $sqlDatas = array();
            $usePoFile = sanitize_text_field($_POST['usePoFile']);

            $table_name = $wpdb->prefix . "vcht_settings";
            $wpdb->update($table_name, array('usePoFile' => $usePoFile), array('id' => 1));


            $table_name = $wpdb->prefix . "vcht_texts";
            foreach ($_POST as $key => $value) {
                if (substr($key, 0, 6) == 'field_') {
                    $id = substr($key, 6);
                    if ($value != "") {
                        $wpdb->update($table_name, array('content' => stripslashes($value)), array('id' => $id));
                    }
                }
            }

            if ($sqlDatas['usePoFile'] == 1) {
                if (function_exists('icl_register_string')) {
                    $db_table_name = $wpdb->prefix . "vcht_fields";
                    $fields = $wpdb->get_results("SELECT * FROM $db_table_name ORDER BY id ASC");
                    foreach ($fields as $field) {
                        icl_register_string('VisualChat Frontend', 'field_' . $field->id . '_label', $field->title);
                        icl_register_string('VisualChat Frontend', 'field_' . $field->id . '_placeholder', $field->placeholder);
                    }
                    $db_table_name = $wpdb->prefix . "vcht_texts";
                    $texts = $wpdb->get_results("SELECT * FROM $db_table_name ORDER BY id ASC");
                    foreach ($texts as $text) {
                        icl_register_string('VisualChat Frontend', $text->original, $text->content);
                    }
                }
            }
        }
        die();
    }

    public function ajax_saveUserAccount() {
        if (current_user_can('visual_chat')) {
            global $wpdb;
            $username = sanitize_text_field($_POST['username']);
            $email = sanitize_text_field($_POST['email']);
            $imgAvatar = sanitize_text_field($_POST['imgAvatar']);

            $user = wp_get_current_user();
            $table_name = $wpdb->prefix . "vcht_users";
            $users = $wpdb->get_results("SELECT * FROM $table_name WHERE userID=" . $user->ID . " LIMIT 1");
            if (count($users) > 0) {
                $user = $users[0];
                $wpdb->update($table_name, array('username' => $username, 'email' => $email, 'imgAvatar' => $imgAvatar), array('id' => $user->id));
            }
        }
        die();
    }

//get city info from IP Adress
    private function get_country_city_from_ip($ip) {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException("IP is not valid");
        }

//contact ip-server
        $response = @file_get_contents('http://www.netip.de/search?query=' . $ip);
        if (empty($response)) {
            return "error";
        }

        $patterns = array();
        $patterns["domain"] = '#Name: (.*?)&nbsp;#i';
        $patterns["country"] = '#Country: (.*?)&nbsp;#i';
        $patterns["state"] = '#State/Region: (.*?)<br#i';
        $patterns["town"] = '#City: (.*?)<br#i';

        $ipInfo = array();

        foreach ($patterns as $key => $pattern) {
            $ipInfo[$key] = preg_match($pattern, $response, $value) && !empty($value[1]) ? $value[1] : 'not found';
        }

        return $ipInfo;
    }

    public function ajax_geolocalize() {
        if (current_user_can('visual_chat')) {
            global $wpdb;
            $user = wp_get_current_user();
            $table_name = $wpdb->prefix . "vcht_users";
            $users = $wpdb->get_results("SELECT * FROM $table_name WHERE userID=" . $user->ID . " LIMIT 1");
            if (count($users) > 0) {
                $user = $users[0];
                $country_info = $this->get_country_city_from_ip($_SERVER['REMOTE_ADDR']);
                if ($country_info['country'] != 'not found') {
                    $country = $country_info['country'];
                }
                if ($country_info['town'] != 'not found') {
                    $city = $country_info['town'];
                }
                $wpdb->update($wpdb->prefix . 'vcht_users', array('country' => $country, 'city' => $city), array('id' => $user->id));
            }
        }
        die();
    }

    public function ajax_transferChat() {
        if (current_user_can('visual_chat')) {
            global $wpdb;
            $user = wp_get_current_user();
            $operatorID = sanitize_text_field($_POST['operatorID']);
            $userID = sanitize_text_field($_POST['userID']);
            $table_name = $wpdb->prefix . "vcht_users";
            $users = $wpdb->get_results("SELECT * FROM $table_name WHERE userID=" . $user->ID . " LIMIT 1");
            if (count($users) > 0) {
                $user = $users[0];


                $operators = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id=%s LIMIT 1", $operatorID));
                if (count($operators) > 0) {
                    $operator = $operators[0];

                    $table_name = $wpdb->prefix . "vcht_messages";
                    $wpdb->insert($table_name, array('msgDate' => date("Y-m-d H:i:s"), 'senderID' => $user->id, 'receiverID' => $userID,
                        'type' => 'transfer', 'transferUsername' => $operator->username, 'transferID' => $user->ID));
                    $wpdb->update($wpdb->prefix . 'vcht_users', array('currentOperator' => $operatorID), array('id' => $userID));
                    $wpdb->insert($table_name, array('msgDate' => date("Y-m-d H:i:s"), 'senderID' => $userID, 'receiverID' => $operatorID,
                        'type' => 'receiveTransfer', 'transferUsername' => $user->username, 'transferID' => $user->ID));
                }
            }
        }
    }

    public function ajax_removeUserLogs() {
        if (current_user_can('manage_options')) {
            global $wpdb;
            $userID = sanitize_text_field($_POST['userID']);

            $table_name = $wpdb->prefix . "vcht_messages";
            $wpdb->delete($table_name, array('senderID' => $userID));
            $wpdb->delete($table_name, array('receiverID' => $userID));
            die();
        }
    }

    /**
     * Main Instance
     *
     *
     * @since 1.0.0
     * @static
     * @return Main instance
     */
    public static function instance($parent) {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($parent);
        }
        return self::$_instance;
    }

// End instance()

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone() {
        _doing_it_wrong(__FUNCTION__, '', $this->parent->_version);
    }

// End __clone()

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup() {
        _doing_it_wrong(__FUNCTION__, '', $this->parent->_version);
    }

}
