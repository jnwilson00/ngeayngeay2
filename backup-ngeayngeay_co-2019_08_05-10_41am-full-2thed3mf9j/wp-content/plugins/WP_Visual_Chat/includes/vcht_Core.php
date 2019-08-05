<?php

if (!defined('ABSPATH'))
    exit;

class vcht_Core {

    /**
     * The single instance
     * @var    object
     * @access  private
     * @since    1.0.0
     */
    private static $_instance = null;

    /**
     * Settings class object
     * @var     object
     * @access  public
     * @since   1.0.0
     */
    public $settings = null;

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

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $script_suffix;

    /**
     * For menu instance
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $menu;

    /**
     * For template
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $plugin_slug;

    /**
     * Constructor function.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function __construct($file = '', $version = '1.0.0') {
        $this->_version = $version;
        $this->_token = 'vcht';
        error_reporting(E_ERROR | E_PARSE);
        $this->file = $file;
        $this->dir = dirname($this->file);
        $this->chmodWrite = ( 0747 & ~ umask() );
        if (defined('FS_CHMOD_DIR')) {
            $this->chmodWrite = FS_CHMOD_DIR;
        }

        $this->uploads_dir = trailingslashit($this->dir) . 'uploads';
        $this->assets_dir = trailingslashit($this->dir) . 'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));
        $this->uploads_url = esc_url(trailingslashit(plugins_url('/uploads/', $this->file)));
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'), 10, 1);
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_styles'), 10, 1);

        add_action('plugins_loaded', array($this, 'init_localization'));
        add_action('wp_head', array($this, 'options_custom_styles'));
        // add_action('admin_bar_menu', array($this, 'custom_toolbar_link'),999);
        add_action('wp_ajax_nopriv_client_checkOperators', array($this, 'client_checkOperators'));
        add_action('wp_ajax_client_checkOperators', array($this, 'client_checkOperators'));
        add_action('wp_ajax_nopriv_client_sendContactForm', array($this, 'client_sendContactForm'));
        add_action('wp_ajax_client_sendContactForm', array($this, 'client_sendContactForm'));
        add_action('wp_ajax_nopriv_client_checkOnlineOperator', array($this, 'client_checkOnlineOperator'));
        add_action('wp_ajax_client_checkOnlineOperator', array($this, 'client_checkOnlineOperator'));
        add_action('wp_ajax_nopriv_client_startChat', array($this, 'client_startChat'));
        add_action('wp_ajax_client_startChat', array($this, 'client_startChat'));
        add_action('wp_ajax_nopriv_client_sendMessage', array($this, 'client_sendMessage'));
        add_action('wp_ajax_client_sendMessage', array($this, 'client_sendMessage'));
        add_action('wp_ajax_nopriv_client_getNewMessages', array($this, 'client_getNewMessages'));
        add_action('wp_ajax_client_getNewMessages', array($this, 'client_getNewMessages'));
        add_action('wp_ajax_nopriv_client_getOperatorInfos', array($this, 'client_getOperatorInfos'));
        add_action('wp_ajax_client_getOperatorInfos', array($this, 'client_getOperatorInfos'));
        add_action('wp_ajax_nopriv_client_closeChat', array($this, 'client_closeChat'));
        add_action('wp_ajax_client_closeChat', array($this, 'client_closeChat'));
        add_action('wp_ajax_nopriv_client_uploadFile', array($this, 'client_uploadFile'));
        add_action('wp_ajax_client_uploadFile', array($this, 'client_uploadFile'));
        add_action('wp_ajax_nopriv_client_getLastHistory', array($this, 'client_getLastHistory'));
        add_action('wp_ajax_client_getLastHistory', array($this, 'client_getLastHistory'));
        add_action('wp_ajax_nopriv_client_updateClient', array($this, 'client_updateClient'));
        add_action('wp_ajax_client_updateClient', array($this, 'client_updateClient'));
        add_action('wp_ajax_nopriv_client_checkChatActive', array($this, 'client_checkChatActive'));
        add_action('wp_ajax_client_checkChatActive', array($this, 'client_checkChatActive'));
        add_action('wp_ajax_nopriv_client_geolocalize', array($this, 'client_geolocalize'));
        add_action('wp_ajax_client_geolocalize', array($this, 'client_geolocalize'));
    }

    public function custom_toolbar_link($wp_admin_bar) {
        if (current_user_can('visual_chat')) {
            $args = array(
                'id' => 'visual_chat',
                'title' => '<span class="ab-icon"></span>' . __('Visual Chat', 'WP_Visual_Chat'),
                'href' => admin_url('admin.php?page=vcht-console'),
                'meta' => array('class' => 'vhct_toolbarPage')
            );
            $wp_admin_bar->add_node($args);
        }
    }

    /*
     * Plugin init localization
     */

    public function init_localization() {
        $moFiles = scandir(trailingslashit($this->dir) . 'languages/');
        $selFile = '';
        foreach ($moFiles as $moFile) {
            if (strlen($moFile) > 3 && strpos($moFile, 'Frontend') == 0 && substr($moFile, -3) == '.mo' && strpos(strtolower($moFile), strtolower(get_locale())) > -1) {
                $selFile = $moFile;
                load_textdomain('WP_Visual_Chat_Frontend', trailingslashit($this->dir) . 'languages/' . $moFile);
            }
        }
        if (!$selFile) {
            load_textdomain('WP_Visual_Chat_Frontend', trailingslashit($this->dir) . 'languages/Frontend_en_US.mo');
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
            $settings[0]->purchaseCode = '';
            return $settings[0];
        } else {
            return false;
        }
    }

