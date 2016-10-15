/**
 * Byjuno_ByjunoCore Magento JS component
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
                template: 'Byjuno_ByjunoCore/payment/byjunocore-form'
            },

            getCode: function() {
                return 'byjuno_byjunocore';
            },

            isActive: function() {
                return true;
            }
        });
    }
);
