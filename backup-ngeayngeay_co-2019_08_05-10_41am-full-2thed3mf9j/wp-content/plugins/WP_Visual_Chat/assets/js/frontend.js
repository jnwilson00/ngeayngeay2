// sent from php : vcht_url,vcht_position,vcht_pageUrl
var vcht_selectionMode = false;
var vcht_documentBody;
var vcht_avatarSel = false;
var nua = navigator.userAgent;
var vcht_elementShow = '';
var vcht_chatID = 0;
var vcht_userID = 0;
var vcht_isLogged = false;
var vcht_currentOperator = false;
var vcht_currentUser = false;
var vcht_currentFiles = new Array();
var vcht_uploadFilesDropzone;
var vcht_checkMsgTimer = false;
var vcht_shownElement = '';
var vcht_uploadFilesDropzone;
var vcht_geoDone = false;
var vcht_timerBounce = false;

jQuery(window).on('load', function () {
    vcht_documentBody = jQuery('html,body');

    jQuery.each(vcht_data.texts, function (i) {
        var text = this;
        text = text.replace(/\n/g, '<br/>');
        text = text.replace(/\\n/g, '<br/>');
        text = text.replace(/<br \/>/g, '<br/>');
        text = text.replace(/<br\/><br\/>/g, '<br/>');
        vcht_data.texts[i] = text;
    });

    if (!vcht_isIframe()) {
        if (vcht_elementShow != "" && vcht_elementShow.length > 0) {
            vcht_showElement(vcht_elementShow);
        }
    }
    jQuery('*').click(function (e) {
        if (vcht_selectionMode) {
            if (jQuery(this).children().length == 0 || jQuery(this).is('a') || jQuery(this).is('button') || jQuery(this).is('img') || jQuery(this).is('select')) {
                e.preventDefault();
                jQuery('.vcht_selectedDom').removeClass('vcht_selectedDom');
                jQuery(this).addClass('vcht_selectedDom');
                window.parent.vcht_selectDomElement(this);
                vcht_selectionMode = false;
            }

        }
    });
    if (!vcht_isIframe()) {
        if (vcht_data.enableChat == 1) {
            vcht_initChatPanel();
            vcht_initBounce();
            if (sessionStorage.getItem("vcht_geoSend") === null || sessionStorage.vcht_geoSend !== 'true') {
                vcht_geoSend();
            }
            if (sessionStorage.getItem("vcht_isChatting") !== null && sessionStorage.vcht_isChatting === 'true') {
                vcht_checkIfChatActive();

            } else {
            }
            if (sessionStorage.getItem("vcht_shownElement") !== null && sessionStorage.getItem("vcht_shownElement") != "") {
                vcht_showElement(sessionStorage.getItem("vcht_shownElement"), false);
                sessionStorage.vcht_shownElement = "";
                vcht_minify();
            }
            if (vcht_data.vtrk == 1) {
                vcht_vtrkTimer();
            }
            vcht_checkMsgTimer = setInterval(vcht_checkNewMessages, vcht_data.ajaxCheckDelay * 1000)
        }
        jQuery(window).resize(vcht_onResize);
        vcht_onResize();
    }
});
function vcht_geoSend() {
    if (vcht_data.enableGeolocalization == 1) {
        jQuery.ajax({
            type: 'post',
            url: vcht_data.ajaxurl,
            data: {
                action: 'client_geolocalize'
            },
            success: function (rep) {
                rep = rep.trim();
                if (rep == '1') {
                    sessionStorage.vcht_geoSend = true;
                }
            }
        });
    }
}
function vcht_checkIfChatActive() {
    jQuery.ajax({
        type: 'post',
        url: vcht_data.ajaxurl,
        data: {
            action: 'client_checkChatActive'
        },
        success: function (rep) {
            if (rep == 1) {
                vcht_expand();
            } else {
                sessionStorage.vcht_isChatting = false;

            }
        }
    });
}
function vcht_vtrkTimer() {
    if (!jQuery('#vcht_chatPanel').is('.vcht_hiddenPanel') && (sessionStorage.getItem("vcht_isChatting") === null || sessionStorage.vcht_isChatting == 'false')) {
        jQuery.ajax({
            url: vcht_data.ajaxurl,
            type: 'post',
            data: {
                action: 'client_updateClient',
                url: document.location.href
            },
            success: function (rep) {
                if (sessionStorage.getItem("vcht_geoSend") === null || sessionStorage.vcht_geoSend !== 'true') {
                    vcht_geoSend();
                }
                setTimeout(vcht_vtrkTimer, vcht_data.trkDelay * 1000);
                if (rep == 'chat') {
                    if (!jQuery('#vcht_chatPanel').is('.vcht_hiddenPanel')) {
                        sessionStorage.vcht_isChatting = true;
                        vcht_startChat(null, true);
                        vcht_expand();

                        if (jQuery('#vcht_audioMsg').data('enable') != 'false') {
                            jQuery('#vcht_audioMsg').get(0).play();
                            if (jQuery('#vcht_chatPanel').is('.vcht_minify')) {
                                vcht_minify();
                            }
                        }
                    }
                }
            }
        });
    } else {
        setTimeout(vcht_vtrkTimer, vcht_data.trkDelay * 1000);
    }
}
function vcht_onResize() {
    jQuery('#vcht_chatPanel > div:not(#vcht_chatHeader)').css({
        height: jQuery('#vcht_chatPanel').height() - jQuery('#vcht_chatHeader').height()
    });
    jQuery('#vcht_writeBtnsCt').css({
        marginRight: 0 - jQuery('#vcht_writeBtnsCt').width()
    });
    jQuery('#vcht_writeMsgContainer').css({
        marginRight: jQuery('#vcht_writeBtnsCt').width()
    });
    jQuery('#vcht_history').css({
        height: jQuery('#vcht_chatCommPanel').innerHeight() - (jQuery('#vcht_writeMsgContainer').outerHeight() + 18)
    });
    jQuery('#vcht_uploadFilesField').css({
        height: jQuery('#vcht_uploadFilesPanel').height() - (parseInt(jQuery('#vcht_uploadFilesPanel').css('padding-top')) + jQuery('#vcht_uploadFilesBtnCt').height())
    });

}
function vcht_isIframe() {
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}
function vcht_startSelection() {
    vcht_selectionMode = true;
}
function vcht_showElement(el, avatarImg) {
    jQuery('.vcht_selectedDom').removeClass('vcht_selectedDom');
    if (jQuery(el).length > 0) {
        if (jQuery(window).width() <= 480) {
            vcht_minify();
        }

        if (jQuery('.vcht_avatarSel').length > 0) {
            vcht_avatarSel = jQuery('.vcht_avatarSel');
        } else {
            if (avatarImg) {
                vcht_avatarSel = jQuery('<div class="vcht_avatarSel" style="background-image: none;"><div class="vcht_avatarArrow"></div><img src="' + avatarImg + '" alt=""/></div>');
            } else {
                vcht_avatarSel = jQuery('<div class="vcht_avatarSel"><div class="vcht_avatarArrow"></div></div>');
            }
        }
        jQuery('body').append(vcht_avatarSel);
        jQuery(el).addClass('vcht_selectedDom');
        if (vcht_isAnyParentFixed(jQuery(el))) {
            if (jQuery(el).position().top - 140 < 0) {
                vcht_avatarSel.addClass('bottom');
                vcht_avatarSel.css({
                    top: jQuery(el).position().top + jQuery(el).outerHeight() + 70,
                    left: jQuery(el).position().left + jQuery(el).outerWidth() / 2
                });
            } else {
                vcht_avatarSel.removeClass('bottom');
                vcht_avatarSel.css({
                    top: jQuery(el).position().top - 70,
                    left: jQuery(el).position().left + jQuery(el).outerWidth() / 2
                });
            }
            jQuery(vcht_documentBody).animate({scrollTop: jQuery(el).position().top - 200}, 500);
        } else {
            if (jQuery(el).offset().top - 140 < 0) {
                vcht_avatarSel.addClass('bottom');
                vcht_avatarSel.css({
                    top: jQuery(el).offset().top + jQuery(el).outerHeight() + 70,
                    left: jQuery(el).offset().left + jQuery(el).outerWidth() / 2
                });
            } else {
                vcht_avatarSel.removeClass('bottom');
                vcht_avatarSel.css({
                    top: jQuery(el).offset().top - 70,
                    left: jQuery(el).offset().left + jQuery(el).outerWidth() / 2
                });
            }
            jQuery(vcht_documentBody).animate({scrollTop: jQuery(el).offset().top - 200}, 500);
        }
        vcht_avatarSel.fadeIn();
        if(jQuery('#vcht_chatPanel').is('.vcht_fullscreen')){
            vcht_fullscreen();
        }
    }
}
function vcht_stopShowElement() {
    jQuery('.vcht_selectedDom').removeClass('vcht_selectedDom');
    jQuery('.vcht_avatarSel').fadeOut();
}
function vcht_isAnyParentFixed($el, rep) {
    if (!rep) {
        var rep = false;
    }
    try {
        if ($el.parent().length > 0 && $el.parent().css('position') == "fixed") {
            rep = true;
        }
    } catch (e) {
    }
    if (!rep && $el.parent().length > 0) {
        rep = vcht_isAnyParentFixed($el.parent(), rep);
    }
    return rep;
}
function vcht_initChatPanel() {
    var soundEnabled = 'false';
    if (vcht_data.playSoundCustomer == '1') {
        soundEnabled = 'true';
    }
    jQuery('body').append('<audio id="vcht_audioMsg" controls data-enable="' + soundEnabled + '"><source src="' + vcht_data.assetsUrl + '/sound/message.ogg" type="audio/ogg"><source src="' + vcht_data.assetsUrl + '/sound/message.mp3" type="audio/mpeg"></audio>');

    var chatPanel = jQuery('<div id="vcht_chatPanel" class="vcht_bootstrap vcht_minify"></div>');
    jQuery('body').append(chatPanel);
    chatPanel.append('<div id="vcht_chatHeader"></div>');
    chatPanel.find('#vcht_chatHeader').append('<span class="vcht_title">' + vcht_data.texts['Need Help ?'] + '</span>');
    if (vcht_data.showCloseBtn == 1) {
        chatPanel.find('#vcht_chatHeader').append('<a href="javascript:" id="vcht_btnClose"  class="vcht_btn" onclick="vcht_closeChat();"><span class="fa fa-times"></span></a>');
    }
    if (vcht_data.showFullscreenBtn == 1) {
        chatPanel.find('#vcht_chatHeader').append('<a href="javascript:" id="vcht_btnFullScreen" class="vcht_btn" onclick="vcht_fullscreen();"><span class="fa fa-window-maximize"></span></a>');
    }
    if (vcht_data.showMinifyBtn == 1) {
        chatPanel.find('#vcht_chatHeader').append('<a href="javascript:" id="vcht_btnMinify" class="vcht_btn" onclick="vcht_minify();"><span class="fa fa-window-minimize"></span></a>');
    }

    jQuery('#vcht_chatHeader').click(function () {
        if (jQuery(this).find('a.vcht_btn:hover').length == 0) {
            vcht_minify();
        }
    });

    chatPanel.append('<div id="vcht_chatContactForm" class="vcht_scrollbar"></div>');
    if (vcht_data.contactFormIcon != '') {
        jQuery('#vcht_chatContactForm').append('<div class="vcht_mainIcon"><span class="fa ' + vcht_data.contactFormIcon + '"></span></div>');
    }
    jQuery('#vcht_chatContactForm').append('<p>' + vcht_data.texts['Sorry, there is currently no operator online. Feel free to contact us by using the form below.'] + '</p>');

    if (vcht_data.enableContactForm == '1') {
        jQuery.each(vcht_data.contactFields, function () {
            var fieldCt = jQuery('<div class="form-group" data-id="' + this.id + '"></div>');
            var iconStart = '';
            var iconEnd = '';
            if (this.icon != '') {
                if (this.iconPosition == '1') {
                    iconStart = '<div class="input-group">';
                    iconEnd = '<span class="input-group-addon" id="basic-addon1"><span class="fa ' + this.icon + '"></span></span></div>';
                } else {
                    iconStart = '<div class="input-group"><span class="input-group-addon" id="basic-addon1"><span class="fa ' + this.icon + '"></span></span>';
                    iconEnd = '</div>';
                }
            }
            fieldCt.append('<label>' + this.title + '</label>');
            if (this.type == 'checkbox') {
                fieldCt.append('<input type="checkbox" data-toggle="switch" />');
            } else if (this.type == 'textarea') {
                fieldCt.append('<textarea class="form-control" >' + this.defaultValue + '</textarea>');
            } else if (this.type == 'dropdown') {
                var options = this.optionsValues.split(',');
                var select = jQuery('<select class="form-control"  ></select>');
                jQuery.each(options, function () {
                    select.append('<option value="' + this + '">' + this + '</option>');
                });
                fieldCt.append(select);
            } else if (this.type == 'numberfield') {
                var max = '';
                if (this.valueMax > 0) {
                    max = 'max="' + this.valueMax + '"';
                }
                fieldCt.append(iconStart + '<input type="number" class="form-control" min="' + this.valueMin + '" ' + max + ' value="' + this.defaultValue + '" />' + iconEnd);
            } else {
                var inputType = 'text';
                var inputName = '';
                if (this.infoType == 'email') {
                    inputType = 'email';
                    inputName = 'name="email"';
                }
                fieldCt.append(iconStart + '<input type="' + inputType + '" ' + inputName + ' class="form-control" placeholder="' + this.placeholder + '"  value="' + this.defaultValue + '" />' + iconEnd);
            }
            jQuery('#vcht_chatContactForm').append(fieldCt);
        });
        if (vcht_data.contactFields.length > 0) {
            jQuery('#vcht_chatContactForm').append('<a href="javascript:" class="btn btn-primary" onclick="vcht_sendContactForm();"><span class="fa fa-paper-plane"></span>' + vcht_data.texts['Send this message'] + '</a>');
        }
        jQuery('#vcht_chatContactForm').append('<div id="vcht_contactAnswer">' + vcht_data.texts['Thank you.[n]Your message has been sent.[n]We will contact you soon.'] + '</div>');
    }
    chatPanel.append('<div id="vcht_chatLoginForm" class="vcht_scrollbar"></div>');
    if (vcht_data.loginFormIcon != '') {
        jQuery('#vcht_chatLoginForm').append('<div class="vcht_mainIcon"><span class="fa ' + vcht_data.loginFormIcon + '"></span></div>');
    }
    jQuery.each(vcht_data.loginFields, function () {
        var fieldCt = jQuery('<div class="form-group" data-id="' + this.id + '" data-isrequired="' + this.isRequired + '" data-validation="' + this.validation + '"></div>');
        fieldCt.append('<label>' + this.title + '</label>');
        var iconStart = '';
        var iconEnd = '';
        if (this.icon != '') {
            if (this.iconPosition == '1') {
                iconStart = '<div class="input-group">';
                iconEnd = '<span class="input-group-addon" id="basic-addon1"><span class="fa ' + this.icon + '"></span></span></div>';
            } else {
                iconStart = '<div class="input-group"><span class="input-group-addon" id="basic-addon1"><span class="fa ' + this.icon + '"></span></span>';
                iconEnd = '</div>';
            }
        }

        if (this.type == 'checkbox') {
            fieldCt.append('<input type="checkbox" data-toggle="switch" />');
        } else if (this.type == 'textarea') {
            fieldCt.append('<textarea class="form-control" >' + this.defaultValue + '</textarea>');
        } else if (this.type == 'dropdown') {
            var options = this.optionsValues.split(',');
            var select = jQuery(iconStart + '<select class="form-control"></select>' + iconEnd);
            jQuery.each(vcht_data.contactFields, function () {
                select.append('<option value="' + this + '">' + this + '</option>');
            });
            fieldCt.append(select);
        } else if (this.type == 'numberfield') {
            var max = '';
            if (this.valueMax > 0) {
                max = 'max="' + this.valueMax + '"';
            }
            fieldCt.append(iconStart + '<input type="number" class="form-control"  min="' + this.valueMin + '" ' + max + ' value="' + this.defaultValue + '" />' + iconEnd);
        } else {
            var inputType = 'text';
            var inputName = '';
            if (this.infoType == 'email') {
                inputType = 'email';
                inputName = 'name="email"';
            }
            fieldCt.append(iconStart + '<input type="' + inputType + '" ' + inputName + ' class="form-control" placeholder="' + this.placeholder + '"  value="' + this.defaultValue + '" />' + iconEnd);

        }
        jQuery('#vcht_chatLoginForm').append(fieldCt);
    });
    
    jQuery('.vcht_bootstrap [data-toggle="switch"]').vcht_BootstrapSwitch();
    jQuery('#vcht_chatLoginForm').append('<a href="javascript:" onclick="vcht_validLoginForm();" class="btn btn-primary"><span class="fa fa-check"></span>' + vcht_data.texts['Start'] + '</a>');

    var chatCommPanel = jQuery('<div id="vcht_chatCommPanel"></div>');
    chatCommPanel.append('<div id="vcht_history" class="vcht_scrollbar"></div>');
    chatCommPanel.append('<div id="vcht_writeMsgContainer"></div>');
    chatCommPanel.find('#vcht_writeMsgContainer').append('<div id="vcht_writeBtnsCt"></div>');
    chatCommPanel.find('#vcht_writeMsgContainer').append('<textarea id="vcht_chatPanelMsgArea" class="form-control"></textarea>');
    if (vcht_data.allowFilesFromCustomers == 1) {
        chatCommPanel.find('#vcht_writeBtnsCt').append('<a href="javascript:" id="vcht_btnUploadFiles" data-title="' + vcht_data.texts['Transfer some files'] + '" data-toggle="tooltip" onclick="vcht_openUploadPanel();" class="btn btn-default btn-circle"><span class="fa fa-paperclip"></span></a>');
    }
    chatCommPanel.find('#vcht_writeBtnsCt').append('<a href="javascript:" onclick="vcht_sendMessage();" data-title="' + vcht_data.texts['Send this message'] + '" data-toggle="tooltip" class="btn btn-primary btn-circle"><span class="fa fa-paper-plane"></span></a>');
    chatPanel.append(chatCommPanel);

    if (vcht_data.allowFilesFromCustomers == '1') {
        var uploadFilesPanel = jQuery('<div id="vcht_uploadFilesPanel"></div>');
        uploadFilesPanel.append('<div id="vcht_uploadFilesField" class="vcht_dropzone dropzone"></div>');
        uploadFilesPanel.append('<div id="vcht_uploadFilesBtnCt"><a href="javascript:" onclick="vcht_validFilesUpload();" class="btn btn-primary"><span class="fa fa-cloud-upload"></span>' + vcht_data.texts['Confirm'] + '</a></div>');
        chatPanel.append(uploadFilesPanel);
    }

    chatPanel.append('<div id="vcht_chatLoaderCt"><div id="vcht_chatLoader"><div class="vcht_spinner"><div class="vcht_double-bounce1"></div><div class="vcht_double-bounce2"></div></div></div></div>');

    jQuery('#vcht_chatPanel').addClass('vcht_minify');
    jQuery('#vcht_btnMinify span').removeClass('fa-window-minimize').addClass('fa-window-restore');
    jQuery('#vcht_chatPanel').css({
        bottom: 0 - (jQuery('#vcht_chatPanel').outerHeight() - jQuery('#vcht_chatHeader').outerHeight())
    });
    setTimeout(function () {
        jQuery('#vcht_chatPanel').css({
            opacity: 1
        });
    }, 251);
    jQuery('#vcht_chatPanel .vcht_scrollbar').mCustomScrollbar();

    jQuery("#vcht_uploadFilesField").dropzone({
        url: vcht_data.ajaxurl,
        paramName: 'file',
        maxFilesize: vcht_data.filesMaxSize,
        maxFiles: vcht_data.filesMaxSize,
        addRemoveLinks: true,
        dictRemoveFile: '',
        dictCancelUpload: '',
        acceptedFiles: vcht_data.allowedFiles,
        dictDefaultMessage: vcht_data.texts['Drop files to upload here'],
        dictFileTooBig: vcht_data.texts['The selected file exceeds the authorized size'],
        dictInvalidFileType: vcht_data.texts['The selected type of file is not allowed'],
        dictMaxFilesExceeded: vcht_data.texts['You can not upload any more files'],
        init: function () {
            vcht_uploadFilesDropzone = Dropzone.forElement('#vcht_uploadFilesField');
            this.on('thumbnail', function (file, dataUrl) {
                var thumb = jQuery(file.previewElement);
                thumb.attr('data-file', file);
            });
            this.on('sending', function (file, xhr, formData) {
                formData.append('action', 'client_uploadFile');
                formData.append('receiverID', vcht_currentOperator.id);
                jQuery('#vcht_uploadFilesBtnCt >a.btn').fadeOut();
            }),
                    this.on("complete", function (file, xhr) {
                        if (jQuery('#vcht_uploadFilesField').find('.dz-preview:not(.dz-complete)').length == 0 || dropzone.find('.dz-preview').length == 0) {
                            jQuery('#vcht_uploadFilesBtnCt >a.btn').fadeIn();
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

    jQuery('#vcht_chatPanelMsgArea').keypress(function (e) {
        if (e.which == 13) {
            vcht_sendMessage();
            return false;
        }
    });
    jQuery('#vcht_chatLoginForm').find('input,textarea').keypress(function (e) {
        if (e.which == 13) {
            if (jQuery(this).closest('.form-group').index() < jQuery(this).closest('.form-group').parent().children.length - 2) {
                jQuery(this).closest('.form-group').next('.form-group').find('input,textarea').focus();
            } else {
                vcht_validLoginForm();
            }
            return false;
        }
    });

    jQuery('#vcht_chatPanel [data-toggle="tooltip"]').v_tooltip();
}
function vcht_getLoginFieldByID(id) {
    var rep = false;
    jQuery.each(vcht_data.loginFields, function () {
        if (this.id == id) {
            rep = this;
        }
    });
    return rep;
}
function vcht_getFieldByID(id) {
    var rep = false;
    jQuery.each(vcht_data.contactFields, function () {
        if (this.id == id) {
            rep = this;
        }
    });
    if (!rep) {
        jQuery.each(vcht_data.loginFields, function () {
            if (this.id == id) {
                rep = this;
            }
        });
    }
    return rep;
}
function vcht_checkFieldsError($panel) {
    var error = false;
    $panel.find('.form-group[data-isrequired="1"],.form-group[data-validation!=""]').each(function () {
        var field = vcht_getFieldByID(jQuery(this).attr('data-id'));
        if (field != false) {
            if (field.validation != "" && field.type == "textfield" && (jQuery(this).find('input').val() != "" || jQuery(this).attr('[data-isrequired="1"]'))) {
                if (field.validation == 'phone' && (jQuery(this).find('input.form-control').val().length < 5 || /^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$/i.test(jQuery(this).find('input.form-control').val()) == false)) {
                    error = true;
                    jQuery(this).addClass('has-error');
                } else if (field.validation == 'email' && jQuery(this).find('input').val().length > 0 && !vcht_checkEmail(jQuery(this).find('input.form-control').val())) {
                    error = true;
                    jQuery(this).addClass('has-error');
                } else if (field.validation == 'fill' && jQuery(this).find('input.form-control').val().trim().length < 1) {
                    error = true;
                    jQuery(this).addClass('has-error');
                } else if (field.validation == 'custom') {
                    if (parseInt(field.valueMin) > 0 && jQuery(this).find('input.form-control').val().length < parseInt(field.valueMin)) {
                        error = true;
                        jQuery(this).addClass('has-error');
                    }
                    if (parseInt(field.valueMax) > 0 && jQuery(this).find('input.form-control').val().length > parseInt(field.valueMax)) {
                        error = true;
                        jQuery(this).addClass('has-error');
                    }
                    if (jQuery(this).attr('data-validcar') != "") {
                        var field = jQuery(this);
                        if (field.validationCaracts.indexOf(',') > -1) {
                            var chars = field.validationCaracts.split(',');
                            jQuery.each(chars, function () {
                                if (field.val().indexOf(this) == -1) {
                                    error = true;
                                    jQuery(this).addClass('has-error');
                                }
                            });
                        } else {
                            if (field.val().indexOf(field.validationCaracts) == -1) {
                                error = true;
                                jQuery(this).addClass('has-error');
                            }
                        }
                    }
                }
            }
            if (field.isRequired == 1) {
                if (jQuery(this).find('input[type="checkbox"]').length > 0) {
                    if (!jQuery(this).find('input[type="checkbox"]').vcht_BootstrapSwitch('state')) {
                        error = true;
                        jQuery(this).addClass('has-error');
                    }
                } else {
                    if (jQuery(this).find('.form-control').val().trim().length < 1) {
                        error = true;
                        jQuery(this).addClass('has-error');
                    }
                }
            }

        }

    });
    return error;
}
function vcht_sendContactForm() {
    var error = false;
    var fields = new Array();
    jQuery('#vcht_chatContactForm .form-group').removeClass('has-error');
    error = vcht_checkFieldsError(jQuery('#vcht_chatContactForm'));
    jQuery('#vcht_chatContactForm .form-group').each(function () {
        var field = vcht_getFieldByID(jQuery(this).attr('data-id'));
        if (field.type == "checkbox") {
            fields.push({
                id: field.id,
                value: jQuery(this).find('input[type="checkbox"]').vcht_BootstrapSwitch('state')
            });
        } else {
            fields.push({
                id: field.id,
                value: jQuery(this).find('.form-control').val()
            });
        }
    });

    if (!error) {
        jQuery.ajax({
            url: vcht_data.ajaxurl,
            type: 'post',
            data: {
                action: 'client_sendContactForm',
                fields: fields
            },
            success: function (rep) {
            }
        });
        jQuery('#vcht_chatContactForm .form-group').removeClass('has-error');
        jQuery('#vcht_chatContactForm .form-group').each(function () {
            var field = vcht_getFieldByID(jQuery(this).attr('data-id'));
            if (field.type == 'checkbox') {
                jQuery(this).find('[type="checkbox"]').is(':checked');
            } else {
                jQuery(this).find('.form-control').val(field.defaultValue);
            }
        });
        jQuery('#vcht_chatContactForm .mCSB_container').children(':not(#vcht_contactAnswer):not(.vcht_mainIcon),a.btn').fadeOut();
        setTimeout(function () {
            jQuery('#vcht_chatContactForm').find('#vcht_contactAnswer').fadeIn();
        }, 250);

        setTimeout(function () {
            vcht_minify();
            setTimeout(function () {
                jQuery('#vcht_chatContactForm .mCSB_container').children(':not(#vcht_contactAnswer):not(.vcht_mainIcon),a.btn').fadeIn();
                jQuery('#vcht_chatContactForm').find('#vcht_contactAnswer').fadeOut();
            }, 250);
        }, 5000);
    }
}
function vcht_checkEmail(email) {
    if (email.indexOf("@") != "-1" && email.indexOf(".") != "-1" && email != "")
        return true;
    return false;
}
function vcht_onChatOpen() {
    if (vcht_chatID == 0 && !vcht_isLogged) {
        jQuery.ajax({
            url: vcht_data.ajaxurl,
            type: 'post',
            data: {
                action: 'client_checkOnlineOperator'
            },
            success: function (rep) {
                rep = rep.trim();
                jQuery('#vcht_chatLoaderCt').fadeOut();
                setTimeout(function () {
                    vcht_onResize();
                    jQuery('#vcht_history').mCustomScrollbar("scrollTo", "bottom");
                }, 255);
                if (rep == '1') {
                    if (sessionStorage.getItem("vcht_fields") === null) {
                        if (vcht_data.enableLoginPanel == 1) {
                            jQuery('#vcht_chatLoginForm').show();
                        } else {
                            jQuery('#vcht_chatLoginForm').hide();
                            vcht_startChat();
                        }
                    } else {
                        vcht_startChat();
                    }
                    jQuery('#vcht_chatContactForm').hide();
                } else {
                    jQuery('#vcht_chatLoginForm').hide();
                    jQuery('#vcht_chatContactForm').show();
                    if (jQuery('#vcht_chatPanel').is('.vcht_minify')) {
                        setTimeout(function () {
                            jQuery('#vcht_chatLoaderCt').fadeOut();
                        }, 251);
                    } else {
                        jQuery('#vcht_chatLoaderCt').fadeOut();
                    }
                }
            }
        });
    }
}
function  vcht_startChat(fields, noStartMsg) {
    vcht_isLogged = true;
    jQuery('#vcht_loader').fadeIn();

    jQuery.ajax({
        url: vcht_data.ajaxurl,
        type: 'post',
        data: {
            action: 'client_startChat',
            fields: fields
        },
        success: function (rep) {
            if (sessionStorage.getItem("vcht_geoSend") === null || sessionStorage.vcht_geoSend !== 'true') {
                vcht_geoSend();
            }
            rep = JSON.parse(rep);
            vcht_currentUser = rep;
            vcht_currentUser.history = new Array();

            setTimeout(function () {
                jQuery('#vcht_chatContactForm').hide();
                jQuery('#vcht_chatLoginForm').hide();
                jQuery('#vcht_chatCommPanel').show();
                vcht_onResize();
                jQuery('#vcht_btnUploadFiles').attr('disabled', 'disabled');
                if (!noStartMsg) {
                    vcht_writeStartMsg();
                }
                if (sessionStorage.getItem("vcht_isChatting") !== null && sessionStorage.vcht_isChatting) {
                    vcht_getLastHistory();

                }
            }, 250);
        }
    });
}
function vcht_validLoginForm() {
    if (vcht_data.enableLoginPanel == 1) {
        var error = false;
        if (sessionStorage.getItem("vcht_fields") === null) {
            var fields = new Array();
            jQuery('#vcht_chatLoginForm .form-group').removeClass('has-error');
            error = vcht_checkFieldsError(jQuery('#vcht_chatLoginForm'));
            jQuery('#vcht_chatLoginForm .form-group').each(function () {
                var field = vcht_getFieldByID(jQuery(this).attr('data-id'));
                if (field.type == "checkbox") {
                    fields.push({
                        id: field.id,
                        value: jQuery(this).find('input[type="checkbox"]').vcht_BootstrapSwitch('state')
                    });
                } else {
                    fields.push({
                        id: field.id,
                        value: jQuery(this).find('.form-control').val()
                    });
                }

            });
        } else {
            fields = JSON.parse(sessionStorage.vcht_fields);
        }
        if (!error) {
            sessionStorage.vcht_fields = JSON.stringify(fields);
            vcht_startChat(fields);
        }
    }
}
function vcht_minify() {
    jQuery('#vcht_btnFullScreen span').removeClass('fa-window-restore').addClass('fa-window-maximize');
    if (!jQuery('#vcht_chatPanel').is('.vcht_minify')) {
        vcht_stopShowElement();
        jQuery('#vcht_chatPanel').addClass('vcht_minify');
        var delay = 0;
        if (jQuery('#vcht_chatPanel').is('.vcht_fullscreen')) {
            delay = 251;
        }
        jQuery('#vcht_chatPanel').removeClass('vcht_fullscreen');
        jQuery('#vcht_btnMinify span').removeClass('fa-window-minimize').addClass('fa-window-restore');

        setTimeout(function () {
            jQuery('#vcht_chatPanel').css({
                bottom: 0 - (jQuery('#vcht_chatPanel').outerHeight() - jQuery('#vcht_chatHeader').outerHeight())
            });
        }, delay);
    } else {
        setTimeout(function () {
            jQuery('#vcht_btnMinify span').removeClass('fa-window-restore').addClass('fa-window-minimize');
        }, 251);
        vcht_expand();
    }
    setTimeout(vcht_onResize, 251);
}
function vcht_expand() {
    jQuery('#vcht_chatPanel').removeClass('vcht_minify');
    jQuery('#vcht_chatPanel').removeClass('vcht_fullscreen');
    jQuery('#vcht_chatPanel').css({
        bottom: 0
    });
    vcht_onChatOpen();
}
function vcht_fullscreen() {
    jQuery('#vcht_chatPanel').removeClass('vcht_minify');
    jQuery('#vcht_btnMinify span').removeClass('fa-window-restore').addClass('fa-window-minimize');
    if (!jQuery('#vcht_chatPanel').is('.vcht_fullscreen')) {
        jQuery('#vcht_chatPanel').addClass('vcht_fullscreen');
        jQuery('#vcht_btnFullScreen span').removeClass('fa-window-maximize').addClass('fa-window-restore');
    } else {
        jQuery('#vcht_chatPanel').removeClass('vcht_fullscreen');
        jQuery('#vcht_btnFullScreen span').removeClass('fa-window-restore').addClass('fa-window-maximize');
    }
    jQuery('#vcht_chatPanel').css({
        bottom: 0
    });
    vcht_onChatOpen();
    setTimeout(vcht_onResize, 251);
}
function vcht_closeChat(minify) {
    var delay = 0;
    if (!jQuery('#vcht_chatPanel').is('.vcht_minify')) {
        vcht_minify();
        delay = 251;
    }
    vcht_stopShowElement();
    setTimeout(function () {
        jQuery('#vcht_history .vcht_message:not(.vcht_startMsg)').remove();
    }, delay);
    if (!minify) {
        jQuery('#vcht_chatPanel').addClass('vcht_hiddenPanel');
        setTimeout(function () {
            jQuery('#vcht_chatPanel').css({
                bottom: 0 - (jQuery('#vcht_chatPanel').height() + 18)
            });
        }, delay);
    }
    if (vcht_data.enableLoginPanel == 1) {
        jQuery('#vcht_chatLoginForm').show();
        jQuery('#vcht_chatCommPanel').hide();
    }
    jQuery('#vcht_history .mCSB_container').html('');
    //clearTimeout(vcht_checkMsgTimer);
    sessionStorage.vcht_isChatting = false;
    var keepOnline = 0;
    if (minify) {
        keepOnline = 1;
    }
    var operatorID = 0;
    if (vcht_currentOperator) {
        operatorID = vcht_currentOperator.id;
    }
    jQuery.ajax({
        url: vcht_data.ajaxurl,
        type: 'post',
        data: {
            action: 'client_closeChat',
            keepOnline: keepOnline,
            receiverID: operatorID
        }
    });
}
function vcht_writeStartMsg() {
    if (jQuery('#vcht_history .vcht_mssage').length == 0) {
        $item = jQuery('<div class="vcht_message vcht_operatorMsg vcht_startMsg"></div>');
        $item.append('<img class="vcht_avatar" src="' + vcht_data.defaultImgAvatar + '" />');
        var now = new Date();
        var $bubbleCt = jQuery('<div class="vcht_bubbleContainer"></div>');
        $bubbleCt.append('<div class="vcht_infos">' + vcht_addZero(now.getHours()) + ':' + vcht_addZero(now.getMinutes()) + ' :</div>');
        $bubbleCt.append('<div class="vcht_bubble">' + vcht_data.texts['Hello! How can we help you ?'] + '</div>');

        $item.append($bubbleCt);
        jQuery('#vcht_history .mCSB_container').append($item);
        $item.stop().slideDown();
        setTimeout(function () {
            jQuery('#vcht_history').mCustomScrollbar("scrollTo", "bottom");
        }, 400);
    }
}
function vcht_addZero(i) {
    if (i < 10) {
        i = "0" + i;
    }
    return i;
}


function vcht_sendMessage() {
    var receiverID = -1;
    if (vcht_currentOperator != false) {
        receiverID = vcht_currentOperator.id;
    }
    vcht_stopShowElement();
    jQuery('#vcht_chatPanelMsgArea').removeClass('vcht_error');
    var content = jQuery('#vcht_chatPanelMsgArea').val();
    if (content.length > 0 || vcht_currentFiles.length > 0) {
        jQuery('#vcht_chatPanelMsgArea').val('');
        jQuery('#vcht_chatPanelMsgArea').select();
        var msg = {id: 0,
            content: content,
            receiverID: receiverID,
            senderID: vcht_currentUser.id,
            files: vcht_currentFiles,
            page: document.location.href,
            domElement: ''
        };
        var msgItem = vcht_writeMsg(msg);
        jQuery('#vcht_chatPanelFilesBtn').removeClass('vcht_active');
        jQuery('#vcht_chatPanelShowElementBtn').removeClass('vcht_active');

        if (vcht_data.allowFilesFromCustomers == 1) {
            vcht_uploadFilesDropzone.removeAllFiles();
        }
        jQuery.ajax({
            url: vcht_data.ajaxurl,
            type: 'post',
            data: {
                action: 'client_sendMessage',
                receiverID: receiverID,
                senderID: vcht_currentUser.id,
                content: content,
                files: JSON.stringify(vcht_currentFiles),
                page: document.location.href
            },
            success: function (msgID) {
                msgID = parseInt(msgID.trim());
                msgItem.attr('data-id', msgID);
            }
        });
        vcht_currentFiles = new Array();
        jQuery('#vcht_btnUploadFiles').removeClass('vcht_active');

        if (!vcht_checkMsgTimer) {
            // vcht_checkMsgTimer = setTimeout(vcht_checkNewMessages, vcht_data.ajaxCheckDelay * 1000)
        }
    } else {
        jQuery('#vcht_chatPanelMsgArea').addClass('vcht_error');
    }
}
function vcht_checkNewMessages() {
    if(sessionStorage.getItem("vcht_isChatting") !== null && sessionStorage.getItem("vcht_isChatting") == 'true'){
    jQuery.ajax({
        url: vcht_data.ajaxurl,
        type: 'post',
        data: {
            action: 'client_getNewMessages',
            page: document.location.href
        },
        success: function (rep) {
            if (sessionStorage.vcht_isChatting) {
                rep = rep.trim();
                rep = jQuery.parseJSON(rep);
                //  vcht_checkMsgTimer = setTimeout(vcht_checkNewMessages, vcht_data.ajaxCheckDelay * 1000);
                jQuery.each(rep, function (i) {
                    this.files = jQuery.parseJSON(this.files);
                    vcht_addToHistory(this);
                    
                    if(i == rep.length -1){
                        var msg = this;
                        if (msg.domElement != '') {
                            if (vcht_shownElement != msg.domElement && msg.senderID != vcht_currentUser.id) {
                                vcht_shownElement = msg.domElement;
                                vcht_showUrl(msg.page);
                            }
                        }
                    }
                    
                    
                });
            }

        }
    });
}
}


function vcht_writeMsg(msg, dontExecute) {
    var $item = false;
    var sender = vcht_currentUser;
    var operatorClass = '';

    if (msg.senderID != sender.id) {
        sender = vcht_currentOperator;
        operatorClass = 'vcht_operatorMsg';
    } else {
        sender.avatarImg = vcht_data.customerImgAvatar;
    }
    if (jQuery('#vcht_history .vcht_message[data-id="' + msg.id + '"]').length == 0 || msg.id == 0) {
        if (msg.type == 'close' && msg.senderID != vcht_currentUser.id) {
            msg.content = vcht_data.texts['[username] stopped the chat'];
            msg.content = msg.content.replace('[username]', sender.username);
            if (!dontExecute) {
                setTimeout(function () {
                    if (jQuery('#vcht_chatPanel').is('.vcht_minify')) {
                        vcht_minify();
                    }
                    vcht_closeChat(true);
                }, 3000);
            }
        }
        if (msg.type == 'transfer') {
            msg.content = vcht_data.texts['[username1] tranfers the chat to [username2]'];
            msg.content = msg.content.replace('[username1]', sender.username);
            msg.content = msg.content.replace('[username2]', msg.transferUsername);
            vcht_currentOperator.username = msg.transferUsername;
            vcht_currentOperator.id = msg.transferID;
            vcht_getOperatorInfos(msg.senderID);
        }
        $item = jQuery('<div class="vcht_message  ' + operatorClass + '" data-id="' + msg.id + '"></div>');
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
                $files.append('<div class="vcht_messageFile"><span class="fa ' + iconClass + '"></span>' + this + '</div>');
           
                
            });
            $bubbleCt.find('.vcht_bubble').append($files);
        }
        if (msg.domElement != '') {
            $bubbleCt.find('.vcht_bubble').append('<a href="javascript:" onclick="vcht_clickShownElement(this);" class="vcht_elementShown" data-domelement="' + msg.domElement + '" data-page="' + msg.page + '"><span class="fa fa-eye"></span>' + vcht_data.texts['Shows an element of the website'] + '</a>');

        }
        if ($bubbleCt.find('.vcht_bubble').text().length > 0) {
            $item.append($bubbleCt);
            jQuery('#vcht_history .mCSB_container').append($item);
            $item.stop().slideDown();
            setTimeout(function () {
                jQuery('#vcht_history').mCustomScrollbar("scrollTo", "bottom");
            }, 400);
        }
    }
    return $item;
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


function vcht_addToHistory(msg, showOwnMsgs, dontExecute) {
    var sender = vcht_currentOperator;
    vcht_currentUser.history.push(msg);
    if (msg.senderID == vcht_currentUser.id) {
        sender = vcht_currentUser;
        if (showOwnMsgs) {
            vcht_writeMsg(msg, dontExecute);
        }
    } else {
        if (!vcht_currentOperator) {
            sessionStorage.vcht_isChatting = true;
            vcht_currentOperator = {
                id: 0,
                history: new Array()
            };
            vcht_currentOperator.history.push(msg);
            vcht_getOperatorInfos(msg.senderID);
        } else {
            if (vcht_currentOperator.id == 0) {
                vcht_currentOperator.history.push(msg);
            } else {

                jQuery('#vcht_btnUploadFiles').removeAttr('disabled');
                vcht_writeMsg(msg, dontExecute);
            }
        }
    }

}

function vcht_getOperatorInfos(id) {
    jQuery.ajax({
        url: vcht_data.ajaxurl,
        type: 'post',
        data: {
            action: 'client_getOperatorInfos',
            operatorID: id
        },
        success: function (rep) {
            rep = JSON.parse(rep);
            vcht_currentOperator.id = rep.id;
            vcht_currentOperator.username = rep.username;
            vcht_currentOperator.avatarImg = rep.avatar;
            vcht_currentOperator.id = rep.id;
            jQuery('#vcht_btnUploadFiles').removeAttr('disabled');

            jQuery.each(vcht_currentOperator.history, function () {
                vcht_writeMsg(this, true);
            });
        }
    });
}

function vcht_clickShownElement(link) {
    var domElement = jQuery(link).attr('data-domelement');
    var page = jQuery(link).attr('data-page');
    vcht_shownElement = domElement;
    vcht_showUrl(page);
}
function vcht_showUrl(url) {
    var currentUrl = document.location.href;

    if (url == '') {
        url = currentUrl;
    }
    if (currentUrl.substr(currentUrl.length - 1, 1) == '/') {
        currentUrl = currentUrl.substr(0, currentUrl.length - 1);
    }
    if (url.substr(url.length - 1, 1) == '/') {
        url = url.substr(0, url.length - 1);
    }
    if (url.indexOf('http://') == 0 && currentUrl.indexOf('https://') == 0) {
        url = 'https://' + url.substr(7, url.length);
    } else if (url.indexOf('https://') == 0 && currentUrl.indexOf('https://') == 0) {
        url = 'http://' + url.substr(8, url.length);
    }
    if (url == currentUrl) {
        if (vcht_shownElement != '') {
            vcht_showElement(vcht_shownElement, false);
        }
    } else {
        sessionStorage.vcht_shownElement = vcht_shownElement;
        document.location.href = url;
    }
}
function vcht_openUploadPanel() {
    if (!jQuery('#vcht_btnUploadFiles').is('[disabled]')) {
        jQuery('#vcht_chatCommPanel').fadeOut();
        setTimeout(function () {
            jQuery('#vcht_uploadFilesPanel').fadeIn();
            vcht_onResize();
        }, 251);
    }
}
function vcht_validFilesUpload() {
    vcht_currentFiles = new Array();
    jQuery('#vcht_uploadFilesField .dz-preview.dz-success').each(function () {
        var file = jQuery(this).attr('data-file');
        file = file.replace(/ /g, '_');
        vcht_currentFiles.push(file);
    });
    jQuery('#vcht_uploadFilesPanel').fadeOut();
    if (vcht_currentFiles.length > 0) {
        jQuery('#vcht_btnUploadFiles').addClass('vcht_active');
    } else {
        jQuery('#vcht_btnUploadFiles').removeClass('vcht_active');
    }
    setTimeout(function () {
        jQuery('#vcht_chatCommPanel').fadeIn();
        vcht_onResize();
    }, 251);
}
function vcht_getLastHistory() {
    jQuery.ajax({
        url: vcht_data.ajaxurl,
        type: 'post',
        data: {
            action: 'client_getLastHistory'
        },
        success: function (rep) {
            rep = rep.trim();
            rep = jQuery.parseJSON(rep);
            if (vcht_currentUser.history == null) {
                vcht_currentUser.history = new Array();
            }
            jQuery.each(rep, function () {
                this.files = jQuery.parseJSON(this.files);
                vcht_addToHistory(this, true, true);
            });
            if (!vcht_checkMsgTimer) {
                //  vcht_checkMsgTimer = setTimeout(vcht_checkNewMessages, vcht_data.ajaxCheckDelay * 1000)
            }
        }
    });
}

function vcht_initBounce() {
    if (vcht_data.bounceFx == '1') {
        if (vcht_timerBounce) {
            clearInterval(vcht_timerBounce);
        }
        vcht_timerBounce = setInterval(function () {
            if (jQuery('#vcht_chatPanel').is('.vcht_minify') && !jQuery('#vcht_chatPanel').is('.vcht_hiddenPanel')) {
                jQuery('#vcht_chatPanel').css({
                    bottom: 0 - ((jQuery('#vcht_chatPanel').outerHeight() - jQuery('#vcht_chatHeader').outerHeight()) + 28)
                });
                setTimeout(function () {
                    if (jQuery('#vcht_chatPanel').is('.vcht_minify')) {
                        jQuery('#vcht_chatPanel').css({
                            bottom: 0 - (jQuery('#vcht_chatPanel').outerHeight() - jQuery('#vcht_chatHeader').outerHeight())
                        });
                    }
                }, 800);
            }
        }, 4000);
    }
}