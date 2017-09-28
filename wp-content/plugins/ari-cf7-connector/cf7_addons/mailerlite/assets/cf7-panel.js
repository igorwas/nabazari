;jQuery(document).on('cf7c_loaded', function(e, app, undefined) {
    var $ = jQuery,
        FV = AppHelper.FormValidation,
        STATE = {
            apiKey: ARI_CF7C_CF7_MAILERLITE['apiKey'],

            listsLoaded: false,

            fieldsLoaded: false,

            lists: null,

            fields: null,

            formTags: null
        };

    function isPredefinedApiKey(apiKey) {
        if (!apiKey)
            return false;

        return /^{{.+}}$/.test(apiKey);
    };

    function getListName(listId, defaultName) {
        var lists = STATE['lists'] || [],
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

        if (!fieldId || !STATE.fields)
            return fieldProp;

        var fieldInfo = null;
        for (var i = 0; i < STATE.fields.length; i++) {
            var currentField = STATE.fields[i];
            if (currentField.id == fieldId) {
                fieldInfo = currentField;
                break;
            }
        }

        if (!fieldInfo)
            return fieldProp;

        fieldProp = fieldInfo[propName] !== undefined ? fieldInfo[propName] : defaultValue;

        return fieldProp;
    };

    function getFieldTag(fieldId, defaultTag) {
        return getFieldProperty(fieldId, 'key', defaultTag);
    };

    function getFieldName(fieldId, defaultName) {
        return getFieldProperty(fieldId, 'name', defaultName);
    };

    var ApiKeySelectorManager = {
        selectors: {},

        getSelector: function(id, configContainer) {
            if (this.selectors[id] !== undefined)
                return this.selectors[id];

            this.selectors[id] = new ApiKeySelector(id, configContainer);

            return this.selectors[id];
        }
    };

    function ApiKeySelector(id, configContainer) {
        this.id = id;
        this.uiContainer = $('#' + id + '_container');
        this.ctrlApiKey = $('#' + id);
        this.configContainer = configContainer;
        this.ddlApiKey = configContainer.find('[data-apikey-selector]');
        this.tbxNewApiKey = configContainer.find('[data-apikey-new]');
        this.statusBar = this.uiContainer.find('[data-mailerlite-apikey-info]');
        this.btnKeyValidate = configContainer.find('[data-apikey-new-validate]');

        var configPanels = {};
        configContainer.find('[data-mailerlite-key-config]').each(function() {
            var $this = $(this);

            configPanels[$this.attr('data-mailerlite-key-config')] = $this;
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

    ApiKeySelector.prototype = {
        constructor: ApiKeySelector,

        init: function() {
            var self = this;

            this.ddlApiKey.on('change', function() {
                var val = self.ddlApiKey.val();

                if (val) {
                    self.applyApiKey(val);
                }
            });

            this.configContainer.find('[data-mailerlite-key-config-switch]').on('click', function() {
                var configType = $(this).attr('data-mailerlite-key-config-switch');

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
                        FV.setInvalid(apikeyEl, AppHelper.getMessage('mailerlite_key_empty', 'The key can not be empty'));
                        return false;
                    }

                    this.start();

                    var btn = this;
                    AppHelper.ajaxAddon('mailerlite_mailerlite_check-apikey', {
                        data: {
                            api_key: apiKey
                        }
                    }).done(function(data) {
                        if (data.result) {
                            var isValid = data.result['valid'];
                            if (isValid)
                                FV.setValid(apikeyEl, AppHelper.getMessage('mailerlite_key_valid', 'The key is valid'));
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
                    this.uiContainer.addClass('ari-cf7c-mailerlite-apikey-defined');
                else
                    this.uiContainer.removeClass('ari-cf7c-mailerlite-apikey-defined');

                this.ctrlApiKey.val(apiKey);

                this.statusBar.val(this.getApiKeyInfo(apiKey));

                app.emitEvent('mailerlite_change_key', apiKey);
            };

            $.magnificPopup.instance.close();
        },

        clear: function() {
            FV.clear(this.tbxNewApiKey);
        }
    };

    $('.cf7-conn-mailerlite-select-apikey').magnificPopup({
        type: 'inline',

        callbacks: {
            elementParse: function(item) {
                var configContainer = $(item.src),
                    ctrlId = configContainer.attr('data-mailerlite-apikey-id'),
                    apiKeySelector = ApiKeySelectorManager.getSelector(ctrlId, configContainer);

                apiKeySelector.initConfig();
            },

            beforeClose: function() {
                var configContainer = $(this.currItem.src),
                    ctrlId = configContainer.attr('data-mailerlite-apikey-id'),
                    apiKeySelector = ApiKeySelectorManager.getSelector(ctrlId, configContainer);

                apiKeySelector.clear();
            }
        }
    });

    function getLists(apiKey, successCallback, failedCallback, reload) {
        reload = reload || false;

        return AppHelper.ajaxAddon('mailerlite_mailerlite_get-lists', {
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

    function getFields(successCallback, failedCallback, alwaysCallback, reload) {
        reload = reload || false;

        if (!STATE['apiKey']) {
            STATE.fields = null;
            STATE.fieldsLoaded = true;
        };

        if (!STATE['apiKey'] || (STATE.fieldsLoaded && !reload)) {
            if (successCallback)
                successCallback(STATE.fields);

            if (alwaysCallback)
                alwaysCallback();
        } else {
            AppHelper.ajaxAddon('mailerlite_mailerlite_get-fields', {
                data: {
                    api_key: STATE.apiKey,

                    reload: reload ? '1' : ''
                }
            }).done(function(data) {
                if (data && !data.is_error) {
                    var retFields = data.result || {};

                    STATE.fieldsLoaded = true;
                    STATE['fields'] = retFields;

                    if (successCallback)
                        successCallback(STATE.fields);
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

    var $subscriptionsClonerEl = $('.ari-cf7-mailerlite-subscription-list'),
        subscriptionsCloner = $subscriptionsClonerEl.ariCloner({
            sortable: {
                enabled: false
            },

            scrollTo: {
                enabled: false
            },

            onInit: function() {
                var self = this;
                this.findClonerElements('.ari-cloner-template').each(function() {
                    var item = $(this);

                    self.initItem(item);
                });

                self.getElement().on('click', '.ari-cf7c-mailerlite-customfields-switcher', function() {
                    var chk = $(this),
                        item = chk.closest('.ari-cloner-template');

                    self.toggleCustomFields(item, $(this).is(':checked'));
                });

                self.getElement().on('click', '.ari-cf7c-mailerlite-lists-reload', function() {
                    self.reloadLists();

                    return false;
                });
            },

            onAddItem: function(item) {
                var options = {
                    'list': {
                        'data': AppHelper.prepareSelect2Data(STATE['lists'], {text: 'name'})
                    }
                };

                this.initItem(item, options);
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
                isListsReloading: false,

                getContainerEl: function() {
                    return this.getElement().closest('TD');
                },

                showLoading: function() {
                    AppHelper.showLoading(this.getContainerEl(), 'tr');
                },

                hideLoading: function() {
                    AppHelper.hideLoading(this.getContainerEl());
                },

                getListSelect2ContainerList: function() {
                    return this.getElement().find('.ari-cf7c-mailerlite-list-container .select2');
                },

                showListsLoading: function() {
                    this.getListSelect2ContainerList().each(function() {
                        AppHelper.showSmallLoading($(this));
                    });
                },

                hideListsLoading: function() {
                    this.getListSelect2ContainerList().each(function() {
                        AppHelper.hideLoading($(this));
                    });
                },

                initItem: function(item, options) {
                    options = options || {};
                    var cloner = this,
                        fieldsCloner = cloner.getChildClonerById(item.attr('id'), 'custom_fields'),
                        listOptions = $.extend({}, options['list'] || {}),
                        ddlLists = cloner.getControl('list_id', item),
                        listMetaCtrl = cloner.getControl('list_meta', item),
                        confirmFieldCtrl = cloner.getControl('confirm_field', item),
                        showCustomFields = cloner.getControl('use_custom_fields', item).is(':checked'),
                        currentMeta = AppHelper.parseJSON(listMetaCtrl.val()),
                        selectedLists = [];

                    cloner.toggleCustomFields(item, showCustomFields);

                    confirmFieldCtrl.select2();

                    if (currentMeta !== null) {
                        if (listOptions['data'] === undefined && currentMeta !== null) {
                            listOptions['data'] = AppHelper.prepareSelect2Data(currentMeta, {text: 'name'});
                        };

                        $.map(currentMeta, function(metaItem) {
                            selectedLists.push(metaItem['id']);
                        });
                    };

                    ddlLists.off('change.cf7c').on('change.cf7c', function() {
                        var selectedLists = ddlLists.val(),
                            currentMeta = AppHelper.parseJSON(listMetaCtrl.val()),
                            meta = {};

                        if (selectedLists !== null) {
                            for (var i = 0; i < selectedLists.length; i++) {
                                var listId = selectedLists[i];

                                meta[listId] = {
                                    id: listId,

                                    name: getListName(listId, currentMeta && currentMeta[listId] ? currentMeta[listId]['name'] : '')
                                };
                            }
                        };

                        listMetaCtrl.val(JSON.stringify(meta));
                        fieldsCloner.resetFieldsLoaded();
                        fieldsCloner.setLists(meta);
                    });

                    if (ddlLists.hasClass('select2-hidden-accessible'))
                        ddlLists.select2('destroy').empty();

                    ddlLists
                        .select2(listOptions)
                        .on('select2:opening', function() {
                            if (!STATE['listsLoaded'] && STATE['apiKey']) {
                                cloner.showListsLoading();

                                setTimeout(function() {
                                    getLists(
                                        STATE['apiKey'],

                                        function(lists) {
                                            changeLists(lists);
                                            ddlLists.select2('open');
                                        },

                                        function(error) {
                                            AppHelper.alert(error);
                                        }
                                    ).always(function() {
                                            cloner.hideListsLoading();
                                        });
                                }, 1);

                                return false;
                            }
                        });

                    if (selectedLists.length > 0) {
                        ddlLists.val(selectedLists).trigger('change');
                    };

                    fieldsCloner.findClonerElements('.ari-cloner-template').each(function() {
                        var fieldsItem = $(this);

                        fieldsCloner.initItem(fieldsItem);
                    });
                },

                applyNewLists: function(lists) {
                    var self = this,
                        listSelect2Data = AppHelper.prepareSelect2Data(lists, {text: 'name'});

                    this.findClonerElements('.ari-cloner-template').each(function() {
                        var item = $(this);

                        self.initItem(item, {list: {data: listSelect2Data}});
                    });
                },

                resetFields: function() {
                    var self = this;

                    this.findClonerElements('.ari-cloner-template').each(function() {
                        var item = $(this),
                            fieldsCloner = self.getChildClonerById(item.attr('id'), 'custom_fields');

                        fieldsCloner.rebindFields();
                    });
                },

                toggleCustomFields: function(item, visible) {
                    if (visible) {
                        item.removeClass('ari-cf7c-mailerlite-customfields-hidden');
                    } else {
                        item.addClass('ari-cf7c-mailerlite-customfields-hidden');
                    }
                },

                reloadLists: function() {
                    if (this.isListsReloading)
                        return ;

                    if (STATE['apiKey']) {
                        var cloner = this;

                        STATE['listsLoaded'] = false;
                        cloner.isListsReloading = true;
                        cloner.showListsLoading();

                        getLists(
                            STATE['apiKey'],

                            function(lists) {
                                changeLists(lists);
                            },

                            function(error) {
                                AppHelper.alert(error);
                            },

                            true
                        ).always(function() {
                                cloner.isListsReloading = false;
                                cloner.hideListsLoading();
                            });

                        return false;
                    }
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
                        enabled: true
                    },

                    scrollTo: {
                        enabled: false,

                        options: {
                            offset: AppHelper.getScrollOffset()
                        }
                    },

                    onInit: function() {
                        var self = this;

                        self.getElement().on('click', '.ari-cf7c-mailerlite-fields-reload', function() {
                            self.reloadFields();

                            return false;
                        });
                    },

                    onAddItem: function(item) {
                        var options = {list: {data: this.getSelect2Fields()}};

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
                        getSelect2Fields: function() {
                            var select2Fields,
                                fields = STATE.fields !== undefined ? STATE.fields : null;

                            if ($.isArray(fields) && fields.length > 0) {
                                select2Fields = AppHelper.prepareSelect2Data(fields, {id: 'id', text: 'name'});
                            } else {
                                select2Fields = [];
                            }

                            return select2Fields
                        },

                        rebindFields: function() {
                            var options = {},
                                self = this;

                            if (this.isFieldsLoaded())
                                options['list'] = {data: this.getSelect2Fields()};

                            this.findClonerElements('.ari-cloner-template').each(function() {
                                var item = $(this);

                                self.initItem(item, options);
                            });
                        },

                        resetFieldsLoaded: function() {
                            this.fieldsLoaded = false;
                        },

                        isFieldsLoaded: function() {
                            return this.fieldsLoaded;
                        },

                        markFieldsLoaded: function() {
                            this.fieldsLoaded = true;
                        },

                        getLists: function() {
                            return this.lists || [];
                        },

                        setLists: function(lists) {
                            this.lists = lists;
                        },

                        isValidField: function(fieldId) {
                            if (!fieldId)
                                return false;

                            return true;
                        },

                        getListFieldsSelect2ContainerList: function() {
                            return this.getElement().find('.ari-cf7c-mailerlite-listfields-container .select2');
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
                                ddlFields = cloner.getControl('list_field_id', item),
                                fieldMetaCtrl = cloner.getControl('list_field_meta', item),
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

                            ddlFields.off('change.cf7c').on('change.cf7c', function() {
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

                                    cloner.showFieldsLoading();

                                    setTimeout(function() {
                                        getFields(
                                            function() {
                                                cloner.markFieldsLoaded();
                                                cloner.rebindFields();

                                                ddlFields.select2('open');
                                            },

                                            function(error) {
                                                AppHelper.alert(error);
                                            },

                                            function() {
                                                cloner.hideFieldsLoading();
                                            }
                                        );
                                    }, 1);

                                    return false;
                                });

                            if (selectedField)
                                ddlFields.val(selectedField).trigger('change');
                            else
                                ddlFields.val('').trigger('change');
                        },

                        reloadFields: function() {
                            var cloner = this;

                            cloner.resetFieldsLoaded();
                            cloner.showFieldsLoading();

                            getFields(
                                function() {
                                    cloner.markFieldsLoaded();
                                    cloner.rebindFields();
                                },

                                function(error) {
                                    AppHelper.alert(error);
                                },

                                function() {
                                    cloner.hideFieldsLoading();
                                },

                                true
                            );
                        }
                    }
                }
            }
        }, AppHelper.getClonerInitData($subscriptionsClonerEl));

    app.form.on('submit', function() {
        AppHelper.saveClonerData(subscriptionsCloner);
    });

    function changeLists(lists) {
        STATE['listsLoaded'] = true;
        STATE['lists'] = lists;

        subscriptionsCloner.applyNewLists(lists);
    };

    function resetFields() {
        STATE.fieldsLoaded = false;
        STATE.fields = null;

        subscriptionsCloner.resetFields();
    };

    app.subscribeEvent('mailerlite_change_key', function(e, apiKey) {
        STATE['apiKey'] = apiKey;
        subscriptionsCloner.reset(true);

        resetFields();

        if (!apiKey) {
            changeLists(null);
            return ;
        };

        subscriptionsCloner.showLoading();

        getLists(
            apiKey,

            function(lists) {
                changeLists(lists);
            },

            function(error) {
                AppHelper.alert(error);
            }
        ).always(function() {
            subscriptionsCloner.hideLoading();
        });
    });
});