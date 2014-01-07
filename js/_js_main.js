(function(window){
    ////////////////////////////////////////////////////////////////
    //Start JavaCore Setting
    ////////////////////////////////////////////////////////////////
    var S = {
        Interval:10, EffectNow:true,
        Validate:true, ValidateAll:false,
        ImgPath:'image/', ImgSys:'images/_system/',
        Ajax :{
            LoadImg : 'preloader.gif',
            ShwLoad:true,
            Duplicate:true,
            MaxOffset : 0
        },
        Effect : {
            Interval : 2, Mode : 'ease',
            Start : 0, Rate : 3,
            Speed : 3
        },
        Fancy : {
            DWidth : 500,
            DHeight : 250,
            Symbol : 'symbol_information.png',
            CBtnImg : 'fancy_close.png'
        }
    },
    ////////////////////////////////////////////////////////////////
    //End JavaCore Setting
    ////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////
    //Start Labling Setting
    ////////////////////////////////////////////////////////////////

    I = {
        Ajax:{
            Title:'Ajax Error',
            Msg:'Address',
            Load:'Loading',
            Website:'Website',
            Status:'HTTP Status',
            XSupport:'Sorry, your internet browser is too old, dosn\'t support our website, please update your browser, thank you.'
        },
        FancyMsgOk : 'Ok',
        FancyMsgCancel : 'Cancel',
        HDesc:{}
    },

    ////////////////////////////////////////////////////////////////
    //End Labling Setting
    ////////////////////////////////////////////////////////////////

    C = {
        Tick:0, Ready:false,
        Opacity:'', Selectable:'',
        ScrollSize:-1,
        Browser:{
            Type:'',
            MajorVer:0,
            FullVer:''
        },
        EObj:{},
        Ajax:{
            Main:{},
            ID:-1, Load:false,
            DObj : null, Obj:{}, Now:-1,
            Work:false, Task:-1
        },
        Job:{ ID:-1, Now:-1 },
        Task:{ ID:-1 },
        Fancy : {
            BoxNow : -1,
            DSysLine : 'System',
            DMsgLine : 'Message',
        },
        PressedEsc : false,
        Keyboard : {
            8: "BACKSPACE", 9: "TAB", 13: "ENTER", 16: "SHIFT", 17: "CTRL", 18: "ALT",
            20: "CAPLOCK", 27: "ESC", 32: "SPACE", 33: "PAGEUP", 34: "PAGEDOWN", 36: "HOME",
            37: "LEFT", 38: "UP", 39: "RIGHT", 40: "DOWN", 45: "INSERT", 46: "DELETE", 91: "WINKEY",
            96: "PAD0", 97: "PAD1", 98: "PAD2", 99: "PAD3", 100: "PAD4", 101: "PAD5",
            102: "PAD6", 103: "PAD7", 104: "PAD8", 105: "PAD9", 106: "PAD*",
            107: "PAD+", 109: "PAD-", 110: "PAD.", 111: "PAD/",
            112: "F1", 113: "F2", 114: "F3", 115: "F4", 116: "F5", 117: "F6",
            118: "F7", 119: "F8", 120: "F9", 121: "F10", 122: "F11", 123: "F12",
            144: "NUMLOCK", 186: ";", 188: ",", 192: "`", 190: ".", 191: "/",
            219: "[", 220: "\\", 221: "]", 222: "'"
        }
    },

    L = {
        Ajax:{}, Data:{}, RValue:{}, Window:{},
        Protocol:{}, Event:{}, EvtObj:{}, Keyboard:{},
        Counter:{}, Validate:[], Menu:{},
        Fancy:[], Job:[], Task:[], Ready:[]
    },

    ElmSelector = function(ObjSel){
        if(ObjSel === document || ObjSel === window || IsDom(ObjSel))
            return [ObjSel];
        if(document.getElementById(ObjSel))
            return [document.getElementById(ObjSel)];
        if(window.hasOwnProperty('Sizzle') && typeof(Sizzle) === 'function')
    	   return Sizzle(ObjSel);
        if(IsDom(ObjSel))
            return [ObjSel];
        if(typeof(ObjSel) !== 'string')
            return [];
    	var Selected = new Array();
    	if(!document.getElementsByTagName)
            return Selected;
    	ObjSel = ObjSel.replace(/\s*([^\w])\s*/g,"$1"); //Remove the 'beutification' spaces
    	var Selectors = ObjSel.split(","), SElements = function(Context, Tag) { // Grab all of the tagName elements within current context
    		if (!Tag)
                Tag = '*';
    		var Found = new Array; // Get elements matching tag, filter them for class selector
    		for (var A = 0, LenA = Context.length; Con = Context[A], A < LenA; A++) {
    			var Eles;
    			if (Tag == '*')
                    Eles = Con.all ? Con.all : Con.getElementsByTagName("*");
    			else Eles = Con.getElementsByTagName(Tag);
        			for(var B = 0, LenB = Eles.length; B < LenB; B++)
                        Found.push(Eles[B]);
    		}
    		return Found;
    	}

    	COMMA:
    	for(var i = 0, LenI = Selectors.length; Selector = Selectors[i], i < LenI; i++) {
    		var Context = new Array(document), Inheriters = Selector.split(" ");

    		SPACE:
    		for(var j = 0, LenJ = Inheriters.length; Element = Inheriters[j], j < LenJ; j++) {
    			//This part is to make sure that it is not part of a CSS3 Selector
    			var L_Bracket = Element.indexOf("["), R_bracket = Element.indexOf("]"), Pos = Element.indexOf("#");//ID
    			if(Pos + 1 && !(Pos > L_Bracket && Pos < R_bracket)) {
    				var Parts = Element.split("#"), Tag = Parts[0], ID = Parts[1], Eles = document.getElementById(ID);
    				if(!Eles || (Tag && Eles.nodeName.toLowerCase() != Tag)) //Specified element not found
    					continue COMMA;
    				Context = new Array(Eles);
    				continue SPACE;
    			}

    			Pos = Element.indexOf(".");//Class
    			if(Pos + 1 && !(Pos > L_Bracket && Pos < R_bracket)) {
    				var Parts = Element.split('.'), Tag = Parts[0], ClsName = Parts[1], Found = SElements(Context, Tag);
    				Context = new Array;
     				for (var l = 0, Lenl = Found.length; Fnd = Found[l], l < Lenl; l++) {
     					if(Fnd.className && Fnd.className.match(new RegExp('(^|\s)' + ClsName + '(\s|$)')))
                            Context.push(Fnd);
     				}
    				continue SPACE;
    			}

    			if(Element.indexOf('[') + 1) {//If the char '[' appears, that means it needs CSS 3 parsing
    				// Code to deal with attribute selectors
    				if (Element.match(/^(\w*)\[(\w+)([=~\|\^\$\*]?)=?['"]?([^\]'"]*)['"]?\]$/))
    					var Tag = RegExp.$1, Attr = RegExp.$2, Operator = RegExp.$3, Value = RegExp.$4;
    				var Found = SElements(Context, Tag);
    				Context = new Array;
    				for (var l=0, Lenl = Found.length; Fnd = Found[l], l < Lenl; l++) {
     					if(Operator == '=' && Fnd.getAttribute(Attr) != Value)continue;
    					if(Operator == '~' && !Fnd.getAttribute(Attr).match(new RegExp('(^|\\s)' + Value + '(\\s|$)')))continue;
    					if(Operator == '|' && !Fnd.getAttribute(Attr).match(new RegExp('^' + Value + '-?')))continue;
    					if(Operator == '^' && Fnd.getAttribute(Attr).indexOf(Value)!=0)continue;
    					if(Operator == '$' && Fnd.getAttribute(Attr).lastIndexOf(Value)!=(Fnd.getAttribute(Attr).length-value.length))continue;
    					if(Operator == '*' && !(Fnd.getAttribute(Attr).indexOf(Value)+1)) continue;
    					else if(!Fnd.getAttribute(Attr))continue;
    					Context.push(Fnd);
     				}
    				continue SPACE;
    			}

    			//Tag selectors - no class or id specified.
    			var Found = SElements(Context, Element);
    			Context = Found;
    		}
    		for (var o = 0, LenO = Context.length; o < LenO; o++)
                Selected.push(Context[o]);
    	}
    	return Selected;
    },

    ElmEnum = function(ObjSel, Callback){
        if(typeof(ObjSel) === 'object'){
            if(ObjSel === document || ObjSel === window || (ObjSel.hasOwnProperty('nodeType') && ObjSel.nodeType === 1)){
                if(typeof(Callback) === 'function')
                    Callback.apply(ObjSel);
                return [ObjSel];
            }else
                return false;
        }else if(typeof(ObjSel) === 'string'){
            if (document.getElementById(ObjSel)){
                if(typeof(Callback) === 'function')
                    Callback.apply(document.getElementById(ObjSel));
                return [document.getElementById(ObjSel)];
            }else{
                var ObjArray = ElmSelector(ObjSel);
                if(ObjArray.length > 0){
                    if(typeof(Callback) === 'function')
                        for(var Key in ObjArray)
                            Callback.apply(ObjArray[Key]);
                    return ObjArray;
                }else
                    return false;
            }
        }else
            return false;
    },

    IsDom = function(Obj) {
        try{return Obj instanceof HTMLElement;}catch(e){
            try{
                return (typeof Obj==="object") && (Obj.nodeType===1) && (typeof Obj.style === "object") && (typeof Obj.ownerDocument ==="object");
            } catch(e){return false;}
        }
    },

    Var = {
        Int : function(Variable){
            if(Var.IsNumber(Variable))
                return Math.round(parseFloat(Variable));
            else
                return 0;
        },

        Float : function(Variable){
            if(Var.IsNumber(Variable))
                return parseFloat(Variable);
            else
                return 0;
        },

        Clone : function (Source) {
          var Copy = new Object;
          for (Prop in Source) {
            if (Source.hasOwnProperty(Prop))
              Copy[Prop] = Source[Prop];
          }
          return Copy;
        },

        Default : function(Variable, Default, Member){
            if(Default === undefined)Default = null;
            if(Member === undefined)Member = null;
            if(Member === null){
                if(Variable === undefined || Variable === null)
                    return Default;
                else if(typeof(Variable) === "number" && (isNaN(Variable) || !isFinite(Variable)))
                    return Default;
                else
                    return Variable;
            }else{
                if(typeof(Variable) === 'object' && Variable !== null && Variable.hasOwnProperty(Member))
                    return Var.Default(Variable[Member], Default);
                else
                    return Default;
            }
        },

        Remove : function(Variable, Prop){
            if(typeof(Variable) === 'object'){
                var Temp = Variable;
                Variable = {};
                for(var Key in Temp)
                    if(Key !== Prop)
                        Variable[Key] = Temp[Key];
                return Variable;
            } else if(typeof(Variable) === 'array'){
                var Temp = Variable.slice((Prop) + 1 || Variable.length);
                Variable.length = Prop < 0 ? Variable.length + Prop : Prop;
                return Variable.push.apply(Variable, Temp);
            }
        },

        Len : function(AObj)
        {
            var Length = 0;
            if(typeof(AObj) === 'object')
                for (var Key in AObj)
                    if(AObj.hasOwnProperty(Key))
                        Length++;
          return Length;
        },

        Split : function(Separetor, Value)
        {
            var Length = Value.length;
            var Ary = new Array();
            var Data = '';
            var Char = '';
            for(var i=0;i<=Value.length; i++){
                Char = Value.substr(i,1);
                Debug(Char);
                if(Value.substr(i,1) === "\\"){
                    i++;
                    Char = Value.substr(i,1);
                    Data += Char;
                }else if(Char === Separetor.substr(0,1)){
                    Ary.push(Data);
                    Data = '';
                }else
                    Data += Char;
            }
          return Ary;
        },

        Append : function (Value, AddValue, Mode)
        {
            if(typeof(Value) !== 'object')
                return Value;
            if(typeof(AddValue) !== 'object')
                return Value;
            Mode = Var.Default(Mode, 2);
            var SObj = Value;
            switch(Mode){
                case 0:
                    for(var Key in AddValue)
                        if(!SObj.hasOwnProperty(Key))
                            SObj[Key] = AddValue[Key];
                break;

                case 1:
                    for(var Key in AddValue)
                        if(SObj.hasOwnProperty(Key))
                            SObj[Key] = AddValue[Key];
                break;

                case 2:
                    for(var Key in AddValue)
                        SObj[Key] = AddValue[Key];
                break;

                case 3:
                    SObj = AddValue;
            }
            return SObj;
        },

        Sort: function(AryObj) {
            if(typeof(AryObj) !== 'object')
                return AryObj;
            else if(AryObj instanceof Array)
                return AryObj.sort();
            else {
                var Arry = [];
                for (var Prop in AryObj)
                    if (AryObj.hasOwnProperty(Prop))
                        Arry.push({'Key': Prop, 'Value': AryObj[Prop]});
                Arry.sort(function(A, B) { return A.Value - B.Value; });
                return Arry;
            }
        },

        LPad : function(Pad, Len, Value) {
            if(typeof(Value) !== 'string')
                Value = Value.toString();
            while (Value.length < Len)
                Value = Pad + Value;
            return Value;
        },

        RPad : function(Pad, Len, Value) {
            if(typeof(Value) !== 'string')
                Value = Value.toString();
            while (Value.length < Len)
                Value += Pad;
            return Value;
        },

        LTrim : function(Value){
            if(typeof(Value) !== 'string')
                return Value;
            return Value.replace(/^\s+/,'');
        },

        RTrim : function(Value){
            if(typeof(Value) !== 'string')
                return Value;
            return Value.replace(/\s+$/,'');
        },

        Trim : function(Value){
            if(typeof(Value) !== 'string')
                return Value;
            return Value.replace(/^\s+|\s+$/g, '');
        },

        TrimAll : function(Value){
            if(typeof(Value) !== 'string')
                return Value;
            return Value.replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,'').replace(/\s+/g,' ');
        },

        InArray : function(Needle, Haystack, Strict) {
            Strict = Var.Default(Strict, false);
            if(typeof(Haystack) === 'object' && Haystack !== null)
                for(var Key in Haystack)
                    if((Strict === false && Haystack[Key] == Needle) || (Strict === true && Haystack[Key] === Needle))
                        return true;
            return false;
        },

        IsSet : function(Variable){
            if(Variable === undefined || Variable === null)
                return false;
            else
                return true;
        },

        IsEmpty : function(Variable){
            if(Variable === undefined || Variable === null || Variable === 0 || Variable === '')
                return true;
            else if(typeof(Variable) === 'object' && Var.Len(Variable) === 0)
                return true;
            else
                return false
        },

        IsNumber : function(Variable) {
            Variable = parseFloat(Variable);
            return !isNaN(Variable) && isFinite(Variable);
        },

        IsString : function(Variable){
            return typeof(Variable) === 'string' && isNaN(Variable);
        }
    },

    Data = {
        Get : function(Key, Type){
            if(!L.Data.hasOwnProperty(Key))
                return false;
            else{
                Type = Type || '';
                switch(Type.toLowerCase()){
                    case "int":
                        return Var.Int(L.Data[Key].Value);

                    case "float":
                    case "double":
                        return Var.Float(L.Data[Key].Value);

                    default:
                    return L.Data[Key].Value;
                }
            }
        },

        Set : function(Key, Value, Send, Public){
            L.Data[Key] = {
                Value : Value,
                Send : Send,
                Public : Public
            };
        },

        Send : function(Key, Send){
            if(!this.List.hasOwnProperty(Key))
                L.Data[Key].Send = Send;
        },

        Global : function(Key, Public){
            L.Data[Key].Public = Public;
        },

        Remove : function(Key){
            L.Data = Var.Remove(L.Data, Key);
        },

        Clear : function(Public){
            for(var Key in L.Data)
                if((Public === true && L.Data[Key].Public === true) || L.Data[Key].Public === false)
                    L.Data = Var.Remove(L.Data, Key);
        },

        GetUrl : function(){
            var UrlValue = "";
            for(var Key in L.Data){
                if(L.Data[Key].Send === true){
                    UrlValue += ((UrlValue == '') ? '' : '&') + Common.UrlEncode(Key) + '=' + Common.UrlEncode(L.Data[Key].Value);
                }
            }
            return UrlValue;
        },

        Serialize : function (Content) {
            if (typeof(Content) === 'object') {
                if(Content === null)
                    return "null";
                var SOutput = "";
                if (Content instanceof Array) {
                      for (var NID = 0; NID < Content.length; SOutput += this.Serialize(Content[NID]) + ",", NID++);
                        return "[" + SOutput.substr(0, SOutput.length - 1) + "]";
                }
                if (Content.toString !== Object.prototype.toString)
                    return Content.toString().replace(/"/g, "\\$&");
                    for (var SProp in Content)
                        SOutput += SProp.replace(/"/g, "\\$&") +':'+ this.Serialize(Content[SProp]) + ",";
                return "{" + SOutput.substr(0, SOutput.length - 1) + "}";
            }
            return (typeof Content === "string") ? "\"" + Content.replace(/"/g, "\\$&") + "\"" : String(Content);
        },

        Unserialize : function (Content) {
            var JObject = undefined;
            try {JObject = eval("(" + Content + ")");}
            catch (e){
                Debug.Error(e, 'Data.Unserialize');
                return false;
            }
            return JObject;
        },

        IsJSon : function(JSonTxt){
            if (typeof(JSonTxt) === 'string' && /^[\],:{}\s]*$/.test(JSonTxt.replace(/\\['\\\/bfnrtu]/g, '@').
            replace(/"[^'\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
            replace(/(?:^|:|,)(?:\s*\[)+/g, '')))
                    return true;
            else
                return false
        },

        Cookie : function(Value, ExpDay)
        {
            if(!Value){
                var Value = document.cookie, Start = Value.indexOf(" " + _.Key + "=");
                if (Start === -1)
                    Start = Value.indexOf(_.Key + "=");
                if (Start === -1)
                    return null;
                else
                {
                    Start = Value.indexOf("=", Start) + 1;
                    var End = Value.indexOf(";", Start);
                    if (End == -1)
                        End = Value.length;
                    return unescape(Value.substring(Start, End));
                }
            }else{
                var Exdate = new Date();
                Exdate.setDate(Exdate.getDate() + ExpDay);
                var C_Value = escape(Value) + ((exdays==null) ? "" : "; expires=" + Exdate.toUTCString());
                document.cookie = _.Key + "=" + C_Value;
            }
        },

        NumSplit : function(Number, MinLen){
            if(typeof(Number) === 'number'){
                var Value = Number.toString();
                if(typeof(MinLen) === 'number' && MinLen > 0){
                    Value = Var.LPad("0", MinLen, Value);
                }
                Value = Value.split('');
                for(var Key in Value)
                    Value[Key] = Var.Int(Value[Key]);
                return Value;
            }
        },

        Nl2br : function(StrValue) {
            return (StrValue + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2');
        },

        StrUrlToLinks : function(StrValue, Target) {
            Target = Var.Default(Target, '');
            var UrlExp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
            return StrValue.replace(UrlExp, "<a href='$1' target='" + Target + "'>$1</a>");
        }
    },

    //------------------------------------------------------------------------
    // JavaScript Return Method function
    //------------------------------------------------------------------------
    Return = {
        Clear : function(){
            L.RValue = {};
        },

        Insert : function(Key, Value){
            L.RValue[Key] = Value;
        },

        Value : function(){

            if(Var.Len(L.RValue) > 1)
                return L.RValue;
            else
                for(var Key in L.RValue)
                    return L.RValue[Key];
        }
    },

    //------------------------------------------------------------------------
    // JavaScript Common Function
    //------------------------------------------------------------------------
    Common = {

        Random : function(Min, Max){
            return Math.floor(Math.random() * (Max - Min + 1) + Min);
        },

        RGBtoHex : function(R, G, B) {
            return Common.ToHex(R) + Common.ToHex(G) + Common.ToHex(B);
        },

        ToHex : function(N) {
            if (N === null) return "00";
            N = Var.Int(N); if (N == 0 || isNaN(N)) return "00";
            N = Math.max(0, N); N=Math.min(N, 255); N = Math.round(N);
            return "0123456789ABCDEF".charAt((N - N % 16) / 16) + "0123456789ABCDEF".charAt(N % 16);
        },

        ImageUrl : function(RImgUrl, Type){
            var Path = '';
            switch(Type){
                case "system": Path = S.ImgSys; break;
                default: Path = S.ImgPath; break;
            }
            if(/:/.test(RImgUrl) || /\/\//.test(RImgUrl) || /^\//.test(RImgUrl))
                return RImgUrl;
            else
                return Path + RImgUrl;
        },

        UrlEncode : function(DataObj){
            if(typeof(DataObj) === 'string')
        	   return encodeURIComponent(DataObj);
            else
                return encodeURIComponent(Data.Serialize(DataObj));
        },

        UrlDecode : function(DataObj, Serialized){
            if(Serialized === true)
                return Data.Unserialize(decodeURIComponent(DataObj));
            else
                return decodeURIComponent(DataObj);
        },

    	BrowserType : function() {
    	   if(C.Browser.Type === ''){
            	var agt = navigator.userAgent.toLowerCase();
            	if(agt.indexOf('msie') > -1)C.Browser.Type='msie';
            	if(agt.indexOf('chrome') > -1)C.Browser.Type='chrome';
            	if(agt.indexOf('seamonkey') > -1)C.Browser.Type='seamonkey';
            	if(agt.indexOf('firefox') > -1)C.Browser.Type='firefox';
            	if(agt.indexOf('safari') > -1)C.Browser.Type='safari';
            	if(agt.indexOf('opera') > -1)C.Browser.Type='opera';
            	C.Browser="unknown";
                return C.Browser.Type;
            }else
                return C.Browser.Type;
    	},

        BrowserVer : function(VerType) {
            if(C.Browser.MajorVer === 0 && C.Browser.FullVer === ''){
                var NavAgt = navigator.userAgent.toLowerCase();
                var Version = "";
                var Browser = Common.BrowserType();
                var VOffset;
                switch(Browser){
                    case "msie":Version = NavAgt.substring(NavAgt.indexOf("msie") + 5);break;
                    case "chrome":Version = NavAgt.substring(NavAgt.indexOf("chrome") + 7);break;
                    case "firefox":Version = NavAgt.substring(NavAgt.indexOf("firefox") + 8);break;
                    case "seamonkey":Version = NavAgt.substring(NavAgt.indexOf("seamonkey") + 10);break;

                    case "safari":
                        if ((VOffset = NavAgt.indexOf("Version")) != -1)
                            Version = NavAgt.substring(NavAgt.indexOf("safari") + 8);
                        else
                            Version = NavAgt.substring(NavAgt.indexOf("safari") + 7);
                    break;

                    case "opera":
                        if ((VOffset = NavAgt.indexOf("Version")) != -1)
                            Version = NavAgt.substring(NavAgt.indexOf("opera") + 8);
                        else
                            Version = NavAgt.substring(NavAgt.indexOf("opera") + 6);
                    break;
                }
                C.Browser.MajorVer = Var.Int(Version);
                C.Browser.FullVer = Version;
            }
            switch(VerType){
                case "Full":return C.Browser.Type + '/' + C.Browser.FullVer;break;
                case "Version":return C.Browser.FullVer; break;
                default:return C.Browser.MajorVer;
            }
        },

        Html : function(){
            return document.getElementsByTagName('html')[0];
        },

        Head : function(){
            return document.getElementsByTagName('head')[0];
        },

        Body : function(){
            return document.getElementsByTagName('body')[0];
        },

        View : {
        	Width : function() {
                var Width = 0;
                if (Common.Body() && Common.Body().offsetWidth)Width = Common.Body().offsetWidth;
                if (document.compatMode=='CSS1Compat' && document.documentElement && document.documentElement.offsetWidth )Width = document.documentElement.offsetWidth;
                if (window.innerWidth)Width = window.innerWidth;
                return Var.Float(Width);
            },
        	Height : function() {
                var Height = 0;
                if (Common.Body() && Common.Body().offsetHeight)Height = Common.Body().offsetHeight;
                if (document.compatMode=='CSS1Compat' && document.documentElement && document.documentElement.offsetHeight )Height = document.documentElement.offsetHeight;
                if (window.innerHeight)Height = window.innerHeight;
                return Var.Float(Height);
        	}
        },

        Scroll : {

            To : function(Left, Top){
                window.scrollTo(Var.Default(Left, 0),Var.Default(Top, 0));
            },

            Left : function() {
                if (document.all || document.documentElement)
                    return Var.Float((!document.documentElement.scrollLeft) ? document.all.scrollLeft : document.documentElement.scrollLeft);
                else
                    return Var.Float(window.pageXOffset);
            },

            Top : function(){
                    if (document.all || document.documentElement)
                        return Var.Float((!document.documentElement.scrollTop) ? document.all.scrollTop : document.documentElement.scrollTop);
                    else
                        return  Var.Float(window.pageYOffset);
            },

            Width : function(){
                if(document.width !== undefined)
                    return document.width
                else
                    return Math.max(Common.Body().scrollWidth, Common.Body().offsetWidth, document.documentElement.clientWidth, document.documentElement.scrollWidth, document.documentElement.offsetWidth);
            },

            Height : function(){
                if(document.height !== undefined)
                    return document.height
                else
                    return Math.max(Common.Body().scrollHeight, Common.Body().offsetHeight, document.documentElement.clientHeight, document.documentElement.scrollHeight, document.documentElement.offsetHeight);
            },

            Size : function () {
                if(C.ScrollSize < 0){
                    var Outer = Document.Create('div', {
                        style : {
                            position:'absolute',
                            top:'0px',
                            left:'0px',
                            visibility:'hidden',
                            width:'200px',
                            height:'150px',
                            overflow:'hidden'
                        }
                    }),
                    Inner = _$(Outer).Create('p', {
                        style : {
                            width:'100%',
                            height:'200px'
                        }
                    }),
                    Wa = Inner.offsetWidth;
                    Outer.style.overflow = 'scroll';
                    var Wb = Inner.offsetWidth;
                    if (Wa === Wb) Wb = Outer.clientWidth;
                    _$(Outer).Remove();
                    C.ScrollSize = Wa - Wb;
                }
                return C.ScrollSize;
            }
        },

        HttpData : function(HData){
            if(HData === null || HData === undefined)
                return '';
            else if(typeof(HData) === 'object'){
                var HTTP_Data = '';
                for(var Key in HData)
                    if(/_FORM_DATA/i.test(Key))
                        HTTP_Data += ((HTTP_Data === '')?'':'&') + Common.FormData(HData[Key]);
                    else
                        HTTP_Data += ((HTTP_Data === '')?'':'&') + Key + '=' + Common.UrlEncode((typeof(HData[Key]) === 'undefined') ? '' : ((typeof(HData[Key]) === 'object') ? Data.Serialize(HData[Key]) : HData[Key]));
                return HTTP_Data;
            }else
                return Data;
        },

        GenCode : function(Len){
                var i = 0, Result = '',
                SChr=function(){
                    var iChr = 0;
                    switch(Common.Random(1,3)){
                        case 1:iChr=Common.Random(48,57);break;
                        case 2:iChr=Common.Random(65,90);break;
                        case 3:iChr=Common.Random(97,122);
                    }
                    return String.fromCharCode(iChr);
                };
                Len = Var.Default(Len, 8);
                for(i=0; i<Len; i++)
                    Result += SChr();
                return Result;
        },

    	FormData : function(ObjSel) {
    		var Data = '';
            _$(ObjSel).Do(function(){
        		var Children = this.getElementsByTagName('input');
                if(Children.length > 0){
                    for (var i = 0; i < Children.length; i++) {
                        if (Children.item(i).nodeType == 1 && Children.item(i).id !== '' && Children.item(i).type != 'image' && Children.item(i).type != 'button' && Children.item(i).type != 'submit' && Children.item(i).type != 'reset') {
                            switch(Children.item(i).type){
                                case "checkbox":
                                    if(Children.item(i).checked == true)
                                        Data += ((Data!=='')?'&':'') +  Children.item(i).id + "=" + Common.UrlEncode(Children.item(i).value);
                                    break;

                                case "radio":
                                    if(Children.item(i).checked == true)
                                        Data += ((Data!=='')?'&':'') +  Children.item(i).id + "=" + Common.UrlEncode(Children.item(i).value);
                                    break;

                                default:
                                	Data += ((Data!=='')?'&':'') +  Children.item(i).id + "=" + Common.UrlEncode(Children.item(i).value);
                            }
                        }
                    }
                }

        		var Children = this.getElementsByTagName('textarea');
                if(Children.length > 0){
                    for (var i = 0; i < Children.length; i++) {
                    	if (Children.item(i).nodeType == 1 && Children.item(i).id !== '') {
                    		Data += ((Data!=='')?'&':'') +  Children.item(i).id + "=" + Common.UrlEncode(Children.item(i).value);
                    	}
                    }
                }

        		var Children = this.getElementsByTagName('select');
                if(Children.length > 0){
                    for (var i = 0; i < Children.length; i++) {
                    	if (Children.item(i).nodeType == 1 && Children.item(i).id !== '') {
                    		Data += ((Data!=='')?'&':'') +  Children.item(i).id + "=" + Common.UrlEncode(Children.item(i).value);
                    	}
                    }
                }
            });
            return Data;
    	},

        Post: function(Url, Params, Target) {
            Url = Var.Default(Url, '');
            if(Url === '')
                return;
            Target = Var.Default(Target, '');
            var Form = Document.Create('form', {
                attrib:{
                    action: Url,
                    method: 'post',
                    target: Target
                },
            });

            for(var Key in Params) {
                if(Params.hasOwnProperty(Key)) {
                    _$(Form).Create('input', {
                        parent: Form,
                        attrib:{
                            type: 'hidden',
                            name: Key,
                            value: Params[Key]
                        }
                    });
                 }
            }
            Form.submit();
            _$(Form).Remove();
        },

        RGBColor : function(Color) { //Color String to array
            if(typeof(Color) !== 'string'){
            	var CObj = {
                	Red : 255,
                	Green : 255,
                	Blue : 255
                }
                return CObj;
            }else if(Color.toLowerCase().indexOf('rgb') != -1 && Color.toLowerCase().indexOf('rgba(0, 0, 0, 0)') == -1 && Color.toLowerCase().indexOf('rgb(0, 0, 0)') == -1){
                if(Color.toLowerCase().indexOf('rgba') != -1)
                    var digits = /(.*?)rgba\((\d+), (\d+), (\d+)\)/.exec(Color);
                else
                    var digits = /(.*?)rgb\((\d+), (\d+), (\d+)\)/.exec(Color);
            	var CObj = {
            	   Red : Var.Int(Var.Default(digits[2], 255)),
            	   Green : Var.Int(Var.Default(digits[3], 255)),
            	   Blue : Var.Int(Var.Default(digits[4], 255))
                }
                return CObj;

            }else{
                Color = Color.replace("0x", "");
                Color = Color.replace("#", "");
                Color = Color.replace(/ /g,'');
                Color = Color.toLowerCase();

                var Color_List = {
                    aliceblue: 'f0f8ff', antiquewhite: 'faebd7', aqua: '00ffff', aquamarine: '7fffd4', azure: 'f0ffff',
                    beige: 'f5f5dc',bisque: 'ffe4c4',black: '000000',blanchedalmond: 'ffebcd',blue: '0000ff',blueviolet: '8a2be2',
                    brown: 'a52a2a',burlywood: 'deb887',
                    cadetblue: '5f9ea0',chartreuse: '7fff00',chocolate: 'd2691e',coral: 'ff7f50',cornflowerblue: '6495ed',
                    cornsilk: 'fff8dc',crimson: 'dc143c',cyan: '00ffff',
                    darkblue: '00008b',darkcyan: '008b8b',darkgoldenrod: 'b8860b',darkgray: 'a9a9a9',darkgreen: '006400',
                    darkkhaki: 'bdb76b',darkmagenta: '8b008b',darkolivegreen: '556b2f',darkorange: 'ff8c00',darkorchid: '9932cc',
                    darkred: '8b0000',darksalmon: 'e9967a',darkseagreen: '8fbc8f',darkslateblue: '483d8b',darkslategray: '2f4f4f',
                    darkturquoise: '00ced1',darkviolet: '9400d3',deeppink: 'ff1493',deepskyblue: '00bfff',dimgray: '696969',dodgerblue: '1e90ff',
                    feldspar: 'd19275',firebrick: 'b22222',floralwhite: 'fffaf0',forestgreen: '228b22',fuchsia: 'ff00ff',
                    gainsboro: 'dcdcdc',ghostwhite: 'f8f8ff',gold: 'ffd700',goldenrod: 'daa520',gray: '808080',green: '008000',greenyellow: 'adff2f',
                    honeydew: 'f0fff0',hotpink: 'ff69b4',indianred : 'cd5c5c',indigo : '4b0082',ivory: 'fffff0',
                    khaki: 'f0e68c',lavender: 'e6e6fa',lavenderblush: 'fff0f5',lawngreen: '7cfc00',lemonchiffon: 'fffacd',lightblue: 'add8e6',
                    lightcoral: 'f08080',lightcyan: 'e0ffff',lightgoldenrodyellow: 'fafad2',lightgrey: 'd3d3d3',lightgreen: '90ee90',
                    lightpink: 'ffb6c1',lightsalmon: 'ffa07a',lightseagreen: '20b2aa',lightskyblue: '87cefa',lightslateblue: '8470ff',
                    lightslategray: '778899',lightsteelblue: 'b0c4de',lightyellow: 'ffffe0',lime: '00ff00',limegreen: '32cd32',linen: 'faf0e6',
                    magenta: 'ff00ff',maroon: '800000',mediumaquamarine: '66cdaa',mediumblue: '0000cd',mediumorchid: 'ba55d3',
                    mediumpurple: '9370d8',mediumseagreen: '3cb371',mediumslateblue: '7b68ee',mediumspringgreen: '00fa9a',
                    mediumturquoise: '48d1cc',mediumvioletred: 'c71585',midnightblue: '191970',mintcream: 'f5fffa',
                    mistyrose: 'ffe4e1',moccasin: 'ffe4b5',
                    navajowhite: 'ffdead',navy: '000080',
                    oldlace: 'fdf5e6',olive: '808000',olivedrab: '6b8e23',orange: 'ffa500',orangered: 'ff4500',orchid: 'da70d6',
                    palegoldenrod: 'eee8aa',palegreen: '98fb98',paleturquoise: 'afeeee',palevioletred: 'd87093',papayawhip: 'ffefd5',
                    peachpuff: 'ffdab9',peru: 'cd853f',pink: 'ffc0cb',plum: 'dda0dd',powderblue: 'b0e0e6',purple: '800080',
                    red: 'ff0000',rosybrown: 'bc8f8f',royalblue: '4169e1',saddlebrown: '8b4513',salmon: 'fa8072',
                    sandybrown: 'f4a460',seagreen: '2e8b57',seashell: 'fff5ee',sienna: 'a0522d',silver: 'c0c0c0',skyblue: '87ceeb',
                    slateblue: '6a5acd',slategray: '708090',snow: 'fffafa',springgreen: '00ff7f',steelblue: '4682b4',
                    tan: 'd2b48c',teal: '008080',thistle: 'd8bfd8',tomato: 'ff6347',turquoise: '40e0d0',
                    violet: 'ee82ee',violetred: 'd02090',
                    wheat: 'f5deb3',white: 'ffffff',whitesmoke: 'f5f5f5',
                    yellow: 'ffff00',yellowgreen: '9acd32'
                };
                if (Color_List.hasOwnProperty(Color))
                    Color = Color_List[Key];
            	var RGB = Var.Int(Color, 16),
                CObj = {
            	   Red : (RGB & (255 << 16)) >> 16,
            	   Green : (RGB & (255 << 8)) >> 8,
            	   Blue : (RGB & 255)
                };
            	return CObj;
            }
        },

        GetDate : function(DateSep, DateObj){ //Return like yyyy-mm-dd hh:mm:ss
            if(!DateObj instanceof Date)
                DateObj = new Date();
            DateSep = Var.Default(DateSep, '-');
            return DateObj.getFullYear() + DateSep + (DateObj.getMonth() + 1) + DateSep + DateObj.getDate();
        },

        GetTime : function(TimeSep, TimeObj){ //Return like hh:mm:ss
            if(!TimeObj instanceof Date)
                TimeObj = new Date();
            TimeSep = Var.Default(TimeSep, ':');
            return TimeObj.getHours() + TimeSep + TimeObj.getMinutes() + TimeSep + TimeObj.getSeconds();
        },

        AddCSS : function(Link, Property){
            if(typeof(Property) !== 'object' || Property === null)
                Property = {};
            Property.parent = Var.Default(Property, Common.Head(), 'parent');
            Property.rel = Var.Default(Property, 'stylesheet', 'rel');
            Property.type = Var.Default(Property, 'text/css', 'type');
            Property.href = Link;
            _$(Common.Head()).Create('link', Property);
        },

        AddScript : function(Src, Property, Callback){
            if(typeof(Property) !== 'object' || Property === null)
                Property = {};
            Property.parent = Var.Default(Property, Common.Head(), 'parent');
            Property.type = Var.Default(Property, 'text/javascript', 'type');
            Property.src = Src;
            var JObj = Document.Create('script', Property);
            if(typeof(Callback) === 'function'){
                var ScriptReady = function(){
                    if (!this.readyState || this.readyState === "loaded" || this.readyState === "complete") {
                        JObj.onload = null;
                        JObj.onreadystatechange = null;
                        Callback();
                    }
                };
                JObj.onload = ScriptReady;
                JObj.onreadystatechange = ScriptReady;
            }
        },

        ImgPreload : function(Json){
            List = Data.Unserialize(Json);
            if(List !== undefined){
                var Images = new Array()
                for (i = 0; i < List.length; i++) {
                	Images[i] = new Image();
                	Images[i].src = List[i];
                }
            }
        },

        HasPlaceholder : function () {
            var test = document.createElement('input');
            return ('placeholder' in test);
        },

       Preload : function(Content, Callback, Preloader){
            var Own = false;
            if(!Preloader){
                Own = true;
                Preloader = Document.Create('div', {style:{cssText:'display:block; visibility: hidden; position:absolute;'}});
            }
            _$(Preloader).Html(Content);
            var ImgObj = Preloader.getElementsByTagName("img"), TCObj = ImgObj.length, CNObj = 0,
            Load_Done = function(){
                Content = _$(Preloader).Html();
                if(Own === true)
                    _$(Preloader).Remove();
                if(typeof(Callback) === 'function')
                    Callback(Content);
            };

            if(TCObj > 0){
                for(var i in ImgObj){
                    if(_$(ImgObj[i]).Attr(ImgObj[i], 'src') === ''){
                        _$(ImgObj[i]).Remove();
                        CNObj++;
                        if(TCObj === CNObj)
                            Load_Done();
                    } else if (typeof(ImgObj[i].naturalWidth) !== "undefined" && ImgObj[i].naturalWidth !== 0){
                        CNObj++;
                        if(TCObj === CNObj)Load_Done();
                    } else {
                        _$(ImgObj[i]).On('load', function(){
                            CNObj++;
                            if (typeof(this.naturalWidth) === "undefined" || this.naturalWidth === 0)
                                _$(this).Remove();
                            if(TCObj === CNObj)Load_Done();
                        });
                        _$(ImgObj[i]).On('error', function(){
                            CNObj++;
                            _$(this).Remove();
                            if(TCObj === CNObj)Load_Done();
                        });
                    }
                }
            }else
                Load_Done();
       }
    },

    //-----------------------------------------------------------------------
    // JavaScript Counter Function
    //------------------------------------------------------------------------
    Counter = {

        Apply : function(Config){
            var Now = new Date();
            var Result = {
                Count : Var.Float(Var.Default(Config,0,'Count')),
                Time : Var.Default(Config, Now.toUTCString(), 'Time'),
                Method : Var.Default(Config, 'normal', 'Method'),
                Callback : Var.Default(Config, false, 'Callback'),
                Numsplit : Var.Default(Config, false, 'Numsplit'),
                Interval : Var.Int(Var.Default(Config, 100, 'Interval')),
                Start : Var.Default(Config, true,'Start'),
            }
            if(Config.Interval <= 0)
                Config.Interval = 100;
            return Result;
        },

        Now : function(Name, Config){
            if(L.Counter.hasOwnProperty(Name))
                return;
            Config = Counter.Apply(Config);
            if(typeof(Config.Callback) === 'function'){
                L.Counter[Name] = {
                    Callback : Config.Callback,
                    Job : 0,
                    Numsplit : Config.Numsplit
                };
                L.Counter[Name].Timer = Counter.Calcurate(Name, 0);
                if(Config.Start === true)
                    Counter.Timing(Name);
                L.Counter[Name].Job = Job.Work(function(){Counter.Timing(Name);}, 'Loop', Config.Interval);
            }
        },

        Up : function(Name, Config){
            if(L.Counter.hasOwnProperty(Name))
                return;
            Config = Counter.Apply(Config);
            if(typeof(Config.Callback) === 'function'){
                L.Counter[Name] = {
                    Callback : Config.Callback,
                    Method : Config.Method,
                    Job : 0,
                    Tick : Config.Count,
                    Numsplit : Config.Numsplit,
                    Timer : {}
                };
                L.Counter[Name].Timer = Counter.Calcurate(Name, 0);
                if(Config.Start === true)
                    L.Counter[Name].Callback(Timer);
                if(Config.Count > 0)
                    L.Counter[Name].Job = setInterval(function(){Counter.Remaining_Up(Name);}, Config.Count, Config.Interval);
            }
        },

        Down : function(Name, Config){
            if(L.Counter.hasOwnProperty(Name))
                return;
            Config = Counter.Apply(Config);
            if(typeof(Config.Callback) === 'function'){
                L.Counter[Name] = {
                    Callback : Config.Callback,
                    Method : Config.Method,
                    Job : 0,
                    Tick : Config.Count,
                    Numsplit : Config.Numsplit,
                    Timer : {}
                };
                Counter.Calcurate(Name);
                if(Config.Start === true)
                    L.Counter[Name].Callback(L.Counter[Name].Timer);
                if(Config.Count > 0)
                    L.Counter[Name].Job = Job.Work(function(){Counter.Remaining_Down(Name);}, Config.Count, Config.Interval);
            }
        },

        Time : function(Name, Config){
            if(L.Counter.hasOwnProperty(Name))
                return;
           Config = Counter.Apply(Config);
           var Now = new Date();
	       var ETime = Date.parse(Config.Time);
           Config.Count = Var.Float(Math.floor((ETime - Now) / 1000));
            if(typeof(Config.Callback) === 'function'){
                L.Counter[Name] = {
                    Callback : Config.Callback,
                    Method : Config.Method,
                    Job : 0,
                    Tick : Config.Count,
                    Numsplit : Config.Numsplit,
                    Timer : {}
                };
                Counter.Calcurate(Name);
                if(Config.Start === true)
                    L.Counter[Name].Callback(L.Counter[Name].Timer);
                if(Config.Count > 0)
                    L.Counter[Name].Job = Job.Work(function(){Counter.Remaining_Down(Name);}, Config.Count, 100);
            }
        },

        Stop : function(Name){
            if(L.Counter.hasOwnProperty(Name)){
                Job.Break(L.Counter[Name].Job);
                L.Counter = Var.Remove(L.Counter, Name);
            }
        },

        Timing : function(Name){
            var Now = new Date();
            var Timer = {
                Yrs : Now.getFullYear(),
                Mth : Now.getMonth(),
                Week : Now.getDay(),
                Day : Now.getDate(),
                Hrs : Now.getHours(),
                Min : Now.getMinutes(),
                Sec : Now.getSeconds(),
                Ms : Now.getMilliseconds()
            };
            if(L.Counter[Name].Numsplit === true){
                Timer.Yrs = Data.NumSplit(Timer.Yrs, 4);
                Timer.Mth = Data.NumSplit(Timer.Mth, 2);
                Timer.Week = Data.NumSplit(Timer.Week, 1);
                Timer.Day = Data.NumSplit(Timer.Day, 2);
                Timer.Hrs = Data.NumSplit(Timer.Hrs, 2);
                Timer.Min = Data.NumSplit(Timer.Min, 2);
                Timer.Sec = Data.NumSplit(Timer.Sec, 2);
                Timer.Ms = Data.NumSplit(Timer.Ms, 3);
            }
            L.Counter[Name].Callback(Timer, Name);
        },

        Remaining_Down : function(Name){
            var Timer = Counter.Calcurate(Name, L.Counter[Name].Timer.Tick - 1);
            L.Counter[Name].Callback(Timer, Name);
            if(Timer.End === true)
                Counter.Stop(Name);
        },

        Remaining_Up : function(Name){
            var Timer = Counter.Calcurate(Name, L.Counter[Name].Timer.Tick + 1);
            if(L.Counter[Name].Timer.Tick === L.Counter[Name].Tick)
                Timer.End = true;
            L.Counter[Name].Callback(Timer, Name);
            if(Timer.End === true)
                Counter.Stop(Name);
        },

        Calcurate : function(Name, CountSec){
            if(typeof(CountSec) === 'undefined')
                CountSec = L.Counter[Name].Tick;
            L.Counter[Name].Timer = {
                Tick : CountSec,
                Day : Math.floor(CountSec / (60 * 60 * 24)),
                Hrs : Math.floor(CountSec / (60 * 60)),
                Min : Math.floor(CountSec / 60),
                Sec : CountSec % 60,
                End : CountSec === 0 ? true : false
            };
            switch(L.Counter[Name].Method){

                case 'hrs':
                case 'hours':
                    L.Counter[Name].Timer.Day = 0;
                    L.Counter[Name].Timer.Min %= 60;
                break;

                case 'min':
                case 'minutes':
                L.Counter[Name].Timer.Day = 0;
                L.Counter[Name].Timer.Hrs = 0;
                break;

                default:
                L.Counter[Name].Timer.Hrs %= 24;
                L.Counter[Name].Timer.Min %= 60;
            }
            if(L.Counter[Name].Numsplit === true){
                L.Counter[Name].Timer.Day = Data.NumSplit(L.Counter[Name].Timer.Day, 3);
                L.Counter[Name].Timer.Hrs = Data.NumSplit(L.Counter[Name].Timer.Hrs, 2);
                L.Counter[Name].Timer.Min = Data.NumSplit(L.Counter[Name].Timer.Min, 2);
                L.Counter[Name].Timer.Sec = Data.NumSplit(L.Counter[Name].Timer.Sec, 2);
            }
            return L.Counter[Name].Timer;
        }
    },


    //------------------------------------------------------------------------
    // Keyboard Function
    //------------------------------------------------------------------------
    Keyboard = {
        Bind : function(Key, Action, AltKey, ShiftKey, CtrlKey){
            var SKey;
            if(typeof(Action) !== 'function' || (typeof(Key) !== 'string' && typeof(Key) !== 'number'))
                return false;
            if(typeof(Key) === 'string') SKey = Key.toUpperCase(); else SKey = Key;
            if(typeof(AltKey) !== "boolean") AltKey = false;
            if(typeof(ShiftKey) !== "boolean") ShiftKey = false;
            if(typeof(CtrlKey) !== "boolean") CtrlKey = false;
            L.Keyboard[SKey] = {
                BndKey : SKey,
                Action : Action,
                AltKey : AltKey,
                ShiftKey : ShiftKey,
                CtrlKey : CtrlKey
            };
        },

        Unbind : function(Key){
            L.Keyboard = Var.Remove(L.Keyboard, Key);
        },

        Clear : function(){
            L.Keyboard = {};
        },

        Pressed : function(EventObj, EObj){
            if(EObj.Target.tagName === 'TEXTAREA')
                return true;
            if(EObj.Target.tagName === 'INPUT' && EObj.Target.type === 'text' && EObj.KeyCode !== 27)
                return true;
            if(C.Keyboard.hasOwnProperty(EObj.KeyCode)) RealKey = C.Keyboard[EObj.KeyCode]; else RealKey = EObj.KeyChar;
            var Prevent = false, FancyOpend = {
                All : Fancy.Opening('*'),
                System : Fancy.Opening(),
                Msg : Fancy.Opening(C.Fancy.DMsgLine)
            };
            if((FancyOpend.Msg || FancyOpend.System) && RealKey === "ESC"){
                Prevent = true;
                if(FancyOpend.Msg)
                    Fancy.Close(C.Fancy.DMsgLine, null, true)
                else
                    Fancy.Close(null, null, true);
            }else if(FancyOpend.All && RealKey === "ESC" && C.PressedEsc === false){
                C.PressedEsc = true;
                Prevent = true;
                Job.Work(function(){C.PressedEsc = false;}, 1, 90);
            }else if(FancyOpend.All && C.PressedEsc === true && RealKey === "ESC"){
                Prevent = true;
                C.PressedEsc = false;
                Fancy.Close('*', null, true);
            }else if(FancyOpend.Msg && RealKey === "ENTER"){
                Prevent = true;
                Fancy.Close(C.Fancy.DMsgLine, null, true);
            }else{
                for (var i in L.Keyboard)
                {
                    var IsPressed = false, SKeyboard = L.Keyboard[i];
                    if(typeof(SKeyboard.BndKey) === 'string' && SKeyboard.BndKey === RealKey)
                        IsPressed = true;
                    else if(typeof(SKeyboard.BndKey) === 'number' && SKeyboard.BndKey === BKey)
                        IsPressed = true;
                    if(IsPressed && EObj.Alt === SKeyboard.AltKey &&
                    EObj.Ctrl === SKeyboard.CtrlKey && EObj.Shift === SKeyboard.ShiftKey){
                        Prevent = true;
                        SKeyboard.Action();
                    }
                }
            }
            if(Prevent === true)
                Event.PreventDefault(EventObj);
        }
    },

    //------------------------------------------------------------------------
    // JavaScript Window Function etc. Open New Dialog Window
    //------------------------------------------------------------------------
    Window = {
        Open : function(Url, WWidth, WHeight, Name, Config){
            WWidth = Var.Int(WWidth);
            WHeight = Var.Int(WHeight);
            WWidth = WWidth || 120;
            WHeight = WHeight || 80;
        	var WLeft = ((Var.Int(screen.width) - Var.Int(WWidth)) / 2);
        	var WTop = ((Var.Int(screen.height) - Var.Int(WHeight)) / 2);
            Config = ',' + Config || '';
            Config = 'left=' + WLeft + ',top=' + WTop + ',width=' + WWidth + 'px,height=' + WHeight + 'px,toolbar=0,location=0,directories=0,status=0,menubar=0,copyhistory=no' + Config;
            L.Window[Name] = window.open(Url, Name, Config);
            L.Window[Name].moveTo(WLeft, WTop);
        },

        Close : function(Name){
            L.Window[Name].close();
        },

        CloseAll : function(){
            for(Name in L.Window)
                L.Window[Name].close();
        },

        Print : function(Name){
            for(Name in L.Window)
                L.Window[Name].print();
        },

        Move : function(Name, Left, Top){
            L.Window[Name].moveTo(Left, Top);
        },

        MoveAll : function(Left, Top){
            for(Name in L.Window)
                L.Window[Name].moveTo(Left, Top);
        },

        Resize : function(Name, Width, Height){
            L.Window[Name].resizeTo(Width, Height);
        },

        ResizeAll : function(Width, Height){
            for(Name in L.Window)
                L.Window[Name].resizeTo(Width, Height);
        }
    },

    //------------------------------------------------------------------------
    // Menu / Context Menu Object
    //------------------------------------------------------------------------
    Menu = {
        MCreate : function(Name, MData, MClass, MIClass){
            if(L.Menu.hasOwnProperty(Name))
                Menu.MDestroy(Name, true);
            var DMenu = {
                Main : $(Document.Create('div', {attr:{_menu:Name},
                style:{overflow:'visible', display:'none', position:'absolute', left:'0px', top:'0px', width:'0px', height:'0px'}})),
                Obj : {},
                Opened : false,
                Data : MData
            };
            DMenu.Obj['root'] = DMenu.Main.Create('ul', {
                classname:MClass,
                style:{position:'absolute', display:'none', listStyleType:'none', margin:'0px'},
                attr:{_menu:Name, _mnulevel:0}
            });
            var MenuObj = $(DMenu.Obj['root']);
            MenuObj.Opacity(0);
            MenuObj.MouseLeave(Menu.MEClose);
            for(var Html in MData){
                var Href = MData[Html];
                if(typeof(Href) === 'object'){
                    if(Href.hasOwnProperty('_type') && Href['_type'] === 'item'){
                        var Item = $(MenuObj.Create('li', Var.Remove(Href, '_type')));
                        Item.ClassName(MIClass);
                        Item.Html(Html);
                        Item.Attr({_menu:Name, _mnulevel:0});
                    }else{
                        var ISubId = Common.GenCode(12),
                        Item = $(MenuObj.Create('li', {
                            classname: MIClass, html:Html,
                            attr:{_menu:Name, _mnulevel:0, _mnusub:ISubId}
                        }));
                        Item.HOver(Menu.MIOpen, Menu.MIClose, false ,true);
                        Menu.MCreateSub(DMenu, Name, ISubId, Href, MClass, MIClass, 1);
                    }
                }else if(typeof(Href) === 'string'){
                    MenuObj.Create('li', {
                        classname: MIClass,
                        html:Html, href:Href + '##Menu:Close::' + Name,
                        attr:{_menu:Name, _mnulevel:0}
                    });
                }
            }
            if(MenuObj.Style('padding') <= 2)
                MenuObj.Style({padding:'2px'});
            L.Menu[Name] = DMenu;
        },

        MCreateSub : function(DMenu, Name, SubId, MData, MClass, MIClass, MLevel){
            DMenu.Obj[SubId] = DMenu.Main.Create('ul', {
                classname:MClass,
                style:{position:'absolute', display:'none', listStyleType:'none', margin:'0px'},
                attr:{_menu:Name, _mnulevel:MLevel, _mnusubid:SubId}
            });
            var MenuObj = $(DMenu.Obj[SubId]);
            MenuObj.Opacity(0);
            MenuObj.MouseLeave(Menu.MEClose);
            for(var Html in MData){
                var Href = MData[Html];
                if(typeof(Href) === 'object'){
                    if(Href.hasOwnProperty('_type') && Href._type === 'item'){
                        Href = Var.Remove(Href, '_type');
                        var Item = $(MenuObj.Create('li', Href));
                        Item.ClassName(MIClass);
                        Item.Html(Html);
                        Item.Attr({_menu:Name, _mnulevel:MLevel, _mnusubid:SubId});
                    }else{
                        var ISubId = Common.GenCode(12),
                        Item = $(MenuObj.Create('li', {
                            classname: MIClass, html:Html,
                            attr:{_menu:Name, _mnulevel:0, _mnusubid:SubId, _mnusub:ISubId}
                        }));
                        Item.HOver(Menu.MIOpen, Menu.MIClose, false ,true);
                        Menu.MCreateSub(DMenu, Name, ISubId, Href, MClass, MIClass, MLevel + 1);
                    }
                }else if(typeof(Href) === 'string'){
                    MenuObj.Create('li', {
                        classname: MIClass,
                        html:Html, href:Href + '##Menu:Close::' + Name,
                        attr:{_menu:Name, _mnulevel:MLevel, _mnusubid:SubId}
                    });
                }
            }
            if(MenuObj.Style('padding') <= 2)
                MenuObj.Style({padding:'2px'});
        },

        MIOpen : function(){
            var ItmObj = $(this), MName = ItmObj.Attr('_menu'), MSubID = ItmObj.Attr('_mnusub');
            if(MName !== null && MSubID !== null && L.Menu.hasOwnProperty(MName) && L.Menu[MName].Opened === true){
                var MenuObj = $(L.Menu[MName].Obj[MSubID]);
                MenuObj.Style({display:'block'});
                var LRect = ItmObj.GetAPos(), MRect = MenuObj.GetAPos(),
                MWidth = _$.Common.Scroll.Width(), MHeight = _$.Common.Scroll.Height(), X=0, Y=0;
                if(LRect.right + MRect.width > MWidth) X = LRect.left - MRect.width; else X = LRect.right;
                if(LRect.top + MRect.height > MHeight) Y = LRect.top - MRect.height; else Y = LRect.top;
                MenuObj.Style({left:X+'px', top:Y+'px'});
                MenuObj.ClearEffect('Effect.Fade');
                if(S.EffectNow === true)
                    MenuObj.Fade({Target:100});
                else
                    MenuObj.Opacity(100);
            }
        },

        MIClose : function(EventObj, EObj){
            var ItmObj = $(this), MenuObj = $(EObj.Related), MName = ItmObj.Attr('_menu'), MLevel = Var.Int(ItmObj.Attr('_mnulevel')),
             MSubID = ItmObj.Attr('_mnusub'), TMenu = Var.IsEmpty(MenuObj.Attr('_menu')), TMSubID = MenuObj.Attr('_mnusubid');
            if(MName !== null && MSubID !== null && L.Menu.hasOwnProperty(MName) && L.Menu[MName].Opened === true && MSubID !== TMSubID && !TMenu){
                var MenuObj = $(L.Menu[MName].Obj[MSubID]);
                if(S.EffectNow === true)
                    MenuObj.Fade({Target:0, Callback:function(){
                        $(this).Style({left:'0px',top:'0px', display:'none'});
                    }});
                else{
                    MenuObj.Opacity(0);
                    MenuObj.Style({left:'0px',top:'0px', display:'none'});
                }
            }else if(TMenu)
                Menu.MClose(MName);
        },

        MOpen : function(Name, MnuX, MnuY, Offset){
            Offset = Var.Default(Offset, 2);
            if(L.Menu.hasOwnProperty(Name)){
                L.Menu[Name].Opened = true;
                L.Menu[Name].Offset = Offset;
                if(IsDom(this)){
                    var Rect = $(this).GetAPos();
                    if(!Var.IsNumber(MnuX))
                        MnuX = Rect.left;
                    if(!Var.IsNumber(MnuY))
                        MnuY = Rect.bottom;
                }
                L.Menu[Name].Main.Style({display:'block'});
                var MenuObj = $(L.Menu[Name].Obj['root']);
                MenuObj.Style({display:'block'});
                MenuObj.ClearEffect('Effect.Fade');
                MenuObj.Opacity(0);
                var MWidth = Common.Scroll.Width(), MHeight = Common.Scroll.Height(), MRect = MenuObj.GetAPos(), X=0, Y=0;
                if(MnuX + MRect.width > MWidth) X = MnuX - MRect.width + Offset; else X = MnuX - Offset;
                if(MnuY + MRect.height > MHeight) Y = MnuY - MRect.height + Offset; else Y = MnuY - Offset;
                MenuObj.Style({left:X+'px',top:Y+'px'});
                if(S.EffectNow === true)
                    MenuObj.Fade({Target:100});
                else
                    MenuObj.Opacity(100);
            }
        },

        MOnContext : function(){
            Offset = 2;
            var Name = $(this).Attr('menu');
            if(L.Menu.hasOwnProperty(Name)){
                L.Menu[Name].Opened = true;
                L.Menu[Name].Main.Style({display:'block'});
                var MenuObj = $(L.Menu[Name].Obj['root']);
                MenuObj.Style({display:'block'});
                MenuObj.ClearEffect('Effect.Fade');
                MenuObj.Opacity(0);
                var MWidth = Common.Scroll.Width(), MHeight = Common.Scroll.Height(), MRect = MenuObj.GetAPos(), X=0, Y=0;
                if(C.EObj.X + MRect.width > MWidth) X = C.EObj.X - MRect.width + Offset; else X = C.EObj.X - Offset;
                if(C.EObj.Y + MRect.height > MHeight) Y = C.EObj.Y - MRect.height + Offset; else Y = C.EObj.Y - Offset;
                MenuObj.Style({left:X+'px',top:Y+'px'});
                if(S.EffectNow === true)
                    MenuObj.Fade({Target:100});
                else
                    MenuObj.Opacity(100);
            }
        },

        MEClose : function(EventObj, EObj){
            var MenuObj = $(this), MName = MenuObj.Attr('_menu'), MLevel = Var.Int(MenuObj.Attr('_mnulevel')), TMName = $(EObj.Related).Attr('_menu'), TMLevel = Var.Int($(EObj.Related).Attr('_mnulevel'));
            if(MName !== null && MLevel !== null && L.Menu.hasOwnProperty(MName) && L.Menu[MName].Opened === true && (MName !== TMName || MName === TMName && MLevel > TMLevel)){
                if(MName !== TMName || MLevel === 0){
                    for(var i in L.Menu[MName].Obj){
                        var MObj = $(L.Menu[MName].Obj[i]);
                        MObj.ClearEffect('Effect.Fade');
                        if(S.EffectNow === true)
                            MObj.Fade({Target:0, Callback:function(){
                                $(this).Style({left:'0px',top:'0px', display:'none'});
                            }});
                        else
                            MObj.Style({left:'0px',top:'0px', display:'none'});
                    }
                    L.Menu[MName].Opened = false;
                }else{
                    MenuObj.ClearEffect('Effect.Fade');
                    if(S.EffectNow === true)
                        MenuObj.Fade({Target:0, Callback:function(){
                            MenuObj.Style({left:'0px',top:'0px', display:'none'});
                        }});
                    else
                        MenuObj.Style({left:'0px',top:'0px', display:'none'});
                }
            }
        },

        MClose : function(Name){
            if(L.Menu.hasOwnProperty(Name) && L.Menu[Name].Opened === true){
                L.Menu[Name].Opened = false;
                for(var i in L.Menu[Name].Obj){
                    var MenuObj = $(L.Menu[Name].Obj[i]);
                    MenuObj.ClearEffect('Effect.Fade');
                    if(S.EffectNow === true)
                        MenuObj.Fade({Target:0, Callback:function(){
                            $(this).Style({left:'0px',top:'0px', display:'none'});
                        }});
                    else
                        MenuObj.Style({left:'0px',top:'0px', display:'none'});
                }
            }
        },

        MDestroy : function(Name, Force){
            if(L.Menu.hasOwnProperty(Name)){
                var MenuObj = L.Menu[Name].Main;
                if(S.EffectNow === true && Force !== true)
                    MenuObj.Fade({Target:0, Callback:function(){
                        MenuObj.Remove();
                        L.Menu = Var.Remove(L.Menu, Name);
                    }});
                else{
                    MenuObj.Remove();
                    L.Menu = Var.Remove(L.Menu, Name);
                }
            }
        }
    };

    //------------------------------------------------------------------------
    // JavaScript Ajax Object
    //------------------------------------------------------------------------
    Ajax = {

        DefaultObj : function(Obj){
            if(IsDom(Obj) || (Obj.hasOwnProperty('Type') && Obj.Type() === 'Object'))
                C.DObj = Obj;
        },

        //------------------------------------------------------------------------
        // JavaScript Ajax Loading Partten Function
        //------------------------------------------------------------------------
        Load :
         {
            Obj : function(Obj, LoadImg){
                var ImgLoad = (Var.IsEmpty(LoadImg) ? Common.ImageUrl(S.Ajax.LoadImg, 'system') : Common.ImageUrl(LoadImg)),
                Rect = Obj.GetAPos(),
                MWidth = Obj.Style('width'),
                MHeight = Obj.Style('height'),
                PHeight = MHeight === 'auto' || MHeight <= 0 ? 15 : MHeight,
                PWidth = MWidth === 'auto' || MWidth <= 0 ? 15 : MWidth,
                PSize = 0;
                PHeight = PHeight > 64 ? 64 : PHeight;
                PWidth = PWidth > 64 ? 64 : PWidth;
                if(PWidth > PHeight) PSize = PHeight; else PSize = PWidth;
                return "<div style='display:block !important; overflow:hidden !important; width:" + PSize + "px !important; height:" + PSize + "px !important; background:url(\"" + ImgLoad +
                "\") no-repeat center center !important; background-size:"+PSize+"px !important; border:none !important;' />&nbsp;</div>";
            },

            Fancy : function(LoadImg){
                var ImgLoad = (Var.IsEmpty(LoadImg) ? Common.ImageUrl(S.Ajax.LoadImg, 'system') : Common.ImageUrl(LoadImg));
                return ImgLoad;
            }
        },

        //------------------------------------------------------------------------
        // JavaScript Ajax Syetem Function
        //------------------------------------------------------------------------
        Return : function(Http_Status, Url){
            Fancy.Close('*');
        	Fancy.MsgBox('<span style=\'color:#000044; font-weight:900;\'>' + I.Ajax.Status + ' : ' + Http_Status + ' ' + (I.HDesc[Http_Status] !== undefined ? I.HDesc[Http_Status] : '') +
            '</span><blockquote><span style=\'color:#000088;\'>' + I.Ajax.Website + '</span> : <span style=\'color:#880000; font-weight:400;\'>\'' +
            Url + '\'</span></blockquote>', '<span style=\'color:#880000;\'>' + I.Ajax.Title + '</span>',500, 250);
        },

        Create : function()
        {
        	C.Ajax.Obj = false;
            if (window.XMLHttpRequest) { // Check if Mozilla, Safari,...
                C.Ajax.Obj = new XMLHttpRequest();
            } else if (window.ActiveXObject) { //Check if New IE
                    try {C.Ajax.Obj = new ActiveXObject("Msxml2.XMLHTTP");}
                    catch (e) {
                        try {C.Ajax.Obj = new ActiveXObject("Microsoft.XMLHTTP");}  //Check if Old IE
                        catch (e) { //Error not XML Object found
                            Fancy.MsgBox(I.Ajax.XSupport, I.Ajax.Title, 500, 300);
                            return false;
                        }
                    }
                }
        },

        Duplicate : function(Index){
            if(!S.Ajax.Duplicate){
                for (var Key in L.Ajax)
                    if(L.Ajax[Key].Url === L.Ajax[Index].Url &&
                        L.Ajax[Key].Data === L.Ajax[Index].Data &&
                        L.Ajax[Key].Method === L.Ajax[Index].Method &&
                        L.Ajax[Key].Flow === L.Ajax[Index].Flow &&
                        Index !== Key)
                        return true;
                return false;
            }else
                return false;
        },

        Request : function()
        {
            C.Ajax.Work = false;
            if(C.Ajax.Load === false && Var.Len(L.Ajax) > 0){
                for (var Key in L.Ajax){
                    if(!Ajax.Duplicate(Key)){
                        C.Ajax.Now = Key;
                        C.Ajax.Work = true;
                        break;
                    }else
                        L.Ajax = Var.Remove(L.Ajax, Key);
                }
            }
            if(C.Ajax.Work === true){
                C.Ajax.Load = true;
                C.Ajax.ID++;
        		Ajax.Create();
                var HTTP_Data = Data.GetUrl(), Requester = L.Ajax[C.Ajax.Now];

                if(typeof(Requester.Url) === 'undefined'){
                    C.Ajax.Load = false;
                    L.Ajax = Var.Remove(L.Ajax, C.Ajax.Now);
                    return false;
                }

                switch(Requester.Method){

                    case "POST":
                        if(HTTP_Data.length > 0)
                            Requester.Data += ((Requester.Data !== '') ? '&' : '') + HTTP_Data;
                        C.Ajax.Obj.open("POST", Requester.Url, true);
                        break;

                    case "GET":
                        if(HTTP_Data.length > 0)
                            Requester.Data += ((Requester.Data !== '') ? '&' : '') + HTTP_Data;

                        if(Requester.Data.length > 0)
                            Requester.Url += ((Requester.Url.indexOf("?") == -1) ? '?': '&') + Requester.Data;
                        Requester.Data = "";
                  		C.Ajax.Obj.open("GET", Requester.Url, true);
                    break;

                }

                //Setup When Ajax Return
        		C.Ajax.Obj.onreadystatechange = function(){
              		Ajax.Response(C.Ajax.ID, C.Ajax.Now);
                }

        		//Setup HTTP Request Header
            		C.Ajax.Obj.setRequestHeader('Content-type', 'application/x-www-form-urlencoded;charset=UTF-8');
            		C.Ajax.Obj.setRequestHeader('Accept', '*/*');
            		C.Ajax.Obj.setRequestHeader('Pragma', 'no-cache');
            		C.Ajax.Obj.setRequestHeader('Cache-Control', 'no-cache, must-revalidate');

        		//Setup Loading Status
                switch(Requester.Flow){
                    case "OBJECT":
                        if(typeof(Requester.Target) === 'string' ||IsDom(Requester.Target))
                            Requester.Target = $(Requester.Target);
                        if(Requester.ShwLoad === true)
                            Requester.Target.Html(Ajax.Load.Obj(Requester.Target, Requester.LoadImg));
                        break;

                    case "FANCY":
                        if(Requester.ShwLoad === true)
                            Fancy.Loading(Ajax.Load.Fancy(Requester.LoadImg));
                        break;
                }

                //Send data to URL
                switch(Requester.Method){
                    case "GET":
                        C.Ajax.Obj.send(null);
                        break;

                    case "POST":
                  		C.Ajax.Obj.send(Requester.Data);
                    break;
                }
                L.Ajax[C.Ajax.Now] = Requester;
            }
        },

        Response : function(Ajax_ID, Task_ID){
            switch(C.Ajax.Obj.readyState){
            case 4:
            	switch(C.Ajax.Obj.status){
                    case 200:
                        var Requester = L.Ajax[Task_ID], SData = '', Web_Data = '', JS_Attach = '', Calback = Requester.Callback;
                        if(typeof(C.Ajax.Obj.responseText) === 'string' && C.Ajax.Obj.responseText !== ''){
                            SData = C.Ajax.Obj.responseText.split(/\1\1\r\n\r\n/);
                            Web_Data = SData[0];
                            if(SData.length > 0)
                                JS_Attach = SData[1];
                        }

                        switch(Requester.Flow){

                        case "ONLY":
                            console.log(SData);
                            if(typeof(Calback) === 'function')
                                Calback.apply(this, SData);
                            break;

                        case "FANCY":
                            var CFancy = Var.Default(Requester, {}, 'Fancy');
                            CFancy.Box = Var.Default(CFancy, {}, 'Box');
                            CFancy.Box.Width = Var.Default(CFancy.Size, 0, 'Width');
                            CFancy.Box.Height = Var.Default(CFancy.Size, 0, 'Height');
                            CFancy.Callback = function(){
                                Ajax.AttachJs(JS_Attach, Requester.Url);
                                if(typeof(Calback) === 'function')
                                    Calback.apply(this, SData)
                            };
                            Fancy.Show(Web_Data, CFancy);
                            break;

                        case "OBJECT":
                            Requester.Target.Do(function(){
                                var Obj = this;
                        		switch(Obj.tagName){

                        			case "TEXT":
                        			case "TEXTAREA":
                                        _$(Obj).Value(Web_Data);
                                        if(typeof(Calback) === 'function')
                                                Calback.apply(Obj, SData);
                        				break;

                        			default:
                                       if(Requester.Effect === true && Requester.ShwLoad === true){
                                            _$(Obj).Opacity(0);
                                            Common.Preload(Web_Data, function(){

                                                //Start Javascript Effect
                                                _$(Obj).Fade({Target:100});
                                                Ajax.AttachJs(JS_Attach, Requester.Url);
                                                if(typeof(Calback) === 'function')
                                                    Calback.apply(Obj, SData);
                                            }, Obj);
                                        }else
                                            Common.Preload(Web_Data, function(){
                                                Ajax.AttachJs(JS_Attach, Requester.Url);
                                                if(typeof(Calback) === 'function')
                                                    Calback.apply(Obj, SData);
                                            }, Obj);
                                }
                            });
                            break;

                        case "FUNCTION":
                            Web_Data = Web_Data.split(/\1,\1/);
                            if(typeof(Requester.Target) === 'function')
                                Requester.Target.apply(null, Web_Data);
                            if(typeof(Calback) === 'function')
                                Calback.apply(null, SData);
                            break;

                        case "EXECUTE":
                            try{eval(Web_Data);}catch(e){Debug.Error(e, 'Ajax.Call.Execute', Requester.Url);}
                            if(typeof(Calback) === 'function')
                                Calback(SData);
                            break;
                    }

                    //Execute Attached Javascript
                    JS_Attach = JS_Attach || '';
                    if(JS_Attach !== '')
                        Ajax.AttachJs(JS_Attach, Requester.Url);
                break;

                default:
                    Ajax.Return(C.Ajax.Obj.status, Requester.Url);
                break;
                }

                C.Ajax.Load = false;
                L.Ajax = Var.Remove(L.Ajax, Task_ID);
                Ajax.Request();
            break;
            }
        },

        AttachJs : function(JSAttach, Url){
            try{eval(JSAttach);}catch(e){Debug.Error(e, 'Ajax.Attach.Execute', Url);}
        },

        Only : function(Url, Data, Config)
        {
            if(Var.Default(Config, false, 'Validate') === true){
                if(Validate.Check() === false)
                    return false;
                if(!Var.Default(Config, false, 'KeepValidate'))
                    Validate.Clear();
            }
            C.Ajax.Task++;
            L.Ajax[C.Ajax.Task] = {
                Method : Var.Default(Config, "POST", 'Method'),
                Flow : "ONLY",
                Url : Url,
                Data : Common.HttpData(Data),
                Callback : Var.Default(Config, false, 'Callback'),
                Done : false
            };
            Ajax.Request();
        },

        Execute : function(Url, Data, Config)
        {
            if(Var.Default(Config, false, 'Validate') === true){
                if(Validate.Check() === false)
                    return false;
                if(!Var.Default(Config, false, 'KeepValidate'))
                    Validate.Clear();
            }
            C.Ajax.Task++;
            L.Ajax[C.Ajax.Task] = {
                Method : Var.Default(Config, "POST", 'Method'),
                Flow : "EXECUTE",
                Url : Url,
                Data : Common.HttpData(Data),
                Callback : Var.Default(Config, false, 'Callback'),
                Done : false
            };
            Ajax.Request();
        },

        Object : function(Url, Data, Config)
        {
            if(Var.Default(Config, false, 'Validate') === true){
                if(Validate.Check() === false)
                    return false;
                if(!Var.Default(Config, false, 'KeepValidate'))
                    Validate.Clear();
            }
            C.Ajax.Task++;
            L.Ajax[C.Ajax.Task] = {
                Method : Var.Default(Config, "POST", 'Method'),
                Flow : "OBJECT",
                Url : Url,
                Data : Common.HttpData(Data),
                Target : Var.Default(Config, C.DObj, 'Target'),
                LoadImg : Var.Default(Config, '', 'LoadImg'),
                ShwLoad : Var.Default(Config, S.Ajax.ShwLoad, 'ShwLoad'),
                Effect : Var.Default(Config, S.EffectNow, 'Effect'),
                Callback : Var.Default(Config, false, 'Callback'),
                Done : false
            };
            Ajax.Request();
        },

        Fancy : function(Url, Data, Config)
        {
            if(Var.Default(Config, false, 'Validate') === true){
                if(Validate.Check() === false)
                    return false;
                if(!Var.Default(Config, false, 'KeepValidate'))
                    Validate.Clear();
            }
            C.Ajax.Task++;
            L.Ajax[C.Ajax.Task] = {
                Method : Var.Default(Config, "POST", 'Method'),
                Flow : "FANCY",
                Url : Url,
                Data : Common.HttpData(Data),
                Size :
                    {
                        Width : Var.Default(Config, 0, 'Width'),
                        Height : Var.Default(Config, 0, 'Height')
                    },
                ShwLoad : Var.Default(Config, S.Ajax.ShwLoad, 'ShwLoad'),
                Fancy : Var.Default(Config, {}, 'Fancy'),
                Effect : Var.Default(Config, S.EffectNow, 'Effect'),
                Callback : Var.Default(Config, false, 'Callback'),
                Done : false
            };
            Ajax.Request();
        },

        Function : function(Url, Data, Config)
        {
            if(typeof(Function) !== 'function')
                return false;
            if(Var.Default(Config, false, 'Validate') === true){
                if(Validate.Check() === false)
                    return false;
                if(!Var.Default(Config, false, 'KeepValidate'))
                    Validate.Clear();
            }
            C.Ajax.Task++;
            L.Ajax[C.Ajax.Task] = {
                Method : Var.Default(Config, "POST", 'Method'),
                Flow : "FUNCTION",
                Url : Url,
                Data : Common.HttpData(Data),
                Target : Var.Default(Config, null, 'Target'),
                Callback : Var.Default(Config, false, 'Callback'),
                Done : false
            };
            Ajax.Request();
        }
    },

    //------------------------------------------------------------------------
    // JavaScript Intergrade HTML Element Fancy Box
    //------------------------------------------------------------------------
	Fancy = {

        Init : function(){
            C.Fancy.DSysLine = Common.GenCode(10);
            C.Fancy.DMsgLine = Common.GenCode(10);
            C.Ajax.Main = Document.Create('div', {css:'position:absolute !important; left:0px !important; top:0px !important; width:0px !important; height:0px !important; overflow:visible !important; margin:0% !important; padding:0px !important;'});
        },

        Now : function(){
            return C.Fancy.BoxNow;
        },

        Apply : function(iFancy, Config){
            if(typeof(Config) !== 'object')
                Config = {};
            L.Fancy[iFancy].Width = Var.Float(Var.Default(Config, 0, 'Width'));
            L.Fancy[iFancy].Height = Var.Float(Var.Default(Config, 0, 'Height'));
            L.Fancy[iFancy].Method = Var.Default(Config, 'popup', 'Method');
            L.Fancy[iFancy].Modal = Var.Default(Config, false, 'Modal');
            L.Fancy[iFancy].MaxHOffset = Var.Default(Config, S.Fancy.MaxOffset, 'MaxHOffset');
            L.Fancy[iFancy].MaxVOffset = Var.Default(Config, S.Fancy.MaxOffset, 'MaxVOffset');
            L.Fancy[iFancy].Channel = Var.Default(Config, C.Fancy.DSysLine, 'Channel');
            L.Fancy[iFancy].Channel = (L.Fancy[iFancy].Channel === '*') ? C.Fancy.DSysLine : L.Fancy[iFancy].Channel;
            L.Fancy[iFancy].Effect = Var.Default(Config, Common.Random(0, 2), 'Effect');
            L.Fancy[iFancy].Callback = Var.Default(Config, null, 'Callback');
            L.Fancy[iFancy].Parent = Var.Default(Config, C.Ajax.Main, 'Parent');
            if(typeof(L.Fancy[iFancy].Parent) === 'string')
                L.Fancy[iFancy].Parent = _$(L.Fancy[iFancy].Parent).First();
            if(Config.hasOwnProperty('Overlay'))
                L.Fancy[iFancy]['Overlay'] = Var.Append(L.Fancy[iFancy]['Overlay'], Config['Overlay'], 1);
            if(Config.hasOwnProperty('Box'))
                L.Fancy[iFancy]['Box'] = Var.Append(L.Fancy[iFancy]['Box'], Config['Box'], 1);
            if(Config.hasOwnProperty('CloseBtn'))
                L.Fancy[iFancy]['CloseBtn'] = Var.Append(L.Fancy[iFancy]['CloseBtn'], Config['CloseBtn'], 1);
            if(Config.hasOwnProperty('Container'))
                L.Fancy[iFancy]['Container'] = Var.Append(L.Fancy[iFancy]['Container'], Config['Container'], 1);
        },

        Next : function(Config){
            C.Fancy.BoxNow = Common.GenCode(10);
            var iFancy = C.Fancy.BoxNow;
            L.Fancy[iFancy] = {
                Width:0, Height:0, Method:'popup', Modal : false,
                Channel:C.Fancy.DSysLine, Callback:null, Parent:C.Ajax.Main,
                MLeft:0, MTop:0, MHeight:0, MWidth:0, MaxHOffset:0, MaxVOffset:0, Effect:0,
                Overlay : {Obj:null, Show:true, Color:'#fff', Opaque:70},
                Box : {Obj:null, Frame:true, Left:0, Top:0, Width:0, Height:0, FixLeft:null, FixTop:null, OffsetLeft:0, OffsetTop:0, Overflow:'visible'},
                CloseBtn:{Obj:null, Show:true, Image:'', Left:0, CLeft:0, Top:0, CTop:0, Width:0, Height:0, FixLeft:null, FixTop:null, OffsetLeft:0, OffsetTop:0, Align : {H:'R',V:'T'}},
                Container : {Obj:null, Left:0, Top:0, Width:0, Height:0}
            };
            Fancy.Apply(iFancy, Config);
            L.Fancy[iFancy].Method = L.Fancy[iFancy].Method.toLowerCase();
            var FancyNow = L.Fancy[iFancy], Parent = FancyNow.Parent;
            switch(L.Fancy[iFancy].Method){
                case 'popup':
                    var Position = 'fixed';
                    break;

                case 'static':
                    var Position = 'static';
                    break;

                case "abox":
                case "afbox":
                    Parent = Common.Body();
                    var Position = 'absolute';
                break;

                case "ibox":
                case "ifbox":
                    if(_$(Parent).Style('position') !== 'relative'){
                        CList = Parent.childNodes;
                        for(var i=0; i< CList.length; i++)
                            if(_$(CList[i]).Style('position') === 'relative'){
                                Parent = CList[i];
                                break;
                            }
                    }
                    var Position = 'absolute';
                break;

                default:
                    var Position = 'absolute';
                    break;
            }
            FancyNow.Overlay.Obj = _$(Parent).Create('div', {html:'&nbsp;', style:{visibility:'hidden', position:Position, border:'none', margin:'0px', padding:'0px', zIndex:9999}});
            FancyNow.Box.Obj = _$(Parent).Create('div', {style:{visibility:'hidden', overflow:'visible', position:Position, border:'none', margin:'0px', padding:'0px', zIndex:10000}});
            FancyNow.Container.Obj = _$(FancyNow.Box.Obj).Create('div', {style:{position:'absolute', border:'none', margin:'0px', padding:'0px', width:'auto', height:'auto'}});
            FancyNow.CloseBtn.Obj = _$(FancyNow.Box.Obj).Create('div', {style:{position:'absolute', border:'none', margin:'0px', padding:'0px', cursor:'pointer'}});

            _$(FancyNow.CloseBtn.Obj).Click(function(){Fancy.iClose(iFancy);});
            Fancy.CalRect(iFancy);
            return iFancy;
        },

        Clear : function(iFancy){
            if(typeof(iFancy) !== 'undefined' && L.Fancy.hasOwnProperty(iFancy)){
                _$(L.Fancy[iFancy].Overlay.Obj).Remove();
                _$(L.Fancy[iFancy].Box.Obj).Remove();
                L.Fancy = Var.Remove(L.Fancy, iFancy);
            }
         },

    	iClose : function(iFancy){
            Fancy.Overlay.Hide(iFancy);
            Fancy.CloseBtn.Hide(iFancy);
            Fancy.Box.Hide(iFancy);
        },

    	Opening : function(Channel){
            if(typeof(Channel) !== 'string' || Channel === '')
                Channel = C.Fancy.DSysLine;
            for(var i in L.Fancy)
               if(L.Fancy[i].Channel === Channel || Channel === '*')
                    return true;
        },

    	iFancy : function(Channel){
            if(typeof(Channel) !== 'string' || Channel === '')
                Channel = C.Fancy.DSysLine;
            for(var i in L.Fancy)
               if(L.Fancy[i].Channel === Channel)
                return i;
        },

    	Close : function(Channel, Exception, NoModal){
            Channel = Var.Default(Channel, C.Fancy.DSysLine);
            if(Channel === '')Channel = C.Fancy.DSysLine;
            NoModal = Var.Default(NoModal, false);
            for(var i in L.Fancy){
                if((L.Fancy[i].Channel === Channel || (Channel === '*' && L.Fancy[i].Modal === false)) && i !== Exception && (NoModal !== true || (NoModal === true && L.Fancy[i].Modal === false))){
                    Fancy.Overlay.Hide(i);
                    Fancy.CloseBtn.Hide(i);
                    Fancy.Box.Hide(i);
                }
            }
        },

        CalRect : function(iFancy){
            PObj = _$(L.Fancy[iFancy].Parent), ScrollSize = Common.Scroll.Size();
            var PRect = PObj.GetAPos();
            switch(L.Fancy[iFancy].Method){

            case 'popup':
        		L.Fancy[iFancy].MWidth = Common.View.Width();
                L.Fancy[iFancy].MHeight = Common.View.Height();
                break;

            case 'abox':
            case 'afbox':
                L.Fancy[iFancy].MLeft = PRect.left;
                L.Fancy[iFancy].MTop = PRect.top;
        		L.Fancy[iFancy].MWidth = PRect.width;
                L.Fancy[iFancy].MHeight = PRect.height;
                break;

            case 'ibox':
            case 'ifbox':
                L.Fancy[iFancy].MLeft = 0;
                L.Fancy[iFancy].MTop = 0;
        		L.Fancy[iFancy].MWidth = PRect.width;
                L.Fancy[iFancy].MHeight = PRect.height;
                break;
            }
            if(PObj.HasScrollY())
                L.Fancy[iFancy].MWidth -= ScrollSize;
            if(PObj.HasScrollX())
                L.Fancy[iFancy].MHeight -= ScrollSize;
        },

        Overlay : {
            Show : function(iFancy){
                var Overlay = L.Fancy[iFancy].Overlay; Obj = _$(Overlay.Obj);
                Fancy.Overlay.Reposition(iFancy);
                if(Overlay.Show){
                    switch(L.Fancy[iFancy].Method){
                    case 'popup':
                    case 'ifbox':
                    case 'afbox':
                        if(S.Effect)Obj.Opacity(0); else Obj.Opacity(Overlay.Opaque);
                        Obj.Style({backgroundColor: Overlay.Color});
                        Obj.Fade({Target:Overlay.Opaque, Speed:5, Rate:5});
                        break;

                    default:
                        Obj.Style({display:'none'});
                    }
                    Obj.Style({visibility:'visible'});
                }else
                    Obj.Style({display:'none'});
            },

            Hide : function(iFancy){
                var Obj = _$(L.Fancy[iFancy].Overlay.Obj);
                if(S.Effect)
                    Obj.Fade({Target:0, Speed:5, Rate:5, Callback:function(){
                        _$(this).Style({display:'none', left:'auto', top:'auto', width:'auto', height:'auto'});
                    }});
                else
                    Obj.Style({display:'none', left:'auto', top:'auto', width:'auto', height:'auto'});
            },

            Reposition : function(iFancy){
                var Overlay = L.Fancy[iFancy].Overlay;
                _$(L.Fancy[iFancy].Overlay.Obj).Style({
                    left : L.Fancy[iFancy].MLeft + 'px',
                    top : L.Fancy[iFancy].MTop + 'px',
                    width : L.Fancy[iFancy].MWidth+'px',
                    height : L.Fancy[iFancy].MHeight+'px'
                });
            }
        },

        CloseBtn : {

            Show : function(iFancy, Callback){

                var CloseBtn = L.Fancy[iFancy].CloseBtn,
                ShowBtn = function(){
                        Fancy.CloseBtn.Reposition(iFancy, true);
                        _$(L.Fancy[iFancy].CloseBtn.Obj).Style({
                            left : ((CloseBtn.Left < 0) ? 0 : CloseBtn.Left)+'px',
                            top : ((CloseBtn.Top < 0) ? 0 : CloseBtn.Top)+'px',
                            width : CloseBtn.Width+'px',
                            height : CloseBtn.Height+'px'
                        });
                    if(typeof(Callback) === 'function')
                        Callback();
                };
                if(CloseBtn.Show === true && L.Fancy[iFancy].Modal === false){
                    if(typeof(CloseBtn.Image) === 'function'){
                        CloseBtn.Image(CloseBtn.Obj);
                        ShowBtn();
                    }else if(typeof(CloseBtn.Image) === 'string'){
                        CloseBtn.Image = ((CloseBtn.Image === '') ? Common.ImageUrl( S.Fancy.CBtnImg, 'system') : Common.ImageUrl(CloseBtn.Image));
                        var ImgObj = _$(L.Fancy[iFancy].CloseBtn.Obj).Create('img', {style:{border:'none'}});
                        ImgObj.onload = ShowBtn;
                        ImgObj.onerror = function(){
                            _$(ImgObj).Remove();
                            ShowBtn();
                        };
                        _$(ImgObj).Src(CloseBtn.Image);
                    }else
                        if(typeof(Callback) === 'function')
                            Callback();
                }else
                    if(typeof(Callback) === 'function')
                        Callback();
            },

            Hide : function(iFancy) {
                _$(L.Fancy[iFancy].CloseBtn.Obj).Style({display:'none'});
            },

            Reposition : function(iFancy, CalcOnly){
                var CloseBtn = L.Fancy[iFancy].CloseBtn, FancyBox = L.Fancy[iFancy].Box.Obj, Container = L.Fancy[iFancy].Container.Obj,
                CWidth = _$(Container).OuterWidth(), CHeight = _$(Container).OuterWidth(), Obj = _$(CloseBtn.Obj);
                if(_$(Obj).Effecting())
                    return false;
                CloseBtn.Width = Obj.OuterWidth();
                CloseBtn.Height = Obj.OuterHeight();
                switch(CloseBtn.Align.H){
                    case "L":CloseBtn.Left = -(CloseBtn.Width / 2);break;
                    case "R":CloseBtn.Left = CWidth - (CloseBtn.Width / 2);break;
                }
                switch(CloseBtn.Align.V){
                    case "T":CloseBtn.Top = -(CloseBtn.Height / 2);break;
                    case "B":CloseBtn.Top = CHeight - (CloseBtn.Height / 2);break;
                }
                if(typeof(CloseBtn.FixLeft) === 'number')
                    CloseBtn.Left = CloseBtn.FixLeft;
                else
                    CloseBtn.Left += CloseBtn.OffsetLeft;
                if(typeof(CloseBtn.FixTop) === 'number')
                    CloseBtn = CloseBtn.FixTop;
                else
                    CloseBtn.Top += CloseBtn.OffsetTop;

                CloseBtn.Right = CloseBtn.Left + CloseBtn.Width;
                CloseBtn.Bottom = CloseBtn.Top + CloseBtn.Height;
                L.Fancy[iFancy].CloseBtn = CloseBtn;
                if(CalcOnly !== true)
                    Job.Work(function(){Obj.Move({Left: ((CloseBtn.Left < 0) ? 0 : CloseBtn.Left), Top : ((CloseBtn.Top < 0) ? 0 : CloseBtn.Top)});}, 1, 20);
            }
        },

        Box : {

            Left : function(iFancy){
                return L.Fancy[iFancy].Box.Left;
            },

            Top : function(iFancy){
                return L.Fancy[iFancy].Box.Top;
            },

            Width : function(iFancy){
                return L.Fancy[iFancy].Box.Width;
            },

            Height : function(iFancy){
                return L.Fancy[iFancy].Box.Height;
            },

            Frame : function(iFancy){
                var Container = _$(L.Fancy[iFancy].Container.Obj);
                if(L.Fancy[iFancy].Box.Frame === true){
                    Container.ClassName('box');
                }else{
                    Container.Style({
                        padding:'0px',
                        backgroundColor:'transparent',
                        border:'none'
                    });
                }
            },

            Open : function(iFancy, Content, Callback){
                if(typeof(Content) !== 'string' || Content.length === 0){
                    Fancy.Close(L.Fancy[iFancy].Channel);
                    return;
                }
                Common.Preload(Content, function(){
                    Fancy.Box.Frame(iFancy);
                    Fancy.CloseBtn.Show(iFancy, Callback);
                }, L.Fancy[iFancy].Container.Obj);
            },

            Calcurate : function(iFancy){
                var FBox = L.Fancy[iFancy].Box,
                Box_Width = L.Fancy[iFancy].Width,
                Box_Height = L.Fancy[iFancy].Height,
                FContainer = L.Fancy[iFancy].Container,
                Container = _$(L.Fancy[iFancy].Container.Obj),
                ScrollSize = Common.Scroll.Size();

               //Setup Width
                if(Box_Width === 0){
                    Box_Width = Container.OuterWidth();
                    if(!Var.IsNumber(Box_Width) || Box_Width === 0)
                        FBox.Width = FBox.CWidth = S.Fancy.DWidth;
                    else{
                        FBox.Width = Box_Width;
                        FBox.CWidth = 'auto';
                    }
                }else
                    FBox.Width = FBox.CWidth = Box_Width;

                //Setup Height
                if(Box_Height === 0){
                    Box_Height = Container.OuterHeight();
                    if(!Var.IsNumber(Box_Height) || Box_Height === 0)
                        FBox.Height = FBox.CWidth = S.Fancy.DHeight;
                    else{
                        FBox.Height = Box_Height;
                        FBox.CHeight = 'auto';
                    }
                }else
                    FBox.Height = FBox.CHeight = Box_Height;

                var FCloseBtn = L.Fancy[iFancy].CloseBtn;
                if(FCloseBtn.Show === true){
                    if(FCloseBtn.Right > FBox.Width)
                        FBox.Width = FCloseBtn.Right;

                    if(FCloseBtn.Left < 0){
                        FBox.Width += -1 * FCloseBtn.Left;
                        FContainer.Left = -1 * FCloseBtn.Left;
                    }

                    if(FCloseBtn.Bottom > FBox.Height)
                        FBox.Height = FCloseBtn.Bottom;

                    if(FCloseBtn.Top < 0){
                        FBox.Height += -1 * FCloseBtn.Top;
                        FContainer.Top = -1 * FCloseBtn.Top;
                    }
                }

                //Check if out of overlay size, resize to overlay size & Include the scrollbar width and height
                if(L.Fancy[iFancy].Method === 'popup'){
                    if(FBox.Width > L.Fancy[iFancy].MWidth - L.Fancy[iFancy].MaxHOffset){
                        FBox.Width = L.Fancy[iFancy].MWidth - L.Fancy[iFancy].MaxHOffset - ScrollSize;
                        FBox.Overflow = 'auto';
                    }else
                        FBox.Overflow = 'visible';
                    if(FBox.Height > L.Fancy[iFancy].MHeight - L.Fancy[iFancy].MaxVOffset){
                        FBox.Height = L.Fancy[iFancy].MHeight - L.Fancy[iFancy].MaxVOffset - ScrollSize;
                        FBox.Overflow = 'auto';
                    }else
                        FBox.Overflow = 'visible';
                }

                //Calcurate FancyBox Position
                FBox.Left = L.Fancy[iFancy].MLeft + ((L.Fancy[iFancy].MWidth - FBox.Width) / 2);
                FBox.Top = L.Fancy[iFancy].MTop + ((L.Fancy[iFancy].MHeight - FBox.Height) / 2);

                if(L.Fancy[iFancy].Method === 'popup' || L.Fancy[iFancy].Method === 'floatbox'){
                    if(FBox.Left + FBox.Width > L.Fancy[iFancy].MWidth - L.Fancy[iFancy].MaxHOffset)
                        FBox.Left = L.Fancy[iFancy].MWidth - FBox.Width - L.Fancy[iFancy].MaxHOffset;
                    if(FBox.Top + FBox.Height > L.Fancy[iFancy].MHeight - L.Fancy[iFancy].MaxVOffset)
                        FBox.Top = L.Fancy[iFancy].MHeight - FBox.Height - L.Fancy[iFancy].MaxVOffset;
                }

                if(typeof(FBox.FixLeft) === 'number')
                    FBox.Left = L.Fancy[iFancy].MLeft + FBox.FixLeft;
                else if(typeof(FBox.OffsetLeft) === 'number')
                    FBox.Left += FBox.OffsetLeft;

                if(typeof(FBox.FixTop) === 'number')
                    FBox.Top = L.Fancy[iFancy].MTop + FBox.FixTop;
                else if(typeof(FBox.OffsetTop) === 'number')
                    FBox.Top += FBox.OffsetTop;
            },

            Reposition : function(iFancy){
                var FBox = L.Fancy[iFancy].Box, FContainer = L.Fancy[iFancy].Container, FCloseBtn = L.Fancy[iFancy].CloseBtn, ScrollSize = Common.Scroll.Size();

                if(_$(FBox.Obj).Effecting())
                    _$(FBox.Obj).ClearEffect();

                FBox.Width = (FBox.CWidth === 'auto') ? _$(FContainer.Obj).OuterWidth() : FBox.CWidth;
                if(FBox.Width === 0 || isNaN(FBox.Width))
                    FBox.Width = S.Fancy.DWidth;

                FBox.Height = (FBox.CHeight === 'auto') ? _$(FContainer.Obj).OuterHeight() : FBox.CHeight;
                if(FBox.Height === 0 || isNaN(FBox.Height))
                    FBox.Height = S.Fancy.DHeight;

                if(FCloseBtn.Right > FBox.Width)
                    FBox.Width = FCloseBtn.Right;

                if(FCloseBtn.Left < 0){
                    FBox.Width += -1 * FCloseBtn.Left;
                    FContainer.Left = -1 * FCloseBtn.Left;
                }

                if(FCloseBtn.Bottom > FBox.Height)
                    FBox.Height = FCloseBtn.Bottom;

                if(FCloseBtn.Top < 0){
                    FBox.Height += -1 * FCloseBtn.Top;
                    FContainer.Top = -1 * FCloseBtn.Top;
                }

                //Check if out of overlay size, resize to overlay size & Include the scrollbar width and height
                FBox.Overflow = 'visible';
                if(L.Fancy[iFancy].Method === 'popup'){
                    if(FBox.Width > L.Fancy[iFancy].MWidth - L.Fancy[iFancy].MaxHOffset){
                        FBox.Width = L.Fancy[iFancy].MWidth - L.Fancy[iFancy].MaxHOffset - ScrollSize;
                        FBox.Overflow = 'auto';
                    }
                    if(FBox.Height > L.Fancy[iFancy].MHeight - L.Fancy[iFancy].MaxVOffset){
                        FBox.Height = L.Fancy[iFancy].MHeight - L.Fancy[iFancy].MaxVOffset - ScrollSize;
                        FBox.Overflow = 'auto';
                    }
                }

                //Calcurate FancyBox Position
                FBox.Left = L.Fancy[iFancy].MLeft + ((L.Fancy[iFancy].MWidth - FBox.Width) / 2);
                FBox.Top = L.Fancy[iFancy].MTop + ((L.Fancy[iFancy].MHeight - FBox.Height) / 2);

                if(L.Fancy[iFancy].Method === 'popup' || L.Fancy[iFancy].Method === 'floatbox'){
                    if(FBox.Left + FBox.Width > L.Fancy[iFancy].MWidth - L.Fancy[iFancy].MaxHOffset)
                        FBox.Left = L.Fancy[iFancy].MWidth - FBox.Width - L.Fancy[iFancy].MaxHOffset;
                    if(FBox.Top + FBox.Height > L.Fancy[iFancy].MHeight - L.Fancy[iFancy].MaxVOffset)
                        FBox.Top = L.Fancy[iFancy].MHeight - FBox.Height - L.Fancy[iFancy].MaxVOffset;
                }

                if(typeof(FBox.FixLeft) === 'number')
                    FBox.Left = L.Fancy[iFancy].MLeft + FBox.FixLeft;
                else if(typeof(FBox.OffsetLeft) === 'number')
                    FBox.Left += FBox.OffsetLeft;

                if(typeof(FBox.FixTop) === 'number')
                    FBox.Top = L.Fancy[iFancy].MTop + FBox.FixTop;
                else if(typeof(FBox.OffsetTop) === 'number')
                    FBox.Top += FBox.OffsetTop;

                _$(FBox.Obj).Style({overflow:FBox.Overflow});
                _$(FBox.Obj).Move({Left:FBox.Left, Top:FBox.Top});
                _$(FBox.Obj).Grow({Width:FBox.Width, Height:FBox.Height});
            },

            View : function(iFancy){
                Fancy.Close(L.Fancy[iFancy].Channel, iFancy);
                var FBox = L.Fancy[iFancy].Box, BObj = _$(FBox.Obj);
                Fancy.Box.Calcurate(iFancy);

                _$(L.Fancy[iFancy].Container.Obj).Style({
                    width : (Var.IsNumber(FBox.CWidth) ? FBox.CWidth + 'px' : FBox.CHeight),
                    height : (Var.IsNumber(FBox.CHeight) ? FBox.CHeight + 'px' : FBox.CHeight)
                });

                BObj.Opacity(100);
                BObj.Style({
                    visibility:'visible',
                    overflow : 'hidden',
                    left : FBox.Left + 'px',
                    top : FBox.Top + 'px',
                    width : FBox.Width + 'px',
                    height : FBox.Height + 'px'
                });
                Behavior.Style();
            },

            Show : function (iFancy){
                Fancy.Close(L.Fancy[iFancy].Channel, iFancy);
                var FBox = L.Fancy[iFancy].Box, FContainer = L.Fancy[iFancy].Container, FObj = _$(FBox.Obj);
                Fancy.Box.Calcurate(iFancy);

                _$(FContainer.Obj).Style({
                    left : FContainer.Left + 'px',
                    top : FContainer.Top + 'px',
                    width : (Var.IsNumber(FBox.CWidth) ? FBox.CWidth + 'px' : FBox.CHeight),
                    height : (Var.IsNumber(FBox.CHeight) ? FBox.CHeight + 'px' : FBox.CHeight)
                });

                FObj.Style({
                    overflow:'hidden',
                    left : FBox.Left + 'px',
                    top : FBox.Top + 'px',
                    width : FBox.Width + 'px',
                    height : FBox.Height + 'px',
                    visibility:'visible'
                });

                Behavior.Style();

                if(S.Effect){

                    FObj.Opacity(0);
                    FObj.Fade({Target:100});

                    L.Fancy[iFancy].Effect = 0;
                    switch(L.Fancy[iFancy].Effect){
                        case 1:
                            var LeftNow = FBox.Left, TopNow = FBox.Top;
                            switch(Common.Random(0,3)){
                                case 0:LeftNow -= 50; TopNow -= 50; break;
                                case 1:LeftNow -= 50; TopNow += 50; break;
                                case 2:LeftNow += 50; TopNow -= 50; break;
                                case 3:LeftNow += 50; TopNow += 50; break;
                            }
                           FObj.Style({left:LeftNow+'px', top:TopNow+'px'});
                           FObj.Move({Left:FBox.Left, Top:FBox.Top, Rate:2}, 'Fancy_Task_Open_Show_' + iFancy);
                        break;

                        case 2:
                            var WType = '';
                            FObj.Style({width : '10px', height : '10px'});
                            FObj.Grow({Type:'bold', Width:FBox.Width, Height:FBox.Height, Rate:10}, 'Fancy_Task_Open_Show_' + iFancy);
                        break;
                    }
                    Task.Register(function(){
                        FObj.Style({overflow:FBox.Overflow});
                        if(typeof(L.Fancy[iFancy].Callback) ==='function')
                            L.Fancy[iFancy].Callback();
                    }, 1, 10, 'Fancy_Task_Open_Show_' + iFancy);
                    Task.Start('Fancy_Task_Open_Show_' + iFancy);
                }else{
                    FObj.Style({overflow:FBox.Overflow});
                    if(typeof(L.Fancy[iFancy].Callback) ==='function')
                        L.Fancy[iFancy].Callback();
                }
            },

            Hide : function(iFancy){
                var FBox = L.Fancy[iFancy].Box, FContainer = L.Fancy[iFancy].Container, FObj = _$(FBox.Obj);
                FObj.Disabled(true);
               if(S.Effect){

                    FObj.ClearEffect('*');
                    switch(L.Fancy[iFancy].Effect){
                        case 1:
                            var LeftNow = FBox.Left, TopNow = FBox.Top;
                            switch(Common.Random(0,3)){
                                case 0:LeftNow -= 100; TopNow -= 100; break;
                                case 1:LeftNow -= 100; TopNow += 100; break;
                                case 2:LeftNow += 100; TopNow -= 100; break;
                                case 3:LeftNow += 100; TopNow += 100; break;
                            }
                            FObj.Move({Left:LeftNow, Top:TopNow, Rate:4});
                        break;

                        case 2:
                            FObj.Grow({Type:'bold', Width:0, Height:0, Rate:15});
                        break;
                    }
                    FObj.Fade({Target:0}, 'Fancy_Task_Close_Fancy_' + iFancy);
                    Task.Register(function(){Fancy.Clear(iFancy);}, 1, 1, 'Fancy_Task_Close_Fancy_' + iFancy);
                    Task.Start('Fancy_Task_Close_Fancy_' + iFancy);
                }else{
                    FObj.Style({display:'none', overflow:'hidden',left:'auto',top:'auto',width:'auto',height:'auto'});
                    Fancy.Clear(iFancy);
                }
            },

        	FixPos : function(iFancy, Left, Top){
                if(_$(L.Fancy[iFancy].Box.Obj).Effecting())
                    return false;
                if(typeof(Left) === 'number')
                    L.Fancy[iFancy].Box.FixLeft = Var.Float(Left);
                if(typeof(Top) === 'number')
                    L.Fancy[iFancy].Box.FixTop = Var.Float(Top);
                if(typeof(Left) === 'number' || typeof(Top) === 'number')
                    Fancy.Box.Reposition(iFancy);
        	},

            OffsetPos : function(iFancy, Left, Top){
                if(_$(L.Fancy[iFancy].Box.Obj).Effecting())
                    return false;
                if(typeof(Left) === 'number')
                    L.Fancy[iFancy].Box.OffsetLeft = Var.Float(Left);
                if(typeof(Top) === 'number')
                    L.Fancy[iFancy].Box.OffsetTop = Var.Float(Top);
                if(typeof(Left) === 'number' || typeof(Top) === 'number')
                    Fancy.Box.Reposition(iFancy);
            },

            IsOverFlow : function(iFancy){
                if(L.Fancy[iFancy].Box.Width >= L.Fancy[iFancy].MWidth - C.Fancy.MaxOffset || L.Fancy[iFancy].Box.Height >= L.Fancy[iFancy].MHeight - C.Fancy.MaxOffset)
                    return L.Fancy[iFancy].Box.Overflow = true;
                else
                    return false;
            }
        },

    	BoxFixPos : function(Left, Top, Channel){
            var iFancy = Fancy.iFancy(Channel);
            if(L.Fancy.hasOwnProperty(iFancy))
                Fancy.Box.FixPos(iFancy, Left, Top);
    	},

        BoxOffPos : function(Left, Top, Channel){
            var iFancy = Fancy.iFancy(Channel);
            if(L.Fancy.hasOwnProperty(iFancy))
                Fancy.Box.OffsetPos(iFancy, Left, Top);
        },

        Loading : function(LoadImg, Config)
    	{
            if(typeof(Config) !== 'object' || Config === null)Config = {};
            Config.Effect = 0;
            Config.Width = 64;
            Config.Height = 64;
            var iFancy = Fancy.Next(Config), FancyBox = L.Fancy[iFancy].Box.Obj,
            Container = L.Fancy[iFancy].Container.Obj, Overlay = L.Fancy[iFancy].Overlay.Obj,
            Lock_Handel = function(){return false;};
            Overlay.oncontextmenu = FancyBox.onclick = FancyBox.oncontextmenu = Lock_Handel;
            var Content = "<div style=\"display:block; width:100%; height:100%; background: url('" + LoadImg +
                "') no-repeat !important; background-position:center center !important; margin:0px !important; padding:0px !important; border:none !important;\">&nbsp;</div>";
            Fancy.Overlay.Show(iFancy);
            _$(Container).Html(Content);
            Fancy.Box.View(iFancy);
        },

    	Show : function(WebData, Config)
    	{
            if(typeof(Config) !== 'object' || Config === null)Config = {};
            var iFancy = Fancy.Next(Config), FancyBox = L.Fancy[iFancy].Box.Obj, Overlay = L.Fancy[iFancy].Overlay.Obj;
            FancyBox.oncontextmenu = function(){return true;};
            if(L.Fancy[iFancy].Modal === true)
                Overlay.onclick = function(e){_$(FancyBox).ShakeObj(); Event.PreventDefault(e); return false;}
            else
                Overlay.onclick = Overlay.oncontextmenu = function(){Fancy.iClose(iFancy);};
            Fancy.Overlay.Show(iFancy);
            Fancy.Box.Open(iFancy, WebData, function(){Fancy.Box.Show(iFancy);});
    	},

    	MsgBox : function(Msg, Title, Config)
    	{
            if(typeof(Config) !== 'object' || Config === null)Config = {};
            Config.Channel = Var.Default(Config, C.Fancy.DMsgLine, 'Channel');
            Config.Button = Var.Default(Config, null, 'Button');
            Config.CloseBtn = {Show: false};
            Config.Width = Var.Float(Var.Default(Config, S.Fancy.DWidth, 'Width'));
            if(Config.Width > Common.View.Width())
                Config.Width = Common.View.Width();
            var iFancy = Fancy.Next(Config), FancyBox = L.Fancy[iFancy].Box.Obj, Overlay = L.Fancy[iFancy].Overlay.Obj;
            Config.Width -= 2; Config.Height -= 2;
            var Content = '<div class="box" style="display:block; padding:10px; border:1px solid #ccc; width:' + (Config.Width- 20)  + 'px;">' +
            (typeof(Title) === 'string' && Title !== ''
             ? '<div class="box_title" style="padding:10px; overflow:hidden; font-size:20px; border-bottom:1px solid; width:' + (Config.Width - 40)  + 'px;">' + Title + '</div>' :'') +
            '<div style="padding:10px; width:' + (Config.Width - 40)  + 'px; height:' + (Config.Height - 130)  + 'px;">' +
            '<img src="' + Common.ImageUrl(S.Fancy.Symbol, 'system') + '" style="border:none; padding-right:5%; width:30%;" align="left" />' +
            Msg + '<div style="float:left; width:100%; height:auto; text-align:center;">';
            if(typeof(Config.Button) === 'object' && Config.Button !== null)
                for(var Key in Config.Button)
                    Content += '<input type="button" value="' + Key + '" class="btn" href="' + Config.Button[Key] + '##fancymsg:close">\n';
            Content += '<input type="button" value="' + (Var.Len(Config.Button) === 0 ? I.FancyMsgOk : I.FancyMsgCancel) + '" class="btnlow" href="fancymsg:close">\n</div></div><div style="clear:both;"></div>';
            Overlay.onclick = function(e){_$(FancyBox).ShakeObj(); Event.PreventDefault(e); return false;}
            Fancy.Overlay.Show(iFancy);
            Fancy.Box.Open(iFancy, Content, function(){
                Fancy.Box.Show(iFancy);
                if(typeof(L.Fancy[iFancy].Callback) ==='function')
                    L.Fancy[iFancy].Callback();
            });
    	},

    	AskBox : function(Msg, Button, Title, Config)
    	{
            if(typeof(Config) !== 'object' || Config === null)Config = {};
                Config.Button = Var.Default(Config, Button, 'Button');
            Fancy.MsgBox(Msg, Title, Config);
    	},

    	Reposition : function(){
            for(var i in L.Fancy){
                Fancy.CalRect(i);
                Fancy.Overlay.Reposition(i);
                Fancy.CloseBtn.Reposition(i);
                Fancy.Box.Reposition(i);
            }
    	}
    },

    //------------------------------------------------------------------------
    // JavaScript Input Validate Function
    //------------------------------------------------------------------------
    Validate = {

        Clear : function(){
            L.Validate = [];
        },

        Check : function(){
            var ValidCheck = true, FailedObj;
            if(S.Validate === false || Var.Len(L.Validate) === 0)
                return true
            else{
                for(var Key in L.Validate){
                    Behavior.Place.Revert(L.Validate[Key].Target);
                    var ValidPass = true, Validator = L.Validate[Key], Obj = Validator.Target, Value = _$(Obj).Value();
                    if(!Document.Contain(Validator.Target)){
                        L.Validate = Var.Remove(L.Validate, Key);
                        continue;
                    }
                    if(typeof(Validator.Method) === "string"){
                        switch(Validator.Method){

                            case "reg":
                            case "regexp":
                                if(Validator.Extend instanceof RegExp)
                                    ValidPass = Validator.Extend.test(Value);
                            break;

                            case "email":
                                ValidPass = Validate.IsEmail(Value);
                            break;

                            case "int":
                            case "interger":
                                ValidPass = Validate.IsInt(Value);
                                break;

                            case "ip":
                            case "ipv4":
                            case "ipaddress":
                                ValidPass = Validate.IsIPV4(Value);
                                break;

                            case "num":
                            case "number":
                            case "numeric":
                                if(isNaN(Value) === true || isFinite(Value) === false || Value === '')
                                    ValidPass = false;
                                break;

                            case "tel":
                            case "telnum":
                            case "telephone":
                                var Teltest = /^[(]{0,1}[0-9]{3}[)]{0,1}[-\s\.]{0,1}[0-9]{3}[-\s\.]{0,1}[0-9]{4,5}$/;
                                ValidPass = Teltest.test(Value);
                                break;

                            case "must":
                                switch(typeof(Value)){
                                    case 'undefined':ValidPass = false;break;
                                    case 'object':
                                        if((Value === null || Var.Len(Value) === 0))
                                            ValidPass = false;
                                    break;
                                    case 'string':
                                        Value = Value.replace(/\s/g,"");
                                        if(Value.length <= 0)
                                            ValidPass = false;
                                    break;
                                    default:
                                        ValidPass = true;
                                }
                                break;

                            case "checked":
                                ValidPass = _$(Obj).Check();
                            break;

                            case "radio":
                                var CheckCount = 0;
                                _$('['+Obj.name+']').Do(function(){
                                    if(this.checked === true)
                                        CheckCount++;
                                });
                                ValidPass = (CheckCount > 0 ? true : false);
                            break;
                        }
                    }else if(typeof(Validator.Method) === "function"){
                        ValidPass = Validator.Method(Value);
                        ValidPass = ((ValidPass === false) ? false : true);
                    }
                    if(typeof(Value) === 'string'){
                        if(Value.length < Validator.Min)
                            ValidPass = false;
                        if(Value.length > Validator.Max && Validator.Max > 0)
                            ValidPass = false;
                    }
                    if(ValidPass === false && !FailedObj){
                        FailedObj = Obj;
                        ValidCheck = false;
                    }
                    Behavior.Place.Apply(Obj);
                    Validate.Action(Validator.Feedback, ValidPass);
                    if(S.ValidateAll === false && ValidCheck === false){
                        if(Validator.Method !== 'radio' && FailedObj)
                            FailedObj.focus();
                        Fancy.Reposition();
                        return ValidCheck;
                    }
                }
                if(FailedObj)
                    FailedObj.focus();
                Fancy.Reposition();
                return ValidCheck;
            }
        },

        Action : function(Feedback, Valid_Pass){
            if(typeof(Feedback) === 'object'){
                for(var Target in Feedback){
                    switch(Target){
                        case "function":
                            if(typeof(Feedback[Target]) === 'function')
                                Feedback[Target](Valid_Pass);
                            break;

                        case "js":
                            if(Valid_Pass === false)
                                try {eval(Feedback[Target]);} catch (e){Debug.Error(e,'Validate.Action.js');}
                            break;

                        default:
                            if(Valid_Pass === false) _$(Target).Html(Feedback[Target]); else _$(Target).Html('&nbsp;');
                            break;
                    }
                }
            }
        },

        IsInt : function(IntStr){
            var NumExp = /^[0-9]+$/;
            if(IntStr.match(NumExp)) {
                return true;
            } else {
                return false;
            }
        },

        IsIPV4 : function(IPStr){
            var IPExp = /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/;
            if (IPExp.test(IPStr)) {
                var IPParts = IPStr.split(".");
                for (var i=0; i < IPParts.length; i++) {
                    if(Validate_IsInt(IPParts[i]) === false){
                        return false;
                    }else if (Var.Int(Var.Float(IPParts[i])) > 255){
                        return false;
                    }js
                }
                return true;
            } else {
                return false;
            }
        },

        IsEmail : function(EmailStr) {
            var EmailExp = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            return EmailExp.test(EmailStr);
        }
    },

    //------------------------------------------------------------------------
    //JavaScript Potoro type script handle.
    //------------------------------------------------------------------------
    Behavior = {

        Style : function(){
            _$('[dragthis],[dragto]').Style({cursor:"move"});
            _$('[href]').Style({cursor:"pointer"});
            _$('[href],[dragthis],[drophere],[dragto]').Selectable(false);
            Behavior.Place.Apply();
        },

        Command: function(CmdStr){
            var Executed = false, SCharReg = {All : /##/, Infor : /::/},  SChar = {All : '##', Infor : '::'};
            if(!Var.IsEmpty(CmdStr) && Var.IsString(CmdStr)){
                var All = CmdStr.split(SCharReg['All']);
                for(var Key in All){
                    var Cmd = All[Key], Protocol = '', Detail = [], Params = [], Handle = '';

                    if(Cmd.indexOf(":") === -1)
                        var Protocol = Cmd;
                    else{
                        var Protocol = Cmd.substring(0, Cmd.indexOf(":")),
                        Detail = Cmd.substring(Cmd.indexOf(":") + 1, Cmd.length),
                        Params = Detail.split(SCharReg['Infor']),
                        Handle = Params[0];
                        Params.shift();
                        for(var i in Params){
                            Params[i] = _$.Common.UrlDecode(Params[i]);
                            if(/^(\{|\[).*(\}|\])/.test(Params[i]) || /^function(\s*)\((.*)\)(\s*)\{(.*)\}$/.test(Params[i]) || /^(\-?)([0-9\.])$/.test(Params[i]) ||
                            Params[i] === 'null' || Params[i] === 'undefined' ||
                            Params[i] === 'true'|| Params[i] === 'false')
                                try{Params[i] = eval('(' + Params[i] + ')');}catch(e){}
                        }
                    }
                    if(Protocol === 'undefined' || Protocol === 'null' || Protocol === undefined || Protocol === null)
                        continue;
                    if(L.Protocol.hasOwnProperty(Protocol)){
                        if(typeof(L.Protocol[Protocol]) === 'function'){
                            Executed = true;
                            if(typeof(Detail) === 'string')
                                L.Protocol[Protocol].apply(this, Detail.split(SCharReg['Infor']));
                            else
                                L.Protocol[Protocol].apply(this);
                        }
                    }else if(window.hasOwnProperty(Protocol) && window[Protocol].hasOwnProperty(Handle) && typeof(window[Protocol][Handle]) === 'function'){
                        Executed = true;
                        window[Protocol][Handle].apply(this, Params);
                    }else{
                        switch(Protocol.toLowerCase()){

                            case "direct":
                                if(typeof(Detail) === 'string'){
                                    Executed = true;
                                    window.location.href = Detail;
                                }
                            break;

                            case "http":
                            case "https":
                                if(typeof(this) === 'object' && this.tagName !== "A"){
                                    Executed = true;
                                    window.open(CmdKeyWord, _$(this).Attr("target"));
                                }
                            break;

                            case "mail":
                            case "mailto":
                                Executed = true;
                                Window.Open("mailto:" + Data, 0, 0,"email");
                                break;

                            case "window":
                            case "openwin":
                                Executed = true;
                                Window.Open.apply(this, Params);
                                break;

                            case "validate":
                                Executed = true;
                                switch(Handle){
                                    case "register":Validate.Register(Data.Unserialize(Params[0]));break;
                                    case "clear":Validate.Clear();break;
                                    case "check":Validate.Check();break;
                                }
                                break;

                            case "fancy":
                                Executed = true;
                                if(Handle.toLowerCase() === 'close')
                                    Fancy.Close.apply(this, Params);
                                else
                                    Fancy.Show.apply(this, Params);
                                break;

                            case "fancymsg":
                                Executed = true;
                                if(Handle.toLowerCase() === 'close')
                                    Fancy.Close.apply(this, [C.Fancy.DMsgLine]);
                                else
                                    Fancy.MsgBox.apply(this, Params);
                                break;

                            case "fancyask":
                                Executed = true;
                                if(Handle.toLowerCase() === 'close')
                                    Fancy.Close.apply(this, [C.Fancy.DMsgLine]);
                                else
                                    Fancy.AskBox.apply(this, Params);
                                break;

                            case "ajax":
                                Executed = true;
                                switch(Handle.toLowerCase()){
                                    case "only":Ajax.Only.apply(this, Params);break;
                                    case "execute":Ajax.Execute.apply(this, Params);break;
                                    case "function":Ajax.Function.apply(this, Params);break;
                                    case "object":Ajax.Object.apply(this, Params);break;
                                    case "fancy":Ajax.Fancy.apply(this, Params);break;
                                }
                                break;

                            case "menu":
                                Executed = true;
                                switch(Handle.toLowerCase()){
                                    case "open":Menu.MOpen.apply(this, Params);break;
                                    case "close":Menu.MClose.apply(this, Params);break;
                                    case "destroy":Menu.MDestroy.apply(this, Params);break;
                                }
                                break;

                            case "effect":
                                Executed = true;
                                    switch(Handle.toLowerCase()){
                                        case "fade": $(this).Fade.apply(this, Params);break;
                                        case "blinkcolor":$(this).BlinkColor.apply(this, Params);break;
                                        case "blinkobj":$(this).BlinkObj.apply(this, Params);break;
                                        case "playsprite":$(this).PlaySprite.apply(this, Params);break;
                                        case "shakeobj":$(this).ShakeObj.apply(this, Params);break;
                                        case "move":$(this).Move.apply(this, Params);break;
                                    }
                                break;

                            case "function":
                                Executed = true;
                                if(eval('typeof ' + Handle) === 'function'){
                                    try {eval(Handle + '.apply(this, Params);');}
                                    catch (e){Debug.Error(e,'Behavior.Href.Click:function');return false;}
                                }
                            break;

                            case "js":
                            case "javascript":
                                Executed = true;
                                try {eval(Detail);}catch (e){Debug.Error(e,'Behavior.Href.Click:js');return false;}
                                break;
                        }
                    }
                }
            }
            return Executed;
        },

        Place : {

            Focus : function(EventObj, EObj){
                var Obj = _$(EObj.Target), ClassNow = Obj.ClassName(), PClass = Obj.Attr('phclass');
                if(Obj.Attr('phlabel') !== null && Obj.Value() === Obj.Attr('phlabel')){
                    Obj.Value('');
                    Obj.DelAttr(Obj, 'phusing');
                    if(PClass !== null){
                        if(typeof(ClassNow) !== 'string')
                            ClassNow = '';
                        ClassNow = ClassNow.replace(new RegExp(PClass, 'g'), '');
                        ClassNow = ClassNow.replace(/\s$/, '');
                        Obj.ClassName(ClassNow);
                    }
                }
            },

            Blur : function(EventObj, EObj){
                var Obj = _$(EObj.Target), Placeholder = Obj.Attr('phlabel'), ClassNow = Obj.ClassName(), PClass = Obj.Attr('phclass');
                if(Obj.Attr('phlabel') !== null){
                    Obj.Attr({phusing:'true'});
                    if(Obj.Value() === ''){
                        Obj.Value(Placeholder);
                        if(PClass !== null){
                            if(typeof(ClassNow) !== 'string')
                                ClassNow = '';
                            ClassNow += (ClassNow === '' ? '' : ' ') + PClass
                            Obj.ClassName(ClassNow);
                        }
                    }
                }
            },

            Apply : function(ObjSel){
                ElmEnum(Var.Default(ObjSel, 'input, textarea'), function(){
                    var Obj = _$(this), Placeholder = Obj.Attr('phlabel'), ClassNow = Obj.ClassName(), PClass = Obj.Attr('phclass');
                    if(Obj.Attr('phlabel') !== null){
                        Obj.Attr({phusing:'true'});
                        if(Obj.Value() === ''){
                            Obj.Value(Placeholder);
                            if(PClass !== null){
                                if(typeof(ClassNow) !== 'string')
                                    ClassNow = '';
                                ClassNow += (ClassNow === '' ? '' : ' ') + PClass
                                Obj.ClassName(ClassNow);
                            }
                        }
                    }
                });
            },

            Revert : function(ObjSel){
                ElmEnum(Var.Default(ObjSel, 'input, textarea'),function(){
                    var Obj = _$(this), ClassNow = Obj.ClassName(), PClass = Obj.Attr('phclass');
                    if(Obj.Attr('phlabel') !== null && Obj.Value() === Obj.Attr('phlabel')){
                        Obj.Value('');
                        if(PClass !== null){
                            if(typeof(ClassNow) !== 'string')
                                ClassNow = '';
                            ClassNow = ClassNow.replace(new RegExp(PClass, 'g'), '');
                            ClassNow = ClassNow.replace(/\s$/, '');
                            Obj.ClassName(ClassNow);
                        }
                    }
                });
            }
        },

        Roll :
        {
            Over : function(EventObj, EObj){
                var TObj = _$(EObj.Target).Attr('_RO');
                if(TObj === null)
                    return;
                TObj = Common.UrlDecode(TObj, true);
                switch(EObj.Type){

                    case 'mouseleave':
                        if(TObj.Sel === false)
                            Behavior.Roll.Action(EObj.Target, TObj, "OUT", EObj);
                        break;

                    case 'mouseenter':
                        Behavior.Roll.Action(EObj.Target, TObj, "IN", EObj);
                        break;
                }
            },

            Selected : function(ObjSel){
                var SelRO = _$(SelObj).Attr(SelObj, '_RO');
                if(SelRO){
                    SelRO = Common.UrlDecode(SelRO, true);
                    if(SelRO.Grp === '')
                        return;
                    _$('[_RO]').Do(function(){
                        var ObjRO = Common.UrlDecode(_$(this).Attr('_RO'), true);
                        if(SelRO.Grp === ObjRO.Grp){
                            if(SelObj === this){
                                SelRO.Sel = true;
                                _$(SelObj).Attr({_RO:Common.UrlEncode(SelRO)});
                                Behavior.Roll.Action(SelObj, SelRO, "IN");
                            }else{
                                ObjRO.Sel = false;
                                _$(this).Attr({_RO:Common.UrlEncode(ObjRO)});
                                Behavior.Roll.Action(this, ObjRO, "OUT");
                            }
                        }
                    });
                }
            },

            Action : function(Obj, RO, IO, EObj){
                if(typeof(RO) === 'object' && RO !== null){
                    if(IO === "IN"){
                        var Target = Var.Default(RO.TObj, Obj);
                        var Value = RO.In;
                    }else if(IO === 'OUT'){
                        var Target = Var.Default(RO.RObj, Obj);
                        var Value = RO.Out;
                    } else
                        return false;
                    switch(RO.Method.toLowerCase()){
                        case "bgcolor":
                            _$(Target).Style({backgroundColor:Value});
                            break;
                        case "class":
                            _$(Target).ClassName(Value);
                            break;
                        case "sprite":
                            Sprite.Frame(Target, Var.Int(Value));
                            break;
                        case "background":
                            Value = Common.ImageUrl(Value);
                            _$(Target).Style({backgroundImage:Value});
                            break;
                        case "display":
                            _$(Target).Style({display:Value});
                            break;
                        case "visible":
                            _$(Target).Style({visibility:Value});
                            break;
                        case "style":
                            _$(Target).Style(Value);
                            break;
                        case "function":
                            if(typeof(Value) === 'function')
                                try {Value.apply(Target, [IO, EObj]);}catch (e){Debug.Error(e,'Behavior.Roll.Over.'+IO);return false;}
                            else
                                try {eval(Value +".apply(Target, [IO, EObj]);");}catch (e){Debug.Error(e,'Behavior.Roll.Over.'+IO);return false;}
                            break;
                    }
                }
            }
        },

        Href : {

            Protocol : function(PName, Function){
                if(typeof Function === 'function')
                    L.Protocol[PName.toLowerCase()] = Function;
            },

            Remove : function(Protocol){
                L.Protocol = Var.Remove(L.Protocol, Protocol);
            },

            Clear : function(Protocol){
                L.Protocol = {};
            },

            Click : function(EventObj, EObj){
                if(EObj.Click !== 1 || EObj.Draging === true){
                    Event.PreventDefault(EventObj);
                    return false;
                }
                var ObjNow = EObj.Target, Href = ObjNow.getAttribute("href", 2);
                if(Var.IsEmpty(Href)){
                while (ObjNow = ObjNow.parentNode)
                    if(ObjNow.getAttribute && (Href = ObjNow.getAttribute("href", 2)))
                        break;
                }
                if(Href instanceof Array)
                    Href = Href[0];
                if(!Var.IsEmpty(Href)){
                    var Continue = !Behavior.Command.apply(ObjNow, [Href]);
                    if(Continue === true && typeof(Href) === 'string' && Href.charAt(0) === '#'){
                        Continue = false;
                        window.location.href = Href;
                    }
                    if(Continue === false){
                        Event.PreventDefault(EventObj);
                        return Continue;
                    }
                }
            }
        }
    },


    //------------------------------------------------------------------------
    // JavaScript Object Event Control
    //------------------------------------------------------------------------
    Event = {

        EvtCustom : function(EType){
            if(!L.Event.hasOwnProperty(EType))
                L.Event[EType] = [];
        },

        PreventDefault : function(EventObj){
            if (!EventObj && window.event)
                var EventObj = window.event;
            if(EventObj){
                if(typeof(EventObj.preventDefault) === 'function')
                    EventObj.preventDefault();
                if(typeof(EventObj.stopPropagation) === 'function')
                    EventObj.stopPropagation();
                if(EventObj.defaultPrevented)
                    EventObj.defaultPrevented = true;
                if(EventObj.returnValue)
                    EventObj.returnValue = false;
            }
            return false;
        },

        Fire : function(EType, EventObj){
            if(!EventObj)EventObj = window.event;
            var IsParentLine = function(MainObj, RelatedObj){if(MainObj === document || MainObj === window || MainObj === RelatedObj) return true; else return false;};
            if(L.Event.hasOwnProperty(EType)){
                EObj = C.EObj;
                for(var i in L.Event[EType]){
                    var EventListen = L.Event[EType][i];
                    switch(EType){
                        case "dragstart":
                        case "drag":
                            if(IsParentLine(this, EObj.DragObj))
                                EventListen.Fn.apply(EObj.DragObj, [EventObj, EObj]);
                        break;

                        case "drop":
                            if(IsParentLine(this, EObj.DragObj) && EObj.DragObj.getAttribute('dragto', 2) !== null)
                                EventListen.Fn.apply(EObj.DragObj, [EventObj, EObj]);
                            else if(IsParentLine(this, EObj.DropObj))
                                EventListen.Fn.apply(EObj.DropObj, [EventObj, EObj]);
                        break;

                        case "mouseenter":
                            if(IsParentLine(this, EObj.Target)){
                                var RelatedObj = EObj.Related.parentNode;
                                while(EObj.Related.parentNode === EObj.Target)
                                    EventListen.Fn.apply(EObj.Target, [EventObj, EObj]);
                            }
                            break;

                        default:
                            if(IsParentLine(this, EObj.Target))
                                    EventListen.Fn.apply(EObj.Target, [EventObj, EObj]);
                        break;
                    }
                }
            }
        },

        Trigger : function(EType, EventObj, Fn, EID, Times){
            var args = [], Obj = this, Called = 0, Result = null;
            if(!EventObj)EventObj = window.event;
            Event.Collector(EventObj);
            if(L.Event.hasOwnProperty(EType)){
                switch(EType){
                    case 'mouseleave':
                    case 'mouseenter':
                        if(!C.EObj.Related || (C.EObj.Related !== Obj && !_$(Obj).Contain(C.EObj.Related))){
                            if(L.EvtObj.hasOwnProperty(EID))
                                L.EvtObj[EID].Times++;
                            if(typeof(Fn) === 'function')Result = Fn.apply(Obj, [EventObj, C.EObj]);
                        }
                    break;
                }
            }else{
                if(L.EvtObj.hasOwnProperty(EID))
                    L.EvtObj[EID].Times++;
                if(typeof(Fn) === 'function')Result = Fn.apply(Obj, [EventObj, C.EObj]);
            }
            if(L.EvtObj.hasOwnProperty(EID) && L.EvtObj[EID].Times === Times && Times > 0)
                $(Obj).Off(EType, L.EvtObj[EID].Fn, EID);
            if(Result === false)
                Event.PreventDefault(EventObj);
            return Result;
        },

        Collector : function(EventObj){

            //Select the Event Object.
            if(!EventObj)EventObj = window.event;

            //C.EObj = EObj;
            if(typeof(EventObj.type) === 'undefined')
                return false;

            //Type of event capture
            C.EObj.Type = EventObj.type;

            //Source Element Infor
            if (EventObj.target) C.EObj.Target = EventObj.target;
            if (C.EObj.Target && (C.EObj.Target.nodeType === 3 || C.EObj.Target.nodeType === 8)) // defeat Safari bug
            	C.EObj.Target = C.EObj.Target.parentNode;

            if(EventObj.relatedTarget)C.EObj.Related = EventObj.relatedTarget;
            if(C.EObj.Related && (C.EObj.Related.nodeType === 3 || C.EObj.Related.nodeType === 8)) // defeat Safari bug
                	C.EObj.Related = C.EObj.Related.parentNode;

             //Related Element Infor
             switch(C.EObj.Type){

                //Keyboard Event Infor
                case "keypress":
                case "keyup":
                case "keydown":
                    if (EventObj.keyCode) C.EObj.KeyCode = EventObj.keyCode;
                    else if (EventObj.which) C.EObj.KeyCode = EventObj.which;
                    if(C.EObj.Type === 'keypress')
                        C.EObj.KeyCode -= 32;
                    C.EObj.KeyChar = String.fromCharCode(C.EObj.KeyCode);

                    C.EObj.Alt = EventObj.altKey;
                    C.EObj.Shift = EventObj.shiftKey;
                    C.EObj.Ctrl = EventObj.ctrlKey;
                break;
             }

            //Mouse Coordinate Infor
            if(C.EObj.Type.indexOf("mouse") > -1
            || C.EObj.Type === 'click'
            || C.EObj.Type === 'dbclick'
            || C.EObj.Type === 'contextmenu'){

                if (EventObj.pageX || EventObj.pageY){
                	C.EObj.X = EventObj.pageX;
                	C.EObj.Y = EventObj.pageY;
                } else if (EventObj.clientX || EventObj.clientY){
                	C.EObj.X = EventObj.clientX + Common.Scroll.Left();
                	C.EObj.Y = EventObj.clientY + Common.Scroll.Top();
                }

                //Mouse Click Button Infor
                if (EventObj.which){
                    switch(EventObj.which){
                        case 1: C.EObj.Click = 1;break;
                        case 3: C.EObj.Click = 2;break;
                        case 2: C.EObj.Click = 3;break;
                    }
                }else if (EventObj.button){
                    switch(EventObj.button){
                        case 1: C.EObj.Click = 1;break;
                        case 2: C.EObj.Click = 2;break;
                        case 4: C.EObj.Click = 3;break;
                    }
                }
            }else
                C.EObj.Click = 0;

            //Mouse Wheel Infor
            if(C.EObj.Type !== "mousewheel" && C.EObj.Type !== "DOMMouseScroll" )
                C.EObj.Wheel = 0;
            else if (EventObj.wheelDelta) /* IE/Opera. */
                    C.EObj.Wheel = EventObj.wheelDelta / 120;
            else if (EventObj.detail)
                    C.EObj.Wheel = -EventObj.detail / 3;
            return C.EObj;
        },

        EObj : function(EventObj){
            return C.EObj;
        },

        GetReady : function(){

            var ReadyCall = function(){
                Behavior.Style();
                if(L.Ready.length > 0){
                    for(var i=0; i < L.Ready.length; i++)
                        try{L.Ready[i]();}catch(e){Debug.Error(e, 'Ready');}
                }
                C.Ready = true;
                L.Ready = [];
            };

            var Completed = function(e){
                if (!e)var e = window.event;
                var ImgLst = document.getElementsByTagName('img');
                var TCObj = ImgLst.length;
                var CNObj = 0;
                if(TCObj > 0)
                    for(var i in ImgLst)
                        if(_$(ImgLst[i]).Src() === ''){
                            CNObj++;
                            if(CNObj === TCObj)
                                ReadyCall();
                        }else if (typeof(this.naturalWidth) !== "undefined" || this.naturalWidth !== 0){
                            CNObj++;
                            if(CNObj === TCObj)
                                ReadyCall();
                        } else{
                            _$(ImgLst[i]).On('load', function(){
                                CNObj++;
                                if (typeof(this.naturalWidth) === "undefined" || this.naturalWidth === 0)
                                    _$(this).Remove();
                                if(CNObj === TCObj)
                                    ReadyCall();
                            });
                            _$(ImgLst[i]).On('error', function(){
                                CNObj++;
                                _$(this).Remove();
                                if(CNObj === TCObj)
                                    ReadyCall();
                            });
                        }
                else
                    ReadyCall();
            };

    		if (document.readyState === "complete") {
    			Job.Work(Completed, 1, 10);
    		} else if (document.addEventListener) {
                Document.On('DOMContentLoaded', Completed, false, 1);
                WinObj.On('load', Completed, false, 1);
    		} else {
    			Document.On('readystatechange', Completed, false, 1);
                WinObj.On('load', Completed, false, 1);
    			var top = false;
    			try {top = window.frameElement == null && document.documentElement;}catch(e){}
    			if ( top && top.doScroll ) {
    				(function doScrollCheck() {
    						try {
    							top.doScroll("left");
    						} catch(e) {
                                return Job.Work(doScrollCheck, 1, 10);
    						}
    						Completed();
    				})();
    			}
    		}
        }
    },

    //------------------------------------------------------------------------
    // JavaScript Internal Interval Execute System
    //------------------------------------------------------------------------
    Job = {

        Work : function(Action, Loop, Interval, Channel){
            if(typeof(Action) !== 'function')
                return false;
            Channel = Var.Default(Channel, '');
            C.Job.ID++;
            if(Interval <= 0)
                Interval = 1;
            if(C.Job.ID >= S.Max)
                C.Job.ID = 0;
            L.Job[C.Job.ID] = {
                    Action : Action,
                    Loop : Var.Default(Loop, 1),
                    Interval : Var.Default(Interval, 1),
                    Enable : true,
                    Channel : Channel
                };
            return C.Job.ID;
        },

        Run : function(){
            C.Tick++;
            if(C.Tick > 86400000)
                C.Tick = 0;

            if(Var.Len(L.Job) > 0)
                for (var ID in L.Job){
                    var ExeMode = 0;
                    if((Var.IsNumber(L.Job[ID].Loop) && L.Job[ID].Loop <= 0) || L.Job[ID].Loop === "break" || L.Job[ID].Loop === "stop")
                            ExeMode = 2;
                    else if(C.Tick % L.Job[ID].Interval === 0){
                        if(typeof(L.Job[ID].Loop) === 'number')
                            L.Job[ID].Loop--;
                        ExeMode = 1;
                    }
                    C.Job.Now = ID;
                    switch(ExeMode){
                        case 1:L.Job[ID].Action(ID);break;
                        case 2:Task.Next(ID);break;
                    }
                }
        },

        Now : function(){
            return C.Job.Now;
        },

        Remove : function(ID){
            if(Var.Len(L.Job) > 0){
                L.Job = Var.Remove(L.Job, ID);
            }
        },

        Clear : function(Channel){
            if(Var.Len(L.Job) > 0){
                Channel = Var.Default(Channel, '*');
                if(Channel === '*')
                    L.Job = {};
                else{
                    for (var ID in L.Job){
                        if(L.Job[ID].Channel === Channel || L.Job[ID].Channel === '*'){
                            L.Job = Var.Remove(L.Job, ID);
                        }
                    }
                }
            }
        },

        Break : function(Job_ID){
            if(Var.Len(L.Job) > 0 && typeof(L.Job[Job_ID]) !== "undefined")
                L.Job[Job_ID].Loop = 'break';
        }
    },


    //------------------------------------------------------------------------
    // JavaScript Internal Task System
    //------------------------------------------------------------------------
    Task = {
        Register : function(Action, Loop, Interval, Channel){
            Channel = Var.Default(Channel, '');
            C.Task.ID++;
            if(C.Task.ID >= S.Max)
                C.Task.ID = 0;
            L.Task[C.Task.ID] = {
                Channel : Channel,
                Action : Action,
                Loop : Loop,
                Interval : Interval
            };
            return C.Task.ID;
        },

        Remove : function(ID){
            if(Var.Len(L.Task) > 0)
                L.Task = Var.Remove(L.Task, ID);

        },

        Clear : function(Channel){
            if(Var.Len(L.Task) > 0){
                Channel = Var.Default(Channel, '*');
                if(Channel === '*'){
                    L.Task = {};
                }else{
                    for (var ID in L.Task){
                        if(L.Task[ID].Channel === Channel)
                            L.Task = Var.Remove(L.Task, ID);
                    }
                }
            }
        },

        Next : function(JID){
            var Channel = L.Job[JID].Channel;
            Job.Remove(JID);
            if(Var.Len(L.Task) > 0 && !Var.IsEmpty(Channel))
                for (var i in L.Task)
                    if(L.Task[i].Channel === Channel){
                        Job.Work(L.Task[i].Action, L.Task[i].Loop, L.Task[i].Interval, L.Task[i].Channel);
                        Task.Remove(i);
                        return i;
                    }
        },

        Start : function(Channel){
            if(Var.Len(L.Task) > 0)
                for (var i in L.Task)
                    if(L.Task[i].Channel === Channel){
                        Job.Work(L.Task[i].Action, L.Task[i].Loop, L.Task[i].Interval, L.Task[i].Channel);
                        Task.Remove(i);
                        return i;
                    }
        }
    },


    //------------------------------------------------------------------------
    // JavaScript Web HTML Effect highlight, fade in, fade out, etc..
    //------------------------------------------------------------------------
    Effect = {

        BlinkColor : function(JobID, BlinkColor){

            if(typeof(BlinkColor) === 'object' && BlinkColor !== null){

                var Obj = _$(BlinkColor.Obj);
                if(Obj.Attr('Effect.BlinkColor') !== BlinkColor.Access){
                    Job.Break(JobID);
                    return false;
                }

                if(BlinkColor.Now === 'ORI'){
                    BlinkColor.Now = 'COLOR';
                    Obj.Style({backgroundColor:BlinkColor.BColor});
                }else{
                    BlinkColor.Times--;
                    BlinkColor.Now = 'ORI';
                    Obj.Style({backgroundColor:BlinkColor.OColor});

                    if(BlinkColor.Times <= 0)
                        BlinkColor.Stop = true;
                }

                if(BlinkColor.Stop === true){
                    Effect.End(JobID, BlinkColor, 'BlinkColor');
                    return;
                }

            }else
                Job.Break(JobID);
        },

        //Setup Element Fancy_Overlay Color Blink Interval Function
        BlinkObj : function(JobID, BlinkObj){
            if(typeof(BlinkObj) === 'object' && BlinkObj !== null){

                var Obj = _$(BlinkObj.Obj);
                if(Obj.Attr('Effect.BlinkObj') !== BlinkObj.Access){
                    Job.Break(JobID);
                    return false;
                }

                if(BlinkObj.Now === BlinkObj.OVisible){
                    if(BlinkObj.Now === 'visible')
                        BlinkObj.Now = 'hidden';
                    else
                        BlinkObj.Now = 'visible';
                    Obj.Style({visibility:'hidden'});
                }else{
                    BlinkObj.Times--;
                    Obj.Style({visibility: BlinkObj.OVisible});

                    if(BlinkObj.Times <= 0)
                        BlinkObj.Stop = true;
                }

                if(BlinkObj.Stop === true)
                    Effect.End(JobID, BlinkObj, 'BlinkObj');

            }else
                Job.Break(JobID);
        },

        //Setup Element Shake Interval Function
        ShakeObj : function(JobID, ShakeObj){

            if(typeof(ShakeObj) === 'object' && ShakeObj !== null){

                var Obj = _$(ShakeObj.Obj);
                if(Obj.Attr('Effect.ShakeObj') !== ShakeObj.Access){
                    Job.Break(JobID);
                    return false;
                }

                if(ShakeObj.Power <= 0){
                    Obj.Style({
                        left:ShakeObj.Left+'px',
                        top:ShakeObj.Top+'px'
                    });
                    ShakeObj.Stop = true;
                }else{
                    Obj.Style({
                        left:Var.Float((Common.Random(1,10) >= 5)? ShakeObj.Left + ShakeObj.Power : ShakeObj.Left - ShakeObj.Power)+'px',
                        top:Var.Float((Common.Random(1,10) >= 5)? ShakeObj.Top + ShakeObj.Power : ShakeObj.Top - ShakeObj.Power)+'px'
                    });
                    ShakeObj.Power -= ShakeObj.Rate;
                }

                if(ShakeObj.Stop === true)
                    Effect.End(JobID, ShakeObj, 'ShakeObj');

            }else
                Job.Break(JobID);
        },

        //Setup Element Shake Interval Function
        PlaySprite : function(JobID, PlaySprite){
            if(typeof(PlaySprite) === 'object' && PlaySprite !== null){

                var Obj = _$(PlaySprite.Obj);
                if(Obj.Sprite.IsInit() === false || Obj.Attr('Effect.PlaySprite') !== PlaySprite.Access){
                    Job.Break(JobID);
                    return false;
                }

                if(typeof(PlaySprite.Times) === 'number' && PlaySprite.Times <= 0)
                    PlaySprite.Stop = true;
                else{
                    if(PlaySprite.Frame.search(",") > -1){
                        var Frames = PlaySprite.Frame.split(',');
                        if(!isNaN(PlaySprite.Now))
                            PlaySprite.Now++;
                        else
                            PlaySprite.Stop = true;
                        var iFrame = Var.Int(Frames[PlaySprite.Now]);
                        Obj.Sprite.Frame(PlaySprite.Obj, iFrame);
                        if(PlaySprite.Now > Var.Len(Frames) - 1){
                            PlaySprite.Now = -1;
                            if(typeof(PlaySprite.Times) === 'number')
                                PlaySprite.Times--;
                        }
                    }else
                        PlaySprite.Stop = true;
                }

                if(PlaySprite.Stop === true)
                    Effect.End(JobID, PlaySprite, 'Sprite');

            }else
                Job.Break(JobID);
        },

        //Setup Element Fadein Interval Function
        Fade : function(JobID, Fade){
            if(typeof(Fade) === 'object' && Fade !== null){
                var Done = false, Obj = _$(Fade.Obj);
                if(Obj.Attr('Effect.Fade') !== Fade.Access){
                    Job.Break(JobID);
                    return false;
                }
                Fade.Now = Obj.Opacity();
                Fade.Now += Fade.Dir * Fade.Speed;
                if(Fade.Mode === 'ease')
                    Fade.Speed += Fade.Rate;
                if(Fade.Dir === 0 && Fade.Now === Fade.To)Fade.Stop = true
                else if(Fade.Dir > 0 && Fade.Now >= Fade.To)Fade.Stop = true
                else if(Fade.Dir < 0 && Fade.Now <= Fade.To)Fade.Stop = true
                if(Fade.Stop === true)Fade.Now = Fade.To;
                Obj.Opacity(Fade.Now);
                if(Fade.Stop === true)
                    Effect.End(JobID, Fade, 'Fade');
            }else
                Job.Break(JobID);
        },

        //Setup Element Move Interval Function
        Move : function(JobID, Move){

            if(typeof(Move) === 'object' && Move !== null){

                var Obj = _$(Move.Obj);
                if(Obj.Attr('Effect.Move') !== Move.Access){
                    Job.Break(JobID);
                    return false;
                }

                if(Move.Mode !== 'ease'){
                    Move.Speed.X = ((Move.Speed.X <= 0)? 1 : Move.Speed.X);
                    Move.Speed.Y = ((Move.Speed.Y <= 0)? 1 : Move.Speed.Y);
                }
                if(Move.Unit !== 'px'){
                    var Left = Var.Float(Move.Obj.style['left']);
                    var Top = Var.Float(Move.Obj.style['top']);
                }else{
                    var Left = Obj.Style('left');
                    var Top = Obj.Style('top');
                }
                if(Move.Dir.X > 0)
                    Left += ((Left + Move.Speed.X > Move.Target.Left) ? Move.Target.Left - Left : Move.Speed.X);
                else if(Move.Dir.X < 0)
                    Left -= ((Left - Move.Speed.X < Move.Target.Left) ? Left - Move.Target.Left : Move.Speed.X);

                if(Move.Dir.Y > 0)
                    Top += ((Top + Move.Speed.Y > Move.Target.Top) ? Move.Target.Top - Top : Move.Speed.Y);
                else if(Move.Dir.Y < 0)
                    Top -= ((Top - Move.Speed.Y < Move.Target.Top) ? Top - Move.Target.Top : Move.Speed.Y);

                if(Move.Mode === 'ease'){
                    Move.Rate = ((Move.Rate <= 0) ? 1 : Move.Rate);
                    Move.Speed.X += Move.Rate;
                    Move.Speed.Y += Move.Rate;
                }

                if(
                    ((Move.Dir.X > 0 && Var.Int(Left) >= Var.Int(Move.Target.Left)) || (Move.Dir.X < 0 && Var.Int(Move.Target.Left) >= Var.Int(Left)) || Move.Dir.X === 0) && //HitTest in X line
                    ((Move.Dir.Y > 0 && Var.Int(Top) >= Var.Int(Move.Target.Top)) || (Move.Dir.Y < 0 && Var.Int(Move.Target.Top) >= Var.Int(Top)) || Move.Dir.Y === 0) //HitTest in Y Line
                 ){
                    Obj.Style({left:Move.Target.Left + Move.Unit, top:Move.Target.Top + Move.Unit});
                    Move.Stop = true;
                }else
                    Obj.Style({left:Left + Move.Unit, top:Top + Move.Unit});

                if(Move.Stop === true)
                    Effect.End(JobID, Move, 'Move');

            }else
                Job.Break(JobID);
        },

        //Setup Element Move Interval Function
        Grow : function(JobID, Grow){

            if(typeof(Grow) === 'object' && Grow !== null){

                var Obj = _$(Grow.Obj);
                if(Obj.Attr('Effect.Grow') !== Grow.Access){
                    Job.Break(JobID);
                    return false;
                }

                if(Grow.Mode !== 'ease'){
                    Grow.Speed.X = ((Grow.Speed.X <= 0)? 1 : Grow.Speed.X);
                    Grow.Speed.Y = ((Grow.Speed.Y <= 0)? 1 : Grow.Speed.Y);
                }

                if(Grow.Dir.X < 0)
                    var SpeedX = Grow.Speed.X * -1;
                else
                    var SpeedX = Grow.Speed.X;
                if(Grow.Dir.Y < 0)
                    var SpeedY = Grow.Speed.Y * -1;
                else
                    var SpeedY = Grow.Speed.X;

                switch(Grow.Type){
                    case "width":
                        if(Grow.Dir.X > 0){
                            if (Grow.Now.Width >= Grow.Target.Width)Grow.Now.Height += SpeedY; else Grow.Now.Width += SpeedX;
                        }else{
                            if (Grow.Now.Width <= Grow.Target.Width)Grow.Now.Height += SpeedY; else Grow.Now.Width += SpeedX;
                        }
                    break;

                    case "height":
                        if(Grow.Dir.Y > 0){
                            if (Grow.Now.Height >= Grow.Target.Height)Grow.Now.Width += SpeedX; else Grow.Now.Height += SpeedY;
                        }else{
                            if (Grow.Now.Height <= Grow.Target.Height)Grow.Now.Width += SpeedX; else Grow.Now.Height += SpeedY;
                        }
                    break;

                    default:
                        Grow.Now.Width += SpeedX;
                        Grow.Now.Height += SpeedY;
                    break;
                }

                if(Grow.Dir.X > 0){if (Grow.Now.Width > Grow.Target.Width)Grow.Now.Width = Grow.Target.Width;}else{if (Grow.Now.Width < Grow.Target.Width)Grow.Now.Width = Grow.Target.Width;}
                if(Grow.Dir.Y > 0){if (Grow.Now.Height > Grow.Target.Height)Grow.Now.Height = Grow.Target.Height;}else{if (Grow.Now.Height < Grow.Target.Height)Grow.Now.Height = Grow.Target.Height;}

                Obj.Style({
                    width:Grow.Now.Width + Grow.Unit,
                    height:Grow.Now.Height + Grow.Unit
                });

                if(Grow.Now.Width === Grow.Target.Width && Grow.Now.Height === Grow.Target.Height){
                    Obj.Style({overflow:Grow.Overflow});
                    Grow.Stop = true;
                }else{
                    if(Grow.Mode === 'ease'){
                        Grow.Rate = ((Grow.Rate <= 0) ? 1 : Grow.Rate);
                        Grow.Speed.X += Grow.Rate;
                        Grow.Speed.Y += Grow.Rate;
                    }
                }

                if(Grow.Stop === true)
                    Effect.End(JobID, Grow, 'Grow');

            }else
                Job.Break(JobID);
        },

        End :function(JobID, Data, Type){
            if(typeof(Data) === 'object' && typeof(Type) === 'string'){
                _$(Data.Obj).DelAttr('Effect.' + Type);
                Job.Break(JobID);
                if(typeof(Data.CB) === 'function'){
                    try {Data.CB.apply(Data.Obj);}catch (e){Debug.Error(e,'Effect.' + Type + '.Callback');return false;}
                }
            }
        },
    },

    //------------------------------------------------------------------------
    //JavaScript Debug, only able to use when debug element exits.
    //------------------------------------------------------------------------
    Debug = {
        Log : function(Debug_Text, Clear){
            var Debuger = Document.Create('div', {id:'__debug__', style:{display:'none'}});
            if(typeof(Clear)=='boolean' && Clear === true)
                Debuger.innerHTML = Debug_Text;
            else
                Debuger.innerHTML += Debug_Text + '<br />';
        },

        Error : function(ErrObj, Report, Url){
            var Stack = (ErrObj.hasOwnProperty('stack') ? ErrObj.stack.split(/\n/, 2) : '-'), StackMsg;
            Stack.splice(0, 1);
            for(var i in Stack)
                Stack[i] = Var.LTrim(Stack[i]);
            var Msg = Var.Default(ErrObj, Var.Default(ErrObj, '-', 'description'), 'message'),
            Error = {
                Type : ErrObj.name.toString(),
                Message : Var.Default(Msg, '-'),
                Line : Var.Default(ErrObj, '-', 'lineNumber'),
                Char : Var.Default(ErrObj, '-', 'columnNumber'),
                Reporter : Var.Default(Report, '-'),
                Stack : Stack,
                Url : Var.Default(ErrObj, Url, 'filename')
            };
            if(Common.BrowserType() === 'msie')
                Debug.Console("Type: "+Error.Type+"\nMessage: "+Error.Message+"\nReporter:"+Error.Reporter+"\nStack: "+Error.Stack+"\nUrl: "+Error.Url);
            else{
                Debug.Console(Error);
            }
        },

        Console : function(Data){
            try{console.log(Data);}catch(e){};
        }
    },

    //------------------------------------------------------------------------
    //JavaScript Setup Ready Callback
    //------------------------------------------------------------------------
    Ready = function(Callback){
        if(typeof(Callback) === 'function'){
            if(C.Ready === false)
                L.Ready[L.Ready.length] = Callback;
            else
                Callback();
        }
    };

    //------------------------------------------------------------------------
    //JavaScript Select Obj from Selector And Return Object Method
    //------------------------------------------------------------------------
    function _$(Selector){

        if(Selector === document && Document)
            return Document;

        if(Selector === window && WinObj)
            return WinObj;

        $ = function(ElmObj){
           var _ = $.prototype = {

                Type : function(){
                    return 'Object';
                },

                First : function(){
                    return _.ElmObjs[0];
                },

                Last : function(){
                    return _.ElmObjs[_.ElmObjs.length];
                },

                Enum : function(){
                    return _.ElmObjs;
                },

                Do : function(Callback, Objs){
                    if(typeof(Objs) === 'string' || (typeof(Objs) === 'object' && Objs !== null))
                        Objs = ElmSelector(Objs);
                    if(Objs instanceof Array === false)
                        Objs = _.ElmObjs;
                    if(Objs.length > 0){
                        if(typeof(Callback) === 'function'){
                            for(var i in Objs)
                                Callback.apply(Objs[i]);
                        return Objs.length;
                        }else
                            return Objs.length;
                    }
                },

            	Create : function(Type, Property){
                    var ObjID = Var.Default(Property, false, 'id');
                    if(ObjID === false || !document.getElementById(ObjID)){
                        Property = Property || {};
                        var CObj = document.createElement(Type);
                        if(!Property.hasOwnProperty('parent')){
                            var PObj = _.ElmObjs[0];
                            if(PObj === document)
                                PObj = document.getElementsByTagName('body')[0];
                            Property.parent = PObj;
                        }
                        for(var Name in Property){
                            var Value = Property[Name];
                            if(Value !== null && Value !== '' && typeof(Value) !== 'undefined'){
                                switch(Name){
                                    case "id": if(typeof(Value) === 'string' && Value !== '')CObj.id = Value; break;
                                    case "parent": _.AppendChild(CObj, Value); break;
                                    case "attr": _.Attr(Value, CObj); break;
                                    case "style": _.Style(Value, CObj); break;
                                    case "classname": CObj.className = Value; break;
                                    case "html": CObj.innerHTML = Value; break;
                                    case "css": CObj.style.cssText = Value; break;
                                    case "value": CObj.value = Value; break;
                                    case "checked": CObj.checked = Value; break;
                                    case "href": CObj.setAttribute('href', Value); break;
                                    case "src": CObj.src = Value;break;
                                    default: CObj[Name] = Value; break;
                                }
                            }
                        }
                        Behavior.Style();
                        return CObj;
                    }else
                        return document.getElementById(ObjID);
            	},

            	Clone : function(ObjToClone, All, Property){
                        if(!IsDom(ObjToClone))
                            return false;
                        var ObjID = Var.Default(Property, false, 'id');
                        if(ObjID !== false && document.getElementById(ObjID))
                            return false;
                        Property = Property || {};
                        if(All)All = true; else All = false;
                        var CObj = ObjToClone.cloneNode(All);
                        if(!Property.hasOwnProperty('parent')){
                            var PObj = _.ElmObjs[0];
                            if(PObj === document)
                                PObj = document.getElementsByTagName('body')[0];
                            Property.parent = PObj;
                        }
                        for(var Name in Property){
                            var Value = Property[Name];
                            if(Value !== null && typeof(Value) !== 'undefined'){
                                switch(Name){
                                    case "id": if(typeof(Value) === 'string' && Value !== '')CObj.id = Value; break;
                                    case "parent": _.AppendChild(CObj, Value); break;
                                    case "attr": _.Attr(Value, CObj); break;
                                    case "style": _.Style(Value, CObj); break;
                                    case "classname": CObj.className = Value; break;
                                    case "html": CObj.innerHTML = Value; break;
                                    case "css": CObj.style.cssText = Value; break;
                                    case "value": CObj.value = Value; break;
                                    case "checked": CObj.checked = Value; break;
                                    case "href": CObj.setAttribute('href', Value); break;
                                    case "src": CObj.src = Value;break;
                                    default: CObj[Name] = Value; break;
                                }
                            }
                        }
                        Behavior.Style();
                    	return CObj;
            	},

            	Update : function(Property){
                    if(typeof(Property) !== 'object' || Property === null)
                        return false;
                    Property = Property || {};
                    for(var Name in Property){
                        var Value = Property[Name];
                        if(Value !== null && typeof(Value) !== 'undefined'){
                            switch(Name){
                                case "attr": _.Attr(Value); break;
                                case "style": _.Style(Value); break;
                                case "classname": _.ClassName(Value); break;
                                case "html": _.Html(Value); break;
                                case "css": _.Css(Value); break;
                                case "value": _.Value(Value); break;
                                case "checked": _.Check(Value); break;
                                case "href": _.Attr({href:Value}); break;
                                case "src": _.Src(Value);break;
                                default: _.Do(function(){this[Name] = Value;}); break;
                            }
                        }
                    }
                    Behavior.Style();
            	},

                Remove : function(ObjSel){
                    _.Do(function(){
                        if(this.nodeName !== 'HTML' && this.nodeName !== 'HEAD' && this.nodeName !== 'BODY' && this.parentNode)
                            this.parentNode.removeChild(this);
                    }, ObjSel);
                },

                NextElm : function(RefObj){
                    var RefObj = RefObj.nextSibling;
                    while (NextObj.nodeType !== 1)
                      NextObj = NextObj.nextSibling;
                    return NextObj;
                },

                PrevElm : function(RefObj){
                    var PrevObj = RefObj.previousSibling;
                    while (PrevObj.nodeType !== 1)
                        PrevObj = PrevObj.previousSibling;
                    return PrevObj;
                },

                Contain : function(ElemCheck){
                    Return.Clear();
                    ElemCheck = ElmSelector(ElemCheck)[0];
                    _.Do(function(){
                        while ((ElemCheck = ElemCheck.parentNode))
            				if (ElemCheck === this)
            					return Return.Insert(this, true);
                        return Return.Insert(this, false);
                    });
                    return Return.Value();
                },

                ListChild : function(Callback, ObjSel) {
                    Return.Clear();
                    _.Do(function(){
                        var Obj = this, Childs = [], Child = null;
                        if (Obj.children){
                            for (var i = 0; i < Obj.children.length; i++){
                                Child = Obj.children[i];
                                Childs.push(Child);
                                if(typeof(Callback) === 'function')
                                    Callback(Child);
                            }
                        }else{
                            for (var i = 0; i < Obj.childNodes.length; i++){
                                if (Obj.childNodes[i].nodeType === 1){
                                    Child = Obj.childNodes[i];
                                    Childs.push(Child);
                                    if(typeof(Callback) === 'function')
                                        Callback(Child);
                                }
                            }
                        }
                        Return.Insert(Obj, Childs);
                    });
                    return Return.Value();
                },

                InsertBeforeElm : function(MoveObj, RefObj){
                    var Parent = RefObj.parentNode;
                    try{Parent.insertBefore(MoveObj, RefObj);}catch (e){Debug.Error(e,'Child.Insert.Before');};
                },

                InsertAfterElm : function(MoveObj, RefObj){
                    var Parent = RefObj.parentNode;
                    try{
                        if(Parent.lastChild !== RefObj)
                            Parent.insertBefore(MoveObj, _.NextElm(RefObj));
                    }catch (e){Debug.Error(e,'Child.Insert.After');};
                },

                AppendChild : function(Child, ObjSel){
                    _.Do(function(){this.appendChild(Child);}, ObjSel);
                },

                ClearChild : function(ObjSel){
                    _.Do(function(){
                        while(this.firstChild)
                            this.removeChild(this.firstChild);
                    }, ObjSel);
                },

                Style: function(Css, ObjSel){
                    if(typeof(Css) === 'string'){
                        Return.Clear();
                        var SType = (document.defaultView && document.defaultView.getComputedStyle ? 'defaultView' : '');
                        _.Do(function(){
                            if (SType === 'defaultView')
                                var CSS_Syle = document.defaultView.getComputedStyle(this, '').getPropertyValue(Css);
                            else if (this.currentStyle){
                                Css = Css.replace(/\-(\w)/g, function (StrMatch, P1){return P1.toUpperCase();});
                                var CSS_Syle = this.currentStyle[Css];
                            }else
                                var CSS_Syle = this.style[Css];
                            if(Var.IsNumber(CSS_Syle) && /px$/.test(CSS_Syle))
                                CSS_Syle = Var.Float(CSS_Syle);
                            Return.Insert(this, CSS_Syle);
                        }, ObjSel);
                        return Return.Value();
                    }else if(typeof(Css) === 'object' && Var.Len(Css) > 0){
                        _.Do(function(){
                                for(var Key in Css)
                                    try{this.style[Key] = Css[Key];}catch(e){Debug.Error(e,'Obj.Style.Set');}
                        }, ObjSel);
                    }
                },

                Css : function(CssText){
                    if(CssText === undefined){
                        Return.Clear();
                        _.Do(function(){Return.Insert(this, this.style.cssText);}, ObjSel);
                        return Return.Value();
                    }else
                        _.Do(function(){this.style.cssText=CssText;}, ObjSel);
                },

                Left : function(){
                    return _.Style('left');
                },

                Top : function(){
                    return _.Style('top');
                },

                Width : function(){
                    return _.Style('width');
                },

                Height : function(){
                    return _.Style('height');
                },

                HasScrollX : function(){
                    Return.Clear();
                    _.Do(function(){
                        if(this === window || this === document || this.tagName === 'BODY' || this.tagName === 'HEAD' || this.tagName === 'HTML'){
                            if(Common.Scroll.Width() > Common.View.Width())
                                Return.Insert(this, true);
                            else
                                Return.Insert(this, false);
                        }else{
                            if(this.scrollWidth > this.clientWidth)
                                Return.Insert(this, true);
                            else
                                Return.Insert(this, false);
                        }
                    });
                    return Return.Value();
                },

                HasScrollY : function(){
                    Return.Clear();
                    _.Do(function(){
                        if(this === window || this === document || this.tagName === 'BODY' || this.tagName === 'HEAD' || this.tagName === 'HTML'){
                            if(Common.Scroll.Height() > Common.View.Height())
                                Return.Insert(this, true);
                            else
                                Return.Insert(this, false);
                        }else{
                            if(this.scrollHeight > this.clientHeight)
                                Return.Insert(this, true);
                            else
                                Return.Insert(this, false);
                        }
                    });
                    return Return.Value();
                },

                Opacity : function(Trans, ObjSel) {
                    if(Trans === undefined){
                        Return.Clear();
                        _.Do(function(){
                            var Opacity = _.Style(C.Opacity, this);
                            if(C.Opacity === 'filter'){
                                Opacity = Opacity.replace("alpha(opacity=","")
                                Opacity = Opacity.replace(")","");
                            }
                            if(Opacity === '')
                                Opacity = 1;
                            Opacity = Var.Float(Opacity);
                            if(C.Opacity !== 'filter')
                                Opacity *= 100;
                            if(Opacity > 100)
                                Opacity = 0;
                            Return.Insert(this, Opacity);
                        }, ObjSel);
                        return Return.Value();
                    }else{
                        Trans = Var.Float(Trans / 100);
                        if(Trans > 100)
                            Trans = 100;
                        else if(Trans < 0)
                            Trans = 0;
                        _.Do(function(){
                            this.style[C.Opacity] = (C.Opacity === 'filter') ?  "alpha(opacity=" + (Trans * 100) + ")" : Trans;
                        }, ObjSel);
                    }
                },

                OuterWidth : function(ObjSel){
                    Return.Clear();
                    _.Do(function(){
                        var Width = _.Style("width", this)
                        Width += _.Style("margin-left", this);
                        Width += _.Style("margin-right", this);
                        Width += _.Style("padding-left", this);
                        Width += _.Style("padding-right", this);
                        Width += _.Style("border-left-width", this);
                        Width += _.Style("border-right-width", this);
                        Return.Insert(this, Width);
                    }, ObjSel);
                    return Return.Value();
                },

                OuterHeight: function(ObjSel){
                    Return.Clear();
                    _.Do(function(){
                        var Height = _.Style("height", this);
                        Height += _.Style("margin-top", this);
                        Height += _.Style("margin-bottom", this);
                        Height += _.Style("padding-top", this);
                        Height += _.Style("padding-bottom", this);
                        Height += _.Style("border-top-width", this);
                        Height += _.Style("border-bottom-width", this);
                        Return.Insert(this, Height);
                    }, ObjSel);
                    return Return.Value();
                },

                GetAPos : function(ObjSel) {
                    Return.Clear();
                    _.Do(function(){
                        var _ObjPos = this.getBoundingClientRect();
                        var SLeft = Common.Scroll.Left();
                        var STop = Common.Scroll.Top();
                        Return.Insert(this, {
                            left: _ObjPos.left + SLeft,
                            top: _ObjPos.top + STop,
                            right: _ObjPos.right + SLeft,
                            bottom: _ObjPos.bottom + STop,
                            width: _ObjPos.right - _ObjPos.left,
                            height: _ObjPos.bottom - _ObjPos.top,
                        });
                    }, ObjSel);
                    return Return.Value();
                },

                Disabled : function(Disabled, ObjSel){
                    if(Disabled === undefined){
                        Return.Clear();
                        _.Do(function(){Return.Insert(this, this.disabled);}, ObjSel);
                        return Return.Value();
                    }else
                        _.Do(function(){this.disabled = Disabled;}, ObjSel);
                },

                ToggleDisplay : function(Default, ObjSel){
                    Default = _$.Var.Default(Default, 'block');
                    _.Do(function(){if(_.Style('display', this) === 'none') _.Style({display:Default}, this); else _.Style({display:'none'}, this);}, ObjSel);
                },

                ClassName : function(ClassName, ObjSel){
                    if(ClassName === undefined){
                        Return.Clear();
                        _.Do(function(){Return.Insert(this, this.className);}, ObjSel);
                        return Return.Value();
                    }else
                        _.Do(function(){this.className = ClassName;}, ObjSel);
                },

                Value : function(Value, Append, ObjSel){
                    if(Value === undefined){
                        Return.Clear();
                        _.Do(function(){Return.Insert(this, this.value);}, ObjSel);
                        return Return.Value();
                    }else
                        _.Do(function(){if(Append === true)this.value += Value; else this.value = Value; }, ObjSel);
                },

                Html : function(Html, Append, ObjSel){
                    if(Html === undefined){
                        Return.Clear();
                        _.Do(function(){Return.Insert(this, this.innerHTML);}, ObjSel);
                        return Return.Value();
                    }else{
                        _.Do(function(){if(Append === true)this.innerHTML += Html; else this.innerHTML = Html; }, ObjSel);
                        Behavior.Style();
                    }
                },

                Check : function(Check, ObjSel){
                    if(Check === undefined){
                        Return.Clear();
                        _.Do(function(){Return.Insert(this, this.checked);}, ObjSel);
                        return Return.Value();
                    }else
                        _.Do(function(){this.checked = Check;}, ObjSel);
                },

                Src : function(Source, ObjSel){
                    if(Source === undefined){
                        Return.Clear();
                        _.Do(function(){Return.Insert(this, this.src);}, ObjSel);
                        return Return.Value();
                    }else
                        _.Do(function(){this.src = Source;}, ObjSel);
                },

                Map : function(MapName, ObjSel){
                    if(typeof(Disabled) === 'undefined'){
                        Return.Clear();
                        _.Do(function(){if(this.tagName = 'IMG')Return.Insert(this, this.useMap);}, ObjSel);
                        return Return.Value();
                    }else
                        _.Do(function(){if(this.tagName = 'IMG')this.useMap = '#' + MapName;}, ObjSel);
                },

                Attr : function(AttrPV, ObjSel){
                    if(typeof(AttrPV) === 'string'){
                        Return.Clear();
                        _.Do(function(){
                            if(this.tagName === 'HTML')
                                return;
                            try{Return.Insert(this, this.getAttribute(AttrPV, 2));}catch(e){return false;}
                            }, ObjSel);
                        return Return.Value();
                    }else if(typeof(AttrPV) === 'object'){
                        _.Do(function(){
                            for(var Key in AttrPV)
                                this.setAttribute(Key, AttrPV[Key].toString());
                        }, ObjSel);
                    }
                },

                DelAttr : function(AttName, ObjSel){
                    _.Do(function(){
                        if(typeof(AttName) === 'string')
                            this.removeAttribute(AttName);
                        else if(AttName instanceof Array)
                            for(var Key in AttName)
                                this.removeAttribute(AttName[Key]);
                    }, ObjSel);
                },

                Selectable : function(Toggle){
                    if(Toggle === true)
                        _.Do(function(){this.style[C.Selectable]='text'});
                    else
                        _.Do(function(){this.style[C.Selectable]='none'});
                },

                Checked : function(){
                    var Value = '';
                    _.Do(function(){if(this.tagName === 'INPUT' && this.type === 'checkbox' && this.checked === true)Value += ((Value=='')?'':',') + this.value;});
                    return Value;
                },

                Href : function(Link){
                       _.Attr({href:Link});
                       _.Style({cursor:"pointer"});
                },

                Validate : function(VObj){
                    if(typeof(VObj) !== 'object' || !VObj.hasOwnProperty('Method'))
                        return false;
                    _.Do(function(){
                        if(this.tagName === 'INPUT' || this.tagName === 'TEXTAREA' || this.tagName === 'SELECT'){
                            if(typeof(VObj.Method) === 'string')
                                VObj.Method = VObj.Method.toLowerCase();
                            var iValidate = L.Validate.length;
                            for(var i in L.Validate)
                                if(L.Validate[i].Target === this){
                                    iValidate = i;
                                    break;
                                }
                            L.Validate[iValidate] = {
                                Target : this,
                                Method : Var.Default(VObj, 'must', 'Method'),
                                Extend : Var.Default(VObj, null, 'Extend'),
                                Min : Var.Default(VObj, 0, 'Min'),
                                Max : Var.Default(VObj, 0, 'Max'),
                                Feedback : Var.Default(VObj, {},'Feedback')
                            };
                        }
                    });
                },

                BlinkColor : function(Config, Channel){
                    _.Do(function(){
                    if(_.Attr('Effect.BlinkColor', this) === null){
                        var Interval = Var.Default(Config, S.Effect.Interval * 10, 'Interval');
                        var OColor = _.Style('background-color', this);
                        var Times = Var.Default(Config, 1, 'Times');
                        if(OColor === '')
                            OColor = "#fff";
                        var BlinkColor = {
                            Access : Common.GenCode(10),
                            Obj : this,
                            OColor : OColor,
                            BColor : Var.Default(Config, 1, 'Color'),
                            Times : Times,
                            Now : 'ORI',
                            CB : Var.Default(Config, false, 'Callback'),
                            Stop : false
                        };
                        _.Attr({'Effect.BlinkColor':BlinkColor.Access}, this);

                        //Register Effect Job Function
                        if(Channel !== undefined)
                            Task.Register(function(ID){Effect.BlinkColor(ID, BlinkColor);}, Var.Int(Times) * 2, Interval, Channel);
                        else
                            Job.Work(function(ID){Effect.BlinkColor(ID, BlinkColor);}, Var.Int(Times) * 2, Interval);
                        }
                    });
                },

                //Setup Element BlinkObj System
                BlinkObj : function(Config, Channel){
                    _.Do(function(){
                        if(_.Attr('Effect.BlinkObj', this) === null){
                            var Times = Var.Default(Config, 1, 'Times'),
                            Interval = Var.Default(Config, S.Effect.Interval, 'Interval'),
                            BlinkObj = {
                                Access : Common.GenCode(10),
                                Obj : this,
                                OVisible : _.Style('visibility', this),
                                Times : Times,
                                Now : _.Style('visibility', this),
                                CB : Var.Default(Config, false, 'Callback'),
                                Stop : false
                            };
                            _.Attr({'Effect.BlinkObj':BlinkObj.Access}, this);

                            //Register Effect Job Function
                            if(Channel !== undefined)
                                Task.Register(function(ID){Effect.BlinkObj(ID, BlinkObj);}, Var.Int(Times) * 2, Interval, Channel);
                            else
                                Job.Work(function(ID){Effect.BlinkObj(ID, BlinkObj);}, Var.Int(Times) * 2, Interval);
                        }
                    });
                },

                //Setup Element ShakeObj System
                ShakeObj : function(Config, Channel){
                        _.Do(function(){
                            if(_.Attr('Effect.ShakeObj', this) === null){
                                var Interval = Var.Default(Config, S.Effect.Interval, 'Interval');
                                var ShakeObj = {
                                    Access : Common.GenCode(10),
                                    Obj : this,
                                    Power : Var.Default(Config, 10, 'Power'),
                                    Rate : Var.Default(Config, S.Effect.Rate, 'Rate'),
                                    Left : Var.Int(_.Style('left', this)),
                                    Top : Var.Int(_.Style('top', this)),
                                    CB : Var.Default(Config, false, 'Callback'),
                                    Stop : false
                                };
                                _.Attr({'Effect.ShakeObj':ShakeObj.Access}, this);

                                //Register Effect Job Function
                                if(Channel !== undefined)
                                    Task.Register(function(ID){Effect.ShakeObj(ID, ShakeObj);}, 'contition', Interval, Channel);
                                else
                                    Job.Work(function(ID){Effect.ShakeObj(ID, ShakeObj);}, 'contition', Interval);
                            }
                        });
                    },

                //Setup Element PlaySprite System
                PlaySprite : function(Config, Channel){
                        _.Do(function(){
                            if(_.Attr('Effect.PlaySprite', this) === null){
                                var Interval = Var.Default(Config, S.Effect.Interval, 'Interval');
                                var Frame = Var.Default(Config, '','Frame');
                                if(Frame.search(",") > -1){
                                    var Frames = Frame.split(',');
                                    Frame = '';
                                    for(var Key in Frames){
                                        var iFrame = Var.Int(Frames[Key]);
                                        if(!isNaN(iFrame))
                                            Frame += (Frame === '') ? iFrame : ',' + iFrame;
                                    }
                                }else{
                                    if(!isNaN(Frame) && Frame !== ''){
                                        _$(Obj).Sprite.Frame(Var.Int(Frame));
                                        return true;
                                    }else{
                                        Frame = '';
                                        for(var i=0; i < _$(Obj).Sprite.Length; i++)
                                            Frame += (Frame === '')? i : ',' + i;
                                    }
                                }
                                var PlaySprite = {
                                    Access : Common.GenCode(10),
                                    Obj : this,
                                    Frame : Frame,
                                    Now : -1,
                                    Times : Var.Default(Config, 1,'Times'),
                                    CB : Var.Default(Config, false, 'Callback'),
                                    Stop : false
                                };
                                _.Attr({'Effect.PlaySprite':PlaySprite.Access}, this);

                                //Register Effect Job Function
                                if(Channel !== undefined)
                                    Task.Register(function(ID){Effect.PlaySprite(ID, PlaySprite);}, 'contition', Interval, Channel);
                                else
                                    Job.Work(function(ID){Effect.PlaySprite(ID, PlaySprite);}, 'contition', Interval, Channel);
                            }
                        });
                    },

                //Setup Element Fadein System
                Fade : function(Config, Channel){
                    _.Do(function(){
                        if(_.Attr('Effect.Fade', this) === null){
                            var Interval = Var.Default(Config, S.Effect.Interval, 'Interval'),
                            Mode = Var.Default(Config, S.Effect.Mode, 'Mode'),
                            Fade_Now = _.Opacity(undefined, this),
                            Fade_To = Var.Default(Config, Fade_Now < 100 ? 100 : 0, 'Target'),
                            Fade = {
                                Access : Common.GenCode(10),
                                Obj : this,
                                Mode : Var.Default(Config, S.Effect.Mode, 'Mode'),
                                Speed : Var.Default(Config, S.Effect.Speed, 'Speed'),
                                Rate : Var.Default(Config, S.Effect.Rate, 'Rate'),
                                Dir : (Fade_To > Fade_Now ? 1 : (Fade_To < Fade_Now ? -1 : 0)),
                                Now : Fade_Now,
                                To: Fade_To,
                                CB : Var.Default(Config, false, 'Callback'),
                                Stop : false
                            };
                            _.Attr({'Effect.Fade':Fade.Access}, this);

                            //Register Effect Job Function
                            if(Channel !== undefined)
                                Task.Register(function(ID){Effect.Fade(ID, Fade);}, 'contition', Interval, Channel);
                            else
                                Job.Work(function(ID){Effect.Fade(ID, Fade);}, 'contition', Interval);
                        }
                    });
                },

                //Setup Element Move System
                Move : function(Config, Channel){
                        _.Do(function(){
                            if(_.Attr('Effect.Move', this) === null){
                                var Interval = Var.Default(Config, S.Effect.Interval, 'Interval');
                                var Unit = Var.Default(Config, 'px', 'Unit');
                                if(Unit !== 'px'){
                                    var OLeft = Var.Float(this.style['left']);
                                    var OTop = Var.Float(this.style['top']);
                                }else{
                                    var OLeft = Var.Float(_.Style('left', this));
                                    var OTop = Var.Float(_.Style('top', this));
                                }
                                var TLeft = Var.Float(Var.Default(Config, OLeft, 'Left'));
                                var TTop = Var.Float(Var.Default(Config, OTop, 'Top'));
                                var Move = {
                                    Access : Common.GenCode(10),
                                    Obj : this,
                                    Mode : Var.Default(Config, S.Effect.Mode,'Mode'),
                                    Rate : Var.Default(Config, S.Effect.Rate, 'Rate'),
                                    Speed :{
                                        X : Var.Default(Config, S.Effect.Speed, 'SpeedX'),
                                        Y : Var.Default(Config, S.Effect.Speed, 'SpeedY')
                                    },
                                    Dir : {
                                        X : ((OLeft === TLeft) ? 0 : (OLeft > TLeft ? -1 : 1)),
                                        Y : ((OTop === TTop) ? 0 : (OTop > TTop ? -1 : 1))
                                    },
                                    Target : {
                                        Left : TLeft,
                                        Top : TTop
                                    },
                                    Unit : Unit,
                                    CB : Var.Default(Config, false, 'Callback'),
                                    Stop : false
                                };
                                _.Attr({'Effect.Move':Move.Access}, this);

                                //Register Effect Job Function
                                if(Channel !== undefined)
                                    Task.Register(function(ID){Effect.Move(ID, Move);}, 'contition', Interval, Channel);
                                else
                                    Job.Work(function(ID){Effect.Move(ID, Move);}, 'contition', Interval);
                            }
                        });
                },

                //Setup Element Move System
                Grow : function(Config, Channel){
                        _.Do(function(){
                            if(_.Attr('Effect.Grow', this) === null){
                                var Interval = Var.Default(Config, S.Effect.Interval, 'Interval');
                                var Unit = Var.Default(Config, 'px', 'Unit');
                                if(Unit !== 'px'){
                                    var Width = Var.Float(this.style['width']);
                                    var Height = Var.Float(this.style['height']);
                                }else{
                                    var Width = Var.Float(_.Style('width', this));
                                    var Height = Var.Float(_.Style('height', this));
                                }
                                var TWidth = Var.Default(Config, 0, 'Width');
                                var THeight = Var.Default(Config, 0, 'Height');
                                var Grow = {
                                    Access : Common.GenCode(10),
                                    Obj : this,
                                    Mode : Var.Default(Config, S.Effect.Mode, 'Mode'),
                                    Rate : Var.Default(Config, S.Effect.Rate, 'Rate'),
                                    Speed :{
                                        X : Var.Default(Config, S.Effect.Speed, 'SpeedX'),
                                        Y : Var.Default(Config, S.Effect.Speed, 'SpeedY')
                                    },
                                    Type : Var.Default(Config, 'bold', 'Type'),
                                    Overflow : Var.Default(_.Style(this, 'overflow'), 'auto'),
                                    Dir : {
                                        X : (Width > TWidth) ? -1 : 1,
                                        Y : (Height > THeight) ? -1 : 1
                                    },
                                    Now : {
                                        Width : Width,
                                        Height: Height
                                    },
                                    Target : {
                                        Width : TWidth,
                                        Height: THeight
                                    },
                                    Unit : Unit,
                                    CB : Var.Default(Config, false, 'Callback'),
                                    Stop : false
                                };
                                _.Attr({'Effect.Grow':Grow.Access}, this);

                                //Setup Intializing Style
                                _.Style({overflow:'hidden'}, this);

                                //Register Effect Job Function
                                if(Channel !== undefined)
                                    Task.Register(function(ID){Effect.Grow(ID, Grow);}, 'contition', Interval, Channel);
                                else
                                    Job.Work(function(ID){Effect.Grow(ID, Grow);}, 'contition', Interval);
                            }
                        });
                },

                //Clear the object effect
                Effecting : function(EffectName){
                    Return.Clear();
                    EffectName = Var.Default(EffectName,'');
                    _.Do(function(){
                        if(EffectName === ''){
                            if(this.getAttribute('Effect.BlinkColor', 2) !== null
                            || this.getAttribute('Effect.BlinkObj', 2) !== null
                            || this.getAttribute('Effect.ShakeObj', 2) !== null
                            || this.getAttribute('Effect.PlaySprite', 2) !== null
                            || this.getAttribute('Effect.Fade', 2) !== null
                            || this.getAttribute('Effect.Move', 2) !== null
                            || this.getAttribute('Effect.Grow', 2) !== null
                            || this.getAttribute('Effect.Highlight', 2) !== null){
                                var Working = true;
                            }else
                                var Working = false;
                            Return.Insert(this, Working);
                        }else{
                            if(this.getAttribute('Effect.' + EffectName, 2) !== null){
                                var Working = true;
                            }else
                                var Working = false;
                            Return.Insert(this, Working);
                        }
                    });
                    return Return.Value();
                },

                ClearEffect : function(EffectName){
                    if(EffectName === undefined || EffectName === '*')
                        _.DelAttr(['Effect.BlinkColor',
                        'Effect.BlinkObj','Effect.ShakeObj',
                        'Effect.PlaySprite','Effect.Fade',
                        'Effect.Move','Effect.Grow']);
                    else if(typeof(EffectName) === 'string' || (EffectName instanceof Array && EffectName.length > 0))
                        _.DelAttr(EffectName);
                },

                Sprite : {

                    //Get Object Display type
                    Init : function(Sprite_Sheet, Offset_Left, Offset_Top, Frame_Width, Frame_Height, Frame_Lenght, Frame_PerRow, Init_ID){
                        Offset_Left = Var.Float(Offset_Left) * -1;
                        Offset_Top = -Var.Float(Offset_Top) * -1;
                        Frame_Width = Var.Float(Frame_Width);
                        Frame_Height = Var.Float(Frame_Height);
                        Frame_Lenght = Var.Int(Frame_Lenght);
                        Frame_PerRow = Var.Int(Frame_PerRow);
                        Init_ID = Var.Int(Init_ID);
                        Sprite_Sheet = Common.ImageUrl(Sprite_Sheet);
                        if(!isNaN(Offset_Left) && !isNaN(Offset_Top) && !isNaN(Frame_Width) && !isNaN(Frame_Height) && !isNaN(Frame_Lenght) && !isNaN(Frame_PerRow)
                         && Frame_Width > 0 && Frame_Height > 0 && Frame_Lenght > 0 && Frame_PerRow){
                            _.Do(function(){
                                _.Style({
                                    width:Frame_Width+'px',
                                    height:Frame_Height+'px',
                                    backgroundImage: 'url(\'' + Sprite_Sheet + '\')'
                                }, this);
                                var Sprite = {
                                    Init : true,
                                    Len : Frame_Lenght,
                                    Row : Frame_PerRow,
                                    Now : 0,
                                    Offset : {
                                        Left : Offset_Left,
                                        Top : Offset_Top
                                    },
                                    Size : {
                                        Width : Frame_Width,
                                        Height : Frame_Height
                                    },
                                    Pos : {
                                        X:0,
                                        Y:0
                                    }
                                };
                                _.Attr({Sprite:Common.UrlEncode(Sprite)}, this);
                            });
                            Sprite.Frame(Init_ID);
                        }
                    },

                    IsInit : function(ObjSel){
                        Return.Clear();
                        _.Do(function(){
                            var Sprite = Common.UrlDecode(this.getAttribute('Sprite', 2), true);
                            Return.Insert(this, (typeof(Sprite) === 'object') ? Sprite : false);
                        }, ObjSel);
                        return Return.Value();
                    },

                    Frame : function(FrameID, ObjSel){
                        FrameID = Var.Default(FrameID, null);
                        var Sprite;
                        if(Var.IsNumber(FrameID)){
                            _.Do(function(){
                                if(Sprite = _.Sprite.IsInit()){
                                    if(Sprite.Len > FrameID){
                                        Sprite.Pos.X = -1 * Sprite.Size.Width * (FrameID % Sprite.Row);
                                        Sprite.Pos.Y = -1 * Sprite.Size.Height * ((FrameID - (FrameID % Sprite.Row)) / Sprite.Row);
                                        Sprite.Pos.X -= Sprite.Offset.Left;
                                        Sprite.Pos.Y -= Sprite.Offset.Top;
                                        Sprite.Now = FrameID;
                                        _.Attr('Sprite', Common.UrlEncode(Sprite), this);
                                        _.Style({backgroundPosition: Sprite.Pos.X.toString() + 'px ' + Sprite.Pos.Y.toString() + 'px'}, this);
                                    }
                                }
                            }, ObjSel);
                        }
                    },

                    Length : function(ObjSel){
                        Return.Clear();
                        _.Do(function(){
                            if(Sprite = _$(this).Sprite.IsInit())
                                Return.Insert(this, Sprite.Len);
                            else
                                Return.Insert(this, false);
                        }, ObjSel);
                        return Return.Value();
                    }
                },

                On : function(EType, Fn, Bubble, Times){
                    Bubble = Var.Default(Bubble, false);
                    Times = Var.Int(Times);
                    if(typeof(Fn) !== 'function')return;
                    var NType = EType, Continue = false;
                    switch(EType){
                        case 'click':if(/iPhone/i.test(navigator.userAgent) || /iPad/i.test(navigator.userAgent))EType = 'touchclick';break;
                        case 'mousewheel':if(/Firefox/i.test(navigator.userAgent))EType = "DOMMouseScroll";break;
                        case 'mouseenter':NType='mouseover';Continue = true;break;
                        case 'mouseleave':NType='mouseout';Continue = true;break;
                    }
                    _.Do(function(){
                        if(L.Event.hasOwnProperty(EType)){
                            if(typeof(Fn) === 'function')
                                _.Do(function(){
                                    L.Event[EType][L.Event[EType].length] = {
                                        Obj : this,
                                        Fn : Fn,
                                        Times : Times
                                    };
                                });
                            if(Continue === false)
                                return;
                        }

                        var EID = Common.GenCode(12),
                        Fnc = function(EventObj){Event.Trigger.apply(this, [EType, EventObj, Fn, EID, Times]);};
                        L.EvtObj[EID] = {Obj:this, Fn:Fn, Fnc:Fnc, EType:EType, Times:0};
                        if (window.addEventListener){
                            this.addEventListener(NType, Fnc, Bubble);
                        }else if (window.attachEvent){
                            this.attachEvent('on' + NType, Fnc);
                        }
                    });
                },

                Off : function(EType, Fn, EID){
                    var NType = EType;
                    switch(EType){
                        case 'click':if(/iPhone/i.test(navigator.userAgent) || /iPad/i.test(navigator.userAgent))EType = 'touchclick';break;
                        case 'mousewheel':if(/Firefox/i.test(navigator.userAgent))EType = "DOMMouseScroll";break;
                        case 'mouseenter':NType='mouseover';break;
                        case 'mouseleave':NType='mouseout';break;
                    }
                    if(L.Event.hasOwnProperty(NType)){
                        for(var i = 0; i<L.Event[NType].length; i++)
                            _.Do(function(){
                                if(L.Event[NType][i].Obj === this && L.Event[NType][i].Fn === Fn)
                                    L.Event[NType] = Var.Remove(L.Event[NType], i);
                            });
                    }else{
                        _.Do(function(){
                            if(L.EvtObj.hasOwnProperty(EID)){
                                var EvtObj = L.EvtObj[EID];
                                if(EvtObj.Obj === this && EvtObj.Fn === Fn && EvtObj.EType === EType){
                                   if (typeof(window.removeEventListener) !== 'undefined')
                                        this.removeEventListener(NType, EvtObj.Fnc);
                                   else if (typeof(window.detachEvent) !== 'undefined')
                                        this.detachEvent('on' + NType, EvtObj.Fnc);
                                   L.EvtObj = Var.Remove(L.EvtObj, EID);
                                   return true;
                                }
                            }else{
                                var SKeys = [];
                                    for(var Key in L.EvtObj) SKeys.push(Key);
                                SKeys.reverse();
                                for(var Key in SKeys){
                                    var EID = SKeys[Key], EvtObj = L.EvtObj[EID];
                                    if(EvtObj.Obj === this && EvtObj.Fn === Fn && EvtObj.EType === EType){
                                       if (window.removeEventListener)
                                            this.removeEventListener(NType, EvtObj.Fnc);
                                       else if (window.detachEvent)
                                            this.detachEvent('on' + NType, EvtObj.Fnc);
                                       L.EvtObj = Var.Remove(L.EvtObj, EID);
                                       return true;
                                    }
                                }
                            }
                        });
                    }
                },

                Rollover : function(Method, ROIn, ROOut, ROGroup, ROTarget, RORelated){
                    var RO = {Method:Method,In:ROIn,Out:ROOut,Sel:false,Grp:null,TObj:null,RObj:null};
                    if(ROGroup)
                        RO.Grp = ROGroup;
                    if(ROTarget)
                        RO.TObj = ROTarget;
                    if(RORelated)
                        RO.RObj = RORelated;
                    _.Attr({_RO:Common.UrlEncode(RO)});
                    _.Do(function(){Behavior.Roll.Action(this, RO, "OUT");});
                    _.MouseEnter(Behavior.Roll.Over)
                    _.MouseLeave(Behavior.Roll.Over);
                },

                Placeholder : function(Placetext, Placeclass){
                    if(typeof(Placetext) === 'string' && Placetext !== ''){
                        var PH = {'phlabel':Placetext};
                        if(typeof(Placeclass) === 'string' && Placeclass !== '')
                            PH['phclass'] = Placeclass;
                        _.Attr(PH);
                        _.Do(function(){
                            _$(this).On('focus', Behavior.Place.Focus);
                            _$(this).On('blur', Behavior.Place.Blur);
                        });
                    }
                },

                Focus : function(Fn, Bubble, Times){
                    Type = 'focus';
                    if(typeof(Fn) === 'function') _.On('focus', Fn, Bubble, Times); else _.Do(function(){if(this.focus)this.focus(); else this.onfocus();});
                },

                Blur : function(Fn, Bubble, Times){
                    Type = 'blur';
                    if(typeof(Fn) === 'function') _.On('blur', Fn, Bubble, Times);
                },

                FocusIn : function(Fn, Bubble, Times){
                    Type = 'focusin';
                    if(typeof(Fn) === 'function') _.On('focusin', Fn, Bubble, Times);
                },

                FocusOut : function(Fn, Bubble, Times){
                    Type = 'focusout';
                    if(typeof(Fn) === 'function') _.On('focusout', Fn, Bubble, Times);
                },

                Click : function(Fn, Bubble, Times){
                    Type = 'click';
                    if(typeof(Fn) === 'function') _.On('click', Fn, Bubble, Times); else _.Do(function(){if(this.click)this.click(); else this.onclick();});
                },

                DbClick : function(Fn, Bubble, Times){
                    Type = 'dbclick';
                    if(typeof(Fn) === 'function') _.On('dbclick', Fn, Bubble, Times);
                },

                Change : function(Fn, Bubble, Times){
                    Type = 'change';
                    if(typeof(Fn) === 'function') _.On('change', Fn, Bubble, Times);
                },

                Select : function(Fn, Bubble, Times){
                    Type = 'select';
                    if(typeof(Fn) === 'function')_.On('select', Fn, Bubble, Times);
                },

                KeyPress : function(Fn, Bubble, Times){
                    Type = 'keypress';
                    if(typeof(Fn) === 'function') _.On(Type, Fn, Bubble, Times);
                },

                KeyDown : function(Fn, Bubble, Times){
                    Type = 'keydown';
                    if(typeof(Fn) === 'function') _.On(Type, Fn, Bubble, Times);
                },

                KeyUp : function(Fn, Bubble, Times){
                    Type = 'keyup';
                    if(typeof(Fn) === 'function') _.On(Type, Fn, Bubble, Times);
                },

                MouseEnter : function(Fn, Bubble, Times){
                    if(typeof(Fn) === 'function') _.On('mouseenter', Fn, Bubble, Times);
                },

                MouseLeave : function(Fn, Bubble, Times){
                    if(typeof(Fn) === 'function') _.On('mouseleave', Fn, Bubble, Times);
                },

                MouseOver : function(Fn, Bubble, Times){
                    Type = 'mouseover';
                    if(typeof(Fn) === 'function') _.On(Type, Fn, Bubble, Times);
                },

                MouseOut : function(Fn, Bubble, Times){
                    Type = 'mouseout';
                    if(typeof(Fn) === 'function') _.On(Type, Fn, Bubble, Times);
                },

                MouseMove : function(Fn, Bubble, Times){
                    Type = 'mousemove';
                    if(typeof(Fn) === 'function') _.On(Type, Fn, Bubble, Times);
                },

                MouseDown : function(Fn, Bubble, Times){
                    Type = 'mouseover';
                    if(typeof(Fn) === 'function') _.On(Type, Fn, Bubble, Times);
                },

                MouseUp : function(Fn, Bubble, Times){
                    Type = 'mouseup';
                    if(typeof(Fn) === 'function') _.On(Type, Fn, Bubble, Times);
                },

                MouseWheel : function(Fn, Bubble, Times){
                    Type = 'mousewheel';
                    if(typeof(Fn) === 'function') _.On(Type, Fn, Bubble, Times);
                },

                ContextMenu : function(Fn, Bubble, Times){
                    Type = 'contextmenu';
                    if(typeof(Fn) === 'function') _.On(Type, Fn, Bubble, Times);
                },

                HOver : function(InFn, OutFn, Bubble, Times){
                    if(typeof(InFn) === 'function') _.On('mouseenter', InFn, Bubble, Times);
                    if(typeof(OutFn) === 'function') _.On('mouseleave', OutFn, Bubble, Times);
                },
            };

            _.ElmObjs = ElmSelector(ElmObj);
            return _;
        };
        return new $(Selector);
    };

    var WinObj = _$(window), Document = _$(document), MElm = document.getElementsByTagName('body')[0],
    CoreObj = { Var:Var, Common:Common, Data:Data, Keyboard:Keyboard, Ajax:Ajax, Fancy:Fancy, Menu:Menu,
    Validate:Validate, Window:Window, Job:Job, Task:Task, Event:Event, Behavior:Behavior, Ready:Ready, Document:Document};
    if(!MElm)
        MElm = document.getElementsByTagName('*')[0];
    if('opacity' in MElm.style)C.Opacity = 'opacity';
    else if('filter' in MElm.style)C.Opacity = 'filter';
    else if('MozOpacity' in MElm.style)C.Opacity = 'MozOpacity';
    else if('KhtmlOpacity' in MElm.style)C.Opacity = 'KhtmlOpacity';
    if ('user-select' in MElm.style)C.Selectable = 'user-select';
    else if ('-webkit-user-select' in MElm.style)C.Selectable = '-webkit-user-select';
    else if ('-khtml-user-select' in MElm.style)C.Selectable = '-khtml-user-select';
    else if ('-moz-user-select' in MElm.style)C.Selectable = '-moz-user-select';
    else if ('-o-user-select' in MElm.style)C.Selectable = '-o-user-select';
    else if ('user-select' in MElm.style)C.Selectable = 'user-select';

    for(var Key in CoreObj)
        _$[Key] = CoreObj[Key];

    //------------------------------------------------------------------------
    // Setup Custom Event
    //------------------------------------------------------------------------

    //Intial Custom Event
    Event.EvtCustom('mouseenter');
    Event.EvtCustom('mouseleave');
    Event.EvtCustom('touchclick');
    Event.EvtCustom('dragstart');
    Event.EvtCustom('drag');
    Event.EvtCustom('drop');

    ///* TouchClick Event
    Document.On('touchstart', function(){C.EObj.TouchClick = true;});
    Document.On('touchmove', function(){C.EObj.TouchClick = false;});
    Document.On('touchend', function(EventObj){
        if(!EventObj && window.event)EventObj = window.event;
        if(C.EObj.TouchClick){
            C.EObj.Click = 1;
            EventObj.type = 'touchclick';
            Event.Collector(EventObj);
            Event.Fire.apply(this, [C.EObj.Type, EventObj]);
            C.EObj.TouchClick = false;
        }
    });
    //TouchClick Event End */

    ///* Drag N Drop Event Start
    Document.On('mousedown', function(EventObj){
        if(!EventObj && window.event)EventObj = window.event;
        EventObj.type = 'dragstart';
        Event.Collector(EventObj);
        if(C.EObj.Click === 1)
            C.EObj.Mouse = 1;
        if(C.EObj.Target.tagName !== 'HTML' && C.EObj.Target.tagName !== 'BODY' && C.EObj.Mouse === 1){
            //Behavior.Drag.Start.apply(this, [C.EObj]);
            C.EObj.DragObj = C.EObj.Target;
            Event.Fire.apply(this, [C.EObj.Type, EventObj]);
        }
    });

    Document.On('mousemove', function(EventObj){
        if(!EventObj && window.event)EventObj = window.event;
        EventObj.type = 'drag';
        Event.Collector(EventObj);
        if(C.EObj.DragObj && C.EObj.Mouse === 1){
            C.EObj.Draging = true;
            //Behavior.Drag.Move.apply(this, [EObj]);
        }
        if(C.EObj.DragObj && C.EObj.Mouse === 1)
            Event.Fire.apply(C.EObj.DragObj, [C.EObj.Type, EventObj]);
    });

    Document.On('mouseup', function(EventObj){
        if(!EventObj && window.event)EventObj = window.event;
        EventObj.type = 'drop';
        Event.Collector(EventObj);
        /*if(C.EObj.Draging === true && C.EObj.Mouse === 1){
            if(C.EObj.DragObj)
                Behavior.Drag.Drop.apply(this, [C.EObj]);
        }*/
        if(C.EObj.Draging === true && C.EObj.Mouse === 1){
            C.EObj.DropObj = C.EObj.Target;
            Event.Fire.apply(this, [C.EObj.Type, EventObj]);
        }
        C.EObj = {};
    });
    //Drag N Drop Event End */

    //------------------------------------------------------------------------
    // Initializing and subscribe event.
    //------------------------------------------------------------------------
    Fancy.Init();
    Document.On('keydown', Keyboard.Pressed);
    Document.On('click', Behavior.Href.Click);
    WinObj.On('resize', Fancy.Reposition);
    WinObj.On('load', function(){setInterval(Job.Run, S.Interval);});
    Event.GetReady();

    window.onerror = function(){
        var Type = arguments[0].toString().split(/:/,1)[0],
        Stack = (arguments.hasOwnProperty(4) ? Var.LTrim(arguments[4].stack.split(/\n/,2)[1]) : '-'),
        ErrObj = {
            Type : Var.Default(Type, 'Error'),
            Message : Var.Default(arguments, '-', 0),
            Url : Var.Default(arguments, '-', 1),
            Line : Var.Default(arguments, '-', 2),
            Char : Var.Default(arguments, '-', 3),
            Stack : Var.Default(Stack, '-')
        };
        if(Common.BrowserType() === 'msie')
            Debug.Console('Type:'+ErrObj.Type+"\nMessage:"+ErrObj.Message+"\nUrl: "+ErrObj.Url+"\nLine: "+ErrObj.Line+"\nChar: "+ErrObj.Char+"\nStack: "+ErrObj.Stack);
        else
            Debug.Console(ErrObj);
        Event.PreventDefault(arguments[0]);
        return true;
    };

    //Export class object to window.
    window._$ = _$;

})(window);