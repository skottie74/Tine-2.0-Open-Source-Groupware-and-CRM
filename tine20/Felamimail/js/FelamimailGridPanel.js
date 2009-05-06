/**
 * Tine 2.0
 * 
 * @package     Felamimail
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Philipp Schuele <p.schuele@metaways.de>
 * @copyright   Copyright (c) 2007-2009 Metaways Infosystems GmbH (http://www.metaways.de)
 * @version     $Id:GridPanel.js 7170 2009-03-05 10:58:55Z p.schuele@metaways.de $
 *
 * @todo        add actions (reply, ...)
 * @todo        add dragndrop
 * @todo        add header to preview
 * @todo        add attachments
 * @todo        add more filters (to/cc/date...)
 */
 
Ext.namespace('Tine.Felamimail');

/**
 * Message grid panel
 */
Tine.Felamimail.GridPanel = Ext.extend(Tine.Tinebase.widgets.app.GridPanel, {
    // model generics
    recordClass: Tine.Felamimail.Model.Message,
    evalGrants: false,
    
    // grid specific
    defaultSortInfo: {field: 'received', direction: 'DESC'},
    gridConfig: {
        loadMask: true,
        autoExpandColumn: 'subject',
        // drag n drop
        enableDragDrop: true,
        ddGroup: 'mailToTreeDDGroup'
    },
    
    /**
     * Return CSS class to apply to rows depending upon flags
     * - checks Flagged, Deleted and Seen
     * 
     * @param {} record
     * @param {} index
     * @return {String}
     */
    getViewRowClass: function(record, index) {
        var flags = record.get('flags');
        var className = '';
        if(flags !== null) {
            if (flags.match(/Flagged/)) {
                className += ' flag_flagged';
            }
            if (flags.match(/Deleted/)) {
                className += ' flag_deleted';
            }
        }
        if (flags === null || !flags.match(/Seen/)) {
            className += ' flag_unread';
        }
        return className;
    },
    
    /**
     * init message grid
     */
    initComponent: function() {
        this.recordProxy = Tine.Felamimail.messageBackend;
        
        this.gridConfig.columns = this.getColumns();
        this.initFilterToolbar();
        this.initDetailsPanel();
        
        this.plugins = this.plugins || [];
        this.plugins.push(this.filterToolbar);        
        
        Tine.Felamimail.GridPanel.superclass.initComponent.call(this);
        
        //this.action_addInNewWindow.setDisabled(! Tine.Tinebase.common.hasRight('manage', 'Felamimail', 'records'));
        //this.action_editInNewWindow.requiredGrant = 'editGrant';
        
        this.grid.getSelectionModel().on('rowselect', function(selModel, rowIndex, r) {
            // toggle read/seen flag of mail (only if 1 selected row)
            if (selModel.getCount() == 1) {
                Ext.get(this.grid.getView().getRow(rowIndex)).removeClass('flag_unread');
            }
        }, this);
    },
    
    /**
     * init actions with actionToolbar, contextMenu and actionUpdater
     * 
     * @private
     */
    initActions: function() {

        this.action_deleteRecord = new Ext.Action({
            requiredGrant: 'deleteGrant',
            allowMultiple: true,
            singularText: this.app.i18n._('Delete'),
            pluralText: this.app.i18n._('Delete'),
            translationObject: this.i18nDeleteActionText ? this.app.i18n : Tine.Tinebase.tranlation,
            text: this.app.i18n._('Delete'),
            handler: this.onDeleteRecords,
            disabled: true,
            iconCls: 'action_delete',
            scope: this
        });
        
        this.actions = [
            this.action_deleteRecord
        ];
        
        this.actionToolbar = new Ext.Toolbar({
            split: false,
            height: 26,
            items: this.actions
        });
        
        this.contextMenu = new Ext.menu.Menu({
            items: this.actions.concat(this.contextMenuItems)
        });
        
        // pool together all our actions, so that we can hand them over to our actionUpdater
        for (var all=this.actionToolbarItems.concat(this.contextMenuItems), i=0; i<all.length; i++) {
            if(this.actions.indexOf(all[i]) == -1) {
                this.actions.push(all[i]);
            }
        }
        
        /*
        this.action_editInNewWindow = new Ext.Action({
            requiredGrant: 'readGrant',
            text: this.i18nEditActionText ? this.app.i18n._hidden(this.i18nEditActionText) : String.format(_('Edit {0}'), this.i18nRecordName),
            disabled: true,
            actionType: 'edit',
            handler: this.onEditInNewWindow,
            iconCls: 'action_edit',
            scope: this
        });
        
        this.action_addInNewWindow = new Ext.Action({
            requiredGrant: 'addGrant',
            actionType: 'add',
            text: this.i18nAddActionText ? this.app.i18n._hidden(this.i18nAddActionText) : String.format(_('Add {0}'), this.i18nRecordName),
            handler: this.onEditInNewWindow,
            iconCls: this.app.appName + 'IconCls',
            scope: this
        });
                
        this.actions = [
            this.action_addInNewWindow,
            this.action_editInNewWindow,
            this.action_deleteRecord
        ];
        
        this.actionToolbar = new Ext.Toolbar({
            split: false,
            height: 26,
            items: this.actions.concat(this.actionToolbarItems)
        });
        
        this.contextMenu = new Ext.menu.Menu({
            items: this.actions.concat(this.contextMenuItems)
        });
        
        // pool together all our actions, so that we can hand them over to our actionUpdater
        for (var all=this.actionToolbarItems.concat(this.contextMenuItems), i=0; i<all.length; i++) {
            if(this.actions.indexOf(all[i]) == -1) {
                this.actions.push(all[i]);
            }
        }
        */
    },
    
    /**
     * initialises filter toolbar
     */
    initFilterToolbar: function() {
        this.filterToolbar = new Tine.widgets.grid.FilterToolbar({
            filterModels: [
                {label: this.app.i18n._('Subject'),    field: 'subject',       operators: ['contains']}
                // @todo add filters
                /*
                {label: this.app.i18n._('Message'),    field: 'query',       operators: ['contains']},
                {label: this.app.i18n._('Description'),    field: 'description', operators: ['contains']},
                new Tine.Felamimail.TimeAccountStatusGridFilter({
                    field: 'status'
                }),
                */
                //new Tine.widgets.tags.TagFilter({app: this.app})
             ],
             defaultFilter: 'subject',
             filters: []
        });
    },    
    
    /**
     * the details panel (shows message content)
     * 
     */
    initDetailsPanel: function() {
        this.detailsPanel = new Tine.widgets.grid.DetailsPanel({
            defaultHeight: 300,
            gridpanel: this,
            currentId: null,
            
            updateDetails: function(record, body) {
                if (record.id !== this.currentId) {
                    this.currentId = record.id;
                    Tine.Felamimail.messageBackend.loadRecord(record, {
                        scope: this,
                        success: function(message) {
                            record.data.body = message.data.body;                            
                            record.data.flags = message.data.flags;
                            
                            this.tpl.overwrite(body, message.data);
                            this.getEl().down('div').down('div').scrollTo('top', 0, false);
                            this.getLoadMask().hide();
                            
                            // toggle read/seen flag of mail
                            //Ext.get(this.grid.getView().getRow(rowIndex)).removeClass('flag_unread');
                        }
                    });
                    this.getLoadMask().show();
                } else {
                    this.tpl.overwrite(body, record.data);
                }
            },

            tpl: new Ext.XTemplate(
                '<div class="preview-panel-felamimail-body">',
                    //'<tpl for="Body">',
                            '<div class="Mail-Body-Content">{[this.encode(values.body)]}</div>',
                    // '</tpl>',
                '</div>',{
                
                encode: function(value, type, prefix) {
                    if (value) {
                        /*
                        if (type) {
                            switch (type) {
                                case 'longtext':
                                    value = Ext.util.Format.ellipsis(value, 150);
                                    break;
                                default:
                                    value += type;
                            }                           
                        }
                        */
                        
                        var encoded = Ext.util.Format.htmlEncode(value);
                        encoded = Ext.util.Format.nl2br(encoded);
                        
                        return encoded;
                    } else {
                        return '';
                    }
                    return value;
                }
            }),
            
            // use default Tpl for default and multi view
            defaultTpl: new Ext.XTemplate(
                '<div class="preview-panel-felamimail-body">',
                    '<div class="Mail-Body-Content"></div>',
                '</div>'
            )
        });
    },
    
    /**
     * returns cm
     * @private
     */
    getColumns: function(){
        return [{
            id: 'id',
            header: this.app.i18n._("Id"),
            width: 100,
            sortable: true,
            dataIndex: 'id',
            hidden: true
        }, {
            id: 'flags',
            //header: this.app.i18n._("Status"),
            width: 16,
            sortable: true,
            dataIndex: 'flags',
            renderer: Tine.Felamimail.getFlagIcon
        },{
            id: 'subject',
            header: this.app.i18n._("Subject"),
            width: 300,
            sortable: true,
            dataIndex: 'subject'
        },{
            id: 'from',
            header: this.app.i18n._("From"),
            width: 150,
            sortable: true,
            dataIndex: 'from'
            //renderer: this.statusRenderer.createDelegate(this)
        },{
            id: 'to',
            header: this.app.i18n._("To"),
            width: 150,
            sortable: true,
            dataIndex: 'to',
            hidden: true
        },{
            id: 'sent',
            header: this.app.i18n._("Sent"),
            width: 150,
            sortable: true,
            dataIndex: 'sent',
            hidden: true,
            renderer: Tine.Tinebase.common.dateTimeRenderer
        },{
            id: 'received',
            header: this.app.i18n._("Received"),
            width: 150,
            sortable: true,
            dataIndex: 'received',
            renderer: Tine.Tinebase.common.dateTimeRenderer
        },{
            id: 'size',
            header: this.app.i18n._("Size"),
            width: 80,
            sortable: true,
            dataIndex: 'size',
            hidden: true
        }];
    },
    
    /**
     * status column renderer
     * @param {string} value
     * @return {string}
     */
    statusRenderer: function(value) {
        return this.app.i18n._hidden(value);
    }
});
