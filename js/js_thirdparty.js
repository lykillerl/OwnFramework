(function(window){
    function _GACls(){

        Me = this;
        var Init = false, TName = 'Default';

        Me.Init = function() {
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','__GATrack');
            Init = true;
        };

        Me.CreateTracker = function(TrackName, TrackID){
            if(Init === true && TrackName !== '' && TrackID !== '')
                __GATrack('create', TrackID, {'name': TrackName});
        };

        Me.TrackPage = function(Page, Title, Tracker, Callback){
            if(Init === true && Page !== ''){
                var Config = {
                    'hitType': 'pageview',
                    'page': Page,
                }
                if(typeof(Title) === 'string')Config['title'] = Title;
                if(typeof(Callback) === 'function')Config['hitCallback'] = Callback;
                if(typeof(Tracker) !== 'string')Tracker='';
                if(Tracker === '')Tracker = TName;
                __GATrack(Tracker + '.send', Config);
            }
        };

        Me.TrackEvent = function(Category, Action, Label, Value, Tracker, Callback){
            if(Init === true && Category !== '' && Action !== ''){
                var Config = {
                    'hitType': 'event',
                    'eventCategory': Category,
                    'eventAction': Action,
                    'eventLabel': Label,
                    'eventValue': Value
                }
                if(typeof(Title) === 'string')Config['title'] = Title;
                if(typeof(Callback) === 'function')Config['hitCallback'] = Callback;
                if(typeof(Tracker) !== 'string')Tracker ='';
                if(Tracker === '')Tracker = TName;
                __GATrack(Tracker + '.send', Config);
            }
        };

        Me.Init();
        if(typeof(_$.Data.Get('_GA_TRACK_CODE')) === 'string')
            Me.CreateTracker(TName, _$.Data.Get('_GA_TRACK_CODE'));
    }
    window._GA = new _GACls();

    function _TwittCls(FMsg){

        var Me = this;
        var _TwittUrl = 'http://twitter.com/intent/tweet?source=webclient&text=';

        Me.Share = function(FMsg){
            if(_TwittUrl && FMsg)
                _$.Window.Open(_TwittUrl + _$.Common.EncodeURL(FMsg), 600, 500, 'twitter');
        }
    }
    window._Twitt = new _TwittCls();

    function _FBCls(){

        var Me = this, ReadyList = [];
        Me.Inited = false;

        Me.Init = function(){
            if(!Me.Inited)
                FB.init({
                    appId  : _$.Var.Default(_$.Data.Get('_FB_APP_ID'), ''),
                    status : true, // check login status
                    cookie : true, // enable cookies to allow the server to access the session
                    xfbml  : true  // parse XFBML
                });

            Me.Inited = true;
            if(typeof(FB.Canvas.setAutoGrow) === 'function')
                FB.Canvas.setAutoGrow();
            if(ReadyList.length > 0){
                for(var i in ReadyList)
                    Me.GetStatus(ReadyList[i]);
                ReadyList = null;
            }
        };

        Me.Call = function(CallBack, Scope){
            if(!Me.Inited)
                return false;
            FB.getLoginStatus(function(Response){
              switch(Response.status){

                //user have already login and authorize our app.
                case 'connected':
                    _$.Data.Set('_FBAccess', Response.authResponse.accessToken, true);
                    _$.Data.Set('_FBID', Response.authResponse.userID, true);
                    if(typeof(CallBack) === "function")
                        CallBack(true);
                break;

                //user have not authorize our app.
                case 'not_authorized':

                //user is not login to the facebook
                default:
                    Me.Login(CallBack, Scope);
                }
            });
        };

        Me.Login = function(CallBack, Scope){
            if(!Me.Inited)
                return false;
            Scope = _$.Var.Default(Scope, _$.Data.Get('_FB_APP_INIT_PERMIT'));
            if(Scope === false)
                Scope = '';
            FB.login(function(Response) {
            	if (Response.authResponse){
                    _$.Data.Set('_FBAccess', Response.authResponse.accessToken, true);
                    _$.Data.Set('_FBID', Response.authResponse.userID, true);
                    if(typeof(CallBack) === "function")
                        CallBack(true);
                }else
                    if(typeof(CallBack) === "function")
                        CallBack(false);
            }, {scope: Scope});
        };

        Me.Logout = function(CallBack){
            if(!Me.Inited)
                return false;
            FB.logout(CallBack);
        };

        Me.Api = function(Command, CallBack, Params, Method){
            if(!Me.Inited)
                return false;
            FB.api(Command, Method, Params, CallBack);
        };

        Me.Permission = function(CallBack){
            if(!Me.Inited)
                return false;
            FB.api("/me/permissions",
            function (FPermit) {
                if(typeof(CallBack) === "function")
                    CallBack(FPermit);
            });
        };

        Me.GetStatus = function(CallBack){
            if(!Me.Inited)
                return false;
            FB.getLoginStatus(function(Response){
                if(Response.status === "connected"){
                    _$.Data.Set('_FBAccess', Response.authResponse.accessToken, true);
                    _$.Data.Set('_FBID', Response.authResponse.userID, true);
                    if(typeof(CallBack) === "function")
                        CallBack(true);
              }else{
                if(typeof(CallBack) === "function")
                    CallBack(false);
              }
            }, true);
        };

        Me.Injection = function(CallBack){
            if(!Me.Inited)
                return false;
            FB.api("/me",
            function (FBDetail) {
                FBDetail.quotes = '';
                _$.Data.Set('_FBData', FBDetail);
                _$.Data.Set('_FBID', FBDetail.id, true);
                if(typeof(CallBack) === "function")
                    CallBack(FBDetail);
            });
        };

        Me.Request = function(Msg_Show, CallBack) {
            if(!Me.Inited)
                return false;
            FB.ui(
            {
                method: 'apprequests',
                message: Msg_Show
            },
              function(Response) {
                if (Response && Response.post_id)
                    CallBack(Response);
            });
        };

        Me.Share = function(FName, FLink, FPic, FMsg, Callback){
            if(!Me.Inited)
                return false;
            var Infor = {
                method: 'feed',
                caption : _$.Var.Default(FName, _$.Data.Get('_FB_SHARE_CAPTION')),
                name: _$.Var.Default(FName, _$.Data.Get('_FB_SHARE_NAME')),
                link: _$.Var.Default(FLink, _$.Data.Get('_FB_SHARE_LINK')),
                picture: _$.Var.Default(FPic, _$.Data.Get('_FB_SHARE_PIC')),
                description: _$.Var.Default(FMsg, _$.Data.Get('_FB_SHARE_MSG'))
              }
            FB.ui(Infor,
              function(Response) {
                if(typeof(CallBack) === "function")
                    CallBack(Response);
            });
        };

        Me.PInfor = function(CallBack){
            if(!Me.Inited)
                return false;
            if(typeof(CallBack) === 'function')
                FB.Canvas.getPageInfo(function(Pg_Infor){
                    if(Pg_Infor.scrollLeft <1)Pg_Infor.scrollLeft = 1;
                    if(Pg_Infor.scrollTop <1)Pg_Infor.scrollTop = 5;
                    CallBack(Pg_Infor);
                });
        };

        Me.ScrollTo = function(Left, Top){
            if(!Me.Inited)
                return false;
            FB.Canvas.scrollTo(Left, Top);
        };

        Me.SetSize = function(Size){
            if(!Me.Inited)
                return false;
            FB.Canvas.setSize(Size);
        };

        Me.Ready = function(CallBack){
            if(!Me.Inited)
                ReadyList.push(CallBack);
            else
                Me.GetStatus(CallBack);
        };

        window.fbAsyncInit = Me.Init;
        var FB_Root = _$(document).Create('div', {id:'fb-root', style:{display:'none'}});
        _$.Common.AddScript('//connect.facebook.net/en_US/all.js');
    };
    window._FB = new _FBCls();
})(window);