;jQuery(document).on('cf7c_loaded', function(e, app, undefined) {
    var $ = jQuery,
        FV = AppHelper.FormValidation,
        STATE = {
            apiKey: ARI_CF7C_CF7_MAILCHIMP['apiKey'],

            mailchimpListsLoaded: false,

            mailchimpLists: null,

            mailchimpListsFields: {},

            formTags: null
        };

    function isPredefinedApiKey(apiKey) {
        if (!apiKey)
            return false;

        return /^{{.+}}$/.test(apiKey);
    };

    function getListName(listId, defaultName) {
        var lists = STATE['mailchimpLists'] || [],
            listName = defaultName !== undefined ? defaultName : '';

        for (var i = 0; i < lists.length; i++) {
            var list = lists[i];

            if (list['id'] == listId) {
                listName = list['name'];
            }
        };

        return listName;
    };

    function getFieldProperty(fieldId, propName, defaultValue) {
        var fieldProp = defaultValue !== undefined ? defaultValue : '';

        if (!fieldId)
            return fieldProp;

        var fieldMeta = fieldId.split('_');
        if (fieldMeta.length < 2)
            return fieldProp;

        var listId = fieldMeta[0],
            fieldId = fieldMeta[1];

        if (STATE.mailchimpListsFields[listId] !== undefined) {
            var listFields = STATE.mailchimpListsFields[listId];
            for (var i = 0; i < listFields.length; i++) {
                var fieldInfo = listFields[i];

                if (fieldInfo['field_id'] == fieldId) {
                    fieldProp = fieldInfo[propName] !== undefined ? fieldInfo[propName] : defaultValue;
                    break;
                }
            }
        }

        return fieldProp;
    };

    function getFieldTag(fieldId, defaultTag) {
        return getFieldProperty(fieldId, 'tag', defaultTag);
    };

    function getFieldName(fieldId, defaultName) {
        return getFieldProperty(fieldId, 'name', defaultName);

        var fieldName = defaultName !== undefined ? defaultName : '';

        if (!fieldId)
            return fieldName;

        var fieldMeta = fieldId.split('_');
        if (fieldMeta.length < 2)
            return fieldName;

        var listId = fieldMeta[0],
            fieldId = fieldMeta[1];

        if (STATE.mailchimpListsFields[listId] !== undefined) {
            var listFields = STATE.mailchimpListsFields[listId];
            for (var i = 0; i < listFields.length; i++) {
                var fieldInfo = listFields[i];

                if (fieldInfo['field_id'] == fieldId) {
                    fieldName = fieldInfo['name'];
                    break;
                }
            }
        }

        return fieldName;
    };

    var MailchimpApiKeySelectorManager = {
        selectors: {},

        getSelector: function(id, configContainer) {
            if (this.selectors[id] !== undefined)
                return this.selectors[id];

            this.selectors[id] = new MailchimpApiKeySelector(id, configContainer);

            return this.selectors[id];
        }
    };

    function MailchimpApiKeySelector(id, configContainer) {
        this.id = id;
        this.uiContainer = $('#' + id + '_container');
        this.ctrlApiKey = $('#' + id);
        this.configContainer = configContainer;
        this.ddlApiKey = configContainer.find('[data-apikey-selector]');
        this.tbxNewApiKey = configContainer.find('[data-apikey-new]');
        this.statusBar = this.uiContainer.find('[data-maichimp-apikey-info]');
        this.btnKeyValidate = configContainer.find('[data-apikey-new-validate]');

        var configPanels = {};
        configContainer.find('[data-mailchimp-key-config]').each(function() {
            var $this = $(this);

            configPanels[$this.attr('data-mailchimp-key-config')] = $this;
        });
        this.configPanels = configPanels;

        var apiKeyList = {};
        this.ddlApiKey.find('option[value!=""]').each(function() {
            var $option = $(this),
                apiKey = $option.attr('data-apikey'),
                apiKeyId = $option.attr('value');

            apiKeyList[apiKeyId] = apiKey;
        });
        this.apiKeyList = apiKeyList;

        this.init();
    };

    MailchimpApiKeySelector.prototype = {
        constructor: MailchimpApiKeySelector,

        init: function() {
            var self = this;

            this.ddlApiKey.on('change', function() {
                var val = self.ddlApiKey.val();

                if (val) {
                    self.applyApiKey(val);
                }
            });

            this.configContainer.find('[data-mailchimp-key-config-switch]').on('click', function() {
                var configType = $(this).attr('data-mailchimp-key-config-switch');

                self.showConfigPanel(configType);

                return false;
            });

            this.configContainer.find('[data-apikey-new-apply]').on('click', function() {
                var apiKey = self.tbxNewApiKey.val();

                self.applyApiKey(apiKey);

                return false;
            });

            this.btnKeyValidate.ariButton({
                onClick: function() {
                    var apikeyEl = self.tbxNewApiKey,
                        apiKey = $.trim(apikeyEl.val());

                    FV.clear(apikeyEl);

                    if (!apiKey) {
                        FV.setInvalid(apikeyEl, AppHelper.getMessage('mailchimp_key_empty', 'The key can not be empty'));
                        return false;
                    }

                    this.start();

                    var btn = this;
                    AppHelper.ajaxAddon('mailchimp_mailchimp_check-apikey', {
                        data: {
                            api_key: apiKey
                        }
                    }).done(function(data) {
                        if (data.result) {
                            var isValid = data.result['valid'];
                            if (isValid)
                                FV.setValid(apikeyEl, AppHelper.getMessage('mailchimp_key_valid', 'The key is valid'));
                            else
                                FV.setInvalid(apikeyEl, data.result['message']);
                        } else {
                            var error = AppHelper.getMessage('request_failed');
                            if (data && data['error'])
                                error = data['error'];

                            alert(error);
                        }
                    }).fail(function() {
                        alert(AppHelper.getMessage('request_failed'));
                    }).always(function() {
                        btn.complete();
                    });

                    return false;
                }
            });
        },

        hideConfigPanels: function() {
            for (var key in this.configPanels)
                (this.configPanels[key]).css({display: 'none'});
        },

        showConfigPanel: function(configType) {
            this.hideConfigPanels();

            if (this.configPanels[configType])
                (this.configPanels[configType]).css({'display': 'block'});
        },

        getCurrentApiKey: function() {
            return this.ctrlApiKey.val();
        },

        hasPredefinedKeys: function() {
            return this.ddlApiKey.length > 0 && this.ddlApiKey.find('option').length > 1;
        },

        initConfig: function() {
            var apiKey = this.getCurrentApiKey(),
                isPredefined = isPredefinedApiKey(apiKey),
                hasPredefinedKeys = this.hasPredefinedKeys(),
                activeConfigType;

            if (isPredefined && !hasPredefinedKeys)
                apiKey = '';

            if (apiKey) {
                activeConfigType = isPredefined ? 'predefined' : 'new';
            } else {
                activeConfigType = hasPredefinedKeys ? 'predefined' : 'new';
            };

            this.ddlApiKey.val('');
            this.tbxNewApiKey.val('');
            FV.clear(this.tbxNewApiKey);

            if (apiKey) {
                if (isPredefined)
                    this.ddlApiKey.val(apiKey);
                else
                    this.tbxNewApiKey.val(apiKey);
            };

            this.showConfigPanel(activeConfigType);
        },

        resolveApiKey: function(apiKey) {
            var isPredefined = isPredefinedApiKey(apiKey);

            if (isPredefined) {
                if (this.apiKeyList[apiKey] !== undefined)
                    apiKey = this.apiKeyList[apiKey];
                else
                    apiKey = '';
            };

            return apiKey;
        },

        getApiKeyInfo: function(apiKey) {
            return this.resolveApiKey(apiKey);
        },

        applyApiKey: function(apiKey) {
            var currentApiKey = this.getCurrentApiKey();

            if (currentApiKey != apiKey) {
                if (apiKey)
                    this.uiContainer.addClass('ari-cf7c-mailchimp-apikey-defined');
                else
                    this.uiContainer.removeClass('ari-cf7c-mailchimp-apikey-defined');

                this.ctrlApiKey.val(apiKey);

                this.statusBar.val(this.getApiKeyInfo(apiKey));

                app.emitEvent('mailchimp_change_key', apiKey);
            };

            $.magnificPopup.instance.close();
        },

        clear: function() {
            FV.clear(this.tbxNewApiKey);
        }
    };

    $('.cf7-conn-mailchimp-select-apikey').magnificPopup({
        type: 'inline',

        callbacks: {
            elementParse: function(item) {
                var configContainer = $(item.src),
                    ctrlId = configContainer.attr('data-mailchimp-apikey-id'),
                    apiKeySelector = MailchimpApiKeySelectorManager.getSelector(ctrlId, configContainer);

                apiKeySelector.initConfig();
            },

            beforeClose: function() {
                var configContainer = $(this.currItem.src),
                    ctrlId = configContainer.attr('data-mailchimp-apikey-id'),
                    apiKeySelector = MailchimpApiKeySelectorManager.getSelector(ctrlId, configContainer);

                apiKeySelector.clear();
            }
        }
    });

    function getMailchimpLists(apiKey, successCallback, failedCallback, reload) {
        reload = reload || false;

        return AppHelper.ajaxAddon('mailchimp_mailchimp_get-lists', {
            data: {
                api_key: apiKey,

                reload: reload ? '1' : ''
            }
        }).done(function(data) {
            if (data && !data.is_error) {
                if (successCallback)
                    successCallback(data.result);
            } else {
                var error = null;
                if (data && data['error'])
                    error = data['error'];
                else
                    error = AppHelper.getMessage('request_failed');

                if (failedCallback)
                    failedCallback(error);
            }
        });
    };

    function getMailchimpListFields(listId, successCallback, failedCallback, alwaysCallback, reload) {
        reload = reload || false;
        listId = listId || '';

        var listFields,
            isLoaded = false;

        if (!listId) {
            listFields = [];
            isLoaded = true;
        } else if (!reload && STATE.mailchimpListsFields[listId] !== undefined) {
            listFields = STATE.mailchimpListsFields[listId];
            isLoaded = true;
        };

        if (isLoaded) {
            if (successCallback)
                successCallback(listFields);

            if (alwaysCallback)
                alwaysCallback();
        } else {
            AppHelper.ajaxAddon('mailchimp_mailchimp_get-list-fields', {
                data: {
                    api_key: STATE.apiKey,

                    list_id: listId,

                    reload: reload ? '1' : ''
                }
            }).done(function(data) {
                if (data && !data.is_error) {
                    var retListFields = data.result || {};

                    STATE.mailchimpListsFields[listId] = retListFields;

                    if (successCallback)
                        successCallback(retListFields);
                } else {
                    var error = null;
                    if (data && data['error'])
                        error = data['error'];
                    else
                        error = AppHelper.getMessage('request_failed');

                    if (failedCallback)
                        failedCallback(error);
                }
            }).fail(function() {
                alert(AppHelper.getMessage('request_failed'));
            }).always(function() {
                if (alwaysCallback)
                    alwaysCallback();
            });
        }
    };

    var $subscriptionsClonerEl = $('.ari-cf7-mailchimp-subscription-list'),
        subscriptionsCloner = $subscriptionsClonerEl.ariCloner({
            sortable: {
                enabled: false
            },

            scrollTo: {
                options: {
                    offset: AppHelper.getScrollOffset()
                }
            },

            minItemsCount: 1,

            onInit: function() {
                var self = this;

                this.findClonerElements('.ari-cloner-template').each(function() {
                    var item = $(this);

                    self.initItem(item);
                });
            },

            onAddItem: function(item) {
                this.initItem(item);
            },

            onItemsChanged: function(type) {
                if (type == 'reset') {
                    var self = this;

                    this.findClonerElements('.ari-cloner-template').each(function() {
                        var item = $(this);

                        self.initItem(item);
                    });
                }
            },

            mixins: {
                getContainerEl: function() {
                    return this.getElement().closest('TD');
                },

                showLoading: function() {
                    AppHelper.showLoading(this.getContainerEl(), 'tr');
                },

                hideLoading: function() {
                    AppHelper.hideLoading(this.getContainerEl());
                },

                applyNewLists: function(lists) {
                    var self = this,
                        listSelect2Data = AppHelper.prepareSelect2Data(lists, {text: 'name'});

                    this.findClonerElements('.ari-cloner-template').each(function() {
                        var item = $(this),
                            listsCloner = self.getChildClonerById(item.attr('id'), 'lists');

                        listsCloner.applyNewLists(listSelect2Data);
                    });
                },

                initItem: function(item) {
                    var confirmFieldCtrl = this.getControl('confirm_field', item);

                    confirmFieldCtrl.select2();
                }
            },

            childClonersOptions: {
                lists: {
                    confirmOnRemove: true,

                    minItemsCount: 1,

                    sortable: {
                        enabled: false
                    },

                    fx: {
                        enabled: false
                    },

                    scrollTo: {
                        enabled: true,

                        options: {
                            offset: AppHelper.getScrollOffset()
                        }
                    },

                    messages: {
                        removeItem: AppHelper.getMessage('mailchimp_remove_list', 'Do you want to remove the selected list section?')
                    },

                    onInit: function () {
                        var self = this;
                        this.findClonerElements('.ari-cloner-template').each(function () {
                            var item = $(this);

                            self.initItem(item);
                        });

                        self.getElement().on('click', '.ari-cf7c-mailchimp-customfields-switcher', function () {
                            var chk = $(this),
                                item = chk.closest('.ari-cloner-template');

                            self.toggleCustomFields(item, $(this).is(':checked'));
                        });

                        self.getElement().on('click', '.ari-cf7c-mailchimp-lists-reload', function () {
                            self.reloadLists(true);

                            return false;
                        });
                    },

                    onAddItem: function (item) {
                        var options = {
                            'list': {
                                'data': AppHelper.prepareSelect2Data(STATE['mailchimpLists'], {text: 'name'})
                            }
                        };

                        this.initItem(item, options);
                    },

                    onItemsChanged: function (type) {
                        if (type == 'reset') {
                            var self = this;

                            this.findClonerElements('.ari-cloner-template').each(function () {
                                var item = $(this);

                                self.initItem(item);
                            });
                        }
                    },

                    mixins: {
                        isListsReloading: false,

                        getListSelect2ContainerList: function () {
                            return this.getElement().find('.ari-cf7c-mailchimp-list-container .select2');
                        },

                        showListsLoading: function() {
                            this.getListSelect2ContainerList().each(function () {
                                AppHelper.showSmallLoading($(this));
                            });
                        },

                        hideListsLoading: function() {
                            this.getListSelect2ContainerList().each(function () {
                                AppHelper.hideLoading($(this));
                            });
                        },

                        toggleCustomFields: function(item, visible) {
                            if (visible) {
                                item.removeClass('ari-cf7c-mailchimp-customfields-hidden');
                            } else {
                                item.addClass('ari-cf7c-mailchimp-customfields-hidden');
                            }
                        },

                        applyNewLists: function(lists) {
                            var self = this;

                            this.findClonerElements('.ari-cloner-template').each(function () {
                                var item = $(this);

                                self.initItem(item, {list: {data: lists}});
                            });
                        },

                        reloadLists: function(force, options) {
                            if (this.isListsReloading)
                                return;

                            options = options || {};
                            force = force || false;

                            if (!STATE['apiKey'] || (!force && STATE['mailchimpListsLoaded']))
                                return;

                            var cloner = this;

                            STATE['mailchimpListsLoaded'] = false;
                            this.isListsReloading = true;
                            this.showListsLoading();

                            getMailchimpLists(
                                STATE['apiKey'],

                                function (lists) {
                                    changeMailchimpLists(lists);

                                    if ($.isFunction(options.complete))
                                        options.complete.call(cloner);
                                },

                                function (error) {
                                    AppHelper.alert(error);
                                },

                                force
                            ).always(function () {
                                    cloner.isListsReloading = false;
                                    cloner.hideListsLoading();
                                });

                            return false;
                        },

                        getListId: function(item) {
                            return this.getControl('list_id', item).val();
                        },

                        listIsChanged: function(list, item) {
                            var fieldsCloner = this.getFieldsCloner(item);

                            fieldsCloner.setList(list);
                        },

                        getFieldsCloner: function(item) {
                            return this.getChildClonerById(item.attr('id'), 'custom_fields');
                        },

                        _initCustomFieldsControl: function (item) {
                            var fieldsCloner = this.getFieldsCloner(item),
                                showCustomFields = this.getControl('use_custom_fields', item).is(':checked');

                            this.toggleCustomFields(item, showCustomFields);

                            fieldsCloner.findClonerElements('.ari-cloner-template').each(function () {
                                fieldsCloner.initItem($(this));
                            });
                        },

                        _initListsControl: function(item, options) {
                            var cloner = this,
                                listOptions = $.extend({}, options['list'] || {}),
                                ddlLists = cloner.getControl('list_id', item),
                                listMetaCtrl = cloner.getControl('list_meta', item),
                                currentMeta = AppHelper.parseJSON(listMetaCtrl.val()),
                                selectedList = '';

                            if (currentMeta !== null) {
                                if (listOptions['data'] === undefined && currentMeta !== null) {
                                    listOptions['data'] = AppHelper.prepareSelect2Data([currentMeta], {text: 'name'});
                                }

                                selectedList = currentMeta.id || '';
                            }

                            ddlLists.off('change.mailchimp').on('change.mailchimp', function(e) {
                                var listId = ddlLists.val(),
                                    currentMeta = AppHelper.parseJSON(listMetaCtrl.val()),
                                    meta = {};

                                if (listId !== null) {
                                    meta.id = listId;
                                    meta.name = getListName(listId, currentMeta && currentMeta.id == listId ? currentMeta.name : '');
                                }

                                listMetaCtrl.val(JSON.stringify(meta));

                                cloner.listIsChanged(meta, item);
                            });

                            if (ddlLists.hasClass('select2-hidden-accessible'))
                                ddlLists.select2('destroy').empty();

                            ddlLists
                                .select2(listOptions)
                                .off('select2:opening')
                                .on('select2:opening', function () {
                                    if (!STATE['mailchimpListsLoaded'] && STATE['apiKey']) {
                                        cloner.showListsLoading();

                                        setTimeout(function () {
                                            cloner.reloadLists(false, {
                                                complete: function () {
                                                    ddlLists.select2('open');
                                                }
                                            });
                                        }, 1);

                                        return false;
                                    }
                                });

                            if (selectedList !== null) {
                                ddlLists.val(selectedList).trigger('change');
                            }
                        },

                        initItem: function (item, options) {
                            options = options || {};

                            this._initListsControl(item, options);
                            this._initCustomFieldsControl(item);
                        }
                    },

                    childClonersOptions: {
                        custom_fields: {
                            confirmOnRemove: false,

                            minItemsCount: 1,

                            sortable: {
                                enabled: false
                            },

                            fx: {
                                //enabled: false
                            },

                            scrollTo: {
                                enabled: false
                            },

                            onInit: function() {
                                var self = this;

                                self.getElement().on('click', '.ari-cf7c-mailchimp-fields-reload', function() {
                                    self.reloadListFields(true);

                                    return false;
                                });
                            },

                            onAddItem: function(item) {
                                var options = {list: {data: this.getSelect2ListsFields()}};

                                this.initItem(item, options);
                            },

                            onItemsChanged: function(type) {
                                if (type == 'reset') {
                                    var self = this;

                                    this.resetFieldsLoaded();
                                    this.findClonerElements('.ari-cloner-template').each(function() {
                                        var item = $(this);

                                        self.initItem(item);
                                    });
                                }
                            },

                            mixins: {
                                isListsFieldsReloading: false,

                                getSelect2ListsFields: function() {
                                    var list = this.getList(),
                                        select2ListsFields = [];

                                    if (!list)
                                        return select2ListsFields;

                                    var listId = list.id,
                                        listName = list.name,
                                        listFields = STATE.mailchimpListsFields[listId] !== undefined ? STATE.mailchimpListsFields[listId] : null;

                                    if ($.isArray(listFields) && listFields.length > 0) {
                                        select2ListsFields.push({
                                            text: listName,

                                            children: AppHelper.prepareSelect2Data(listFields, {id: 'system_id', text: 'name'})
                                        });
                                    }

                                    return select2ListsFields
                                },

                                bindFields: function(force) {
                                    force = force || false;

                                    var options = {},
                                        self = this;

                                    if (force) {
                                        this.resetFieldsLoaded();
                                    } if (this.isFieldsLoaded()) {
                                        options['list'] = {data: this.getSelect2ListsFields()};
                                    };

                                    this.findClonerElements('.ari-cloner-template').each(function() {
                                        var item = $(this);

                                        self.initItem(item, options);
                                    });
                                },

                                resetFieldsLoaded: function() {
                                    this.mailchimpFieldsLoaded = false;
                                },

                                isFieldsLoaded: function() {
                                    return this.mailchimpFieldsLoaded;
                                },

                                markFieldsLoaded: function() {
                                    this.mailchimpFieldsLoaded = true;
                                },

                                getList: function() {
                                    return this.mailchimpList || null;
                                },

                                setList: function(list, silent) {
                                    silent = silent || false;

                                    this.mailchimpList = list;

                                    if (!silent) {
                                        this.bindFields(true);
                                    }
                                },

                                getListId: function() {
                                    var list = this.getList();

                                    return list ? list.id : '';
                                },

                                isValidField: function(fieldId) {
                                    if (!fieldId)
                                        return false;

                                    var listId = this.getListId();
                                    if (!listId)
                                        return false;

                                    var fieldListId = fieldId.split('_')[0];

                                    return listId === fieldListId;
                                },

                                getListFieldsSelect2ContainerList: function() {
                                    return this.getElement().find('.ari-cf7c-mailchimp-listfields-container .select2');
                                },

                                showFieldsLoading: function() {
                                    this.getListFieldsSelect2ContainerList().each(function() {
                                        AppHelper.showSmallLoading($(this));
                                    });
                                },

                                hideFieldsLoading: function() {
                                    this.getListFieldsSelect2ContainerList().each(function() {
                                        AppHelper.hideLoading($(this));
                                    });
                                },

                                initItem: function(item, options) {
                                    var cloner = this;

                                    options = options || {};

                                    var listOptions = $.extend({}, options['list'] || {}),
                                        ddlFields = cloner.getControl('mailchimp_field_id', item),
                                        fieldMetaCtrl = cloner.getControl('mailchimp_field_meta', item),
                                        formFieldCtrl = cloner.getControl('form_field', item),
                                        currentMeta = AppHelper.parseJSON(fieldMetaCtrl.val()),
                                        selectedField = null;

                                    formFieldCtrl.select2({
                                        tags: true
                                    });

                                    if (currentMeta !== null) {
                                        if (!this.isValidField(currentMeta['id'])) {
                                            currentMeta = null;
                                        };

                                        if (currentMeta !== null) {
                                            if (listOptions['data'] === undefined) {
                                                listOptions['data'] = AppHelper.prepareSelect2Data([currentMeta], {text: 'name'});
                                            };

                                            selectedField = currentMeta['id'];
                                        }
                                    };

                                    ddlFields.off('change.mailchimp').on('change.mailchimp', function() {
                                        var selectedField = ddlFields.val(),
                                            currentMeta = AppHelper.parseJSON(fieldMetaCtrl.val()),
                                            meta = {};

                                        if (selectedField) {
                                            var fieldId = selectedField,
                                                isMetaForCurrentField = (currentMeta && currentMeta['id'] && currentMeta['id'] == fieldId);

                                            meta = {
                                                id: fieldId,

                                                name: getFieldName(fieldId, isMetaForCurrentField ? currentMeta['name'] : ''),

                                                tag: getFieldTag(fieldId, isMetaForCurrentField ? currentMeta['tag'] : '')
                                            };
                                        };

                                        fieldMetaCtrl.val(JSON.stringify(meta));
                                    });

                                    if (ddlFields.hasClass('select2-hidden-accessible'))
                                        ddlFields.select2('destroy').empty();

                                    ddlFields
                                        .select2(listOptions)
                                        .off('select2:opening')
                                        .on('select2:opening', function() {
                                            if (cloner.isFieldsLoaded())
                                                return ;

                                            setTimeout(function() {
                                                cloner.reloadListFields(false, {
                                                    complete: function() {
                                                        ddlFields.select2('open');
                                                    }
                                                });
                                            }, 1);

                                            return false;
                                        });

                                    if (selectedField)
                                        ddlFields.val(selectedField).trigger('change');
                                    else
                                        ddlFields.val('').trigger('change');
                                },

                                reloadListFields: function(force, options) {
                                    force = force || false;
                                    options = options || {};

                                    var cloner = this;

                                    cloner.resetFieldsLoaded();
                                    cloner.showFieldsLoading();

                                    var listId = cloner.getListId();
                                    getMailchimpListFields(
                                        listId,

                                        function() {
                                            cloner.markFieldsLoaded();
                                            cloner.bindFields();

                                            if ($.isFunction(options.complete))
                                                options.complete.call(cloner);
                                        },

                                        function(error) {
                                            AppHelper.alert(error);
                                        },

                                        function() {
                                            cloner.hideFieldsLoading();
                                        },

                                        force
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }, AppHelper.getClonerInitData($subscriptionsClonerEl));

    app.form.on('submit', function() {
        AppHelper.saveClonerData(subscriptionsCloner);
    });

    function changeMailchimpLists(lists) {
        STATE['mailchimpListsLoaded'] = true;
        STATE['mailchimpLists'] = lists;

        subscriptionsCloner.applyNewLists(lists);
    };

    app.subscribeEvent('mailchimp_change_key', function(e, apiKey) {
        STATE['apiKey'] = apiKey;
        subscriptionsCloner.reset(true);

        if (!apiKey) {
            changeMailchimpLists(null);
            return ;
        };

        subscriptionsCloner.showLoading();

        getMailchimpLists(
            apiKey,

            function(lists) {
                changeMailchimpLists(lists);
            },

            function(error) {
                AppHelper.alert(error);
            }
        ).always(function() {
                subscriptionsCloner.hideLoading();
            });
    });
});