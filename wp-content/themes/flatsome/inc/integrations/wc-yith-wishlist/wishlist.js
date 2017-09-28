jQuery( document ).ready(function($) {

    "use strict";

	$('.yith-wcwl-wishlistexistsbrowse.show').parent().parent().prev('.wishlist-button').addClass('wishlist-added');

    var wishlistButton = $('.wishlist-button');
    var wishlistPopup = $('.wishlist-popup .yith-wcwl-add-button');

	$(wishlistButton || wishlistPopup).on('click', function(e){

        if($(this).parent().find('.yith-wcwl-wishlistexistsbrowse').hasClass('show')){
            var link = $(this).parent().find('.yith-wcwl-wishlistexistsbrowse a').attr('href');
            window.location.href = link;
            return;
        }

        $(this).addClass('wishlist-added');
        $(this).addClass('loading');
        $(this).parent().find('.add_to_wishlist').click();
        e.preventDefault();
    });

    var flatsome_add_to_wishlist = function() {
        $('.wishlist-button').removeClass('loading');

        $.ajax({
            beforeSend: function () {

            },
            complete  : function () {

            },
            data      : {
                action: 'flatsome_update_wishlist_count'
            },
            success   : function (data) {
                $('i.wishlist-icon').addClass('added');
                if(data == 0){
                    $('i.wishlist-icon').removeAttr('iconLabel');
                } else if(data == 1){
                    $('i.wishlist-icon').attr('data-icon-label','1');
                } else {
                    $('i.wishlist-icon').attr('data-icon-label',data);
                }
                setTimeout(function(){
                    $('i.wishlist-icon').removeClass('added');
                }, 500);
            },

            url: yith_wcwl_l10n.ajax_url
        });
    };

    $('body').on( 'added_to_wishlist removed_from_wishlist', flatsome_add_to_wishlist);
});