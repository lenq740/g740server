/**
 * G740Viewer
 * Copyright 2017-2019 Galinsky Leonid lenq740@yandex.ru
 * Licensed under the BSD license
 */

define(
	[],
	function() {
		if (typeof(g740)=='undefined') g740={};
		
		g740.convertor={
// Преобразование из формата G740 в JavaScript
			toJavaScript: function(expr, t) {
				if (t=='date') return this._toJavaScriptDate(expr);
				if (t=='num') return this._toJavaScriptNum(expr);
				if (t=='check') return this._toJavaScriptCheck(expr);
				return this._toJavaScriptString(expr);
			},
// Преобразование из JavaScript в формат G740
			toG740: function(expr, t) {
				if (t=='date') return this._toG740Date(expr);
				if (t=='num') return this._toG740Num(expr);
				if (t=='check') return this._toG740Check(expr);
				return this._toG740String(expr);
			},
			// Преобразование из JavaScript в текстовое представление
			js2text: function(expr, t) {
				if (t=='date') return this._js2textDate(expr);
				if (t=='num') return this._js2textNum(expr);
				if (t=='check') return this._js2textCheck(expr);
				return this._js2textString(expr);
			},
			// Преобразование из текстового представления в JavaScript
			text2js: function(expr, t) {
				if (t=='date') return this._text2jsDate(expr);
				if (t=='num') return this._text2jsNum(expr);
				if (t=='check') return this._text2jsCheck(expr);
				return this._text2jsString(expr);
			},
// Преобразование строки из формата G740 в JavaScript
			_toJavaScriptString: function(expr) {
				return expr;
			},
//	Преобразование даты из формата G740 в JavaScript
			_toJavaScriptDate: function(expr) {
				if (expr=='') return null;
				var result=null;
				try {
					result=new Date(expr.substr(0,4), expr.substr(5,2)-1, expr.substr(8,2));
				}
				catch (e) {
					result=null;
				}
				return result;
			},
//	Преобразование числа из формата G740 в JavaScript
			_toJavaScriptNum: function(expr) {
				if (typeof(expr)=='string')	expr=expr.replace(',','.');
				var result=parseFloat(expr);
				if (isNaN(result)) result=0;
				return result;
			},
//	Преобразование логического значения из формата G740 в JavaScript
			_toJavaScriptCheck: function(expr) {
				var result=false;
				var t=typeof(expr);
				if (t=='string') {
					result=true;
					if (expr=='') result=false;
					if (expr=='0') result=false;
				}
				else if (t=='number' || t=='boolean') {
					result=(expr)?true:false;
				}
				return result;
			},
//	Преобразование строки из JavaScript в формат G740
			_toG740String: function(expr) {
				if (expr===null) return '';
				var t=typeof(expr);
				if (t=='string') return expr;
				if (t=='number') return expr.toString();
				if (t=='undefined') return '';
				if (t=='boolean') {
					if (expr) return '1';
					return '0';
				}
				if (t=='object') {
					if (expr.getFullYear && expr.getMonth && expr.getDate) {
						try {
							var yy=expr.getFullYear();
							var mm=expr.getMonth()+1;
							var dd=expr.getDate();
							yy=yy.toString();
							while (yy.length<4) yy='0'+yy;
							mm=mm.toString();
							while (mm.length<2) mm='0'+mm;
							dd=dd.toString();
							while (dd.length<2) dd='0'+dd;
							return yy+'-'+mm+'-'+dd;
						}
						catch (e) {
							return '';
						}
					}
					if (expr===true) return '1';
					if (expr===false) return '0';
					if (expr.toString) return expr.toString();
				}
				return '';
			},
//	Преобразование даты из JavaScript в формат G740
			_toG740Date: function(expr) {
				if (expr===null) return '';
				if (typeof(expr)=='object') {
					if (expr.getFullYear && expr.getMonth && expr.getDate) {
						try {
							var yy=expr.getFullYear();
							var mm=expr.getMonth()+1;
							var dd=expr.getDate();
							yy=yy.toString();
							while (yy.length<4) yy='0'+yy;
							mm=mm.toString();
							while (mm.length<2) mm='0'+mm;
							dd=dd.toString();
							while (dd.length<2) dd='0'+dd;
							return yy+'-'+mm+'-'+dd;
						}
						catch (e) {
							return '';
						}
					}
				}
				return '';
			},
//	Преобразование числа из JavaScript в формат G740
			_toG740Num: function(expr) {
				if (expr===null) return '0';
				var t=typeof(expr);
				if (t=='number') return expr.toString();
				if (t=='string') {
					expr=expr.replace(',','.');
					var val=parseFloat(expr);
					if (isNaN(val)) val=0;
					return val.toString();
				}
				return '0';
			},
//	Преобразование логического значения из JavaScript в формат G740
			_toG740Check: function(expr) {
				if (expr===true) return '1';
				if (expr==1) return '1';
				return '0';
			},
//	Преобразование строки из JavaScript в текст
			_js2textString: function(expr) {
				if (expr===null) return '';
				var t=typeof(expr);
				if (t=='string') return expr;
				if (t=='number') return expr.toString();
				if (t=='undefined') return '';
				if (t=='object') {
					if (expr.getFullYear && expr.getMonth && expr.getDate) {
						try {
							var yy=expr.getFullYear();
							var mm=expr.getMonth()+1;
							var dd=expr.getDate();
							yy=yy.toString();
							while (yy.length<4) yy='0'+yy;
							mm=mm.toString();
							while (mm.length<2) mm='0'+mm;
							dd=dd.toString();
							while (dd.length<2) dd='0'+dd;
							return dd+'.'+mm+'.'+yy;
						}
						catch (e) {
							return '';
						}
					}
					if (expr===true) return '1';
					if (expr===false) return '0';
					if (expr.toString) return expr.toString();
				}
				return '';
			},
//	Преобразование даты из JavaScript в текст
			_js2textDate: function(expr) {
				if (expr===null) return '';
				if (typeof(expr)=='object') {
					if (expr.getFullYear && expr.getMonth && expr.getDate) {
						try {
							var yy=expr.getFullYear();
							var mm=expr.getMonth()+1;
							var dd=expr.getDate();
							yy=yy.toString();
							while (yy.length<4) yy='0'+yy;
							mm=mm.toString();
							while (mm.length<2) mm='0'+mm;
							dd=dd.toString();
							while (dd.length<2) dd='0'+dd;
							return dd+'.'+mm+'.'+yy;
						}
						catch (e) {
							return '';
						}
					}
				}
				return '';
			},
//	Преобразование числа из JavaScript в текст
			_js2textNum: function(expr) {
				if (expr===null) return '0';
				var t=typeof(expr);
				if (t=='number') return expr.toString();
				if (t=='string') {
					expr=expr.replace(',','.');
					var val=parseFloat(expr);
					if (isNaN(val)) val=0;
					return val.toString();
				}
				return '0';
			},
//	Преобразование логического значения из JavaScript в текст
			_js2textCheck: function(expr) {
				if (expr===true) return '1';
				if (expr==1) return '1';
				return '0';
			},
//	Преобразование строки из текста в JavaScript
			_text2jsString: function(expr) {
				return expr;
			},
//	Преобразование даты из текста в JavaScript
			_text2jsDate: function(expr) {
				if (expr=='') return null;
				var result=null;
				try {
					expr=dojo.trim(expr);
					var pos=0;
					var dd=expr.substr(pos,2).toString();
					if (dd.length!=2) return null;
					pos+=2;
					var delim=expr.substr(pos,1);
					if (delim=='.' || delim=='-' || delim=='/') pos++;
					var mm=expr.substr(pos,2).toString();
					if (mm.length!=2) return null;
					pos+=2;
					var delim=expr.substr(pos,1);
					if (delim=='.' || delim=='-' || delim=='/') pos++;
					var yy=expr.substr(pos,4).toString();
					if (yy.length!=2 && yy.length!=4) return null;
					
					var ss=dd+mm+yy;
					for(var i=0; i<ss.length; i++) {
						var c=ss.substr(i,1);
						if (c<'0') return null;
						if (c>'9') return null;
					}
					
					if (yy.length==2) {
						yy=2000+parseInt(yy);
					}

					result=new Date(yy, mm-1, dd);
				}
				catch (e) {
					result=null;
				}
				return result;
			},
//	Преобразование числа из текста в JavaScript
			_text2jsNum: function(expr) {
				expr=expr.replace(',','.');
				var result=parseFloat(expr);
				if (isNaN(result)) result=0;
				return result;
			},
//	Преобразование логического значения из текста в JavaScript
			_text2jsCheck: function(expr) {
				var result=false;
				if (expr=='1') result=true;
				return result;
			},
			jscompare: function(value1, value2) {
				if ((value1===null) && (value2===null)) return true;
				if (typeof(value1))
				if (value1===null) return 
			}
		};
		return g740;
	}
);