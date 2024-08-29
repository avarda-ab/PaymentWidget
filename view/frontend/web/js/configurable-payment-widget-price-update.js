define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function (widget) {
        $.widget('mage.configurable', widget, {
            setPaymentWidgetPrice: function () {
                let selectedProduct = this.simpleProduct;
                let selectedPrice;

                if (selectedProduct && this.options.spConfig.optionPrices[selectedProduct].finalPrice.amount) {
                    selectedPrice = this.options.spConfig.optionPrices[selectedProduct].finalPrice.amount;
                } else {
                    selectedPrice = Math.min(...Object.values(this.options.spConfig.optionPrices).map(price => price.finalPrice.amount));
                }

                const paymentWidget = $('#avarda-payment-widget');

                if (paymentWidget.length) {
                    paymentWidget.attr('price', selectedPrice);
                }
            }
        });

        widget.prototype._reloadPrice = wrapper.wrap(widget.prototype._reloadPrice, function(original) {
            this.setPaymentWidgetPrice();
            return original();
        });

        return $.mage.configurable;
    }
});
