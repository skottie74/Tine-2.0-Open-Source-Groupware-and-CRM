Ext.namespace('Tine.Admin.Groups');
Tine.Admin.Groups.Main = {
    
    
    actions: {
        addGroup: null,
        editGroup: null,
        deleteGroup: null,
    },
    
    handlers: {
        /**
         * onclick handler for addBtn
         */
        addGroup: function(_button, _event) {
            Tine.Tinebase.Common.openWindow('contactWindow', "index.php?method=Admin.editGroup&groupId=''", 400, 300);
        },

        /**
         * onclick handler for editBtn
         */
        editGroup: function(_button, _event) {
            var selectedRows = Ext.getCmp('AdminGroupsGrid').getSelectionModel().getSelections();
            var groupId = selectedRows[0].id;
            
            Tine.Tinebase.Common.openWindow('contactWindow', 'index.php?method=Admin.editGroup&groupId=' + groupId, 400, 300);
        },

        
        /**
         * onclick handler for deleteBtn
         */
        deleteGroup: function(_button, _event) {
            Ext.MessageBox.confirm('Confirm', 'Do you really want to delete the selected groups?', function(_button){
                if (_button == 'yes') {
                
                    var groupIds = new Array();
                    var selectedRows = Ext.getCmp('AdminGroupsGrid').getSelectionModel().getSelections();
                    for (var i = 0; i < selectedRows.length; ++i) {
                        groupIds.push(selectedRows[i].id);
                    }
                    
                    groupIds = Ext.util.JSON.encode(groupIds);
                    
                    Ext.Ajax.request({
                        url: 'index.php',
                        params: {
                            method: 'Admin.deleteGroups',
                            groupIds: groupIds
                        },
                        text: 'Deleting contact(s)...',
                        success: function(_result, _request){
                            Ext.getCmp('AdminGroupsGrid').getStore().reload();
                        },
                        failure: function(result, request){
                            Ext.MessageBox.alert('Failed', 'Some error occured while trying to delete the conctact.');
                        }
                    });
                }
            });
        }    
    },
    
    initComponent: function()
    {
        this.actions.addGroup = new Ext.Action({
            text: 'add group',
            handler: this.handlers.addGroup,
            iconCls: 'action_addGroup',
            scope: this
        });
        
        this.actions.editGroup = new Ext.Action({
            text: 'edit group',
            disabled: true,
            handler: this.handlers.editGroup,
            iconCls: 'action_edit',
            scope: this
        });
        
        this.actions.deleteGroup = new Ext.Action({
            text: 'delete group',
            disabled: true,
            handler: this.handlers.deleteGroup,
            iconCls: 'action_delete',
            scope: this
        });

    },
    
    displayGroupsToolbar: function()
    {
        var quickSearchField = new Ext.app.SearchField({
            id: 'quickSearchField',
            width:240,
            emptyText: 'enter searchfilter'
        }); 
        quickSearchField.on('change', function(){
            Ext.getCmp('AdminGroupsGrid').getStore().load({
                params: {
                    start: 0,
                    limit: 50
                }
            });
        }, this);
        
        var groupsToolbar = new Ext.Toolbar({
            id: 'AdminGroupsToolbar',
            split: false,
            height: 26,
            items: [
                this.actions.addGroup, 
                this.actions.editGroup,
                this.actions.deleteGroup,
                '->', 
                'Search:', 
                ' ',
                quickSearchField
            ]
        });

        Tine.Tinebase.MainScreen.setActiveToolbar(groupsToolbar);
    },

    displayGroupsGrid: function() 
    {
        // the datastore
        var dataStore = new Ext.data.JsonStore({
            baseParams: {
                method: 'Admin.getGroups'
            },
            root: 'results',
            totalProperty: 'totalcount',
            id: 'id',
            fields: Tine.Admin.Model.Group,
            // turn on remote sorting
            remoteSort: true
        });
        
        dataStore.setDefaultSort('id', 'asc');

        dataStore.on('beforeload', function(_dataStore) {
            _dataStore.baseParams.filter = Ext.getCmp('quickSearchField').getRawValue();
        }, this);        
        
        // the paging toolbar
        var pagingToolbar = new Ext.PagingToolbar({
            pageSize: 25,
            store: dataStore,
            displayInfo: true,
            displayMsg: 'Displaying groups {0} - {1} of {2}',
            emptyMsg: "No groups to display"
        }); 
        
        // the columnmodel
        var columnModel = new Ext.grid.ColumnModel([
            { resizable: true, id: 'id', header: 'ID', dataIndex: 'id', width: 10 },
            { resizable: true, id: 'name', header: 'Name', dataIndex: 'name', width: 50 },
            { resizable: true, id: 'description', header: 'Description', dataIndex: 'description' },
        ]);
        
        columnModel.defaultSortable = true; // by default columns are sortable
        
        // the rowselection model
        var rowSelectionModel = new Ext.grid.RowSelectionModel({multiSelect:true});

        rowSelectionModel.on('selectionchange', function(_selectionModel) {
            var rowCount = _selectionModel.getCount();

            if(rowCount < 1) {
                // no row selected
                this.actions.deleteGroup.setDisabled(true);
                this.actions.editGroup.setDisabled(true);
            } else if(rowCount > 1) {
                // more than one row selected
                this.actions.deleteGroup.setDisabled(false);
                this.actions.editGroup.setDisabled(true);
            } else {
                // only one row selected
                this.actions.deleteGroup.setDisabled(false);
                this.actions.editGroup.setDisabled(false);
            }
        }, this);
        
        // the gridpanel
        var gridPanel = new Ext.grid.GridPanel({
            id: 'AdminGroupsGrid',
            store: dataStore,
            cm: columnModel,
            tbar: pagingToolbar,     
            autoSizeColumns: false,
            selModel: rowSelectionModel,
            enableColLock:false,
            loadMask: true,
            autoExpandColumn: 'n_family',
            border: false,
            view: new Ext.grid.GridView({
                autoFill: true,
                forceFit:true,
                ignoreAdd: true,
                emptyText: 'No groups to display'
            })            
            
        });
        
        gridPanel.on('rowcontextmenu', function(_grid, _rowIndex, _eventObject) {
            _eventObject.stopEvent();
            if(!_grid.getSelectionModel().isSelected(_rowIndex)) {
                _grid.getSelectionModel().selectRow(_rowIndex);
            }
            var contextMenu = new Ext.menu.Menu({
                id:'ctxMenuGroups', 
                items: [
                    this.actions.editGroup,
                    this.actions.deleteGroup,
                    '-',
                    this.actions.addGroup 
                ]
            });
            contextMenu.showAt(_eventObject.getXY());
        }, this);
        
        gridPanel.on('rowdblclick', function(_gridPar, _rowIndexPar, ePar) {
            var record = _gridPar.getStore().getAt(_rowIndexPar);
            //console.log('id: ' + record.data.id);
            try {
                Tine.Tinebase.Common.openWindow('contactWindow', 'index.php?method=Admin.editGroup&groupId=' + record.data.id, 400, 300);
            } catch(e) {
                // alert(e);
            }
        }, this);

        // add the grid to the layout
        Tine.Tinebase.MainScreen.setActiveContentPanel(gridPanel);
    },
    
    /**
     * update datastore with node values and load datastore
     */
    loadData: function()
    {
        var dataStore = Ext.getCmp('AdminGroupsGrid').getStore();
            
        dataStore.load({
            params:{
                start:0, 
                limit:50 
            }
        });
    },

    show: function() 
    {
        this.initComponent();
        
        var currentToolbar = Tine.Tinebase.MainScreen.getActiveToolbar();

        if(currentToolbar === false || currentToolbar.id != 'AdminGroupsToolbar') {
            this.displayGroupsToolbar();
            this.displayGroupsGrid();
        }
        this.loadData();
    },
    
    reload: function() 
    {
        if(Ext.ComponentMgr.all.containsKey('AdminGroupsGrid')) {
            setTimeout ("Ext.getCmp('AdminGroupsGrid').getStore().reload()", 200);
        }
    }
}

