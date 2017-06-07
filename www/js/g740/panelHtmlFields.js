//-----------------------------------------------------------------------------
// Панели HtmlFields
//
//<panel columns="1"> 
//<fields>
//<group caption="" isexpanded="0|1" isexpand="0|1">
//</group>
//<line caption="">
//</line>
//<field column="0">
//</fields>
//
//-----------------------------------------------------------------------------
define(
	[],
	function () {
	    if (typeof (g740) == 'undefined') g740 = {};
	    // Класс HtmlFields
	    dojo.declare(
			'g740.HtmlFields',
			[g740._PanelAbstract, dijit._TemplatedMixin],
			{
			    isG740Fields: true,
				fields: [],
			    _childs: [],
			    title: '',
			    xml: null,
			    templateString:
					'<div class="g740-html-fields" data-dojo-attach-point="domRoot">' +
					'</div>',
			    set: function (name, value) {
			        if (name == 'fields' && value) {
			            this.fields = [];
			            var objRowSet = this.getRowSet();
			            if (!objRowSet) return false;
			            if (!value) return false;
			            if (typeof (value) != 'object') return false;
			            if (!value.length) return false;
			            var rowsetFields = objRowSet.getFieldsByNodeType(this.nodeType);
			            for (var i = 0; i < value.length; i++) {
			                var fldNew = value[i];
			                if (!fldNew) continue;
			                var fieldName = fldNew.name;
			                var fldRowSet = rowsetFields[fieldName];
			                if (!fldRowSet && fldNew.type != 'button') continue;
			                var field = {};
			                if (fldRowSet) for (var paramName in fldRowSet) field[paramName] = fldRowSet[paramName];
			                for (var paramName in fldNew) field[paramName] = fldNew[paramName];
			                this.fields.push(field);
			            }
			            return true;
			        }
			        this.inherited(arguments);
			    },
			    constructor: function (para, domElement) {
			        var procedureName = 'g740.HtmlFields.constructor';
					this.fields = [];
					this._childs = [];
					this.set('objForm', para.objForm);
					this.set('rowsetName', para.rowsetName);
					if (para.nodeType) this.set('nodeType', para.nodeType);
					//this.set('fields', para.fields);
					this.set('xml', para.xml);
					this.on('Focus', this.onG740Focus);
			    },
			    destroy: function () {
			        var procedureName = 'g740.HtmlFields.destroy';
					if (this.fields) {
						for (var i = 0; i < this.fields.length; i++) this.fields[i] = null;
						this.fields = [];
					}
					if (this._childs) {
						for (var i = 0; i < this._childs.length; i++) {
							var obj = this._childs[i];
							if (!obj) continue;
							obj.destroyRecursive();
							this._childs[i] = null;
						}
						this._childs = [];
					}
					this.inherited(arguments);
			    },
			    postCreate: function () {
			        this.inherited(arguments);
			        this.render();
			    },
			    render: function () {
			        if (!this.domRoot) return false;
			        for (var i = 0; i < this._childs.length; i++) {
			            var obj = this._childs[i];
			            if (!obj) continue;
			            obj.destroyRecursive();
			            this._childs[i] = null;
			        }
			        this._childs = [];
			        this.renderRecursive(this.xml, this.domRoot, '');
			        dojo.parser.parse(this.domRoot);
			    },

			    renderRecursive: function (xml, root, roottype) {
			        var xmlNode = xml.firstChild;
			        while (xmlNode) {
			            switch (xmlNode.nodeType) {
			                case 1:
			                    var elem = null;
			                    if (xmlNode.nodeName == 'expander') {
			                        elem = this.renderExpander(xmlNode, root, roottype);
			                    }
                                else if (xmlNode.nodeName == 'grid') {
                                    elem = this.renderGroup(xmlNode, root, roottype);
                                    roottype = "grid";
                                }
			                    else if (xmlNode.nodeName == 'group') {
			                        elem = this.renderGroup(xmlNode, root, roottype);
			                        roottype = "group";
			                    }
			                    else if (xmlNode.nodeName == 'line') {
			                        elem = this.renderGroup(xmlNode, root, roottype);
			                        roottype = "line";
                                }
			                    else if (xmlNode.nodeName == 'field') {
		                            elem = this.renderField(xmlNode, root, roottype);
			                    }
			                    else {
			                        elem = g740.dom.append(xmlNode.nodeName, root);
			                        for (var j = 0; j < xmlNode.attributes.length; j++) {
			                            var a = xmlNode.attributes[j];
			                            if (a.nodeName && a.nodeValue) {
			                                elem.setAttribute(a.nodeName, a.nodeValue);
			                            }
			                        }
			                    }
			                    if (elem != null) {
			                        this.renderRecursive(xmlNode, elem, roottype);
			                    }
			                    break;
			                case 3:
			                    // если текст начинается с @ то, его значение будет вычеслено динамически
			                    var v = xmlNode.nodeValue;
			                    if (v.length > 0 && v.substring(0, 1) == '@' && root.nodeType == 1) {
			                        root.setAttribute("data-g740-innerhtml", 'get(\'' + v.substring(1) + '\')');
			                    }
			                    else {
			                        g740.dom.appendText(root, xmlNode.nodeValue);
			                    }
			                    break;
			            }

			            xmlNode = xmlNode.nextSibling;
			        }
			    },
			    renderExpander: function (xmlNode, root, roottype) {
			        var title = g740.xml.getAttrValue(xmlNode, "caption", "");
			        var isopen = g740.xml.getAttrValue(xmlNode, "isopen", "1");
			        var expander = g740.dom.append("div", root);
			        expander.setAttribute("data-dojo-type","dijit/TitlePane");
			        expander.setAttribute("data-dojo-props", "title: '" + title + "', open: " + (isopen == '1'));
                    return expander
			    },
			    renderGroup: function (xmlNode, root, roottype) {
			        var caption = g740.xml.getAttrValue(xmlNode, "caption", "");
			        if (caption) {
			            var dv = g740.dom.append("div", root);
			            g740.dom.appendText(dv, caption);
			        }
			        var group = g740.dom.append("div", root);
			        return group
			    },
			    renderLine: function (xmlNode, root, roottype) {
			        var caption = g740.xml.getAttrValue(xmlNode, "caption", "");
			        if (caption) {
			            var dv = g740.dom.append("div", root);
			            g740.dom.appendText(dv, caption);
			        }
			        var table = g740.dom.append("table", root);
			        var tr = g740.dom.append("tr", root);
			        return tr
			    },
			    renderGrid: function (xmlNode, root, roottype) {
			        var table = g740.dom.append("table", root);
			        return table
			    },
			    renderField: function (xmlNode, root, roottype) {
			        var field = g740.panels.buildFldDef(xmlNode);
			        var rowset = this.objForm.rowsets[this.rowsetName];
			        var rowsetFields = rowset.getFieldsByNodeType(this.nodeType);
			        var field1 = rowsetFields[field.name];
			        if ((field.name || field.type == 'button') && field.visible && (field1 || field.type == 'button')) {
			            if (!!field1) {
			                for (var pn in rowsetFields[field.name]) {
			                    if (!!!field[pn]) field[pn] = field1[pn];
			                }
			            }
			            this.fields.push(field);
			            var elem = null;
			            var p = {
			                objForm: this.objForm,
			                rowsetName: this.rowsetName,
			                fieldName: field.name,
			                fieldDef: field,
			                nodeType: this.nodeType
			            };

			            var cr = field.type == 'check' || field.type == 'radio' || field.captionright;
			            var cu = field.captionup;
			            if (roottype == "grid") {
			                var tr = g740.dom.append("tr", root);
			                var tv = null;
			                var tc = null;
			                if (!!!field.caption) {
			                    tv = g740.dom.append("td", tr);
			                    tr.setAttribute("colspan", "2");
                            }
			                else if (cu) {
			                    tv = g740.dom.append("td", tr);
			                    tc = tv;
			                }
			                else if (cr) {
			                    tv = g740.dom.append("td", tr);
			                    tc = g740.dom.append("td", tr);
                            }
			                else {
			                    tc = g740.dom.append("td", tr);
			                    tv = g740.dom.append("td", tr);
			                }
			                this.renderCaption(field, tc);
    	                    elem = this.renderSimpleField(field, p, tv);
			            }
			            else if (roottype == "line") {
			                var td = g740.dom.append('td', root);
			                if (cu) {
			                    this.renderCaption(field, td);
			                    elem = this.renderSimpleField(field, p, g740.dom.append("div", td));
			                }
			                else if (cr) {
			                    elem = this.renderSimpleField(field, p, g740.dom.append("div", td));
			                    this.renderCaption(field, td);
			                }
			                else {
			                    this.renderCaption(field, td);
			                    elem = this.renderSimpleField(field, p, g740.dom.append("div", td));
			                }
			            }
			            else {
			                if (cu) {
			                    var dv = g740.dom.append('div', root, { className: 'g740-html-simplepwner' });
			                    this.renderCaption(field, dv);
			                    elem = this.renderSimpleField(field, p, g740.dom.append("div", dv));
			                }
			                else if (cr) {
			                    var dv = g740.dom.append('div', root, { className: 'g740-html-simplepwner' });
			                    elem = this.renderSimpleField(field, p, g740.dom.append("div", dv));
			                    this.renderCaption(field, dv);
			                }
			                else {
			                    var dv = g740.dom.append('div', root, { className: 'g740-html-simplepwner' });
			                    this.renderCaption(field, dv);
			                    elem = this.renderSimpleField(field, p, g740.dom.append("div", dv));
			                }

			            }

                    }
			        elem = null;
			    },
			    renderValues: function (root, objRowSet) {
			        var elem = root.firstChild;
			        while (elem) {
			            switch (elem.nodeType) {
			                case 1:
			                    for (var j = 0; j < elem.attributes.length; j++) {
			                        var a = elem.attributes[j];
			                        if (a.nodeName.toLowerCase() == 'data-g740-innerhtml') {
			                            try {
			                                var v = g740.js_eval(objRowSet, a.nodeValue, '');
			                                    if (!v) v = '';
			                                    elem.innerHTML = v;
			                            }
			                            catch (e) {
			                            }

			                        }
			                    }
			                    this.renderValues(elem, objRowSet);
			                    break;
			            }
			            elem = elem.nextSibling;
			        }
                    
			    },
			    renderSimpleField: function (field, p, dv) {
			        var elem = null;
                    if (dv) {
                        elem = g740.panels.createObjField(field, p, dv);
                        if (elem) this._childs.push(elem);
			        }
			        return elem;
			    },
			    renderCaption: function (field, dv) {
			        if (dv && field.caption) {
			            var span = g740.dom.append("span", dv, {className: 'g740-html-label'});
			            g740.dom.appendText(span, field.caption);
                    }
			    },


			    focus: function () {
			        if (this.domNode) this.domNode.focus();
			    },
			    doG740Repaint: function (para) {
			        var objRowSet = this.getRowSet();
			        if (!objRowSet) return false;
			        if (!para) para = {};
			        if (para.objRowSet && para.objRowSet.name != this.rowsetName) return true;
                    /*
			        this.renderValues(this.domRoot, objRowSet);

			        var parent = this.getParent()
			        while (parent) {
			            if (parent.resize) {
			                parent.resize();
			                break;
			            }
			            parent = parent.getParent();
			        }
                    */
			        for (var i = 0; i < this._childs.length; i++) {
			            var obj = this._childs[i];
			            if (!obj) continue;
			            if (obj.doG740Repaint) obj.doG740Repaint();
                    }
			    },
			    onG740Focus: function () {
			        if (this.objForm) this.objForm.onG740ChangeFocusedPanel(this);
			        return true;
			    }
			}
		);
        /*
	    g740.panels._builderHtmlFieldsRecursive = function (xml, para, fields, rowsetFields) {
	        var l = g740.xml.findArrayOfChild(xml);
	        for (var i = 0; i < l.length; i++) {
	            var xmlNode = l[i];
	            switch (xmlNode.nodeName) {
	                case 'field':
	                    var field = g740.panels.buildFldDef(xmlNode);
	                    if ((field.name || field.type == 'button') && field.visible && (rowsetFields[field.name] || field.type == 'button')) {
	                        field.group = para.curGroup;
	                        field.line = para.curLine;
	                        if (para.nodeType) field.nodeType = para.nodeType;
	                        fields.push(field);
	                    }
	                    break;
	                case 'group':
	                    para.curGroup = g740.xml.objFromXml(xmlNode, { caption: '' });
	                    para.curLine = null;
	                    g740.panels._builderHtmlFieldsRecursive(xmlNode, para, fields, rowsetFields);
	                    para.curGroup = null;
	                    break;
	                case 'line':
	                    para.curLine = g740.xml.objFromXml(xmlNode, { caption: '' });
	                    g740.panels._builderHtmlFieldsRecursive(xmlNode, para, fields, rowsetFields);
	                    para.curLine = null;
	                    break;
	            }
	        }
	    };
        */

	    g740.panels._builderHtmlFields = function (xml, para) {
	        var procedureName = 'g740.panels._builderHtmlFields';
	        var result = null;
			if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
			if (xml.nodeName != 'panel') g740.systemError(procedureName, 'errorXmlNodeNotFound', xml.nodeName);
			if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
			if (!para.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'para.objForm');
			if (!para.rowsetName) {
				g740.trace.goBuilder({
					formName: para.objForm.name,
					panelType: 'htmlfields',
					messageId: 'errorRowSetNameEmpty'
				});
				return null;
			}

			var objRowSet = para.objForm.rowsets[para.rowsetName];

			if (!objRowSet) {
				g740.trace.goBuilder({
					formName: para.objForm.name,
					panelType: 'htmlfields',
					rowsetName: para.rowsetName,
					messageId: 'errorRowSetNotFoundInForm'
				});
				return null;
			}

			para.rowsetFields = objRowSet.getFieldsByNodeType(para.nodeType);
			para.xml = xml;
			var result = new g740.HtmlFields(para, null);
	        return result;
	    };
	    g740.panels.registrate('html', g740.panels._builderHtmlFields);
	}
);