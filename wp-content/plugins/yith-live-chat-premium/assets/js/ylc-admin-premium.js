(function ($) {

    /**
     * COLOR PICKER
     */
    $('.panel-colorpicker').wpColorPicker({
        onInit: function () {
            console.log('test');
        },
        change: function (event, ui) {
        },
        clear : function () {
            var input = $(this);
            input.val(input.data('default-color'));
            input.change();
        }
    }).each(function () {
        var select_label = $(this).data('variations-label');
        $(this).parent().parent().find('a.wp-color-result').attr('title', select_label);
    });

    /**
     * NUMBER SPINNER
     */
    $.fn.spinner = function (params) {

        //private methods
        var _createButton = function (buttonClass, buttonLabel) {
            return $('<button/>', {
                'class': buttonClass + ' spinner-button',
                text   : buttonLabel
            });
        };

        var _createBody = function (input) {
            //create wrapper
            var wrapper = input.wrap('<div class="spinner-wrapper"></div>').parent();

            //create spinner buttons
            var plus = _createButton('button-plus', '+').appendTo(wrapper).show(),
                minus = _createButton('button-minus', '-').appendTo(wrapper).show();

            return wrapper;
        };

        var _buttonClick = function (e) {
            var input = e.data.input,
                params = e.data.params,
                button = $(this),
                value = parseFloat(input.val());

            if (button.hasClass('button-plus')) {
                if (params.max != null) {
                    if (( value + params.interval ) <= params.max) {
                        input.val(value + params.interval);
                    } else {
                        input.val(params.max);
                    }
                } else {
                    input.val(value + params.interval);
                }
            } else if (button.hasClass('button-minus')) {
                if (params.min != null) {
                    if (( value - params.interval ) >= params.min) {
                        input.val(value - params.interval);
                    } else {
                        input.val(params.min);
                    }
                } else {
                    input.val(value - params.interval);
                }
            }

            input.change();
            e.preventDefault();
        };

        var _validateContent = function (e) {
            var value = parseFloat($(this).val());

            if (params.max != null && value >= params.max) {
                $(this).val(params.max);
            } else if (value <= params.min || isNaN(value)) {
                $(this).val(params.min ? params.min : 0);
            } else {
                $(this).val(value);
            }
        };


        //public methods
        var methods = {
            init: function (params) {

                var params = $.extend({
                    min          : null,
                    max          : null,
                    interval     : 1,
                    defaultValue : 0,
                    mouseWheel   : true,
                    largeInterval: 10
                }, params);

                var self = this,
                    t = $(this),
                    data = t.data('spinner');

                return this.each(function () {
                    //check if the plugin hasn't already been initialized
                    //and it's an input[type=text] element
                    if (!data && t.is(':text')) {
                        //initialize the value
                        if (params.defaultValue) {
                            t.val(params.defaultValue);
                        }

                        //create the spinner body
                        var wrapper = _createBody(t);

                        //event handlers
                        //var mouseWheelEventName = $.browser.mozilla ? 'DOMMouseScroll' : 'mousewheel';

                        wrapper.find('.spinner-button')
                            .bind('click.spinner', {params: params, input: t}, _buttonClick);

                        t.bind('blur.spinner', _validateContent);
                        //.bind('keyup.spinner', _validateKey)
                        //.bind(mouseWheelEventName, _inputMousewheel);

                        //register field data
                        t.data('spinner', {
                            target: self
                        });
                    }
                });
            },

            destroy: function (params) {
                console.log('destroy', params);
            }
        };

        //execute the plugin
        if (methods[params]) {
            return methods[params].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof params === 'object' || !params) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + params + ' does not exist');
        }
    };

    /**
     * OPERATOR AVATAR MANAGEMENT
     */
    if (typeof wp !== 'undefined' && typeof wp.media !== 'undefined') {

        //upload
        var _custom_media = true,
            _orig_send_attachment = wp.media.editor.send.attachment;

        // preview
        $('#ylc_operator_avatar').change(function () {

            var option = $('option:selected', '#ylc_operator_avatar_type').val();

            if (option == 'image') {

                var url = $(this).val();
                var re = new RegExp("(http|ftp|https)://[a-zA-Z0-9@?^=%&amp;:/~+#-_.]*.(gif|jpg|jpeg|png|ico)");

                var preview = $('.ylc-op-avatar .preview img');
                if (re.test(url)) {
                    preview.attr('src', url)

                } else {
                    preview.attr('src', '');
                }

            }

        }).change();

        $(document).on('click', '#ylc_operator_avatar_button', function (e) {
            var send_attachment_bkp = wp.media.editor.send.attachment;
            var button = $('#ylc_operator_avatar_button');
            var field = $('#ylc_operator_avatar');
            var preview = $('.ylc-op-avatar .preview img');
            _custom_media = true;

            wp.media.editor.send.attachment = function (props, attachment) {
                if (_custom_media) {

                    field.val(attachment.url);
                    preview.attr('src', attachment.url).change();

                } else {

                    return _orig_send_attachment.apply(this, [props, attachment]);

                }

            };

            wp.media.editor.open(button);
            return false;
        });

    }

    $('.ylc-op-avatar .add_media').on('click', function () {
        _custom_media = false;
    });

    /**
     * TIPS
     */
    var tiptip_args = {
        'attribute': 'data-tip',
        'fadeIn'   : 50,
        'fadeOut'  : 50,
        'delay'    : 200
    };

    $('.ylc-tips').tipTip(tiptip_args);

    /**
     * DEPENDENT REQUIRED FIELD
     */
    $('[data-field]').each(function () {
        var t = $(this);

        var field = '#' + t.data('field'),
            dep = '#' + t.data('dep'),
            value = t.data('value');

        $(dep).on('change', function () {
            dependencies_handler_req(field, dep, value.toString());
        }).change();
    });

    //Handle dependencies.
    function dependencies_handler_req(id, deps, values) {
        var result = true;

        //Single dependency
        if (typeof( deps ) == 'string') {
            if (deps.substr(0, 6) == ':radio') {
                deps = deps + ':checked';
            }

            var values = values.split(',');

            for (var i = 0; i < values.length; i++) {

                if ($(deps).val() != values[i]) {
                    result = false;
                }
                else {
                    result = true;
                    break;
                }
            }
        }

        if (!result) {

            $(id + '-container').closest('tr').hide();

            if ($(id).prop('required')) {

                $(id).prop('required', false);
                $(id).data('req', true);

            }

        } else {

            $(id + '-container').closest('tr').show();

            if ($(id).data('req') == true) {

                $(id).prop('required', true);
                $(id).data('req', false);

            }

        }
    }

    function getEnhancedSelectFormatString() {
        var formatString = {
            formatMatches        : function (matches) {
                if (1 === matches) {
                    return ylc.i18n_matches_1;
                }

                return ylc.i18n_matches_n.replace('%qty%', matches);
            },
            formatNoMatches      : function () {
                return ylc.i18n_no_matches;
            },
            formatAjaxError      : function () {
                return ylc.i18n_ajax_error;
            },
            formatInputTooShort  : function (input, min) {
                var number = min - input.length;

                if (1 === number) {
                    return ylc.i18n_input_too_short_1;
                }

                return ylc.i18n_input_too_short_n.replace('%qty%', number);
            },
            formatInputTooLong   : function (input, max) {
                var number = input.length - max;

                if (1 === number) {
                    return ylc.i18n_input_too_long_1;
                }

                return ylc.i18n_input_too_long_n.replace('%qty%', number);
            },
            formatSelectionTooBig: function (limit) {
                if (1 === limit) {
                    return ylc.i18n_selection_too_long_1;
                }

                return ylc.i18n_selection_too_long_n.replace('%qty%', limit);
            },
            formatLoadMore       : function () {
                return ylc.i18n_load_more;
            },
            formatSearching      : function () {
                return ylc.i18n_searching;
            }
        };

        return formatString;
    }

    $(':input.ylc-select').filter(':not(.enhanced)').each(function () {
        var select2_args = $.extend({
            minimumResultsForSearch: 10,
            allowClear             : $(this).data('allow_clear') ? true : false,
            placeholder            : $(this).data('placeholder')
        }, getEnhancedSelectFormatString());

        $(this).select2(select2_args).addClass('enhanced');
    });

    $('.select_all').click(function () {
        $(this).closest('td').find('select option').attr('selected', 'selected');
        $(this).closest('td').find('select').trigger('change');
        return false;
    });

    $('.select_none').click(function () {
        $(this).closest('td').find('select option').removeAttr('selected');
        $(this).closest('td').find('select').trigger('change');
        return false;
    });

    $('#yit_live_chat_options_showing-pages-all').change(function () {

        var pages = $('#yit_live_chat_options_showing-pages-container').parent().parent();

        if ($(this).is(':checked')) {
            pages.hide();
        } else {

            if (!$('#yit_live_chat_options_only-vendor-chat').is(':checked')) {
                pages.show();
            }

        }

    }).change();

    $('#yit_live_chat_options_only-vendor-chat').change(function () {

        var pages_all = $('#yit_live_chat_options_showing-pages-all-container').parent().parent(),
            pages = $('#yit_live_chat_options_showing-pages-container').parent().parent();

        if ($(this).is(':checked')) {
            pages_all.hide();
            pages.hide();
        } else {
            pages_all.show();

            if (!$('#yit_live_chat_options_showing-pages-all').is(':checked')) {
                pages.show();
            }

        }

    }).change();

})(jQuery);
