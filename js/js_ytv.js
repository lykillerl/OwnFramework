(function(window){

    function AppCls(){
        var Me = this, Admin = 0, YTPlayer = null,
        Container = _$('div#ytv_container'),
        MWidth = Container.Style('width'),
        MHeight = Container.Style('height'),
        Fancy  = {Overlay:{Color:'#000'}, Box:{Frame:false}, CloseBtn:{Show:false}},
        SFancy = {Overlay:{Color:'#000'}},
        BlockSizeX = 120, BlockSizeY = 90;

        Me.Init = function(){
            MWidth = MWidth - (MWidth % BlockSizeX);
            MHeight = MHeight - (MHeight % BlockSizeY);
            $('div').Selectable(false);
            $('html').ContextMenu(function(e){return _$.Event.PreventDefault(e);}, false);
            Container.Html('');
            _$.Menu.MCreate('Dashboard', {
                'List All User':{_type:'item', id:'btn_lstusr', href:'function:App.Dashboard.LstUsr'},
                'New Category':{_type:'item', id:'btn_newcat', href:'function:App.New.Cat'},
                'New Video':{_type:'item', id:'btn_newvideo', href:'function:App.New.Video'}
            }, 'menu', 'menu_list');
            _$.Menu.MCreate('Video', {
                'Edit Category':{_type:'item', id:'ytv_vedit'},
                'Delete Video':{_type:'item', id:'ytv_vdelete'}
            }, 'menu', 'menu_list');
            _$.Common.AddScript('https://www.youtube.com/iframe_api');
        };

        Me.Dashboard = {

            Login : function(){
                _FB.Call(function(Login){
                    if(Login === true){
                        _$.Ajax.Function('request.php',{Action:'Login'} , {Target:function(Data){
                            var Login = _$.Data.Unserialize(Data), YTData = _$.Data.Get('YTData');
                            if(Login.Status === 'ok'){
                                _$.Data.Set('Login', Login);
                                _$.Data.Set('Admin', _$.Var.Int(Login.Admin));
                                $('div#btn_login').Style({display:'none'});
                                switch(Login.Admin){
                                    case 2:
                                    _$('li#btn_lstusr').Style({display:'block'});

                                    case 1:
                                    _$('li#btn_newcat').Style({display:'block'});
                                    if(YTData.category.hasOwnProperty(_$.Data.Get('LoadedCat')))
                                        _$('div#ytv_chgcat').Style({display:'block'});

                                    case 0:
                                        _$('div#btn_logout, div#btn_dashboard').ToggleDisplay();
                                }
                            }else
                                _$.Fancy.MsgBox('Sorry, can\'t login now, please try again later, thank you.','Login Failed');
                        }});
                    }
                });
            },

            Logout : function(Auto){
                _$.Data.Remove('Login');
                _$.Data.Remove('Admin');
                _$('li#btn_lstusr, li#btn_newcat, div#ytv_chgcat, div#btn_logout, div#btn_dashboard').Style({display:'none'});
                _$('div#btn_login').Style({display:'block'});
                if(Auto !== true)
                    _FB.Logout(function(){_$.Ajax.Function('request.php',{Action:'Logout'});});
            },

            LstUsr : function(){
                if(_$.Data.Get('Login') && _$.Data.Get('Admin') > 1)
                    _$.Ajax.Fancy('request.php', {UI:'LstUsr'}, {Fancy:SFancy});
            }
        };

        Me.VMenu = function(EventObj, EObj){
            var YTID = $(this).Attr('ytvid'), Item = _$.Data.Get('YTData').ytvlist[YTID];
            if(_$.Data.Get('_FBID') === Item.owner || _$.Data.Get('Admin') > 0){
                $('li#ytv_vedit').Href('function:App.Chg.Video::' + YTID);
                $('li#ytv_vdelete').Href('function:App.Submit.DelVideo::' + YTID);
                _$.Menu.MOpen('Video', EObj.X, EObj.Y);
            }
        };

        Me.New = {
            Cat : function(){
                if(_$.Data.Get('Login') && _$.Data.Get('Admin') > 0){
                    _$.Ajax.Fancy('request.php', {UI:'NewCategory'}, {Fancy:SFancy});
                }
            },

            Video : function(){
                if(_$.Data.Get('Login')){
                    _$.Ajax.Fancy('request.php', {UI:'NewVideo'}, {Fancy:SFancy});
                }
            },
        };

        Me.Chg = {
            Cat : function(ID){
                if(_$.Data.Get('Login') && _$.Data.Get('Admin') > 0){
                    _$.Ajax.Fancy('request.php', {UI:'ChgCategory', ID:ID}, {Fancy:SFancy});
                }
            },

            Video : function(ID){
                if(_$.Data.Get('Login')){
                    _$.Ajax.Fancy('request.php', {UI:'ChgVideo', ID:ID}, {Fancy:SFancy});
                }
            }
        };

        Me.Submit = {
            AddCat : function(){
                if(_$.Data.Get('Login') && _$.Data.Get('Admin') > 0){
                    _$.Validate.Clear();
                    $('input#CTitle').Validate({Method:'must'});
                    _$.Ajax.Execute('request.php', {Action:'RegCat', CTitle:$('input#CTitle').Value()}, {Validate:true});
                }
            },

            ChgCat : function(ID){
                if(_$.Data.Get('Login') && _$.Data.Get('Admin') > 0){
                    _$.Validate.Clear();
                    $('input#CTitle').Validate({Method:'must'});
                    _$.Ajax.Execute('request.php', {Action:'RegCat', ID:ID, CTitle:$('input#CTitle').Value()}, {Validate:true});
                }
            },

            DelCat : function(ID){
                if(_$.Data.Get('Login') && _$.Data.Get('Admin') > 0)
                    _$.Ajax.Execute('request.php', {Action:'DelCat', ID:ID});
            },

            AddVideo : function(){
                if(_$.Data.Get('Login')){
                    _$.Validate.Clear();
                    $('input#YTVID').Validate({Method:'must'});
                    _$.Ajax.Execute('request.php', {Action:'RegVideo', YTVID:$('input#YTVID').Value(), SelCat:$('input[videocat]').Checked()}, {Validate:true});
                }
            },

            ChgVideo : function(ID){
                if(_$.Data.Get('Login'))
                    _$.Ajax.Execute('request.php', {Action:'RegVideo', YTVID:ID, SelCat:$('input[videocat]').Checked()});
            },

            DelVideo : function(ID){
                if(_$.Data.Get('Login'))
                    _$.Fancy.AskBox('Are you sure, you want to delete the video?', {
                        'Delete Video':"ajax:execute::request.php::{Action:'DelVideo', ID:"+ID+"}"
                    },'Delete Video', SFancy);
                    _$.Ajax.Fancy('request.php', {UI:'DelVideo', ID:ID},{Fancy:SFancy});
            }
        };

        Me.YTFPlay = function(ID){
            var Item = _$.Data.Get('YTData').ytvlist[ID], VWidth = _$.Common.View.Width() < 800 ? _$.Common.View.Width() - 25: 780, VHeight = (VWidth / 100) * 56.25;
            Fancy.Callback = function(){
                _$.Ajax.Only('request.php',{Action:'View',ID:ID}, {Callback:Me.Update});
                YTPlayer = new YT.Player('YTPlayer', {
                  videoId: ID,
                  playerVars  : {
                    autoplay : 1,
                    cc_load_policy : 0,
                    showsearch : 0,
                    rel : 0,
                    showinfo : 0,
                    version : 3,
                    disablekb : 1,
                    enablejsapi : 1,
                    fs : 1,
                    html5 : 1
                  },
                  events: {
                    onStateChange: function(Status){
                        if(Status.data === 0)
                            Me.YTFStop();
                    }
                  }
                });
            };
            _$.Fancy.Show('<div id="YTPlayer" style="width:' + VWidth + 'px; height:' + VHeight + 'px; background:#001 url(\'images/_system/preloader.gif\') no-repeat center center;">' +
            '&nbsp;</div>'+
            '<table class="ytv_view_content">' +
            '<tr><td class="ytv_view_title">' + Item.title + '</td>' +
            '<td class="ytv_view_infor">Views : <b>' + Item.view + '</b><br />Added : <b>' + Item.timemod + '</b></td></tr>' +
            '<tr class="ytv_view_desc"><td colspan="2"><div class="ytv_view_desc">' + _$.Data.StrUrlToLinks(_$.Data.Nl2br(Item.desc), '_blank') + '</div></td></tr>' +
            '</table>', Fancy);
            Fancy = _$.Var.Remove(Fancy, 'Callback');
        };

        Me.YTFStop = function(){
            YTPlayer.destroy();
            _$.Fancy.Close();
        };

        Me.LoadCat = function(CatID){
            _$.Menu.MClose('Category');
            var YTData = _$.Data.Get('YTData'), CatList = YTData.category, Items = YTData.ytvlist, Listed = 0;
            Container.ClearChild();
            if(_$.Data.Get('LoadedCat') !== CatID){
                Container.Opacity(0);
                _$.Data.Set('LoadedCat', CatID);
            }
            $('div#ytv_catlb').Html(CatList.hasOwnProperty(CatID) ? CatList[CatID] : 'All');
            if(CatList.hasOwnProperty(CatID)){
                if(_$.Data.Get('Login') && _$.Data.Get('Admin') > 0){
                    $('div#ytv_chgcat').Href('function:App.Chg.Cat::' + CatID);
                    $('div#ytv_chgcat').Style({display:'block'});
                }
                for(var id in Items){
                    var Item = Items[id];
                    if(_$.Var.InArray(CatID, Item.category)){
                        var ItemObj = $(Container.Create('div', {
                           attr:{href:'function:App.YTFPlay::'+id, ytvid:id},
                           classname:'ytv_list_block',
                           html:'&nbsp;'
                        }));
                        ItemObj.Create('img', {
                           src:Item.thumbnail,
                           classname:'ytv_video_img',
                           html:'&nbsp;'
                        });
                        ItemObj.Create('div', {
                           classname:'ytv_video_title',
                           html: Item.title.substr(0, 20) + (Item.title.length > 20 ? '...' :'')
                        });
                        ItemObj.ContextMenu(Me.VMenu);
                        Listed++;
                    }
                }
            }else{
                $('div#ytv_chgcat').Style({display:'none'});
                for(var cat in CatList){
                    Container.Create('div',{classname:'ytv_catlist_title', html:CatList[cat]});
                    for(var vid in Items){
                        var Item = Items[vid];
                        if(_$.Var.InArray(cat, Item.category)){
                            var ItemObj = $(Container.Create('div', {
                               attr:{href:'function:App.YTFPlay::'+vid, ytvid:vid},
                               classname:'ytv_list_block',
                               html:'&nbsp;'
                            }));
                            ItemObj.Create('img', {
                               src:Item.thumbnail,
                               classname:'ytv_video_img',
                               html:'&nbsp;'
                            });
                            ItemObj.Create('div', {
                               classname:'ytv_video_title',
                               html: Item.title.substr(0, 20) + (Item.title.length > 20 ? '...' :'')
                            });
                            ItemObj.ContextMenu(Me.VMenu, false ,true);
                            Listed++;
                        }
                    }
                }
            }
            if(Listed === 0)
                Container.Html('<div style="font:1.2em Arial; color:#999; width:auto; padding:2em;">Selected Category doesn\'t content any video.</div>');
            Container.Fade({Target:100});
        };

        Me.Update = function(){
            _$.Ajax.Function('request.php', {UI:'YTList'},{Target:function(Data){
                YTData = _$.Data.Unserialize(Data), CatMenu = $('div#ytv_catmenu'), Cats = YTData.category, Items = YTData.ytvlist;
                if(YTData.status === 'ok'){
                    _$.Data.Set('YTData', YTData);
                    CatMenu.Html('');
                    CatMenu.Create('div', {
                       classname: 'menu_list ytv_catmenu_list',
                       href:'function:App.LoadCat',
                       html: 'All'});
                    for(var key in Cats){
                        CatMenu.Create('div', {
                           classname: 'menu_list ytv_catmenu_list',
                           href:'function:App.LoadCat::'+key,
                           html: Cats[key]});
                    }
                    Me.LoadCat(_$.Data.Get('LoadedCat'));
                }
            }});
        };

        Me.LoadLst = function(){
            _$.Ajax.Function('request.php', {UI:'YTList'},{Target:function(Data){
                YTData = _$.Data.Unserialize(Data), CatMenu = $('div#ytv_catmenu'), Cats = YTData.category, Items = YTData.ytvlist;
                if(YTData.status === 'ok'){
                    _$.Data.Set('YTData', YTData);
                    var CMenu = {All:'function:App.LoadCat'};
                    for(var key in Cats)
                        CMenu[Cats[key]] = 'function:App.LoadCat::'+key;
                    _$.Menu.MCreate('Category', CMenu, 'menu','menu_list');
                    Me.LoadCat();
                }
            }});
        };

        Me.FancyMove = function(){
            if(window.top !== window){
                _FB.PInfor(function(info){
                        _$.Fancy.BoxFixPos(undefined, info.scrollTop);
                });
            }
        }
    };
    window.App = new AppCls();

    _FB.Ready(function(Login){
        if(Login === true) App.Dashboard.Login(); else App.Dashboard.Logout(true);
    });

    _$.Ready(function(){
        App.Init();
        App.LoadLst();
        _$.Job.Work(App.FancyMove, 'loop', 50);
    });
})(window);