/**
 * G740Viewer
 * Copyright 2017-2019 Galinsky Leonid lenq740@yandex.ru
 * Licensed under the BSD license
 */

define(
	[],
	function() {
		if (typeof(g740)=='undefined') g740={};
		
		var xml={
			_document: null,
// Создает и возвращает XML документ
			createDocument: function() {
				var result=dojox.xml.parser.parse('<?xml version="1.0" encoding="UTF-8"?><root></root>', 'text/xml');
				return result;
			},
// Возвращает XML документ
			getXmlDocument: function() {
				if (this._document==null) {
					this._document=this.createDocument();
				}
				return this._document;
			},
// Возвращает корневой узел XML документа
			getXmlRoot: function() {
				return this.getXmlDocument().documentElement;
			},
// Создать Xml узел
			createElement: function(name) {
				return this.getXmlDocument().createElement(name);
			},
// Создать Xml узел CDATASection
			createCDATASection: function(value) {
				return this.getXmlDocument().createCDATASection(value);
			},
// Создать Xml узел TextNode
			createTextNode: function(value) {
				return this.getXmlDocument().createTextNode(value);
			},
// Проверка - является ли параметр XML узлом
			isXmlNode: function(xmlNode) {
				if (xmlNode==null) return false;
				if (typeof(xmlNode)!='object') return false;
				if (xmlNode.nodeType==null) return false;
				if (xmlNode.nodeName==null) return false;
				return true;
			},
// Читатает значение атрибута XML узла, при возникновении проблем возвращает defValue
			getAttrValue: function(xmlNode, name, defValue)	{
				var result=defValue;
				if (!this.isXmlNode(xmlNode)) return result;
				if (xmlNode.attributes==null) return result;
				var a=xmlNode.attributes.getNamedItem(name);
				if (a!=null) {
					result=a.value;
				}
				return result;
			},
//	Проверяет наличие аттрибута
			isAttr: function(xmlNode, name) {
				if (!this.isXmlNode(xmlNode)) return false;
				if (xmlNode.attributes==null) return false;
				var a=xmlNode.attributes.getNamedItem(name);
				return a!=null;
			},
// Проверяет соответствие XML узла условию, заданному параметром Para
//	para.nodeName - имя узла
//	para.listOfAttr - ассоциативный массив имен и значений атрибутов
			_isTestByParaOk: function(xmlNode, para) {
				if (!this.isXmlNode(xmlNode)) return false;
				if (para==null) return false;
				if (typeof(para)!='object') return false;
				if ((para.nodeName!=null) && (xmlNode.nodeName!=para.nodeName)) return false;
				if (para.listOfAttr!=null) {
					for (var name in para.listOfAttr) {
						if (this.getAttrValue(xmlNode, name, '')!=para.listOfAttr[name]) return false;
					}
				}
				return true;
			},
// Возвращает массив дочерних узлов, удовлетворяющих условию
//	xmlSource - null, XML узел или массив XML узлов
//	para - условие (см _isTestByParaOk())
//	para.isFirstOnly - искать первое подходящее по условию
			findArrayOfChild: function(xmlSource, para) {
				var result=new Array();
				if (xmlSource==null) return result;
				if (para==null) para={};
				if (typeof(para)!='object') para={};
				var arrayOfSource=null;
				if ((arrayOfSource==null) && this.isXmlNode(xmlSource)) {
					arrayOfSource=new Array();
					arrayOfSource[0]=xmlSource;
				}
				if ((arrayOfSource==null) && (typeof(xmlSource)=='object') && (xmlSource.length!=null)) {
					arrayOfSource=new Array();
					var j=0;
					for (var i=0; i<xmlSource.length; i++) {
						var xml=xmlSource[i];
						if (this.isXmlNode(xml)) arrayOfSource[j++]=xml;
					}
				}
				if (arrayOfSource==null) arrayOfSource=new Array();
				for (var i=0; i<arrayOfSource.length; i++) {
					var xml=arrayOfSource[i].firstChild;
					while (xml!=null) {
						if (this._isTestByParaOk(xml,para)) {
							result[result.length]=xml;
							if (para.isFirstOnly) break;
						}
						xml=xml.nextSibling;
					}
					if (para.isFirstOnly && (result.length>0)) break;
				}
				return result;
			},
// Возвращает первый подходящий дочерний узел, удовлетворяющий условию, или null, если таких нет
//	xmlSource - null, XML узел или массив XML узлов
//	para - условие (см _IsTestByParaOk())
			findFirstOfChild: function(xmlSource, para) {
				var result=null;
				if (para==null) Para={};
				if (typeof(para)!='object') Para={};
				para.isFirstOnly=true;
				var arrayOfResult=this.findArrayOfChild(xmlSource, para);
				if (arrayOfResult.length>0) result=arrayOfResult[0];
				return result;
			},
// Возвращает символьное представление XML узла
			toStr: function(xmlNode) {
				return dojox.xml.parser.innerXML(xmlNode);
			}
		};
		g740.xml=xml;
		return xml;
	}
);