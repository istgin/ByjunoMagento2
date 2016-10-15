/**
 * Icepay_IcpCore Magento JS component
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
                type: 'icepay_icpcore',
                component: 'Icepay_IcpCore/js/view/payment/method-renderer/icpcore-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);