    /**
     * Load admin JS files
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function frontend_enqueue_scripts($hook = '') {
        $settings = $this->getSettings();

        if ($settings->enableChat && (!$settings->enableLoggedVisitorsOnly || is_user_logged_in())) {
            wp_register_script($this->_token . '-flat-ui', esc_url($this->assets_url) . 'js/flat-ui-pro.min.js', array('jquery'), $this->_version);
            wp_enqueue_script($this->_token . '-flat-ui');
            wp_register_script($this->_token . '-customScrollbar', esc_url($this->assets_url) . 'js/jquery.mCustomScrollbar.concat.min.js', array('jquery'));
            wp_enqueue_script($this->_token . '-customScrollbar');
            wp_register_script($this->_token . '-dropzone', esc_url($this->assets_url) . 'js/dropzone.min.js', array('jquery'));
            wp_enqueue_script($this->_token . '-dropzone');

            wp_register_script($this->_token . '-frontend', esc_url($this->assets_url) . 'js/frontend.min.js', array('jquery',
                'jquery-ui-core',
                'jquery-ui-mouse',
                'jquery-ui-position'), $this->_version);
            wp_enqueue_script($this->_token . '-frontend');

            global $wpdb;
            $ctFields = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_fields WHERE inLoginPanel=0 ORDER BY ordersort ASC");
            $loginFields = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_fields WHERE inLoginPanel=1 ORDER BY ordersort ASC");

            if ($settings->usePoFile == 1) {
                if (function_exists('icl_object_id')) {
                    foreach ($ctFields as $field) {
                        $field->title = icl_t('VisualChat Frontend', 'field_' . $field->id . '_label', $field->title);
                        $field->placeholder = icl_t('VisualChat Frontend', 'field_' . $field->id . '_placeholder', $field->placeholder);
                    }
                    foreach ($loginFields as $field) {
                        $field->title = icl_t('VisualChat Frontend', 'field_' . $field->id . '_label', $field->title);
                        $field->placeholder = icl_t('VisualChat Frontend', 'field_' . $field->id . '_placeholder', $field->placeholder);
                    }
                }
            }
            $textsReq = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_texts ORDER BY id ASC");

            $texts = array();
            foreach ($textsReq as $value) {
                $originalJS = str_replace('\\\\', '\\', $value->original);
                $originalJS = preg_replace("/\r\n|\r|\n/", '[n]', $originalJS);
                $originalJS = str_replace('\n', '[n]', $originalJS);
                $texts[$originalJS] = $this->getCorrectText($value->original);
                $texts[$originalJS] = str_replace('\\n', '<br/>', $texts[$originalJS]);
                $texts[$originalJS] = stripslashes($texts[$originalJS]);
            }

            wp_localize_script($this->_token . '-frontend', 'vcht_data', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'websiteUrl' => get_home_url(),
                'uploadsUrl' => $this->uploads_url,
                'assetsUrl' => $this->assets_url,
                'operatorID' => $user->ID,
                'vtrk' => $settings->enableVisitorsTracking,
                'trkDelay' => $settings->trackingDelay,
                'ajaxCheckDelay' => $settings->ajaxCheckDelay,
                'allowFilesFromCustomers' => $settings->allowFilesFromCustomers,
                'filesMaxSize' => $settings->filesMaxSize,
                'allowedFiles' => $settings->allowedFiles,
                'contactFields' => $ctFields,
                'loginFields' => $loginFields,
                'enableChat' => $settings->enableChat,
                'showCloseBtn' => $settings->showCloseBtn,
                'showFullscreenBtn' => $settings->showFullscreenBtn,
                'showMinifyBtn' => $settings->showMinifyBtn,
                'contactFormIcon' => $settings->contactFormIcon,
                'loginFormIcon' => $settings->loginFormIcon,
                'enableLoginPanel' => $settings->enableLoginPanel,
                'defaultImgAvatar' => $settings->defaultImgAvatar,
                'customerImgAvatar' => $settings->customerImgAvatar,
                'enableContactForm' => $settings->enableContactForm,
                'playSoundCustomer' => $settings->playSoundCustomer,
                'enableGeolocalization' => $settings->enableGeolocalization,
                'texts' => $texts,
                'bounceFx' => $settings->bounceFx));
        }
    }

    /**
     * Load admin CSS files
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function frontend_enqueue_styles($hook = '') {
        $settings = $this->getSettings();
        if ($settings->enableChat && (!$settings->enableLoggedVisitorsOnly || is_user_logged_in())) {
            wp_register_style($this->_token . '-bootstrap', esc_url($this->assets_url) . 'css/bootstrap.min.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-bootstrap');
            wp_register_style($this->_token . '-flat-ui', esc_url($this->assets_url) . 'css/flat-ui-pro.min.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-flat-ui');
            wp_register_style($this->_token . '-customScrollbar', esc_url($this->assets_url) . 'css/jquery.mCustomScrollbar.min.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-customScrollbar');
            wp_register_style($this->_token . '-dropzone', esc_url($this->assets_url) . 'css/dropzone.min.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-dropzone');
            wp_register_style($this->_token . '-fontawesome', esc_url($this->assets_url) . 'css/font-awesome.min.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-fontawesome');
            wp_register_style($this->_token . '-frontend', esc_url($this->assets_url) . 'css/frontend.min.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-frontend');
        }
    }

    /*
     * Chat custom styles integration
     */

