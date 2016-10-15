/**
 * Byjuno_ByjunoCore Magento JS component
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'byjuno_byjunocore',
                component: 'Byjuno_ByjunoCore/js/view/payment/method-renderer/byjunocore-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);