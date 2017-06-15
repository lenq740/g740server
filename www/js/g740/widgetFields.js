//-----------------------------------------------------------------------------
// Виджеты для использования в панелях
//-----------------------------------------------------------------------------
define(
	[],
	function() {
		if (typeof(g740)=='undefined') g740={};
		
		// Контейнер, содержащий поле и метку - название поля
		dojo.declare(
			'g740.FieldContainer',
			[dijit._Widget, dijit._TemplatedMixin],
			{
				g740className: 'g740.FieldContainer',	// Имя базового класса
				objFieldEditor: null,
				isLabelTop: false,
				label: '',
				isStretch: false,
				isLayoutContainer: true,
				left: 0,
				templateString: '<div class="g740-fieldcontainer">'+
					'<div class="g740-fieldcontainer-label" data-dojo-attach-point="domNodeLabel"></div>'+
					'<div class="g740-fieldcontainer-field" data-dojo-attach-point="domNodeField">'+
					'</div>'+
				'</div>',
				destroy: function() {
					if (this.domNodeField) {
						this.domNodeField.innerHTML='';
					}
					if (this.objFieldEditor) {
						this.objFieldEditor.destroyRecursive();
						this.objFieldEditor=null;
					}
					this.inherited(arguments);
				},
				set: function(name, value) {
					if (name=='objFieldEditor') {
						if (this.objFieldEditor==value) return true;
						if (value) {
							this.addChild(value);
						}
						else {
							this.removeChild(this.objFieldEditor);
						}
						return true;
					}
					if (name=='label') {
						this.label=value;
						if (this.label==' ') this.label=='';
						if (this.domNodeLabel) {
							this.domNodeLabel.innerHTML='';
							var domText=document.createTextNode(this.label);
							this.domNodeLabel.appendChild(domText);
						}
						return true;
					}
					if (name=='isLabelTop') {
						if (this.isLabelTop==value) return true;
						this.isLabelTop=value;
						return true;
					}
					if (name=='focused') {
						if (value) this.doG740Focus();
						return true;
					}
					if (name=='left') {
						if (this.left==value) return true;
						this.left=value;
						if (this.domNode) {
							dojo.style(this.domNode,'left',this.left+'px');
						}
					}
					this.inherited(arguments);
				},
				postCreate: function() {
					this.inherited(arguments);
					this.render();
				},
				render: function() {
					if (!this.domNode) return;
					if (this.label) {
						var label=this.label;
						this.label='';
						this.set('label',label);
					}
					if (this.objFieldEditor) {
						var objFieldEditor=this.objFieldEditor;
						this.objFieldEditor=null;
						this.set('objFieldEditor',objFieldEditor);
						if (this.objFieldEditor.domNode) {
							dojo.style(this.objFieldEditor.domNode, 'width', '100%');
						}
					}
				},
				addChild: function(obj) {
					if (obj && obj.g740className=='g740.FieldEditor') {
						this.objFieldEditor=obj;
						this.isStretch=false;
						if (this.objFieldEditor.fieldDef) this.isStretch=this.objFieldEditor.fieldDef.stretch;
						
						if (this.domNodeField && this.objFieldEditor.domNode) {
							this.domNodeField.innerHTML='';
							this.domNodeField.appendChild(this.objFieldEditor.domNode);
							dojo.style(this.domNodeField, 'width', this.objFieldEditor.getStyleWidth());
						}
						this._fieldWidthValue=0;
						this._fieldHeightValue=0;
					}
				},
				removeChild: function(obj) {
					if (obj && obj==this.objFieldEditor) {
						this.objFieldEditor=null;
						if (this.domNodeField) this.domNodeField.innerHTML='';
						this._fieldWidthValue=0;
						this._fieldHeightValue=0;
					}
				},
				getChildren: function() {
					var result=[];
					if (this.objFieldEditor) result=[this.objFieldEditor];
					return result;
				},
				doResize: function() {
					dojo.style(this.domNodeField, 'left', this.getFieldLeftPosition()+'px');

					dojo.style(this.domNodeField, 'width', this.getFieldWidth()+'px');
					dojo.style(this.domNodeField, 'height', this.getFieldHeight()+'px');
					dojo.style(this.domNodeField, 'top', this.getFieldTopPosition()+'px');
					dojo.style(this.domNodeField, 'left', this.getFieldLeftPosition()+'px');

					dojo.style(this.domNode, 'width', this.getWidth()+'px');
					dojo.style(this.domNode, 'height', this.getHeight()+'px');
					dojo.style(this.domNode,'left',this.left+'px');
					if (this.isLabelTop) {
					} else {
						dojo.style(this.domNodeLabel,'top','1px');
					}
				},
				
				_labelWidth: -1,
				getLabelWidth: function() {
					if (this._labelWidth<0) 
					if (!this.domNodeLabel.offsetParent) {
						this._labelWidth=g740.size.getLabelWidth(this.label);
					} else {
						this._labelWidth=this.domNodeLabel.offsetWidth;
					}
					return this._labelWidth;
				},
				getLabelHeight: function() {
					return 16;
				},
				
				_fieldWidth: 0,
				getFieldWidth: function() {
					if (!this._fieldWidth) {
						this._fieldWidth=0;
						if (this.objFieldEditor) {
							this._fieldWidth=parseInt(this.objFieldEditor.getWidth());
							if (this.isLabelTop && this._fieldWidth<this.getLabelWidth()) this._fieldWidth=this.getLabelWidth();
						}
					}
					return this._fieldWidth;
				},
				setFieldWidth: function(width) {
					if (this.objFieldEditor) this.objFieldEditor.setWidth(width);
					this._fieldWidth=0;
				},
				_fieldMinWidth: 0,
				getFieldMinWidth: function() {
					if (!this._fieldMinWidth) {
						this._fieldMinWidth=0;
						if (this.objFieldEditor) {
							this._fieldMinWidth=parseInt(this.objFieldEditor.getMinWidth());
							if (this.isLabelTop && this._fieldMinWidth<this.getLabelWidth()) this._fieldMinWidth=this.getLabelWidth();
						}
					}
					return this._fieldMinWidth;
				},
				_fieldHeightValue: 0,
				getFieldHeight: function() {
					if (this._fieldHeightValue) return this._fieldHeightValue;
					var result=20;
					if (this.objFieldEditor) {
						result=parseInt(this.objFieldEditor.getHeight());
						this._fieldHeightValue=result;
					}
					return result;
				},
				getFieldLeftPosition: function() {
					var result=0;
					if (!this.isLabelTop) result=this.getLabelWidth()+3;
					return result;
				},
				getFieldTopPosition: function() {
					var result=0;
					if (this.isLabelTop) result=this.getLabelHeight();
					return result;
				},
				getWidth: function() {
					var result=0;
					if (this.isLabelTop) {
						var result=this.getFieldWidth();
						var labelWidth=this.getLabelWidth();
						if (result<labelWidth) result=labelWidth;
					}
					else {
						result=this.getFieldLeftPosition()+this.getFieldWidth();
					}
					return result;
				},
				getHeight: function() {
					var labelHeight=this.getLabelHeight();
					var result=this.getFieldTopPosition()+this.getFieldHeight();
					if (result<labelHeight) result=labelHeight;
					return result;
				},
				getMinWidth: function() {
					var result=0;
					if (this.isLabelTop) {
						var result=this.getFieldMinWidth();
						var labelWidth=this.getLabelWidth();
						if (result<labelWidth) result=labelWidth;
					}
					else {
						result=this.getFieldLeftPosition()+this.getFieldMinWidth();
					}
					return result;
				},
				
				getVisible: function() {
					if (this.objFieldEditor) return this.objFieldEditor.getVisible();
					return false;
				},
				getReadOnly: function() {
					if (this.objFieldEditor) return this.objFieldEditor.getReadOnly();
					return true;
				},
				getRowSet: function() {
					if (this.objFieldEditor) return this.objFieldEditor.getRowSet();
					return null;
				},
				doG740Repaint: function() {
					if (this.objFieldEditor) this.objFieldEditor.doG740Repaint();
				},
				doG740Focus: function() {
					if (this.objFieldEditor) this.objFieldEditor.doG740Focus();
				}
			}
		);
		
		// Абстрактный предок всех полей
		dojo.declare(
			'g740.FieldEditor',
			null,
			{
				g740className: 'g740.FieldEditor',	// Имя базового класса
				objForm: null,
				objRowSet: null,
				objPanel: null,
				objActionGo: null,
				rowsetName: null,
				fieldName: null,
				nodeType: '',
				fieldDef: {},
				color: '',
				rowId: null,
				emptyValue: '',
				isSaveOnChange: false,
				isShowNullAsEmptyString: true,
				set: function(name, value) {
					if (name=='objForm') {
						this.objForm=value;
						this.objRowSet=this.getRowSet();
						return true;
					}
					if (name=='rowsetName') {
						this.rowsetName=value;
						this.objRowSet=this.getRowSet();
						return true;
					}
					if (name=='fieldName') {
						this.fieldName=value;
						return true;
					}
					if (name=='nodeType') {
						this.nodeType=value;
						return true;
					}
					if (name=='fieldDef') {
						this.fieldDef=value;
						return true;
					}
					if (name=='rowId') {
						this.rowId=value;
						return true;
					}
					if (name=='isSaveOnChange') {
						this.isSaveOnChange=value;
						return true;
					}
					if (name=='color') {
						this.color=value;
						return true;
					}
					if (name=='focused') {
						if (value) this.doG740Focus();
						return true;
					}
					this.inherited(arguments);
				},
				preamble: function(para, domElement) {
					var procedureName='g740.FieldEditor.preamble';
					try {
						var p=para.constraints;
						if (p) for(var name in p) if (!para[name]) para[name]=p[name];
						this.inherited(arguments);
					}
					finally {
					}
				},
				constructor: function(para, domElement) {
					var procedureName='g740.FieldEditor.constructor';
					try {
						this._repaint={
							isRepaint: false,
							value: ''
						};
					}
					finally {
					}
				},
				destroy: function() {
					var procedureName='g740.FieldEditor.destroy';
					try {
						this.objForm=null;
						this.objRowSet=null;
						this.fieldDef=null;
						this._repaint=null;
						if (this.objActionGo) {
							this.objActionGo.destroy();
							this.objActionGo=null;
						}
						this.objPanel=null;
						this.inherited(arguments);
					}
					finally {
					}
				},
				postCreate: function() {
					this.on('Change',this.onG740Change);
					this.on('Focus',this.onG740Focus);
					this.on('Blur',this.onG740Blur);
					this.on('KeyPress',this.onG740KeyPress);
					this.setWidth(this.getMinWidth());
					this.inherited(arguments);
				},
				getActionGo: function() {
					if (!this.objActionGo && this.fieldDef && this.fieldDef.on && this.fieldDef.on.dblclick) {
						var p={
							objForm: this.objForm,
							rowsetName: this.rowsetName,
							request: this.fieldDef.on.dblclick
						};
						this.objActionGo=new g740.Action(p);
					}
					return this.objActionGo;
				},
				getRowSet: function() {
					var procedureName='g740.FieldEditor.getRowSet';
					try {
						if (!this.objForm) return null;
						if (this.objForm.isObjectDestroed) return null;
						if(!this.rowsetName) return null;
						var objRowSet=this.objForm.rowsets[this.rowsetName];
						if (!objRowSet) return null;
						if (objRowSet.isObjectDestroed) return null;
					}
					finally {
					}
					return objRowSet;
				},
				getReadOnly: function() {
					if (!this.objRowSet) return true;
					if (this.fieldDef.readonly) return true;
					var rowId=this.rowId;
					if (rowId===null) rowId=this.objRowSet.getFocusedId();
					return this.objRowSet.getFieldProperty({fieldName: this.fieldName, propertyName: 'readonly', id: rowId});
				},
				getVisible: function() {
					if (!this.objRowSet) return false;
					if (this.fieldDef.visible===false) return false;
					return this.objRowSet.getFieldProperty({fieldName: this.fieldName, propertyName: 'visible'});
				},
				_getButtonWidth: function() {
					return 0;
				},
				
				getMinWidth: function() {
					var result=10;
					if (this.fieldDef.len) {
						result=parseInt(this.fieldDef.len*g740.config.charwidth+this._getButtonWidth()+5);
					}
					return result;
				},
				getHeight: function() {
					var result=18;
					return result;
				},

				_width: 0,
				getWidth: function() {
					if (!this._width) this._width=this.getMinWidth();
					return this._width;
				},
				setWidth: function(width) {
					if (width<this.getMinWidth()) width=this.getMinWidth();
					this._width=width;
					if (this.domNode) dojo.style(this.domNode, 'width', this.getStyleWidth());
				},
				
				getStyleWidth: function() {
					var result=this.getWidth()+'px';
					var isStretch=false;
					if (this.fieldDef.stretch) isStretch=true;
					if (isStretch) result='100%';
					return result;
				},
				// Блокируем обратную отписку изменений в RowSet из перерисовки
				_repaint: {
					isRepaint: false,
					value: ''
				},
				doG740Repaint: function() {
					var procedureName='g740.FieldEditor.doG740Repaint';
					if (!this.objRowSet) return false;
					var rowId=this.rowId;
					if (rowId===null) rowId=this.objRowSet.getFocusedId();

					var value=this.objRowSet.getFieldProperty({fieldName: this.fieldName, propertyName: 'value', id: rowId});
					var readOnly=this.getReadOnly();
					
					if (this.objRowSet.isTree && this.nodeType) {
						var nodeType='';
						var node=this.objRowSet.objTreeStorage.getNode(rowId,this.objRowSet.getFocusedParentNode());
						if (node) nodeType=node.nodeType;
						if (this.nodeType!=nodeType) {
							value=this.emptyValue;
							readOnly=true;
						}
					}

					this._repaint.isRepaint=true;
					if (this.isShowNullAsEmptyString) {
						if (value==null) value='';
					}
					this._repaint.value=value;

					var textValue=this.convertFromValueToTextValue(value);
					this.set('value',textValue);
					this.set('readOnly',readOnly);
					return true;
				},
				onG740Change: function(newTextValue) {
					try {
						if (!this.objRowSet) return false;
						var newValue=this.convertFromTextValueToValue(newTextValue);

						// Если onchange вызван из doG740Repaint, то отписывать обратно в RowSet не надо
						if (this._repaint.isRepaint) {
							var fields=this.objRowSet.getFieldsByNodeType(this.nodeType);
							var fld=fields[this.fieldName];
							if (!fld) return false;
							if (g740.convertor.toG740(newValue,fld.type)==g740.convertor.toG740(this._repaint.value,fld.type)) {
								this._repaint.isRepaint=false;
								this._repaint.value='';
								return true;
							}
						}
						var rowId=this.rowId;
						if (rowId===null) rowId=this.objRowSet.getFocusedId();

						// Если в дереве не то nodeType, то отписывать не надо
						if (this.objRowSet.isTree && this.nodeType) {
							var nodeType='';
							var node=this.objRowSet.objTreeStorage.getNode(rowId,this.objRowSet.getFocusedParentNode());
							if (node) nodeType=node.nodeType;
							if (this.nodeType!=nodeType) return false;
						}
						
						this.objRowSet.setFieldProperty({fieldName: this.fieldName, propertyName: 'value', value: newValue, id: rowId});
						if (this.isSaveOnChange && this.objRowSet.getExistUnsavedChanges()) {
							this.objRowSet.exec({requestName: 'save'});
						}
					}
					finally {
					}
				},
				
				convertFromValueToTextValue: function(value) {
					return value;
				},
				convertFromTextValueToValue: function(text) {
					return text;
				},
				
				onG740Focus: function() {
					if (this.rowId && this.objRowSet && this.rowId!=this.objRowSet.getFocusedId()) this.objRowSet.setFocusedId(this.rowId);
				},
				onG740Blur: function() {
				},
				onG740KeyPress: function(e) {
				},
				doG740Focus: function() {
					var node=this.domNode;
					if (this.focusNode) node=this.focusNode;
					if (node.focus) node.focus();
				},
				_isWaitClick: false,
				_setWaitClickToFalse: function() {
					this._isWaitClick=false;
				}
			}
		);
		
	    // Виджет для поля даты
		dojo.declare(
			'g740.FieldEditor.Date',
			[dijit.form.DateTextBox, g740.FieldEditor],
			{
				isShowNullAsEmptyString: false,
				constructor: function(para, domElement) {
					var procedureName='g740.FieldEditor.Date.constructor';
					try {
						this.set('datePattern','dd.MM.yyyy');
						this.set('selector','date');
						this.emptyValue=null;
						this.inherited(arguments);
					}
					finally {
					}
				},

				postCreate: function() {
					this.inherited(arguments);
				},
				on: function(name, func) {
					if (name=='Focus') {
						if (this.focusNode) dojo.on(this.focusNode, 'focus', dojo.hitch(this, func));
						return true;
					}
					if (name=='Blur') {
						if (this.focusNode) dojo.on(this.focusNode, 'blur', dojo.hitch(this, func));
						return true;
					}
					return this.inherited(arguments);
				},
				_onKey: function(e) {
					if (e.keyCode==13 && e.ctrlKey) {
						// Ctrl+Enter
						dojo.stopEvent(e);
						if (!this.getReadOnly()) this.openDropDown();
					}
					else if (this._opened && e.keyCode == 27) {
						this.closeDropDown();
					}
					else if (!e.ctrlKey && (e.keyCode==13 || (e.keyCode==9 && !e.shiftKey))) {
						// Enter, Tab
						dojo.stopEvent(e);
						if (this.textbox) this.set('displayedValue',this.textbox.value);
						if (this.objPanel) this.objPanel.doG740FocusChildNext(this);
					}
					else if (!e.ctrlKey && (e.keyCode==9 && e.shiftKey)) {
						// Shift+Tab
						dojo.stopEvent(e);
						if (this.textbox) this.set('displayedValue',this.textbox.value);
						if (this.objPanel) this.objPanel.doG740FocusChildPrev(this);
					}
				},
				getMinWidth: function() {
					result=parseInt(9*g740.config.charwidth+this._getButtonWidth());
					return result;
				},
				setWidth: function(width) {
					this._width=this.getMinWidth();
					if (this.domNode) dojo.style(this.domNode, 'width', this.getStyleWidth());
				},
				_getButtonWidth: function() {
					return 15;
				},
				openDropDown: function() {
					var objActionGo=this.getActionGo();
					if (objActionGo) {
						// предотвращаем дребезг
						if (this._isWaitClick) return false;
						this._isWaitClick=true;
						if (objActionGo && objActionGo.getEnabled()) objActionGo.exec();
						g740.execDelay.go({
							delay: 250,
							obj: this,
							func: this._setWaitClickToFalse
						});
						return false;
					}
					this.inherited(arguments);
				},
				onG740Focus: function() {
					if (this.domNode && !dojo.hasClass(this.domNode,'g740-widget-focused')) dojo.addClass(this.domNode,'g740-widget-focused');
					this.inherited(arguments);
				},
				onG740Blur: function() {
					if (this.domNode && dojo.hasClass(this.domNode,'g740-widget-focused')) dojo.removeClass(this.domNode,'g740-widget-focused');
					this.inherited(arguments);
				},
			}
		);
		// Виджет для поля строки
		dojo.declare(
			'g740.FieldEditor.String',
			[g740.TextBox, g740.FieldEditor],
			{
				constructor: function(para, domElement) {
					var procedureName='g740.FieldEditor.String.constructor';
					try {
						//this.inherited(arguments);
					}
					finally {
					}
				},
				postCreate: function() {
					if (this.fieldDef) {
						if (this.fieldDef.on && this.fieldDef.on.dblclick) this.set('buttonVisible',true);
						if (this.fieldDef.type=='num') dojo.addClass(this.domNode,'g740-widget-num');
					}
					this.on('ButtonClick',this.onG740ButtonClick);
					this.on('Blur',this.onG740Blur);
					this.inherited(arguments);
				},
				convertFromValueToTextValue: function(value) {
					var result=value;
					if (this.fieldDef && this.fieldDef.type=='num' && this.fieldDef.dec) {
						var result=parseFloat(result);
						if (isNaN(result)) result=0;
						result=result.toFixed(this.fieldDef.dec);
					}
					return result;
				},
				onG740Blur: function() {
					if (this.domNodeInput) this.onG740Change(this.domNodeInput.value);
				},
				onKeyDown: function(e) {
					if (e.keyCode==13 && e.ctrlKey) {
						// Ctrl+Enter
						dojo.stopEvent(e);
						this.onG740ButtonClick();
					}
					else if (!e.ctrlKey && (e.keyCode==13 || (e.keyCode==9 && !e.shiftKey))) {
						// Enter, Tab
						dojo.stopEvent(e);
						if (this.domNodeInput) this.onG740Change(this.domNodeInput.value);
						if (this.objPanel) this.objPanel.doG740FocusChildNext(this);
					}
					else if (!e.ctrlKey && (e.keyCode==9 && e.shiftKey)) {
						// Shift+Tab
						dojo.stopEvent(e);
						if (this.domNodeInput) this.onG740Change(this.domNodeInput.value);
						if (this.objPanel) this.objPanel.doG740FocusChildPrev(this);
					}
				},
				onG740ButtonClick: function() {
					if (this.getReadOnly()) return false;
					if (this.objRowSet && this.rowId!==null) {
						if (this.objRowSet.getFocusedId()!=this.rowId) {
							if (!this.objRowSet.setFocusedId(this.rowId)) return false;
						}
					}
					// предотвращаем дребезг
					if (this._isWaitClick) return false;
					this._isWaitClick=true;
					this.domNodeInput.focus();
					var objActionGo=this.getActionGo();
					if (objActionGo && objActionGo.getEnabled()) objActionGo.exec();
					g740.execDelay.go({
						delay: 250,
						obj: this,
						func: this._setWaitClickToFalse
					});
				}
			}
		);
		// Виджет для поля многострочного редактора
		dojo.declare(
			'g740.FieldEditor.Memo',
			[g740.Memo, g740.FieldEditor],
			{
				constructor: function(para, domElement) {
					var procedureName='g740.FieldEditor.Memo.constructor';
					try {
						this.inherited(arguments);
					}
					finally {
					}
				},
				postCreate: function() {
					dojo.style(this.domNodeTextArea, 'height', this.getHeight()+'px');
					this.on('ButtonClick',this.onG740ButtonClick);
					this.on('Blur',this.onG740Blur);
					this.inherited(arguments);
				},
				onG740Blur: function() {
					if (this.domNodeTextArea) this.onG740Change(this.domNodeTextArea.value);
				},
				onG740ButtonClick: function() {
					if (this.objRowSet && this.rowId!==null) {
						if (this.objRowSet.getFocusedId()!=this.rowId) {
							if (!this.objRowSet.setFocusedId(this.rowId)) return false;
						}
					}
					// предотвращаем дребезг
					if (this._isWaitClick) return false;
					this._isWaitClick=true;
					this.domNodeTextArea.focus();
					var objActionGo=this.getActionGo();
					if (objActionGo) {
						if (!this.getReadOnly() && objActionGo.getEnabled()) objActionGo.exec();
					}
					else {
						var objDialog=new g740.DialogEditorMemo(
							{ 
								objForm: this.objForm,
								rowsetName: this.rowsetName,
								fieldName: this.fieldName,
								nodeType: this.nodeType,
								domNodeOwner: this.domNode,
								readOnly: this.getReadOnly(),
								objOwner: this,
								duration: 0, 
								draggable: false
							}
						);
						objDialog.show();
					}
					g740.execDelay.go({
						delay: 250,
						obj: this,
						func: this._setWaitClickToFalse
					});
				},
				onG740KeyPress: function(e) {
					if (e.keyCode==13 && e.ctrlKey) {
						// Ctrl+Enter
						dojo.stopEvent(e);
						this.onG740ButtonClick();
					}
					else if (!e.ctrlKey && (e.keyCode==13 || (e.keyCode==9 && !e.shiftKey))) {
						// Enter, Tab
						if (this.fieldDef.enter && e.keyCode==13) {
						}
						else {
							dojo.stopEvent(e);
							if (this.domNodeTextArea) this.onG740Change(this.domNodeTextArea.value);
							if (this.objPanel) this.objPanel.doG740FocusChildNext(this);
						}
					}
					else if (!e.ctrlKey && (e.keyCode==9 && e.shiftKey)) {
						// Shift+Tab
						dojo.stopEvent(e);
						if (this.domNodeTextArea) this.onG740Change(this.domNodeTextArea.value);
						if (this.objPanel) this.objPanel.doG740FocusChildPrev(this);
					}
				},
				getHeight: function() {
					var rows=4;
					if (this.fieldDef.rows) rows=this.fieldDef.rows;
					var result=g740.config.charheight*rows;
					return result;
				}
			}
		);
		// Виджет для поля CheckBox
		dojo.declare(
			'g740.FieldEditor.Icons',
			[dijit._Widget, dijit._TemplatedMixin, g740.FieldEditor],
			{
				value: 0,
				icons: null,
				templateString: '<div class="g740-widget-icons" data-dojo-attach-event="onclick: onBodyClick">'+
					'<input type="checkbox" class="g740-focused" data-dojo-attach-point="focusNode" data-dojo-attach-event="'+
						'onkeypress: onKeyPress, onfocus: _onWidgetFocus, onblur: _onWidgetBlur'+
					'"></input>'+
					'<div class="g740-widget-icons-icon" data-dojo-attach-point="domNodeIcon" data-dojo-attach-event="onclick: doClick"></div>'+
					'<div class="g740-widget-icons-label" data-dojo-attach-point="domNodeLabel"></div>'+
				'</div>',
				constructor: function(para) {
				},
				set: function(name, value) {
					if (name=='value') {
						this.setValue(value);
						return true;
					}
					if (name=='readOnly') {
						if (value) {
							dojo.addClass(this.domNode, 'g740-readonly');
						}
						else {
							dojo.removeClass(this.domNode, 'g740-readonly');
						}
						return true;
					}
					this.inherited(arguments);
				},
				getMinWidth: function() {
					var result=18;
					var caption='';
					if (this.fieldDef) {
						caption=this.fieldDef.name;
						if (this.fieldDef.caption) caption=this.fieldDef.caption;
					}
					if (caption) {
						result+=8+parseInt(caption.length*g740.config.charlabelwidth);
					}
					return result;
				},
				postCreate: function() {
					this.inherited(arguments);
					if (this.fieldDef) {
						var caption=this.fieldDef.name;
						if (this.fieldDef.caption) caption=this.fieldDef.caption;
						this.setLabel(caption);
						if (!this.icons) {
							var list='';
							if (this.fieldDef.list) list=this.fieldDef.list;
							this.icons=list.split(';')
						}
					}
					if (!this.icons) this.icons=[];
				},
				setLabel: function(value) {
					if (this.domNodeLabel) {
						this.domNodeLabel.innerHTML='';
						var domText=document.createTextNode(value);
						this.domNodeLabel.appendChild(domText);
					}
				},
				getBaseType: function() {
					var result='string';
					if (this.fieldDef && this.fieldDef.basetype=='num') result='num';
					return result;
				},
				setValue: function(value) {
					if (!this.icons) return false;
					var baseType=this.getBaseType();
					if (baseType=='num') {
						if (value>=this.icons.length) value=this.icons.length-1;
						if (value<0) value=0;
						this.value=value;
						if (this.domNodeIcon) this.domNodeIcon.className='g740-widget-icons-icon '+g740.icons.getIconClassName(this.icons[value]);
					}
					if (baseType=='string') {
						this.value=value;
						if (this.domNodeIcon) this.domNodeIcon.className='g740-widget-icons-icon '+g740.icons.getIconClassName(value);
					}
				},
				convertFromValueToTextValue: function(value) {
					var baseType=this.getBaseType();
					if (baseType=='num') {
						if (typeof(value)=='boolean') value=value?1:0;
					}
					return value;
				},
				doClick: function() {
					if (!this.icons) return false;
					if (this.getReadOnly()) return false;
					var baseType=this.getBaseType();
					if (baseType=='num') {
						var value=this.value;
						value++;
						if (value>=this.icons.length) value=0;
						this.onG740Change(value);
					}
					if (baseType=='string') {
						var index=-1;
						for(var i=0; i<this.icons.length; i++) {
							if (this.icons[i]==this.value) {
								index=i;
								break;
							}
						}
						index++;
						if (index>=this.icons.length) index=0;
						this.onG740Change(this.icons[index]);
					}
				},
				onBodyClick: function() {
					this.set('focused',true);
				},
				_onWidgetFocus: function() {
					if (!dojo.hasClass(this.domNode, 'g740-widget-focused')) dojo.addClass(this.domNode, 'g740-widget-focused');
				},
				_onWidgetBlur: function() {
					if (dojo.hasClass(this.domNode, 'g740-widget-focused')) dojo.removeClass(this.domNode, 'g740-widget-focused');
				},
				onKeyPress: function(e) {
					if (!e.ctrlKey && (e.keyCode==13 || (e.keyCode==9 && !e.shiftKey))) {
						// Enter, Tab
						dojo.stopEvent(e);
						if (this.objPanel) this.objPanel.doG740FocusChildNext(this);
					}
					else if (!e.ctrlKey && (e.keyCode==9 && e.shiftKey)) {
						// Shift+Tab
						dojo.stopEvent(e);
						if (this.objPanel) this.objPanel.doG740FocusChildPrev(this);
					}
					else if (!e.ctrlKey && !e.shiftKey && e.charCode==32) {
						dojo.stopEvent(e);
						this.doClick();
					}
					else {
						dojo.stopEvent(e);
					}
				}
			}
		);
		dojo.declare(
			'g740.FieldEditor.Check',
			[g740.FieldEditor.Icons],
			{
				constructor: function(para) {
					this.icons=['check-off', 'check-on'];
				},
				getBaseType: function() {
					return 'num';
				},
				setValue: function(value) {
					if (!this.icons) return false;
					var baseType=this.getBaseType();
					if (baseType=='num') {
						if (value>=this.icons.length) value=this.icons.length-1;
						if (value<0) value=0;
						this.value=value;
						if (this.domNodeIcon) this.domNodeIcon.className='g740-widget-icons-icon '+this.icons[value];
					}
					if (baseType=='string') {
						this.value=value;
						if (this.domNodeIcon) this.domNodeIcon.className='g740-widget-icons-icon '+value;
					}
				},
				getHeight: function() {
					var result=16;
					return result;
				},
			}
		);
		
	    // Виджет для поля List
		dojo.declare(
			'g740.FieldEditor.List',
			[g740.ComboBox, g740.FieldEditor],
			{
				postCreate: function() {
					this.inherited(arguments);
				},
				onG740Change: function(newValue) {
					return false;
				},
				_list: null,
				convertFromValueToTextValue: function(value) {
					var baseType='string';
					if (this.fieldDef && this.fieldDef.basetype=='num') baseType='num';
					if (baseType=='num') {
						value=g740.convertor.toJavaScript(value,'num');
						if (value<=0) return '';

						if (!this._list) {
							var list=this.fieldDef.list;
							if (!list) list='';
							this._list=list.split(';')
						}
						if (this._list.length<value) return '';
						return this._list[value-1];
					}
					return value;
				},
				convertFromTextValueToValue: function(text) {
					var baseType='string';
					if (this.fieldDef && this.fieldDef.basetype=='num') baseType='num';
					if (baseType=='num') {
						if (!text) return 0;
						if (!this._list) {
							var list=this.fieldDef.list;
							if (!list) list='';
							this._list=list.split(';')
						}
						for(var i=0; i<this._list.length; i++) {
							if (this._list[i]==text) return i+1;
						}
						return 0;
					}
					return text;
				},
				onButtonClick: function() {
					if (this.getReadOnly()) return false;
					if (this.objRowSet && this.rowId!==null) {
						if (this.objRowSet.getFocusedId()!=this.rowId) {
							if (!this.objRowSet.setFocusedId(this.rowId)) return false;
						}
					}
					// предотвращаем дребезг
					if (this._isWaitClick) return false;
					this._isWaitClick=true;
					this.domNodeInput.focus();

					var objActionGo=this.getActionGo();
					if (objActionGo) {
						if (objActionGo.getEnabled()) objActionGo.exec();
					}
					else {
						var filter='';
						if (this.domNodeInput && this.domNodeInput.value!=this._value) filter=this.domNodeInput.value;
						var objDialog=new g740.DialogEditorList(
							{ 
								objForm: this.objForm,
								rowsetName: this.rowsetName,
								fieldName: this.fieldName,
								fieldDef: this.fieldDef,
								nodeType: this.nodeType,
								domNodeOwner: this.domNode,
								objOwner: this,
								filter: filter,
								duration: 0, 
								draggable: false
							}
						);
						objDialog.show();
					}
					g740.execDelay.go({
						delay: 250,
						obj: this,
						func: this._setWaitClickToFalse
					});
				},
				onKeyDown: function(e) {
					if (e.keyCode==13 && e.ctrlKey) {
						// Ctrl+Enter
						dojo.stopEvent(e);
						this.onButtonClick();
					}
					else if (!e.ctrlKey && (e.keyCode==13 || (e.keyCode==9 && !e.shiftKey))) {
						// Enter, Tab
						dojo.stopEvent(e);
						if (this.domNodeInput && this.domNodeInput.value!=this._value) {
							this.onButtonClick();
						}
						else {
							if (this.objPanel) this.objPanel.doG740FocusChildNext(this);
						}
					}
					else if (!e.ctrlKey && (e.keyCode==9 && e.shiftKey)) {
						// Shift+Tab
						dojo.stopEvent(e);
						if (this.domNodeInput && this.domNodeInput.value!=this._value) {
							this.onButtonClick();
						}
						else {
							if (this.objPanel) this.objPanel.doG740FocusChildPrev(this);
						}
					}
				}
			}
		);
		// Виджет для поля справочника
		dojo.declare(
			'g740.FieldEditor.Ref',
			[g740.ComboBox, g740.FieldEditor],
			{
				postCreate: function() {
					this.inherited(arguments);
				},
				onG740Change: function(newValue) {
					return false;
				},
				isDialogOpened: false,
				onButtonClick: function(e) {
					var isFiltered=false;
					if (e && e.isFiltered) isFiltered=true;
					if (this.getReadOnly()) return false;
					if (this.objRowSet && this.rowId!==null) {
						if (this.objRowSet.getFocusedId()!=this.rowId) {
							if (!this.objRowSet.setFocusedId(this.rowId)) return false;
						}
					}
					// предотвращаем дребезг
					if (this._isWaitClick) return false;
					this._isWaitClick=true;
					this.domNodeInput.focus();
					
					var objActionGo=this.getActionGo();
					if (objActionGo) {
						this.isDialogOpened=true;
						if (objActionGo.getEnabled()) {
							objActionGo.exec();
						}
					}
					else {
						var filter='';
						if (isFiltered) {
							if (this.domNodeInput && this.domNodeInput.value!=this._value) filter=this.domNodeInput.value;
							if (filter=='') filter='--//--';
						}
						
						//if (isFiltered && this.domNodeInput && this.domNodeInput.value!=this._value) filter=this.domNodeInput.value;
						this.isDialogOpened=true;
						var objDialog=new g740.DialogEditorRef(
							{ 
								objForm: this.objForm,
								rowsetName: this.rowsetName,
								fieldName: this.fieldName,
								nodeType: this.nodeType,
								domNodeOwner: this.domNode,
								objOwner: this,
								filter: filter,
								duration: 0,
								draggable: false
							}
						);
						objDialog.show();
					}
					g740.execDelay.go({
						delay: 250,
						obj: this,
						func: this._setWaitClickToFalse
					});
				},
				onKeyDown: function(e) {
					if (e.keyCode==13 && e.ctrlKey) {
						// Ctrl+Enter
						dojo.stopEvent(e);
						this.onButtonClick();
					}
					else if (!e.ctrlKey && (e.keyCode==13 || (e.keyCode==9 && !e.shiftKey))) {
						// Enter, Tab
						dojo.stopEvent(e);
						if (this.domNodeInput && this.domNodeInput.value!=this._value) {
							this.onButtonClick({isFiltered: true});
						}
						else {
							if (this.objPanel) this.objPanel.doG740FocusChildNext(this);
						}
					}
					else if (!e.ctrlKey && (e.keyCode==9 && e.shiftKey)) {
						// Shift+Tab
						dojo.stopEvent(e);
						if (this.domNodeInput && this.domNodeInput.value!=this._value) {
							this.onButtonClick({isFiltered: true});
						}
						else {
							if (this.objPanel) this.objPanel.doG740FocusChildPrev(this);
						}
					}
				},
				onBlur: function() {
					if (!this.isDialogOpened && this.domNodeInput && this.domNodeInput.value!=this._value) {
						this.domNodeInput.value=this._value;
					}
					this.inherited(arguments);
				},
				onFocus: function() {
					this.isDialogOpened=false;
					this.inherited(arguments);
				}
			}
		);
		// Виджет RadioGroup
		dojo.declare(
			'g740.FieldEditor.RadioGroupBox',
			[dijit._Widget, dijit._TemplatedMixin, g740.FieldEditor],
			{
				templateString: '<div class="g740-radiogroupbox">'+
					'<input type="checkbox" class="g740-focused" data-dojo-attach-point="focusNode"></input>'+
					'<div data-dojo-attach-point="domBody">'+
					'</div>'+
				'</div>',
				list: '',
				baseType: 'string',
				value: '',
				_radioItems: null,
				_selectedIndex: -1,
				set: function(name, value) {
					if (name=='list') {
						if (this.list!=value) {
							this.list=value;
							if (!this.list) this.list='';
						}
						return true;
					}
					if (name=='baseType') {
						if (this.baseType!=value) {
							if (value!='string' && value!='num') g740.systemError('g740.RadioGroupBox.set(baseType)',errorIncorrectValue, value);
							this.baseType=value;
						}
						return true;
					}
					if (name=='value') {
						this.setValue(value);
						return true;
					}
					if (name=='readOnly') {
						if (!this.domNode) return false;
						if (value) {
							dojo.addClass(this.domNode, 'g740-readonly');
						}
						else {
							dojo.removeClass(this.domNode, 'g740-readonly');
						}
						return true;
					}
					if (name=='focused') {
						if (this.focusNode && value) this.focusNode.focus();
						return true;
					}
					this.inherited(arguments);
				},
				
				_getMinWidthValue: 0,
				getMinWidth: function() {
					if (!this._getMinWidthValue) {
						var result=18;
						var n=0;
						var lst=this.list.split(';');
						for(var i=0; i<lst.length; i++) {
							if (n<lst[i].length) n=lst[i].length;
						}
						if (n>0) {
							result+=8+parseInt(n*g740.config.charlabelwidth);
							this._getMinWidthValue=result;
						}
					}
					return this._getMinWidthValue;
				},
				_getHeightValue: 0,
				getHeight: function() {
					if (this._getHeightValue) return this._getHeightValue;
					var result=19;
					var n=0;
					var lst=this.list.split(';');
					if (lst.length>0) {
						result=19*lst.length;
						this._getHeightValue=result;
					}
					return result;
				},
				destroy: function() {
					this._radioItems=null;
					this.inherited(arguments);
				},
				postCreate: function() {
					if (this.fieldDef) {
						this.list=this.fieldDef.list;
						this.baseType='string';
						if (this.fieldDef.basetype=='num') this.baseType='num';
					}
					this.on('focus', this._onWidgetFocus);
					this.on('blur', this._onWidgetBlur);
					this.focusNode.obj=this;
					this.focusNode.onkeypress=function(e) {
						if (this.obj) this.obj.onKeyPress(e);
					};
					this.render();
					this.inherited(arguments);
				},
				render: function() {
					if (!this.domNode) return;
					if (!this.domBody) return;
					this.domBody.innerHTML='';
					var lst=this.list.split(';');
					this._radioItems=[];
					for (var i=0; i<lst.length; i++) {
						var domDivItem=document.createElement('div');
						domDivItem.className='g740-radio-item';
						if (this.baseType=='num') {
							domDivItem.setAttribute('data-value',i);
							domDivItem.setAttribute('data-index',i);
						}
						else {
							domDivItem.setAttribute('data-value',lst[i]);
							domDivItem.setAttribute('data-index',i);
						}
						domDivItem.obj=this;
						var domDivIcon=document.createElement('div');
						domDivIcon.className='g740-radio-icon check-off';
						domDivIcon.onclick=function() {
							var domDivItem=this.parentNode;
							if (!domDivItem) return false;
							if (!domDivItem.obj) return false;
							if (!domDivItem.obj.doChange) return false;
							domDivItem.obj.doChange(domDivItem.getAttribute('data-value'));
							domDivItem.obj.setSelectedIndex(domDivItem.getAttribute('data-index'));
							domDivItem.obj.set('focused',true);
						};
						
						domDivItem.appendChild(domDivIcon);
						var domDivLabel=document.createElement('div');
						domDivLabel.className='g740-radio-label';
						domDivLabel.onclick=function() {
							var domDivItem=this.parentNode;
							if (!domDivItem) return false;
							if (!domDivItem.obj) return false;
							if (!domDivItem.obj.doChange) return false;
							domDivItem.obj.setSelectedIndex(domDivItem.getAttribute('data-index'));
							domDivItem.obj.set('focused',true);
						};

						var domLabelText=document.createTextNode(lst[i]);
						domDivLabel.appendChild(domLabelText);
						domDivItem.appendChild(domDivLabel);
						this.domBody.appendChild(domDivItem);
						this._radioItems.push(domDivItem);
					}
					this.setValue(this.value);
				},
				setValue: function(value) {
					if (!this._radioItems) return false;
					if (this.baseType=='num') {
						value=g740.convertor.toJavaScript(value,'num');
					}
					for(var i=0; i<this._radioItems.length; i++) {
						var domDivItem=this._radioItems[i];
						var isOk=false;
						if (domDivItem.getAttribute('data-value')==value) isOk=true;
						if (isOk) {
							if (!dojo.hasClass(domDivItem,'check-on')) dojo.addClass(domDivItem,'check-on');
							if (dojo.hasClass(domDivItem,'check-off')) dojo.removeClass(domDivItem,'check-off');
						} 
						else {
							if (!dojo.hasClass(domDivItem,'check-off')) dojo.addClass(domDivItem,'check-off');
							if (dojo.hasClass(domDivItem,'check-on')) dojo.removeClass(domDivItem,'check-on');
						}
					}
					this.value=value;
				},
				setSelectedIndex: function(index) {
					if (!this._radioItems) return false;
					if (this._selectedIndex==index) return true;
					for(var i=0; i<this._radioItems.length; i++) {
						var domDivItem=this._radioItems[i];
						if (i!=index) {
							if (dojo.hasClass(domDivItem,'g740-widget-focused')) dojo.removeClass(domDivItem,'g740-widget-focused');
						}
						else {
							if (!dojo.hasClass(domDivItem,'g740-widget-focused')) dojo.addClass(domDivItem,'g740-widget-focused');
						}
					}
					this._selectedIndex=index;
				},
				onKeyPress: function(e) {
					if (e.charCode==32 && !e.ctrlKey) {
						// Space
						dojo.stopEvent(e);
						if (this._radioItems && this._selectedIndex>=0) {
							var domDivItem=this._radioItems[this._selectedIndex];
							if (domDivItem) this.doChange(domDivItem.getAttribute('data-value'));
						}
					}
					else if (!e.ctrlKey && (e.keyCode==13 || (e.keyCode==9 && !e.shiftKey))) {
						// Enter, Tab
						dojo.stopEvent(e);
						if (this._radioItems && (this._selectedIndex+1)<this._radioItems.length) {
							this.setSelectedIndex(this._selectedIndex+1);
						}
						else {
							if (this.objPanel) this.objPanel.doG740FocusChildNext(this);
						}
					}
					else if (!e.ctrlKey && (e.keyCode==9 && e.shiftKey)) {
						// Shift+Tab
						dojo.stopEvent(e);
						if (this._radioItems && (this._selectedIndex-1)>=0) {
							this.setSelectedIndex(this._selectedIndex-1);
						}
						else {
							if (this.objPanel) this.objPanel.doG740FocusChildPrev(this);
						}
					}
				},
				_onWidgetFocus: function() {
					if (!this._radioItems) return false;
					this.setSelectedIndex(0);
				},
				_onWidgetBlur: function() {
					this.setSelectedIndex(-1);
				},
				onG740Change: function(newTextValue) {
				},
				doChange: function(newValue) {
					if (!this.objRowSet) return false;
					var rowId=this.rowId;
					if (rowId===null) rowId=this.objRowSet.getFocusedId();
					// Если в дереве не то nodeType, то отписывать не надо
					if (this.objRowSet.isTree && this.nodeType) {
						var nodeType='';
						var node=this.objRowSet.objTreeStorage.getNode(rowId,this.objRowSet.getFocusedParentNode());
						if (node) nodeType=node.nodeType;
						if (this.nodeType!=nodeType) return false;
					}
					this.objRowSet.setFieldProperty({fieldName: this.fieldName, propertyName: 'value', value: newValue, id: rowId});
					if (this.isSaveOnChange && this.objRowSet.getExistUnsavedChanges()) {
						this.objRowSet.exec({requestName: 'save'});
					}
				}
			}
		);
		// Виджет для поля RefList
		dojo.declare(
			'g740.FieldEditor.RefList',
			[dijit._Widget, dijit._TemplatedMixin, g740.FieldEditor],
			{
				templateString: '<div class="g740-widget-reflist">'+
					'<input type="checkbox" class="g740-focused" data-dojo-attach-point="domNodeFocused" data-dojo-attach-event="'+
						'onkeypress: onKeyPress'+
					'"></input>'+
					'<table class="g740-widget-table" cellpadding="0px" cellspacing="0px">'+
					'<tr>'+
						'<td>'+
							'<div class="g740-items" data-dojo-attach-point="domNodeItems" data-dojo-attach-event="'+
								'onclick: _onItemsClick'+
							'">'+
							'</div>'+
						'</td>'+
						'<td style="width:23px" valign="top" align="center">'+
							'<div class="btnfieldeditor" data-dojo-attach-event="'+
							'onclick: onButtonClick'+
							'"></div>'+
							'<div class="btnfieldclear" data-dojo-attach-event="'+
							'onclick: onClearClick'+
							'"></div>'+
						'</td>'+
					'</tr>'+
					'</table>'+
				'</div>',
				readOnly: false,
				value: '',
				items: null,		// {'id': 'value', 'id[nodeType]': 'value'}
				ordered: null,		// ['id','id[nodeType]',...,'id']
				constructor: function(para) {
					this.items={};
					this.ordered=[];
				},
				destroy: function() {
					this.items={};
					this.ordered=[];
					this.inherited(arguments);
				},
				set: function(name, value) {
					if (name=='value') {
						this.setValue(value);
						return true;
					}
					if (name=='focused') {
						if (value) {
							if (this.domNodeFocused) this.domNodeFocused.focus();
						}
					}
					if (name=='readOnly') {
						this.setReadOnly(value);
						return true;
					}
					this.inherited(arguments);
				},
				postCreate: function() {
					this.render();
					dojo.style(this.domNodeItems,'height',this.getHeight()+'px');
					this.focusNode=this.domNodeFocused;
					this.inherited(arguments);
				},
				setValue: function(newValue) {
					if (this.value==newValue) return true;
					this.items={};
					this.ordered=[];
					if (!newValue) newValue='';
					if (!newValue.toString) newValue='';
					newValue=newValue.toString();
					var lst=newValue.split("\n");
					for(var i=0; i<lst.length; i++) {
						var item=lst[i];
						var n=item.indexOf('=');
						if (n<0) continue;
						var name=item.substr(0,n);
						var value=item.substr(n+1,9999);
						this.items[name]=value;
						this.ordered.push(name);
					}
					this.value=this.getValue();
					this.render();
				},
				getValue: function() {
					var result='';
					for(var i=0; i<this.ordered.length; i++) {
						var name=this.ordered[i];
						var value=this.items[name];
						if (result) result+="\n";
						result+=name+'='+value;
					}
					this.value=result;
					return result;
				},
				setReadOnly: function(value) {
					value=(value)?true:false;
					if (value==this.readOnly) return true;
					this.readOnly=value;
					if (value) {
						if (!dojo.hasClass(this.domNodeItems, 'dijitTextBoxReadOnly')) dojo.addClass(this.domNodeItems, 'dijitTextBoxReadOnly');
					}
					else {
						if (dojo.hasClass(this.domNodeItems, 'dijitTextBoxReadOnly')) dojo.removeClass(this.domNodeItems, 'dijitTextBoxReadOnly');
					}
				},
				render: function() {
					if (!this.domNodeItems) return;
					this.domNodeItems.innerHTML='';
					for(var i=0; i<this.ordered.length; i++) {
						var name=this.ordered[i];
						var domDiv=document.createElement('div');
						var domText=document.createTextNode(this.items[name]);
						domDiv.appendChild(domText);
						this.domNodeItems.appendChild(domDiv);
					}
					var readOnly=this.readOnly;
					this.readOnly='';
					this.setReadOnly(readOnly);
				},
				onButtonClick: function() {
					if (this.getReadOnly()) return false;
					if (this.objRowSet && this.rowId!==null) {
						if (this.objRowSet.getFocusedId()!=this.rowId) {
							if (!this.objRowSet.setFocusedId(this.rowId)) return false;
						}
					}
					// предотвращаем дребезг
					if (this._isWaitClick) return false;
					this._isWaitClick=true;
					this.domNodeFocused.focus();
					
					var objActionGo=this.getActionGo();
					if (objActionGo) {
						if (objActionGo.getEnabled()) objActionGo.exec();
					}
					else {
						var objDialog=new g740.DialogEditorRefList(
							{ 
								objForm: this.objForm,
								rowsetName: this.rowsetName,
								fieldName: this.fieldName,
								nodeType: this.nodeType,
								domNodeOwner: this.domNode,
								objOwner: this,
								duration: 0, 
								draggable: false
							}
						);
						objDialog.show();
					}
					g740.execDelay.go({
						delay: 250,
						obj: this,
						func: this._setWaitClickToFalse
					});
				},
				onClearClick: function() {
					if (!this.objRowSet) return false;
					var rowId=this.rowId;
					if (rowId===null) rowId=this.objRowSet.getFocusedId();
					// Если в дереве не то nodeType, то отписывать не надо
					if (this.objRowSet.isTree && this.nodeType) {
						var nodeType='';
						var node=this.objRowSet.objTreeStorage.getNode(rowId,this.objRowSet.getFocusedParentNode());
						if (node) nodeType=node.nodeType;
						if (this.nodeType!=nodeType) return false;
					}
					
					this.objRowSet.setFieldProperty({fieldName: this.fieldName, propertyName: 'value', value: '', id: rowId});
					if (this.isSaveOnChange && this.objRowSet.getExistUnsavedChanges()) {
						this.objRowSet.exec({requestName: 'save'});
					}
				},
				onG740Change: function(newValue) {
					return false;
				},
				onBlur: function() {
					if (dojo.hasClass(this.domNode,'g740-widget-focused')) dojo.removeClass(this.domNode,'g740-widget-focused');
				},
				onFocus: function() {
					if (!dojo.hasClass(this.domNode,'g740-widget-focused')) dojo.addClass(this.domNode,'g740-widget-focused');
				},
				onKeyPress : function(e) {
					if (e.keyCode==13 && e.ctrlKey) {
						// Ctrl+Enter
						dojo.stopEvent(e);
						this.onButtonClick();
					}
					else if (!e.ctrlKey && (e.keyCode==13 || (e.keyCode==9 && !e.shiftKey))) {
						// Enter, Tab
						dojo.stopEvent(e);
						if (this.objPanel) this.objPanel.doG740FocusChildNext(this);
					}
					else if (!e.ctrlKey && (e.keyCode==9 && e.shiftKey)) {
						// Shift+Tab
						dojo.stopEvent(e);
						if (this.objPanel) this.objPanel.doG740FocusChildPrev(this);
					}
					else {
						dojo.stopEvent(e);
					}
				},
				_onItemsClick: function(e) {
					this.set('focused',true);
				},
				getHeight: function() {
					return 60;
				}
			}
		);
		// Виджет для поля RefTree
		dojo.declare(
			'g740.FieldEditor.RefTree',
			[g740.FieldEditor.RefList],
			{
				onButtonClick: function() {
					if (this.getReadOnly()) return false;
					if (this.objRowSet && this.rowId!==null) {
						if (this.objRowSet.getFocusedId()!=this.rowId) {
							if (!this.objRowSet.setFocusedId(this.rowId)) return false;
						}
					}
					// предотвращаем дребезг
					if (this._isWaitClick) return false;
					this._isWaitClick=true;
					this.domNodeFocused.focus();
					
					var objActionGo=this.getActionGo();
					if (objActionGo) {
						if (objActionGo.getEnabled()) objActionGo.exec();
					}
					else {
						var objDialog=new g740.DialogEditorRefTree(
							{ 
								objForm: this.objForm,
								rowsetName: this.rowsetName,
								fieldName: this.fieldName,
								nodeType: this.nodeType,
								domNodeOwner: this.domNode,
								objOwner: this,
								duration: 0, 
								draggable: false
							}
						);
						objDialog.show();
					}
					g740.execDelay.go({
						delay: 250,
						obj: this,
						func: this._setWaitClickToFalse
					});
				}
			}
		);
		
	    // Виджет кнопки
		dojo.declare(
			'g740.FieldEditor.Button',
			[dijit.form.Button, g740.FieldEditor],
			{
			    constructor: function (para, domElement) {
			        var procedureName = 'g740.FieldEditor.Button.constructor';
			        try {
			            this.inherited(arguments);
			        }
			        finally {
			        }
			    },
			    postCreate: function () {
			        if (this.fieldDef) {
			            if (this.fieldDef.request) this.set('buttonVisible', true);
			            if (this.fieldDef.caption) this.set('label', this.fieldDef.caption);
			            else if (this.fieldDef.request) this.set('label', this.fieldDef.request.caption);
			        }
			        this.on('Click', this.onG740ButtonClick);
			        this.inherited(arguments);
			    },
			    onG740ButtonClick: function () {
			        //if (this.getReadOnly()) return false;
			        if (this.objRowSet && this.rowId !== null) {
			            if (this.objRowSet.getFocusedId() != this.rowId) {
			                if (!this.objRowSet.setFocusedId(this.rowId)) return false;
			            }
			        }
			        //this.domNodeInput.focus();
			        var objActionGo = this.getActionGo();
			        if (objActionGo) {
						// предотвращаем дребезг
						if (this._isWaitClick) return false;
						this._isWaitClick=true;
						if (objActionGo && objActionGo.getEnabled()) objActionGo.exec();
						g740.execDelay.go({
							delay: 250,
							obj: this,
							func: this._setWaitClickToFalse
						});
			        }
			    }
			}
		);

		return g740;
	}
);