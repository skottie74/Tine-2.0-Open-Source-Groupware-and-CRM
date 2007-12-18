Ext.namespace('Egw.Crm');

Egw.Crm = function() {


      /**
     * the initial tree to be displayed in the left treePanel
     */
    var _initialTree = [{
        "text":"Projects",
        "cls":"treemain",
        "allowDrag":false,
        "allowDrop":true,
        "id":"crm",
        "icon":"images\/oxygen\/16x16\/apps\/package-multimedia.png",
        "application":"Crm",
        "datatype":"projects",
        /*"children":[],*/
        "leaf":null,
        "contextMenuClass":"ctxMenuProject",
        "owner":"all",
    //    "jsonMethod":"Crm.getProjectsByOwner",
        "dataPanelType":"projects"
    }];
    
     /**
     * creates the crm tree panel
     *
     */
    var _getTreePanel = function() 
    {
        var treeLoader = new Ext.tree.TreeLoader({
            dataUrl:'index.php',
            baseParams: {
            	jsonKey: Egw.Egwbase.Registry.get('jsonKey'),
            	location: 'mainTree'
            }
        });
   /*     treeLoader.on("beforeload", function(_loader, _node) {
            switch(_node.attributes.nodeType) {
                case 'otherAddressbooks':
                    _loader.baseParams.method   = 'Addressbook.getOtherUsers';
                    break;
                    
                case 'sharedAddressbooks':
                    _loader.baseParams.method   = 'Addressbook.getSharedAddressbooks';
                    break;

                case 'userAddressbooks':
                    _loader.baseParams.method   = 'Addressbook.getAddressbooksByOwner';
                    _loader.baseParams.owner    = _node.attributes.owner;
                    break;
            }
        }, this);
 */   
        var treePanel = new Ext.tree.TreePanel({
            title: 'CRM',
            id: 'Crm_Tree',
//            iconCls: 'CrmTreePanel',
            loader: treeLoader,
            rootVisible: false,
            border: false
        });
        
        // set the root node
        var treeRoot = new Ext.tree.TreeNode({
            text: 'root',
            draggable:false,
            allowDrop:false,
            id:'root'
        });
        treePanel.setRootNode(treeRoot);

        for(var i=0; i< _initialTree.length; i++) {
            treeRoot.appendChild(new Ext.tree.AsyncTreeNode(_initialTree[i]));
        }
        
        treePanel.on('click', function(_node, _event) {
            Egw.Crm.show(_node);
        }, this);

        treePanel.on('beforeexpand', function(_panel) {
            if(_panel.getSelectionModel().getSelectedNode() === null) {
                _panel.expandPath('/root');
                _panel.selectPath('/root');
            }
            _panel.fireEvent('click', _panel.getSelectionModel().getSelectedNode());
        }, this);

  /*      treePanel.on('contextmenu', function(_node, _event) {
            _event.stopEvent();
            //_node.select();
            //_node.getOwnerTree().fireEvent('click', _node);
            _treeNodeContextMenu = _node;
            //console.log(_node.attributes.nodeType);
            switch(_node.attributes.nodeType) {
                case 'userAddressbooks':
                case 'sharedAddressbooks':
                    _contextMenuUserAddressbooks.showAt(_event.getXY());
                    break;

                case 'singleAddressbook':
                    _contextMenuSingleAddressbook.showAt(_event.getXY());
                    break;

                default:
                    //console.log(_node.attributes.nodeType);
                    break;
            }
        });
*/
        return treePanel;
    };  
    
    
    
    var _createDataStore = function()
    {

         var ds_crm = new Ext.data.JsonStore({
            baseParams: {
                method: 'Crm.getProjectsByOwner',
                owner: 'all'
            },
            root: 'results',
            totalProperty: 'totalcount',
            id: 'pj_id',
            fields: [
                {name: 'pj_id'},            
                {name: 'pj_name'},
                {name: 'pj_distributionphase_id'},
                {name: 'pj_customertype_id'},
                {name: 'pj_leadsource_id'},
                {name: 'pj_owner'},
                {name: 'pj_modifier'},
                {name: 'pj_start'},
                {name: 'pj_modified'},
                {name: 'pj_description'},
                {name: 'pj_end'},
                {name: 'pj_turnover'},
                {name: 'pj_probability'},
                {name: 'pj_end_scheduled'},
                {name: 'pj_lastread'},
                {name: 'pj_lastreader'},
                
                {name: 'pj_projectstate'},
                {name: 'pj_leadtype'},
                {name: 'pj_leadsource'}//,
                /*
                {name: 'pj_partner'},
                {name: 'pj_lead'},
                {name: 'pj_partner_id'},
                {name: 'pj_lead_id'}  */
            ],
            // turn on remote sorting
            remoteSort: true
        });
     
   
        ds_crm.setDefaultSort('pj_name', 'asc');

        ds_crm.on('beforeload', function(_dataSource) {
        	_dataSource.baseParams.filter = Ext.getCmp('quickSearchField').getRawValue();
            
        	/*
        	var from = Date.parseDate(
        	   Ext.getCmp('adminApplications_dateFrom').getRawValue(),
        	   'm/d/y'
        	);
            _dataSource.baseParams.from   = from.format("Y-m-d\\T00:00:00");

            var to = Date.parseDate(
               Ext.getCmp('adminApplications_dateTo').getRawValue(),
               'm/d/y'
            );
            _dataSource.baseParams.to     = to.format("Y-m-d\\T23:59:59");  */
        });        
        
        ds_crm.load({params:{start:0, limit:50}});
        
        return ds_crm;
    }


	  	var action_add = new Ext.Action({
			text: 'add',
			iconCls: 'action_add',
			handler: function () {
           //     var tree = Ext.getCmp('venues-tree');
		//		var curSelNode = tree.getSelectionModel().getSelectedNode();
			//	var RootNode   = tree.getRootNode();
            
                Egw.Egwbase.Common.openWindow('CrmProjectWindow', 'index.php?method=Crm.editProject&_projectId=0&_eventId=NULL', 900, 700);
             }
 		}); 
        
        var handler_edit = function() 
        {
            var _rowIndex = Ext.getCmp('gridCrm').getSelectionModel().getSelections();
            Egw.Egwbase.Common.openWindow('projectWindow', 'index.php?method=Crm.editProject&_projectId='+_rowIndex[0].id, 900, 700);   

        }

    var handler_pre_delete = function(){
        Ext.MessageBox.show({
            title: 'Delete Project?',
            msg: 'Are you sure you want to delete this project?',
            buttons: Ext.MessageBox.YESNO,
            fn: handler_delete,
            icon: Ext.MessageBox.QUESTION
        });
    }

        var handler_delete = function(btn) 
        {
            if (btn == 'yes') {
                
                var _rowIndexIds = Ext.getCmp('gridCrm').getSelectionModel().getSelections();

                var toDelete_Ids = new Array();
                
                for (var i = 0; i < _rowIndexIds.length; ++i) {
                         toDelete_Ids.push(_rowIndexIds[i].id);
                } 
                
                var projectIds = Ext.util.JSON.encode(toDelete_Ids);

                Ext.Ajax.request({
                    params: {
                        method: 'Crm.deleteProjects',
                        _projectIds: projectIds
                    },
                    text: 'Deleting project...',
                    success: function(_result, _request){
                        Ext.getCmp('gridCrm').getStore().reload();
                    },
                    failure: function(result, request){
                        Ext.MessageBox.alert('Failed', 'Some error occured while trying to delete the project.');
                    }
                });
            } 
        }

	   	var action_edit = new Ext.Action({
			text: 'edit',
			disabled: true,
			handler: handler_edit,
			iconCls: 'action_edit'
		});
			
	   	var action_delete = new Ext.Action({
			text: 'delete',
			disabled: true,
			handler: handler_pre_delete,
			iconCls: 'action_delete'
		});	


    var _showCrmToolbar = function() {
    
        var quickSearchField = new Ext.app.SearchField({
            id:        'quickSearchField',
            width:     200,
            emptyText: 'enter searchfilter'
        }); 
        quickSearchField.on('change', function() {
            Ext.getCmp('gridCrm').getStore().load({params:{start:0, limit:50}});
        });
        
        var currentDate = new Date();
        var oneWeekAgo = new Date(currentDate.getTime() - 604800000);
        
        var dateFrom = new Ext.form.DateField({
            id:             'Crm_dateFrom',
            allowBlank:     false,
            validateOnBlur: false,
            value:          oneWeekAgo
        });
        var dateTo = new Ext.form.DateField({
            id:             'Crm_dateTo',
            allowBlank:     false,
            validateOnBlur: false,
            value:          currentDate
        });
        

       function editOptions(item) {
           var deleted_options = new Array();
           
           var Dialog = new Ext.Window({
				title: item.table,
                id: 'options_window',
				modal: true,
			    width: 350,
			    height: 500,
			    minWidth: 300,
			    minHeight: 500,
			    layout: 'fit',
			    plain:true,
			    bodyStyle:'padding:5px;',
			    buttonAlign:'center'
            });	
            
            var st = new Ext.data.JsonStore({
                baseParams: {
                    method: 'Crm.get'+item.table,
                    sort: item.mapping,
                    dir: 'ASC'
                },
                root: 'results',
                totalProperty: 'totalcount',
                id: 'key',
                fields: [
                    {name: 'key', mapping: item.mapping + '_id'},
                    {name: 'value', mapping: item.mapping}    
                ],
                // turn on remote sorting
                remoteSort: false
            });
            
            st.on('remove', function(_store,_record,_index){
                deleted_options.push(_record.data.key);
            })
            st.load();
            

            
            var cm = new Ext.grid.ColumnModel([
                	{id:'id', header: "id", dataIndex: 'key', width: 25, hidden: true },
                    {id:'value', header: 'entries', dataIndex: 'value', width: 200, hideable: false, sortable: false, editor: new Ext.form.TextField({
                    allowBlank: false
                    }) }
            ]);            
            
             var entry = Ext.data.Record.create([
               {name: 'key', mapping: item.mapping + '_id', type: 'int'},
               {name: 'value', mapping: item.mapping, type: 'int'}
            ]);
            
            var handler_options_add = function(){
                var p = new entry({
                    key: '-1',
					value: ''
                });
                gridPanel.stopEditing();
                st.insert(0, p);
                gridPanel.startEditing(0, 0);
            }
                        
            var handler_options_delete = function(){
               	var optionGrid  = Ext.getCmp('editOptionsGrid');
        		var optionStore = optionGrid.getStore();
                
        		var selectedRows = optionGrid.getSelectionModel().getSelections();
                for (var i = 0; i < selectedRows.length; ++i) {
                    optionStore.remove(selectedRows[i]);
                }   
            }                        
                        
          
           var handler_options_saveClose = function(){
                var store_options = Ext.getCmp('editOptionsGrid').getStore();
                var store_options_mod = store_options.getModifiedRecords();
                var modified_options_json = Egw.Egwbase.Common.getJSONdata(store_options_mod);
           
                var options = Ext.getCmp('optionsData');
                    options.setValue(modified_options_json);
         
           	    var form_options = Ext.getCmp('editOptionsDialog').getForm();
            	form_options.render();
         
            	if(form_options.isValid()) {     
        			var additionalData = {};
                        additionalData.deletedOptions = Ext.util.JSON.encode(deleted_options);
                    
                                                        
        			form_options.submit({
            			waitTitle:'Please wait!',
            			waitMsg:'saving options...',
                        params:additionalData,
            			success:function(form, action, o) {
                            store_options.reload();
                            Ext.getCmp('options_window').hide();
                            Ext.getCmp('options_window').destroy();                            
            			},
            			failure:function(form, action) {
            			//	Ext.MessageBox.alert("Error",action.result.errorMessage);
            			}
            		});
            	} else {
            		Ext.MessageBox.alert('Errors', 'Please fix the errors noted.');
            	}           
            }          
            
            var gridPanel = new Ext.grid.EditorGridPanel({
                store: st,
                id: 'editOptionsGrid',
                cm: cm,
                autoExpandColumn:'value',
                frame:false,
                viewConfig: {
                    forceFit: true
                },
                sm: new Ext.grid.RowSelectionModel({multiSelect:true}),
                clicksToEdit:2,
                tbar: [{
                    text: 'new item',
                    iconCls: 'action_add',
                    handler : handler_options_add
                    },{
                    text: 'delete item',
                    iconCls: 'action_delete',
                    handler : handler_options_delete
                    },{
                    text: 'save',
                    iconCls: 'action_saveAndClose',
                    handler : handler_options_saveClose 
                    }]  
                });
                  
                      
      		var form = new Ext.FormPanel({
			baseParams: {method :'Crm.save' + item.table },
		    labelAlign: 'top',
			region: 'center',
            id: 'editOptionsDialog',
			deferredRender: false,
            items: [{
                layout: 'form',
                border: false,
                items: [{
                   xtype: 'hidden',
                   name: 'optionsData',
                   id: 'optionsData',
                   value: ''
                }]
              }] 
            });
              
          Dialog.add(gridPanel);
          Dialog.add(form);
          Dialog.show();		  
        }

        
        var settings_tb_menu = new Ext.menu.Menu({
            id: 'crmSettingsMenu',
            items: [
                {text: 'Leadstatus', handler: editOptions, table: 'Projectstates', mapping: 'pj_projectstate'},
                {text: 'Leadsource', handler: editOptions, table: 'Leadsources', mapping: 'pj_leadsource'},
                {text: 'Leadtype', handler: editOptions, table: 'Leadtypes', mapping: 'pj_leadtype'},
                {text: 'Product', handler: editOptions, table: 'Productsource', mapping: 'pj_productsource'}
            ]
        });     
        
        
        var toolbar = new Ext.Toolbar({
            id: 'toolbarCrm',
            split: false,
            height: 26,
            items: [
                action_add,
                action_edit,
                action_delete,
                new Ext.Toolbar.Separator(),
                {
                    text:'Options',
                    iconCls: 'action_edit',  
                    menu: settings_tb_menu
                },
                '->',
                'Display from: ',
                ' ',
                dateFrom,
                'to: ',
                ' ',
                dateTo,                
                new Ext.Toolbar.Separator(),
                '->',
                'Search:', ' ',
                ' ',
                quickSearchField
            ]
        });
        
        Egw.Egwbase.MainScreen.setActiveToolbar(toolbar);
    }
    
    var _renderResult = function(_value, _cellObject, _record, _rowIndex, _colIndex, _dataStore) {
        switch (_value) {
            case '-3' :
                return 'invalid password';
                break;

            case '-2' :
                return 'ambiguous username';
                break;

            case '-1' :
                return 'user not found';
                break;

            case '0' :
                return 'failure';
                break;

            case '1' :
                return 'success';
                break;
        }
    }

    
    /**
     * creates the grid
     * 
     */
    var _showGrid = function() 
    {
    	
        var dataStore = _createDataStore();
        
        var pagingToolbar = new Ext.PagingToolbar({ // inline paging toolbar
            pageSize: 50,
            store: dataStore,
            displayInfo: true,
            displayMsg: 'Displaying projects {0} - {1} of {2}',
            emptyMsg: "No projects to display"
        }); 
        
      	var ctxMenuGrid = new Ext.menu.Menu({
        id:'ctxMenuGrid', 
        items: [
            action_edit,
            action_delete
        ]
    });

        var expander = new Ext.grid.RowExpander({
            tpl : new Ext.Template(
                '<p><b>Notes:</b> {pj_description}<br>',
                '<p><b>Activities:</b> </p>'
            )
        });
        
        var columnModel = new Ext.grid.ColumnModel([
            expander,
            {resizable: true, header: 'Projekt ID', id: 'pj_id', dataIndex: 'pj_id', width: 20, hidden: true},
            {resizable: true, header: 'Projektname', id: 'pj_name', dataIndex: 'pj_name', width: 200},
            {resizable: true, header: 'Partner', id: 'pj_partner', dataIndex: 'pj_partner', width: 150},
            {resizable: true, header: 'Lead', id: 'pj_lead', dataIndex: 'pj_lead', width: 150},
            {resizable: true, header: 'Status', id: 'pj_projectstate', dataIndex: 'pj_projectstate', width: 150},
            {resizable: true, header: 'Wahrscheinlichkeit', id: 'pj_probability', dataIndex: 'pj_probability', width: 50, renderer: Ext.util.Format.percentage},
            {resizable: true, header: 'Umsatz', id: 'pj_turnover', dataIndex: 'pj_turnover', width: 100, renderer: Ext.util.Format.euMoney }
        ]);
        
        columnModel.defaultSortable = true; // by default columns are sortable
        
        var rowSelectionModel = new Ext.grid.RowSelectionModel({multiSelect:true});
        
        rowSelectionModel.on('selectionchange', function(_selectionModel) {
            var rowCount = _selectionModel.getCount();

            if(rowCount < 1) {
                action_delete.setDisabled(true);
                action_edit.setDisabled(true);
            } 
            if (rowCount == 1) {
               action_edit.setDisabled(false);
               action_delete.setDisabled(false);               
            }    
            if(rowCount > 1) {                
               action_edit.setDisabled(true);
            }
        });
        
        var gridPanel = new Ext.grid.GridPanel({
            id: 'gridCrm',
            store: dataStore,
            cm: columnModel,
            tbar: pagingToolbar,   
            viewConfig: {
                        forceFit:true
                    },  
            plugins: expander,                    
            autoSizeColumns: false,
            selModel: rowSelectionModel,
            enableColLock:false,
            loadMask: true,
   //         autoExpandColumn: 'Projektname',
            border: false
        });
        
        Egw.Egwbase.MainScreen.setActiveContentPanel(gridPanel);


        gridPanel.on('rowcontextmenu', function(_grid, _rowIndex, _eventObject) {
            _eventObject.stopEvent();
            if(!_grid.getSelectionModel().isSelected(_rowIndex)) {
                _grid.getSelectionModel().selectRow(_rowIndex);
                action_delete.setDisabled(false);
            }
            ctxMenuGrid.showAt(_eventObject.getXY());
        });
        
        gridPanel.on('rowdblclick', function(_gridPanel, _rowIndexPar, ePar) {
            var record = _gridPanel.getStore().getAt(_rowIndexPar);
            Egw.Egwbase.Common.openWindow('projectWindow', 'index.php?method=Crm.editProject&_projectId='+record.data.pj_id, 900, 700);            
        });
       
       return;
    }
        
    // public functions and variables
    return {
        show: function(_node) {          
            _showCrmToolbar();
            _showGrid();    
          //  _getCrmTree();
          
        },
        
        getPanel: _getTreePanel,

        reload: function() {
            Ext.getCmp('gridCrm').getStore().reload();
            Ext.getCmp('gridCrm').doLayout();
        }
    }
    
}(); // end of application



