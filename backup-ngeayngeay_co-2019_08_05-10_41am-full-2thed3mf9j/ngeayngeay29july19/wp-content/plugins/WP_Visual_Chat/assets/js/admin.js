var vcht_isConnected = false;
var vcht_allUsers = new Array();
var vcht_currentInterlocutor = false;
var vcht_checkMsgTimer;
var vcht_isInit = false;
var vcht_currentUser = false;
var vcht_currentFiles = new Array();
var vcht_uploadFilesDropzone;
var vcht_modeSelection = false;
var vcht_currentSelection = '';
var vcht_currentPage = '';
var vcht_shownElement = '';
var vcht_userHistoryTable;
var vcht_cannedMsgsTable;
var vcht_lastShowTime = new Date('0001-01-01');
var vcht_currentCannedMsgID = -1;
var vcht_cannedMsgs = new Array();
var vcht_settings = false;
var vcht_isCurrentFieldLogin = false;
var vcht_formfield;
var vcht_pendingUsers = new Array();
var vcht_userHistoryTable;
var vcht_fullHistoryTable;

jQuery(document).ready(function () {
    jQuery(window).resize(vcht_onResize);
    vcht_onResize();
    vcht_initUI();
    vcht_initListeners();
    if (document.location.href.indexOf('vcht_online=1') > -1) {
        vcht_logIn();
    }
    if (document.location.href.indexOf('&panel=') > -1) {
        var panelID = document.location.href.substr(document.location.href.indexOf('&panel=') + 7, document.location.href.length);
        if (jQuery('#' + panelID).closest('#vcht_adminSettingsPanel').length > 0) {
            vcht_showMainSettingsPanel();
        }
        setTimeout(function () {
            jQuery('a[href="#' + panelID + '"]').trigger('click');
        }, 350);
    } else {
        jQuery('#vcht_loader').fadeOut();
    }
    jQuery('[data-toggle="tooltip"]').each(function () {
        if (jQuery(this).closest('#vcht_mainHeader').length == 0) {
            jQuery(this).v_tooltip();
        }
    });
    jQuery('#vcht_mainHeader [data-toggle="tooltip"]').v_tooltip({
        container: '#vcht_mainWrapper',
        template: '<div class="tooltip vcht_fixedTooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
        placement: 'bottom'
    });


    jQuery('.modal .vcht_modalCloseBtn').click(function () {
        jQuery(this).closest('.modal').fadeOut();
    });
    vcht_loadCannedMsgs();
    vcht_loadFields();
    if (vcht_data.enableVisitorsTracking == 1) {
        //  jQuery('#vcht_onlineVisitorsList').addClass('vcht_hidden');
    }
});
function vcht_initListeners() {
    jQuery('#vcht_operatorBtnConnect').click(vcht_toggleLogin);
    jQuery('#vcht_chatPanelCannedMsgBtn').click(vcht_openWinPickCannedMsg);
    jQuery('#vcht_chatPanelSendBtn').click(vcht_sendMessage);
    jQuery('#vcht_chatPanelFilesBtn').click(vcht_openWinFilesUpload);
    jQuery('#vcht_chatPanelShowElementBtn').click(vcht_startShowElement);
    jQuery('#vcht_chatPanelCloseBtn').click(vcht_stopCurrentChat);
    jQuery('#vcht_transferChatSelect').change(vcht_transferChatSelectChange);

    jQuery('#vcht_operatorHeaderPic,#vcht_operatorHeaderUsername').click(vcht_showWinUserAccount);

    jQuery('.vcht_fullPanel .vcht_closeBtn').click(function () {
        jQuery(this).closest('.vcht_fullPanel').fadeOut();
        if (jQuery(this).closest('.vcht_fullPanel').attr('id') == 'vcht_adminSettingsPanel') {
            if (document.location.href.indexOf('&panel=vcht_tabSettingsGeneral') > -1) {
                history.pushState(null, null, document.location.href.replace('&panel=vcht_tabSettingsGeneral', ''));
            }
        }
    });
    jQuery('#vcht_viewUserHistoryBtn').click(function () {
        vcht_openUserHistory(vcht_currentInterlocutor.id);
    });

    jQuery('.imageBtn').click(function () {
        vcht_formfield = jQuery(this).prev('input');
        tb_show('', 'media-upload.php?TB_iframe=true');
        return false;
    });
    window.old_tb_remove = window.tb_remove;
    window.tb_remove = function () {
        window.old_tb_remove();
        vcht_formfield = null;
    };
    window.original_send_to_editor = window.send_to_editor;
    window.send_to_editor = function (html) {
        if (vcht_formfield) {
            var alt = jQuery('img', html).attr('alt');
            var fileurl = jQuery('img', html).attr('src');
            if (jQuery('img', html).length == 0) {
                fileurl = jQuery(html).attr('src');
                alt = jQuery(html).attr('alt');
            }
            vcht_formfield.val(fileurl);
            vcht_formfield.trigger('keyup');
            tb_remove();
        } else {
            window.original_send_to_editor(html);
        }
    };


    jQuery('#collapse-button').click(function () {
        vcht_onResize();
        setTimeout(vcht_onResize, 24);
        setTimeout(vcht_onResize, 50);
        setTimeout(vcht_onResize, 100);
    });
    jQuery('#vcht_adminSettingsPanel [data-toggle="switch"]').vcht_BootstrapSwitch('onSwitchChange', vcht_updateSettingsPanel);
    jQuery('#vcht_adminSettingsPanel input,select,textarea').on('change', vcht_updateSettingsPanel);

    jQuery('#vcht_chatPanelMsgArea').keypress(function (e) {
        if (e.which == 13) {
            vcht_sendMessage();
            return false;
        }
    });
    jQuery("#vcht_uploadFilesField").dropzone({
        url: ajaxurl,
        paramName: 'file',
        maxFilesize: vcht_data.filesMaxSize,
        maxFiles: 10,
        addRemoveLinks: true,
        dictRemoveFile: '',
        dictCancelUpload: '',
        acceptedFiles: vcht_data.allowedFiles,
        dictDefaultMessage: vcht_data.texts['Drop files to upload here'],
        dictFileTooBig: vcht_data.texts['File is too big (max size: {{maxFilesize}}MB)'].replace('maxFilesize', vcht_data.filesMaxSize),
        dictInvalidFileType: vcht_data.texts['Invalid file type'],
        dictMaxFilesExceeded: vcht_data.texts['You can not upload any more files'],
        init: function () {
            vcht_uploadFilesDropzone = Dropzone.forElement('#vcht_uploadFilesField');
            this.on('thumbnail', function (file, dataUrl) {
                var thumb = jQuery(file.previewElement);
                thumb.attr('data-file', file);
            });
            this.on('sending', function (file, xhr, formData) {
                formData.append('action', 'vcht_uploadFile');
                formData.append('receiverID', vcht_currentInterlocutor.id);
                jQuery('#vcht_winFilesUpload .modal-footer>a.btn-primary').fadeOut();
            }),
                    this.on("complete", function (file, xhr) {
                        if (jQuery('#vcht_uploadFilesField').find('.dz-preview:not(.dz-complete)').length == 0 || dropzone.find('.dz-preview').length == 0) {
                            jQuery('#vcht_winFilesUpload .modal-footer>a.btn-primary').fadeIn();
                        }
                    });
            this.on("success", function (file, xhr) {
                var thumb = jQuery(file.previewElement);
                thumb.attr('data-file', file.name);
            });
            this.on("removedfile", function (file, xhr) {
            });
        }
    });
    if (vcht_data.allowFilesFromOperators == 1) {
    } else {
        jQuery('#vcht_chatPanelFilesBtn').hide();
    }

    jQuery('#vcht_webFrame').on('load', function () {
        jQuery('#vcht_loader').fadeOut();
        jQuery('#vcht_loaderFrame').fadeOut();

        if (vcht_modeSelection) {
            jQuery('#vcht_selectionInfoPanel').stop().slideDown();
            jQuery('#vcht_webFrame').fadeIn();
        }
        if (vcht_shownElement != '') {

            jQuery('#vcht_webFrame').get(0).contentWindow.vcht_showElement(vcht_shownElement, vcht_currentInterlocutor.avatarImg);
            vcht_shownElement = '';
        }
    });

    jQuery('#vcht_editFieldPanel').find('input,select,textarea').on('change', vcht_updateEditFieldPanel);
    jQuery('#vcht_editFieldPanel').find('[data-toggle="switch"]').vcht_BootstrapSwitch('onSwitchChange', vcht_updateEditFieldPanel);
    vcht_updateEditFieldPanel();
}
function vcht_transferChatSelectChange() {
    var operatorID = jQuery('#vcht_transferChatSelect').val();
    if (operatorID != '' && parseInt(operatorID) > 0) {
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'vcht_transferChat',
                operatorID: operatorID,
                userID: vcht_currentInterlocutor.id
            }
        });

        vcht_currentInterlocutor.currentOperator = operatorID;
        vcht_stopCurrentChat();
    }
    jQuery('#vcht_transferChatSelect').val('');
}
function vcht_initUI() {
    jQuery('.vcht_bootstrap [data-toggle="switch"]').vcht_BootstrapSwitch();
    jQuery('.vcht_bootstrap .form-group').each(function () {
        var self = this;
        if (jQuery(self).find('small').length > 0) {
            jQuery(this).find('.form-control,.bootstrap-switch').v_tooltip({
                title: jQuery(self).find('small').html(),
                placement: 'bottom'
            });
            jQuery(self).find('small').hide();
        }
    });
    jQuery('.vcht_fullPanel .vcht_scrollbar').mCustomScrollbar({
        theme: 'dark'
    });
    jQuery('#vcht_loginFieldsTable tbody').sortable({
        helper: function (e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each(function (index)
            {
                jQuery(this).width($originals.eq(index).width());
            });
            return $helper;
        },
        stop: function (event, ui) {
            var fields = '';
            jQuery('#vcht_loginFieldsTable tbody tr[data-id]').each(function (i) {
                fields += jQuery(this).attr('data-id') + ',';
            });
            if (fields.length > 0) {
                fields = fields.substr(0, fields.length - 1);
            }
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'vcht_changeFieldsOrders',
                    fields: fields
                }
            });
        }
    });
    jQuery('#vcht_contactFieldsTable tbody').sortable({
        helper: function (e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each(function (index)
            {
                jQuery(this).width($originals.eq(index).width());
            });
            return $helper;
        },
        stop: function (event, ui) {
            var fields = '';
            jQuery('#vcht_contactFieldsTable tbody tr[data-id]').each(function (i) {
                fields += jQuery(this).attr('data-id') + ',';
            });
            if (fields.length > 0) {
                fields = fields.substr(0, fields.length - 1);
            }
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'vcht_changeFieldsOrders',
                    fields: fields
                }
            });
        }
    });
    jQuery('.vcht_iconslist li a').click(function () {
        jQuery(this).closest('.form-group').find('.btn.dropdown-toggle>span.vcht_name').html(jQuery(this).attr('data-icon'));
        jQuery(this).closest('.form-group').find('input.vcht_iconField').val(jQuery(this).attr('data-icon'));
        jQuery(this).closest('ul').find('li.vcht_active').removeClass('vcht_active');
        jQuery(this).closest('li').addClass('vcht_active');
    });
    jQuery('input.vcht_iconField').on('change', function () {
        if (jQuery(this).closest('.form-group').find('.btn.dropdown-toggle>span.vcht_name').html() != jQuery(this).val()) {
            jQuery(this).closest('.form-group').find('.btn.dropdown-toggle>span.vcht_name').html(jQuery(this).val());
        }
    });
     jQuery('#vcht_contactFieldsTable tbody').sortable({
        helper: function (e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each(function (index)
            {
                jQuery(this).width($originals.eq(index).width());
            });
            return $helper;
        },
        stop: function (event, ui) {
            var fields = '';
            jQuery('#vcht_contactFieldsTable tbody tr[data-id]').each(function (i) {
                fields += jQuery(this).attr('data-id') + ',';
            });
            if (fields.length > 0) {
                fields = fields.substr(0, fields.length - 1);
            }
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'vcht_changeFieldsOrders',
                    fields: fields
                }
            });
        }
    });
    
    jQuery('#vcht_itemOptionsValues tbody').sortable({
        helper: function (e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each(function (index)
            {
                jQuery(this).width($originals.eq(index).width());
            });
            return $helper;
        },
        stop: function (event, ui) {
           
        }
    });
    jQuery('#vcht_contactFieldsTable tbody').sortable({
        cancel: ".static",
        helper: function (e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each(function (index)
            {
                jQuery(this).width($originals.eq(index).width());
            });
            return $helper;
        },
        stop: function (event, ui) {
            var fields = '';
            jQuery('#vcht_contactFieldsTable tbody tr[data-id]').each(function (i) {
                fields += jQuery(this).attr('data-id') + ',';
            });
            if (fields.length > 0) {
                fields = fields.substr(0, fields.length - 1);
            }
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'vcht_changeFieldsOrders',
                    fields: fields
                }
            });
        }
    });
    jQuery('.colorpick').each(function () {
        var $this = jQuery(this);
        if (jQuery(this).prev('.vcht_colorPreview').length == 0) {
            jQuery(this).before('<div class="vcht_colorPreview"></div>');
        }
        jQuery(this).prev('.vcht_colorPreview').click(function () {
            jQuery(this).next('.colorpick').trigger('click');
        });
        jQuery(this).colpick({
            color: $this.val().substr(1, 7),
            onChange: function (hsb, hex, rgb, el, bySetColor) {
                jQuery(el).val('#' + hex);
                jQuery(el).prev('.vcht_colorPreview').css({
                    backgroundColor: '#' + hex
                });
            }
        });
    });
    vcht_updateSettingsPanel();
}
function vcht_onResize() {
    jQuery('#vcht_mainWrapper').css({
        width: jQuery(window).width() - jQuery('#adminmenuwrap').width(),
        left: jQuery('#adminmenuwrap').width(),
        height: jQuery(window).height() - jQuery('#wpadminbar').height(),
        top: jQuery('#wpadminbar').height()
    });
    jQuery('#vcht_usersListPanel').css({
        height: jQuery(window).height() - (jQuery('#wpadminbar').height() + jQuery('#vcht_mainHeader').height()),
        top: jQuery('#wpadminbar').height() + jQuery('#vcht_mainHeader').height(),
        left: jQuery('#adminmenuwrap').width()
    });
    jQuery('#vcht_usersListPanel .vcht_panel:not(.vcht_hidden) .vcht_panelBody').each(function () {
        jQuery(this).css({
            height: (jQuery('#vcht_usersListPanel').height() / jQuery('#vcht_usersListPanel .vcht_panel:not(.vcht_hidden)').length) - jQuery(this).closest('.vcht_panel').find('.vcht_panelHeader').height()
        });
    });

    var cssLeft = 0;
    var cssWidth = 0;
    if (!jQuery('#vcht_usersListPanel').is('.vcht_collapsed')) {
        cssLeft = jQuery('#vcht_usersListPanel').width();
        cssWidth = jQuery('#vcht_usersListPanel').width();
    }
    setTimeout(function () {
        var panelHeight = jQuery('#vcht_mainWrapper').height() - (jQuery('#vcht_mainHeader').height() + jQuery('#vcht_chatPanel:not(.vcht_hidden)').height());
        if (jQuery(window).width() <= 782) {
            panelHeight = jQuery(window).height() - jQuery('#wpadminbar').height()
        }
        jQuery('.vcht_fullPanel').css({
            left: cssLeft,
            width: jQuery('#vcht_mainWrapper').width() - cssWidth,
            height: panelHeight
        });
        setTimeout(function () {
            jQuery('.vcht_fullPanel:not(#vcht_backgroundPanel)').each(function () {
                var _this = this;
                jQuery(this).find('.vcht_fullPanelBody').css({
                    height: jQuery(_this).innerHeight() - jQuery(_this).find('.vcht_fullPanelHeader').outerHeight()
                });
            });
        }, 400);
    }, 350);

    jQuery('#vcht_chatPanel').css({
        left: cssLeft + jQuery('#adminmenuwrap').width(),
        width: jQuery(window).width() - (cssLeft + jQuery('#adminmenuwrap').width())
    });
    jQuery('#vcht_chatPanel.vcht_fullscreen .vcht_chatPanelBody').css({
        height: jQuery(window).height() - (jQuery('#wpadminbar').outerHeight() + jQuery('#vcht_mainHeader').outerHeight() + jQuery('#vcht_chatPanelHeader').outerHeight())
    });
    jQuery('#vcht_chatPanel:not(.vcht_fullscreen):not(.vcht_minify) .vcht_chatPanelBody').css({
        height: 320 + (jQuery('#vcht_chatPanelMsgArea').height() - 38)
    });
    jQuery('#vcht_chatPanel.vcht_minify,#vcht_chatPanel.vcht_hidden').find('.vcht_chatPanelBody').css({
        height: 0
    });
    jQuery('#vcht_chatPanelMsgArea').css({
        width: jQuery('#vcht_chatPanelWriteContainer').width() - (jQuery('#vcht_chatPanelWriteContainer>label').outerWidth() + jQuery('#vcht_chatPanelBtnsTb').outerWidth() + 28)
    });
    vcht_updateBubblePanels();
}

