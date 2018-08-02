//-----------------------------------------------------------------------------
// Панель Grid
//-----------------------------------------------------------------------------
define(
	[],
	function () {
	    if (typeof (g740) == 'undefined') g740 = {};

	    // Класс Grid
	    dojo.declare(
			'g740.Grid',
			[dojox.grid.DataGrid, g740._PanelAbstract],
			{
			    isG740Grid: true,
				isG740Clipboard: true,
				objRowSet: null,
			    g740structure: null,
			    objActionOnDblClick: null,
			    constructor: function (para, domElement) {
			        var procedureName = 'g740.Grid.constructor';
					this.objForm = para.objForm;
					this.rowsetName = para.rowsetName;
					this.g740structure = para.structure;
					if (this.objForm && this.objForm.rowsets) {
						this.objRowSet = this.objForm.rowsets[this.rowsetName];
						if (this.objRowSet) {
							this.set('store', this.objRowSet.getDataApi());
						}
					}
					this.objActionOnDblClick = para.objActionOnDblClick;

					this.set('selectionMode', 'single');
					this.set('escapeHTMLInData', true);
					this.set('rowSelector', false);
					this.set('elasticView', false);
					this.set('rowsPerPage', 10000);

					this.on('CellFocus', this.onG740CellFocus);
					this.on('CellDblClick', this.onG740CellDblClick);
					this.on('KeyDown', this.onG740KeyDown);
					this.on('Focus', this.onG740Focus);
					this.on('Blur', this.onG740Blur);
					this.on('StyleRow', this.onG740StyleRow);

					//console.log(this, para);
			    },

			    // Вытаскиваем контекстное меню из описаний верхних панелей
			    onRowContextMenu: function (e) {
			        if (!this.objMenu) {
			            this.objMenu = this.getParentMenu();
			            if (this.objMenu && this.objMenu.bindDomNode) this.objMenu.bindDomNode(this.domNode);
			        }
			        return this.objMenu;
			    },
			    onHeaderContextMenu: function (e) {
			        if (!this.objMenu) {
			            this.objMenu = this.getParentMenu();
			            if (this.objMenu && this.objMenu.bindDomNode) this.objMenu.bindDomNode(this.domNode);
			        }
			        return this.objMenu;
			    },

			    destroy: function () {
			        var procedureName = 'g740.Grid.destroy';
					if (this.objActionOnDblClick) {
						this.objActionOnDblClick.destroy();
						this.objActionOnDblClick = null;
					}
					this.objRowSet = null;
					this.set('store', null);
					this.inherited(arguments);
			    },
			    canEdit: function (objCell, rowIndex) {
			        this.inherited(arguments);
			        if (!this.objRowSet) return false;
			        if (this.objRowSet.isObjectDestroed) return false;
			        if (!objCell) return false;
			        if (objCell.request) return false;
			        if (this.objActionOnDblClick) return false;
			        var node = this.getItem(rowIndex);
			        if (this.objRowSet.getFocusedNode() != node) return false;
			        var fieldName = objCell.field;
			        var fields = this.objRowSet.getFields(node);
			        var fld = fields[fieldName];
			        if (!fld) return false;
			        if (fld.refid) return false;
			        if (fld.type == 'check') return false;
			        if (fld.type == 'memo') return false;
			        if (fld.type == 'list') return false;
			        if (fld.type == 'icons') return false;
					if (fld.type == 'radio') return false;
			        var isReadOnly = this.objRowSet.getFieldProperty({ fieldName: fieldName, propertyName: 'readonly' });
			        return !isReadOnly;
			    },
			    getCellCount: function () {
			        var result = 0;
			        if (this.layout) result = this.layout.cellCount;
			        return result;
			    },
			    isRepainted: false,
			    doG740Repaint: function (para) {
			        var procedureName = 'g740.Grid.doG740Repaint';
			        if (!this.objRowSet) return false;
			        if (!this.objRowSet.isObjectDestroed && this.objRowSet.isEnabled) {
			            if (dojo.hasClass(this.domNode, 'dojoxGridRowSetDisabled')) dojo.removeClass(this.domNode, 'dojoxGridRowSetDisabled');
			        } else {
			            if (!dojo.hasClass(this.domNode, 'dojoxGridRowSetDisabled')) {
			                dojo.addClass(this.domNode, 'dojoxGridRowSetDisabled');
			                this.render();
			            }
			        }
			        if (this.objRowSet.isObjectDestroed) return false;
			        if (!para) para = {};
			        if (para.objRowSet && para.objRowSet.name != this.rowsetName) return true;
			        var oldRepainted = this.isRepainted;
			        this.isRepainted = true;
			        try {
			            var isFocused = this.focused;
			            // Полная перерисовка
			            if (para.isFull) {
			                this.doG740RepaintVisible();
			                var scrollTop = this.scrollTop;
			                this.render();
			                this.scrollTo(scrollTop);
			            }
			            var rowIndex = -1;
						var node = this.objRowSet.getFocusedNode();
			            if (!para.isFull && para.isRowUpdate && para.node && para.node!=node) {
							// Отдельно отрабатывем запрос на перерисовку не текущей явно указанной строки
							var rowIndex=this.getItemIndex(para.node);
							this.updateRow(rowIndex);
						}
						else {
							if (node) rowIndex = this.getItemIndex(node);
							var cellIndex = 0;
							var fieldName = '';
							if (this.focus.cell) {
								cellIndex = this.focus.cell.index;
								fieldName = this.focus.cell.field;
							}
							if (!para.isFull && para.isRowUpdate) {
								this.updateRow(rowIndex);
							}
							this.doG740ScrollToRow(rowIndex);
							if (isFocused) {
								this.focus.focusGrid();
								this.focus.setFocusIndex(rowIndex, cellIndex);
							}
							g740.execDelay.go({
								delay: 10,
								obj: this,
								func: this.doG740SetSelectionDelay,
								para: {
									rowIndex: rowIndex
								}
							});
						}
			        }
			        finally {
			            this.isRepainted = oldRepainted;
			        }
			    },

				getRowNode: function(rowIndex) {
					if (!this.focus) return null;
					return this.inherited(arguments);
				},
			    
				_isFirstG740RepaintVisible: true,
				doG740RepaintVisible: function (para) {
			        var procedureName = 'g740.Grid.doG740RepaintVisible';
			        if (!this.g740structure) return false;
			        if (this.g740structure.length == 0) return false;
			        if (!this._isFirstG740RepaintVisible && !this.g740structure[0].isJsVisible) return true;
			        if (!this.layout) return false;
			        if (!this.layout.cells) return false;

			        var fieldsOld = {};
			        var cells = this.layout.cells;
			        for (var i = 0; i < cells.length; i++) {
			            var cell = cells[i];
			            fieldsOld[cell.field] = true;
			        }

			        var fieldsNew = {};
			        var cellsNew = [];
			        var cells = this.g740structure[0].cells;
			        if (!cells) return false;
			        for (var i = 0; i < cells.length; i++) {
			            var cell = cells[i];
			            if (cell['js_visible']) {
			                var visible = g740.js_eval(this.objRowSet, cell['js_visible'], true);
			                if (!visible) continue;
			            }
			            fieldsNew[cell.field] = true;
			            cellsNew.push(cell);
			        }

			        var isVisibleChanged = false;
			        for (var fieldName in fieldsOld) {
			            if (!fieldsNew[fieldName]) {
			                isVisibleChanged = true;
			                break;
			            }
			        }
			        for (var fieldName in fieldsNew) {
			            if (!fieldsOld[fieldName]) {
			                isVisibleChanged = true;
			                break;
			            }
			        }

			        if (this._isFirstG740RepaintVisible && this.domNode.offsetWidth>0) {
						// Первый раз всегда перестраиваем - для пересчета stretch полей
						this._isFirstG740RepaintVisible=false;
						isVisibleChanged=true;
					}
					if (isVisibleChanged) {
			            var structure = [
							{
							    defaultCell: this.g740structure[0].defaultCell,
							    cells: this.doG740RebuildCellsForStretch(cellsNew)
							}
			            ];
			            this.set('structure', structure);
			        }
			    },
				doG740RebuildCellsForStretch: function(cells) {
					var width=this.domNode.offsetWidth-25;
					var result=cells;
					if (!width) return result;
					var w=0;
					var nStretch=0;
					for(var i=0; i<result.length; i++) {
						var cell=result[i];
						if (!cell) continue;
						var fldDef=cell.fldDef;
						if (!fldDef) continue;
						var len = fldDef.len;
						if (!len) len = 10;
						w+=(len*g740.config.charwidth)+8;
						if (fldDef.stretch) nStretch++;
					}
					if (!nStretch) return result;
					if (w>=width) return result;
					for(var i=0; i<result.length; i++) {
						var cell=result[i];
						if (!cell) continue;
						var fldDef=cell.fldDef;
						if (!fldDef) continue;
						if (!fldDef.stretch) continue;
						var delta=parseInt((width-w)/nStretch);
						nStretch--;
						w+=delta;
						var len = fldDef.len;
						if (!len) len = 10;
						cell.width=(len*g740.config.charwidth+delta)+'px';
					}
					return result;
				},
				canFocused: function() {
					return true;
				},
			    doG740Focus: function () {
			        var objParent = this.getParent();
			        if (objParent && objParent.doG740SelectChild) objParent.doG740SelectChild(this);
			        var cellIndex = 0;
			        if (this.focus.cell) cellIndex = this.focus.cell.index;
			        var rowIndex = -1;
			        var node = this.objRowSet.getFocusedNode();
			        if (node) rowIndex = this.getItemIndex(node);
			        this.focus.focusGrid();
			        this.focus.setFocusIndex(rowIndex, cellIndex);
			    },
				doG740ScrollToRow: function (rowIndex) {
					var domItem = this.getRowNode(rowIndex);
			        if (!domItem) return false;
			        var y = this.scrollTop;

					var h = this.scroller.windowHeight;
					if (h>60) {
						var dH=0;
						dH=parseInt(h*0.1);
						if (dH<25) dH=25;
						h=h-dH;
					}
					
					var isScroll=false;
					if (domItem.offsetTop < y) {
						y=parseInt(domItem.offsetTop);
						isScroll=true;
					}
			        if ((domItem.offsetTop + domItem.offsetHeight) > (y + h)) {
			            y = parseInt(domItem.offsetTop + domItem.offsetHeight - h);
						isScroll=true;
			        }
					if (y < 0) {
						y = 0;
						isScroll=true;
					}
					if (isScroll) this.scrollTo(y);
			    },
			    doG740SetSelectionDelay: function (para) {
			        this.selection.clear();
			        this.selection.setSelected(para.rowIndex, true);
			    },
			    isOnCellFocused: false,
			    onG740CellFocus: function (objCell, rowIndex) {
			        if (!this.objRowSet) return false;
			        if (this.objRowSet.isObjectDestroed) return false;
			        if (!this.objForm) return false;
			        if (this.objForm.getFocusedRowSet() != this.objRowSet) return false;

			        var cellIndex = 0;
			        if (objCell) cellIndex = objCell.index;
			        if (!this.isOnCellFocused) {
			            this.isOnCellFocused = true;
			            try {
			                if (!this.isRepainted) {
			                    var isOk = false;
			                    var newNode = this.getItem(rowIndex);
			                    var oldNode = this.objRowSet.getFocusedNode();

			                    // При смене фокуса принудительно закрываем редактор, сохраняя содержимое
			                    if (newNode == oldNode) this.edit.apply();

			                    if (this.objRowSet.objTreeStorage.isNode(newNode) && !newNode.isObjectDestroed && newNode != oldNode) {
			                        var isOk = this.objRowSet.setFocusedNode(newNode);
			                    }
			                    if (!isOk) {
			                        var rowIndex = this.getItemIndex(oldNode);
			                        this.focus.setFocusIndex(rowIndex, cellIndex);
			                    }
			                    this.selection.clear();
			                    this.selection.setSelected(rowIndex, true);
			                }
			            }
			            finally {
			                this.isOnCellFocused = false;
			            }
			        }
			        else {
			            //						this.selection.clear();
			            //						this.selection.setSelected(rowIndex, true);
			        }
			    },
			    // Перехватываем сохранение отредактированного поля, если редактируем через виджет, то сохранять значение не надо, виджет сам все сохранит как надо
			    doApplyCellEdit: function (newValue, rowIndex, fieldName) {
			        this.inherited(arguments);
			        this.updateRow(rowIndex);
			        g740.execDelay.go({
			            delay: 10,
			            obj: this,
			            func: this.doG740SetSelectionDelay,
			            para: {
			                rowIndex: rowIndex
			            }
			        });

			        if (!this.objRowSet) return false;
			        if (this.objRowSet.isObjectDestroed) return false;
			        var objCell = null;
			        var cellCount = this.getCellCount();
			        for (var i = 0; i < cellCount; i++) {
			            var cell = this.getCell(i);
			            if (!cell) continue;
			            if (cell.field != fieldName) continue;
			            objCell = cell;
			            break;
			        }
			        return true;
			    },

			    get: function (rowIndex, node) {
			        if (this.grid && this.grid.objRowSet && !this.grid.objRowSet.isObjectDestroed && node && this.field) {
			            var objRowSet = this.grid.objRowSet;
			            var result = '';
			            var fields = objRowSet.getFields(node);
			            var fld = fields[this.field];
			            var row = node.info;
			            if (fld && row) {
			                result = row[this.field + '.value'];
			                if (typeof (result) == 'string') result = result.replaceAll('"', "'");
			                if (fld.type == 'memo' && typeof (result) == 'string') {
			                    result = result.split("\n").join(' ');
			                    if (result.length > 128) result = result.substr(0, 128) + ' ...';
			                }
							if (fld.type=='num' && fld.dec) {
								var value=parseFloat(result);
								if (isNaN(value)) value=0;
								result=value.toFixed(fld.dec);
							}
			            }
			            return result;
			        }
			        else {
			            return this.inherited(arguments);
			        }
			    },

			    doKeyEvent: function (e) {
			        if (e && e.type == 'keydown' && this.objRowSet && !this.objRowSet.isObjectDestroed) {
			            // Esc
			            if (e.keyCode == 27) {
			                this.objRowSet.undoUnsavedChanges();
			                dojo.stopEvent(e);
			                return true;
			            }
			            // Ctrl+Del
			            if (e.ctrlKey && e.keyCode == 46) {
			                this.objRowSet.execConfirmDelete();
			                dojo.stopEvent(e);
			                return true;
			            }
			            // Ins, Ctrl+Ins
			            if (e.keyCode == 45) {
			                this.objRowSet.exec({ requestName: 'append' });
			                dojo.stopEvent(e);
			                return true;
			            }
			            // F2
			            if (!e.ctrlKey && e.keyCode == 113) {
			                this.objRowSet.exec({ requestName: 'save' });
			                dojo.stopEvent(e);
			                return true;
			            }
			        }
					if (e && e.type == 'keydown') {
						if (!e.ctrlKey && !e.shiftKey && e.keyCode==9) {
							// Tab
							dojo.stopEvent(e);
							var objParent=this.getParent();
							if (objParent && objParent.doG740FocusChildNext) {
								g740.execDelay.go(
								{
									delay: 100,
									obj: objParent,
									func: objParent.doG740FocusChildNext,
									para: this
								});
							}
						}
						if (!e.ctrlKey && e.shiftKey && e.keyCode==9) {
							// Shift+Tab
							dojo.stopEvent(e);
							var objParent=this.getParent();
							if (objParent && objParent.doG740FocusChildPrev) {
								g740.execDelay.go(
								{
									delay: 100,
									obj: objParent,
									func: objParent.doG740FocusChildPrev,
									para: this
								});
							}
						}
					}
			        this.inherited(arguments);
			    },

			    onG740CellDblClick: function (e) {
			        if (!this.objRowSet) return false;
			        if (this.objRowSet == null) return false;
			        if (this.objRowSet.isObjectDestroed) return false;

			        if (this.objActionOnDblClick) {
			            this.objActionOnDblClick.exec();
			        }
			        else {
			            var rowIndex = e.rowIndex;
			            var objCell = e.cell;
			            var fieldName = objCell.field;
			            var gridNode = this.getItem(rowIndex);
			            var rowSetNode = this.objRowSet.getFocusedNode();

			            if (!gridNode) return false;
			            if (gridNode != rowSetNode) return false;
			            if (!fieldName) return false;
			            var fields = this.objRowSet.getFields(rowSetNode);
			            var fld = fields[fieldName];
			            if (!fld) return false;
			            if (objCell.request || fld.refid || fld.type == 'check' || fld.type == 'memo' || fld.type == 'list' || fld.type == 'icons' || fld.type == 'radio') this.doG740EditorClick();
			        }
			    },
			    onG740KeyDown: function (e) {
			        if (!this.objRowSet) return false;
			        if (this.objRowSet.isObjectDestroed) return false;
			        var objCell = this.focus.cell;
			        if (!objCell) return false;
			        var fieldName = objCell.field;
			        if (!fieldName) return false;
			        var rowSetNode = this.objRowSet.getFocusedNode();
			        if (!rowSetNode) return false;
			        var fields = this.objRowSet.getFields(rowSetNode);
			        var fld = fields[fieldName];
			        if (!fld) return false;
			        var cellIndex = objCell.index;
			        var rowIndex = this.focus.rowIndex;
			        // Space, Enter
			        if (!e.ctrlKey && e.keyCode == 13) {

			            if (this.objActionOnDblClick) {
			                this.objActionOnDblClick.exec();
			            } else if (objCell.request || fld.refid || fld.type == 'check' || fld.type == 'memo' || fld.type == 'list' || fld.type == 'icons' || fld.type == 'radio') {
			                this.doG740EditorClick();
			                dojo.stopEvent(e);
			            }
			        }
			    },
			    // Обработка обращения к внешнему редактору поля
			    doG740EditorClick: function () {
			        if (!this.objRowSet) return false;
			        if (this.objRowSet.isObjectDestroed) return false;
			        var objCell = this.focus.cell;
			        if (!objCell) return false;
					
					var fieldName = objCell.field;
					var fldDef=objCell._props['fldDef'];
					if (!fldDef) {
						var node = this.objRowSet.getFocusedNode();
						var fields = this.objRowSet.getFields(node);
						var fldDef = fields[fieldName];
					}
			        if (!fldDef) return false;
					if (objCell.readonly) return false;
			        var isReadOnly = this.objRowSet.getFieldProperty({
			            fieldName: fieldName,
			            propertyName: 'readonly'
			        });
			        if (isReadOnly) return false;
			        if (objCell.request) return this._doG740RequestClick();
			        if (fldDef.refid) return this._doG740EditorRefClick();
			        if (fldDef.type == 'check') return this._doG740EditorCheckClick();
			        if (fldDef.type == 'memo') return this._doG740EditorMemoClick();
			        if (fldDef.type == 'list' || fldDef.type == 'radio') return this._doG740EditorListClick();
			        if (fldDef.type == 'icons') return this._doG740EditorIconsClick();
			        return false;
			    },
			    // Обработка запроса request
			    _doG740RequestClick: function () {
			        var objCell = this.focus.cell;
			        if (!objCell) return false;
			        var request = objCell.request;
			        if (!request) return false;
			        if (request.name == 'form') {
			            var requestAttr = {
			                objForm: this.objForm,
			                rowsetName: this.rowsetName,
			                modal: request.modal,
			                width: request.width,
			                height: request.height,
			                onclose: request.onclose,
			                confirm: request.confirm
			            };
			            var G740params = this.objRowSet._getRequestG740params(request.params);
			            this.objForm.exec({
			                requestName: request.name,
			                requestMode: request.mode,
			                G740params: G740params,
			                attr: requestAttr
			            });
			        }
			        else {
			            var G740params = this.objRowSet._getRequestG740params(request.params);
			            this.objRowSet.exec({
			                requestName: request.name,
			                requestMode: request.mode,
			                G740params: G740params
			            });
			        }
			    },
			    // Обработка редактирования поля Check
			    _doG740EditorCheckClick: function () {
			        var objCell = this.focus.cell;
			        if (!objCell) return false;
			        var fieldName = objCell.field;
			        var value = this.objRowSet.getFieldProperty({ fieldName: fieldName });
			        this.objRowSet.setFieldProperty({
			            fieldName: fieldName,
			            value: !value
			        });
			        return true;
			    },
			    // Обработка редактирования поля Ref
			    _doG740EditorRefClick: function () {
			        var objCell = this.focus.cell;
			        if (!objCell) return false;
			        var fieldName = objCell.field;
			        var objDialog = new g740.DialogEditorRef(
						{
						    objForm: this.objForm,
						    rowsetName: this.rowsetName,
						    fieldName: fieldName,
						    domNodeOwner: objCell.getNode(this.focus.rowIndex),
						    objOwner: this,
						    duration: 0,
						    draggable: false
						}
					);
			        objDialog.show();
			    },
			    // Обработка редактирования поля Memo
			    _doG740EditorMemoClick: function () {
			        var objCell = this.focus.cell;
			        if (!objCell) return false;
			        var fieldName = objCell.field;
			        var objDialog = new g740.DialogEditorMemo(
						{
						    objForm: this.objForm,
						    rowsetName: this.rowsetName,
						    fieldName: fieldName,
						    domNodeOwner: objCell.getNode(this.focus.rowIndex),
						    objOwner: this,
						    duration: 0,
						    draggable: false
						}
					);
			        objDialog.show();
			    },
			    // Обработка редактирования поля Memo
			    _doG740EditorListClick: function () {
			        var objCell = this.focus.cell;
			        if (!objCell) return false;
			        var fieldName = objCell.field;
			        var objDialog = new g740.DialogEditorList(
						{
						    objForm: this.objForm,
						    rowsetName: this.rowsetName,
						    fieldName: fieldName,
						    domNodeOwner: objCell.getNode(this.focus.rowIndex),
						    objOwner: this,
						    duration: 0,
						    draggable: false
						}
					);
			        objDialog.show();
			    },
			    _doG740EditorIconsClick: function () {
			        var objCell = this.focus.cell;
			        if (!objCell) return false;
			        var objRowSet = this.objRowSet;
			        if (!objRowSet) return false;

					var fieldName = objCell.field;
					var fldDef=objCell._props['fldDef'];
					if (!fldDef) {
						var node = this.objRowSet.getFocusedNode();
						var fields = this.objRowSet.getFields(node);
						var fldDef = fields[fieldName];
					}
					if (!fldDef) return false;
					var list=objCell._props['list'];
					if (!list) return false;
					var baseType='string';
					if (fldDef.basetype=='num') baseType='num';
			        var value = this.objRowSet.getFieldProperty({fieldName: fieldName });

					if (baseType=='num') {
						value=g740.convertor.toJavaScript(value,'num');
						value++;
						if (value>list.length) {
							value=0;
							if (fldDef.notnull) value=1;
						}
						this.objRowSet.setFieldProperty({
							fieldName: fieldName,
							value: value
						});
					}
					else {
						var index=-1;
						for (var i=0; i<list.length; i++) {
							if (list[i]==value) {
								index=i;
								break;
							}
						}
						index++;
						var value='';
						if (index>=list.length) {
							index=-1;
							if (fldDef.notnull) index=0;
						}
						if (index>=0 && index<list.length) value=list[index];
							
						this.objRowSet.setFieldProperty({
							fieldName: fieldName,
							value: value
						});
					}
					return true;
/*

					console.log(objCell);

			        var fieldName = objCell.field;
			        var node = objRowSet.getFocusedNode();
			        var fields = objRowSet.getFields(node);
			        if (!fields) return false;
			        var fldDef = fields[fieldName];
			        if (!fldDef) return false;
			        if (!fldDef.list) return false;
			        var lst = fldDef.list.split(';');
			        return true;
*/
			    },

			    onG740Focus: function () {
			        if (this.objForm) this.objForm.onG740ChangeFocusedPanel(this);
			        dojo.addClass(this.domNode, 'g740-grid-focused');
			        return true;
			    },
			    onG740Blur: function () {
			        dojo.removeClass(this.domNode, 'g740-grid-focused');
			        return true;
			    },
			    onG740StyleRow: function (row) {
			        var node = this.getItem(row.index);
			        var objRowSet = this.objRowSet;
			        if (objRowSet && node && node.info) {
			            var rowClassName = 'dojoxGridRow';
			            if (row.selected) rowClassName = 'dojoxGridRowSelected';
			            if ((row.index % 2) == 1) rowClassName += ' dojoxGridRowOdd';
			            if (node.info['row.mark']) {
			                rowClassName += ' g740-mark';
			            } else {
			                if (node.info['row.color']) rowClassName += ' g740-color-' + node.info['row.color'];
			            }
			            row.customClasses = rowClassName;

			            var fields = objRowSet.getFields(node);
			            var objCell = null;
			            for (var cellIndex = 0; objCell = this.getCell(cellIndex) ; cellIndex++) {
			                var fld = fields[objCell.field];
			                if (!fld) continue;
			                var color = '';
			                if (fld.color) color = fld.color;
			                if (node.info[objCell.field + '.color']) color = node.info[objCell.field + '.color'];

			                var domNode = objCell.getNode(row.index);
			                if (!domNode) continue;

			                var lst = domNode.className.split(' ');
			                var lstRes = [];
			                for (var i = 0; i < lst.length; i++) {
			                    if (lst[i].substr(0, 11) == 'g740-color-') continue;
			                    lstRes.push(lst[i]);
			                }
			                if (color) lstRes.push('g740-color-' + color);
			                domNode.className = lstRes.join(' ');
			            }
			        }
			    }
			}
		);

	    g740.panels._builderGrid = function (xml, para) {
	        var result = null;
	        var procedureName = 'g740.panels._builderGrid';
			if (!g740.xml.isXmlNode(xml)) g740.systemError(procedureName, 'errorValueUndefined', 'xml');
			if (xml.nodeName != 'panel') g740.systemError(procedureName, 'errorXmlNodeNotFound', xml.nodeName);
			if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
			if (!para.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'para.objForm');
			if (!para.rowsetName) {
				g740.trace.goBuilder({
					formName: para.objForm.name,
					panelType: 'grid',
					messageId: 'errorRowSetNameEmpty'
				});
				return null;
			}
			var objRowSet = para.objForm.rowsets[para.rowsetName];
			if (!objRowSet) {
				g740.trace.goBuilder({
					formName: para.objForm.name,
					panelType: 'grid',
					rowsetName: para.rowsetName,
					messageId: 'errorRowSetNotFoundInForm'
				});
				return null;
			}

			var xmlRequests = g740.xml.findFirstOfChild(xml, { nodeName: 'requests' });
			if (!g740.xml.isXmlNode(xmlRequests)) xmlRequests = xml;
			var lst = g740.xml.findArrayOfChild(xmlRequests, { nodeName: 'request' });
			for (var i = 0; i < lst.length; i++) {
				var xmlRequest = lst[i];
				var t = 'dblclick';
				var request = {
					sync: true,
					params: {}
				}
				g740.panels.buildRequestParams(xmlRequest, request);
				if (!para.objActionOnDblClick) {
					var p = {
						objForm: para.objForm,
						rowsetName: para.rowsetName
					};
					para.objActionOnDblClick = new g740.Action(p);
				}
				para.objActionOnDblClick.request = request;
			}

			var isFocusOnShow = para.isFocusOnShow;
			para.isFocusOnShow = false;
			var objPanel = new g740.Panel(para, null);
			var p = {
				objForm: para.objForm,
				rowsetName: para.rowsetName,
				objActionOnDblClick: para.objActionOnDblClick,
				color: para.color,
				region: 'center',
				isFocusOnShow: isFocusOnShow
			};
			var rowsetFields = objRowSet.getFieldsByNodeType('');
			var cells = [];
			var xmlFields = g740.xml.findFirstOfChild(xml, { nodeName: 'fields' });
			if (!g740.xml.isXmlNode(xmlFields)) xmlFields = xml;
			var lst = g740.xml.findArrayOfChild(xmlFields, { nodeName: 'field' });
			var isJsVisible = false;
			for (var i = 0; i < lst.length; i++) {
				var xmlField = lst[i];
				var fieldName = g740.xml.getAttrValue(xmlField, 'name', '');
				if (!fieldName) fieldName = g740.xml.getAttrValue(xmlField, 'field', '');
				var fld = rowsetFields[fieldName];
				if (!fld) continue;

				var fldNew = g740.panels.buildFldDef(xmlField, fld);
				if (!fldNew.visible) continue;

				var cell = {};
				cell['field'] = fieldName;
				cell['name'] = fldNew.caption;
				cell['styles'] = '';
				cell['js_visible'] = g740.xml.getAttrValue(xmlField, 'js_visible', fld.js_visible);
				if (cell['js_visible']) isJsVisible = true;

				var len = fldNew.len;
				if (!len) len = 10;
				cell['width'] = (len*g740.config.charwidth)+'px';

				var request = null;
				if (fldNew.on && fldNew.on.dblclick) request = fldNew.on.dblclick;
				if (!request && fldNew.refid) {
					var fldRefId = rowsetFields[fldNew.refid];
					if (fldRefId) request = fldRefId.request;
				}
				if (request) cell['request'] = request;
				
				cell['fldDef']=fldNew;
				if (fldNew.type == 'num') {
					cell['styles'] += 'text-align: right;';
				}
				if (fldNew.type == 'check') {
					cell['formatter'] = function (value) {
						var result = '<div class="g740-grid-check g740-grid-check-';
						if (value) {
							result += 'on'
						}
						else {
							result += 'off'
						}
						result += '"></div>';
						return result;
					};
				}
				if (fldNew.type == 'icons') {
					var list=fldNew.list;
					if (!list) list='';
					cell['list']=list.split(';');
					if (fldNew.basetype=='num') {
						cell['formatter'] = function (value) {
							var list=this._props.list;
							if (!list) list=[];
							value=g740.convertor.toJavaScript(value,'num');
							var icon='';
							if (value>0) {
								if (list.length>=value) icon=list[value-1];
							}
							var result = '<div class="g740-grid-icons ' + g740.icons.getIconClassName(icon) + '"></div>';
							return result;
						};
					}
					else {
						cell['formatter'] = function (value) {
							var result = '<div class="g740-grid-icons ' + g740.icons.getIconClassName(value) + '"></div>';
							return result;
						};
					}
				}
				if ((fldNew.type=='list' || fldNew.type=='radio') && fldNew.basetype=='num') {
					var list=fldNew.list;
					if (!list) list='';
					cell['list']=list.split(';');
					cell['formatter'] = function (value) {
						var list=this._props.list;
						if (!list) list=[];
						value=g740.convertor.toJavaScript(value,'num');
						var result='';
						if (value>0) {
							if (list.length>=value) result=list[value-1];
						}
						return result;
					};
				}

				if (fldNew.type == 'date') {

					cell['formatter'] = function (value) {
						return g740.convertor.js2text(value, 'date');
					};

					cell['editable'] = true;
					cell['widgetClass'] = dijit.form.DateTextBox;
					//cell['constraint']={datePattern: g740.getRegionConfig('datePattern'), selector: 'date'};
					cell['type'] = dojox.grid.cells._Widget;
				}
				if (fldNew.readonly) {
					cell['editable'] = false;
					cell['readonly'] = true;
				}
				cells.push(cell);
			}
			p.structure = [
				{
					defaultCell: { width: '10px', editable: true },
					cells: cells,
					isJsVisible: isJsVisible
				}
			];

			if (objRowSet.paginatorCount) {
				var objPaginator = new g740.Paginator({
					region: 'bottom',
					rowsetName: objRowSet.name,
					objForm: para.objForm
				},null);
				objPanel.addChild(objPaginator);
			}

			var objGrid = new g740.Grid(p, null);
			objPanel.addChild(objGrid);
			objPanel.isG740AutoMenu = true;
			return objPanel;
	    };
	    g740.panels.registrate('grid', g740.panels._builderGrid);

	    return g740;
	}
);