Egw.Crm.ProjectEditDialog = function() {

    var dialog;
    
    var deleted_products = new Array();
    
    /**
        * the dialog to display the contact data
        */
    var projectedit;
    
    // private functions and variables
    
    var handler_applyChanges = function(_button, _event) 
    {
        var grid_products          = Ext.getCmp('grid_choosenProducts');
		var store_products         = Ext.getCmp('grid_choosenProducts').getStore();
        var store_products_mod     = store_products.getModifiedRecords();
        var modified_products_json = Egw.Egwbase.Common.getJSONdata(store_products_mod);
       
    	var projectForm = Ext.getCmp('projectDialog').getForm();
		projectForm.render();
    	
    	if(projectForm.isValid()) {
			var additionalData = {};
			if(formData.values) {
				additionalData.pj_id = formData.values.pj_id;
			}	
            additionalData.products = modified_products_json;
            additionalData.deletedProducts = Ext.util.JSON.encode(deleted_products);
                        
			projectForm.submit({
    			waitTitle:'Please wait!',
    			waitMsg:'saving event...',
    			params:additionalData,
    			success:function(form, action, o) {
                    store_products.reload();
    				window.opener.Egw.Crm.reload();
    			},
    			failure:function(form, action) {
    			//	Ext.MessageBox.alert("Error",action.result.errorMessage);
    			}
    		});
    	} else {
    		Ext.MessageBox.alert('Errors', 'Please fix the errors noted.');
    	}
    }
 

    var handler_saveAndClose = function(_button, _event) 
    {       
        var store_products = Ext.getCmp('grid_choosenProducts').getStore();
        var store_products_mod = store_products.getModifiedRecords();
        var modified_products_json = Egw.Egwbase.Common.getJSONdata(store_products_mod);
       
    	var projectForm = Ext.getCmp('projectDialog').getForm();
		projectForm.render();
    	
    	if(projectForm.isValid()) {
			var additionalData = {};
			if(formData.values) {
				additionalData.pj_id = formData.values.pj_id;
			}	
            additionalData.products = modified_products_json;
            additionalData.deletedProducts = Ext.util.JSON.encode(deleted_products);
                        
			projectForm.submit({
    			waitTitle:'Please wait!',
    			waitMsg:'saving event...',
    			params:additionalData,
    			success:function(form, action, o) {
                    store_products.reload();                    
    				window.opener.Egw.Crm.reload();
    				window.setTimeout("window.close()", 400);
    			},
    			failure:function(form, action) {
    			//	Ext.MessageBox.alert("Error",action.result.errorMessage);
    			}
    		});
    	} else {
    		Ext.MessageBox.alert('Errors', 'Please fix the errors noted.');
    	}
    }

    var handler_pre_delete = function(){
        Ext.MessageBox.show({
            title: 'Delete Project?',
            msg: 'Are you sure you want to delete this project?',
            buttons: Ext.MessageBox.YESNO,
            fn: handler_delete,
//            animEl: 'mb4',
            icon: Ext.MessageBox.QUESTION
        });
    }


    var handler_delete = function(btn) 
    {
        if (btn == 'yes') {
            var projectIds = Ext.util.JSON.encode([formData.values.pj_id]);
            
            Ext.Ajax.request({
                params: {
                    method: 'Crm.deleteProjects',
                    _projectIds: projectIds
                },
                text: 'Deleting project...',
                success: function(_result, _request){
                    window.opener.Egw.Crm.reload();
                    window.setTimeout("window.close()", 400);
                },
                failure: function(result, request){
                    Ext.MessageBox.alert('Failed', 'Some error occured while trying to delete the project.');
                }
            });
        } 
    }


   	var action_saveAndClose = new Ext.Action({
		text: 'save and close',
		handler: handler_saveAndClose,
		iconCls: 'action_saveAndClose'
	});

   	var action_applyChanges = new Ext.Action({
		text: 'apply changes',
		handler: handler_applyChanges,
		iconCls: 'action_applyChanges'
	});

   	var action_delete = new Ext.Action({
		text: 'delete project',
		handler: handler_pre_delete,
		iconCls: 'action_delete'
	});

    /**
     * display the event edit dialog
     *
     */
    var _displayDialog = function() {
        Ext.QuickTips.init();

        // turn on validation errors beside the field globally
        Ext.form.Field.prototype.msgTarget = 'side';

        
        var disableButtons = true;
        if(formData.values) {
            disableButtons = false;
        }       
        
        var projectToolbar = new Ext.Toolbar({
        	region: 'south',
          	id: 'applicationToolbar',
			split: false,
			height: 26,
			items: [
				action_saveAndClose,
				action_applyChanges,
				action_delete
			]
		});

		var _setParameter = function(_dataSource)
		{
            _dataSource.baseParams.method = 'Crm.getEvents';
            _dataSource.baseParams.options = Ext.encode({
            });
        }
  
  
 
        var _editHandler = function(_button, _event) {
        
            editWindow.show();
            	
        }; 

        var _action_edit = new Ext.Action({
            text: 'editieren',
            //disabled: true,
            handler: _editHandler,
            iconCls: 'action_edit'
        });
 

        function formatDate(value){
            return value ? value.dateFormat('M d, Y') : '';
        };
    
     
        var st_leadstatus = new Ext.data.JsonStore({
            baseParams: {
                method: 'Crm.getProjectstates',
                sort: 'pj_projectstate',
                dir: 'ASC'
            },
            root: 'results',
            totalProperty: 'totalcount',
            id: 'key',
            fields: [
                {name: 'key', mapping: 'pj_projectstate_id'},
                {name: 'value', mapping: 'pj_projectstate'}

            ],
            // turn on remote sorting
            remoteSort: true
        });
 
        var leadstatus = new Ext.form.ComboBox({
                fieldLabel:'Leadstatus', 
                id:'leadstatus',
                name:'projectstate',
                hiddenName:'pj_distributionphase_id',
				store: st_leadstatus,
				displayField:'value',
                valueField:'key',
				typeAhead: true,
//				mode: 'local',
				triggerAction: 'all',
				emptyText:'',
				selectOnFocus:true,
				editable: false,
				anchor:'96%'    
        });
	
	
        var st_leadtyp = new Ext.data.JsonStore({
            baseParams: {
                method: 'Crm.getLeadtypes',
                sort: 'pj_leadtype',
                dir: 'ASC'                
            },
            root: 'results',
            totalProperty: 'totalcount',
            id: 'key',
            fields: [
                {name: 'key', mapping: 'pj_leadtype_id'},
                {name: 'value', mapping: 'pj_leadtype'}

            ],
            // turn on remote sorting
            remoteSort: true
        }); 	
		
		var leadtyp = new Ext.form.ComboBox({
                fieldLabel:'Leadtypes', 
                id:'leadtype',
                name:'pj_leadtyp',
                hiddenName:'pj_customertype_id',
				store: st_leadtyp,
				displayField:'value',
                valueField:'key',
				typeAhead: true,
//				mode: 'local',
				triggerAction: 'all',
				emptyText:'',
				selectOnFocus:true,
				editable: false,
				anchor:'96%'    
        });
    

        var st_leadsource = new Ext.data.JsonStore({
            baseParams: {
                method: 'Crm.getLeadsources',
                sort: 'pj_leadsource',
                dir: 'ASC'
            },
            root: 'results',
            totalProperty: 'totalcount',
            id: 'key',
            fields: [
                {name: 'key', mapping: 'pj_leadsource_id'},
                {name: 'value', mapping: 'pj_leadsource'}

            ],
            // turn on remote sorting
            remoteSort: true
        }); 	

		var leadsource = new Ext.form.ComboBox({
                fieldLabel:'Leadquelle', 
                id:'leadsource',
                name:'pj_leadsource',
                hiddenName:'pj_leadsource_id',
				store: st_leadsource,
				displayField:'value',
                valueField:'key',
				typeAhead: true,
//				mode: 'local',
				triggerAction: 'all',
				emptyText:'',
				selectOnFocus:true,
				editable: false,
				anchor:'100%'    
        });
		
		

	
        
        var st_owner =  new Ext.data.SimpleStore({
                fields: ['key','value'],
                data: [
                        ['0','Meier, Heinz'],
                        ['1','Schultze, Hans'],
                        ['2','Vorfelder, Meier']
                    ]
        });
        
        var st_activities = new Ext.data.SimpleStore({
                fields: ['id','status','status2','datum','titel','message','responsible'],
                data: [
                        ['0','3','4','05.12.2007 15:30','der titel','die lange message','Meier,Heiner'],
                        ['1','2','1','12.11.2007 07:10','der titel2','die lange message2','Schultze,Heinz'],
                        ['2','4','2','14.12.2007 18:40','der titel3','die lange message3','Meier,Heiner'],
                        ['3','3','4','05.12.2007 15:30','der titel','die lange message','Meier,Heiner'],
                        ['4','2','1','12.11.2007 07:10','der titel2','die lange message2','Schultze,Heinz'],
                        ['5','3','4','05.12.2007 15:30','der titel','die lange message','Meier,Heiner'],
                        ['6','2','1','12.11.2007 07:10','der titel2','die lange message2','Schultze,Heinz'],
                        ['7','4','2','14.12.2007 18:40','der titel3','die lange message3','Meier,Heiner'],
                        ['8','4','2','14.12.2007 18:40','der titel3','die lange message3','Meier,Heiner'],
                        ['9','4','2','14.12.2007 18:40','der titel3','die lange message3','Meier,Heiner']
                    ]
        });
     
        var st_probability = new Ext.data.SimpleStore({
                fields: ['key','value'],
                data: [
                        ['0','0%'],
                        ['1','10%'],
                        ['2','20%'],
                        ['3','30%'],
                        ['4','40%'],
                        ['5','50%'],
                        ['6','60%'],
                        ['7','70%'],
                        ['8','80%'],
                        ['9','90%'],
                        ['10','100%']
                    ]
        });
     
        var st_contacts = new Ext.data.JsonStore({
 //           url: 'index.php',
            //baseParams: getParameterContactsDataStore(_node),
            root: 'results',
            totalProperty: 'totalcount',
            id: 'contact_id',
            fields: [
                {name: 'contact_id'},
                {name: 'contact_tid'},
                {name: 'contact_owner'},
                {name: 'contact_private'},
                {name: 'cat_id'},
                {name: 'n_family'},
                {name: 'n_given'},
                {name: 'n_middle'},
                {name: 'n_prefix'},
                {name: 'n_suffix'},
                {name: 'n_fn'},
                {name: 'n_fileas'},
                {name: 'contact_bday'},
                {name: 'org_name'},
                {name: 'org_unit'},
                {name: 'contact_title'},
                {name: 'contact_role'},
                {name: 'contact_assistent'},
                {name: 'contact_room'},
                {name: 'adr_one_street'},
                {name: 'adr_one_street2'},
                {name: 'adr_one_locality'},
                {name: 'adr_one_region'},
                {name: 'adr_one_postalcode'},
                {name: 'adr_one_countryname'},
                {name: 'contact_label'},
                {name: 'adr_two_street'},
                {name: 'adr_two_street2'},
                {name: 'adr_two_locality'},
                {name: 'adr_two_region'},
                {name: 'adr_two_postalcode'},
                {name: 'adr_two_countryname'},
                {name: 'tel_work'},
                {name: 'tel_cell'},
                {name: 'tel_fax'},
                {name: 'tel_assistent'},
                {name: 'tel_car'},
                {name: 'tel_pager'},
                {name: 'tel_home'},
                {name: 'tel_fax_home'},
                {name: 'tel_cell_private'},
                {name: 'tel_other'},
                {name: 'tel_prefer'},
                {name: 'contact_email'},
                {name: 'contact_email_home'},
                {name: 'contact_url'},
                {name: 'contact_url_home'},
                {name: 'contact_freebusy_uri'},
                {name: 'contact_calendar_uri'},
                {name: 'contact_note'},
                {name: 'contact_tz'},
                {name: 'contact_geo'},
                {name: 'contact_pubkey'},
                {name: 'contact_created'},
                {name: 'contact_creator'},
                {name: 'contact_modified'},
                {name: 'contact_modifier'},
                {name: 'contact_jpegphoto'},
                {name: 'account_id'}
            ],
            // turn on remote sorting
            remoteSort: true
        });
        
        st_contacts.setDefaultSort('n_family', 'asc');
        
        st_contacts.on('beforeload', function(_st_contacts) {
            _st_contacts.baseParams.datatype = 'allcontacts';
            _st_contacts.baseParams.method = 'Crm.getContactsByOwner';
            _st_contacts.baseParams.owner = 'allcontacts';
        });   
        
        
        var tpl_contacts = new Ext.Template(
            '<div class="search-item">',
                '<h3>{contact_id} , {n_fileas}</h3>',
            '</div>'
        );
     
        var cm_activities = new Ext.grid.ColumnModel([
            	{id:'status', header: "Status", dataIndex: 'status', width: 25, sortable: true },
                {id:'status2', header: "Status2", dataIndex: 'status2', width: 25, sortable: true },
                {id:'datum', header: "Datum", dataIndex: 'datum', width: 100, sortable: true },
                {id:'titel', header: "Titel", dataIndex: 'titel', width: 170, sortable: true },
                {id:'message', header: "Message", dataIndex: 'message', width: 300, sortable: true },
                {id:'responsible', header: "Verantwortlicher", dataIndex: 'responsible', width: 300, sortable: true }
        ]);
        
        var activities_limited = new Ext.grid.GridPanel({
                store: st_activities,
                id:'grid_activities_limited',
                cm: cm_activities,
                viewConfig: {
                    forceFit: true
                },
                sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
                anchor: '100% 100%',
                enableColumnHide: false,
                enableColumnMove: false,
                enableHdMenu: false,
                stripeRows: true, 
                frame:false,
                iconCls:'icon-grid'
        });  
  
  
	   if (formData.values) {
			var _pj_id = formData.values.pj_id;
	   } else {
			var _pj_id = '-1';
	   }
  
        var st_choosenProducts = new Ext.data.JsonStore({
            baseParams: {
                method: 'Crm.getProductsById',
                _id: _pj_id
            },
            root: 'results',
            totalProperty: 'totalcount',
            id: 'pj_id',
            fields: [
                {name: 'pj_id'},
                {name: 'pj_product_id'},
                {name: 'pj_product_desc'},
                {name: 'pj_product_price'}
            ],
            // turn on remote sorting
            remoteSort: true
        });
        
        st_choosenProducts.on('remove', function(_store,_record,_index){
            deleted_products.push(_record.data.pj_id);
        })
        
        st_choosenProducts.load();
 
  /*
        var st_productsAvailable = new Ext.data.JsonStore({
            baseParams: {
                method: 'Crm.getProductsAvailable',
                _id: _pj_id
            },
            root: 'results',
            totalProperty: 'totalcount',
            id: 'pj_product_id ',
            fields: [
                {name: 'pj_product_id '},
                {name: 'pj_product'}
            ],
            // turn on remote sorting
            remoteSort: true
        });
		st_productsAvailable.load();
	*/	
		var st_productsAvailable = new Ext.data.SimpleStore({
                fields: ['pj_product_id','value'],
                data: [
                        ['0','CEREC AE'],
                        ['1','CEREC MC XL'],
                        ['2','CEREC 3 SE'],
                        ['3','CEREC PPU'],
                        ['4','Inlab MC XL'],
                        ['5','Inlab'],
                        ['6','InEOS'],
                        ['7','InFire'],
                        ['8','InCoris'],
                        ['9','InFinident'],
                        ['10','PC']
                    ],
                id: 'pj_product_id'
        });  
  
        function renderProductCombo(value) {
            if(value) return st_productsAvailable.getAt(value).get('value');
        }
  
        var cm_choosenProducts = new Ext.grid.ColumnModel([{
                header: "Produkt",
                dataIndex: 'pj_product_id',
                width: 300,
                editor: new Ext.form.ComboBox({
                    name: 'product_combo',
                    hiddenName: 'pj_product_id',
                    store: st_productsAvailable, 
                    displayField:'value', 
                    valueField: 'pj_product_id',
                    allowBlank: false, 
                    editable: false,
                    forceSelection: true, 
                    triggerAction: "all", 
                    mode: 'local', 
                    lazyRender:true,
                    listClass: 'x-combo-list-small'
                    }),
                renderer: renderProductCombo
                } , { 
                header: "Seriennummer",
                dataIndex: 'pj_product_desc',
                width: 300,
                editor: new Ext.form.TextField({
                    allowBlank: false
                    })
                } , {
                header: "Preis",
                dataIndex: 'pj_product_price',
                width: 150,
                align: 'right',
                editor: new Ext.form.NumberField({
                    allowBlank: false,
                    allowNegative: false,
                    decimalSeparator: ','
                    }),
                renderer: Ext.util.Format.euMoney
                }
        ]);
        
        var handler_remove_product = function(_button, _event)
        {
    		var productGrid = Ext.getCmp('grid_choosenProducts');
    		var productStore = productGrid.getStore();
            
    		var selectedRows = productGrid.getSelectionModel().getSelections();
            for (var i = 0; i < selectedRows.length; ++i) {
                productStore.remove(selectedRows[i]);
            }    	
        }; 
        
        var product = Ext.data.Record.create([
           {name: 'pj_id', type: 'int'},
           {name: 'pj_project_id', type: 'int'},
           {name: 'pj_product_id', type: 'int'},
           {name: 'pj_product_desc', type: 'string'},
           {name: 'pj_product_price', type: 'float'}
        ]);
        
        var grid_choosenProducts = new Ext.grid.EditorGridPanel({
            store: st_choosenProducts,
            id: 'grid_choosenProducts',
            cm: cm_choosenProducts,
            sm: new Ext.grid.RowSelectionModel({multiSelect:true}),
            anchor: '100% 100%',
//            autoExpandColumn:'common',
            frame:false,
            clicksToEdit:2,
            tbar: [{
                text: 'Produkt hinzufügen',
                handler : function(){
                    var p = new product({
                        pj_id: '-1',
						pj_project_id: _pj_id,
                        pj_product_id: '',                       
                        pj_product_desc:'',
                        pj_product_price: ''
                    });
                    grid_choosenProducts.stopEditing();
                    st_choosenProducts.insert(0, p);
                    grid_choosenProducts.startEditing(0, 0);
                }
            } , {
                text: 'Produkt löschen',
                handler : handler_remove_product
            }]  
        });

        grid_choosenProducts.on('rowcontextmenu', function(_grid, _rowIndex, _eventObject) {
            _eventObject.stopEvent();
            if(!_grid.getSelectionModel().isSelected(_rowIndex)) {
                _grid.getSelectionModel().selectRow(_rowIndex);
            }
            //var record = _grid.getStore().getAt(rowIndex);
            //_ctxMenuGrid.showAt(_eventObject.getXY());
        });
        /*
    var action_delete = new Ext.Action({
        text: 'delete contact',
        disabled: true,
        handler: _deleteBtnHandler,
        iconCls: 'action_delete'
    });        
        
    var _ctxMenuGrid = new Ext.menu.Menu({
        id:'ctxMenuProduct', 
        items: [
            action_delete
        ]
    });
      */  
  
        var cm_choosenContacts = new Ext.grid.ColumnModel([
            	{id:'anschrift', header: "Anschrift", dataIndex: 'anschrift', width: 250, sortable: true },
                {id:'kontakt', header: "Kontakt", dataIndex: 'kontakt', width: 250, sortable: true }
        ]);
  
        var st_choosenContacts = new Ext.data.SimpleStore({
                fields: ['key','value'],
                data: [
                        ['0','0%'],
                        ['1','10%']
                    ]
        });
  
        var grid_contact = new Ext.grid.GridPanel({
                store: st_choosenContacts,
                id:'grid_choosenContacts',
                cm: cm_choosenContacts,
                viewConfig: {
                    forceFit: true
                },
                sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
                anchor: '100% 87%',
                enableColumnHide: false,
                enableColumnMove: false,
                enableHdMenu: false,
                stripeRows: true, 
                frame:false,
                iconCls:'icon-grid'
        });  
  
		var projectedit = new Ext.FormPanel({
			baseParams: {method :'Crm.saveProject'},
		    labelAlign: 'top',
			bodyStyle:'padding:5px',
            anchor:'100%',
			region: 'center',
            id: 'projectDialog',
			tbar: projectToolbar, 
			deferredRender: false,
            items: [{
                xtype:'tabpanel',
	            plain:true,
	            activeTab: 0,
				deferredRender:false,
                anchor:'100% 100%',
	            defaults:{bodyStyle:'padding:10px'},
	            items:[{
	                title:'Übersicht',
	                layout:'form',
					deferredRender:false,
					border:false,
					items:[{  
                        layout:'column',
        	            border:false,
        				deferredRender:false,
                        anchor:'100%',
                        items:[{
                            columnWidth:.5,
                            layout: 'form',
                            border:false,
                            items: [{
                                xtype:'fieldset',
                                title:'Projekt',
                                height: 270,
                                anchor:'99%',
                                items: [{
                                    xtype:'textfield',
                                    fieldLabel:'Projektname', 
                                    name:'pj_name',
                                    anchor:'100%'
                                } , {
                                    layout:'column',
                    	            border:false,
                    				deferredRender:false,
                                    anchor:'100%',
                                    items:[{
                                        columnWidth:.5,
                                        layout: 'form',
                                        border:false,
                                        items: [
                                            leadstatus  
                                         , 
									 		leadtyp
										]
                                    } , {                               
                                        columnWidth:.5,
                                        layout: 'form',
                                        border:false,
                                        items: [
                                        	leadsource  
                                         , {
                                            xtype:'combo',
                                            fieldLabel:'Verantwortlicher', 
                                            name:'pj_owner',
            								store: st_owner,
            								displayField:'value',
            								valueField:'key',
            								typeAhead: true,
            								mode: 'local',
            								triggerAction: 'all',
            								emptyText:'',
            								selectOnFocus:true,
            								editable: false,
            								anchor:'100%'
                                        }]
                                    }]
                                } , {
                                    xtype:'textarea',
                                    fieldLabel:'Notizen', 
                                    hideLabel: true,
                                    name:'pj_description',
                                    height: 90,
                                    anchor:'100%'
                                }]
                            } , {
                                xtype:'fieldset',
                                title:'Projektdaten',
                                height: 75,
                                anchor:'99%',
                                items: [{
                                    layout:'column',
                    	            border:false,
                    				deferredRender:false,
                                    anchor:'100%',
                                    items:[{
                                        columnWidth:.33,
                                        layout: 'form',
                                        border:false,
                                        items: [{
                                            xtype:'datefield',
                                            fieldLabel:'Start', 
                                            name:'pj_start',
                                            id:'startDate',
                                     //       format:formData.config.dateFormat, 
									 		format: 'd.m.Y',
                                            altFormat:'Y-m-d',
                                            anchor:'95%'
                                        }]
                                    } , {
                                        columnWidth:.33,
                                        layout: 'form',
                                        border:false,
                                        items: [{
                                            xtype:'datefield',
                                            fieldLabel:'Ende', 
                                            name:'pj_end',
                                            id:'endDate',
                                     //       format:formData.config.dateFormat, 
 									 		format: 'd.m.Y',
                                            altFormat:'Y-m-d',
                                            anchor:'95%'
                                        }]
                                    } , {
                                        columnWidth:.33,
                                        layout: 'form',
                                        border:false,
                                        items: [{
                                            xtype:'datefield',
                                            fieldLabel:'voraussichtl. Ende', 
                                            name:'pj_end_scheduled',
                                            id:'expectedEndDate',
                                      //    format:formData.config.dateFormat, 
  									 		format: 'd.m.Y',
                                            altFormat:'Y-m-d',
                                            anchor:'95%'
                                        }]
                                    }]
                                }]
                            }]
                        } , {
                            columnWidth:.5,
                            layout: 'form',
                            border:false,
                            items: [{
                                xtype:'fieldset',
                                title:'Kontakt',
                                height: 270,
                                anchor:'100%',
                                items: [{
                                    xtype:'combo',
                                    store: st_contacts,
                                    id: 'st_contacts',
                                    displayField:'contact_id',
                                    typeAhead: false,
                                    loadingText: 'Searching...',
                                    anchor: '100%',
                                    pageSize:50,
                                    queryParam: 'filter',
                                    hideTrigger:true,
                                    tpl: tpl_contacts,
                                    itemSelector: 'div.search-item',
                                    fieldLabel:'Kontakt auswählen', 
                                    hideLabel: true,
                                    name:'pj_contacts',
                                  /*  store: st_contacts,                            
                                    displayField:'n_fileas',
                                    valueField:'contact_id',
                                    typeAhead: false,
                                    loadingText: 'Searching...',
                                    mode: 'local',
                                    hideTrigger: true,                                    
                                    pageSize:10,
                                    tpl: tpl_contacts,
                                    itemSelector: 'div.search-item', */
                            		onSelect: function(record) {
                                        alert(st_contacts.getById(0));
                            			//alert(record);
                                    }
                                } , grid_contact
                                ]
                            } , {
                                xtype:'fieldset',
                                title:'Umsatz',
                                height: 75,
                                anchor:'100%',
                                items: [{
                                    layout:'column',
                    	            border:false,
                    				deferredRender:false,
                                    anchor:'100%',
                                    items:[{
                                        columnWidth:.5,
                                        layout: 'form',
                                        border:false,
                                        items: [{
                                            xtype:'numberfield',
                                            fieldLabel:'erwarteter Umsatz', 
                                            name:'pj_turnover',
                                            anchor:'97%'
                                        }]
                                    } , {
                                        columnWidth:.5,
                                        layout: 'form',
                                        border:false,
                                        items: [{
                                            xtype:'combo',
                                            fieldLabel:'Wahrscheinlichkeit', 
                                            name:'pj_probability',
            								store: st_probability,
            								displayField:'value',
            								valueField:'key',
            								typeAhead: true,
            								mode: 'local',
            								triggerAction: 'all',
            								emptyText:'',
            								selectOnFocus:true,
            								editable: false,
            								anchor:'100%'
                                        }]
                                    }]
                                }]
                            }]
                        }]
                    } , {
                        xtype:'fieldset',
                        title:'Produktübersicht',
                        height: 60,
                        anchor:'100%',
                        items: [{
                            xtype:'textfield',
                            fieldLabel:'Produkte', 
                            hideLabel: true,
                            name:'pj_productoverview',
                            id:'productoverview',
                            disabled: true,
                            value:'1 Inlab MC XL, 1 InEOS, 1 InFire, 1 InCoris',
                            anchor:'100%'
                        }]
                    } , {
                        xtype:'fieldset',
                        title:'letzte 10 Aktivitäten',
                        anchor:'100%',
                        height: 190,
                        items: [
                                activities_limited
                        ]
                    }]
                } , {
	                title:'Aktivitäten',
	                layout:'form',
					deferredRender:false,
					border:false,
					items:[{  
                    }]
                } , {
	                title:'Produkte',
	                layout:'form',
					deferredRender:false,
                    anchor:'100% 100%',
					border:false,
					items:[{  
                        xtype:'fieldset',
                        title:'gewählte Produkte',
                        anchor:'100% 100%',
                        items: [
                            grid_choosenProducts
                        ]
                    
                    }]
                }]
            }]
        });

		var viewport = new Ext.Viewport({
			layout: 'border',
			items: projectedit
		});        

    }

    var setContactDialogValues = function(_formData) {
    	var form = Ext.getCmp('projectDialog').getForm();
    	
    	form.setValues(_formData);

        if (formData.values.pj_start > 0) {
			var startDate = new Date(eval(formData.values.pj_start * 1000));
		}
		
		if (formData.values.pj_end > 0) {
			var endDate = new Date(eval(formData.values.pj_end * 1000));
		}
		
		if (formData.values.pj_end_scheduled > 0) {
			var expectedEndDate = new Date(eval(formData.values.pj_end_scheduled * 1000));
		}

        form.findField('startDate').setValue(startDate);
        form.findField('endDate').setValue(endDate);
        form.findField('expectedEndDate').setValue(expectedEndDate);

		if (formData.values.pj_distributionphase_id) {
			
			var leadstatus = Ext.getCmp('leadstatus');
			var st_leadstatus = leadstatus.store;
			st_leadstatus.on('load', function(){
				leadstatus.setValue(formData.values.pj_distributionphase_id);
			}, this, {
				single: true
			});
			st_leadstatus.load();
		}

		if (formData.values.pj_leadsource_id) {
			var leadsource = Ext.getCmp('leadsource');
			var st_leadsource = leadsource.store;
			st_leadsource.on('load', function(){
				leadsource.setValue(formData.values.pj_leadsource_id);
			}, this, {
				single: true
			});
			st_leadsource.load();
		}
	
		if (formData.values.pj_customertype_id) {
			var leadtype = Ext.getCmp('leadtype');
			var st_leadtype = leadtype.store;
			st_leadtype.on('load', function(){
				leadtype.setValue(formData.values.pj_customertype_id);
			}, this, {
				single: true
			});
			st_leadtype.load();
		}
    }

    var _exportContact = function(_btn, _event) {
        Ext.MessageBox.alert('Export', 'Not yet implemented.');
    }
    
    // public functions and variables
    return {
        display: function() {
            var dialog = _displayDialog();
            if(formData.values) {
                setContactDialogValues(formData.values);
            }
         }
        
    }
    
}(); // end of application