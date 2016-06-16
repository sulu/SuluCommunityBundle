require.config({
    paths: {
        sulucommunity: '../../sulucommunity/js',
        sulucommunitycss: '../../sulucommunity/css'
    }
});

define(function() {

    'use strict';

    return {

        name: "Sulu Community Bundle",

        initialize: function(app) {

            app.components.addSource('sulucommunity', '/bundles/sulucommunity/js/components');

        }
    };
});
