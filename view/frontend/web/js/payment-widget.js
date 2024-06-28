define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    return Component.extend({
        jwtWidgetUrl: '',
        customStyles: '',
        widgetJwtData: false,
        initialize: function () {
            this._super();
            let that = this;
            let sections = ['avarda-payment-widget-jwt'];
            customerData.getInitCustomerData().done(function () {
                that.widgetJwtData = customerData.get('avarda-payment-widget-jwt');
                let x = that.widgetJwtData();
                // Check that if widget JWT is not set or if it is expired
                if ((!x || typeof x === 'object' && !Object.keys(x).length) || (that.widgetJwtData().expiredUtc - Math.floor(Date.now() / 1000)) < 0) {
                    // Refresh widget JWT from server
                    customerData.invalidate(sections);
                    customerData.reload(sections, true).done(function () {
                        that.showPaymentWidget();
                    });
                } else {
                    that.showPaymentWidget();
                }
            });
        },
        showPaymentWidget: function () {
            let s = document.createElement('script');
            s.src = this.jwtWidgetUrl;
            s.type = 'text/javascript';
            s.crossorigin = 'anonymous';
            s.async = true;
            s.dataset.paymentId = this.widgetJwtData().paymentId;
            s.dataset.widgetJwt = this.widgetJwtData().widgetJwt;
            s.dataset.customStyles = this.customStyles;
            document.head.appendChild(s);
        }
    });
});
