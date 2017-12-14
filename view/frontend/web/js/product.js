function showOtherAmountBlock()
{
    if (jQuery('#card-amount').val() == 'other_amount') {
        jQuery('#other_amount').show();
    }
};

function cardAmountChangeAction()
{
    var otherAmount = jQuery('#other_amount');
    if (jQuery('#card-amount').val() == 'other_amount') {
        otherAmount.show();
        otherAmount.addClass('required-entry');
    } else {
        otherAmount.hide();
        otherAmount.removeClass('required-entry');
        jQuery('.warnings #min').hide();
        jQuery('.warnings #max').hide();
    }
};

function otherAmountValidate(from, to)
{
    var otherAmount = jQuery('#other_amount');

    if (otherAmount.val() < from && from > 0) {
        otherAmount.val('');
        otherAmount.addClass('mage-error');
        jQuery('.warnings #max').hide();
        jQuery('.warnings #min').show();
    } else if (otherAmount.val() > to && to < 0) {
        otherAmount.val('');
        otherAmount.addClass('mage-error');
        jQuery('.warnings #min').hide();
        jQuery('.warnings #max').show();
    } else {
        otherAmount.removeClass('mage-error');
        jQuery('.warnings #min').hide();
        jQuery('.warnings #max').hide();
    }
}