function vcht_toggleLogin() {
    if (vcht_isConnected) {
        vcht_logOut();
    } else {
        vcht_logIn();
    }
}
function vcht_logIn() {
    vcht_isConnected = true;
    jQuery('.vcht_bootstrap').addClass('vcht_online');
    jQuery('#vcht_operatorHeaderStatus strong').html(vcht_data.texts['Online']);
    jQuery('#vcht_backgroundPanelTitle strong').html(vcht_data.texts['Online']);
    jQuery('#vcht_operatorBtnConnect').removeClass('btn-primary').addClass('btn-danger');
    jQuery('#vcht_backgroundPanelLoginBtn').removeClass('btn-primary').addClass('btn-danger');
    jQuery('#vcht_operatorBtnConnect').html('<span class="glyphicon glyphicon-remove"></span>' + vcht_data.texts['Log out']);
    jQuery('#vcht_backgroundPanelLoginBtn').html('<span class="glyphicon glyphicon-remove"></span>' + vcht_data.texts['Log out']);
    jQuery('#vcht_backgroundPanelTxt').stop().slideUp();
    jQuery('#vcht_usersListPanel').removeClass('vcht_collapsed');
    vcht_onResize();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_operatorLogIn'
        },
        success: function (rep) {
            vcht_checkOnlineVisitors();
        }
    });
}
function vcht_logOut() {
    vcht_isConnected = false;
    vcht_isInit = false;
    clearTimeout(vcht_checkMsgTimer);
    vcht_currentInterlocutor = false;
    jQuery('#vcht_chatPanel').addClass('vcht_hidden');
    vcht_changeSizeChatPanel('minify');
    jQuery('#vcht_webFrame,#vcht_loaderFrame').fadeOut();
    jQuery('.vcht_bootstrap').removeClass('vcht_online');
    jQuery('#vcht_operatorHeaderStatus strong').html(vcht_data.texts['Offline']);
    jQuery('#vcht_backgroundPanelTitle strong').html(vcht_data.texts['Offline']);
    jQuery('#vcht_operatorBtnConnect').removeClass('btn-danger').addClass('btn-primary');
    jQuery('#vcht_backgroundPanelLoginBtn').removeClass('btn-danger').addClass('btn-primary');
    jQuery('#vcht_operatorBtnConnect').html('<span class="glyphicon glyphicon-ok"></span>' + vcht_data.texts['Log in']);
    jQuery('#vcht_backgroundPanelLoginBtn').html('<span class="glyphicon glyphicon-ok"></span>' + vcht_data.texts['Log in']);
    jQuery('#vcht_backgroundPanelTxt').stop().slideDown();
    jQuery('#vcht_usersListPanel').addClass('vcht_collapsed');
    jQuery('.vcht_userListItem[data-id]').remove();
    jQuery('.vcht_userListItem:not([data-id])').show();

    vcht_onResize();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_operatorLogOut'
        },
        success: function (rep) {
        }
    });
}
function vcht_updateSettingsPanel() {
    if (jQuery('#vcht_adminSettingsPanel').length > 0) {
        if (jQuery('#vcht_adminSettingsPanel').find('[name="enableVisitorsTracking"]').vcht_BootstrapSwitch('state') == true) {
            jQuery('#vcht_adminSettingsPanel').find('[name="trackingDelay"]').closest('.form-group').slideDown();
        } else {
            jQuery('#vcht_adminSettingsPanel').find('[name="trackingDelay"]').closest('.form-group').slideUp();
        }
    }

    if (jQuery('#vcht_adminSettingsPanel').find('[name="allowFilesFromOperators"]').vcht_BootstrapSwitch('state') == true ||
            jQuery('#vcht_adminSettingsPanel').find('[name="allowFilesFromCustomers"]').vcht_BootstrapSwitch('state') == true) {
        jQuery('#vcht_adminSettingsPanel').find('[name="filesMaxSize"]').closest('.form-group').slideDown();
        jQuery('#vcht_adminSettingsPanel').find('[name="allowedFiles"]').closest('.form-group').slideDown();
    } else {
        jQuery('#vcht_adminSettingsPanel').find('[name="filesMaxSize"]').closest('.form-group').slideUp();
        jQuery('#vcht_adminSettingsPanel').find('[name="allowedFiles"]').closest('.form-group').slideUp();
    }

    if (jQuery('#vcht_adminSettingsPanel [name="enableLoginPanel"]').vcht_BootstrapSwitch('state')) {
        jQuery('#vcht_loginFieldsContainer,#vcht_tabSettingsLogin [name]:not(enableLoginPanel)').slideDown();
    } else {
        jQuery('#vcht_loginFieldsContainer,#vcht_tabSettingsLogin [name]:not(enableLoginPanel)').slideUp();
    }
    if (jQuery('#vcht_adminSettingsPanel [name="enableContactForm"]').vcht_BootstrapSwitch('state')) {
        jQuery('#vcht_contactFieldsContainer').slideDown();
        jQuery('#vcht_adminSettingsPanel').find('[name="contactFormIcon"]').closest('.form-group').slideDown();
        jQuery('#vcht_adminSettingsPanel').find('[name="emailAdmin"]').closest('.form-group').slideDown();
        jQuery('#vcht_adminSettingsPanel').find('[name="emailSubject"]').closest('.form-group').slideDown();
    } else {
        jQuery('#vcht_contactFieldsContainer').slideUp();
        jQuery('#vcht_adminSettingsPanel').find('[name="contactFormIcon"]').closest('.form-group').slideUp();
        jQuery('#vcht_adminSettingsPanel').find('[name="emailAdmin"]').closest('.form-group').slideUp();
        jQuery('#vcht_adminSettingsPanel').find('[name="emailSubject"]').closest('.form-group').slideUp();
    }
    if (jQuery('#vcht_adminSettingsPanel [name="usePoFile"]').vcht_BootstrapSwitch('state')) {
        jQuery('#vcht_settingsTextsTable').fadeOut();
    } else {
        jQuery('#vcht_settingsTextsTable').fadeIn();
    }


}

