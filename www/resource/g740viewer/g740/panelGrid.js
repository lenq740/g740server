/**
 * G740Viewer
 * Copyright 2017-2019 Galinsky Leonid lenq740@yandex.ru
 * Licensed under the BSD license
 */

define(
	[],
	function () {
	    if (typeof (g740)=='undefined') g740={};

	    // Класс Grid
	    dojo.declare(
			'g740.Grid',
			[dojox.grid.DataGrid, g740._PanelAbstract],
			{
			    isG740Grid: true,
				isG740Clipboard: true,
				objRowSet: null,
				g740cells: null,
				isShowHeaders: true,
				isShowSelected: true,
				isShowOdd: true,
			    constructor: function (para, domElement) {
			        var procedureName='g740.Grid.constructor';
					this.objForm=para.objForm;
					this.rowsetName=para.rowsetName;
					
					this.g740cells=[];
					if (para.structure) {
						var cells=[];
						for(var structureIndex=0; structureIndex<para.structure.length; structureIndex++) {
							var cells=para.structure[structureIndex].cells;
							if (!cells) continue;
							for (var i=0; i<cells.length; i++) {
								var cell=cells[i];
								if (!cell) continue;
								var g740cell={};
								for(var name in cell) g740cell[name]=cell[name];
								this.g740cells.push(g740cell);
							}
						}
					}
					
					if (this.objForm && this.objForm.rowsets) {
						this.objRowSet=this.objForm.rowsets[this.rowsetName];
						if (this.objRowSet) {
							this.set('store', this.objRowSet.getDataApi());
						}
					}

					this.set('selectionMode', 'single');
					this.set('escapeHTMLInData', true);
					this.set('rowSelector', false);
					this.set('elasticView', false);
					this.set('rowsPerPage', 10000);

					this.on('CellFocus', this.onG740CellFocus);
					this.on('CellDblClick', this.onG740CellDblClick);
					this.on('CellClick', this.onG740CellClick);
					this.on('Focus', this.onG740Focus);
					this.on('Blur', this.onG740Blur);
					this.on('StyleRow', this.onG740StyleRow);
					this.on('ResizeColumn', this.onG740ResizeColumn);

					//console.log(this);
			    },
			    destroy: function () {
			        var procedureName='g740.Grid.destroy';
					this.objRowSet=null;
					this.set('store', null);
					this.g740cells=null;
					this.inherited(arguments);
			    },
				postCreate: function() {
					this.inherited(arguments);
					if (!this.isShowHeaders) dojo.addClass(this.domNode,'g740-headers-hide');
				},

			    // Вытаскиваем контекстное меню из описаний верхних панелей
			    onRowContextMenu: function (e) {
			        if (!this.objMenu) {
			            this.objMenu=this.getParentMenu();
			            if (this.objMenu && this.objMenu.bindDomNode) this.objMenu.bindDomNode(this.domNode);
			        }
			        return this.objMenu;
			    },
			    onHeaderContextMenu: function (e) {
			        if (!this.objMenu) {
			            this.objMenu=this.getParentMenu();
			            if (this.objMenu && this.objMenu.bindDomNode) this.objMenu.bindDomNode(this.domNode);
			        }
			        return this.objMenu;
			    },

			    canEdit: function (objCell, rowIndex) {
			        this.inherited(arguments);
			        if (!this.objRowSet) return false;
			        if (this.objRowSet.isObjectDestroed) return false;
			        if (!objCell) return false;
			        if (objCell.request) return false;
			        if (this.getEventOnActionEnabled()) return false;
			        var node=this.getItem(rowIndex);
			        if (this.objRowSet.getFocusedNode() != node) return false;
			        var fieldName=objCell.field;
			        var fields=this.objRowSet.getFields(node);
			        var fld=fields[fieldName];
			        if (!fld) return false;
			        if (fld.refid) return false;
			        if (fld.type=='check') return false;
			        if (fld.type=='memo') return false;
			        if (fld.type=='list') return false;
			        if (fld.type=='icons') return false;
					if (fld.type=='radio') return false;
					if (fld.type=='date') return false;
			        var isReadOnly=this.objRowSet.getFieldProperty({ fieldName: fieldName, propertyName: 'readonly' });
			        return !isReadOnly;
			    },
			    getCellCount: function () {
			        var result=0;
			        if (this.layout) result=this.layout.cellCount;
			        return result;
			    },
			    isRepainted: false,
			    isFirstRepaint: false,
				doG740Repaint: function (para) {
			        if (!para) para={};
					
			        var procedureName='g740.Grid.doG740Repaint';
			        if (!this.objRowSet) return false;
					
					if (para.objRowSet && para.objRowSet.name==this.rowsetName && !this.isFirstRepaint) {
						this.doG740RepaintCells();
						this.isFirstRepaint=true;
					}
					
					// Если есть неподвижные элементы (noscroll) надо убрать параметр width="auto", иначе проблемы
					if (this.views) {
						var lst=this.views.views;
						if (lst) {
							for(var i=0; i<lst.length; i++) {
								lst[i].viewWidth='';
							}
						}
					}
					
			        if (!this.objRowSet.isObjectDestroed && this.objRowSet.isEnabled) {
			            if (dojo.hasClass(this.domNode, 'dojoxGridRowSetDisabled')) dojo.removeClass(this.domNode, 'dojoxGridRowSetDisabled');
			        } else {
			            if (!dojo.hasClass(this.domNode, 'dojoxGridRowSetDisabled')) {
			                dojo.addClass(this.domNode, 'dojoxGridRowSetDisabled');
			                this.render();
			            }
			        }
			        if (this.objRowSet.isObjectDestroed) return false;
			        if (para.objRowSet && para.objRowSet.name != this.rowsetName) return true;
			        var oldRepainted=this.isRepainted;
			        this.isRepainted=true;
			        try {
			            var isFocused=this.focused;
			            // Полная перерисовка
			            if (para.isFull) {
							this.doG740RepaintCells();
			                var scrollTop=this.scrollTop;
			                this.render();
			                this.scrollTo(scrollTop);
							this.views.resize();
			            }
						
			            var rowIndex=-1;
						var node=this.objRowSet.getFocusedNode();
			            if (!para.isFull && para.isRowUpdate && para.node && para.node!=node) {
							// Отдельно отрабатывем запрос на перерисовку не текущей явно указанной строки
							var rowIndex=this.getItemIndex(para.node);
							this.updateRow(rowIndex);
						}
						else {
							if (node) rowIndex=this.getItemIndex(node);
							var cellIndex=0;
							var fieldName='';
							if (this.focus.cell) {
								cellIndex=this.focus.cell.index;
								fieldName=this.focus.cell.field;
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
			            this.isRepainted=oldRepainted;
			        }
			    },
				
				updateRow: function(rowIndex) {
					this.inherited(arguments);
				},
				
				doG740RepaintCells: function() {
			        if (!this.layout) return false;
			        if (!this.layout.cells) return false;
					if (!this.structure) return false
					if (this.structure.length<1) return false
					if (!this.structure[0].cells) return false
					if (!this.g740cells) return false;
					
					// Формируем список колонок из текущего состояния layout
					var layoutCells={};
					for(var i=0; i<this.layout.cells.length; i++) {
						var cell=this.layout.cells[i];
						if (!cell) continue;
						if (!cell.field) continue;
						layoutCells[cell.field]=cell;
					}
					// Формируем список колонок из текущего состояния structure
					var structureCells={};
					for(var structureIndex=0; structureIndex<this.structure.length; structureIndex++) {
						var cells=this.structure[structureIndex].cells;
						if (!cells) continue;
						for(var i=0; i<cells.length; i++) {
							var cell=cells[i];
							if (!cell) continue;
							if (!cell.field) continue;
							var layoutCell=layoutCells[cell.field];
							if (layoutCell) {
								if (layoutCell.unitWidth) cell.width=layoutCell.unitWidth;
							}
							structureCells[cell.field]=cell;
						}
					}
					delete layoutCells;

					// Формируем новый список колонок по this.g740cells, отслеживаем, есть ли изменения
					var isChanged=false;
					var cells=[];
					for (var i=0; i<this.g740cells.length; i++) {
						var defaultCell=this.g740cells[i];
						if (!defaultCell) continue;
						var cell=structureCells[defaultCell.field];
						var isStructureCellExist=true;
						if (!cell) {
							var cell={};
							for(var name in defaultCell) cell[name]=defaultCell[name];
							isStructureCellExist=false;
						}
			            if (defaultCell['js_visible']) {
			                var visible=g740.js_eval(this.objRowSet, defaultCell['js_visible'], true);
			                if (visible) {
								cells.push(cell);
								if (!isStructureCellExist) isChanged=true;
							}
							else {
								if (isStructureCellExist) isChanged=true;
							}
			            }
						else {
							cells.push(cell);
							if (!isStructureCellExist) isChanged=true;
						}
					}
					
					// Выполняем пересчет stretch
					var nStretch=0;
					var minWidth=0;
					for(var i=0; i<cells.length; i++) {
						var cell=cells[i];

						var fldDef=cell.fldDef;
						if (fldDef && fldDef.stretch) {
							var len=fldDef.len;
							if (!len) len=10;
							minWidth+=(len*g740.config.charwidth)+8;
							nStretch++;
						}
						else {
							var w=parseInt(cell.width,10);
							minWidth+=w;
						}
					}
					if (nStretch>0) {
						var width=this.domNode.offsetWidth-25-cells.length*8;
						var delta=0;
						if (minWidth<width) delta=parseInt((width-minWidth)/nStretch);
						
						for(var i=0; i<cells.length; i++) {
							var cell=cells[i];
							var fldDef=cell.fldDef;
							if (fldDef && fldDef.stretch) {
								var len=fldDef.len;
								if (!len) len=10;
								var w=parseInt((len*g740.config.charwidth)+8+delta)+'px';
								if (cell.width!=w) {
									cell.width=w;
									isChanged=true;
								}
							}
						}
					}
					if (!isChanged && cells.length!=this.layout.cells.length) isChanged=true;
					if (isChanged) {
						var structure=this.structure;
						if (structure.length>=2) {
							var cellsNoScroll=[];
							var cellsScroll=[];
							for(var i=0; i<cells.length; i++) {
								var cell=cells[i];
								if (cell['noscroll']) {
									cellsNoScroll.push(cell);
								}
								else {
									cellsScroll.push(cell);
								}
							}
							structure[0].cells=cellsNoScroll;
							structure[1].cells=cellsScroll;
						}
						else {
							structure[0].cells=cells;
						}
						this.set('structure', structure);
						this.views.resize();
					}
				},

				getRowNode: function(rowIndex) {
					if (!this.focus) return null;
					return this.inherited(arguments);
				},
				canFocused: function() {
					return true;
				},
			    doG740Focus: function () {
			        var objParent=this.getParent();
			        if (objParent && objParent.doG740SelectChild) objParent.doG740SelectChild(this);
			        var cellIndex=0;
			        if (this.focus.cell) cellIndex=this.focus.cell.index;
			        var rowIndex=-1;
			        var node=this.objRowSet.getFocusedNode();
			        if (node) rowIndex=this.getItemIndex(node);
			        this.focus.focusGrid();
			        this.focus.setFocusIndex(rowIndex, cellIndex);
			    },
				doG740ScrollToRow: function (rowIndex) {
					var domItem=this.getRowNode(rowIndex);
			        if (!domItem) return false;
			        var y=this.scrollTop;
					var h=this.scroller.windowHeight;
					
					var isScroll=false;
					if (domItem.offsetTop<y) {
						y=parseInt(domItem.offsetTop);
						isScroll=true;
					}
			        if ((domItem.offsetTop + domItem.offsetHeight) > (y + h)) {
			            y=parseInt(domItem.offsetTop + domItem.offsetHeight - h);
						isScroll=true;
			        }
					if (y<0) {
						y=0;
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

			        var cellIndex=0;
			        if (objCell) cellIndex=objCell.index;
			        if (!this.isOnCellFocused) {
			            this.isOnCellFocused=true;
			            try {
			                if (!this.isRepainted) {
			                    var isOk=false;
			                    var newNode=this.getItem(rowIndex);
			                    var oldNode=this.objRowSet.getFocusedNode();

			                    // При смене фокуса принудительно закрываем редактор, сохраняя содержимое
			                    if (newNode==oldNode) this.edit.apply();

			                    if (this.objRowSet.objTreeStorage.isNode(newNode) && !newNode.isObjectDestroed && newNode != oldNode) {
			                        var isOk=this.objRowSet.setFocusedNode(newNode);
			                    }
			                    if (!isOk) {
			                        var rowIndex=this.getItemIndex(oldNode);
			                        this.focus.setFocusIndex(rowIndex, cellIndex);
			                    }
			                    this.selection.clear();
			                    this.selection.setSelected(rowIndex, true);
			                }
			            }
			            finally {
			                this.isOnCellFocused=false;
			            }
			        }
			        else {
			            //						this.selection.clear();
			            //						this.selection.setSelected(rowIndex, true);
			        }
			    },
			    
				// Перехватываем сохранение отредактированного поля
			    doApplyCellEdit: function (newValue, rowIndex, fieldName) {
			        if (!this.objRowSet) return false;
			        if (this.objRowSet.isObjectDestroed) return false;
					
					var oldRepaintEnabled=this.objRowSet.isRepaintEnabled;
					this.objRowSet.isRepaintEnabled=false;
					try {
						this.objRowSet.setFieldProperty({
							fieldName: fieldName,
							value: newValue,
							id: this._by_idx[rowIndex].idty
						});
						this.updateRow(rowIndex);
					}
					finally{
						this.objRowSet.isRepaintEnabled=oldRepaintEnabled;
						g740.execDelay.go({
							delay: 10,
							obj: this.objRowSet,
							func: this.objRowSet.doG740Repaint,
							para: {isRowUpdate: true}
						});
					}
					return true;
			    },

			    get: function (rowIndex, node) {
			        if (this.grid && this.grid.objRowSet && !this.grid.objRowSet.isObjectDestroed && node && this.field) {
			            var objRowSet=this.grid.objRowSet;
			            var result='';
			            var fields=objRowSet.getFields(node);
			            var fld=fields[this.field];
			            var row=node.info;
			            if (fld && row) {
			                result=row[this.field + '.value'];
			                if (typeof (result)=='string') result=result.replaceAll('"', "'");
			                if (fld.type=='memo' && typeof (result)=='string') {
			                    result=result.split("\n").join(' ');
			                    if (result.length > 128) result=result.substr(0, 128) + ' ...';
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
			        if (e && e.type=='keydown' && this.objRowSet && !this.objRowSet.isObjectDestroed) {
						var rr=null;
						if (this.objForm && this.objForm.getRequestByKey) rr=this.objForm.getRequestByKey(e, this.rowsetName);
						if (rr) {
							dojo.stopEvent(e);
							if (this.edit && this.edit.apply) this.edit.apply();
							this.objForm.exec({
								requestName: rr.name,
								requestMode: rr.mode
							});
							return;
						}
						if (!e.ctrlKey && e.keyCode==13) {
							if (this.getEventOnActionEnabled()) {
								this.execEventOnAction();
								dojo.stopEvent(e);
								return true;
							}
							var objCell=this.focus.cell;
							if (objCell) {
								var fld=objCell.fldDef;
								if (!fld) fld={};
								if (objCell.marked) {
									this.objRowSet.exec({requestName:'mark'});
									dojo.stopEvent(e);
									return true;
								}								
								if (fld.js_onaction || fld.evt_onaction) {
									this.doG740FldAction(fld);
									dojo.stopEvent(e);
									return true;
								}
								if (objCell.request || fld.refid || fld.type=='check' || fld.type=='memo' || fld.type=='list' || fld.type=='icons' || fld.type=='radio' || fld.type=='date') {
									this.doG740EditorClick();
									dojo.stopEvent(e);
									return true;
								}
								if (objCell.noscroll && objCell.readonly) {
									dojo.stopEvent(e);
									return true;
								}
							}
						}
			            // Esc
			            if (e.keyCode==27) {
							if (this.edit && this.edit.cancel) this.edit.cancel();
			                this.objRowSet.undoUnsavedChanges();
			                dojo.stopEvent(e);
			                return true;
			            }
			            // Ctrl+Del
			            if (e.ctrlKey && e.keyCode==46) {
							if (this.edit && this.edit.cancel) this.edit.cancel();
			                this.objRowSet.execConfirmDelete();
			                dojo.stopEvent(e);
			                return true;
			            }
			            // Ins, Ctrl+Ins
			            if (e.keyCode==45) {
							if (this.edit && this.edit.apply) this.edit.apply();
			                this.objRowSet.exec({ requestName: 'append' });
			                dojo.stopEvent(e);
			                return true;
			            }
			            // F2
			            if (!e.ctrlKey && e.keyCode==113) {
							if (this.edit && this.edit.apply) this.edit.apply();
			                this.objRowSet.exec({ requestName: 'save' });
			                dojo.stopEvent(e);
			                return true;
			            }
			        }
					if (e && e.type=='keydown') {
						if (!e.ctrlKey && !e.shiftKey && e.keyCode==9) {
							// Tab
							if (this.edit && this.edit.apply) this.edit.apply();
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
							if (this.edit && this.edit.apply) this.edit.apply();
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

			    onG740CellClick: function (e) {
					try {
						var objCell=e.cell;
						var rowIndex=e.rowIndex;
						var objRowSet=this.objRowSet;
						if (objCell.marked) {
							var node=this.getItem(rowIndex);
							if (objRowSet.getFocusedNode()==node) {
								this.objRowSet.exec({requestName:'mark'});
							}
							else {
							}
						}
					}
					catch(eee) {
					}
				},
				
			    onG740CellDblClick: function (e) {
			        if (!this.objRowSet) return false;
			        if (this.objRowSet==null) return false;
			        if (this.objRowSet.isObjectDestroed) return false;

			        if (this.getEventOnActionEnabled()) {
			            this.execEventOnAction();
			        }
			        else {
			            var rowIndex=e.rowIndex;
			            var objCell=e.cell;
						if (objCell) {
							if (objCell.marked) {
								this.objRowSet.exec({requestName:'mark'});
							}
							else {
/*								
								var fieldName=objCell.field;
								var gridNode=this.getItem(rowIndex);
								var rowSetNode=this.objRowSet.getFocusedNode();

								if (!gridNode) return false;
								if (gridNode != rowSetNode) return false;
								if (!fieldName) return false;
								var fields=this.objRowSet.getFields(rowSetNode);
								var fld=fields[fieldName];
*/
								fld=objCell.fldDef;
								if (!fld) return false;
								if (fld.js_onaction || fld.evt_onaction) {
									this.doG740FldAction(fld);
								}
								else {
									if (fld.refid || fld.type=='check' || fld.type=='memo' || fld.type=='list' || fld.type=='icons' || fld.type=='radio' || fld.type=='date') this.doG740EditorClick();
								}
							}
						}
			        }
			    },
				doG740FldAction: function(fld) {
					if (!fld) return true;
					if (fld.js_onaction) {
						var result=g740.js_eval(this.objRowSet, fld.js_onaction, true);
						if (!result) return true;
					}
					if (fld.evt_onaction) {
						if (this.objRowSet.exec) this.objRowSet.exec({exec: fld.evt_onaction});
					}
					return true;
				},
			    // Обработка обращения к внешнему редактору поля
			    doG740EditorClick: function () {
			        if (!this.objRowSet) return false;
			        if (this.objRowSet.isObjectDestroed) return false;
			        var objCell=this.focus.cell;
			        if (!objCell) return false;
					
					var fieldName=objCell.field;
					var fldDef=objCell._props['fldDef'];
					if (!fldDef) {
						var node=this.objRowSet.getFocusedNode();
						var fields=this.objRowSet.getFields(node);
						var fldDef=fields[fieldName];
					}
			        if (!fldDef) return false;
					if (objCell.readonly) return false;
			        var isReadOnly=this.objRowSet.getFieldProperty({
			            fieldName: fieldName,
			            propertyName: 'readonly'
			        });
			        if (isReadOnly) return false;
			        if (fldDef.refid) return this._doG740EditorRefClick();
			        if (fldDef.type=='check') return this._doG740EditorCheckClick();
			        if (fldDef.type=='memo') return this._doG740EditorMemoClick();
			        if (fldDef.type=='list' || fldDef.type=='radio') return this._doG740EditorListClick();
			        if (fldDef.type=='icons') return this._doG740EditorIconsClick();
					if (fldDef.type=='date') return this._doG740EditorDateClick();
			        return false;
			    },
			    // Обработка запроса request
			    _doG740RequestClick: function () {
			        var objCell=this.focus.cell;
			        if (!objCell) return false;
			        var request=objCell.request;
			        if (!request) return false;
			        if (request.name=='form') {
			            var requestAttr={
			                objForm: this.objForm,
			                rowsetName: this.rowsetName,
			                modal: request.modal,
			                width: request.width,
			                height: request.height,
			                onclose: request.onclose,
			                confirm: request.confirm
			            };
			            var G740params=this.objRowSet._getRequestG740params(request.params);
			            this.objForm.exec({
			                requestName: request.name,
			                requestMode: request.mode,
			                G740params: G740params,
			                attr: requestAttr
			            });
			        }
			        else {
			            var G740params=this.objRowSet._getRequestG740params(request.params);
			            this.objRowSet.exec({
			                requestName: request.name,
			                requestMode: request.mode,
			                G740params: G740params
			            });
			        }
			    },
			    // Обработка редактирования поля Check
			    _doG740EditorCheckClick: function () {
			        var objCell=this.focus.cell;
			        if (!objCell) return false;
			        var fieldName=objCell.field;
			        var value=this.objRowSet.getFieldProperty({ fieldName: fieldName });
			        this.objRowSet.setFieldProperty({
			            fieldName: fieldName,
			            value: !value
			        });
			        return true;
			    },
			    // Обработка редактирования поля Ref
			    _doG740EditorRefClick: function () {
			        var objCell=this.focus.cell;
			        if (!objCell) return false;
			        var fieldName=objCell.field;
			        var objDialog=new g740.DialogEditorRef(
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
			        var objCell=this.focus.cell;
			        if (!objCell) return false;
			        var fieldName=objCell.field;
			        var objDialog=new g740.DialogEditorMemo(
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
			        var objCell=this.focus.cell;
			        if (!objCell) return false;
			        var fieldName=objCell.field;
			        var objDialog=new g740.DialogEditorList(
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
			        var objCell=this.focus.cell;
			        if (!objCell) return false;
			        var objRowSet=this.objRowSet;
			        if (!objRowSet) return false;

					var fieldName=objCell.field;
					var fldDef=objCell._props['fldDef'];
					if (!fldDef) {
						var node=this.objRowSet.getFocusedNode();
						var fields=this.objRowSet.getFields(node);
						var fldDef=fields[fieldName];
					}
					if (!fldDef) return false;
					var list=objCell._props['list'];
					if (!list) return false;
					var baseType='string';
					if (fldDef.basetype=='num') baseType='num';
			        var value=this.objRowSet.getFieldProperty({fieldName: fieldName });

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
			    },
			    _doG740EditorDateClick: function () {
			        var objCell=this.focus.cell;
			        if (!objCell) return false;
			        var fieldName=objCell.field;
			        var objDialog=new g740.DialogEditorDate(
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
			        var node=this.getItem(row.index);
			        var objRowSet=this.objRowSet;
			        if (objRowSet && node && node.info) {
			            var rowClassName='dojoxGridRow';
						if (this.isShowSelected && row.selected) {
							rowClassName='dojoxGridRowSelected';
							var colorItem=g740.appColorScheme.getItem();
							if (colorItem.panelGridWhiteIcons) rowClassName+=' icons-white';
						}
						if (this.isShowOdd && (row.index % 2)==1) rowClassName+=' dojoxGridRowOdd';
						
			            if (node.info['row.mark']) {
			                rowClassName+=' g740-mark';
			            } else {
			                if (node.info['row.color']) rowClassName+=' g740-color-' + node.info['row.color'];
			            }
			            row.customClasses=rowClassName;

			            var fields=objRowSet.getFields(node);
			            var objCell=null;
			            for (var cellIndex=0; objCell=this.getCell(cellIndex) ; cellIndex++) {
			                var fld=fields[objCell.field];
			                if (!fld) continue;
			                var color='';
			                if (fld.color) color=fld.color;
			                if (node.info[objCell.field + '.color']) color=node.info[objCell.field + '.color'];

			                var domNode=objCell.getNode(row.index);
			                if (!domNode) continue;

			                var lst=domNode.className.split(' ');
			                var lstRes=[];
			                for (var i=0; i < lst.length; i++) {
			                    if (lst[i].substr(0, 11)=='g740-color-') continue;
			                    lstRes.push(lst[i]);
			                }
			                if (color) lstRes.push('g740-color-' + color);
			                domNode.className=lstRes.join(' ');
			            }
			        }
			    },
				onG740ResizeColumn: function (cellIndex) {
					g740.execDelay.go({
						delay: 50,
						obj: this,
						func: this.doG740RepaintCells
					});
				},
				_g740ResizeIndex: 0,
				resize: function(size) {
					this.inherited(arguments);
					this._g740ResizeIndex++;
					g740.execDelay.go({
						delay: 100,
						obj: this,
						func: function() {
							this._g740ResizeIndex--;
							if (this._g740ResizeIndex==0) this.onAfterResize();
						}
					});
				},
				onAfterResize: function() {
					this.doG740RepaintCells();
				}
			}
		);

	    g740.panels._builderGrid=function (xml, para) {
	        var result=null;
	        var procedureName='g740.panels._builderGrid';
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
			var objRowSet=para.objForm.rowsets[para.rowsetName];
			if (!objRowSet) {
				g740.trace.goBuilder({
					formName: para.objForm.name,
					panelType: 'grid',
					rowsetName: para.rowsetName,
					messageId: 'errorRowSetNotFoundInForm'
				});
				return null;
			}

			var isFocusOnShow=para.isFocusOnShow;
			para.isFocusOnShow=false;

			var p={
				objForm: para.objForm,
				rowsetName: para.rowsetName,
				color: para.color,
				region: 'center',
				isFocusOnShow: isFocusOnShow
			};
			if (para.js_onaction) {
				p.js_onaction=para.js_onaction;
				delete para.js_onaction;
			}
			if (para.evt_onaction) {
				p.evt_onaction=para.evt_onaction;
				delete para.evt_onaction;
			}
			var objPanel=new g740.Panel(para, null);
			
			var rowsetFields=objRowSet.getFieldsByNodeType('');
			var cells=[];
			var cellsNoScroll=[];
			var isIE=get('IE');
			
			var rMark=objRowSet.getRequestForAnyNodeType('mark','');
			if (rMark) {
				var cell={};
				cell['marked']=true;
				cell['noscroll']=true;
				cell['noresize']=true;
				cell['field']='row_mark';
				cell['name']=' ';
				cell['styles']='';
				cell['classes']='';
				cell['width']='17px';
				cell['formatter']=function (value, rowIndex) {
					var result='';
					try {
						var objGrid=this.grid;
						var node=objGrid.getItem(rowIndex);
						var objRowSet=objGrid.objRowSet;
						
						var iconName='mark-off';
						if (objRowSet.getIsNodeMarked(node)) iconName='mark-on';
						var result='<div class="g740-grid-icons '+g740.icons.getIconClassName(iconName,'small')+'"></div>';
					}
					catch(e) {
					}
					return result;
				};
				if (!isIE) {
					cellsNoScroll.push(cell);
				}
				else {
					cells.push(cell);
				}
			}
			
			
			var xmlFields=g740.xml.findFirstOfChild(xml, { nodeName: 'fields' });
			if (!g740.xml.isXmlNode(xmlFields)) xmlFields=xml;
			var lst=g740.xml.findArrayOfChild(xmlFields, { nodeName: 'field' });
			for (var i=0; i<lst.length; i++) {
				var xmlField=lst[i];
				var fieldName=g740.xml.getAttrValue(xmlField, 'name', '');
				if (!fieldName) fieldName=g740.xml.getAttrValue(xmlField, 'field', '');
				var fld=rowsetFields[fieldName];
				if (!fld) continue;

				var fldNew=g740.panels.buildFldDef(xmlField, fld);
				if (!fldNew.visible) continue;
				if (fldNew['type']) fld['type']=fldNew['type'];
				if (fldNew['basetype']) fld['basetype']=fldNew['basetype'];
				if (fldNew['list']) fld['list']=fldNew['list'];
				
				if (g740.xml.isAttr(xmlField,'noscroll')) fldNew.noscroll=(g740.xml.getAttrValue(xmlField, 'noscroll', '')=='1');
				if (g740.xml.isAttr(xmlField,'clipboard')) fldNew.clipboard=(g740.xml.getAttrValue(xmlField, 'clipboard', '')=='1');

				var cell={};
				cell['field']=fieldName;
				cell['name']=fldNew.caption;
				cell['styles']='';
				cell['classes']='';
				cell['js_visible']=g740.xml.getAttrValue(xmlField, 'js_visible', fld.js_visible);
				if (fldNew.stretch) cell['noresize']=true;

				var len=fldNew.len;
				if (!len) len=10;
				cell['width']=(len*g740.config.charwidth)+'px';

				cell['fldDef']=fldNew;
				if (fldNew.type=='num') {
					cell['styles']+='text-align: right;';
				}
				
				if (fldNew.clipboard) {
					if (cell['classes']) cell['classes']+=' ';
					cell['classes']+='g740-grid-clipboard';
				}
				
				if (fldNew.type=='string' && fldNew.nowrap) {
					cell['formatter']=function(value) {
						var result='<span class="g740-grid-nowrap"';
						if (!value) value='';
						value=value.toString();
						if (value) result+=' title="'+value+'"';
						result+='>';
						result+=value;
						result+='</span>';
						return result;
					};
				}
				if (fldNew.type=='html') {
					console.log(fldNew.type);
					cell['formatter']=function(value, rowIndex) {
						var result='';
						try {
							var objGrid=this.grid;
							var fldDef=this.fldDef;
							var id=objGrid._by_idx[rowIndex].idty;
							var result=objGrid.objRowSet.getFieldProperty({
								fieldName: fldDef.name,
								id: id
							});
							if (!result) result='';
							result=result.toString();
						}
						catch(eee) {
							result='';
						}
						return result;
					};
				}
				if (fldNew.type=='check') {
					cell['formatter']=function (value) {
						var result='<div class="g740-grid-check g740-grid-check-';
						if (value) {
							result+='on'
						}
						else {
							result+='off'
						}
						result+='"></div>';
						return result;
					};
				}
				if (fldNew.type=='icons') {
					var size=g740.config.iconSizeDefault;
					if (fldNew.size) size=fldNew.size;
					var list=fldNew.list;
					if (!list) list='';
					cell['list']=list.split(';');
					if (fldNew.basetype=='num') {
						cell['formatter']=function (value) {
							var list=this._props.list;
							if (!list) list=[];
							value=g740.convertor.toJavaScript(value,'num');
							var icon='';
							if (value>0) {
								if (list.length>=value) icon=list[value-1];
							}
							var result='<div class="g740-grid-icons '+g740.icons.getIconClassName(icon, size)+'"></div>';
							return result;
						};
					}
					else {
						cell['formatter']=function (value) {
							var result='<div class="g740-grid-icons '+g740.icons.getIconClassName(value, size)+'"></div>';
							return result;
						};
					}
				}
				if ((fldNew.type=='list' || fldNew.type=='radio') && fldNew.basetype=='num') {
					var list=fldNew.list;
					if (!list) list='';
					cell['list']=list.split(';');
					cell['formatter']=function(value) {
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
				if (fldNew.type=='date') {
					cell['formatter']=function(value) {
						var result=g740.convertor.js2text(value, 'date');
						return result;
					};
					cell['editable']=true;
					cell['widgetClass']=dijit.form.DateTextBox;
					cell['type']=dojox.grid.cells._Widget;
				}
				
				if (fldNew.readonly) {
					cell['editable']=false;
					cell['readonly']=true;
				}
				
				if (!isIE && fldNew.noscroll) {
					cell['noscroll']=true;
					cellsNoScroll.push(cell);
				}
				else {
					cells.push(cell);
				}
			}

			p.structure=[];
			if (cellsNoScroll.length>0) {
				p.structure.push({
					noscroll: true,
					defaultCell: { width: '10px', editable: true },
					cells: cellsNoScroll
				});
			}
			p.structure.push({
				defaultCell: { width: '10px', editable: true },
				cells: cells
			});

			if (objRowSet.paginatorCount) {
				var objPaginator=new g740.Paginator({
					region: 'bottom',
					rowsetName: objRowSet.name,
					objForm: para.objForm
				},null);
				objPanel.addChild(objPaginator);
			}
			
			if (g740.xml.isAttr(xml,'noheader')) p.isShowHeaders=!g740.convertor.toJavaScript(g740.xml.getAttrValue(xml,'noheader','1'),'check');
			if (g740.xml.isAttr(xml,'noselect')) p.isShowSelected=!g740.convertor.toJavaScript(g740.xml.getAttrValue(xml,'noselect','1'),'check');
			if (g740.xml.isAttr(xml,'noodd')) p.isShowOdd=!g740.convertor.toJavaScript(g740.xml.getAttrValue(xml,'noodd','1'),'check');
			
			var objGrid=new g740.Grid(p, null);
			objPanel.addChild(objGrid);
			objPanel.isG740AutoMenu=true;
			return objPanel;
	    };
	    g740.panels.registrate('grid', g740.panels._builderGrid);
	    return g740;
	}
);