define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function (widget) {
        $.widget('mage.priceBundle', widget, {
            updateProductSummary: function () {
                this._super();
                this.setPaymentWidgetPrice();
            },

            setPaymentWidgetPrice: function () {
                const priceBox = $('.price-configured_price');
                let price = priceBox.find('.price').text();

                price = price.replace(/[^0-9\,]/g, '').replace(',', '.');

                const paymentWidget = $('#avarda-payment-widget');
                if (paymentWidget.length) {
                    paymentWidget.attr('price', price);
                }
            }
        });

        return $.mage.priceBundle;
    }
});