function vcht_loadSettings() {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_loadSettings'
        },
        success: function (rep) {
            rep = rep.trim();
            rep = jQuery.parseJSON(rep);
            vcht_settings = rep;
            jQuery('#vcht_tabSettingsDesign .colorpick').each(function () {
                var $this = jQuery(this);
                eval("jQuery(this).prev('.vcht_colorPreview').css({backgroundColor: rep.settings." + jQuery(this).attr('name') + " });");
            });
            jQuery('#vcht_adminSettingsPanel').find('input,select,textarea').each(function () {
                if (jQuery(this).is('[name]')) {
                    if (jQuery(this).is('[data-toggle="switch"]')) {
                        var value = false;
                        eval('if(rep.settings.' + jQuery(this).attr('name') + ' == 1){jQuery(this).vcht_BootstrapSwitch(\'state\',true);} else {jQuery(this).vcht_BootstrapSwitch(\'state\',false);}');

                    } else if (jQuery(this).is('pre')) {
                        eval('jQuery(this).html(rep.settings.' + jQuery(this).attr('name') + ');');
                    } else {
                        eval('jQuery(this).val(rep.settings.' + jQuery(this).attr('name') + ');');
                    }
                }
            });

            jQuery('#vcht_loginFieldsTable tbody').html('');
            jQuery('#vcht_contactFieldsTable tbody').html('');
            jQuery.each(rep.fields, function () {
                var tr = jQuery('<tr data-id="' + this.id + '"></tr>');
                tr.append('<td>' + this.title + '</td>');
                tr.append('<td>' + this.backendTitle + '</td>');
                tr.append('<td>' + jQuery('#vcht_editFieldPanel').find('[name="type"] > option[value="' + this.type + '"]').text() + '</td>');
                var isRequired = vcht_data.texts['No'];
                if (this.isRequired == 1) {
                    isRequired = vcht_data.texts['Yes'];
                }
                tr.append('<td>' + isRequired + '</td>');
                var tdAction = jQuery('<td class="vcht_actionTh"></td>');
                tdAction.append('<a href="javascript:" onclick="vcht_editField(' + this.id + ',' + this.inLoginPanel + ');" class="btn btn-circle btn-primary"><span class="fa fa-pencil"></span></a>');
                if (this.createdByAdmin == 0 || vcht_data.isAdmin == 1) {
                    tdAction.append('<a href="javascript:" onclick="vcht_removeField(' + this.id + ');" class="btn btn-circle btn-danger"><span class="fa fa-trash"></span></a>');
                }

                tr.append(tdAction);
                if (this.inLoginPanel == '1') {
                    jQuery('#vcht_loginFieldsTable tbody').append(tr);
                } else {
                    jQuery('#vcht_contactFieldsTable tbody').append(tr);
                }
            });


            jQuery('#vcht_tabSettingsRoles table tr[data-role="' + this + '"] [data-toggle="switch"]').vcht_BootstrapSwitch('state', false);
            jQuery('#vcht_tabSettingsRoles table tr[data-role="administrator"] [data-toggle="switch"]').vcht_BootstrapSwitch('state', true).vcht_BootstrapSwitch('disabled', true);
            jQuery('#vcht_tabSettingsRoles table tr[data-role="chat_operator"] [data-toggle="switch"]').vcht_BootstrapSwitch('state', true).vcht_BootstrapSwitch('disabled', true);
            var roles = rep.settings.rolesAllowed.split(',');
            jQuery.each(roles, function () {
                jQuery('#vcht_tabSettingsRoles table tr[data-role="' + this + '"] [data-toggle="switch"]').vcht_BootstrapSwitch('state', true);
            });
            jQuery('input.vcht_iconField').trigger('change');
            setTimeout(function () {
                jQuery('#vcht_loader').fadeOut();
            }, 350);
            jQuery('#vcht_adminSettingsPanel').fadeIn();
            setTimeout(vcht_updateSettingsPanel, 300);
        }
    });
}
function vcht_showMainSettingsPanel() {
    jQuery('#vcht_loader').fadeIn();
    if (!jQuery('#vcgt_chatPanel').is('vcht_minify')) {
        vcht_changeSizeChatPanel('minify');
    }
    vcht_loadSettings();
}
function vcht_createPanelUser(userData, usersWithoutAvatars) {
    if (userData.isCurrentUser === undefined) {
        userData.isCurrentUser = false;
    }

    if (typeof (userData.declined) == "undefined") {
        userData.declined = false;
    }
    if (userData.history.length > 0) {
        if (userData.history[userData.history.length - 1].receiverID == 0 && !userData.declined) {
            userData.isChatting = true;
        } else {
            if (userData.history[userData.history.length - 1].receiverID != vcht_currentUser.id) {
            }
        }
    } else if (typeof (userData.currentOperator) == "undefined" || userData.currentOperator == 0) {
    }
    if (jQuery('#vcht_usersListPanel [data-id="' + userData.id + '"]').length > 0) {
        if (jQuery('#vcht_usersListPanel [data-id="' + userData.id + '"]').closest('#vcht_onlineVisitorsList').length > 0 && userData.isOperator == 1) {
            jQuery('#vcht_usersListPanel [data-id="' + userData.id + '"]').detach().appendTo('#vcht_onlineOperatorsList .vcht_panelBody');
            jQuery('#vcht_usersListPanel [data-id="' + userData.id + '"]').find('.vcht_btnChatUser').removeAttr('disabled');
        } else if (jQuery('#vcht_usersListPanel [data-id="' + userData.id + '"]').closest('#vcht_onlineVisitorsList').length > 0 && userData.isChatting && !userData.declined && userData.currentOperator == vcht_currentUser.id) {
            jQuery('#vcht_usersListPanel [data-id="' + userData.id + '"]').detach().appendTo('#vcht_currentChatsList .vcht_panelBody');
            jQuery('#vcht_usersListPanel [data-id="' + userData.id + '"]').find('.vcht_btnChatUser').removeAttr('disabled');
        } else if (jQuery('#vcht_usersListPanel [data-id="' + userData.id + '"]').closest('#vcht_onlineOperatorsList').length > 0 && userData.isOperator == 0) {
            if (userData.isChatting && (userData.currentOperator == vcht_currentUser.id || typeof (userData.currentOperator) == "undefined" || userData.currentOperator == 0)) {
                jQuery('#vcht_usersListPanel [data-id="' + userData.id + '"]').detach().appendTo('#vcht_currentChatsList .vcht_panelBody');
            } else {
                jQuery('#vcht_usersListPanel [data-id="' + userData.id + '"]').detach().appendTo('#vcht_onlineVisitorsList .vcht_panelBody');
            }
        } else if (jQuery('#vcht_usersListPanel [data-id="' + userData.id + '"]').closest('#vcht_currentChatsList').length > 0 && userData.isOperator == 1) {
            jQuery('#vcht_usersListPanel [data-id="' + userData.id + '"]').detach().appendTo('#vcht_onlineOperatorsList .vcht_panelBody');
        } else if (jQuery('#vcht_usersListPanel [data-id="' + userData.id + '"]').closest('#vcht_currentChatsList').length > 0 && (!userData.isChatting || (userData.currentOperator != vcht_currentUser.id && userData.currentOperator > 0))) {
            jQuery('#vcht_usersListPanel [data-id="' + userData.id + '"]').detach().appendTo('#vcht_onlineVisitorsList .vcht_panelBody');
        }

        if (jQuery('#vcht_usersListPanel [data-id="' + userData.id + '"]').closest('#vcht_onlineVisitorsList').length > 0 && jQuery('#vcht_usersListPanel [data-id="' + userData.id + '"] .vcht_alertPoint').css('display') != 'none') {
            jQuery('#vcht_usersListPanel [data-id="' + userData.id + '"]').detach().appendTo('#vcht_currentChatsList .vcht_panelBody');
        }
    } else {
        var $item = jQuery('<div class="vcht_userListItem" data-id="' + userData.id + '" data-currentuser="' + userData.isCurrentUser + '"></div>');
        $item.append('<img class="vcht_avatarContainer" src="" alt="' + userData.username + '"/>');
        if (!userData.isCurrentUser) {
            $item.append('<a class="btn btn-primary btn-circle vcht_btnChatUser" data-placement="bottom" onclick="if(!jQuery(this).is(\'[disabled]\')){vcht_openChatPanel(' + userData.id + ');}"><span class="fa fa-comment-o"></span></a>');
        }
        $item.append('<span class="vcht_username">' + userData.username + '</span>');
        $item.append('<span class="vcht_alertPoint" style="display:none;"></span>');
        $item.append('<div class="clearfix"></div>');

        if (userData.isOperator) {
            jQuery('#vcht_onlineOperatorsList .vcht_panelBody').append($item);
            jQuery('#vcht_onlineOperatorsList .vcht_userListItem:not([data-id])').hide();

        } else if (userData.isChatting) {
            jQuery('#vcht_currentChatsList .vcht_panelBody').append($item);
            jQuery('#vcht_currentChatsList .vcht_userListItem:not([data-id])').hide();
        } else {
            jQuery('#vcht_onlineVisitorsList .vcht_panelBody').append($item);
            jQuery('#vcht_onlineVisitorsList .vcht_userListItem:not([data-id])').hide();
        }
        if (!userData.avatarImg || userData.avatarImg == '') {
            usersWithoutAvatars.push(userData.id);
        }
    }

    vcht_updateNoUsersPanel();
    vcht_updateOtherChatsBtns();
    vcht_updateTransferSelect();

    return usersWithoutAvatars;
}
function vcht_updateNoUsersPanel() {
    if (jQuery('#vcht_currentChatsList .vcht_userListItem').length > 1) {
        jQuery('#vcht_currentChatsList .vcht_userListItem:not([data-id])').hide();
    } else {
        jQuery('#vcht_currentChatsList .vcht_userListItem:not([data-id])').show();
    }
    if (jQuery('#vcht_onlineOperatorsList .vcht_userListItem').length > 1) {
        jQuery('#vcht_onlineOperatorsList .vcht_userListItem:not([data-id])').hide();
    } else {
        jQuery('#vcht_onlineOperatorsList .vcht_userListItem:not([data-id])').show();
    }
    if (jQuery('#vcht_onlineVisitorsList .vcht_userListItem').length > 1) {
        jQuery('#vcht_onlineVisitorsList .vcht_userListItem:not([data-id])').hide();
    } else {
        jQuery('#vcht_onlineVisitorsList .vcht_userListItem:not([data-id])').show();
    }
}
function vcht_updateTransferSelect() {
    var checkedIds = new Array();
    jQuery('#vcht_onlineOperatorsList .vcht_userListItem[data-id]').each(function () {
        if (parseInt(jQuery(this).attr('data-id')) != vcht_currentUser.id) {
            var id = parseInt(jQuery(this).attr('data-id'));
            checkedIds.push(id);
            if (jQuery('#vcht_transferChatSelect option[value="' + id + '"]').length == 0) {
                var userData = vcht_getUserByID(id);
                jQuery('#vcht_transferChatSelect').append('<option value="' + id + '">' + userData.username + '</option>');
            }
        }
    });

    jQuery('#vcht_transferChatSelect option[value!=""]').each(function () {
        var id = parseInt(jQuery(this).val());
        if (checkedIds.indexOf(id) == -1) {
            jQuery(this).remove();
        }
    });

}
function vcht_updateOtherChatsBtns() {
    jQuery('#vcht_onlineVisitorsList .vcht_userListItem[data-id]').each(function () {
        var user = vcht_getUserByID(jQuery(this).attr('data-id'));
        if (typeof (user.currentOperator) != "undefined" && typeof (user.currentOperator) != "undefined" && user.currentOperator > 0 && user.currentOperator != vcht_currentUser.id) {
            jQuery(this).find('.vcht_btnChatUser').attr('disabled', 'disabled');
            var operator = vcht_getUserByID(user.currentOperator);
            jQuery(this).find('.vcht_btnChatUser').attr('title', vcht_data.texts['Current operator'] + ' : ' + operator.username);
            jQuery(this).find('.vcht_btnChatUser').v_tooltip('fixTitle');
        } else {
            jQuery(this).find('.vcht_btnChatUser').removeAttr('disabled');
            jQuery(this).find('.vcht_btnChatUser').v_tooltip('destroy');
        }
    });
}

function vcht_checkOnlineVisitors() {
    if (vcht_isConnected) {

        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'vcht_operatorGetOnlineVisitors'
            },
            success: function (rep) {
                rep = rep.trim();
                rep = jQuery.parseJSON(rep);

                var usersWithoutAvatars = new Array();

                var checkedIds = new Array();
                jQuery.each(rep, function (i) {
                    checkedIds.push(this.id);
                    if (this.isOperator == 1) {
                        this.isOperator = true;
                    } else {
                        this.isOperator = false;
                    }

                    var existIndex = vcht_getUserIndexByID(this.id);
                    this.history = new Array();
                    var userData = this;
                    if (this.currentOperator == vcht_currentUser.id) {
                        this.isChatting = true;
                    } else {
                        this.isChatting = false;
                    }
                    if (existIndex == -1) {
                        vcht_allUsers.push(this);
                    } else {
                        vcht_allUsers[existIndex].isOperator = this.isOperator;
                        vcht_allUsers[existIndex].currentPage = this.currentPage;
                        vcht_allUsers[existIndex].currentOperator = this.currentOperator;
                        vcht_allUsers[existIndex].isChatting = this.isChatting;

                        userData = vcht_allUsers[existIndex];
                    }
                    if (vcht_currentUser == false && this.id == vcht_data.userID) {
                        vcht_currentUser = this;
                        if (this.country == '' && vcht_data.enableGeolocalization == '1') {
                            vcht_geoSend();
                        }
                    }
                    usersWithoutAvatars = vcht_createPanelUser(userData, usersWithoutAvatars);

                });
                jQuery('#vcht_usersListPanel [data-id]').each(function () {
                    if (checkedIds.indexOf(jQuery(this).attr('data-id')) == -1) {
                        if (vcht_currentInterlocutor && parseInt(jQuery(this).attr('data-id')) == vcht_currentInterlocutor.id) {
                            vcht_stopCurrentChat();
                        }
                        vcht_allUsers = jQuery.grep(vcht_allUsers, function (value) {
                            return value.id != this;
                        });
                        jQuery(this).remove();
                    }
                });

                if (usersWithoutAvatars.length > 0) {
                    vcht_getUsersInfos(usersWithoutAvatars);
                }

                if (!vcht_isInit) {
                    vcht_isInit = true;
                    vcht_checkNewMessages();
                }
                vcht_updateOtherChatsBtns();
                vcht_updateTransferSelect();
            }
        });
        setTimeout(vcht_checkOnlineVisitors, vcht_data.trackingDelay * 1000);
    }
}