Tine.Admin.Groups.EditDialog = {
    handlers: {
        applyChanges: function(_button, _event, _closeWindow) 
        {
            var form = Ext.getCmp('groupDialog').getForm();

            if(form.isValid()) {
                form.updateRecord(Tine.Admin.Groups.EditDialog.groupRecord);
        
                Ext.Ajax.request({
                    params: {
                        method: 'Admin.saveGroup', 
                        contactData: Ext.util.JSON.encode(Tine.Admin.Groups.EditDialog.groupRecord.data)
                    },
                    success: function(_result, _request) {
                        if(window.opener.Tine.Admin) {
                            window.opener.Tine.Admin.Main.reload();
                        }
                        if(_closeWindow === true) {
                            window.close();
                        } else {
                            //this.updateGroupRecord(Ext.util.JSON.decode(_result.responseText));
                            //this.updateToolbarButtons(formData.config.addressbookRights);
                            //form.loadRecord(this.groupRecord);
                        }
                    },
                    failure: function ( result, request) { 
                        Ext.MessageBox.alert('Failed', 'Could not save group.'); 
                    },
                    scope: this 
                });
            } else {
                Ext.MessageBox.alert('Errors', 'Please fix the errors noted.');
            }
        },

        saveAndClose: function(_button, _event) 
        {
            this.handlers.applyChanges(_button, _event, true);
        },

        deleteGroup: function(_button, _event) 
        {
            var groupIds = Ext.util.JSON.encode([Tine.Admin.Groups.EditDialog.groupRecord.data.id]);
                
            Ext.Ajax.request({
                url: 'index.php',
                params: {
                    method: 'Admin.deleteGroups', 
                    groupIds: groupIds
                },
                text: 'Deleting group...',
                success: function(_result, _request) {
                    if(window.opener.Tine.Admin) {
                        window.opener.Tine.Admin.Main.reload();
                    }
                    window.close();
                },
                failure: function ( result, request) { 
                    Ext.MessageBox.alert('Failed', 'Some error occured while trying to delete the group.'); 
                } 
            });                           
        },
        
     },
    
    editGroupDialog: [{
        layout:'column',
        border:false,
        //deferredRender:false,
        //anchor:'100%',
        autoHeight: true,
        items:[{
            columnWidth: 1,
            layout: 'form',
            border:false,
            items: [{
                xtype:'textfield',
                fieldLabel:'Group Name', 
                name:'name',
                anchor:'95%'
            }, {
                xtype:'textarea',
                name: 'description',
                fieldLabel: 'Description',
                grow: false,
                preventScrollbars:false,
                anchor:'95%',
                height: 120
            }]
        }]
    }],

    groupRecord: null,
    
    updateGroupRecord: function(_groupData)
    {
         this.groupRecord = new Tine.Admin.Model.Group(_groupData);
    },

    updateToolbarButtons: function(_rights)
    {        
       /* if(_rights.editGrant === true) {
            Ext.getCmp('groupDialog').action_saveAndClose.enable();
            Ext.getCmp('groupDialog').action_applyChanges.enable();
        }

        if(_rights.deleteGrant === true) {
            Ext.getCmp('groupDialog').action_delete.enable();
        }*/
        
    },

    display: function(_groupData) 
    {
        // Ext.FormPanel
        var dialog = new Tine.widgets.dialog.EditRecord({
            id : 'groupDialog',
            tbarItems: [],
            //title: 'the title',
            labelWidth: 120,
            labelAlign: 'top',
            handlerScope: this,
            handlerApplyChanges: this.handlers.applyChanges,
            handlerSaveAndClose: this.handlers.saveAndClose,
            handlerDelete: this.handlers.deleteGroup,
            handlerExport: this.handlers.exportGroup,
            items: this.editGroupDialog
        });

        var viewport = new Ext.Viewport({
            layout: 'border',
            frame: true,
            items: dialog
        });

        this.updateGroupRecord(_groupData);
        this.updateToolbarButtons(_groupData.grants);
        
        dialog.getForm().loadRecord(this.groupRecord);
        
    }
    
}

Ext.namespace('Tine.Admin.Model');
Tine.Admin.Model.Group = Ext.data.Record.create([
    {name: 'id'},
    {name: 'name'},
    {name: 'description'},
]);
