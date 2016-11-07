/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'mage/url'
    ],
    function (Component, url) {
        'use strict';
        return Component.extend({
            redirectAfterPlaceOrder: false,
            defaults: {
                template: 'Byjuno_ByjunoCore/payment/form_invoice',
                paymentPlan: window.checkoutConfig.payment.byjuno_invoice.default_payment
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'paymentPlan'
                    ]);
                return this;
            },

            afterPlaceOrder: function () { this.selectPaymentMethod();
                window.location.replace(url.build(window.checkoutConfig.payment.byjuno_invoice.redirectUrl));
            },

            getCode: function() {
                return 'byjuno_invoice';
            },

            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'payment_plan': this.paymentPlan()
                    }
                };
            },

            selectPaymentPlan: function() {

            },

            getPaymentPlans: function() {
                return _.map(window.checkoutConfig.payment.byjuno_invoice.methods, function(value, key) {
                    return {
                        'value': value.value,
                        'link': value.link,
                        'label': value.name
                    }
                });
            }
        });
    }
);