function vcht_geoSend() {
    jQuery.ajax({
        type: 'post',
        url: ajaxurl,
        data: {
            action: 'vcht_geolocalize'
        },
        success: function (rep) {

        }
    });
}
function vcht_getUsersInfos(usersIDs) {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_getUsersInfos',
            usersIDs: JSON.stringify(usersIDs)
        },
        success: function (rep) {
            rep = rep.trim();
            rep = jQuery.parseJSON(rep);
            jQuery.each(rep, function (i) {
                if (this != '') {
                    var user = vcht_getUserByID(usersIDs[i]);
                    if (!user) {
                        user = this;
                        user.fields = jQuery.parseJSON(user.fields);
                        var pendingUser = vcht_getPendingUserByID(usersIDs[i]);
                        var mustNotify = false;
                        if (pendingUser != false) {
                            user.isChatting = pendingUser.isChatting;
                            user.history = pendingUser.history;
                            user.currentOperator = pendingUser.currentOperator;
                            if (user.history.length > 0) {
                                mustNotify = true;
                            }
                        }
                        if (user.isOperator == 1) {
                            user.isOperator = true;
                        } else {
                            user.isOperator = false;
                        }

                        vcht_allUsers.push(user);
                        vcht_createPanelUser(user, new Array());
                        if (mustNotify) {
                            if (jQuery('#vcht_audioMsg').data('enable') != 'false') {
                                jQuery('#vcht_audioMsg').get(0).play();
                            }
                            jQuery('.vcht_userListItem[data-id="' + user.id + '"] .vcht_alertPoint').fadeIn();
                        }
                    }
                    if (typeof this.fields == "string") {
                        user.fields = jQuery.parseJSON(this.fields);
                    } else {
                        user.fields = this.fields;
                    }
                    user.avatarImg = this.avatar;
                    user.country = this.country;
                    user.city = this.city;
                    user.currentOperator = this.currentOperator;
                    user.uploadFolderName = this.uploadFolderName;
                    if (typeof (this.avatar) == "undefined") {
                        this.avatar = vcht_data.assetsUrl + 'img/guest-128.png';
                    }
                    jQuery('#vcht_usersListPanel [data-id="' + usersIDs[i] + '"] .vcht_avatarContainer').on('load', function () {
                        jQuery(this).animate({opacity: 1}, 300);
                    });
                    jQuery('#vcht_usersListPanel [data-id="' + usersIDs[i] + '"] .vcht_avatarContainer').attr('src', this.avatar);
                    if (this.senderID != vcht_currentUser.id && (this.senderID != vcht_currentInterlocutor.id)) {
                        vcht_updateFieldsInfos();
                    }

                }
            });

        }
    });

}
function vcht_updateFieldsInfos() {
    jQuery('#vcht_fieldsInfos').html('');
    jQuery.each(vcht_currentInterlocutor.fields, function () {
        var field = vcht_getFieldByID(this.id);
        if (field != false && field.showInDetails == 1) {
            var value = this.value;
            jQuery('#vcht_fieldsInfos').append('<li>' + this.title + ' : <strong>' + value + '</strong></li>');
        }
    });
    if (vcht_currentInterlocutor.country != '') {
        var cityTxt = '';
        if (vcht_currentInterlocutor.city != '') {
            cityTxt = ', ' + vcht_currentInterlocutor.city;
        }
        jQuery('#vcht_fieldsInfos').append('<li>' + vcht_currentInterlocutor.country + cityTxt + '</li>');
    }
}
function vcht_changeSizeChatPanel(size) {
    if (size == 'fullscreen') {
        if (jQuery('#vcht_chatPanel').is('.vcht_fullscreen')) {
            jQuery('#vcht_chatPanel').removeClass('vcht_fullscreen');
            jQuery('#vcht_chatPanelFullscreenBtn .fa').removeClass('fa-window-restore').addClass('fa-window-maximize');
            jQuery('#vcht_chatPanelMinifyBtn .fa').removeClass('fa-window-restore').addClass('fa-window-minimize');
        } else {
            jQuery('#vcht_chatPanelHeader .vcht_alertPoint').fadeOut();
            jQuery('#vcht_chatPanel').removeClass('vcht_minify').addClass('vcht_fullscreen');
            jQuery('#vcht_chatPanelFullscreenBtn .fa').removeClass('fa-window-maximize').addClass('fa-window-restore');
            jQuery('#vcht_chatPanelMinifyBtn .fa').removeClass('fa-window-restore').addClass('fa-window-minimize');
            jQuery('#vcht_chatPanelHistory').mCustomScrollbar("scrollTo", "bottom", {
                scrollInertia: 0,
                timeout: 500
            });
            jQuery('#vcht_chatPanelHistory').mCustomScrollbar("scrollTo", "bottom", {
                scrollInertia: 0,
                timeout: 350
            });
        }
    } else if (size == 'minify') {
        if (jQuery('#vcht_chatPanel').is('.vcht_minify')) {
            jQuery('#vcht_chatPanelHeader .vcht_alertPoint').fadeOut();
            jQuery('#vcht_chatPanel').removeClass('vcht_fullscreen').removeClass('vcht_minify');
            jQuery('#vcht_chatPanelFullscreenBtn .fa').removeClass('fa-window-restore').addClass('fa-window-maximize');
            jQuery('#vcht_chatPanelMinifyBtn .fa').removeClass('fa-window-restore').addClass('fa-window-minimize');
            jQuery('#vcht_chatPanelHistory').mCustomScrollbar("scrollTo", "bottom", {
                scrollInertia: 0,
                timeout: 500
            });
            jQuery('#vcht_chatPanelHistory').mCustomScrollbar("scrollTo", "bottom", {
                scrollInertia: 0,
                timeout: 350
            });
        } else {
            jQuery('#vcht_chatPanelFullscreenBtn .fa').removeClass('fa-window-restore').addClass('fa-window-maximize');
            jQuery('#vcht_chatPanel').removeClass('vcht_fullscreen').addClass('vcht_minify');
            jQuery('#vcht_chatPanelMinifyBtn .fa').removeClass('fa-window-minimize').addClass('fa-window-restore');
        }
    } else if (size == 'default') {
        jQuery('#vcht_chatPanelHeader .vcht_alertPoint').fadeOut();
        jQuery('#vcht_chatPanel').removeClass('vcht_fullscreen');
        jQuery('#vcht_chatPanelFullscreenBtn .fa').removeClass('fa-window-restore').addClass('fa-window-maximize');
        jQuery('#vcht_chatPanelMinifyBtn .fa').removeClass('fa-window-restore').addClass('fa-window-minimize');
        jQuery('#vcht_chatPanel').removeClass('vcht_fullscreen').removeClass('vcht_minify');
        jQuery('#vcht_chatPanelFullscreenBtn .fa').removeClass('fa-window-restore').addClass('fa-window-maximize');
        jQuery('#vcht_chatPanelMinifyBtn .fa').removeClass('fa-window-restore').addClass('fa-window-minimize');
        jQuery('#vcht_chatPanelHistory').mCustomScrollbar("scrollTo", "bottom", {
            scrollInertia: 0,
            timeout: 500
        });
        jQuery('#vcht_chatPanelHistory').mCustomScrollbar("scrollTo", "bottom", {
            scrollInertia: 0,
            timeout: 350
        });
    }
    vcht_onResize();
}
function vcht_openChatPanel(userID) {
    var user = vcht_getUserByID(userID);
    if (!user.isOperator) {
        vcht_showUrl(user.currentPage);
    } else {
        jQuery('#vcht_webFrame,#vcht_loaderFrame').fadeOut();
    }
    if (typeof (jQuery('#vcht_webFrame').get(0)) != "undefined" && typeof (jQuery('#vcht_webFrame').get(0).contentWindow) != "undefined" && typeof (jQuery('#vcht_webFrame').get(0).contentWindow.vcht_stopShowElement) != "undefined") {
        jQuery('#vcht_webFrame').get(0).contentWindow.vcht_stopShowElement();
    }
    jQuery('#vcht_adminSettingsPanel,#vcht_cannedMsgsPanel,#vcht_userHistoryPanel,#vcht_fullHistoryPanel').fadeOut();
    jQuery('#vcht_chatPanelBtnsTb').hide();
    jQuery('.vcht_userListItem[data-id="' + userID + '"] .vcht_alertPoint').hide();
    jQuery('#vcht_chatPanelHeader .vcht_alertPoint').hide();
    var chkWritePanel = true;
    if (userID != vcht_currentInterlocutor.id) {
        if (!user.fields || user.fields.length == 0) {
            vcht_getUsersInfos([userID]);
        }
        vcht_currentFiles = new Array();
        vcht_currentSelection = '';
        vcht_currentPage = '';

        if (!vcht_currentInterlocutor) {
            vcht_changeSizeChatPanel('default');
        }
        vcht_currentInterlocutor = user;
        jQuery('#vcht_chatPanel #vcht_chatPanelHeaderName').html(user.username);
        jQuery('#vcht_chatPanel #vcht_chatPanelHeaderImg').attr('src', user.avatarImg);
        jQuery('#vcht_chatPanel').removeClass('vcht_hidden');
        vcht_onResize();
        vcht_updateFieldsInfos();

        if (user.history && user.history.length > 0) {
            if (user.history[user.history.length - 1].receiverID == 0 && user.history[user.history.length - 1].type != 'close') {
                chkWritePanel = false;
                jQuery('#vcht_chatReqAnswerContainer').show();
                jQuery('#vcht_chatPanelWriteContainer').hide();
            } else {
                jQuery('#vcht_chatReqAnswerContainer').hide();
                jQuery('#vcht_chatPanelWriteContainer').show();
                jQuery('#vcht_chatPanelBtnsTb').delay(300).fadeIn();
            }

        } else {
            jQuery('#vcht_chatReqAnswerContainer').hide();
            if (typeof (user.currentOperator) != "undefined" && user.currentOperator > 0 && user.currentOperator != vcht_currentUser.id) {
                jQuery('#vcht_chatPanelWriteContainer').hide();
                chkWritePanel = false;
            } else {
                jQuery('#vcht_chatPanelWriteContainer').show();
                jQuery('#vcht_chatPanelBtnsTb').delay(300).fadeIn();

            }

        }
      //  setTimeout(function () {
            jQuery('#vcht_chatPanel .vcht_scrollbar').mCustomScrollbar();
            vcht_writeHistory(user);
            setTimeout(function () {
                vcht_onResize();
            }, 250);


      //  }, 500);
    } else if (jQuery('#vcht_chatPanel').is('.vcht_minify')) {
        vcht_changeSizeChatPanel('default');
    }
    if (chkWritePanel) {
        setTimeout(function () {
            if (jQuery('#vcht_chatReqAnswerContainer').css('display') == 'none') {
                jQuery('#vcht_chatPanelWriteContainer').fadeIn();
                jQuery('#vcht_chatPanelBtnsTb').delay(300).fadeIn();
            } else {
                jQuery('#vcht_chatPanelWriteContainer').fadeOut();

            }
        }, 250);
    }
}
function vcht_getUserByID(userID) {
    var rep = false;
    jQuery.each(vcht_allUsers, function () {
        if (this.id == userID) {
            rep = this;
        }
    });
    return rep;
}
function vcht_getPendingUserByID(userID) {
    var rep = false;
    jQuery.each(vcht_pendingUsers, function () {
        if (this.id == userID) {
            rep = this;
        }
    });
    return rep;
}


function vcht_getUserIndexByID(userID) {
    var rep = -1;
    jQuery.each(vcht_allUsers, function (i) {
        if (this.id == userID) {
            rep = i;
        }
    });
    return rep;
}

function vcht_sendMessage() {
    var content = jQuery('#vcht_chatPanelMsgArea').val();
    jQuery('#vcht_chatPanelMsgArea').removeClass('vcht_error');
    if (content.length > 0 || vcht_currentFiles.length > 0 || vcht_currentSelection != '') {
        jQuery('#vcht_chatPanelMsgArea').val('');
        jQuery('#vcht_chatPanelMsgArea').select();
        content = content.replace(/\n/g, '<br/>');
        var msg = {id: 0,
            content: content,
            receiverID: vcht_currentInterlocutor.id,
            senderID: vcht_currentUser.id,
            files: vcht_currentFiles,
            page: vcht_currentPage,
            domElement: vcht_currentSelection
        };
        var receiver = vcht_getUserByID(msg.receiverID);
        if (!receiver) {
        } else {
            receiver.history.push(msg);
        }
        var msgItem = vcht_writeMsg(msg);
        vcht_uploadFilesDropzone.removeAllFiles();
        jQuery('#vcht_chatPanelFilesBtn').removeClass('vcht_active');
        jQuery('#vcht_chatPanelShowElementBtn').removeClass('vcht_active');
        vcht_currentInterlocutor.isChatting = true;
        vcht_currentInterlocutor.currentOperator = vcht_currentUser.id;
        vcht_createPanelUser(vcht_currentInterlocutor);
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'vcht_sendMessage',
                receiverID: vcht_currentInterlocutor.id,
                content: content,
                files: JSON.stringify(vcht_currentFiles),
                page: vcht_currentPage,
                domElement: vcht_currentSelection
            },
            success: function (msgID) {
                msgID = parseInt(msgID.trim());
                msgItem.attr('data-id', msgID);
            }
        });
        vcht_currentFiles = new Array();
        vcht_currentSelection = '';
        vcht_currentPage = '';
    } else {
        jQuery('#vcht_chatPanelMsgArea').addClass('vcht_error');
    }
}

