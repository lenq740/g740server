//-----------------------------------------------------------------------------
// Виджеты для использования в панелях
//-----------------------------------------------------------------------------
define(
	[],
	function() {
		if (typeof(g740)=='undefined') g740={};
		
// Виджет: заголовок панели
		dojo.declare(
			'g740.PanelTitle',
			[dijit._Widget, dijit._TemplatedMixin],
			{
				templateString: '<div class="g74-paneltitle" data-dojo-attach-point="titleNode"></div>',
			    //templateString: '<div style="border-style:none"><h4 data-dojo-attach-point="titleNode"></h4></div>',
			    title: '',
				_setTitleAttr: { node: "titleNode", type: "innerHTML" }
			}
		);

		dojo.declare(
			'g740.PanelSeparator',
			[dijit._Widget, dijit._TemplatedMixin],
			{
			    templateString: '<div></div>',
			    height: '0px',
				set: function(name, value) {
					if (name=='height') {
						this.height=value;
						if (this.domNode) {
							dojo.style(this.domNode, 'height', this.height);
						}
					}
					else {
						this.inherited(arguments);
					}
				},
				postCreate: function() {
					if (this.domNode) {
						dojo.style(this.domNode, 'height', this.height);
					}
					this.inherited(arguments);
				}
			}
		);
	
// Виджет: пагнатор
		dojo.declare(
			'g740.Paginator',
			[dijit._Widget, dijit._TemplatedMixin],
			{
				templateString: 
					'<div class="g740-paginator">'+
						'<div class="paginator-center">'+
							'<div class="btn btn-first" '+
								'data-dojo-attach-point="domNodeBtnFirst" '+
								'data-dojo-attach-event="onclick: onBtnFirstClick" '+
							'></div>'+
							'<div class="btn btn-prev" '+
								'data-dojo-attach-point="domNodeBtnPrev" '+
								'data-dojo-attach-event="onclick: onBtnPrevClick" '+
							'></div>'+
							
							'<div class="pages" data-dojo-attach-point="domNodePaginatorPage"></div>'+
							
							'<div class="btn btn-next" '+
								'data-dojo-attach-point="domNodeBtnNext" '+
								'data-dojo-attach-event="onclick: onBtnNextClick" '+
							'></div>'+
							'<div class="btn btn-last" '+
								'data-dojo-attach-point="domNodeBtnLast" '+
								'data-dojo-attach-event="onclick: onBtnLastClick" '+
							'></div>'+
							'<div style="clear:both"></div>'+
						'</div>'+
					'</div>',
				rowsetName: null,
				objForm: null,
				objRowSet: null,
				isPrevEnabled: true,
				isNextEnabled: true,
				set: function(name, value) {
					if (name=='objForm') {
						this.objForm=value;
						if (this.objForm && this.rowsetName) {
							this.objRowSet=this.objForm.rowsets[this.rowsetName];
						}
						return true;
					}
					if (name=='rowsetName') {
						this.rowsetName=value;
						if (this.objForm && this.rowsetName) {
							this.objRowSet=this.objForm.rowsets[this.rowsetName];
						}
						return true;
					}
					if (name=='isPrevEnabled') {
						this.isPrevEnabled=value;
						if (this.domNodeBtnFirst && this.domNodeBtnPrev) {
							if (this.isPrevEnabled) {
								if (dojo.hasClass(this.domNodeBtnFirst,'disabled')) dojo.removeClass(this.domNodeBtnFirst,'disabled');
								if (dojo.hasClass(this.domNodeBtnPrev,'disabled')) dojo.removeClass(this.domNodeBtnPrev,'disabled');
							} else {
								if (!dojo.hasClass(this.domNodeBtnFirst,'disabled')) dojo.addClass(this.domNodeBtnFirst,'disabled');
								if (!dojo.hasClass(this.domNodeBtnPrev,'disabled')) dojo.addClass(this.domNodeBtnPrev,'disabled');
							}
						}
					}
					if (name=='isNextEnabled') {
						this.isNextEnabled=value;
						if (this.domNodeBtnFirst && this.domNodeBtnPrev) {
							if (this.isNextEnabled) {
								if (dojo.hasClass(this.domNodeBtnLast,'disabled')) dojo.removeClass(this.domNodeBtnLast,'disabled');
								if (dojo.hasClass(this.domNodeBtnNext,'disabled')) dojo.removeClass(this.domNodeBtnNext,'disabled');
							} else {
								if (!dojo.hasClass(this.domNodeBtnLast,'disabled')) dojo.addClass(this.domNodeBtnLast,'disabled');
								if (!dojo.hasClass(this.domNodeBtnNext,'disabled')) dojo.addClass(this.domNodeBtnNext,'disabled');
							}
						}
					}
					this.inherited(arguments);
				},
				destroy: function() {
					this.objForm=null;
					this.objRowSet=null;
					this.inherited(arguments);
				},
				postCreate: function() {
					this.set('isPrevEnabled',false);
					this.set('isNextEnabled',false);
					this.domNodeBtnFirst.title=g740.getMessage('paginatorFirst');
					this.domNodeBtnPrev.title=g740.getMessage('paginatorPrev');
					this.domNodeBtnNext.title=g740.getMessage('paginatorNext');
					this.domNodeBtnLast.title=g740.getMessage('paginatorLast');
					
					this.repaint();
					this.inherited(arguments);
				},
			    
				repaint: function() {
					if (!this.domNodePaginatorPage) return true;
					this.domNodePaginatorPage.innerHTML='';
					var pageCount=this.getPageCount();
					if (pageCount==1) return true;
					
					var pageIndex=this.getPageIndex();
					if (Math.floor(pageIndex/2)*2==pageIndex) {
						var pageIndexFirst=pageIndex-2;
					}
					else {
						if (Math.floor((pageIndex-1)/4)*4==(pageIndex-1)) {
							var pageIndexFirst=pageIndex-1;
						}
						else {
							var pageIndexFirst=pageIndex-3;
						}
					}
					
					if (pageIndexFirst>=pageCount-5) pageIndexFirst=pageCount-5;
					if (pageIndexFirst<0) pageIndexFirst=0;
					for(var i=0; i<5; i++) {
						var pg=pageIndexFirst+i;
						if (pg>=pageCount) break;
						var domItem=document.createElement('div');
						domItem.setAttribute('data-page', pg);
						dojo.addClass(domItem, 'item');
						dojo.on(domItem, 'click', dojo.hitch(this, this.onPageClick));
						if (pg==pageIndex) dojo.addClass(domItem, 'current');
						var domText=document.createTextNode(pg+1);
						domItem.appendChild(domText);
						this.domNodePaginatorPage.appendChild(domItem);
					}
					var domItem=document.createElement('div');
					domItem.style.clear='both';
					this.domNodePaginatorPage.appendChild(domItem);
					
				},
				onPageClick: function(evt) {
					if (!evt) return true;
					var domItem=evt.target;
					var pageIndex=domItem.getAttribute('data-page');
					this.execRefreshPage(pageIndex);
				},
				doG740Repaint: function (para) {
					if (!para) para={};
					if (!para.objRowSet) return true;
					if (para.objRowSet.name!=this.rowsetName) return true;
					if (!para.isFull) return true;
					if (!para.objRowSet.paginatorCount || !para.objRowSet.paginatorAll) {
						this.set('isPrevEnabled',false);
						this.set('isNextEnabled',false);
						return true;
					}

					var pageCount=this.getPageCount();
					var pageIndex=this.getPageIndex();
					this.set('isPrevEnabled',pageIndex>0);
					this.set('isNextEnabled',pageIndex<(pageCount-1));
					this.repaint();
			    },
				getPageCount: function() {
					if (!this.objRowSet) return 0;
					var paginatorCount=Math.floor(this.objRowSet.paginatorCount*0.9);
					if (paginatorCount==0) paginatorCount=1;
					
					var result=Math.floor((this.objRowSet.paginatorAll-0.5*paginatorCount)/paginatorCount)+1;
					if (result<1) result=1;
					return result;
				},
				getPageIndex: function() {
					if (!this.objRowSet) return 0;
					var paginatorCount=Math.floor(this.objRowSet.paginatorCount*0.9);
					if (paginatorCount==0) paginatorCount=1;
					
					var pageCount=this.getPageCount();
					var result=Math.floor(this.objRowSet.paginatorFrom/paginatorCount);
					if (result>=pageCount) result=pageCount-1;
					return result;
				},
				getPageFrom: function(pageIndex) {
					if (!this.objRowSet) return 0;
					var paginatorCount=Math.floor(this.objRowSet.paginatorCount*0.9);
					if (paginatorCount==0) paginatorCount=1;
					
					var pageCount=this.getPageCount();
					if (pageIndex>=pageCount) pageIndex=pageCount-1;
					if (pageIndex<0) pageIndex=0;
					return pageIndex*paginatorCount;
				},
				
				execRefreshPage: function(pageIndex) {
					if (!this.objRowSet) return true;
					var pageCount=this.getPageCount();
					if (pageIndex<0) pageIndex=0;
					if (pageIndex>(pageCount-1)) pageIndex=pageCount-1;
					
					var G740params={
						'paginator.from': this.getPageFrom(pageIndex)
					};
					if (pageIndex==(pageCount-1)) {
						G740params['paginator.count']=this.objRowSet.paginatorCount*3;
					}
					this.objRowSet.exec({
						requestName: 'refresh',
						G740params: G740params
					});
				},
				onBtnFirstClick: function() {
					if (!this.isPrevEnabled) return true;
					this.execRefreshPage(0);
				},
				onBtnPrevClick: function() {
					if (!this.isPrevEnabled) return true;
					var pageIndex=this.getPageIndex();
					this.execRefreshPage(pageIndex-1);
				},
				onBtnNextClick: function() {
					if (!this.isNextEnabled) return true;
					var pageIndex=this.getPageIndex();
					this.execRefreshPage(pageIndex+1);
				},
				onBtnLastClick: function() {
					if (!this.isNextEnabled) return true;
					var pageCount=this.getPageCount();
					this.execRefreshPage(pageCount-1);
				}
			}
		);

// Виджет: строковый редактор с кнопкой вызова диалога
		dojo.declare(
			'g740.TextBox',
			[dijit._Widget, dijit._TemplatedMixin],
			{
				templateString: '<div class="g740-widget-text" data-dojo-attach-event="'+
					'onblur: onBlur, onfocus: onFocus'+
				'">'+
					'<table class="g740-widget-table" cellpadding="0px" cellspacing="0px">'+
					'<tr>'+
						'<td>'+
							'<input type="text" class="g740-widget-input" '+
							'data-dojo-attach-point="domNodeInput" data-dojo-attach-event="'+
							'onkeydown: onKeyDown, onkeyup: onKeyUp, onkeypress: onKeyPress, onchange: doInputChange'+
							'"/>'+
						'</td>'+
						'<td style="width:0px;display:none" valign="top" align="center" data-dojo-attach-point="domNodeTdButton">'+
							'<div class="btnfieldeditor" data-dojo-attach-event="'+
							'onclick: onButtonClick'+
							'"></div>'+
						'</td>'+
					'</tr>'+
					'</table>'+
				'</div>',
				buttonVisible: false,
				set: function(name, value) {
					if (name=='value') {
						this.domNodeInput.value=value;
						return true;
					}
					if (name=='readOnly') {
						this.domNodeInput.readOnly=value;
						if (value) {
							dojo.addClass(this.domNodeInput, 'dijitTextBoxReadOnly');
						}
						else {
							dojo.removeClass(this.domNodeInput, 'dijitTextBoxReadOnly');
						}
						return true;
					}
					if (name=='buttonVisible' && this.buttonVisible!=value) {
						if (value) {
							dojo.style(this.domNodeTdButton, 'width', '23px');
							dojo.style(this.domNodeTdButton, 'display', 'table-cell');
						}
						else {
							dojo.style(this.domNodeTdButton, 'width', '0px');
							dojo.style(this.domNodeTdButton, 'display', 'none');
						}
						this.buttonVisible=value;
					}
					this.inherited(arguments);
				},
				postCreate: function() {
					this.inherited(arguments);
					this.focusNode=this.domNodeInput;
				},
				onButtonClick: function() {
				},
				onKeyDown: function(evt) {
				},
				onKeyUp: function(evt) {
				},
				onKeyPress: function(evt) {
				},
				doInputChange: function(evt) {
					this.onChange(this.domNodeInput.value);
				},
				onChange: function(newValue) {
				},
				onBlur: function() {
				},
				onFocus: function() {
				}
			}
		);

// Виджет: Memo редактор с кнопкой вызова диалога
		dojo.declare(
			'g740.Memo',
			[dijit._Widget, dijit._TemplatedMixin],
			{
				templateString: '<div class="g740-widget-text" data-dojo-attach-event="'+
					'onblur: onBlur, onfocus: onFocus'+
				'">'+
					'<table class="g740-widget-table" cellpadding="0px" cellspacing="0px">'+
					'<tr>'+
						'<td>'+
							'<textarea class="g740-widget-textarea" style="height:100%"'+
							'data-dojo-attach-point="domNodeTextArea" data-dojo-attach-event="'+
							'onkeydown: onKeyDown, onkeyup: onKeyUp, onkeypress: onKeyPress, onchange: doTextAreaChange'+
							'">'+
							'</textarea>'+
						'</td>'+
						'<td style="width:23px" valign="top" align="center">'+
							'<div class="btnfieldeditor" data-dojo-attach-event="'+
							'onclick: onButtonClick'+
							'"></div>'+
						'</td>'+
					'</tr>'+
					'</table>'+
				'</div>',
				set: function(name, value) {
					if (name=='value') {
						this.domNodeTextArea.value=value;
						return true;
					}
					if (name=='readOnly') {
						this.domNodeTextArea.readOnly=value;
						if (value) {
							dojo.addClass(this.domNodeTextArea, 'dijitTextBoxReadOnly');
						}
						else {
							dojo.removeClass(this.domNodeTextArea, 'dijitTextBoxReadOnly');
						}
						return true;
					}
					this.inherited(arguments);
				},
				postCreate: function() {
					this.inherited(arguments);
					this.focusNode=this.domNodeTextArea;
				},
				onButtonClick: function() {
				},
				onKeyDown: function(evt) {
				},
				onKeyUp: function(evt) {
				},
				onKeyPress: function(evt) {
				},
				doTextAreaChange: function(evt) {
					this.onChange(this.domNodeTextArea.value);
				},
				onChange: function(newValue) {
				},
				onBlur: function() {
				},
				onFocus: function() {
				}
			}
		);

// Виджет: Текстовый редактор с кнопкой вызова диалога
		dojo.declare(
			'g740.ComboBox',
			[dijit._Widget, dijit._TemplatedMixin],
			{
				templateString: '<div class="g740-combobox g740-widget-text" data-dojo-attach-event="'+
					'onblur: onBlur, onfocus: onFocus'+
				'">'+
					'<table class="g740-widget-table" cellpadding="0px" cellspacing="0px">'+
					'<tr>'+
						'<td>'+
							'<input type="text" class="g740-widget-input" '+
							'data-dojo-attach-point="domNodeInput" data-dojo-attach-event="'+
							'onkeydown: onKeyDown, onkeyup: onKeyUp, onkeypress: onKeyPress, onchange: doInputChange'+
							'">'+
						'</td>'+
						'<td style="width:23px" valign="top" align="center">'+
							'<div class="btnfieldeditor" data-dojo-attach-event="'+
							'onclick: onButtonClick'+
							'"></div>'+
						'</td>'+
					'</tr>'+
					'</table>'+
				'</div>',
				_readOnly: false,
				_value: '',
				getReadOnly: function() {
					return this._readOnly;
				},
				set: function(name, value) {
					if (name=='value') {
						if (this.domNodeInput) {
							this.domNodeInput.value=value;
							this._value=value;
						}
						return true;
					}
					if (name=='readOnly') {
						if (this.domNodeInput) {
							this.domNodeInput.readOnly=value;
							this._readOnly=value;
							if (value) {
								if (!dojo.hasClass(this.domNodeInput, 'dijitTextBoxReadOnly')) dojo.addClass(this.domNodeInput, 'dijitTextBoxReadOnly');
							}
							else {
								if (dojo.hasClass(this.domNodeInput, 'dijitTextBoxReadOnly')) dojo.removeClass(this.domNodeInput, 'dijitTextBoxReadOnly');
							}
						}
						return true;
					}
					this.inherited(arguments);
				},
				postCreate: function() {
					this.inherited(arguments);
					this.focusNode=this.domNodeInput;
				},
				
				onButtonClick: function() {
				},
				onKeyDown: function(e) {
					if (e.keyCode==13 && e.ctrlKey) {
						// Ctrl+Enter
						dojo.stopEvent(e);
						this.onButtonClick();
					}
				},
				onKeyUp: function(evt) {
				},
				onKeyPress: function(evt) {
				},
				doInputChange: function(evt) {
					this.onChange(this.domNodeInput.value);
				},
				onChange: function(newValue) {
				},
				onBlur: function() {
				},
				onFocus: function() {
				}
			}
		);
		
// Виджет: список с пометками элементов
		dojo.declare(
			'g740.ListCheckBox',
			[dijit._Widget, dijit._TemplatedMixin],
			{
				fieldName: '',
				objRowSet: null,
				
				templateString: '<div class="g740-widget-listcheckbox">'+
				'</div>',
				_itemsDomCheckBox: null,
				_itemsChecked: null,
				_itemsData: null,
				destroy: function() {
					this._itemsDomCheckBox=null;
					this._itemsChecked=null;
					this._itemsData=null;
					this.objRowSet=null;
					this.inherited(arguments);
				},
				set: function(name, value) {
					if (name=='value') {
						this.setValue(value);
						return true;
					}
					if (name=='focused') {
						if (value) this.focus();
					}
					this.inherited(arguments);
				},
				postCreate: function() {
					this.render();
					this.inherited(arguments);
				},
				setChecked: function(id, checked) {
					if (!this._itemsChecked) this._itemsChecked={};
					this._itemsChecked[id]=checked;
					if (!this._itemsDomCheckBox) return false;
					var domCheckBox=this._itemsDomCheckBox[id];
					if (!domCheckBox) return false;
					domCheckBox.checked=checked;
				},
				render: function() {
					if (!this.domNode) return false;
					if (!this._itemsChecked) this._itemsChecked={};
					this.domNode.innerHTML='';
					this._itemsDomCheckBox={};
					var lst=this.getItems();

					for (var i=0; i<lst.length; i++) {
						var item=lst[i];
						if (!item.id) continue;
						if (this._itemsDomCheckBox[item.id]) continue;
						
						var domDiv=document.createElement('div');
						var domLabel=document.createElement('label');
						var domLabelText=document.createTextNode(' '+item.value);
						var domCheckBox=document.createElement('input');
						domCheckBox.setAttribute('type','checkbox');
						domCheckBox.setAttribute('name',item.id);
						if (this._itemsChecked[item.id]) domCheckBox.checked=true;
						
						domLabel.appendChild(domCheckBox);
						domLabel.appendChild(domLabelText);
						domDiv.appendChild(domLabel);
						this.domNode.appendChild(domDiv);
						this._itemsDomCheckBox[item.id]=domCheckBox;
					}
				},
				setValue: function(newValue) {
					this._itemsChecked={};
					if (!newValue) newValue='';
					if (!newValue.toString) newValue='';
					newValue=newValue.toString();
					var lst=newValue.split("\n");
					for(var i=0; i<lst.length; i++) {
						var item=lst[i];
						var n=item.indexOf('=');
						if (n<0) continue;
						var id=item.substr(0,n);
						this._itemsChecked[id]=true;
					}
					if (this._itemsDomCheckBox) for (var id in this._itemsDomCheckBox) {
						var domCheckBox=this._itemsDomCheckBox[id];
						if (!domCheckBox) continue;
						domCheckBox.checked=this._itemsChecked[id]?true:false;
					}
				},
				getValue: function() {
					var result='';
					if (this._itemsDomCheckBox) {
						this._itemsChecked={};
						for(var id in this._itemsDomCheckBox) {
							var domCheckBox=this._itemsDomCheckBox[id];
							if (!domCheckBox) continue;
							if (domCheckBox.checked) this._itemsChecked[id]=true;
						}
					}
					var items=this.getItems();
					for(var i=0; i<items.length; i++) {
						var item=items[i];
						if (!this._itemsChecked[item.id]) continue;
						if (result) result+="\n";
						result+=item.id+'='+item.value;
					}
					return result;
				},
				focus: function() {
					if (this._itemsDomCheckBox) {
						var lst=this.getItems();
						if (lst.length>0) {
							var item=lst[0];
							var domCheckBox=this._itemsDomCheckBox[item.id];
							if (domCheckBox) {
								domCheckBox.focus();
								return;
							}
						}
					}
					if (this.domNode) this.domNode.focus();
				},
				// возвращает упорядоченный массив элементов в виде {id: <id>, value: <value>}
				getItems: function() {
					if (this._itemsData) return this._itemsData;
					var result=[];
					if (!this.objRowSet) return result;
					if (this.objRowSet.g740className!='g740.RowSet') return result;
					if (this.objRowSet.isObjectDestroed) return result;
					var objTreeStorage=this.objRowSet.objTreeStorage;
					if (!objTreeStorage) return result;
					if (objTreeStorage.isObjectDestroed) return result;

					var nodes=objTreeStorage.getChildsOrdered(objTreeStorage.rootNode);
					for (var i=0; i<nodes.length; i++) {
						var node=nodes[i];
						if (!node) continue;
						var row=node.info;
						if (!row) continue;
						var fields=this.objRowSet.getFields(node);
						var fld=fields[this.fieldName];
						if (!fld) continue;
						var item={id: node.id, value: g740.convertor.js2text(row[this.fieldName+'.value'],fld.type)};
						result.push(item);
					}
					this._itemsData=result;
					return this._itemsData;
				},
				onBlur: function() {
					this.focused=false;
				},
				onFocus: function() {
					this.focused=true;
				}
			}
		)

// Виджет: абстрактный предок списка
		dojo.declare(
			'g740._ListAbstract',
			[dijit._Widget, dijit._TemplatedMixin],
			{
				filter: '',
				value: null,
				isAddEmptyItem: false,
				templateString: 
					'<div class="g740-list">'+
						'<div class="g740-list-div-input" data-dojo-attach-point="domNodeDivInput">'+
							'<input type="text" class="g740-list-input" data-dojo-attach-point="domNodeInput" data-dojo-attach-event="'+
								'onkeydown: doInputKeyDown'+
							'"/>'+
						'</div>'+
						'<div class="g740-list-items" data-dojo-attach-point="domNodeItems">'+
						'</div>'+
					'</div>',
				_listOfDomItem: {},
				postCreate: function() {
					this.inherited(arguments);
					this.focusNode=this.domNodeInput;
					this.domNodeInput.value=this.filter;
					this._renderItems();
					g740.execDelay.go({
						delay: 50,
						obj: this,
						func: function() {
							var cursorPos=String(this.filter).length;
							if (this.domNodeInput.setSelectionRange) {
								this.domNodeInput.setSelectionRange(cursorPos,cursorPos);
							}
							else if (this.domNodeInput.createTextRange) {
								var range = this.domNodeInput.createTextRange();
								range.collapse(true);
								range.moveEnd('character', cursorPos);
								range.moveStart('character', cursorPos);
								range.select();
							}
							this.layout();
						}
					});
				},

				destroy: function() {
					this._listOfDomItem={};
					this.inherited(arguments);
				},
				set: function(name, value) {
					if (name=='value') {
						if (this.value!=value) {
							this.value=value;
							this._renderValue();
						}
						return true;
					}
					if (name='focused') {
						if (value) {
							if (this.domNodeInput) this.domNodeInput.focus();
						}
						return true;
					}
					this.inherited(arguments);
				},
				// абстрактный метод - возвращает упорядоченный массив элементов в виде {id: <id>, value: <value>}
				getItems: function() {
					return [];
				},
				_renderItems: function() {
					if (!this.domNodeItems) return false;
					this.domNodeItems.innerHTML='';
					this._listOfDomItem={};
					var items=this.getItems();
					for (var i=0; i<items.length; i++) {
						var item=items[i];
						var domItem=document.createElement('div');
						domItem.setAttribute('data-id',item['id']);
						domItem.setAttribute('title',item['value']);
						domItem.className='g740-list-item';
						domItem.obj=this;
						dojo.on(domItem, 'click', function() {
							if (this.obj && this.obj.doItemClick) {
								var id=this.getAttribute('data-id');
								this.obj.doItemClick(id);
							}
						});
						dojo.on(domItem, 'dblclick', function() {
							if (this.obj && this.obj.doItemDblClick) {
								var id=this.getAttribute('data-id');
								this.obj.doItemDblClick(id);
							}
						});
						var domSpan=document.createElement('span');
						var domText=document.createTextNode(item['value']);
						domSpan.appendChild(domText);
						domItem.appendChild(domSpan);
						this.domNodeItems.appendChild(domItem);
						this._listOfDomItem[item['id']]=domItem;
					}
					if (!this._listOfDomItem[this.value]) this.value=this.getIdFirst();
					this._renderValue();
					this.onResize();
				},
				_oldValue: null,
				_renderValue: function() {
					if (!this._listOfDomItem) return;
					var domDivOld=this._listOfDomItem[this._oldValue];
					if (domDivOld && dojo.hasClass(domDivOld, 'selected')) dojo.removeClass(domDivOld, 'selected');
					var domDivNew=this._listOfDomItem[this.value];
					if (domDivNew && !dojo.hasClass(domDivNew, 'selected')) dojo.addClass(domDivNew, 'selected');
					this._oldValue=this.value;
					this.scrollToSelected();
				},
				
				getIdFirst: function() {
					var result=null;
					if (!this.domNodeItems) return result;
					for (var i=0; i<this.domNodeItems.childNodes.length; i++) {
						var domItem=this.domNodeItems.childNodes[i];
						if (domItem.nodeName!='DIV') continue;
						result=domItem.getAttribute('data-id');
						break;
					}
					return result;
				},
				getIdLast: function() {
					var result=null;
					if (!this.domNodeItems) return result;
					for (var i=this.domNodeItems.childNodes.length-1; i>=0; i--) {
						var domItem=this.domNodeItems.childNodes[i];
						if (domItem.nodeName!='DIV') continue;
						result=domItem.getAttribute('data-id');
						break;
					}
					return result;
				},
				getIdNext: function(id) {
					if (!this.domNodeItems) return null;
					if (!this._listOfDomItem) return null;
					var domItem=this._listOfDomItem[id];
					while (domItem) {
						domItem=domItem.nextSibling;
						if (domItem && domItem.nodeName=='DIV') {
							return domItem.getAttribute('data-id');
						}
					}
					return this.getIdLast();
				},
				getIdPrev: function(id) {
					if (!this.domNodeItems) return null;
					if (!this._listOfDomItem) return null;
					var domItem=this._listOfDomItem[id];
					while (domItem) {
						domItem=domItem.previousSibling;
						if (domItem && domItem.nodeName=='DIV') {
							return domItem.getAttribute('data-id');
						}
					}
					return this.getIdFirst();
				},
				getHeight: function() {
					var n=0;
					for(var id in this._listOfDomItem) {
						n++;
						if (n>=25) break;
					}
					if (n<5) n=5;
					return n*18+25;
				},
				doInputKeyDown: function(e) {
					if (!e.ctrlKey && e.keyCode==40) {
						// Dn
						this.set('value',this.getIdNext(this.value));
						dojo.stopEvent(e);
					}
					else if (!e.ctrlKey && e.keyCode==38) {
						// Up
						this.set('value',this.getIdPrev(this.value));
						dojo.stopEvent(e);
					}
					else if (!e.ctrlKey && e.keyCode==34) {
						// PgDn
						if (this.domNodeItems) {
							var id=this.value;
							var n=parseInt(this.domNodeItems.clientHeight/18);
							for(var i=0; i<n; i++) id=this.getIdNext(id);
							this.set('value',id);
						}
						dojo.stopEvent(e);
					}
					else if (!e.ctrlKey && e.keyCode==33) {
						// PgUp
						if (this.domNodeItems) {
							var id=this.value;
							var n=parseInt(this.domNodeItems.clientHeight/18);
							for(var i=0; i<n; i++) id=this.getIdPrev(id);
							this.set('value',id);
						}
						dojo.stopEvent(e);
					}
					else if (!e.ctrlKey && e.keyCode==13) {
						// Enter
						dojo.stopEvent(e);
						this.onDblClick();
					}
					else {
						this._refreshIndex++;
						g740.execDelay.go({
							delay: 400,
							obj: this,
							func: this._refreshGo
						});
					}
				},
				_refreshIndex: 0,
				_refreshGo: function() {
					this._refreshIndex--;
					if (this._refreshIndex>0) return;
					if (!this.domNodeInput) return;
					if (this.filter==this.domNodeInput.value) return;
					this.filter=this.domNodeInput.value;
					this._renderItems();
					this._renderValue();
				},
				layout: function() {
					this.inherited(arguments);
					if (this.domNodeItems) {
						var h=(this.domNode.clientHeight-this.domNodeDivInput.offsetHeight)+'px';
						dojo.style(this.domNodeItems,'height',h);
					}
					this.scrollToSelected();
				},
				scrollToSelected: function() {
					if (!this.domNodeItems) return;
					if (!this._listOfDomItem) return;
					var domItem=this._listOfDomItem[this.value];
					if (!domItem) return;
					
					var y=this.domNodeItems.scrollTop;
					var h=this.domNodeItems.offsetHeight;
					var delta=10;
					if (delta*4>h) delta=h/4;
					if (domItem.offsetTop<(y+delta)) {
						y=parseInt(domItem.offsetTop-h/2);
					}
					if ((domItem.offsetTop+domItem.offsetHeight)>(y+h-delta)) {
						y=parseInt(domItem.offsetTop-h/2);
					}
					if (y<0) y=0;
					this.domNodeItems.scrollTop=y;
				},
				doItemClick: function(id) {
					if (this.domNodeInput) this.domNodeInput.focus();
					this.set('value',id);
					this.onChange(id);
					this.onClick();
				},
				doItemDblClick: function(id) {
					if (this.domNodeInput) this.domNodeInput.focus();
					this.set('value',id);
					this.onChange(id);
					this.onDblClick();
				},
				onChange: function(newValue) {
				},
				onClick: function(evt) {
				},
				onDblClick: function(evt) {
				},
				onResize: function() {
				}
			}
		);
		
// Виджет: список, начитываемый из DataApi
		dojo.declare(
			'g740.ListRowSet',
			g740._ListAbstract,
			{
				fieldName: '',
				objRowSet: null,
				constructor: function(para, domElement) {
					if (!para) para={};
					if (para.objRowSet) this.objRowSet=para.objRowSet;
					if (para.fieldName) this.fieldName=para.fieldName;
					if (para.isAddEmptyItem) this.isAddEmptyItem=para.isAddEmptyItem;
				},
				destroy: function() {
					this.objRowSet=null;
					this.fieldName='';
					this.inherited(arguments);
				},
				set: function(name, value) {
					if (name=='fieldName') {
						if (this.fieldName!=value) {
							this.fieldName=value;
							this.render();
						}
						return true;
					}
					if (name=='objRowSet') {
						if (this.objRowSet!=value) {
							this.objRowSet=value;
							this.render();
						}
						return true;
					}
					this.inherited(arguments);
				},
				getItems: function() {
					var result=[];
					if (!this.objRowSet) return result;
					if (this.objRowSet.g740className!='g740.RowSet') return result;
					if (this.objRowSet.isObjectDestroed) return result;
					var objTreeStorage=this.objRowSet.objTreeStorage;
					if (!objTreeStorage) return result;
					if (objTreeStorage.isObjectDestroed) return result;
					
					if (this.isAddEmptyItem) result.push({id: '', value: '---//---'});
					
					var nodes=objTreeStorage.getChildsOrdered(objTreeStorage.rootNode);
					var filter='';
					if (this.filter) filter=this.filter.toLowerCase();
					for (var i=0; i<nodes.length; i++) {
						var node=nodes[i];
						if (!node) continue;
						var row=node.info;
						if (!row) continue;
						var fields=this.objRowSet.getFields(node);
						var fld=fields[this.fieldName];
						if (!fld) continue;
						var value=g740.convertor.js2text(row[this.fieldName+'.value'],fld.type);
						if (filter) {
							if (value.toLowerCase().indexOf(filter)<0) continue;
						}
						var item={id: node.id, value: value};
						result.push(item);
					}
					return result;
				}
			}
		);

// Виджет: список, начитываемый из строки, разделенной ;
		dojo.declare(
			'g740.ListItems',
			g740._ListAbstract,
			{
				g740list: '',
				baseType: 'string',
				isAddEmptyItem: false,
				constructor: function(para, domElement) {
					if (!para) para={};
					if (para.list) this.g740list=para.list;
				},
				set: function(name, value) {
					if (name=='list') {
						if (this.g740list!=value) {
							this.g740list=value;
							if (typeof(this.g740list)!='string') this.g740list='';
							this.render();
						}
						return true;
					}
					if (name=='baseType') {
						if (this.baseType!=value) {
							if (value!='string' && value!='num') g740.systemError('g740.ListItems.set(baseType)',errorIncorrectValue, value);
							this.baseType=value;
						}
						return true;
					}
					this.inherited(arguments);
				},
				getItems: function() {
					var result=[];
					var lst=this.g740list.split(';');
					
					if (this.isAddEmptyItem) {
						if (this.baseType=='string') {
							var item={id: '', value: '---//---'};
						}
						if (this.baseType=='num') {
							var item={id: 0, value: '---//---'};
						}
						result.push(item);
					}
					
					var filter='';
					if (this.filter) filter=this.filter.toLowerCase();
					for (var i=0; i<lst.length; i++) {
						if (!lst[i]) continue;

						var value=lst[i];
						if (filter) {
							if (value.toLowerCase().indexOf(filter)<0) continue;
						}
						
						if (this.baseType=='string') {
							var item={id: lst[i], value: value};
						}
						if (this.baseType=='num') {
							var item={id: i+1, value: value};
						}
						result.push(item);
					}
					return result;
				}
			}
		);
		
// Класс MenuBar
		dojo.declare(
			'g740.MenuBar',
			dijit.MenuBar,
			{
			    g740className: 'g740.MenuBar',			// Имя базового класса
			    isObjectDestroed: false,				// Признак - объект уничтожен
			    connectedUser: '',
				constructor: function (para, domElement) {
			        var procedureName = 'g740.MenuBar.constructor';
			        try {
			        }
			        finally {
			        }
			    },
				destroy: function () {
			        var procedureName = 'g740.MenuBar.destroy';
			        try {
			            this.isObjectDestroed = true;
			            this.inherited(arguments);
			        }
			        finally {
			        }
			    },
				postCreate: function() {
					this.inherited(arguments);
					if (this.connectedUser) {
						var domNodeConnectedUser=document.createElement('div');
						domNodeConnectedUser.className='connecteduser';
						domNodeConnectedUser.setAttribute('title',g740.getMessage('disconnect'));
						this.domNode.appendChild(domNodeConnectedUser);
						
						var domNodeLabel=document.createElement('div');
						domNodeLabel.className='label';
						var txt=document.createTextNode(this.connectedUser);
						domNodeLabel.appendChild(txt);
						domNodeConnectedUser.appendChild(domNodeLabel);

						var domNodeExit=document.createElement('div');
						domNodeExit.className='icon';
						domNodeConnectedUser.appendChild(domNodeExit);
						dojo.on(domNodeConnectedUser, 'click', function() {
							var objOwner=this;
							while(true) {
								if (objOwner.className=='g740.Form') break;
								if (!objOwner.getParent) break;
								objOwner=objOwner.getParent();
							}
							g740.request.send({
								objOwner: objOwner,
								arrayOfRequest:['<request name="disconnect"/>'],
								requestName: 'disconnect'
							});
						});
					}
				},
			    doG740Repaint: function (para) {
			        // Перерисовываем детей
			        var lst = this.getChildren();
			        for (var i = 0; i < lst.length; i++) {
			            var obj = lst[i];
			            if (!obj) continue;
			            if (!obj.doG740Repaint) continue;
			            obj.doG740Repaint(para);
			        }
			    }
			}
		);
// Класс Toolbar
		dojo.declare(
			'g740.Toolbar',
			dijit.Toolbar,
			{
				g740className: 'g740.Toolbar',			// Имя базового класса
				isObjectDestroed: false,				// Признак - объект уничтожен
				g740size: 'small',
				set: function(name, value) {
					if (name=='g740size') {
						if (value!='large' && value!='medium' && value!='small') value=g740.config.iconSizeDefault;
						this.g740size=value;
						return true;
					}
					this.inherited(arguments);
				},
				destroy: function() {
					var procedureName='g740.Toolbar.destroy';
					try {
						this.isObjectDestroed=true;
						this.inherited(arguments);
					}
					finally {
					}
				},
				postCreate: function() {
					this.inherited(arguments);
					if (this.g740size=='large') dojo.addClass(this.domNode,'g740large');
					if (this.g740size=='medium') dojo.addClass(this.domNode,'g740medium');
					if (this.g740size=='small') dojo.addClass(this.domNode,'g740small');
				},
				doG740Repaint: function(para) {
					// Перерисовываем детей
					var lst=this.getChildren();
					for (var i=0; i<lst.length; i++) {
						var obj=lst[i];
						if (!obj) continue;
						if (!obj.doG740Repaint) continue;
						obj.doG740Repaint(para);
					}
				}
			}
		);
// Класс ToolbarButton - кнопка в Toolbar
		dojo.declare(
			'g740.ToolbarButton',
			dijit.form.Button,
			{
				g740className: 'g740.ToolbarButton',	// Имя базового класса
				objAction: null,
				g740size: 'small',
				set: function(name, value) {
					if (name=='objAction') {
						this.objAction=value;
						if (this.objAction.label) this.set('label',this.objAction.label.toHtml());
						if (this.objAction.iconClass) this.set('iconClass',this.objAction.getIconClass(this.g740size));
						return true;
					}
					if (name=='focused') {
						if (value) if (this.focusedNode) this.focusedNode.focus();
						return true;
					}
					if (name=='g740size') {
						if (value!='large' && value!='medium') value='small';
						this.g740size=value;
						return true;
					}
					this.inherited(arguments);
				},
				destroy: function() {
					var procedureName='g740.ToolbarButton.destroy';
					try {
						if (this.objAction) {
							this.objAction.destroy();
							this.objAction=null;
						}
						this.inherited(arguments);
					}
					finally {
					}
				},
				postCreate: function() {
					if (this.domNode) {
						for(var i=0; i<this.domNode.childNodes.length; i++) {
							var child=this.domNode.childNodes[i];
							if (child.nodeName=='INPUT') {
								this.focusedNode=child;
							}
						}
						if (this.showLabel) {
							dojo.attr(this.domNode, 'title', this.label);
						}
					}
					this.on('Click', this.onG740Click);
					this.on('KeyDown', this.onG740KeyDown);
					this.on('Focus', this.onG740Focus);
					this.on('Blur', this.onG740Blur);
					this.inherited(arguments);
					
				},
				doG740Repaint: function(para) {
					var isEnabled=false;
					if (this.objAction) isEnabled=this.objAction.getEnabled();
					this.set('disabled', !isEnabled);
					if (isEnabled) {
						this.set('disabled', false);
						if (!this.showLabel && this.objAction.label) this.set('label',this.objAction.label.toHtml());
					}
					else {
						this.set('disabled', true);
						if (!this.showLabel) this.set('label','');
					}
				},
				onG740Click: function() {
					if (this._isClickTimeout) return false;
					this._isClickTimeout=true;
					g740.execDelay.go({
						delay: 800,
						obj: this,
						func: this._setClickTimeoutOff
					});
					if (this.objAction) return this.objAction.exec();
					return false;
				},
				onG740KeyDown: function(e) {
					dojo.stopEvent(e);
				},
				onG740Focus: function() {
				},
				onG740Blur: function() {
				},
				_isClickTimeout: false,
				_setClickTimeoutOff: function() {
					this._isClickTimeout=false;
				}
			}
		);
		dojo.declare(
			'g740.PanelButton',
			g740.ToolbarButton,
			{
				g740className: 'g740.PanelButton',		// Имя базового класса
				onG740KeyDown: function(e) {
					if (!e.ctrlKey && e.keyCode==13) {
						dojo.stopEvent(e);
						this.onClick();
					}
					else {
						dojo.stopEvent(e);
					}
				},
				resize: function(size) {
					if (size && size.h) size.h=parseInt(size.h);
					this.inherited(arguments);
				},
				onG740Focus: function() {
					if (!dojo.hasClass(this.domNode,'g740focused')) dojo.addClass(this.domNode,'g740focused');
				},
				onG740Blur: function() {
					if (dojo.hasClass(this.domNode,'g740focused')) dojo.removeClass(this.domNode,'g740focused');
				},
			}
		);

		dojo.declare(
			'g740.ToolbarComboButton',
			dijit.form.DropDownButton,
			{
				g740className: 'g740.ToolbarComboButton',	// Имя базового класса
				objAction: null,
				g740size: 'small',
				set: function(name, value) {
					if (name=='objAction') {
						this.objAction=value;
						if (this.objAction.label) this.set('label',this.objAction.label.toHtml());
						if (this.objAction.iconClass) this.set('iconClass',this.objAction.getIconClass(this.g740size));
						return true;
					}
					if (name=='g740size') {
						if (value!='large' && value!='medium') value='small';
						this.g740size=value;
						return true;
					}
					this.inherited(arguments);
				},
				constructor: function(para, domElement) {
					var procedureName='g740.ToolbarComboButton.constructor';
					try {
						this.on('Click', this.onG740Click);
					}
					finally {
					}
				},
				destroy: function() {
					var procedureName='g740.ToolbarComboButton.destroy';
					try {
						if (this.objAction) {
							this.objAction.destroy();
							this.objAction=null;
						}
						this.inherited(arguments);
					}
					finally {
					}
				},
				doG740Repaint: function(para) {
					//var isEnabled=false;
					//if (this.objAction) isEnabled=this.objAction.getEnabled();
					//this.set('disabled', !isEnabled);
				},
				_onDropDownClick: function(e) {
					if (this.dropDown) {
						var lst=this.dropDown.getChildren();
						for (var i=0; i<lst.length; i++) {
							var obj=lst[i];
							if (obj.doG740Repaint) {
								obj.doG740Repaint({});
							}
						}
					}
					this.inherited(arguments);
				},
				onG740Click: function() {
					if (this.objAction) return this.objAction.exec();
					return false;
				}
			}
		);
		
		
// Класс выпадающего по правой кнопке меню
		dojo.declare(
			'g740.Menu',
			dijit.Menu,
			{
				g740className: 'g740.Menu',
				postCreate: function() {
					this.inherited(arguments);
					this.on('Focus', this.onG740Show);
				},
				doG740Repaint: function(para) {
					// Перерисовываем детей
					var lst=this.getChildren();
					for (var i=0; i<lst.length; i++) {
						var obj=lst[i];
						if (!obj) continue;
						if (!obj.doG740Repaint) continue;
						obj.doG740Repaint(para);
					}
				},
				onG740Show: function() {
					this.doG740Repaint();
				}
			}
		);
		dojo.declare(
			'g740.PopupMenuItem',
			dijit.PopupMenuItem,
			{
				g740className: 'g740.PopupMenuItem',
				objAction: null,
				set: function(name, value) {
					if (name=='objAction') {
						this.objAction=value;
						if (this.objAction.label) this.set('label',this.objAction.label.toHtml());
						if (this.objAction.iconClass) this.set('iconClass',this.objAction.iconClass);
						return true;
					}
					this.inherited(arguments);
				},
				destroy: function() {
					var procedureName='g740.PopupMenuItem.destroy';
					try {
						if (this.objAction) {
							this.objAction.destroy();
							this.objAction=null;
						}
						this.inherited(arguments);
					}
					finally {
					}
				}
			}
		);
		
		dojo.declare(
			'g740.MenuItem',
			dijit.MenuItem,
			{
				g740className: 'g740.MenuItem',			// Имя базового класса
				objAction: null,
				set: function(name, value) {
					if (name=='objAction') {
						this.objAction=value;
						if (this.objAction.label) this.set('label',this.objAction.label.toHtml());
						if (this.objAction.iconClass) this.set('iconClass',this.objAction.iconClass);
						return true;
					}
					this.inherited(arguments);
				},
				postCreate: function() {
					this.inherited(arguments);
					this.on('Click', this.onG740Click);
				},
				destroy: function() {
					if (this.objAction) {
						this.objAction.destroy();
						this.objAction=null;
					}
					this.inherited(arguments);
				},
				doG740Repaint: function(para) {
					var isEnabled=false;
					if (this.objAction) isEnabled=this.objAction.getEnabled();
					this.set('disabled', !isEnabled);
				},
				onG740Click: function() {
					if (this.objAction) return this.objAction.exec();
					return false;
				}
			}
		);
		
		dojo.declare(
			'g740.PopupMenuBarItem',
			dijit.PopupMenuBarItem,
			{
			    g740className: 'g740.PopupMenuBarItem',
			    objAction: null,
			    set: function (name, value) {
			        if (name == 'objAction') {
			            this.objAction = value;
			            if (this.objAction.label) this.set('label', this.objAction.label.toHtml());
			            if (this.objAction.iconClass) this.set('iconClass', this.objAction.iconClass);
			            return true;
			        }
			        this.inherited(arguments);
			    },
			    destroy: function () {
			        var procedureName = 'g740.PopupMenuBarItem.destroy';
			        try {
			            if (this.objAction) {
			                this.objAction.destroy();
			                this.objAction = null;
			            }
			            this.inherited(arguments);
			        }
			        finally {
			        }
			    },
			    doG740Repaint: function (para) {
					if (this.popup) {
						var lst=this.popup.getChildren();
						for (var i=0; i<lst.length; i++) {
							var obj=lst[i];
							if (obj.doG740Repaint) {
								obj.doG740Repaint(para);
							}
						}
					}
			    }
			}
		);
		dojo.declare(
			'g740.MenuBarItem',
			dijit.MenuBarItem,
			{
			    g740className: 'g740.MenuBarItem',			// Имя базового класса
			    objAction: null,
			    set: function (name, value) {
			        if (name == 'objAction') {
			            this.objAction = value;
			            if (this.objAction.label) this.set('label', this.objAction.label.toHtml());
			            if (this.objAction.iconClass) this.set('iconClass', this.objAction.iconClass);
			            return true;
			        }
			        this.inherited(arguments);
			    },
			    postCreate: function () {
			        this.inherited(arguments);
			        this.on('Click', this.onG740Click);
			    },
			    destroy: function () {
			        if (this.objAction) {
			            this.objAction.destroy();
			            this.objAction = null;
			        }
			        this.inherited(arguments);
			    },
			    doG740Repaint: function (para) {
			        var isEnabled = false;
			        if (this.objAction) isEnabled = this.objAction.getEnabled();
			        this.set('disabled', !isEnabled);
			    },
			    onG740Click: function () {
			        if (this.objAction) return this.objAction.exec();
			        return false;
			    }
			}
		);
		
		dojo.declare(
			'g740.Action',
			null,
			{
				g740className: 'g740.Action',			// Имя базового класса
				objForm: null,							// Ссылка на экранную форму
				rowsetName: '',							// Ссылка на имя набора строк
				request: {},							// Описание запроса
				label: '',
				icon: 'default',
				iconClass: '',
				constructor: function(para) {
					var procedureName='g740.Action.constructor';
					try {
						if (!para) para={};
						if (para.objForm) this.objForm=para.objForm;
						if (para.rowsetName) this.rowsetName=para.rowsetName;
						if (para.request) this.request=para.request;
						
						if (this.request.name=='delete' && !this.request.confirm) this.request.confirm=g740.getMessage('messageConfirmDelete');
						
						var label=this.request.caption;
						var icon=this.request.icon;
						
						if (this.request.name=='connect') this.rowsetName='#form';
						if (this.request.name=='disconnect') this.rowsetName='#form';
						if (this.request.name=='close') this.rowsetName='#form';
						if (this.request.name=='result') this.rowsetName='#form';
						if (this.request.name=='form') this.rowsetName='#form';
						if (this.request.name=='httpget') this.rowsetName='#form';
						if (this.objForm) {
							var fullName=this.request.name;
							if (this.request.mode) fullName+='.'+this.request.mode;
							var r=this.objForm.requests[fullName];
							if (r) this.rowsetName='#form';
						}
						if (!this.rowsetName) this.rowsetName='#focus';

						if (this.rowsetName=='#focus') {
							var info=g740.rowsetRequestInfo[this.request.name];
							if (info) {
								if (info.mode && info.mode[this.request.mode]) info=info.mode[this.request.mode];
								if (!label) {
									if (info.captionId) label=g740.getMessage(info.captionId);
								}
							}
						}
						else if (this.rowsetName=='#form') {
							if (this.objForm) {
								var fullName=this.request.name;
								if (this.request.mode) fullName+='.'+this.request.mode;
								var r=this.objForm.requests[fullName];
								if (r) {
									if (!label) label=r.caption;
									if (!icon) icon=r.icon;
								}
							}
						} else {
							var objRowSet=this.getObjRowSet();
							if (objRowSet) {
								var r=objRowSet.getRequestForAnyNodeType(this.request.name, this.request.mode);
								if (r) {
									if (!label) label=r.caption;
									if (!icon) icon=r.icon;
								}
							}
						}
						
						if (!label) label=this.request.name;
						if (!icon) {
							if (this.request.mode) {
								icon=this.request.name+'.'+this.request.mode;
								if (!g740.icons.getIconClassName(icon)) icon='';
							}
							if (!icon) icon=this.request.name;
						}
						
						this.label=label;
						this.icon=icon;
						this.iconClass=g740.icons.getIconClassName(icon);
						if (!this.iconClass) {
							this.iconClass=g740.icons.getIconClassName('default');
							this.icon='default';
						}
					}
					finally {
					}
				},
				destroy: function() {
					var procedureName='g740.ToolbarButton.destroy';
					try {
						this.objForm=null;
						this.request={};
						this.inherited(arguments);
					}
					finally {
					}
				},
				getRequestAttr: function() {
					var result={
						objForm: this.objForm,
						rowsetName: this.rowsetName,
						save: this.request.save,
						close: this.request.close
					};
					if (this.request.name=='form') {
						result['modal']=this.request.modal;
						result['closable']=this.request.closable;
						result['width']=this.request.width;
						result['height']=this.request.height;
						result['onclose']=this.request.onclose;
					}
					if (this.request.name=='httpget') {
						result['url']=this.request.url;
					}
					return result;
				},
				getEnabled: function() {
					if (this.request.name=='clipboard') {
						try {
							if (this.request.mode=='paste') return false;
							return document.queryCommandSupported(this.request.mode);
						}
						catch (e) {
						}
						return false;
					}
					
					if (!this.objForm) return false;
					var obj=this.getObjRowSet();
					if (!obj) obj=this.objForm;
					if (!obj) return false;
					if (!obj.getRequestEnabled) return false;
					if (this.request.js_enabled) {
						var isEnabled=g740.js_eval(obj, this.request.js_enabled, true);
						if (!isEnabled) return false;
						if (isEnabled=='0') return false;
					}
					if (this.request.name=='connect' || this.request.name=='disconnect' || this.request.name=='form') {
						if (this.request.name=='form' && !this.request.mode) return false;
						return true;
					}
					if (!this.request.name && this.request.exec) return true;
					result=obj.getRequestEnabled(this.request.name, this.request.mode);
					return result;
				},
				exec: function(isNoConfirm) {
					if (!this.getEnabled()) return false;
					if (this.request.name=='clipboard') {
						try {
							document.execCommand(this.request.mode);
						}
						catch(e) {
						}
						return true;
					}
					
					var obj=this.objForm;
					var objRowSet=this.getObjRowSet();
					if (objRowSet) {
						var objFocusedRowSet=this.objForm.getFocusedRowSet();
						if (objFocusedRowSet && objFocusedRowSet!=objRowSet && !objFocusedRowSet.isFilter && objFocusedRowSet.getExistUnsavedChanges()) {
							if (!objFocusedRowSet.exec({requestName: 'save'})) return false;
						}
						obj=objRowSet;
					}
					if (!obj) return false;

					if (this.request.save) {
						var objFocusedRowSet=this.objForm.getFocusedRowSet();
						if (objFocusedRowSet && !objFocusedRowSet.isFilter && objFocusedRowSet.getExistUnsavedChanges()) {
							if (!objFocusedRowSet.exec({requestName: 'save'})) return false;
						}
					}
					
					var confirm = '';
					if (!isNoConfirm) {
						confirm = this.request.confirm;
						if (!confirm && obj.getRequest) {
							var r = obj.getRequest(this.request.name, this.request.mode);
							if (r && r.confirm) confirm = r.confirm;
						}
					}
					if (confirm) {
						var objOwner=null;
						if (this.objForm) objOwner=this.objForm.objFocusedPanel;
						g740.showConfirm({
							messageText: confirm,
							closeObj: this,
							onCloseOk: this.exec,
							closePara: true,
							objOwner: objOwner
						});
						return true;
					}
					var G740params={};
					if (obj._getRequestG740params) {
						var ppp=obj._getRequestG740params(this.request.params);
						for(var name in ppp) G740params[name]=ppp[name];
					}
					var result=obj.exec({
						requestName: this.request.name, 
						requestMode: this.request.mode,
						G740params: G740params,
						attr: this.getRequestAttr()
					});
					return result;
				},
				getIconClass: function(size) {
					return g740.icons.getIconClassName(this.icon, size);
				},
				getObjRowSet: function() {
					var result=null;
					if (!this.objForm) return null;
					if (this.objForm.isObjectDestroed) return null;
					if (!this.rowsetName) return null;
					if (this.rowsetName=='#focus') {
						result=this.objForm.getFocusedRowSet();
					}
					else {
						var result=this.objForm.rowsets[this.rowsetName];
					}
					if (result && result.isObjectDestroed) result=null;
					return result;
				}
			}
		);
		
		return g740;
	}
);