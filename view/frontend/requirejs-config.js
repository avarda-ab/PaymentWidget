var config = {
    config: {
        mixins: {
            'Magento_Swatches/js/swatch-renderer': {
                'Avarda_PaymentWidget/js/swatch-payment-widget-price-update': true
            },
            'Magento_ConfigurableProduct/js/configurable': {
                'Avarda_PaymentWidget/js/configurable-payment-widget-price-update': true
            },
            'Magento_Bundle/js/price-bundle': {
                'Avarda_PaymentWidget/js/bundle-payment-widget-price-update': true
            }
        }
    }
};