function vcht_addToHistory(msg) {
    var sender = vcht_getUserByID(msg.senderID);
    if (sender != false) {
        if (msg.senderID == vcht_currentUser.id) {
            var receiver = vcht_getUserByID(msg.receiverID);
            if (!receiver) {
            } else {
                receiver.history.push(msg);
            }
        } else {
            sender.history.push(msg);
        }
        if (sender.id == vcht_currentInterlocutor.id) {
            vcht_writeMsg(msg);
        }
    }

}
function vcht_writeMsg(msg) {
    var $item = false;
    var sender = vcht_getUserByID(msg.senderID);
    var receiver = vcht_getUserByID(msg.receiverID);
    var uploadFolder = receiver.uploadFolderName;
    if (!sender.isOperator) {
        uploadFolder = sender.uploadFolderName;
    }
    if (msg.content == undefined) {
        msg.content = "";
    }
    if (jQuery('#vcht_chatPanelHistory .vcht_message[data-id="' + msg.id + '"]').length == 0 || msg.id == 0) {

        var operatorClass = '';
        if (msg.type == 'close') {
            msg.content = vcht_data.texts['[username] stopped the chat'];
            msg.content = msg.content.replace('[username]', sender.username);
            sender.isChatting = false;
        } else if (msg.type == 'receiveTransfer') {
            msg.content = vcht_data.texts['[username1] tranfers the chat to [username2]'];
            msg.content = msg.content.replace('[username1]', msg.transferUsername);
            msg.content = msg.content.replace('[username2]', vcht_currentUser.username);

        } else {
            sender.isChatting = true;
            if (sender.id == vcht_currentInterlocutor.id && msg.receiverID > 0) {
                jQuery('#vcht_chatPanelWriteContainer').fadeIn();
            }
        }
        if (sender.id != vcht_currentInterlocutor.id) {
            operatorClass = 'vcht_operatorMsg';
        }

        $item = jQuery('<div class="vcht_message ' + operatorClass + '" data-id="' + msg.id + '"></div>');
        $item.append('<img class="vcht_avatar" src="' + sender.avatarImg + '" />');
        var now = new Date();
        var $bubbleCt = jQuery('<div class="vcht_bubbleContainer"></div>');
        $bubbleCt.append('<div class="vcht_infos">' + vcht_addZero(now.getHours()) + ':' + vcht_addZero(now.getMinutes()) + ', ' + sender.username + ' :</div>');
        $bubbleCt.append('<div class="vcht_bubble">' + msg.content + '</div>');
        if (msg.files && msg.files.length > 0) {
            var $files = jQuery('<div class="vcht_messageFiles"></div>');

            jQuery.each(msg.files, function () {
                var iconClass = 'fa-file-text-o';
                var ext = this.substr(this.lastIndexOf('.') + 1).toLowerCase();
                if (ext == 'jpg' || ext == 'png' || ext == 'gif' || ext == 'tif' || ext == 'svg' || ext == 'bmp' || ext == 'jpeg') {
                    iconClass = 'fa-file-image-o';
                } else if (ext == 'doc' || ext == 'docx') {
                    iconClass = 'fa-file-word-o';
                } else if (ext == 'zip' || ext == 'rar' || ext == '7z' || ext == 'gzip') {
                    iconClass = 'fa-file-archive-o';
                } else if (ext == 'pdf') {
                    iconClass = 'fa-file-pdf-o';
                } else if (ext == 'mp4' || ext == '3gp' || ext == 'avi' || ext == 'mpeg' || ext == 'mov' || ext == 'webm') {
                    iconClass = 'fa-file-video-o';
                } else if (ext == 'ppt') {
                    iconClass = 'fa-file-powerpoint-o';
                }
                $files.append('<div class="vcht_messageFile"><a href="' + vcht_data.uploadsUrl + uploadFolder + '/' + this + '" target="_blank"><span class="fa ' + iconClass + '"></span>' + this + '</a></div>');
            });
            $bubbleCt.find('.vcht_bubble').append($files);
        }
        if (msg.domElement && msg.domElement != '') {
            $bubbleCt.find('.vcht_bubble').append('<a href="javascript:" onclick="vcht_clickShownElement(this);" class="vcht_elementShown" data-domelement="' + msg.domElement + '" data-page="' + msg.page + '"><span class="fa fa-eye"></span>' + vcht_data.texts['Shows an element of the website'] + '</a>');
        }

        $item.append($bubbleCt);
        jQuery('#vcht_chatPanelHistory .mCSB_container').append($item);
        $item.stop().slideDown();
        setTimeout(function () {
            jQuery('#vcht_chatPanelHistory').mCustomScrollbar("scrollTo", "bottom");
        }, 400);

        if (msg.page != '' && msg.page != 'undefined') {

            if (msg.domElement != '') {
                vcht_lastShowTime = Date.now();
            }
            vcht_shownElement = msg.domElement;
            // jQuery('#vcht_loaderFrame').fadeIn();
            vcht_showUrl(msg.page);
        } else if (Date.now() - vcht_lastShowTime > 20 * 1000) {

            vcht_shownElement = '';
            if (vcht_currentInterlocutor.isOperator) {
                jQuery('#vcht_webFrame,#vcht_loaderFrame').fadeOut();
            } else {
                vcht_showUrl(vcht_currentInterlocutor.currentPage);
            }
        }


    }
    return $item;
}
function vcht_clickShownElement(link) {
    var domElement = jQuery(link).attr('data-domelement');
    var page = jQuery(link).attr('data-page');
    vcht_shownElement = domElement;
    vcht_showUrl(page);

}
function vcht_writeHistory(user) {
    jQuery('#vcht_chatPanelHistory .mCSB_container').html('');
    jQuery.each(user.history, function () {
        var msg = this;
        vcht_writeMsg(msg);
    });
}
function vcht_addZero(i) {
    if (i < 10) {
        i = "0" + i;
    }
    return i;
}


function vcht_checkNewMessages() {
    if (vcht_isConnected) {

        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'vcht_operatorGetNewMessages'
            },
            success: function (rep) {
                rep = rep.trim();
                rep = jQuery.parseJSON(rep);
                vcht_checkMsgTimer = setTimeout(vcht_checkNewMessages, vcht_data.ajaxCheckDelay * 1000);

                jQuery.each(rep, function () {
                    this.files = jQuery.parseJSON(this.files);

                    var infosUsers = new Array();
                    if (!vcht_getUserByID(this.senderID)) {
                        infosUsers.push(this.senderID);
                        var pendingUser = vcht_getPendingUserByID(this.senderID);
                        if (pendingUser == false) {
                            pendingUser = {
                                id: this.senderID,
                                isChatting: true,
                                history: new Array()
                            };
                            vcht_pendingUsers.push(pendingUser);
                            vcht_getUsersInfos(infosUsers);
                        }
                        pendingUser.history.push(this);
                    } else {
                        if (this.type == 'receiveTransfer') {
                            this.receiverID = vcht_currentUser.id;
                            vcht_getUserByID(this.senderID).currentOperator = vcht_currentUser.id;

                        }
                        if (this.senderID != vcht_currentUser.id && this.receiverID == 0) {
                            vcht_getUserByID(this.senderID).isChatting = true;
                        }

                        vcht_createPanelUser(vcht_getUserByID(this.senderID), null);

                        vcht_addToHistory(this);
                        if (this.type != 'close') {

                            if (this.senderID != vcht_currentUser.id && (this.senderID != vcht_currentInterlocutor.id || jQuery('#vcht_chatPanel').is('.vcht_minify'))) {
                                if (jQuery('#vcht_audioMsg').data('enable') != 'false') {
                                    jQuery('#vcht_audioMsg').get(0).play();
                                }
                                jQuery('.vcht_userListItem[data-id="' + this.senderID + '"] .vcht_alertPoint').css({display: 'inline-block'});
                                vcht_createPanelUser(vcht_getUserByID(this.senderID), null);

                            }
                            if (this.senderID == vcht_currentInterlocutor.id && jQuery('#vcht_chatPanel').is('.vcht_minify')) {
                                if (jQuery('#vcht_audioMsg').data('enable') != 'false') {
                                    jQuery('#vcht_audioMsg').get(0).play();
                                }
                                jQuery('#vcht_chatPanelHeader .vcht_alertPoint').css({display: 'inline-block'});
                            }
                        } else if (this.senderID == vcht_currentInterlocutor.id) {

                        }
                        if (this.type == "message" && this.senderID != vcht_currentUser.id && vcht_currentInterlocutor == false && this.receiverID > 0) {
                            vcht_openChatPanel(this.senderID);
                        }
                    }
                });
            }
        });
    }
}
function vcht_openWinFilesUpload() {
    jQuery('#vcht_winFilesUpload').modal('show');
}
function vcht_validFilesUpload() {
    vcht_currentFiles = new Array();
    jQuery('#vcht_uploadFilesField .dz-preview.dz-success').each(function () {
        var file = jQuery(this).attr('data-file');
        file = file.replace(/ /g, '_');
        vcht_currentFiles.push(file);
    });
    if (vcht_currentFiles.length > 0) {
        jQuery('#vcht_chatPanelFilesBtn').addClass('vcht_active');
        jQuery('#vcht_chatPanelFilesBtn .badge').html(vcht_currentFiles.length);
    } else {
        jQuery('#vcht_chatPanelFilesBtn').removeClass('vcht_active');
        jQuery('#vcht_chatPanelFilesBtn .badge').html(0);
    }
    setTimeout(function () {
        jQuery('#vcht_chatPanelHistory').mCustomScrollbar("scrollTo", "bottom", {
            scrollInertia: 0,
            timeout: 500
        });
    }, 300);
}
function vcht_showUrl(url) {
    if (typeof (url) != "undefined" && url != '') {
        if (url.indexOf('https:') == 0 && document.location.href.indexOf('https:') == -1) {
            url = 'http:' + url.substr(6, url.length);
        } else if (url.indexOf('http:') == 0 && document.location.href.indexOf('http:') == -1) {
            url = 'https:' + url.substr(5, url.length);
        }
        if (jQuery('#vcht_webFrame').attr('src') != url) {
            jQuery('#vcht_loaderFrame').fadeIn();
            jQuery('#vcht_webFrame').attr('src', url);
        }
        jQuery('#vcht_webFrame').fadeIn();
    }
}
function vcht_startShowElement() {
    vcht_modeSelection = true;
    jQuery('#vcht_chatPanel').stop().stop().slideUp().addClass('vcht_hidden');
    jQuery('#vcht_adminSettingsPanel,#vcht_cannedMsgsPanel,#vcht_userHistoryPanel,#vcht_fullHistoryPanel').fadeOut();
    setTimeout(function () {
        jQuery('#vcht_loaderFrame').fadeIn();
    }, 380);
    jQuery('#vcht_webFrame').attr('src', vcht_data.websiteUrl);
    jQuery('#vcht_selectionInfoPanel [data-step]').hide();
    jQuery('#vcht_selectionInfoPanel [data-step="0"]').show();
    setTimeout(vcht_onResize, 350);
}

function vcht_selectAnElement() {
    jQuery('#vcht_selectionInfoPanel').stop().stop().slideUp();
    jQuery('#vcht_webFrame').get(0).contentWindow.vcht_startSelection();
    setTimeout(function () {
        jQuery('#vcht_selectionInfoPanel [data-step]').hide();
        jQuery('#vcht_selectionInfoPanel [data-step="1"]').show();
        jQuery('#vcht_selectionInfoPanel').stop().stop().slideDown();
    }, 400);
}
function vcht_selectDomElement(link) {
    vcht_currentPage = jQuery('#vcht_webFrame').get(0).contentWindow.location.href;
    vcht_currentSelection = vcht_getPath(link);
    jQuery('#vcht_selectionInfoPanel').stop().stop().slideUp();
    setTimeout(function () {
        jQuery('#vcht_selectionInfoPanel [data-step]').hide();
        jQuery('#vcht_selectionInfoPanel [data-step="2"]').show();
        jQuery('#vcht_selectionInfoPanel').stop().stop().slideDown();
    }, 400);
}
function vcht_confirmSelectElement() {
    vcht_modeSelection = false;
    jQuery('#vcht_chatPanel').stop().stop().slideDown().removeClass('vcht_hidden');
    jQuery('#vcht_selectionInfoPanel').stop().stop().slideUp();
    if (vcht_currentSelection != '') {
        jQuery('#vcht_chatPanelShowElementBtn').addClass('vcht_active');
    }
    if (vcht_currentInterlocutor.isOperator) {
        jQuery('#vcht_webFrame,#vcht_loaderFrame').stop().fadeOut();
    } else {
        vcht_showUrl(vcht_currentInterlocutor.currentPage);
    }
    setTimeout(vcht_onResize, 350);
}