    public function options_custom_styles() {
        $output = '';
        $settings = $this->getSettings();
        if ($settings->enableChat) {

            $fontname = str_replace(' ', '+', $settings->googleFont);
            $output .= '@import url(https://fonts.googleapis.com/css?family=' . $fontname . ':400,700);';

            $output .= '#vcht_chatPanel {';
            $output .= ' font-family:' . $settings->googleFont . '; ';
            $output .= ' width:' . $settings->widthPanel . 'px; ';
            $output .= ' height:' . $settings->heightPanel . 'px; ';
            $output .= ' background-color:' . $settings->color_bg . '; ';
            $output .= ' color:' . $settings->color_texts . '; ';
            $output .= ' border-color:' . $settings->color_headerBg . '; ';
            $output .= ' border-top-left-radius:' . $settings->borderRadius . 'px; ';
            $output .= ' border-top-right-radius:' . $settings->borderRadius . 'px; ';
            if ($settings->chatPosition == 'left') {
                $output .= ' left: 18px; ';
            } else {
                $output .= ' right: 18px; ';
            }
            if ($settings->panelShadow) {
                $output .= ' -moz-box-shadow: 0px 0px 6px rgba(0,0,0,0.5);';
                $output .= ' -webkit-box-shadow: 0px 0px 6px rgba(0,0,0,0.5);';
                $output .= ' box-shadow: 0px 0px 6px rgba(0,0,0,0.5);';
            }
            $output .= '}';
            $output .= "\n";

            $output .= '#vcht_chatPanel p {';
            $output .= ' color:' . $settings->color_texts . '; ';
            $output .= '}';
            $output .= "\n";



            $output .= '#vcht_chatPanel .form-group .input-group-addon {';
            $output .= ' background-color:' . $settings->color_fieldsBorder . '; ';
            $output .= ' border-color:' . $settings->color_fieldsBorder . '; ';
            $output .= '}';
            $output .= "\n";
            $output .= '#vcht_chatPanel .form-group:focus .input-group-addon {';
            $output .= ' background-color:' . $settings->color_fieldsBorderFocus . '; ';
            $output .= ' border-color:' . $settings->color_fieldsBorderFocus . '; ';
            $output .= '}';
            $output .= "\n";

            $output .= '#vcht_chatPanel .form-control {';
            $output .= ' background-color:' . $settings->color_fieldsBg . '; ';
            $output .= ' color:' . $settings->color_fields . '; ';
            $output .= ' border-color:' . $settings->color_fieldsBorder . '; ';
            $output .= '}';
            $output .= "\n";
            $output .= '#vcht_chatPanel .form-control:focus {';
            $output .= ' border-color:' . $settings->color_fieldsBorderFocus . '; ';
            $output .= '}';
            $output .= "\n";

            $output .= '#vcht_chatPanel #vcht_chatHeader {';
            $output .= ' background-color:' . $settings->color_headerBg . '; ';
            $output .= ' color:' . $settings->color_headerTexts . '; ';
            $output .= '}';
            $output .= "\n";
            $output .= '#vcht_chatPanel #vcht_chatHeader > .vcht_btn {';
            $output .= ' background-color:' . $settings->color_headerBtnBg . '; ';
            $output .= ' color:' . $settings->color_headerBtnTexts . '; ';
            $output .= '}';
            $output .= "\n";
            $output .= '#vcht_chatPanel #vcht_chatLoader{';
            $output .= ' background-color:' . $settings->color_loaderBg . '; ';
            $output .= ' color:' . $settings->color_loader . '; ';
            $output .= '}';
            $output .= "\n";
            $output .= '#vcht_chatPanel .vcht_double-bounce1,#vcht_chatPanel .vcht_double-bounce2 {';
            $output .= ' background-color:' . $settings->color_loader . '; ';
            $output .= '}';
            $output .= "\n";
            $output .= '#vcht_chatPanel .mCSB_scrollTools .mCSB_draggerRail{';
            $output .= ' background-color:' . $settings->color_scrollBg . '; ';
            $output .= '}';
            $output .= "\n";
            $output .= '#vcht_chatPanel .mCSB_scrollTools .mCSB_dragger .mCSB_dragger_bar{';
            $output .= ' background-color:' . $settings->color_scroll . '; ';
            $output .= '}';
            $output .= "\n";
            $output .= '#vcht_chatPanel .vcht_mainIcon{';
            $output .= ' color:' . $settings->color_icons . '; ';
            $output .= '}';
            $output .= "\n";

            $output .= '.vcht_avatarSel{';
            $output .= ' background-color:' . $settings->color_showCircleBg . '; ';
            $output .= '}';
            $output .= "\n";
            $output .= '.vcht_avatarSel .vcht_avatarArrow{';
            $output .= ' border-top-color:' . $settings->color_showCircleBg . '; ';
            $output .= '}';
            $output .= "\n";

            $output .= '#vcht_chatPanel .vcht_message .vcht_bubble{';
            $output .= ' background-color:' . $settings->color_customerBubbleBg . '; ';
            $output .= ' color:' . $settings->color_customerBubbleTexts . '; ';
            $output .= '}';
            $output .= "\n";
            $output .= '#vcht_chatPanel .vcht_message .vcht_bubble a{';
            $output .= ' color:' . $settings->color_customerBubbleTexts . '; ';
            $output .= '}';
            $output .= "\n";
            $output .= '#vcht_chatPanel .vcht_message .vcht_bubble .vcht_elementShown,'
                    . '#vcht_chatPanel .vcht_message .vcht_bubble .vcht_messageFile{';
            $output .= ' border-color:' . $settings->color_customerBubbleTexts . '; ';
            $output .= '}';
            $output .= "\n";
            $output .= '#vcht_chatPanel .vcht_message .vcht_bubble:before{';
            $output .= ' border-color: transparent ' . $settings->color_customerBubbleBg . ' transparent transparent; ';
            $output .= '}';
            $output .= "\n";

            $output .= '#vcht_chatPanel .vcht_message.vcht_operatorMsg .vcht_bubble{';
            $output .= ' background-color:' . $settings->color_operatorBubbleBg . '; ';
            $output .= ' color:' . $settings->color_operatorBubbleTexts . '; ';
            $output .= '}';
            $output .= "\n";
            $output .= '#vcht_chatPanel .vcht_message.vcht_operatorMsg .vcht_bubble a {';
            $output .= ' color:' . $settings->color_operatorBubbleTexts . '; ';
            $output .= '}';
            $output .= "\n";
            $output .= '#vcht_chatPanel .vcht_message.vcht_operatorMsg .vcht_bubble .vcht_elementShown,'
                    . '#vcht_chatPanel .vcht_message.vcht_operatorMsg .vcht_bubble .vcht_messageFile{';
            $output .= ' border-color:' . $settings->color_operatorBubbleTexts . '; ';
            $output .= '}';
            $output .= "\n";

            $output .= '#vcht_chatPanel .vcht_message.vcht_operatorMsg .vcht_bubble:before{';
            $output .= ' border-color: transparent transparent transparent ' . $settings->color_operatorBubbleBg . '; ';
            $output .= '}';
            $output .= "\n";

            $output .= '#vcht_chatPanel .form-group > label,.vcht_bootstrap .vcht_message .vcht_infos{';
            $output .= ' color:' . $settings->color_labels . '; ';
            $output .= '}';
            $output .= "\n";

            // TODO
            $output .= '#vcht_chatPanel .tooltip .tooltip-inner{';
            $output .= ' color:' . $settings->color_tooltip . '; ';
            $output .= ' background-color:' . $settings->color_tooltipBg . '; ';
            $output .= '}';
            $output .= "\n";
            $output .= '#vcht_chatPanel .tooltip .tooltip-arrow {';
            $output .= ' border-top-color:' . $settings->color_tooltipBg . '; ';
            $output .= '}';
            $output .= "\n";

            $output .= '#vcht_chatPanel a.btn.btn-default{';
            $output .= ' background-color:' . $settings->color_btnSecBg . '; ';
            $output .= ' color:' . $settings->color_btnSecTexts . '; ';
            $output .= '}';
            $output .= "\n";
            $output .= '#vcht_chatPanel a.btn.btn-primary{';
            $output .= ' background-color:' . $settings->color_btnBg . '; ';
            $output .= ' color:' . $settings->color_btnTexts . '; ';
            $output .= '}';
            $output .= "\n";


            $output .= '.vcht_selectedDom  {';
            $output .= ' -moz-box-shadow: 0px 0px 40px 0px ' . $settings->color_shining . ' !important;';
            $output .= ' -webkit-box-shadow: 0px 0px 40px 0px ' . $settings->color_shining . ' !important;';
            $output .= ' -o-box-shadow: 0px 0px 40px 0px ' . $settings->color_shining . ' !important;';
            $output .= ' box-shadow: 0px 0px 40px 0px ' . $settings->color_shining . ' !important;';
            $output .= '}';
            $output .= "\n";
            $output .= '@-o-keyframes glow {';
            $output .= '0% { -o-box-shadow: 0px 0px 10px 0px ' . $settings->color_shining . ';} 50% { -o-box-shadow: 0px 0px 40px 0px ' . $settings->color_shining . '; } 100% { -o-box-shadow: 0px 0px 10px 0px ' . $settings->color_shining . ';} ';
            $output .= '}';
            $output .= '@-moz-keyframes glow {';
            $output .= '0% { -moz-box-shadow: 0px 0px 10px 0px ' . $settings->color_shining . ';} 50% { -moz-box-shadow: 0px 0px 40px 0px ' . $settings->color_shining . '; } 100% { -moz-box-shadow: 0px 0px 10px 0px ' . $settings->color_shining . ';} ';
            $output .= '}';
            $output .= '@-webkit-keyframes glow {';
            $output .= '0% { -webkit-box-shadow: 0px 0px 10px 0px ' . $settings->color_shining . ';} 50% { -webkit-box-shadow: 0px 0px 40px 0px ' . $settings->color_shining . '; } 100% { -webkit-box-shadow: 0px 0px 10px 0px ' . $settings->color_shining . ';} ';
            $output .= '}';
            $output .= '@keyframes glow {';
            $output .= '0% { box-shadow: 0px 0px 10px 0px ' . $settings->color_shining . ';} 50% { box-shadow: 0px 0px 40px 0px ' . $settings->color_shining . '; } 100% { box-shadow: 0px 0px 10px 0px ' . $settings->color_shining . ';} ';
            $output .= '}';
            $output .= "\n";

            if ($output != '') {
                $output = "\n<style>\n" . $output . "</style>\n";
                echo $output;
            }
        }
    }

