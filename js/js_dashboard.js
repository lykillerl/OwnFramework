(function(window){

    function _Dashboard(){
        var Me = this, Auth = {UID:0, Admin:false, FBID:0, FName:''},
        UAccPg = '_admin/admin_uacc.php', Container = _$('div#container');

        Me.Init = function(){
            _$('div').Selectable(false);
            //$('html').ContextMenu(function(e){return _$.Event.PreventDefault(e);}, false);
            _$.Ajax.DefaultObj(Container);
            _$.Ajax.Function(UAccPg, {Action:'Status'}, {Target:function(Data){
                Auth = _$.Data.Unserialize(Data);
                Me.UISetup();
            }});
        };

        Me.AOnly = function(Url, Data, Config){
            Config = Config || {};
            Config['Method'] = 'POST';
            _$.Ajax.Only(Url, Data, Config);
        };

        Me.AObj = function(Url, Data, Config){
            Config = Config || {};
            Config['Method'] = 'POST';
            _$.Ajax.Object(Url, Data, Config);
        };

        Me.AFancy = function(Url, Data, Config){
            Config = Config || {};
            Config['Method'] = 'POST';
            _$.Ajax.Fancy(Url, Data, Config);
        };

        Me.AExec = function(Url, Data, Config){
            Config = Config || {};
            Config['Method'] = 'POST';
            _$.Ajax.Execute(Url, Data, Config);
        };

        Me.AFnc = function(Url, Data, Config){
            Config = Config || {};
            Config['Method'] = 'POST';
            _$.Ajax.Function(Url, Data, Config);
        };

        Me.MBox = function(Msg, Title, Config){
            _$.Fancy.MsgBox(Msg, Title, Config);
        };

        Me.ActMsg = function(Msg, Title){
            _$.Fancy.Close('*');
            _$.Fancy.MsgBox(Msg, Title);
        };

        Me.FancyClose = function(Channel){
            _$.Fancy.Close(Channel)
        };

        Me.ActRequest = function(Url, Token, SData, Feedback, Reloadment){
            Feedback = _$(Feedback);
            var Data = {Token:Token};
            if(typeof(SData) === 'string'){
                if(/^(\{|\[).*(\}|\])/.test(SData) === false)
                    SData = '{' + SData + '}';
                SData = _$.Data.Unserialize(SData);
                if(!_$.Var.IsEmpty(SData)){
                    for(var Key in SData)
                        if(Key !== 'Token')
                            Data[Key] = SData[Key];
                }
            }
            _$.Ajax.Function(Url, Data, {Method:'POST', Validate:true, KeepValidate:true, Target: function(JSon){
                var Respone = _$.Data.Unserialize(JSon), Status = _$.Var.Default(Respone, '', 'Status'),
                Title = _$.Var.Default(Respone, 'System Status', 'Title'), Msg = _$.Var.Default(Respone, '', 'Msg'),
                Html = _$.Var.Default(Respone, null, 'Html'), Value = _$.Var.Default(Respone, null, 'Value'),
                Reload = _$.Var.Default(Respone, false, 'Reload'), Reloader = _$.Var.Default(Respone, Reloadment, 'Reloader'),
                ServerCallBack = _$.Var.Default(Respone, false, 'Callback'), Callback = _$.Var.Default(Reloader, false, 'Callback'),
                Execute = _$.Var.Default(Respone, false, 'Execute');

                switch(Status){

                    case 'done':
                        _$.Validate.Clear();
                        Me.FancyClose();
                        break;

                    case 'ok':
                        _$.Validate.Clear();
                        Me.FancyClose();
                        Me.MBox(Msg, Title);
                        break;

                    case 'respone':
                        Feedback.Html(Msg);
                        _$.Fancy.Reposition();
                        break;

                    case 'value':
                        Feedback.Value(Msg);
                        Feedback.Focus();
                        break;

                    case 'errwait':
                        Me.MBox(Msg, Title, {Channel:'error'});
                        break;

                    case 'error':
                        _$.Validate.Clear();
                        Me.FancyClose();
                        Me.MBox(Msg, Title);
                        break;
                }
                if(_$.Var.IsSet(Html)){
                    Feedback.Html(Html);
                    _$.Fancy.Reposition();
                }

                if(_$.Var.IsSet(Value)){
                    Feedback.Value(Value);
                    Feedback.Focus();
                }

                if(_$.Var.Default(Reloader, false, 'Reload') === true || Reload === true)
                    Me.AObj(_$.Var.Default(Reloader, Url, 'Url'), _$.Var.Default(Reloader, {}, 'Data'), _$.Var.Default(Reloader, {}, 'Config'));

                if(typeof(Execute) === 'string' && Execute !== 'string')
                    try{eval(Execute);}catch(e){}
                if(typeof(ServerCallBack) === 'function')
                    try{ServerCallBack();}catch(e){}
                if(typeof(Callback) === ' function')
                    try{Callback();}catch(e){};
           }});
        };

        Me.UISetup = function(){
            var PInfor = _$('div.pinfor');
            if(Auth.UID === 0){
                _$('div.navicon').Style({display:'none'});
                _$.Menu.MDestroy('Dashboard');
                PInfor.Html('');
            }else{
                _$.Ajax.Function(UAccPg, {UI:'Menu'}, {Target: function(Data){
                    var Menu = _$.Data.Unserialize(Data);
                    if(_$.Var.Len(Menu) === 1)
                        for (var Key in Menu)
                            Menu = Menu[Key];
                    Menu['Logout'] = "Dashboard:AskBox::Are you sure to logout?::{Logout:'Dashboard:Logout'}::Logout";
                    _$.Menu.MCreate('Dashboard', Menu, 'menu');
                    _$('div.navicon').Style({display:'block'});
                }});
                PInfor.Create('div', {html: Auth.FName, classname:Auth.Admin === true ? 'admin' : ''});
                if(Auth.Profile === true)
                PInfor.Create('input', {value: 'Profile', type:'button', classname:'btnlow', href:'Dashboard:AObj::'+UAccPg});
            }
            _$.Keyboard.Unbind('enter');
            Me.AObj(UAccPg, {UI:'main'},{Callback:function(){
                if(_FB.Inited === true)
                    _$('div#sec_facebook').Style({display:'block'});
            }});
            if(Auth.hasOwnProperty('Execute') && typeof(Auth.Execute) === 'string' && Auth.Execute !== '')
                eval(Auth.Execute);
            if(Auth.hasOwnProperty('Callback') && typeof(Auth.Callback) === 'function')
                try{Auth.Callback();}catch(e){}
        };

        Me.FBConnectAsk = function(Callback){
            Dashboard.AskBox("Are you sure you want to connect to your current login Facebook account?<p><span class='normal'>If you've already connected, it will reconnect to the current Facebook account.</span></p>",
            {'Connect to Facebook':'Dashboard:FBConnect::'+Callback}, 'Facebook Connect');
        };

        Me.Reset = function(){
            _$('input#UName, input#UPass').Value('');
            _$('input#UName').Focus();
            _$.Keyboard.Bind('enter', Me.Login);
        };

        Me.Menu = function(){
            _$.Menu.MOpen.apply(this, ['Dashboard']);
        };

        Me.MItem = function(Type, Url, Data){
            _$.Validate.Clear();
            _$.Data.Clear();
            _$.Keyboard.Unbind('enter');
            if(Type === 'Object')
                Me.AObj(Url, Data);
            else
                Me.AFancy(Url, Data);
        };

        Me.AskBox = function(Msg, Button, Title){
            var Config = {};
            if(typeof(Button) === 'string')
                Button = _$.Data.Unserialize(Button);
            if(typeof(Button) === 'object')
                Config['Button'] = Button;
            Me.MBox(Msg, Title, Config);
        }

        Me.NewPass = function(){
            Me.AFancy(UAccPg, {UI:'NewPass'}, {Fancy:{Modal:true}});
        };

        Me.FBConnect = function(Callback){
            _FB.Call(function(Login){
                if(Login === true){
                    Me.AFnc(UAccPg, {Action:'UFBConnect'}, {Target:function(Data){
                        var Respone = _$.Data.Unserialize(Data);
                        Me.MBox(Respone.Msg, 'Facebook Connect');
                        if(typeof(Callback) === 'function')
                            Callback();
                    }});
                }
            });
        };

        Me.FBLogin = function(){
            _FB.Call(function(Login){
                Me.AFnc(UAccPg, {Action:'UFBLogin'}, {Target:function(Data){
                    Auth = _$.Data.Unserialize(Data);
                    if(typeof(Auth) === 'object' && Auth.hasOwnProperty('Status')){
                        if(Auth.Status === 'error'){
                            _$('div#login_error').Html(Auth.Msg);
                            Me.Reset();
                        }else
                            Me.UISetup();
                    }
                }});
            });
        };

        Me.Login = function(){
            Me.AFnc(UAccPg, {Action:'ULogin', _FORM_DATA:'div#login_form'}, {Target:function(Data){
                Auth = _$.Data.Unserialize(Data);
                if(typeof(Auth) === 'object' && Auth.hasOwnProperty('Status')){
                    if(Auth.Status === 'error'){
                        _$('div#login_error').Html(Auth.Msg);
                        Me.Reset();
                    }else
                        Me.UISetup();
                }
            }});
        };

        Me.Logout = function(){
            Me.AFnc(UAccPg, {Action:'ULogout'}, {Target: function(LogStats){
                if(LogStats === "ok"){
                    Auth = {UID:0, Admin:false, FBID:0, FName:''};
                    Me.UISetup();
                }
            }});
        }
    }
    _$.Data.Set('_FB_APP_ID', '209366755915184');
    window.Dashboard = new _Dashboard();
})(window);