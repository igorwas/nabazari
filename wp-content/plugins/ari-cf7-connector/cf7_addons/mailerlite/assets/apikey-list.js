;jQuery(document).on('app_ready', function(e, app) {
    var $ = jQuery,
        FV = AppHelper.FormValidation;

    $('.ari-cf7-mailerlite-apikey-list').each(function() {
        var $this = $(this),
            cloner = $this.ariCloner({
                sortable: {
                    enabled: false
                }
            }, AppHelper.getClonerInitData($this));

        $this.on('click', '.btn-check-key', function() {
            var $btn = $(this);

            if (!$btn.ariButton('created')) {
                $btn.ariButton({
                    onClick: function() {
                        var template = $btn.closest('.ari-cloner-template'),
                            dataItem = cloner.getItemData(template),
                            apikeyEl = template.find('[data-cloner-control-key=apikey]'),
                            apiKey = dataItem['apikey'];

                        FV.clear(apikeyEl);

                        if (!apiKey) {
                            FV.setInvalid(apikeyEl, AppHelper.getMessage('mailerlite_key_empty', 'The key can not be empty'));
                            return ;
                        }

                        this.start();

                        var self = this;
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

                                AppHelper.alert(error);
                            }
                        }).fail(function() {
                            AppHelper.alert(AppHelper.getMessage('request_failed'));
                        }).always(function() {
                            self.complete();
                        });
                    }
                });
                $btn.trigger('click');
            };

            return false;
        });

        app.form.on('submit', function() {
            AppHelper.saveClonerData(cloner);
        });
    });
});