    public function getCorrectText($text) {
        global $wpdb;
        $settings = $this->getSettings();
        $rep = __($text, 'WP_Visual_Chat');
        if ($settings->usePoFile == 0) {
            $table_name = $wpdb->prefix . "vcht_texts";
            $text_db = $wpdb->get_results("SELECT * FROM $table_name WHERE original='" . $text . "' LIMIT 1");
            if (count($text_db) > 0) {
                $rep = $text_db[0]->content;
            } else {
                $text = str_replace('[n]', '\n', $text);
                $text_db = $wpdb->get_results("SELECT * FROM $table_name WHERE original='" . $text . "' LIMIT 1");
                if (count($text_db) > 0) {
                    $rep = $text_db[0]->content;
                } else {
                    if (strpos($text, 'Hello') === 0) {
                        $text_db = $wpdb->get_results("SELECT * FROM $table_name WHERE id=3 LIMIT 1");
                        $rep = $text_db[0]->content;
                    } else if (strpos($text, 'Thank') === 0) {
                        $text_db = $wpdb->get_results("SELECT * FROM $table_name WHERE id=7 LIMIT 1");
                        $rep = $text_db[0]->content;
                    }
                }
            }
        } else {
            if (function_exists('icl_object_id')) {
                $rep = icl_t('VisualChat Frontend', $text, $text);
            }
            if ($text == 'No operator') {
                $rep = __('No operator Msg', 'WP_Visual_Chat_Frontend');
            }
            if ($text == 'Thank you.Your message has been sent.We will contact you soon.') {
                $rep = __('Message sent', 'WP_Visual_Chat_Frontend');
            }
        }
        return $rep;
    }