function vcht_cancelSelectElement() {
    vcht_modeSelection = false;
    jQuery('#vcht_chatPanel').stop().stop().slideDown().removeClass('vcht_hidden');
    vcht_currentSelection = '';
    vcht_currentPage = '';
    jQuery('#vcht_selectionInfoPanel').stop().stop().slideUp();
    jQuery('#vcht_chatPanelShowElementBtn').removeClass('vcht_active');

    if (vcht_currentInterlocutor.isOperator) {
        jQuery('#vcht_webFrame,#vcht_loaderFrame').stop().fadeOut();
    } else {
        vcht_showUrl(vcht_currentInterlocutor.currentPage);
    }
    setTimeout(vcht_onResize, 350);
}

function vcht_getPath(el) {
    var path = '';
    if (jQuery(el).length > 0 && typeof (jQuery(el).prop('tagName')) != "undefined") {
        if (!jQuery(el).attr('id') || jQuery(el).attr('id').substr(0, 8) == 'ultimate') {
            path = '>' + jQuery(el).prop('tagName') + ':nth-child(' + (jQuery(el).index() + 1) + ')' + path;
            path = vcht_getPath(jQuery(el).parent()) + path;
        } else {
            path += '#' + jQuery(el).attr('id');
        }
    }
    return path;
}
function vcht_saveSettings() {
    var formData = {};
    jQuery('#vcht_adminSettingsPanel').find('input,select,textarea').each(function () {
        if (jQuery(this).closest('#vcht_tabSettingsRoles').length == 0 &&
                jQuery(this).closest('#vcht_tabSettingsTexts').length == 0) {
            if (!jQuery(this).is('[data-toggle="switch"]')) {
                eval('formData.vcht_' + jQuery(this).attr('name') + ' = jQuery(this).val();');
            } else {
                var value = 0;
                if (jQuery(this).vcht_BootstrapSwitch('state') == true) {
                    value = 1;
                }
                eval('formData.vcht_' + jQuery(this).attr('name') + ' = value;');
            }
        }
    });
    formData.action = 'vcht_saveSettings';

    jQuery('#vcht_loader').fadeIn();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: formData,
        success: function (rep) {
            vcht_reloadPage();
        }
    });
}
function vcht_reloadPage() {
    var params = '';
    if (vcht_isConnected) {
        params += '&vcht_online=1';
    }
    if (jQuery('#vcht_adminSettingsPanel').css('display') == 'block') {
        if (jQuery('#vcht_tabSettingsGeneral').css('display') == 'block') {
            params += '&panel=vcht_tabSettingsGeneral';
        } else if (jQuery('#vcht_tabSettingsLogin').css('display') == 'block') {
            params += '&panel=vcht_tabSettingsLogin';
        } else if (jQuery('#vcht_tabSettingsContactForm').css('display') == 'block') {
            params += '&panel=vcht_tabSettingsContactForm';
        } else if (jQuery('#vcht_tabSettingsDesign').css('display') == 'block') {
            params += '&panel=vcht_tabSettingsDesign';
        }
    }
    document.location.href = 'admin.php?page=vcht-console' + params;
}
function vcht_stopCurrentChat() {
    if (vcht_currentInterlocutor) {

        vcht_currentInterlocutor.isChatting = false;
        if (jQuery('#vcht_chatReqAnswerContainer').css('display') == 'none' && vcht_currentInterlocutor.currentOperator == vcht_currentUser.id) {
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'vcht_closeChat',
                    receiverID: vcht_currentInterlocutor.id
                }
            });
        }
        if (!vcht_currentInterlocutor.isOperator) {
            vcht_currentInterlocutor.isChatting = false;
        } else {
        }
        vcht_currentInterlocutor.currentOperator = 0;
        vcht_createPanelUser(vcht_currentInterlocutor);

        if (jQuery('#vcht_usersListPanel [data-id="' + vcht_currentInterlocutor.id + '"]').closest('#vcht_currentChatsList').length > 0) {
            jQuery('#vcht_usersListPanel [data-id="' + vcht_currentInterlocutor.id + '"]').detach().appendTo('#vcht_onlineVisitorsList .vcht_panelBody');
        }
        vcht_updateNoUsersPanel();
    }
    vcht_currentInterlocutor = false;
    jQuery('#vcht_chatPanel').addClass('vcht_hidden');
    vcht_changeSizeChatPanel('minify');
    jQuery('#vcht_webFrame,#vcht_loaderFrame').fadeOut();
}
function vcht_openFullHistoryPanel() {
    jQuery('#vcht_loader').fadeIn();
    jQuery('#vcht_adminSettingsPanel,#vcht_cannedMsgsPanel,#vcht_userHistoryPanel').fadeOut();
    vcht_changeSizeChatPanel('minify');
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_getFullHistory'
        },
        success: function (rep) {
            rep = rep.trim();
            rep = jQuery.parseJSON(rep);

            if (jQuery('#vcht_fullHistoryTable').closest('.dataTables_wrapper').length > 0) {
                vcht_fullHistoryTable.destroy();
            }
            jQuery('#vcht_fullHistoryTable tbody').html('');
            jQuery.each(rep, function () {
                var tr = jQuery('<tr data-id="' + this.id + '"></tr>');
                tr.append('<td>' + this.username + '</td>');
                tr.append('<td>' + this.email + '</td>');
                if (this.isOperator == '1') {
                    tr.append('<td>' + vcht_data.texts['Yes'] + '</td>');
                } else {
                    tr.append('<td>' + vcht_data.texts['No'] + '</td>');
                }
                if (vcht_data.enableGeolocalization == '1') {
                    tr.append('<td>' + this.city + '</td>');
                    tr.append('<td>' + this.country + '</td>');
                }
                tr.append('<td>' + this.ip + '</td>');
                tr.append('<td>' + this.lastActivity + '</td>');
                var tdAction = jQuery('<td class="vcht_actionTh"></td>');
                tdAction.append('<a href="javascript:" onclick="vcht_openUserHistory(' + this.id + ');" class="btn btn-circle btn-primary"><span class="fa fa-eye"></span></a>');
                if (vcht_data.isAdmin == 1) {
                    tdAction.append('<a href="javascript:" onclick="vcht_removeUserLogs(' + this.id + ');" class="btn btn-circle btn-danger"><span class="fa fa-trash"></span></a>');
                }
                tr.append(tdAction);
                jQuery('#vcht_fullHistoryTable tbody').append(tr);
            });
            if (rep.length > 0) {
                jQuery('#vcht_btnDeleteAllLogs').removeAttr('disabled');
            } else {
                jQuery('#vcht_btnDeleteAllLogs').attr('disabled', 'disabled');
            }

            vcht_fullHistoryTable = jQuery('#vcht_fullHistoryTable').DataTable({
                'ordering': false,
                'language': {
                    'search': vcht_data.texts['search'],
                    'infoFiltered': vcht_data.texts['filteredFrom'],
                    'zeroRecords': vcht_data.texts['noRecords'],
                    'infoEmpty': vcht_data.texts['noRecords'],
                    'info': vcht_data.texts['showingPage'],
                    'lengthMenu': vcht_data.texts['display'] + ' _MENU_',
                    'paginate': {
                        'first': '<span class="glyphicon glyphicon-fast-backward"></span>',
                        'previous': '<span class="glyphicon glyphicon-step-backward"></span>',
                        'next': '<span class="glyphicon glyphicon-step-forward"></span>',
                        'last': '<span class="glyphicon glyphicon-fast-forward"></span>'
                    }
                }
            });

            jQuery('#vcht_fullHistoryPanel').fadeIn();
            jQuery('#vcht_loader').fadeOut();
        }
    });
}
function vcht_openUserHistory(userID) {
    jQuery('#vcht_loader').fadeIn();
    jQuery('#vcht_adminSettingsPanel,#vcht_cannedMsgsPanel').fadeOut();
    vcht_changeSizeChatPanel('minify');
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_getUserHistory',
            userID: userID
        },
        success: function (rep) {
            rep = rep.trim();
            rep = jQuery.parseJSON(rep);
            if (jQuery('#vcht_userHistoryTable').closest('.dataTables_wrapper').length > 0) {
                vcht_userHistoryTable.destroy();
            }
            jQuery('#vcht_userHistoryTable tbody').html('');

            jQuery.each(rep, function () {
                var msg = this;
                if (this.files) {
                    this.files = jQuery.parseJSON(this.files);
                }
                var tr = jQuery('<tr data-id="' + this.id + '"></tr>');
                tr.append('<td>' + this.date + '</td>');
                tr.append('<td>' + this.time + '</td>');
                if (vcht_data.enableGeolocalization == '1') {
                    tr.append('<td>' + this.city + '</td>');
                    tr.append('<td>' + this.country + '</td>');
                }
                tr.append('<td>' + this.ip + '</td>');
                tr.append('<td>' + this.email + '</td>');
                tr.append('<td>' + this.username + '</td>');

                var $content = jQuery('<div>' + this.content + '</div>');
                if (this.type == 'close') {
                    $content.html(vcht_data.texts['[username] stopped the chat'].replace('[username]', this.username));
                } else if (this.type == 'transfer') {
                    var ct = vcht_data.texts['[username1] tranfers the chat to [username2]'];
                    ct = ct.replace('[username1]', this.username);
                    ct = ct.replace('[username2]', this.transferUsername);
                    $content.html(ct);
                }

                if (this.files.length > 0) {
                    var $files = jQuery('<div class="vcht_messageFiles"></div>');

                    jQuery.each(this.files, function () {
                        var iconClass = 'fa-file-text-o';
                        var ext = this.substr(this.lastIndexOf('.') + 1).toLowerCase();
                        if (ext == 'jpg' || ext == 'png' || ext == 'gif' || ext == 'tif' || ext == 'svg' || ext == 'bmp' || ext == 'jpeg') {
                            iconClass = 'fa-file-image-o';
                        } else if (ext == 'doc' || ext == 'docx') {
                            iconClass = 'fa-file-word-o';
                        } else if (ext == 'zip' || ext == 'rar' || ext == '7z' || ext == 'gzip') {
                            iconClass = 'fa-file-archive-o';
                        } else if (ext == 'pdf') {
                            iconClass = 'fa-file-pdf-o';
                        } else if (ext == 'mp4' || ext == '3gp' || ext == 'avi' || ext == 'mpeg' || ext == 'mov' || ext == 'webm') {
                            iconClass = 'fa-file-video-o';
                        } else if (ext == 'ppt') {
                            iconClass = 'fa-file-powerpoint-o';
                        }
                        if (msg.uploadUrl == '[noFile]') {
                            $files.append('<div class="vcht_messageFile"><div><span class="fa ' + iconClass + '"></span>' + this + '</div></div>');

                        } else {
                            $files.append('<div class="vcht_messageFile"><a href="' + msg.uploadUrl + '/' + this + '" target="_blank"><span class="fa ' + iconClass + '"></span>' + this + '</a></div>');

                        }
                    });
                    $content.append($files);
                }
                if (this.domElement != '') {
                    $content.append('<a href="javascript:" onclick="vcht_clickShownElement(this);" class="vcht_elementShown" data-domelement="' + this.domElement + '" data-page="' + this.page + '"><span class="fa fa-eye"></span>' + vcht_data.texts['Shows an element of the website'] + '</a>');
                }

                tr.append('<td>' + $content.html() + '</td>');
                jQuery('#vcht_userHistoryTable tbody').append(tr);
            });

            vcht_userHistoryTable = jQuery('#vcht_userHistoryTable').DataTable({
                'ordering': false,
                'language': {
                    'search': vcht_data.texts['search'],
                    'infoFiltered': vcht_data.texts['filteredFrom'],
                    'zeroRecords': vcht_data.texts['noRecords'],
                    'infoEmpty': vcht_data.texts['noRecords'],
                    'info': vcht_data.texts['showingPage'],
                    'lengthMenu': vcht_data.texts['display'] + ' _MENU_',
                    'paginate': {
                        'first': '<span class="glyphicon glyphicon-fast-backward"></span>',
                        'previous': '<span class="glyphicon glyphicon-step-backward"></span>',
                        'next': '<span class="glyphicon glyphicon-step-forward"></span>',
                        'last': '<span class="glyphicon glyphicon-fast-forward"></span>'
                    }
                }
            });
            jQuery('#vcht_userHistoryPanel').fadeIn();
            jQuery('#vcht_loader').fadeOut();

        }
    });

}
function vcht_deleteAllLogs() {
    jQuery('#vcht_fullHistoryTable tbody').html('');
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_deleteAllLogs'
        },
        success: function (rep) {
            jQuery('#vcht_btnDeleteAllLogs').attr('disabled', 'disabled');
        }
    });
}
function vcht_loadCannedMsgs() {
    vcht_cannedMsgs = new Array();
    Mousetrap.reset();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_getCannedMsgs'
        },
        success: function (rep) {
            rep = rep.trim();
            rep = jQuery.parseJSON(rep);
            vcht_cannedMsgs = rep;

            jQuery('#vcht_pickCannedMsgSelect').html('');
            jQuery.each(vcht_cannedMsgs, function () {
                var sentence = this;
                if (sentence.shortcut != '') {
                    jQuery('#vcht_pickCannedMsgSelect').append('<option value="' + sentence.id + '">' + sentence.title + '</option>');
                    Mousetrap.bind(sentence.keyB + '+' + sentence.shortcut, function (e) {
                        vcht_useShortcode(sentence);
                        return false;
                    });
                }
            });
            if (vcht_cannedMsgs.length == 0) {
                jQuery('#vcht_chatPanelCannedMsgBtn').hide();
                vcht_onResize();
            }
        }
    });
}
function vcht_useShortcode(sentence) {
    if (vcht_currentInterlocutor != false) {
        var content = sentence.content;
        content = content.replace(/\[user\]/g, vcht_currentInterlocutor.username);
        content = content.replace(/\[operator\]/g, vcht_currentUser.username);
        content = content.replace(/\[siteurl\]/g, vcht_data.websiteUrl);
        jQuery('#vcht_chatPanelMsgArea').val(content);
        jQuery('#vcht_chatPanelMsgArea').focus();
    }
}
function vcht_showCannedMsgsPanel() {
    jQuery('#vcht_loader').fadeIn();
    jQuery('#vcht_adminSettingsPanel,#vcht_fullHistoryPanel,#vcht_userHistoryPanel').fadeOut();
    if (!jQuery('#vcgt_chatPanel').is('vcht_minify')) {
        vcht_changeSizeChatPanel('minify');
    }
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_getCannedMsgs'
        },
        success: function (rep) {
            rep = rep.trim();
            rep = jQuery.parseJSON(rep);
            vcht_cannedMsgs = rep;
            if (jQuery('#vcht_cannedMsgsTable').closest('.dataTables_wrapper').length > 0) {
                vcht_cannedMsgsTable.destroy();
            }
            jQuery('#vcht_cannedMsgsTable tbody').html('');

            jQuery.each(rep, function () {
                var tr = jQuery('<tr data-id="' + this.id + '"></tr>');
                tr.append('<td>' + this.title + '</td>');
                tr.append('<td>' + this.content + '</td>');
                tr.append('<td>' + this.keyB.toUpperCase() + ' + ' + this.shortcut.toUpperCase() + '</td>');
                var tdAction = jQuery('<td class="vcht_actionTh"></td>');
                tdAction.append('<a href="javascript:" onclick="vcht_editCannedMsg(' + this.id + ');" class="btn btn-circle btn-primary"><span class="fa fa-pencil"></span></a>');
                if (this.createdByAdmin == 0 || vcht_data.isAdmin == 1) {
                    tdAction.append('<a href="javascript:" onclick="vcht_removeCannedMsg(' + this.id + ');" class="btn btn-circle btn-danger"><span class="fa fa-trash"></span></a>');
                }
                tr.append(tdAction);
                jQuery('#vcht_cannedMsgsTable tbody').append(tr);
            });

            vcht_cannedMsgsTable = jQuery('#vcht_cannedMsgsTable').DataTable({
                'ordering': false,
                'language': {
                    'search': vcht_data.texts['search'],
                    'infoFiltered': vcht_data.texts['filteredFrom'],
                    'zeroRecords': vcht_data.texts['noRecords'],
                    'infoEmpty': vcht_data.texts['noRecords'],
                    'info': vcht_data.texts['showingPage'],
                    'lengthMenu': vcht_data.texts['display'] + ' _MENU_',
                    'paginate': {
                        'first': '<span class="glyphicon glyphicon-fast-backward"></span>',
                        'previous': '<span class="glyphicon glyphicon-step-backward"></span>',
                        'next': '<span class="glyphicon glyphicon-step-forward"></span>',
                        'last': '<span class="glyphicon glyphicon-fast-forward"></span>'
                    }
                }
            });
            jQuery('#vcht_cannedMsgsPanel').fadeIn();
            jQuery('#vcht_loader').fadeOut();

        }
    });
}

