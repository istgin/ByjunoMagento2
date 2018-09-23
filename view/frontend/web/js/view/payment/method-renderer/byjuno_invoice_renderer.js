/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'Magento_Checkout/js/model/quote',
        'jquery'
    ],
    function (ko, Component, url, quote, jquery) {
        'use strict';
        return Component.extend({
            redirectAfterPlaceOrder: false,
            defaults: {
                template: 'Byjuno_ByjunoCore/payment/form_invoice',
                paymentPlan: window.checkoutConfig.payment.byjuno_invoice.default_payment,
                deliveryPlan: window.checkoutConfig.payment.byjuno_invoice.default_delivery,
                customGender: window.checkoutConfig.payment.byjuno_invoice.default_customgender
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'paymentPlan',
                        'deliveryPlan',
                        'customGender'
                    ]);
                return this;
            },

            afterPlaceOrder: function () {
                this.selectPaymentMethod();
                window.location.replace(url.build(window.checkoutConfig.payment.byjuno_invoice.redirectUrl));
            },

            getCode: function () {
                return 'byjuno_invoice';
            }, 
			
            getYearRange: function () {
                var dataYReange = new Date();
                var yRange = dataYReange.getFullYear();
                return '1900:'+yRange;
            },

            getDob: function () {
                var dob  = window.checkoutConfig.quoteData.customer_dob;
                if (dob == null)
                {
                    return ko.observable(false);
                }
                return ko.observable(new Date(dob));
            },

            getEmail: function () {
                if (window.checkoutConfig.quoteData.customer_email != null) {
                    return window.checkoutConfig.quoteData.customer_email;
                } else {
                    return quote.guestEmail;
                }
            },

            getBillingAddress: function () {
                if (quote.billingAddress() == null) {
                    return null;
                }

                if (typeof quote.billingAddress().street === 'undefined' || typeof quote.billingAddress().street[0] === 'undefined') {
                    return null;
                }

                return quote.billingAddress().street[0] + ", " + quote.billingAddress().city + ", " + quote.billingAddress().postcode;
            },

            getData: function () {
                if (this.isFieldsEnabled()) {
                    return {
                        'method': this.item.method,
                        'additional_data': {
                            'invoice_payment_plan': this.paymentPlan(),
                            'invoice_send': this.deliveryPlan(),
                            'invoice_customer_gender': this.customGender(),
                            'invoice_customer_dob': jquery("#customer_dob_invoice").val()
                        }
                    };
                } else {
                    return {
                        'method': this.item.method,
                        'additional_data': {
                            'invoice_payment_plan': this.paymentPlan(),
                            'invoice_send': this.deliveryPlan()
                        }
                    };
                }
            },
            getLogo: function () {
                return window.checkoutConfig.payment.byjuno_invoice.logo;
            },

            getPaymentPlans: function () {
                return _.map(window.checkoutConfig.payment.byjuno_invoice.methods, function (value, key) {
                    return {
                        'value': value.value,
                        'link': value.link,
                        'label': value.name
                    }
                });
            },

            isDeliveryVisibility: function() {
                return window.checkoutConfig.payment.byjuno_invoice.paper_invoice;
            },

            getDeliveryPlans: function () {
                var list = [];
                for (var i = 0; i < window.checkoutConfig.payment.byjuno_invoice.delivery.length; i++) {
                    var value = window.checkoutConfig.payment.byjuno_invoice.delivery[i];
                    if (value.value == 'email') {
                        list.push(
                            {
                                'value': value.value,
                                'label': value.text + this.getEmail()
                            }
                        );
                    } else {
                        if (this.getBillingAddress() != null) {
                            list.push(
                                {
                                    'value': value.value,
                                    'label': value.text + this.getBillingAddress()
                                }
                            );
                        }
                    }
                }
                return list;
            },

            isFieldsEnabled: function () {
                return window.checkoutConfig.payment.byjuno_invoice.isDeliveryVisibility;
            },

            getGenders: function() {
                var list = [];
                for (var i = 0; i < window.checkoutConfig.payment.byjuno_invoice.custom_genders.length; i++) {
                    var value = window.checkoutConfig.payment.byjuno_invoice.custom_genders[i];
                    list.push(
                        {
                            'value': value.value,
                            'label': value.text
                        }
                    );
                }
                return list;
            }

        });
    }
);