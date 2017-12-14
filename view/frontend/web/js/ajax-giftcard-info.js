define([
    'jquery',
    'underscore',
    'mage/template',
    "jquery/ui",
    'mage/validation'
], function ($, _, template
) {
    "use strict";

    $.widget('mage.ajaxGiftCardInfo', {
        options: {
        },
        _create: function () {
            this.giftCardForm = $(this.options.giftCardFormSelector);
            this.giftCardInputField = $(this.options.giftCardInputFieldSelector);

            $(this.options.applyButton).on('click', $.proxy(function () {
                this.giftCardInputField.attr('data-validate', '{required:true}');

                var formValidation = this.giftCardForm.validation();
                //formValidation.mage('validation', {});

                if (formValidation.validation('isValid')) {
                    var param = 'ajax=1&giftcard_code=' + this.giftCardInputField.attr("value");

                    $.ajax({
                        showLoader: true,
                        url: this.options.ajaxUrl,
                        data: param,
                        type: "POST",
                        dataType: 'json'
                    }).done(function (data) {
                        var html;
                        if (data.success) {
                            html = template(this.options.infoTemplate, {data: data});
                        } else {
                            html = template(this.options.errorTemplate, {data: data});
                        }
                        $(this.options.infoPlaceholder).html(html);
                    }.bind(this));
                }
            }, this));
        }
    });

    return $.mage.ajaxGiftCardInfo;
});