function vcht_editCannedMsg(id) {
    vcht_currentCannedMsgID = id;
    if (vcht_data.isAdmin == 1) {
        jQuery('#vcht_winCannedMessage [name="createdByAdmin"]').closest('.form-group').show();
    } else {
        jQuery('#vcht_winCannedMessage [name="createdByAdmin"]').closest('.form-group').hide();
    }
    if (id > 0) {
        var msg = vcht_getCannedMsgByID(id);
        if (msg != false) {
            jQuery('#vcht_winCannedMessage').find('input,select,textarea').each(function () {
                if (jQuery(this).is('[data-toggle="switch"]')) {
                    var value = false;
                    eval('if(msg.' + jQuery(this).attr('name') + ' == 1){jQuery(this).vcht_BootstrapSwitch(\'state\',true);} else {jQuery(this).vcht_BootstrapSwitch(\'state\',false);}');

                } else if (jQuery(this).is('pre')) {
                    eval('jQuery(this).html(msg.' + jQuery(this).attr('name') + ');');
                } else {
                    eval('jQuery(this).val(msg.' + jQuery(this).attr('name') + ');');
                }
            });
        }
    } else {
        jQuery('#vcht_winCannedMessage input,select,textarea').val('');
        jQuery('#vcht_winCannedMessage [name="keyB"]').val('shift');
    }
    jQuery('#vcht_winCannedMessage').modal('show');


}
function vcht_getCannedMsgByID(id) {
    var rep = false;
    jQuery.each(vcht_cannedMsgs, function () {
        if (this.id == id) {
            rep = this;
        }
    });
    return rep;
}

function vcht_saveCannedMessage() {
    var error = false;
    if (jQuery('#vcht_winCannedMessage [name="title"]').val() == '') {
        error = true;
        jQuery('#vcht_winCannedMessage [name="title"]').closest('.form-group').addClass('has-error');
    }
    if (jQuery('#vcht_winCannedMessage [name="content"]').val() == '') {
        error = true;
        jQuery('#vcht_winCannedMessage [name="content"]').closest('.form-group').addClass('has-error');
    }
    if (jQuery('#vcht_winCannedMessage [name="shortcut"]').val() == '') {
        error = true;
        jQuery('#vcht_winCannedMessage [name="shortcut"]').closest('.form-group').addClass('has-error');
    }

    if (!error) {
        jQuery('#vcht_loader').fadeIn();
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'vcht_saveCannedMessage',
                id: vcht_currentCannedMsgID,
                title: jQuery('#vcht_winCannedMessage [name="title"]').val(),
                content: jQuery('#vcht_winCannedMessage [name="content"]').val(),
                shortcut: jQuery('#vcht_winCannedMessage [name="shortcut"]').val(),
                createdByAdmin: jQuery('#vcht_winCannedMessage [name="createdByAdmin"]').is(':checked') ? 1 : 0,
                keyB: jQuery('#vcht_winCannedMessage [name="keyB"]').val()
            },
            success: function (rep) {
                jQuery('#vcht_winCannedMessage').modal('hide');
                vcht_showCannedMsgsPanel();
                vcht_loadCannedMsgs();
            }
        });
    }
}
function vcht_removeCannedMsg(id) {
    jQuery('#vcht_loader').fadeIn();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_removeCannedMsg',
            id: id
        },
        success: function () {
            vcht_showCannedMsgsPanel();
        }
    });
}

