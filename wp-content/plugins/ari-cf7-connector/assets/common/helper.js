;(function($, undefined) {
    if (window['AppHelper'] === undefined)
        return ;

    var alertEl = null;

    AppHelper.getScrollOffset = function(absolute) {
        absolute = absolute || false;

        var offset = -($('#wpadminbar').outerHeight() + 5);
        if (absolute)
            offset = Math.abs(offset);

        return offset;
    };

    AppHelper.FormValidation = {
        CLASS: {
            VALID: 'valid',

            INVALID: 'invalid'
        },

        MESSAGE_ATTR: 'data-validation-message',

        clear: function(el) {
            el.removeClass(this.CLASS.VALID + ' ' + this.CLASS.INVALID);
            el.attr(this.MESSAGE_ATTR, '');

            if ($.fn.qtip && el.data('qtipInit')) {
                el.qtip('api').hide();
            }
        },

        setValid: function(el, message) {
            this.changeStatus(el, message, this.CLASS.VALID);
        },

        setInvalid: function(el, message) {
            this.changeStatus(el, message, this.CLASS.INVALID);
        },

        changeStatus: function(el, message, className) {
            var self = this;

            el.addClass(className);
            el.attr(this.MESSAGE_ATTR, message);

            if ($.fn.qtip && !el.data('qtipInit')) {
                var hideTimer = null;

                el.qtip({
                    content: {
                        text: function() {
                            return el.attr(self.MESSAGE_ATTR);
                        },

                        title: false
                    },
                    position: {
                        my: 'bottom center',

                        at: 'top center',

                        adjust: {
                            y: -10
                        }
                    },
                    show: {
                        event: false,

                        ready: true
                    },
                    hide: {
                        event: false
                    },
                    style: {
                        tip: true
                    },
                    events: {
                        show: function(e, api) {
                            if (hideTimer)
                                clearTimeout(hideTimer);

                            var isValid = el.hasClass(self.CLASS.VALID);

                            api.set('style.classes', isValid ? 'qtip-green ari-qtip-fix' : 'qtip-red ari-qtip-fix');
                        },

                        visible: function(e, api) {
                            hideTimer = setTimeout(function() {
                                el.qtip('hide');
                            }, 2000);
                        },

                        hide: function(e, api) {
                            if (hideTimer)
                                clearTimeout(hideTimer);
                        }
                    }
                });
                el.data('qtipInit', true);
            } else {
                el.qtip('api').toggle(true);
            }
        }
    };

    AppHelper.parseJSON = function(data, defaultValue) {
        if (defaultValue === undefined)
            defaultValue = null;

        if (typeof(data) != 'string' && !(data instanceof String))
            return defaultValue;

        var res = null;
        try {
            if (window['JSON']) {
                res = JSON.parse(data);
            } else if ($.parseJSON) {
                res = $.parseJSON;
            }
        } catch (ex) {
            res = defaultValue;
        }

        return res;
    };

    AppHelper.getClonerInitData = function(el) {
        var data = null,
            id = el.attr('id'),
            metadataEl = el.closest('[data-cloner-metadata-container=' + id + ']'),
            storageCtrlId = metadataEl.attr('data-cloner-storage-id');

        if (!storageCtrlId)
            return data;

        return this.parseJSON($('#' + storageCtrlId).val(), null);
    };

    AppHelper.saveClonerData = function(cloner) {
        var el = cloner.getElement(),
            id = el.attr('id'),
            metadataEl = el.closest('[data-cloner-metadata-container=' + id + ']'),
            storageCtrlId = metadataEl.attr('data-cloner-storage-id');

        var data = cloner.getData(true);
        $('#' + storageCtrlId).val(JSON.stringify(data));
    };

    AppHelper.getMessage = function(key, defaultMessage) {
        return this.options.messages[key] !== undefined ? this.options.messages[key] : (defaultMessage || '');
    };

    AppHelper.prepareSelect2Data = function(data, mapping) {
        mapping = mapping || {};
        var select2Data = [];

        if (!data)
            return select2Data;

        $.map(data, function(dataItem) {
            if (mapping['id'] !== undefined) {
                dataItem['id'] = dataItem[mapping['id']];
            }

            if (mapping['text'] !== undefined) {
                dataItem['text'] = dataItem[mapping['text']];
            }

            select2Data.push(dataItem);
        });

        return select2Data;
    };

    AppHelper.ajax = function(options) {
        options = options || {};
        options = $.extend(true, {}, {
            url: this.options.ajaxUrl,

            type: 'POST',

            dataType: 'json'
        }, options);

        if (options['data'] === undefined)
            options['data'] = {};

        return $.ajax(options);
    };

    AppHelper.ajaxAddon = function(action, options) {
        options = options || {};

        if (options['data'] === undefined)
            options['data'] = {};

        options['data']['ctrl'] = 'plugin-dispatcher_request';
        options['data']['delegate'] = action;

        return this.ajax(options);
    };

    AppHelper.showSmallLoading = function(el) {
        el.addClass('ari-loading-overlay ari-loading-overlay-small');
    };

    AppHelper.showLoading = function(el, pos) {
        pos = pos || '';

        el.addClass('ari-loading-overlay' + (pos ? ' ari-loading-pos-' + pos : ''));
    };

    AppHelper.hideLoading = function(el) {
        el.removeClass('ari-loading-overlay ari-loading-overlay-small');
    };

    AppHelper.alert = function(message) {
        if (!alertEl) {
            alertEl = $('<div class="ari-page-alert" id="ari_page_alert"><div id="ari_page_alert_message"></div><div class="action-panel align-right"><button id="btnAriAlertOk" class="button button-primary">' + this.getMessage('ok') + '</button></div></div>').appendTo(document.body);
            $('#btnAriAlertOk').on('click', function() {
                $.magnificPopup.close();

                return false;
            });
        };

        $('#ari_page_alert_message').html(message || '');

        $.magnificPopup.open({
            items: {
                src: '#ari_page_alert'
            },

            mainClass: 'alert-modal',

            type: 'inline'
        }, 0);
    };

    $(document).ready(function() {
        if (window['ARI_APP'] === undefined)
            return ;

        var globalAppConfig = window['ARI_APP'],
            containerId = globalAppConfig['containerId'] || 'ari_cf7connector_plugin';

        AppHelper.options = $.extend(true, AppHelper.options, globalAppConfig['options'] || {});
        AppHelper.createApp(containerId, globalAppConfig['app']);
    });
})(jQuery);