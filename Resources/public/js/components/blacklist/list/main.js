/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define(['jquery', 'underscore'], function($, _) {

    'use strict';

    return {

        stickyToolbar: true,

        defaults: {
            templates: {
                overlayContainer: '<div/>',
                skeleton: '<div><div class="list-toolbar-container"/><div class="list-datagrid-container"/></div>',
                url: '/admin/api/blacklist-items<% if(typeof id !== "undefined") { %>/<%= id %><% } %>'
            },
            translations: {
                typeRequest: 'community.blacklist.request',
                typeBlock: 'community.blacklist.block'
            }
        },

        layout: {
            content: {
                width: 'max'
            }
        },

        header: function() {
            return {
                noBack: true,

                title: 'community.blacklist.title',
                underline: false,

                toolbar: {
                    buttons: {
                        add: {
                            options: {
                                callback: this.addHandler.bind(this)
                            }
                        },
                        deleteSelected: {},
                        export: {
                            options: {
                                urlParameter: {
                                    flat: true
                                },
                                url: this.templates.url() + '.csv'
                            }
                        }
                    }
                }
            };
        },

        initialize: function() {
            this.$el.html(this.templates.skeleton());

            // init list-toolbar and datagrid
            this.sandbox.sulu.initListToolbarAndList.call(this, 'blacklist-items', this.templates.url({id: 'fields'}),
                {
                    el: this.$find('.list-toolbar-container'),
                    template: 'default',
                    listener: 'default',
                    instanceName: 'blacklist-item'
                },
                {
                    el: this.$find('.list-datagrid-container'),
                    url: '/admin/api/blacklist-items',
                    resultKey: 'items',
                    searchFields: ['pattern'],
                    instanceName: 'blacklist-item',
                    viewOptions: {
                        table: {
                            editable: true,
                            editableOptions: {
                                type: {
                                    values: {
                                        request: this.translations.typeRequest,
                                        block: this.translations.typeBlock
                                    }
                                }
                            }
                        }
                    }
                }
            );

            // delete clicked
            this.sandbox.on('sulu.toolbar.delete', function() {
                this.sandbox.emit('husky.datagrid.blacklist-item.items.get-selected', this.deleteItems.bind(this));
            }, this);

            // checkbox clicked
            this.sandbox.on('husky.datagrid.blacklist-item.number.selections', function(number) {
                var postfix = number > 0 ? 'enable' : 'disable';
                this.sandbox.emit('husky.toolbar.header.item.' + postfix, 'deleteSelected', false);
            }.bind(this));
        },

        addHandler: function() {
            this.sandbox.emit('husky.datagrid.blacklist-item.record.add', {
                id: '',
                pattern: '',
                type: 'request'
            });
        },

        deleteItems: function(ids) {
            $.ajax(this.templates.url(), {method: 'DELETE', data: {ids: ids.join(',')}}).then(function() {
                _.each(ids, function(id) {
                    this.sandbox.emit('husky.datagrid.blacklist-item.record.remove', id);
                }.bind(this));
            }.bind(this));
        }
    };
});
