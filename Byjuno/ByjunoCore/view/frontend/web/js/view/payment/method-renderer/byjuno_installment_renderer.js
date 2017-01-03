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
                template: 'Byjuno_ByjunoCore/payment/form_installment',
                paymentPlan: window.checkoutConfig.payment.byjuno_installment.default_payment,
                deliveryPlan: window.checkoutConfig.payment.byjuno_installment.default_delivery,
                customGender: window.checkoutConfig.payment.byjuno_installment.default_customgender
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
                window.location.replace(url.build(window.checkoutConfig.payment.byjuno_installment.redirectUrl));
            },


            getCode: function () {
                return 'byjuno_installment';
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
                return quote.billingAddress().street[0] + ", " + quote.billingAddress().city + ", " + quote.billingAddress().postcode;
            },

            getData: function () {
                if (this.isFieldsEnabled()) {
                    return {
                        'method': this.item.method,
                        'additional_data': {
                            'installment_payment_plan': this.paymentPlan(),
                            'installment_send': this.deliveryPlan(),
                            'installment_customer_gender': this.customGender(),
                            'installment_customer_dob': jquery("#customer_dob_installment").val()
                        }
                    };
                } else {
                    return {
                        'method': this.item.method,
                        'additional_data': {
                            'installment_payment_plan': this.paymentPlan(),
                            'installment_send': this.deliveryPlan()
                        }
                    };
                }
            },
            getLogo: function () {
                return window.checkoutConfig.payment.byjuno_installment.logo;
            },

            getPaymentPlans: function () {
                return _.map(window.checkoutConfig.payment.byjuno_installment.methods, function (value, key) {
                    return {
                        'value': value.value,
                        'link': value.link,
                        'label': value.name
                    }
                });
            },

            getDeliveryPlans: function () {
                var list = [];
                for (var i = 0; i < window.checkoutConfig.payment.byjuno_installment.delivery.length; i++) {
                    var value = window.checkoutConfig.payment.byjuno_installment.delivery[i];
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
                return window.checkoutConfig.payment.byjuno_installment.enable_fields;
            },

            getGenders: function() {
                var list = [];
                for (var i = 0; i < window.checkoutConfig.payment.byjuno_installment.custom_genders.length; i++) {
                    var value = window.checkoutConfig.payment.byjuno_installment.custom_genders[i];
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