    public function client_checkOnlineOperator() {
        global $wpdb;
        $settings = $this->getSettings();
        $table_name = $wpdb->prefix . "vcht_users";
        $time = strtotime(date("Y-m-d H:i:s"));
        $time = $time - ($settings->trackingDelay + 10);
        $date = date("Y-m-d H:i:s", $time);
        $operators = $wpdb->get_results('SELECT isOnline,isOperator,lastActivity FROM ' . $table_name . ' WHERE isOnline=1 AND isOperator=1 AND lastActivity> "' . $date . '" LIMIT 1');
        if (count($operators) > 0) {
            echo '1';
        } else {
            echo '-1';
        }
        die();
    }

    public function client_startChat() {
        global $wpdb;
        session_start();
        $settings = $this->getSettings();

        $email = '';
        $username = __('A visitor', 'WP_Visual_Chat');
        $ip = $_SERVER['REMOTE_ADDR'];
        $clientID = '';

        $wpUserID = 0;
        if (is_user_logged_in()) {
            $userWP = wp_get_current_user();
            // $wpUserID = $userWP->ID;
            $username = $userWP->display_name;
            $email = $userWP->user_email;
        }
        $fields = array();
        foreach ($_POST['fields'] as $key => $value) {
            $rows = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'vcht_fields WHERE id=%s LIMIT 1', $value['id']));
            if (count($rows) > 0) {
                $field = $rows[0];
                $objField = new stdClass();
                $objField->id = $field->id;
                $objField->title = $field->backendTitle;
                $objField->type = $field->type;
                $objField->infoType = $field->infoType;
                $objField->value = $value['value'];
                $fields[] = $objField;
                if ($field->type == 'textfield' && $field->infoType == 'email') {
                    $email = $value['value'];
                }
                if ($field->type == 'textfield' && $field->infoType == 'username') {
                    $username = $value['value'];
                }
            }
        }

        $chkUser = false;

        if (isset($_SESSION['vcht_id']) && $_SESSION['vcht_id'] != "") {
            $senderID = $_SESSION['vcht_id'];
            $table_name = $wpdb->prefix . "vcht_users";
            $users = $wpdb->get_results($wpdb->prepare('SELECT clientID,email,username,id FROM ' . $wpdb->prefix . 'vcht_users WHERE id=%s LIMIT 1', $senderID));
            if (count($users) > 0) {
                $chkUser = true;
                $user = $users[0];
                $clientID = $user->clientID;
                if ($email != "" && $user->email == "") {
                    $wpdb->update($table_name, array('email' => $email, 'username' => $username), array('id' => $user->id));
                } else {
                    $username = $user->username;
                    $email = $user->email;
                }
                if (count($fields) > 0) {
                    $wpdb->update($table_name, array('fieldsJson' => json_encode($fields, true)), array('id' => $user->id));
                }
            } else {
                
            }
        }
        if (!$chkUser) {
            if ($email != '') {
                $rows = $wpdb->get_results($wpdb->prepare('SELECT email,clientID,username FROM ' . $wpdb->prefix . 'vcht_users WHERE email=%s LIMIT 1', $email));
                if (count($rows) > 0) {
                    $user = $rows[0];
                    $clientID = $user->clientID;
                    $username = $user->username;
                }
            } else {
                if ($clientID == '') {
                    $rows = $wpdb->get_results($wpdb->prepare('SELECT email,clientID,username,ip FROM ' . $wpdb->prefix . 'vcht_users WHERE ip=%s LIMIT 1', $ip));
                    if (count($rows) > 0) {
                        $user = $rows[0];
                        $clientID = $user->clientID;
                        $username = $user->username;
                        $email = $user->user_email;
                    }
                }
            }
        }

        if ($clientID == '') {
            $clientID = md5(uniqid());
            $table_name = $wpdb->prefix . "vcht_users";
            $wpdb->insert($table_name, array(
                'userID' => $wpUserID,
                'clientID' => $clientID,
                'username' => $username,
                'email' => $email,
                'uploadFolderName' => md5(uniqid()),
                'lastActivity' => date('Y-m-d H:i:s'),
                'isOnline' => true, 'isOperator' => false,
                'imgAvatar' => $this->assets_url . 'img/guest-48.png',
                'fieldsJson' => json_encode($fields),
                'ip' => $_SERVER['REMOTE_ADDR']));
        }
        $rows = $wpdb->get_results($wpdb->prepare('SELECT clientID,imgAvatar,username,uploadFolderName,id FROM ' . $wpdb->prefix . 'vcht_users WHERE clientID=%s LIMIT 1', $clientID));
        if (count($rows) > 0) {
            $userObj = new stdClass();
            $userObj->id = $rows[0]->id;
            $userObj->clientID = $clientID;
            $userObj->avatar = $rows[0]->imgAvatar;
            $userObj->username = $rows[0]->username;
            $userObj->uploadFolderName = $rows[0]->uploadFolderName;


            $_SESSION['vcht_id'] = $rows[0]->id;
            echo json_encode($userObj, true);
        }



        die();
    }

    public function client_sendContactForm() {
        global $wpdb;

        $emailUser = '';
        $emailContent = '';
        $emailSubject = $this->getCorrectText("New message from your website");
        foreach ($_POST['fields'] as $key => $value) {
            $rows = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'vcht_fields WHERE id=%s LIMIT 1', $value['id']));
            if (count($rows) > 0) {
                $field = $rows[0];
                if ($field->type == 'textfield' && $field->infoType == 'email') {
                    $emailUser = $value['value'];
                }
                if ($field->type == "checkbox") {
                    if ($value['value'] == 'true') {
                        $value['value'] = $this->getCorrectText("Yes");
                    } else {
                        $value['value'] = $this->getCorrectText("No");
                    }
                } 
                    $emailContent .= '<p>' . $field->backendTitle . ' : <strong>' . nl2br(stripslashes($value['value'])) . '</strong></p>';
            }
        }
        if ($emailUser != '') {
            $settings = $this->getSettings();

            $headers = "Return-Path: " . $emailUser . "\n";
            $headers .= "From:" . $emailUser . "\n";
            $headers .= "X-Mailer: PHP " . phpversion() . "\n";
            $headers .= "Reply-To: " . $emailUser . "\n";
            $headers .= "X-Priority: 3 (Normal)\n";
            $headers .= "Mime-Version: 1.0\n";
            $headers .= "Content-type: text/html; charset=utf-8\n";

            if (strpos($settings->emailAdmin, ',') > 0) {
                $emailsArr = explode(',', $settings->emailAdmin);
                $settings->emailAdmin = $emailsArr;
            }
            wp_mail($settings->emailAdmin, $settings->emailSubject, $emailContent, $headers);
        }
        die();
    }

    public function client_sendMessage() {
        global $wpdb;
        $user = wp_get_current_user();
        session_start();
        $senderID = $_SESSION['vcht_id'];
        $table_name = $wpdb->prefix . "vcht_users";
        $users = $wpdb->get_results($wpdb->prepare('SELECT id,currentOperator FROM ' . $wpdb->prefix . 'vcht_users WHERE id=%s LIMIT 1', $senderID));
        if (count($users) > 0) {
            $user = $users[0];
            $receiverID = $user->currentOperator;
            $content = implode("\n", array_map('sanitize_text_field', explode("\n", $_POST['content'])));
            $files = stripslashes(sanitize_text_field($_POST['files']));
            $page = stripslashes(sanitize_text_field($_POST['page']));

            $table_name = $wpdb->prefix . "vcht_messages";
            $wpdb->insert($table_name, array('msgDate' => date("Y-m-d H:i:s"), 'senderID' => $senderID, 'receiverID' => $receiverID,
                'content' => stripslashes($content),
                'files' => $files,
                'page' => $page));
            echo $wpdb->insert_id;
        }
        die();
    }

    public function client_getNewMessages() {
        global $wpdb;
        session_start();
        $senderID = $_SESSION['vcht_id'];
        if ($senderID != "") {

            $users = $wpdb->get_results($wpdb->prepare('SELECT id,currentOperator FROM ' . $wpdb->prefix . 'vcht_users WHERE id=%s LIMIT 1', $senderID));
            if (count($users) > 0) {
                $settings = $this->getSettings();
                $user = $users[0];
                $table_name = $wpdb->prefix . "vcht_messages";


                $rep = array();

                if ($user->currentOperator > 0) {
                    $chkOperator = false;
                    $time = strtotime(date("Y-m-d H:i:s"));
                    $time = $time - ($settings->trackingDelay + 10);
                    $date = date("Y-m-d H:i:s", $time);
                    $operators = $wpdb->get_results($wpdb->prepare('SELECT id,lastActivity,isOnline FROM ' . $wpdb->prefix . 'vcht_users WHERE lastActivity>%s  AND id=%s AND isOnline=1 LIMIT 1', $date, $user->currentOperator));
                    if (count($operators) > 0 && $operators[0]->isOnline) {
                        $chkOperator = true;
                    }
                    if (!$chkOperator) {
                        $messages = $wpdb->get_results($wpdb->prepare('SELECT senderID,receiverID,msgDate,type FROM ' . $wpdb->prefix . 'vcht_messages WHERE (senderID=%s OR receiverID<=0)  AND msgDate>%s ORDER BY msgDate DESC LIMIT 1', $senderID, $date));

                        if (count($messages) > 0 && ($messages[0]->type == 'close' || $messages[0]->type == 'transfer')) {
                            
                        } else {
                            $wpdb->insert($table_name, array('msgDate' => date("Y-m-d H:i:s"), 'senderID' => $user->currentOperator, 'receiverID' => $senderID,
                                'type' => 'close'));
                        }
                    }
                }

                $time = strtotime(date("Y-m-d H:i:s"));
                $time = $time - $settings->ajaxCheckDelay * 2;
                $date = date("Y-m-d H:i:s", $time);
                $messages = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'vcht_messages WHERE (receiverID=%s OR receiverID<=0) AND msgDate>%s', $senderID, $date));

                foreach ($messages as $message) {
                    if ($message != 'receiveTransfer') {
                        $msgObj = new stdClass();
                        $msgObj->id = $message->id;
                        $msgObj->senderID = $message->senderID;
                        $msgObj->receiverID = $message->receiverID;
                        $msgObj->date = $message->msgDate;
                        $msgObj->content = stripslashes($message->content);
                        $msgObj->files = $message->files;
                        $msgObj->domElement = $message->domElement;
                        $msgObj->page = $message->page;
                        $msgObj->type = $message->type;
                        $msgObj->transferUsername = $message->transferUsername;

                        $rep[] = $msgObj;
                    }
                }
                echo json_encode($rep, true);
            }
            $table_name = $wpdb->prefix . "vcht_users";
            $wpdb->update($table_name, array('lastActivity' => date('Y-m-d H:i:s'), 'isOperator' => false, 'isOnline' => 1), array('id' => $user->id));
        }
        die();
    }

    public function client_getOperatorInfos() {
        global $wpdb;
        session_start();
        $senderID = $_SESSION['vcht_id'];
        if ($senderID != "") {
            $operatorID = stripslashes(sanitize_text_field($_POST['operatorID']));

            $rows = $wpdb->get_results($wpdb->prepare('SELECT id,username,imgAvatar,isOperator,currentPage FROM ' . $wpdb->prefix . 'vcht_users WHERE id=%s LIMIT 1', $operatorID));
            if (count($rows) > 0) {
                $operator = $rows[0];
                $userObj = new stdClass();
                $userObj->id = $rows[0]->id;
                $userObj->username = $rows[0]->username;
                $userObj->avatar = $rows[0]->imgAvatar;
                $userObj->isOperator = $rows[0]->isOperator;

                $userObj->currentPage = $rows[0]->currentPage;
                echo json_encode($userObj);
            }
        }
        die();
    }

    public function client_closeChat() {
        global $wpdb;
        session_start();
        $senderID = $_SESSION['vcht_id'];
        $receiverID = stripslashes(sanitize_text_field($_POST['receiverID']));
        $keepOnline = stripslashes(sanitize_text_field($_POST['keepOnline']));

        if ($senderID != "") {
            $table_name = $wpdb->prefix . "vcht_users";
            $wpdb->update($table_name, array('lastActivity' => date('Y-m-d H:i:s'), 'isOnline' => $keepOnline), array('id' => $senderID));

            $table_name = $wpdb->prefix . "vcht_messages";


            $msgs = $wpdb->get_results($wpdb->prepare('SELECT id,type,senderID,receiverID FROM ' . $table_name . ' WHERE senderID=%s OR receiverID=%s ORDER BY id DESC LIMIT 1', $senderID, $senderID));
            if (count($msgs) > 0 && $msgs[0]->type == 'close') {
                
            } else {
                $wpdb->insert($table_name, array('msgDate' => date("Y-m-d H:i:s"), 'senderID' => $senderID, 'receiverID' => $receiverID, 'type' => 'close'));
            }
        }
        die();
    }

    public function client_uploadFile() {

        global $wpdb;

        session_start();
        $senderID = $_SESSION['vcht_id'];
        if ($senderID != "") {
            $receiverID = sanitize_text_field($_POST['receiverID']);
            $table_name = $wpdb->prefix . "vcht_users";
            $users = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'vcht_users WHERE id=%s LIMIT 1', $senderID));
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
                                strpos(strtolower($value["name"]), '.cgi') === false &&
                                strpos(strtolower($value["name"]), '.htaccess') === false &&
                                strpos(strtolower($value["name"]), '..') === false &&
                                strpos(strtolower($value["name"]), '/') === false
                        ) {

                            $fileName = str_replace('..', '', $value["name"]);
                            $fileName = str_replace('/', '', $fileName);
                            $fileName = str_replace(' ', '_', $fileName);

                            $allowedFiles = explode(",", $settings->allowedFiles);
                            $ext = $this->get_extension($value["name"]);
                            if (in_array('.' . strtolower($ext), $allowedFiles)) {
                                //  if(in_array('.'.$ext, $allowedFiles)){

                                if (!is_dir($this->uploads_dir . '/' . $user->uploadFolderName)) {
                                    mkdir($this->uploads_dir . '/' . $user->uploadFolderName);
                                    $fp = fopen($this->uploads_dir . $formSession . $form->randomSeed . '/.htaccess', 'w');
                                    fwrite($fp, '<FilesMatch "\.(htaccess|htpasswd|ini|phps?|fla|psd|log|sh|zip|exe|pl|jsp|asp|htm|pht|phar|sh|cgi|py|php|php\.)$">' . "\n");
                                    fwrite($fp, 'Order Allow,Deny' . "\n");
                                    fwrite($fp, 'Deny from all' . "\n");
                                    fwrite($fp, '</FilesMatch>');
                                    fclose($fp);
                                    chmod($this->uploads_dir . '/' . $user->uploadFolderName, $this->chmodWrite);
                                }
                                move_uploaded_file($value["tmp_name"], $this->uploads_dir . '/' . $user->uploadFolderName . '/' . $fileName);
                                chmod($this->uploads_dir . '/' . $user->uploadFolderName . '/' . $fileName, 0644);
                            }
                        }
                    }
                }
            }
        }
        die();
    }

    private function get_extension($file) {
        $extension = end(explode(".", $file));
        return $extension ? $extension : false;
    }

    public function client_getLastHistory() {
        global $wpdb;
        session_start();
        $senderID = $_SESSION['vcht_id'];
        if ($senderID != "") {
            $rep = array();

            $time = strtotime(date("Y-m-d H:i:s"));
            $time = $time - (60 * 60);
            $date = date("Y-m-d H:i:s", $time);

            $table_name = $wpdb->prefix . "vcht_messages";
            $messages = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $table_name . ' WHERE (receiverID=%s OR senderID=%s) AND msgDate >%s  ORDER BY msgDate ASC', $senderID, $senderID, $date));
            if (count($messages) > 0) {
                $settings = $this->getSettings();
                foreach ($messages as $message) {
                    if ($message->type == 'close') {
                        $rep = array();
                    } else {
                        $rep[] = $message;
                    }
                }
            }
            echo json_encode($rep, true);
        }
        die();
    }

    public function client_updateClient() {
        global $wpdb;
        session_start();
        $senderID = $_SESSION['vcht_id'];
        $chkUser = false;
        $url = sanitize_text_field($_POST['url']);
        if (isset($_SESSION['vcht_id']) && $senderID != "") {
            $table_name = $wpdb->prefix . "vcht_users";
            $users = $wpdb->get_results($wpdb->prepare('SELECT id,currentOperator FROM ' . $wpdb->prefix . 'vcht_users WHERE id=%s LIMIT 1', $senderID));
            if (count($users) > 0) {
                $chkUser = true;
                $wpdb->update($table_name, array('lastActivity' => date('Y-m-d H:i:s'), 'isOperator' => false, 'isOnline' => true, 'currentPage' => $url), array('id' => $senderID));

                if ($users[0]->currentOperator > 0) {
                    $user = $users[0];
                    $time = strtotime(date("Y-m-d H:i:s"));
                    $settings = $this->getSettings();
                    $time = $time - $settings->trackingDelay * 2;
                    $date = date("Y-m-d H:i:s", $time);
                    $messages = $wpdb->get_results($wpdb->prepare('SELECT senderID,msgDate,type,receiverID FROM ' . $wpdb->prefix . 'vcht_messages WHERE senderID=%s AND receiverID=%s AND msgDate>%s AND type!="close" LIMIT 1', $user->currentOperator, $senderID, $date));
                    if (count($messages) > 0) {
                        echo 'chat';
                    } else {
                        $wpdb->update($table_name, array('lastActivity' => date('Y-m-d H:i:s'), 'isOperator' => false, 'currentOperator' => 0, 'isOnline' => true, 'currentPage' => $url), array('id' => $senderID));
                    }
                }
            }
        }
        if (!$chkUser) {
            $username = __('A visitor', 'WP_Visual_Chat');
            $ip = $_SERVER['REMOTE_ADDR'];
            $clientID = '';
            $email = '';

            $wpUserID = 0;
            if (is_user_logged_in()) {
                $userWP = wp_get_current_user();
                //  $wpUserID = $userWP->ID;
                $username = $userWP->display_name;
                $email = $userWP->user_email;
            }

            $clientID = md5(uniqid());
            $table_name = $wpdb->prefix . "vcht_users";
            $wpdb->insert($table_name, array(
                'userID' => $wpUserID,
                'clientID' => $clientID,
                'username' => $username,
                'email' => $email,
                'uploadFolderName' => md5(uniqid()),
                'lastActivity' => date('Y-m-d H:i:s'),
                'isOnline' => true, 'isOperator' => false,
                'currentPage' => $url,
                'imgAvatar' => $this->assets_url . 'img/guest-48.png',
                'ip' => $_SERVER['REMOTE_ADDR']));
            $_SESSION['vcht_id'] = $wpdb->insert_id;
        }
        die();
    }

    public function client_checkChatActive() {
        global $wpdb;
        session_start();
        $senderID = $_SESSION['vcht_id'];
        $chkChat = false;
        if (isset($_SESSION['vcht_id']) && $senderID != "") {
            $table_name = $wpdb->prefix . "vcht_users";
            $users = $wpdb->get_results($wpdb->prepare('SELECT id,currentOperator FROM ' . $wpdb->prefix . 'vcht_users WHERE id=%s LIMIT 1', $senderID));
            if (count($users) > 0) {
                $user = $users[0];
                if ($user->currentOperator > 0) {
                    $time = strtotime(date("Y-m-d H:i:s"));
                    $time = $time - ($settings->trackingDelay + 10);
                    $date = date("Y-m-d H:i:s", $time);
                    $operators = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'vcht_users WHERE id=%s AND isOperator=1 AND lastActivity> %s AND isOnline=1 LIMIT 1', $user->currentOperator, $date));
                    if (count($operators) > 0) {

                        $messages = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'vcht_messages WHERE (senderID="%s" OR receiverID="%s") AND msgDate>%s AND type="message" ORDER BY id DESC LIMIT 1', $senderID, $senderID, $date));
                        if (count($messages) > 0) {
                            $chkChat = true;
                        }
                    }
                }
            }
        }
        if ($chkChat) {
            echo '1';
        }
        die();
    }

    // Ajax : geolocalize user
    public function client_geolocalize() {
        global $wpdb;
        session_start();
        $senderID = $_SESSION['vcht_id'];
        $chkChat = false;
        if (isset($_SESSION['vcht_id']) && $senderID != "") {
            $users = $wpdb->get_results($wpdb->prepare('SELECT id,country FROM ' . $wpdb->prefix . 'vcht_users WHERE id=%s LIMIT 1', $senderID));
            if (count($users) > 0) {
                $user = $users[0];
                if ($user->country == '') {
                    $country_info = $this->get_country_city_from_ip($_SERVER['REMOTE_ADDR']);
                    if ($country_info['country'] != 'not found') {
                        $country = $country_info['country'];
                    }
                    if ($country_info['town'] != 'not found') {
                        $city = $country_info['town'];
                    }
                    $wpdb->update($wpdb->prefix . 'vcht_users', array('country' => $country, 'city' => $city), array('id' => $user->id));
                }
                echo 1;
            }
        }
        die();
    }

    //get city info from IP Adress
    private function get_country_city_from_ip($ip) {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException("IP is not valid");
        }
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

    /**
     * Main Instance
     *
     *
     * @since 1.0.0
     * @static
     * @return Main instance
     */
    public static function instance($file = '', $version = '1.0.0') {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
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
        _doing_it_wrong(__FUNCTION__, '', $this->_version);
    }

    // End __clone()

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup() {
        _doing_it_wrong(__FUNCTION__, '', $this->_version);
    }

    // End __wakeup()

    /**
     * Log the plugin version number.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    private function _log_version_number() {
        update_option($this->_token . '_version', $this->_version);
    }

}
