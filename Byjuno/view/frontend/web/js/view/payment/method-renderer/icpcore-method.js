/**
 * Icepay_IcpCore Magento JS component
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Payment/js/view/payment/cc-form'
    ],
    function (Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Icepay_IcpCore/payment/icpcore-form'
            },

            getCode: function() {
                return 'icepay_icpcore';
            },

            isActive: function() {
                return true;
            }
        });
    }
);