function vcht_editField(id, isLoginField) {
    vcht_isCurrentFieldLogin = isLoginField;
    vcht_currentFieldID = id;
    if (id > 0) {
        var msg = vcht_getFieldByID(id);
        if (msg != false) {
            jQuery('#vcht_editFieldPanel').find('input,select,textarea').each(function () {
                if (jQuery(this).is('[data-toggle="switch"]')) {
                    var value = false;
                    eval('if(msg.' + jQuery(this).attr('name') + ' == 1){jQuery(this).vcht_BootstrapSwitch(\'state\',true);} else {jQuery(this).vcht_BootstrapSwitch(\'state\',false);}');

                } else if (jQuery(this).is('pre')) {
                    eval('jQuery(this).html(msg.' + jQuery(this).attr('name') + ');');
                } else {
                    eval('jQuery(this).val(msg.' + jQuery(this).attr('name') + ');');
                }
            });
            jQuery('#vcht_itemOptionsValues tbody tr:not(.static)').remove();
            if(msg.optionsValues.length>0){
                var optionsValues = msg.optionsValues.split(',') ;
                jQuery.each(optionsValues,function(){
                    jQuery('#vcht_itemOptionsValues tbody').append('<tr><td>'+this+'</td><td><a href="javascript:" onclick="vcht_delOptionDropdown(this);" class="btn btn-circle btn-danger"><span class="fa fa-trash"></span></a></td></tr>');

                });
            }
        }
    } else {
        jQuery('#vcht_editFieldPanel').find('select').each(function () {
            jQuery(this).val(jQuery(this).find('option').first().attr('val'));
        });
        jQuery('#vcht_editFieldPanel').find('input,textarea').val('');
        jQuery('#vcht_editFieldPanel').find('[name="type"]').val('checkbox');
    }
    jQuery('#vcht_editFieldPanel').find('input.vcht_iconField').trigger('change');
    setTimeout(vcht_updateEditFieldPanel, 300);
    jQuery('#vcht_editFieldPanel').fadeIn();
}
function vcht_removeField(id) {
    jQuery('#vcht_loader').fadeIn();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_removeField',
            id: id
        },
        success: function () {
            jQuery('#vcht_loader').fadeOut();
            jQuery('#vcht_loginFieldsTable tr[data-id="' + id + '"],#vcht_contactFieldsTable tr[data-id="' + id + '"]').stop().slideUp();
            setTimeout(function () {
                jQuery('#vcht_loginFieldsTable tr[data-id="' + id + '"],#vcht_contactFieldsTable tr[data-id="' + id + '"]').remove();
            }, 350);
        }
    });
}
function vcht_delOptionDropdown(btn){
    jQuery(btn).closest('tr').remove();
}
function vcht_addOptionDropdown() {
    var value = jQuery('#vcht_itemOptionsValues #option_new_value').val();
    if(value.length>0){
        jQuery('#vcht_itemOptionsValues tbody').append('<tr><td>'+value+'</td><td><a href="javascript:" onclick="vcht_delOptionDropdown(this);" class="btn btn-circle btn-danger"><span class="fa fa-trash"></span></a></td></tr>');
        jQuery('#vcht_itemOptionsValues #option_new_value').val('');
    }
}
function vcht_updateEditFieldPanel() {
    if (jQuery('#vcht_editFieldPanel [name="type"]').val() == 'checkbox') {
        jQuery('#vcht_editFieldPanel [name="icon"]').closest('.form-group').slideUp();
        jQuery('#vcht_editFieldPanel [name="iconPosition"]').closest('.form-group').slideUp();
        jQuery('#vcht_editFieldPanel [name="validation"]').val('');
        jQuery('#vcht_editFieldPanel [name="validation"]').closest('.form-group').slideUp();
        jQuery('#vcht_editFieldPanel [name="placeholder"]').closest('.form-group').slideUp();
        jQuery('#vcht_editFieldPanel [name="defaultValue"]').closest('.form-group').slideUp();
        jQuery('#vcht_editFieldPanel [name="infoType"]').closest('.form-group').slideUp();
        jQuery('#vcht_itemOptionsValuesPanel').slideUp();
    } else if (jQuery('#vcht_editFieldPanel [name="type"]').val() == 'dropdown') {
        jQuery('#vcht_editFieldPanel [name="icon"]').closest('.form-group').slideUp();
        jQuery('#vcht_editFieldPanel [name="iconPosition"]').closest('.form-group').slideUp();
        jQuery('#vcht_editFieldPanel [name="validation"]').val('');
        jQuery('#vcht_editFieldPanel [name="validation"]').closest('.form-group').slideUp();
        jQuery('#vcht_editFieldPanel [name="placeholder"]').closest('.form-group').slideUp();
        jQuery('#vcht_editFieldPanel [name="defaultValue"]').closest('.form-group').slideUp();
        jQuery('#vcht_editFieldPanel [name="infoType"]').closest('.form-group').slideUp();
        jQuery('#vcht_itemOptionsValuesPanel').slideDown();
    } else if (jQuery('#vcht_editFieldPanel [name="type"]').val() == 'numberfield') {
        jQuery('#vcht_editFieldPanel [name="icon"]').closest('.form-group').slideDown();
        jQuery('#vcht_editFieldPanel [name="iconPosition"]').closest('.form-group').slideDown();
        jQuery('#vcht_editFieldPanel [name="validation"]').val('');
        jQuery('#vcht_editFieldPanel [name="validation"]').closest('.form-group').slideUp();
        jQuery('#vcht_editFieldPanel [name="placeholder"]').closest('.form-group').slideUp();
        jQuery('#vcht_editFieldPanel [name="defaultValue"]').closest('.form-group').slideUp();
        jQuery('#vcht_editFieldPanel [name="infoType"]').closest('.form-group').slideUp();
        jQuery('#vcht_itemOptionsValuesPanel').slideUp();
    } else if (jQuery('#vcht_editFieldPanel [name="type"]').val() == 'textfield') {
        jQuery('#vcht_editFieldPanel [name="icon"]').closest('.form-group').slideDown();
        jQuery('#vcht_editFieldPanel [name="iconPosition"]').closest('.form-group').slideDown();
        jQuery('#vcht_editFieldPanel [name="validation"]').closest('.form-group').slideDown();
        jQuery('#vcht_editFieldPanel [name="placeholder"]').closest('.form-group').slideDown();
        jQuery('#vcht_editFieldPanel [name="defaultValue"]').closest('.form-group').slideDown();
        jQuery('#vcht_editFieldPanel [name="infoType"]').closest('.form-group').slideDown();
        jQuery('#vcht_itemOptionsValuesPanel').slideUp();
    } else if (jQuery('#vcht_editFieldPanel [name="type"]').val() == 'textarea') {
        jQuery('#vcht_editFieldPanel [name="icon"]').closest('.form-group').slideUp();
        jQuery('#vcht_editFieldPanel [name="iconPosition"]').closest('.form-group').slideUp();
        jQuery('#vcht_editFieldPanel [name="validation"]').closest('.form-group').slideUp();
        jQuery('#vcht_editFieldPanel [name="placeholder"]').closest('.form-group').slideDown();
        jQuery('#vcht_editFieldPanel [name="defaultValue"]').closest('.form-group').slideDown();
        jQuery('#vcht_editFieldPanel [name="infoType"]').closest('.form-group').slideDown();
        jQuery('#vcht_itemOptionsValuesPanel').slideUp();
    }

    if (jQuery('#vcht_editFieldPanel [name="showInDetails"]').vcht_BootstrapSwitch('state')) {
        jQuery('#vcht_editFieldPanel [name="backendTitle"]').closest('.form-group').slideDown();
    } else {
        jQuery('#vcht_editFieldPanel [name="backendTitle"]').closest('.form-group').slideUp();
    }
    if (jQuery('#vcht_editFieldPanel [name="validation"]').val() == 'custom') {
        jQuery('#vcht_editFieldPanel [name="validationMin"]').closest('.form-group').slideDown();
        jQuery('#vcht_editFieldPanel [name="validationMax"]').closest('.form-group').slideDown();
        jQuery('#vcht_editFieldPanel [name="validationCaracts"]').closest('.form-group').slideDown();
    } else {
        jQuery('#vcht_editFieldPanel [name="validationMin"]').closest('.form-group').slideUp();
        jQuery('#vcht_editFieldPanel [name="validationMax"]').closest('.form-group').slideUp();
        jQuery('#vcht_editFieldPanel [name="validationCaracts"]').closest('.form-group').slideUp();
    }



}
function vcht_saveField() {
    var error = false;
    if (jQuery('#vcht_editFieldPanel [name="title"]').val() == '') {
        error = true;
        jQuery('#vcht_editFieldPanel [name="title"]').closest('.form-group').addClass('has-error');
    }
    if (jQuery('#vcht_editFieldPanel [name="showInDetails"]').vcht_BootstrapSwitch('state') && jQuery('#vcht_editFieldPanel [name="backendTitle"]').val() == '') {
        error = true;
        jQuery('#vcht_editFieldPanel [name="backendTitle"]').closest('.form-group').addClass('has-error');
    }

    if (!error) {

        var formData = {};
        jQuery('#vcht_editFieldPanel').find('input,select,textarea').each(function () {
            if (jQuery(this).closest('#vcht_itemOptionsValuesPanel').length == 0) {
                if (!jQuery(this).is('[data-toggle="switch"]')) {
                    eval('formData.vcht_' + jQuery(this).attr('name') + ' = jQuery(this).val();');
                } else {
                    var value = 0;
                    if (jQuery(this).vcht_BootstrapSwitch('state') == true) {
                        value = 1;
                    }
                    eval('formData.vcht_' + jQuery(this).attr('name') + ' = value;');
                }
            }
        });
        formData.action = 'vcht_saveField';
        formData.id = vcht_currentFieldID;
        formData.vcht_inLoginPanel = vcht_isCurrentFieldLogin;
        
        formData.vcht_optionsValues = '';
        jQuery('#vcht_itemOptionsValuesPanel tbody tr:not(.static)').each(function(){
            formData.vcht_optionsValues+= ','+jQuery(this).children('td:first-child').text();
        });
        if(formData.vcht_optionsValues.length>0){
            formData.vcht_optionsValues = formData.vcht_optionsValues.substr(1,formData.vcht_optionsValues.length-1);
        }

        jQuery('#vcht_loader').fadeIn();

        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: formData,
            success: function () {
                vcht_loadSettings();
                jQuery('#vcht_editFieldPanel').fadeOut();
            }
        });
    }
}
function vcht_getFieldByID(id) {
    var rep = false;
    jQuery.each(vcht_settings.fields, function () {
        if (this.id == id) {
            rep = this;
        }
    });
    return rep;
}
function vcht_saveAllowedRoles() {
    var roles = '';
    jQuery('#vcht_tabSettingsRoles table [data-toggle="switch"][name]').each(function () {
        if (jQuery(this).vcht_BootstrapSwitch('state')) {
            roles += jQuery(this).attr('name') + ',';
        }
    });
    if (roles.length > 0) {
        roles = roles.substr(0, roles.length - 1);
    }

    jQuery('#vcht_loader').fadeIn();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_saveAllowedRoles',
            roles: roles
        },
        success: function () {
            jQuery('#vcht_loader').fadeOut();
        }
    });
}

function vcht_saveTexts() {
    var formData = {};
    jQuery('#vcht_settingsTextsTable').find('input,textarea').each(function () {
        eval('formData.field_' + jQuery(this).closest('tr').attr('data-id') + ' = jQuery(this).val();');
    });
    formData.action = 'vcht_saveTexts';
    formData.usePoFile = 0;
    if (jQuery('#vcht_tabSettingsTexts [name="usePoFile"]').vcht_BootstrapSwitch('state') == true) {
        formData.usePoFile = 1;
    }

    jQuery('#vcht_loader').fadeIn();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: formData,
        success: function () {
            vcht_loadSettings();
        }
    });
}
function vcht_newChatRequest(msg) {
    if (jQuery('.vcht_chatRequestNotice[data-msgid="' + msg.id + '"]').length == 0) {
        var notice = jQuery('<div class="vcht_notice vcht_chatRequestNotice" data-msgid="' + msg.id + '"></div>');
        notice.append('<span class="vcht_noticeIcon fa fa-bell"></span>');
        var headerTxt = vcht_data.texts['New chat request from'] + ' ' + msg.username;
        notice.append('<p class="vcht_noticeHeader">' + headerTxt + '</p>');
        notice.append('<p class="vcht_noticeText">' + msg.content + '</p>');
        notice.append('<p class="vcht_noticeQuestion">' + vcht_data.texts['Do you want to reply ?'] + '</p>');
        notice.append('<div class="vcht_noticeBtns"></div>');
        notice.children('.vcht_noticeBtns').append('<a href="javascript:" class="btn btn-default">' + vcht_data.texts['Yes'] + '</a>');
        notice.children('.vcht_noticeBtns').append('<a href="javascript:" class="btn btn-warning">' + vcht_data.texts['No'] + '</a>');
        jQuery('#vcht_mainWrapper').append(notice);
    }
    vcht_repositionNotices();
}
function vcht_showNotice(text, duration, iconCls) {
    if (duration === null) {
        duration = 5;
    }
    if (iconCls === null) {
        iconCls = 'fa-info';
    }
    var notice = jQuery('<div class="vcht_notice"></div>');
    notice.append('<span class="vcht_noticeIcon fa ' + iconCls + '"></span>');
    notice.append('<p class="vcht_noticeText">' + text + '</p>');
    jQuery('#vcht_mainWrapper').append(notice);
    setTimeout(function () {
        notice.fadeOut();
        setTimeout(function () {
            notice.remove();
            vcht_repositionNotices();
        }, 251);
    }, duration * 1000);

    vcht_repositionNotices();
}
function vcht_repositionNotices() {
    var posY = jQuery('#vcht_mainHeader').height() + jQuery('#wpadminbar').height() + 18;
    jQuery('#vcht_mainWrapper').children('.vcht_notice').each(function () {
        jQuery(this).css({
            top: posY
        });
        posY += jQuery(this).height() + 18;
    });
}
function vcht_declineChat() {

    jQuery('#vcht_usersListPanel [data-id="' + vcht_currentInterlocutor.id + '"] .vcht_alertPoint').hide();
    vcht_currentInterlocutor.isChatting = false;
    vcht_currentInterlocutor.declined = true;
    vcht_stopCurrentChat();
}
function vcht_acceptChat() {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_acceptChat',
            userID: vcht_currentInterlocutor.id
        },
        success: function (rep) {
            if (rep == 'op') {
                vcht_showNotice(vcht_data.texts['Another operator has already answered']);
                vcht_stopCurrentChat();
            } else {
                vcht_currentInterlocutor.isChatting = true;
                jQuery('#vcht_chatReqAnswerContainer').hide();
                jQuery('#vcht_chatPanelWriteContainer').show();
                jQuery('#vcht_chatPanelBtnsTb').fadeIn();
                vcht_onResize();
                setTimeout(vcht_onResize, 250);
            }
        }
    });
}
function vcht_openWinPickCannedMsg() {
    jQuery('#vcht_winPickCannedMsg').fadeIn();
}
function vcht_pickCannedMsg() {
    var id = jQuery('#vcht_winPickCannedMsg select').val();
    var sentence = false;
    jQuery.each(vcht_cannedMsgs, function () {
        if (this.id == id) {
            sentence = this;
        }
    });
    if (sentence) {
        vcht_useShortcode(sentence)
    }
    jQuery('#vcht_winPickCannedMsg').fadeOut();
}
function vcht_showBubblePanel(panelID, targetID) {
    jQuery('#' + panelID).addClass('vcht_show');
    jQuery('#' + panelID).attr('data-target', targetID);
}
function vcht_hideBubblePanel(panelID) {
    jQuery('#' + panelID).removeClass('vcht_show');
}
function vcht_updateBubblePanels() {
    jQuery('.vcht_bubblePanel.vcht_show').each(function () {
        jQuery(this).css({
            top: jQuery('#' + jQuery(this).attr('data-target')).position().top + jQuery('#' + jQuery(this).attr('data-target')).outerHeight(),
            left: jQuery('#' + jQuery(this).attr('data-target')).position().left
        });
    });
}
function vcht_showWinUserAccount() {
    jQuery('#vcht_winUserAccount').modal('show');
}
function vcht_checkEmail(email) {
    if (email.indexOf("@") != "-1" && email.indexOf(".") != "-1" && email != "")
        return true;
    return false;
}
function vcht_saveUserAccount() {
    var error = false;
    var username = jQuery('#vcht_winUserAccount [name="username"]').val();
    var email = jQuery('#vcht_winUserAccount [name="email"]').val();
    var imgAvatar = jQuery('#vcht_winUserAccount [name="imgAvatar"]').val();
    jQuery('#vcht_winUserAccount [name="username"]').closest('.form-group').removeClass('has-error');
    jQuery('#vcht_winUserAccount [name="email"]').closest('.form-group').removeClass('has-error');

    if (username.length < 1) {
        error = true;
        jQuery('#vcht_winUserAccount [name="username"]').closest('.form-group').addClass('has-error');
    }
    if (!vcht_checkEmail(email)) {
        error = true;
        jQuery('#vcht_winUserAccount [name="email"]').closest('.form-group').addClass('has-error');
    }
    if (imgAvatar == "") {
        imgAvatar = vcht_data.assetsUrl + 'img/administrator-2-128.png';
    }
    if (!error) {
        jQuery('#vcht_loader').fadeIn();
        jQuery('#vcht_operatorHeaderUsername').html(username);
        jQuery('#vcht_operatorHeaderPic img').attr('src', imgAvatar);
        jQuery('#vcht_winUserAccount').modal('hide');

        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'vcht_saveUserAccount',
                username: username,
                email: email,
                imgAvatar: imgAvatar
            },
            success: function () {
                vcht_reloadPage();
            }
        });
    }
}
function vcht_loadFields() {

    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_getFields'
        },
        success: function (rep) {
            rep = rep.trim();
            rep = jQuery.parseJSON(rep);
            if (vcht_settings == false) {
                vcht_settings = {};
            }
            vcht_settings.fields = rep;
        }
    });
}
function vcht_removeUserLogs(userID) {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_removeUserLogs',
            userID: userID
        },
        success: function (rep) {
            vcht_openFullHistoryPanel();
        }
    });
}