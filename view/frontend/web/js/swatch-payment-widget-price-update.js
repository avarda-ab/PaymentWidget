define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            setPaymentWidgetPrice: function () {
                let selectedProduct = this.getProduct();

                if (selectedProduct && this.options.jsonConfig.optionPrices[selectedProduct].finalPrice.amount) {
                    const selectedPrice = this.options.jsonConfig.optionPrices[selectedProduct].finalPrice.amount;
                    const paymentWidget = $('#avarda-payment-widget');

                    if (paymentWidget.length) {
                        paymentWidget.attr('price', selectedPrice);
                    }
                }
            },

            setDefaultPaymentWidgetPrice: function () {
                let selectedProduct = this.getProduct();
                let selectedPrice;

                if (selectedProduct && this.options.jsonConfig.optionPrices[selectedProduct].finalPrice.amount) {
                    selectedPrice = this.options.jsonConfig.optionPrices[selectedProduct].finalPrice.amount;
                } else {
                    selectedPrice = Math.min(...Object.values(this.options.jsonConfig.optionPrices).map(price => price.finalPrice.amount));
                }

                const paymentWidget = $('#avarda-payment-widget');

                if (paymentWidget.length) {
                    paymentWidget.attr('price', selectedPrice);
                }
            }
        });

        widget.prototype._UpdatePrice = wrapper.wrap(widget.prototype._UpdatePrice, function(original) {
            this.setPaymentWidgetPrice();
            return original();
        });

        widget.prototype._RenderSwatchSelect = wrapper.wrap(widget.prototype._RenderSwatchSelect, function(original) {
            this.setDefaultPaymentWidgetPrice();
            return original();
        });

        return $.mage.SwatchRenderer;
    }
});
