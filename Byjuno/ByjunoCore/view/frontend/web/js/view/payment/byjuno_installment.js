/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
                type: 'byjuno_installment',
                component: 'Byjuno_ByjunoCore/js/view/payment/method-renderer/byjuno_installment_renderer'
            }
        );
        console.log('yyyy');
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
