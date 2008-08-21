/*
 * Tine 2.0
 * 
 * @package     Tine
 * @subpackage  Tinebase
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Lars Kneschke <l.kneschke@metaways.de>
 * @copyright   Copyright (c) 2007-2008 Metaways Infosystems GmbH (http://www.metaways.de)
 * @version     $Id$
 *
 */

Ext.namespace('Tine.Tinebase.Registry');

Tine.Login = {

    showLoginDialog: function(_defaultUsername, _defaultPassword) {
        // turn on validation errors beside the field globally
        Ext.form.Field.prototype.msgTarget = 'side';  

    	var loginButtons = [{
            id: 'loginbutton',
            text: _('Login'),
            handler: Tine.Login.loginHandler 
        }];
        
        if ( false &&  userRegistration === true ) {
            loginButtons.push({
                text: _('Register'),
                handler: Tine.Login.UserRegistrationHandler
            });
        }
        
        var loginDialog = new Ext.FormPanel({
            id: 'loginDialog',
            labelWidth: 75,
            url:'index.php',
            baseParams:{
            	method: 'Tinebase.login'
            },
            frame:true,
            title: _('Please enter your login data'),
            bodyStyle:'padding:5px 5px 0',
            width: 350,
            defaults: {
            	width: 230
            },
            defaultType: 'textfield',
            items: [{
                fieldLabel: _('Username'),
                id: 'username',
                name: 'username',
                value: _defaultUsername,
                width:225
            }, {
                inputType: 'password',
                fieldLabel: _('Password'),
                id: 'password',
                name: 'password',
                //allowBlank: false,
                value: _defaultPassword,
                width:225
            }],
            buttons: loginButtons
        });
        
        loginDialog.render(document.body);

        Ext.Element.get('loginDialog').center();
        
        Ext.getCmp('username').focus();
        
        Ext.getCmp('username').on('specialkey', function(_field, _event) {
        	if(_event.getKey() == _event.ENTER){
        		Tine.Login.loginHandler();
        	}
        });

        Ext.getCmp('password').on('specialkey', function(_field, _event) {
            if(_event.getKey() == _event.ENTER){
                Tine.Login.loginHandler();
            }
        });
    },
    
    loginHandler: function(){
    	var loginDialog = Ext.getCmp('loginDialog');
    	
        if (loginDialog.getForm().isValid()) {
            loginDialog.getForm().submit({
                waitTitle: _('Please wait!'), 
                waitMsg:_('Logging you in...'),
                params: {
                    jsonKey: Tine.Tinebase.jsonKey
                },
                headers: {
                    'X-Tine20-Request-Type': 'JSON'
                },
                success:function(form, action, o) {
                    Ext.MessageBox.wait(_('Login successful. Loading Tine 2.0...'), _('Please wait!'));
                    window.location = window.location;
                },
                failure:function(form, action) {
                    Ext.MessageBox.show({
                        title: _('Login failure'),
                        msg: _('Your username and/or your password are wrong!!!'),
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.ERROR /*,
                        fn: function() {} */
                    });
                }
            });
        }
    },
    
    UserRegistrationHandler: function () {
        var regWindow = new Tine.Tinebase.UserRegistration();
        regWindow.show();
    }
};