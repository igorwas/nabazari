;(function($) {
    var cf7ElementsSelector = '#wpcf7-admin-form-element :input[type!="hidden"]',
        onHandler = $.fn.on;

    $.fn.on = function() {
        if (arguments.length == 2 && arguments[0] == 'beforeunload' && $.isFunction(arguments[1]) && (arguments[1]).toString().indexOf(cf7ElementsSelector) !== -1) {
            var args = Array.prototype.slice.call(arguments),
                handler = (args[1]).toString();

            handler = handler.replace(cf7ElementsSelector, '#wpcf7-admin-form-element .contact-form-editor-panel:not([id^="cf7connector"]) :input[type!="hidden"]');
            eval('handler = ' + handler);
            args[1] = handler;

            return onHandler.apply(this, args);
        }

        return onHandler.apply(this, arguments);
    };

    $(document).on('app_ready', function(e, app, undefined) {
        function insertIntoPosition(el, content) {
            var $el = $(el),
                val = $el.val(),
                caretPos = el.selectionStart || 0;

            $(el).val(val.substring(0, caretPos) + content + val.substring(caretPos));

            if (el.setSelectionRange) {
                var newPos = caretPos + content.length;
                el.setSelectionRange(newPos, newPos);
            }
        };

        var CF7_CONTROLS = {
            'tabs': $('#contact-form-editor'),

            'formControl': $('#wpcf7-form')
        };

        CF7_CONTROLS.tabs.on('click', '.ari-cf7c-select-all', function() {
            var el = this;
            if (document.selection) {
                var range = document.body.createTextRange();
                range.moveToElementText(el);
                range.select();
            } else if (window.getSelection) {
                var range = document.createRange();
                range.selectNode(el);//range.selectNodeContents( node );
                window.getSelection().removeAllRanges();
                window.getSelection().addRange(range);
            }
        });

        CF7_CONTROLS.tabs.on('click', '.ari-cf7c-insert-content', function() {
            var $this = $(this),
                content = $this.attr('data-insert-content'),
                srcControl = $this.attr('data-insert-control');

            if (content === null || content === '')
                return ;

            if (!srcControl) {
                var parentControl = $this.attr('data-insert-parent-control');
                if (parentControl) {
                    parentControl = $(parentControl);
                    if (parentControl.length > 0) {
                        srcControl = $this.attr('data-insert-control');
                        if (srcControl) {
                            srcControl = parentControl.find(srcControl);
                        }
                    }
                }
            } else {
                srcControl = $(srcControl);
            }

            if (!srcControl || srcControl.length == 0)
                return ;

            insertIntoPosition(srcControl.get(0), content);
        });

        $(document).trigger('cf7c_loaded', [app]);
    });
})(jQuery);