/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/view/messages',
    'MW_Affiliate/js/model/payment/messages'
], function (Component, messageContainer) {
    'use strict';

    return Component.extend({
        initialize: function (config) {
            return this._super(config, messageContainer);
        }
    });
});
