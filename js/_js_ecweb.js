(function(window){

    function _ECWeb(){
        var Me = this, Active_Lst = [], EcMenuMain = null, EcMenuObj = null, Draging = false, KeyMove = false, SelSec = false, SelItm = false, DMouse = {}, MenuOpen = true;
        Me.BottomOffset = 10;
        Me.MenuOpen = false;

        Me.Menu = {

            Close : function(){
                _$(EcMenuObj).Remove();
                EcMenuObj = null;
            },

            Open : function(EventObj, EObj){
                if(EcMenuObj !== null)
                    Me.Menu.Close();
                var Obj = EObj.Target, ECWeb = _$(Obj).Attr('ecweb');
                if(ECWeb === null){
                    Obj = Obj.parentNode;
                    ECWeb = _$(Obj).Attr('ecweb');
                }
                if(ECWeb === null)return;
                var MPos = {X: EObj.X, Y:EObj.Y}, Item = _$(Obj).Attr('item'),
                Parent = _$.Data.Get('ECWebItems').hasOwnProperty(Item) ? _$.Data.Get('ECWebItems')[Item].Parent : 'MAIN';
                if(_$.Data.Get('_ECW_MODE') === 'edit' && Obj && ECWeb !== null){
                    if(EcMenuMain === null)
                    EcMenuMain = _$(document).Create('div',{css:'overflow:visible; display:block; position:absolute; left:0px; top:0px; width:0px; height:0px;'});
                    EcMenuObj = _$(EcMenuMain).Create('ul', {
                        attr: {ecw_menu:'true'},
                        classname:'menu',
                        style:{
                            position : 'absolute',
                            left : MPos.X + 'px',
                            top : MPos.Y + 'px'
                            }
                    });
                    _$.Ajax.Function(_$.Data.Get('_ECW_RESPONE'), {UI:'ECMenu', Type:ECWeb, Parent:Parent, Item:Item},{Target:function(JSon){
                        var Items = _$.Data.Unserialize(JSon);
                        if(_$.Var.Len(Items) === 0)
                            Me.Menu.Close();
                        else{
                            for(var Label in Items){
                                _$(EcMenuObj).Create('li', {
                                    attr: {ecw_menu:'true', href: Items[Label] + '##function:_ECW.Menu.Close##function:_ECW.Deselect'},
                                    html : Label
                                });
                            }
                            _$('li[ecw_menu]').Selectable(false);
                            var MRect = _$(EcMenuObj).GetAPos();
                            if(MRect.right > _$.Common.Scroll.Left() + _$.Common.View.Width())
                                _$(EcMenuObj).Style({left: (_$.Common.Scroll.Left() + _$.Common.View.Width() - _$.Common.Scroll.Size() - (MRect.right - MRect.left) + 10).toString()+'px'});
                            else
                                _$(EcMenuObj).Style({left : (MPos.X - 10) + 'px'});
                            if(MRect.bottom > _$.Common.Scroll.Top() + _$.Common.View.Height())
                                _$(EcMenuObj).Style({top: (_$.Common.Scroll.Top() + _$.Common.View.Height() - _$.Common.Scroll.Size() - (MRect.bottom - MRect.top) + 10).toString()+'px'});
                            else
                                _$(EcMenuObj).Style({top : (MPos.Y - 10) + 'px'});
                            _$(EcMenuObj).MouseLeave(Me.Menu.Close);
                        }
                    }});
                    _$.Behavior.Style();
                }
                if(_$.Data.Get('_ECW_MODE') === 'edit' && (_$(Obj).Attr('ecweb') !== null || _$(Obj.parentNode).Attr('ecweb') !== null)){
                    _$.Event.PreventDefault(EventObj);
                    return false;
                }
            }
        };

        Me.Layer = function(Type, ItemID){
            _$.Ajax.Only(_$.Data.Get('_ECW_RESPONE'), {Action:'Layer', ID:ItemID, Type:Type}, {Callback:function(){
                _$.Ajax.Function(_$.Data.Get('_ECW_RESPONE') ,{UI:'ECWebLst'}, {Target:Me.Render});
            }});
        };

        Me.ItmPos = function(Item){
            if(_$.Data.Get('_ECW_MODE') === 'edit' && Item){
                ItmInfo = {
                    Action:'ItmPos',
                    ID : _$(Item).Attr('item'),
                    X: (_$(Item).Style('right') === 'auto' ? _$(Item).Style('left') : _$(Item).Style('right')),
                    Y: (_$(Item).Style('bottom') === 'auto' ? _$(Item).Style('top') : _$(Item).Style('bottom')),
                }
                var PRect = _$(_$.Data.Get('ECWebItems')[ItmInfo.ID].PObj).GetAPos();
                PRect.width = PRect.right - PRect.left;
                PRect.height = PRect.bottom - PRect.top;
                ItmInfo.PX = (ItmInfo.X  / PRect.width) * 100;
                ItmInfo.PY = (ItmInfo.Y  / PRect.height) * 100;
                _$.Ajax.Only(_$.Data.Get('_ECW_RESPONE'), ItmInfo);
                Me.Reposition();
            }
        };

        Me.Mouse = {

            Start : function(EventObj, EObj){
                var ItmID = _$(EObj.Target).Attr('item');
                if(EObj.Click === 1 && _$.Data.Get('_ECW_MODE') === 'edit' && _$(EObj.Target).Attr('ecweb') !== null && typeof(ItmID) === 'string' &&  _$.Data.Get('ECWebItems')){
                    var Item = _$.Data.Get('ECWebItems')[ItmID];
                    if(Item.Type === 'SECTION' && Item.Align !== 'move')
                        return;
                    SelSec = Item.PObj;
                    SelItm = Item.Obj;
                    var OPos = _$(SelItm).GetAPos();
                    DMouse = {
                        Left: EObj.X - OPos.left,
                        Top: EObj.Y - OPos.top,
                        Right: EObj.X - OPos.right,
                        Bottom: EObj.Y - OPos.bottom
                    }
                    Draging = true;
                }
            },

            Drag : function(EventObj, EObj){
                if(EObj.Click === 1 && _$.Data.Get('_ECW_MODE') === 'edit' && Draging === true && typeof(SelSec) === 'object' && typeof(SelItm) === 'object' && _$.Data.Get('ECWebItems')){

                    var
                    HitRectTestX = function(X, Snap, ORect, ERect){
                        if((ERect.left + Snap > ORect.left && ERect.left - Snap < ORect.left)) X = ERect.left;
                        else if((ERect.left + Snap > ORect.right && ERect.left - Snap < ORect.right)) X = ERect.left - ORect.width;
                        else if((ERect.right + Snap > ORect.left && ERect.right - Snap < ORect.left)) X = ERect.right;
                        else if((ERect.right + Snap > ORect.right && ERect.right - Snap < ORect.right)) X = ERect.right - ORect.width;
                        return X;
                    },

                    HitRectTestY = function(Y, Snap, ORect, ERect){
                        if((ERect.top + Snap > ORect.top && ERect.top - Snap < ORect.top)) Y = ERect.top;
                        else if((ERect.top + Snap > ORect.bottom && ERect.top - Snap < ORect.bottom)) Y = ERect.top - ORect.height;
                        else if((ERect.bottom + Snap > ORect.top && ERect.bottom - Snap < ORect.top)) Y = ERect.bottom;
                        else if((ERect.bottom + Snap > ORect.bottom && ERect.bottom - Snap < ORect.bottom)) Y = ERect.bottom - ORect.height;
                        return Y;
                    },

                    SecRect = _$(SelSec).GetAPos(),
                    ObjRect = _$(SelItm).GetAPos(),
                    LR = _$(SelItm).Style('right') === 'auto' ? "left" : "right",
                    TB = _$(SelItm).Style('bottom') === 'auto' ? "top" : "bottom",
                    Snap = _$.Data.Get('ECWebItems')[_$(SelItm).Attr('item')].Pos.SNAP,
                    Result = {},

                    SLRect = {
                        left:0,
                        top:0,
                        right: SecRect.right - SecRect.left,
                        bottom: SecRect.bottom - SecRect.top,
                        width: SecRect.right - SecRect.left,
                        height: SecRect.bottom - SecRect.top
                    },

                    SRRect = {
                        left: SecRect.right - SecRect.left,
                        top: SecRect.bottom - SecRect.top,
                        right: 0,
                        bottom: 0,
                        width: SecRect.right - SecRect.left,
                        height: SecRect.bottom - SecRect.top
                    };

                    ObjRect.width = ObjRect.right - ObjRect.left;
                    ObjRect.height = ObjRect.bottom - ObjRect.top;

                    if(_$(SelItm).Style('right') === 'auto') Result.X = ((EObj.X - SecRect.left) - DMouse.Left); else Result.X = ((SecRect.right - EObj.X) + DMouse.Right);
                    if(_$(SelItm).Style('bottom') === 'auto') Result.Y = ((EObj.Y - SecRect.top) - DMouse.Top); else Result.Y = ((SecRect.bottom - EObj.Y) + DMouse.Bottom);

                    ObjRect.left = Result.X;
                    ObjRect.top = Result.Y;
                    ObjRect.right = Result.X + ObjRect.width;
                    ObjRect.bottom = Result.Y + ObjRect.height;

                    if(LR === 'left')Result.X = HitRectTestX(Result.X, Snap, ObjRect, SLRect); else if(LR === 'right')Result.X = HitRectTestX(Result.X, Snap, ObjRect, SRRect);
                    if(TB === 'top')Result.Y = HitRectTestY(Result.Y, Snap, ObjRect, SLRect); else if(TB === 'bottom')Result.Y = HitRectTestY(Result.Y, Snap, ObjRect, SRRect);

                    _$("[parent=" + _$(SelItm).Attr('parent') + "]").Do(function(){
                        if(this !== SelItm){
                            var CRect = _$(this).GetAPos(),
                            LTRect = {
                                left: CRect.left - SecRect.left,
                                top: CRect.top - SecRect.top,
                                right: CRect.right - SecRect.left,
                                bottom: CRect.bottom - SecRect.top,
                                width:CRect.right - CRect.left,
                                height:CRect.bottom - CRect.top,
                            },
                            RBRect = {
                                left: SecRect.right - CRect.left,
                                top: SecRect.bottom - CRect.top,
                                right: SecRect.right - CRect.right,
                                bottom: SecRect.bottom - CRect.bottom,
                                width: CRect.right - CRect.left,
                                height: CRect.bottom - CRect.top,
                            };

                            if(LR === 'left')Result.X = HitRectTestX(Result.X, Snap, ObjRect, LTRect); else if(LR === 'right')Result.X = HitRectTestX(Result.X, Snap, ObjRect, RBRect);
                            if(TB === 'top')Result.Y = HitRectTestY(Result.Y, Snap, ObjRect, LTRect); else if(TB === 'bottom')Result.Y = HitRectTestY(Result.Y, Snap, ObjRect, RBRect);
                        }
                    });

                    Result[LR] = Result.X+'px';
                    Result[TB] = Result.Y+'px';
                    _$(SelItm).Style(Result);
                }
            },

            Save : function(EventObj, EObj){
                if(_$.Data.Get('_ECW_MODE') === 'edit' && Draging === true && typeof(SelSec) === 'object' && typeof(SelItm) === 'object' && _$.Data.Get('ECWebItems')){
                    Draging = false;
                    Me.ItmPos(SelItm);
                }
            }
        };

        Me.Deselect = function(){
            Draging = false;
            KeyMove = false;
            SelSec = false;
            SelItm = false;
            DMouse = {};
        };

        Me.Resolve = {

            Position : function(Parent){
                var Items = _$.Data.Get('ECWebItems');
                var SRect = _$(Items[Parent].Obj).GetAPos();
                SRect.width = SRect.right - SRect.left;
                SRect.height = SRect.bottom - SRect.top;
                for(var Key in Items){
                    if(Items[Key].Parent === Parent){
                        var Item = Items[Key].Obj;
                        var IRect = _$(Item).GetAPos();
                        IRect.width = IRect.right - IRect.left;
                        IRect.height = IRect.bottom - IRect.top;

                        if(_$(Item).Style('right') === 'auto'){
                            if(IRect.left <= SRect.left)
                                _$(Item).Style({left:'0px'});
                            else if(IRect.right >= SRect.right)
                                _$(Item).Style({left:(SRect.width - IRect.width).toString()+'px'});
                        }else if(_$(Item).Style('left') === 'auto'){
                            if(IRect.left <= SRect.left)
                                _$(Item).Style({right:(SRect.width - IRect.width).toString()+'px'});
                            else if(IRect.left >= SRect.right)
                                _$(Item).Style({right:'0px'});
                        }

                        if(_$(Item).Style('bottom') === 'auto'){
                            if(IRect.top <= SRect.top)
                                _$(Item).Style({top:'0px'});
                            else if(IRect.bottom >= SRect.bottom)
                                _$(Item).Style({top:(SRect.height - IRect.height).toString()+'px'});
                        }else if(_$(Item).Style('top') === 'auto'){
                            if(IRect.top <= SRect.top)
                                _$(Item).Style({bottom:(SRect.height - IRect.height).toString()+'px'});
                            else if(IRect.bottom >= SRect.bottom)
                                _$(Item).Style({bottom:'0px'});
                        }
                        Me.ItmPos(Item);
                    }
                }
            }
        };

        Me.Keyboard = {

            Move : function(EventObj, EObj){
                if(_$.Data.Get('_ECW_MODE') === 'edit' && SelItm){
                    var Pressed = EObj.KeyCode;
                    var X = _$(SelItm).Style('right') === 'auto' ? _$(SelItm).Style('left') : _$(SelItm).Style('right');
                    var Y = _$(SelItm).Style('bottom') === 'auto' ? _$(SelItm).Style('top') : _$(SelItm).Style('bottom');
                    var LR = _$(SelItm).Style('right') === 'auto' ? "left" : "right";
                    var TB = _$(SelItm).Style('bottom') === 'auto' ? "top" : "bottom";
                    var Result = {};

                    switch(Pressed){
                        case 65:
                            KeyMove = true;
                            X += (LR === "left") ? -1 : 1;
                            break;
                        case 68:
                            KeyMove = true;
                            X += (LR === "left") ? 1 : -1;
                            break;
                        case 87:
                            KeyMove = true;
                            Y += (TB === "top") ? -1 : 1;
                            break;
                        case 83:
                            KeyMove = true;
                            Y += (TB === "top") ? 1 : -1;
                            break;
                    }
                    if(KeyMove === true){
                        Result[LR] = X+'px';
                        Result[TB] = Y+'px';
                        _$(SelItm).Style(Result);
                    }
                }
            },

            Save : function(){
                if(_$.Data.Get('_ECW_MODE') === 'edit' && SelItm && KeyMove){
                    KeyMove = false;
                    Me.ItmPos(SelItm);
                }
            }
        };

        Me.Render = function(Items){
            if(typeof(Items) === 'string')
                Items = _$.Data.Unserialize(Items);
            if(typeof(Items) !== 'object')
                return;
            if(_$.Var.Len(Items) > 0){

                _$.Data.Set('ECWebItems', Items);
                _$(_$.Data.Get('_ECW_CONTENT')).ClearChild();
                Me.Reposition();

                var Main = _$.Data.Get('_ECW_CONTENT'),
                CreateItm = function(PObj, RObj, Parent){

                    for(var Key in Items){
                        if(Items[Key].Parent === Parent){
                            var Item = Items[Key];
                            Item.PObj = PObj;

                            if(_$.Data.Get('_ECW_MODE') === 'edit' && !Item.hasOwnProperty('Attrib'))
                                Item.Attrib = {
                                    ecweb: Item.Type,
                                    item: Key,
                                    parent: Item.Parent
                                };
                            Item.Attrib['_ecw_layer'] = Item.Layer;

                            if(Item.Align !== 'left' && Item.Align !== 'right' && Item.Align !== 'move')
                                Item.Align = 'left';

                            if(Item.Type === 'SECTION'){
                                Item.Obj = _$(RObj).Create('div',
                                {
                                    id : Item.id,
                                    classname : Item.Class,
                                    css : Item.Style,
                                    attr : Item.Attrib,

                                });
                                Item.Attrib = _$.Var.Remove(Item.Attrib, '_ecw_layer');
                                _$(Item.Obj).Style({overflow:'hidden'});
                                if(Item.Align === 'move'){
                                    if(typeof(Item.Pos) === 'object' && typeof(Item.According) === 'object'){
                                        _$(Item.Obj).Style({position:'absolute'});
                                        var Result = {X:'',Y:''};
                                        if(_$.Data.Get('_ECW_Responsive') && _$.Data.Get('_ECW_MODE') === 'view'){
                                            Result.X = Item.Pos.PX.toString() + '%';
                                            Result.Y = Item.Pos.PY.toString() + '%';
                                        }else{
                                            Result.X = Item.Pos.X.toString()+'px';
                                            Result.Y = Item.Pos.Y.toString()+'px';
                                        }
                                        if(Item.According.X === 'left')
                                            _$(Item.Obj).Style({left:Result.X, right:'auto'});
                                        else
                                            _$(Item.Obj).Style({right:Result.X, left:'auto'});

                                        if(Item.According.Y === 'top')
                                            _$(Item.Obj).Style({top:Result.Y, bottom:'auto'});
                                        else
                                            _$(Item.Obj).Style({bottom:Result.Y, top:'auto'});
                                    }
                                }else
                                    _$(Item.Obj).Style({cssFloat:Item.Align, styleFloat:Item.Align});
                                _$(Item.Obj).Selectable(false);

                                if(typeof(Item.Dimension) === 'object'){
                                    var Height = Item.Dimension.HEIGHT;
                                    if(_$.Data.Get('_ECW_Responsive') && /^[0-9\.]+%$/.test(Item.Dimension.HEIGHT)){
                                        _$(Item.Obj).Attr({_ecw_height:_$.Var.Float(Item.Dimension.HEIGHT)});
                                        Height = '0px';
                                    }
                                    _$(Item.Obj).Style({width:Item.Dimension.WIDTH, height:Height});
                                    if(_$.Data.Get('_ECW_Responsive') && /^[0-9\.]+%$/.test(Item.Dimension.HEIGHT)){
                                        var IRect = _$(Item.Obj).GetAPos();
                                        IRect.width = IRect.right - IRect.left;
                                        Item.Obj.style['height'] = ((IRect.width / 100) * _$.Var.Float(Item.Dimension.HEIGHT))+'px';
                                    }
                                }

                                var Class = '';
                                if(_$.Data.Get('_ECW_MODE') === 'edit' && Item.Align === 'move')
                                    Class = 'ecw_sec';

                                Item.Related = _$(Item.Obj).Create('div',
                                {
                                    attr : Item.Attrib,
                                    classname : Class,
                                    style: {position:'relative', width:'100%', height:'100%'}
                                });

                                if(_$.Data.Get('_ECW_MODE') === 'edit' && Item.Align === 'move')
                                    _$(Item.Related).Style({cursor:'move'});

                                _$(Item.Related).Selectable(false);
                                CreateItm(Item.Obj, Item.Related, Key);

                            }else if(Item.Type === 'ITEM'){

                                Item.Attrib['_ecw_layer'] = Item.Layer;
                                if(_$.Data.Get('_ECW_MODE') === 'edit')
                                    if(Item.hasOwnProperty('Class'))
                                        Item.Class += ' ecw_item';
                                    else
                                        Item.Class = 'ecw_item';

                                Item.Obj = _$(RObj).Create('div',
                                {
                                    id : Item.id,
                                    classname : Item.Class,
                                    css: Item.Style,
                                    attr: Item.Attrib,
                                    html: (Item.Content !== '' ?  Item.Content : '&nbsp;')
                                });

                                _$(Item.Obj).Selectable(false);

                                if(typeof(Item.Dimension) === 'object'){
                                    var Height = Item.Dimension.HEIGHT;
                                    if(_$.Data.Get('_ECW_Responsive') && /^[0-9\.]+%$/.test(Item.Dimension.HEIGHT)){
                                        _$(Item.Obj).Attr({_ecw_height:_$.Var.Float(Item.Dimension.HEIGHT)});
                                        Height = '0px';
                                    }
                                    _$(Item.Obj).Style({width:Item.Dimension.WIDTH, height:Height});
                                    if(_$.Data.Get('_ECW_Responsive') && /^[0-9\.]+%$/.test(Item.Dimension.HEIGHT)){
                                        var IRect = _$(Item.Obj).GetAPos();
                                        IRect.width = IRect.right - IRect.left;
                                        Item.Obj.style['height'] = ((IRect.width / 100) * _$.Var.Float(Item.Dimension.HEIGHT))+'px';
                                    }
                                }

                                if(typeof(Item.Pos) === 'object' && typeof(Item.According) === 'object'){
                                    _$(Item.Obj).Style({position:'absolute'});
                                    var Result = {X:'',Y:''};
                                    if(_$.Data.Get('_ECW_Responsive') && _$.Data.Get('_ECW_MODE') === 'view'){
                                        Result.X = Item.Pos.PX.toString() + '%';
                                        Result.Y = Item.Pos.PY.toString() + '%';
                                    }else{
                                        Result.X = Item.Pos.X.toString()+'px';
                                        Result.Y = Item.Pos.Y.toString()+'px';
                                    }
                                    if(Item.According.X === 'left')
                                        _$(Item.Obj).Style({left:Result.X, right:'auto'});
                                    else
                                        _$(Item.Obj).Style({right:Result.X, left:'auto'});

                                    if(Item.According.Y === 'top')
                                        _$(Item.Obj).Style({top:Result.Y, bottom:'auto'});
                                    else
                                        _$(Item.Obj).Style({bottom:Result.Y, top:'auto'});
                                }
                            }
                        }
                    }
                };
                CreateItm(Main, Main, "*");
                if(_$.Data.Get('_ECW_MODE') === 'edit')
                    _$('div[ecweb=ITEM]').Style({cursor:'move'});
                _$(Main).Create('div', {style:{clear:'both'}});
            }
            Me.Reposition();
        };

        Me.Edit = function(Code){
            _$.Fancy.Loading(_$.Ajax.Load.Fancy());
            _$.Ajax.Function(_$.Data.Get('_ECW_RESPONE') ,{UI:'ECWebPg', ECWebCode:Code},{Target:function(Data){
                _$.Data.Set('_ECW_MODE', 'edit');
                var CPage = _$.Data.Unserialize(Data), MainObj = _$.Data.Get('_ECW_CONTENT_MAIN');
                if(typeof(CPage) === 'undefined'){
                    _$.Fancy.Close();
                    _$.Fancy.MsgBox('Erro, Unable to load content.','System Error');
                    return false;
                }
                _$(_$.Data.Get('_ECW_CONTENT')).ClearChild();
                _$(MainObj).Attr({ecweb:'main'});
                document.title = CPage.Title;

                if(CPage.Style !== null)
                    MainObj.style.cssText = CPage.Style;

                var BGColor = _$(MainObj).Style('backgroundColor');
                if(typeof(BGColor) === 'string') BGColor = BGColor.replace(/\s+$/,''); else BGColor = '';
                if(BGColor === '' || BGColor === 'transparent')
                    _$(MainObj).Style({backgroundColor:'#fff'});
                _$(MainObj).Style({width:CPage.Width, margin:'0 auto', overflow:'hidden'});
                _$(_$.Data.Get('_ECW_VIEWPORT')).Attr({content:'width=' + CPage.Width + ', minimum-scale=' + (320 / _$(MainObj).Style('width'))});

                _$.Data.Set('_ECW_GRP', CPage.Grp, true);
                _$.Data.Set('_ECW_PG', CPage.Pg, true);
                _$.Data.Set('_ECW_Responsive', CPage.Responsive);
                _$.Data.Set('_ECW_MHPage', CPage.MHPage);
                _$.Data.Set('_ECW_LAYERS', CPage.Layers);

                _$.Data.Set('_ECW_JS_LOADER', "{SPG_JSCRIPT}?_ECW_GRP=" + CPage.Grp + "&_ECW_PG=" + CPage.Pg + "&IJS=");
                _$.Data.Set('_ECW_CSS_LOADER', "{SPG_STYLE}?_ECW_GRP=" + CPage.Grp + "&_ECW_PG=" + CPage.Pg + "&ICSS=");

                _$.Ajax.Function(_$.Data.Get('_ECW_RESPONE') ,{UI:'ECWebLst'},{Target:Me.Render, Callback:function(Data){
                    if(CPage.CSS !== null && CPage.CSS !== _$.Data.Get('_ECW_CUSTOM_CSS')){
                        _$.Data.Set('_ECW_CUSTOM_CSS', CPage.CSS);
                        _$("#ec_css").Remove();
                        _$.Common.AddCSS(_$.Data.Get('_ECW_CSS_LOADER') + CPage.CSS, {id:'ecw_css'});
                    }
                    _$.Fancy.Close();
                }});
            }});
        };

        Me.View = function(Code){
            _$.Fancy.Loading(_$.Ajax.Load.Fancy());
            _$.Ajax.Function(_$.Data.Get('_ECW_RESPONE') ,{UI:'ECWebGrp', ECWebCode:Code},{Target:function(Data){
                _$.Data.Set('_ECW_MODE', 'view');
                var CGrp = _$.Data.Unserialize(Data), MainObj = _$.Data.Get('_ECW_CONTENT_MAIN');
                if(typeof(CGrp) === 'undefined'){
                    _$.Fancy.Close();
                    _$.Fancy.MsgBox('Sorry, our system is not avaliable, please come back later, thank you.','Please come back later');
                    return false;
                }
                var CPage = {};
                _$(_$.Data.Get('_ECW_CONTENT')).ClearChild();
                _$(MainObj).DelAttr('ecweb');

                _$.Data.Set('_ECW_CGrp', CGrp);
                _$.Data.Set('_ECW_GRP', CGrp.Grp, true);
                _$.Data.Set('_ECW_BOffset', _$.Var.Default(CPage.BOffset, 0));
                _$.Data.Set('_ECW_Responsive', CGrp.Responsive);

                if(_$.Data.Get('_ECW_Responsive')){
                    _$(_$.Data.Get('_ECW_VIEWPORT')).Attr({content:'width=device-width, user-scalable=false;'});
                    var MWidth = _$.Common.View.Width(), CWidth = 0;
                    for(var PKey in CGrp.Pages){
                        if((MWidth <= CGrp.Pages[PKey].Width && CGrp.Pages[PKey].Width < CWidth) || CWidth === 0){
                            CPage = CGrp.Pages[PKey];
                            CWidth = CPage.Width;
                            CGrp.CPage = PKey;
                        }
                    }
                }else{
                    CPage = CGrp.Pages[CGrp.CPage];
                    _$(_$.Data.Get('_ECW_VIEWPORT')).Attr({content:'width=' + CPage.Width + ', minimum-scale=' + (320 / CPage.Width)});
                    _$(MainObj).Style({width:CPage.Width});
                }
                _$.Data.Set('_ECW_PG', CGrp.CPage, true);
                _$.Data.Set('_ECW_LAYERS', CPage.Layers);

                document.title = CPage.Title;

                if(CPage.Style !== null)
                    MainObj.style.cssText = CPage.Style;

                if(_$.Data.Get('_ECW_Responsive'))
                    _$(MainObj).Style({minWidth:'320px', width:'100%', maxWidth:CPage.Width+'px'});
                else
                    _$(MainObj).Style({width:CPage.Width});

                var BGColor = _$(MainObj).Style('backgroundColor');
                if(typeof(BGColor) === 'string') BGColor = BGColor.replace(/\s+$/,''); else BGColor = '';
                if(BGColor === '' || BGColor === 'transparent')
                    _$(MainObj).Style({backgroundColor:'#fff'});
                _$(MainObj).Style({margin:'0 auto', overflow:'hidden'});

                _$.Data.Set('_ECW_MHPage', CPage.MHPage);
                _$.Data.Set('_ECW_BOffset', _$.Var.Default(CPage.BOffset, 0));

                _$.Data.Set('_ECW_JS_LOADER', "{SPG_JSCRIPT}?IJS=");
                _$.Data.Set('_ECW_CSS_LOADER', "{SPG_STYLE}?ICSS=");

                Me.Render(CPage.Itms);

                if(CPage.CSS !== null && CPage.CSS !== _$.Data.Get('_ECW_CUSTOM_CSS')){
                    _$.Data.Set('_ECW_CUSTOM_CSS', CPage.CSS);
                    _$("#ecw_css").Remove();
                    _$.Common.AddCSS(_$.Data.Get('_ECW_CSS_LOADER') + CPage.CSS, {id:'ecw_css'});
                }

                if(CPage.JS !== null && CPage.JS !== _$.Data.Get('_ECW_CUSTOM_JS')){
                    _$.Data.Set('_ECW_CUSTOM_JS', CPage.JS);
                    _$("script#ecw_jscript").Remove();
                    _$.Common.AddScript(_$.Data.Get('_ECW_JS_LOADER') + CPage.JS, {id:'ecw_jscript'}, Me.ActiveNow);
                }
                _$.Fancy.Close();
            }});
        };

        Me.Container = function(){
            return _$.Data.Get('_ECW_CONTENT');
        };

        Me.Reposition = function(){
            var MHeight = 0, MainObj = _$.Data.Get('_ECW_CONTENT'), Layers = _$.Data.Get('_ECW_LAYERS'), BOffset = _$.Var.Float(_$.Data.Get('_ECW_BOffset'));
            if(_$.Data.Get('_ECW_Responsive')){
                for(var Layer=0; Layer<=Layers; Layer++){
                    _$('div[_ecw_layer=' + Layer + ']').Do(function(){
                        var Height = _$(this).Attr('_ecw_height');
                        if(Height !== null){
                        var IRect = _$(this).GetAPos(Obj);
                        IRect.width = IRect.right - IRect.left;
                        this.style['height'] = ((IRect.width / 100) * Height)+'px';
                        }
                    });
                }
            }

            _$('div[_ecw_layer=0]').Do(function(){
                var IRect = _$(this).GetAPos();
                if(IRect.bottom > MHeight)
                    MHeight = IRect.bottom;
            });
            MHeight += BOffset;
            if(_$.Data.Get('_ECW_MODE') === 'edit')
                MHeight += 50;

            if(_$.Data.Get('_ECW_MHPage') === 'page')
                MHeight = (MHeight < _$.Common.View.Height()) ? _$.Common.View.Height( ): MHeight;
            var MStyle = {height:MHeight+'px'};
            if(_$.Data.Get('_ECW_Responsive'))
                MStyle.fontSize = ((_$(MainObj).Style('width') / _$.Data.Get('_ECW_CGrp').Pages[_$.Data.Get('_ECW_PG')].Width) * 100 )+ '%';
            _$(MainObj).Style(MStyle);
            _$(_$.Data.Get('_ECW_CONTENT_MAIN')).Style(MStyle);
        };

        Me.Resize = function(){
            if(_$.Data.Get('_ECW_MODE') === 'view' && _$.Data.Get('_ECW_Responsive')){
                var CGrp = _$.Data.Get('_ECW_CGrp'), MWidth = _$.Common.View.Width(), CPage = {}, CWidth = 0;
                for(var PKey in CGrp.Pages){
                    if((MWidth <= CGrp.Pages[PKey].Width && CGrp.Pages[PKey].Width < CWidth) || CWidth === 0){
                        CPage = CGrp.Pages[PKey];
                        CWidth = CPage.Width;
                        CGrp.CPage = PKey;
                    }
                }
                if(_$.Data.Get('_ECW_PG') !== CGrp.CPage){
                    _$.Data.Set('_ECW_PG', CGrp.CPage, true);
                    _$.Data.Set('_ECW_LAYERS', CPage.Layers);
                    _$(_$.Data.Get('_ECW_CONTENT_MAIN')).Style({minWidth:'320px', width:'100%', maxWidth:CPage.Width+'px'});
                    Me.Render(CPage.Itms);
                    if(CPage.CSS !== null && CPage.CSS !== _$.Data.Get('_ECW_CUSTOM_CSS')){
                        _$.Data.Set('_ECW_CUSTOM_CSS', CPage.CSS);
                        _$("#ecw_css").Remove();
                        _$.Common.AddCSS(_$.Data.Get('_ECW_CSS_LOADER') + CPage.CSS, {id:'ecw_css'});
                    }
                    if(CPage.JS !== null && CPage.JS !== _$.Data.Get('_ECW_CUSTOM_JS') && _$.Data.Get('_ECW_MODE') === 'view'){
                        _$.Data.Set('_ECW_CUSTOM_JS', CPage.JS);
                        _$("script#ecw_jscript").Remove();
                        _$.Common.AddScript(_$.Data.Get('_ECW_JS_LOADER') + CPage.JS, {id:'ecw_jscript'});
                    }
                }else
                    Me.Reposition();
            }else
                Me.Reposition();
        };

        Me.Active = function(Callback){
            if(typeof(Callback) === 'function')
                Active_Lst.push(Callback);
        };

        Me.ActiveNow = function(){
            for(var i in Active_Lst)
                if(typeof(Active_Lst[i]) === 'function')
                    Active_Lst[i]();
            Active_Lst = [];
        };

        //Setup Intializing Variable
        _$.Data.Set('_ECW_RESPONE', "{SPG_FECWEB}");
        _$.Data.Set('_ECW_MODE', "");
        _$.Data.Set('_ECW_CUSTOM_JS','');
        _$.Data.Set('_ECW_CUSTOM_CSS','');

        //Intial ECWeb Mouse Drag Event
        _$(document).On("keydown", Me.Keyboard.Move);
        _$(document).On("keyup", Me.Keyboard.Save);
        _$(document).On("mousedown", Me.Mouse.Start);
        _$(document).On("mousemove", Me.Mouse.Drag);
        _$(document).On("mouseup", function(EventObj, EObj){
            if(EObj.Click === 1)
                Me.Mouse.Save();
        });

        //Intial ECWeb Window Contextmenu & Resize Event
        _$(document).On('contextmenu', Me.Menu.Open);
        _$(window).On(window, 'resize', Me.Resize);

        //Setup Meta Viewport
        if(_$.Data.Get('_ECW_VIEWPORT') === false)
            var ECWeb_Viewport = _$(_$.Common.Head()).Create('meta', {
                attr: {name:'viewport', content:'width=810, minimum-scale=0.3950617283950617'},
            });
        _$(document).InsertBeforeElm(ECWeb_Viewport, _$.Common.Head().firstChild);
        _$.Data.Set('_ECW_VIEWPORT', ECWeb_Viewport);

        //Setup ECWeb Container
        var ECWeb_Content_Main = _$(document).Create('div', {
            style: {display:'block', width:'810px', overflow:'hidden', background:'#fff', color:'#000', margin:'0 auto', padding:'0px', border:'none', borderSpacing:'0px'},
        }), ECWeb_Content = _$(ECWeb_Content_Main).Create('div', {style: {position:'relative', width:'100%', height:'100%'}});
        _$(document).InsertBeforeElm(ECWeb_Content_Main, _$.Common.Body().firstChild);
        _$.Data.Set('_ECW_CONTENT_MAIN', ECWeb_Content_Main);
        _$.Data.Set('_ECW_CONTENT', ECWeb_Content);

        }
        window._ECW = new _ECWeb;

})(window);