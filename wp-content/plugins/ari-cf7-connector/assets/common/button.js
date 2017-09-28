;(function($, undefined) {
    var Button = function(el, options) {
        this.el = $(el);

        this.options = $.extend(
            true,
            {},
            $.fn.ariButton.defaults,
            options || {}
        );

        this.init();
    };

    Button.prototype = {
        el: null,

        options: null,

        inProgress: false,

        constructor: Button,

        init: function() {
            if (this.options.onClick) {
                var self = this;
                this.el.on('click', function() {
                    if (self.inProgress)
                        return false;

                    var result = self.options.onClick.call(self);

                    if (result !== undefined)
                        return result;
                });
            };

            this.el.data('ariButtonCreated', true);
        },

        start: function() {
            this.inProgress = true;
            this.el.addClass(this.options.loadingClass);
        },

        complete: function() {
            this.el.removeClass(this.options.loadingClass);
            this.inProgress = false;
        }
    };

    $.fn.ariButton = function(options) {
        if (typeof(options) == 'string' || options instanceof String) {
            var $this = $(this);

            switch (options) {
                case 'created':
                    return ($this.data('ariButtonCreated') || false);
                    break;

                default:
                    return this;
            }
        }

        var btn = new Button(this, options);

        return btn;
    };

    $.fn.ariButton.defaults = {
        'loadingClass': 'button-in-progress',

        'onClick': null
    };
})(jQuery);