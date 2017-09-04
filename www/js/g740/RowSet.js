//-----------------------------------------------------------------------------
//	Набор строк
//-----------------------------------------------------------------------------
define(
	[],
	function () {
	    if (typeof (g740) == 'undefined') g740 = {};

// Описание стандартных запросов для набора строк
	    g740.rowsetRequestInfo = {
	        'definition': {
	            sync: true,
	            enabled: true,
	            captionId: 'requestDefinition'
	        },
	        'refresh': {
	            captionId: 'requestRefresh',
	            specread: true,
	            sync: false,
	            specnofilter: true
	        },
	        'refreshrow': {
	            captionId: 'requestRefreshRow',
	            specread: true,
	            sync: true,
	            specnofilter: true,
	            specrow: true
	        },
	        'expand': {
	            captionId: 'requestExpand',
	            specread: true,
	            sync: true,
	            specrow: true,
	            specnofilter: true,
	            spectree: true,
	            specparent: true,
	            specinto: true
	        },
	        'save': {
	            sync: true,
	            captionId: 'requestSave',
	            specwrite: true,
	            specnofilter: true,
	            specrow: true
	        },
	        'append': {
	            sync: true,
	            modeDefa: 'after',
	            captionId: 'requestAppend',
	            specwrite: true,
	            specnew: true,
	            specnofilter: true,
	            mode: {
	                after: {
	                    captionId: 'requestAppendAfter',
	                    specafter: true
	                },
	                before: {
	                    captionId: 'requestAppendBefore',
	                    specbefore: true
	                },
	                last: {
	                    captionId: 'requestAppendLast',
	                    speclast: true
	                },
	                first: {
	                    captionId: 'requestAppendFirst',
	                    specfirst: true
	                },
	                into: {
	                    captionId: 'requestAppendInto',
	                    specinto: true,
	                    specparent: true,
	                    specttree: true
	                }
	            }
	        },
	        'copy': {
	            sync: true,
	            captionId: 'requestCopy',
	            specrow: true,
	            specwrite: true,
	            specnofilter: true
	        },
	        'move': {
	            sync: true,
	            captionId: 'requestMove',
	            specrow: true,
	            specwrite: true,
	            specnofilter: true,
	            specinto: true
	            //spectree: true
	        },
	        'link': {
	            sync: true,
	            captionId: 'requestLink',
	            specrow: true,
	            specwrite: true,
	            specnofilter: true,
	            specinto: true,
	            spectree: true
	        },
	        'delete': {
	            sync: true,
	            captionId: 'requestDelete',
	            specrow: true,
	            specwrite: true,
	            specdelete: true,
	            specnofilter: true
	        },
	        'join': {
	            sync: true,
	            captionId: 'requestJoin',
	            specrow: true,
	            specwrite: true,
	            specnofilter: true
	        },
	        'shift': {
	            sync: true,
	            captionId: 'requestShift',
	            specrow: true,
	            specwrite: true,
	            specnofilter: true,
	            mode: {
	                after: {
	                    captionId: 'requestShiftAfter',
	                    specafter: true
	                },
	                before: {
	                    captionId: 'requestShiftBefore',
	                    specbefore: true
	                },
	                last: {
	                    captionId: 'requestShiftLast',
	                    speclast: true
	                },
	                first: {
	                    captionId: 'requestShiftFirst',
	                    specfirst: true
	                }
	            }
	        },
	        'change': {
	            sync: true,
	            captionId: 'requestChange',
	            specwrite: true,
	            specchange: true,
	            specrow: true
	        },
	        'undo': {
	            captionId: 'requestUndo',
	            specwrite: true,
	            speclocal: true,
	            specrow: true,
	            enabled: true
	        },
	        'collapse': {
	            captionId: 'requestCollapse',
	            specrow: true,
	            specread: true,
	            speclocal: true,
	            spectree: true,
	            specinto: true,
	            enabled: true,
	            specnofilter: true
	        },
	        'mark': {
	            captionId: 'requestMark',
	            specrow: true,
	            specread: true,
	            speclocal: true,
	            specnofilter: true
	        },
	        'unmarkall': {
	            captionId: 'requestUnMarkAll',
	            specread: true,
	            speclocal: true,
	            specnofilter: true
	        },
	        init: function () {
	            this.mark.speclocal = !g740.config.markNewStyle;
	            this.markclear.speclocal = !g740.config.markNewStyle;
	        }
	    };

		g740.fieldTypes={
			'string': true,
			'memo': true,
			'date': true,
			'num': true,
			'check': true,
			'ref': true,
			'list': true,
			'icons': true,
			'radio': true,
			'reflist': true,
			'reftree': true
		};
		
//	Набор строк
	    dojo.declare(
			"g740.RowSet",
			null,
			{
			    g740className: 'g740.RowSet',
			    objForm: null,				// Экранная форма - владелец
			    isReadOnly: false,			// Набор строк в режиме просмотра, правка невозможна
			    js_readonly: '',			// Динамическое выражение для вычисления readonly

			    name: '',					// Имя набора строк, должно быть уникально в пределах формы
			    datasource: '',				// Имя источника данных

			    parentName: '',
			    objParent: null,			// Родительский набор строк
			    parentRowsetNodeType: null,	// Если родительский набор строк дерево, то тип узла в цепочке выделенных узлов родителя, к которому цепляемся

			    isFilter: false,			// Набор строк - фильтр
			    isFilterAutoRefresh: true,	// Автоматически перечитывать дочерние источники данных по изменению значений полей фильтра

			    isTree: false,				// Набор строк - дерево (есть операция expand)
				
				paginatorCount: 0,			// Пагинатор: максимальное кол-во строк в странице (0 - без пагинации)
				paginatorFrom: 0,			// Пагинатор, текущая начальная строка набора
				paginatorAll: 0,			// Пагинатор, всего строк, без учета пагинации

			    isRef: false,				// Набор строк - справочник
			    refOwnerName: '',			// Имя набора строк, ссылающегося на этот справочник

			    childs: {},					// Дочерние наборы строк
//	Описания, зависимые от типа узла
//	var nt=this.nodeTypes[nodeType]
//		nt.name - для дерева имя поля label
//		nt.description - для дерева имя поля tooltip
//		nt.fields - описания полей
//		nt.requests - описание запросов
//		nt.mark - список помеченных id
//		nt.markCount
//	fld=nt.fields[name]
//		fld.name
//		fld.type
//		fld.list - список значений через ;
//		fld.caption
//		fld.visible
//		fld.js_visible
//		fld.readonly
//		fld.js_readonly
//		fld.notnull
//		fld.maxlength
//		fld.width
//		fld.len
//		fld.rows
//		fld.dec
//		fld.dlgwidth
//		fld.refid
//		fld.refname
//		fld.reftext
//		fld.def - значение по умолчанию, для добавления новой строки
//		on=fld.on
//			on['dblclick']=request

//		fld.request 	- описание запроса по кнопочке
//	r=nt.requests[name], name - <имя запроса> или change.<имя поля>
//		r.name		- запрос
//		r.mode
//		r.exec				- текстовое описание запроса: <rowset>.<request>.<mode>(<params>)
//		r.enabled
//		r.js_enabled
//		r.caption
//		r.icon
//		r.timeout
//		r.sync
//
//		r.save=0|1			- сохранить изменения перед выполнением запроса
//		r.close=0|1			- закрыть модальную форму после успешного выполнения запроса
//
//		r.modal
//		r.width
//		r.height
//		r.onclose
//
//		r.specread			- спецобработка: операция чтение данных
//		r.specwrite			- спецобработка: операция записи данных (недоступна если readonly)
//		r.spectree			- спецобработка: только для деревьев
//		r.specnofilter		- спецобработка: операция недоступна для фильтра
//		r.specrow			- спецобработка: операция обычно выполняется над текущей строкой, другие строки обычно не затрагиваются
//		r.specnew			- спецобработка: операция порождает новую строку
//		r.specdelete		- спецобработка: операция удаляет текущую строку
//		r.specafter
//		r.specbefore
//		r.specfirst
//		r.speclast
//		r.specinto
//		r.specclient		- спецобработка: операция выполняется на клиенте, без обращения к серверу
//
//		p=r.params[name]
//			p.name
//			p.value
//			p.js_value
//			p.get
//			p.def
//			p.enabled
//			p.js_enabled
//			p.type
			    nodeTypes: {},				// Список описателей для разных типов узлов

			    isObjectDestroed: false,	// Объект уничтожен
			    timeoutRefreshChilds: 300,	// временная задержка на перечитку дочерних наборов строк

			    objTreeStorage: null,		// Хранилище строк
			    isEnabled: false,			// Набор строк загружен и готов к работе

			    objDataApi: null,			// Доступ к интерфейсу dojo.data.Api

				autoRefreshTimeout: 0,		// Задержка автоперечитки (0 - автоперечитка выключена)
				isInActivity: false,		// Состояние бездействия (возможна автоматическая перечитка)

// Создание и уничтожение объекта
// Создание экземпляра объекта
//	para.objForm
//	para.name
//	para.datasource
			    constructor: function (para) {
			        var procedureName = 'g740.RowSet.constructor';
			        if (para && para.name) procedureName = 'g740.RowSet[' + para.name + '].constructor';
					if (!para) para = {};
					this.g740className = 'g740.RowSet';
					this.isObjectDestroed = false;
					this.nodeTypes = {};
					this.nodeTypes[''] = {
						name: 'name',
						description: 'description',
						treemenuForm: 'form',
						treemenuParams: 'params',
						fields: {},
						requests: {},
						mark: {},
						markCount: 0
					};
					this._buildEmptyRequests('');
					this.childs = {};
					this.objForm = null;
					this.name = '';
					if (g740.config.timeoutRefreshChilds) this.timeoutRefreshChilds = g740.config.timeoutRefreshChilds;
					if (para.objForm) this.objForm = para.objForm;
					if (para.name) {
						this.name = para.name;
						this.datasource = para.name;
					}
					if (para.datasource) {
						this.datasource = para.datasource;
						if (!para.name) this.name = para.datasource;
					}
					this.isRef = para.isRef;
					this.refOwnerName = para.refOwnerName;
					this._indexRefreshChilds = 0;

					this.objTreeStorage = new g740.TreeStorage();
					this.isEnabled = false;
					this.focusedPath = [];
					this._focusedParentNode = null;
					this.objDataApi = new g740.RowSetDataApi({ objRowSet: this });

					var names = ['parentName', 'objParent', 'isReadOnly', 'isFilter', 'parentRowsetNodeType'];
					for (var i = 0; i < names.length; i++) {
						var name = names[i];
						if (typeof (para[name]) != 'undefined') this.set(name, para[name]);
					}
					//console.log(this);
			    },
// Уничтожение экземпляра объекта
			    destroy: function () {
			        var procedureName = 'g740.RowSet[' + this.name + '].destroy';
					this.isObjectDestroed = true;
					if (this.nodeTypes) {
						for (var nodeType in this.nodeTypes) {
							var nt = this.nodeTypes[nodeType];
							if (nt.fields) for (var name in nt.fields) nt.fields[name] = null;
							nt.fields = null;
							if (nt.requests) for (var name in nt.requests) nt.requests[name] = null;
							nt.requests = null;
							this.nodeTypes[nodeType] = null;
						}
						this.nodeTypes = {};
						this.nodeTypes[''] = {
							name: 'name',
							description: 'description',
							treemenuForm: 'form',
							treemenuParams: 'params',
							fields: {},
							mark: {},
							markCount: 0
						};
					}
					if (this.objForm) {
						if (this.objForm.rowsets && this.objForm.rowsets[this.name]) {
							this.objForm.rowsets[this.name] = null;
							delete this.objForm.rowsets[this.name];
						}
						this.objForm = null;
					}
					if (this.objParent) {
						if (this.objParent.childs && this.objParent.childs[this.name]) {
							this.objParent.childs[this.name] = null;
							delete this.objParent.childs[this.name];
						}
						this.objParent = null;
					}
					if (this.childs) {
						for (var name in this.childs) {
							var objChild = this.childs[name];
							if (objChild.objParent == this) objChild.objParent = null;
							this.childs[name] = null;
						}
						this.childs = {};
					}
					if (this.objDataApi) {
						this.objDataApi.destroy();
						this.objDataApi = null;
					}
					if (this.objTreeStorage) {
						this.objTreeStorage.destroy();
						this.objTreeStorage = null;
					}
					this.focusedPath = [];
					this._focusedParentNode = null;
			    },
// Работа со свойствами
			    set: function (name, value) {
			        var procedureName = 'g740.RowSet[' + this.name + '].set';
			        if (name == 'name') {
			            if (this.name != value) {
			                if (typeof (value) != 'string') g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'name');
			                if (this.objForm && !this.objForm.isObjectDestroed && this.objForm.rowsets) {
			                    if (this.objForm.rowsets[value]) g740.systemError(procedureName, 'errorNotUniqueValue', value);
			                    delete this.objForm.rowsets[this.name];
			                }
			                this.name = value;
			                if (this.objForm && !this.objForm.isObjectDestroed && this.objForm.rowsets) {
			                    this.objForm.rowsets[this.name] = this;
			                }
			            }
			            if (!this.datasource) this.datasource = value;
			            return true;
			        }
			        if (name == 'datasource') {
			            this.datasource = value;
			            if (!this.name) this.set('name', value);
			            return true;
			        }
			        if (name == 'objForm') {
			            if (this.objForm != value) {
			                if ((typeof (value) == 'object') && (value != null)) {
			                    if (value.g740className != 'g740.Form') g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'objForm');
			                    if (value.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objForm');
			                }
			                if (this.objForm && !this.objForm.isObjectDestroed && this.objForm.rowsets && this.objForm.rowsets[this.name]) {
			                    delete this.objForm.rowsets[this.name];
			                }
			            }
			            this.objForm = value;
			            if (this.objForm && this.objForm.rowsets) {
			                this.objForm.rowsets[this.name] = this;
			            }
			            return true;
			        }
			        if (name == 'parentName') {
			            if (this.parentName != value) {
			                if (value == null) value = '';
			                if (typeof (value) != 'string') g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'parentName');
			                this.parentName = value;
			                if (this.objForm && !this.objForm.isObjectDestroed && this.objForm.rowsets) {
			                    this.objParent = this.objForm.rowsets[this.parentName];
			                }
			            }
			            return true;
			        }
			        if (name == 'objParent') {
			            if (this.objParent != value) {
			                if ((typeof (value) == 'object') && (value != null)) {
			                    if (value.g740className != 'g740.RowSet') g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'objParent');
			                    if (value.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objParent');
			                    if (value.objForm && this.objForm && (this.objForm != value.objForm)) g740.systemError(procedureName, 'Incorrect value', 'objParent');
			                }
			                this.objParent = value;
			                if (this.objParent) {
			                    this.parentName = this.objParent.name;
			                }
			                else {
			                    this.parentName = '';
			                }
			            }
			            return true;
			        }
			        if (name == 'isReadOnly') {
			            if (this.isReadOnly != value) {
			                if (typeof (value) != 'boolean') g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'isReadOnly');
			                this.isReadOnly = value;
			            }
			            return true;
			        }
			        if (name == 'isFilter') {
			            if (this.isFilter != value) {
			                if (typeof (value) != 'boolean') g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'isFilter');
			                this.isFilter = value;
			            }
			            return true;
			        }
			        if (name == 'parentRowsetNodeType') {
			            if (this.parentRowsetNodeType != value) {
			                if (value == null) value = '';
			                if (typeof (value) != 'string') g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'parentRowsetNodeType');
			                this.parentRowsetNodeType = value;
			            }
			            return true;
			        }
			        if (name == 'isRef') {
			            this.isRef = value;
			            return true;
			        }
			        if (name == 'refOwnerName') {
			            this.refOwnerName = value;
			            return true;
			        }
			        if (name == 'focusedPath') {
			            if (typeof (value) == 'string') value = [value];
			            if (typeof (value) != 'object') g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'focusedPath');
			            return this.setFocusedPath(value);
			        }
			        if (name == 'focusedId') {
			            if (typeof (value) != 'string') value = null;
			            return this.setFocusedId(value);
			        }
			        if (name == 'focusedNode') {
			            if (!this.objTreeStorage.isNode(value)) value = null;
			            return this.setFocusedNode(value);
			        }

			        g740.systemError(procedureName, 'Incorrect value', name);
			    },
// Доступ к dojo.data.Api
			    getDataApi: function () {
			        return this.objDataApi;
			    },
// Доступ к хранилищу строк
			    getTreeStorage: function () {
			        return this.objTreeStorage;
			    },
// Вернуть описание запроса по имени
			    getRequestForAnyNodeType: function (requestName, requestMode) {
			        var result = null;
			        for (var nodeType in this.nodeTypes) {
			            var result = this.getRequestByNodeType(requestName, requestMode, nodeType);
			            if (result) break;
			        }
			        return result;
			    },
			    getRequest: function (requestName, requestMode) {
			        var nodeType = '';
			        var node = this.getFocusedNode();
			        if (node) nodeType = node.nodeType;
			        if (!nodeType) nodeType = '';
			        var result = this.getRequestByNodeType(requestName, requestMode, nodeType);
			        if (!result && nodeType != '') result = this.getRequestByNodeType(requestName, requestMode, '');
			        return result;
			    },
			    getRequestByNodeType: function (requestName, requestMode, nodeType) {
			        var requests = this.getRequestsByNodeType(nodeType);
			        if (!requestMode) {
			            var rInfo = g740.rowsetRequestInfo[requestName];
			            if (rInfo) {
			                if (!requestMode && rInfo.modeDefa && requests[requestName + '.' + rInfo.modeDefa]) requestMode = rInfo.modeDefa;
			                if (!requestMode && rInfo.mode) {
			                    for (var mm in rInfo.mode) {
			                        if (requests[requestName + '.' + mm]) {
			                            requestMode = mm;
			                            break;
			                        }
			                    }
			                }
			            }
			        }
			        var fullName = requestName;
			        if (requestMode) fullName = requestName + '.' + requestMode;
			        var result = requests[fullName];
			        return result;
			    },

			    getNt: function (nodeType) {
			        if (!nodeType) nodeType = '';
			        var nt = this.nodeTypes[nodeType];
			        if (!nt) {
			            nodeType = '';
			            nt = this.nodeTypes[nodeType];
			        }
			        if (!nt) {
			            nodeType = '';
			            nt = {};
			            this.nodeTypes[''] = nt;
			        }
			        if (!nt.fields) nt.fields = {};
			        if (!nt.requests) nt.requests = {};
			        return nt;
			    },
// Вернуть описание полей по node
			    getFields: function (node) {
			        var nodeType = '';
			        if (node && typeof (node) == 'object' && node.nodeType) nodeType = node.nodeType;
			        return this.getFieldsByNodeType(nodeType);
			    },
// Вернуть описание полей по nodeType
			    getFieldsByNodeType: function (nodeType) {
			        var nt = this.getNt(nodeType);
			        return nt.fields;
			    },

// Вернуть описание запросов по node
			    getRequests: function (node) {
			        var nodeType = '';
			        if (node && typeof (node) == 'object' && node.nodeType) nodeType = node.nodeType;
			        return this.getRequestsByNodeType(nodeType);
			    },
// Вернуть описание запросов по nodeType
			    getRequestsByNodeType: function (nodeType) {
			        var nt = this.getNt(nodeType);
			        return nt.requests;
			    },


// Построить описания запросов по умолчанию
			    _buildEmptyRequests: function (nodeType) {
			        var nt = this.getNt(nodeType);
			        if (!nt.requests) nt.requests = {};

			        for (var requestName in g740.rowsetRequestInfo) {
			            if (!requestName) continue;
			            var rInfo = g740.rowsetRequestInfo[requestName];
			            if (!rInfo) continue;
			            if (rInfo.enabled && !rInfo.mode) {
			                var fullName = requestName;
			                var request = {
			                    name: requestName,
			                    enabled: true,
			                    params: {}
			                };
			                for (var nn in rInfo) {
			                    if (nn == 'modeDefa') continue;
			                    if (nn == 'mode') continue;
			                    if (nn == 'captionId') {
			                        request['caption'] = g740.getMessage(rInfo['captionId']);
			                        continue;
			                    }
			                    request[nn] = rInfo[nn];
			                }
			                nt.requests[fullName] = request;
			            }
			            if (rInfo.mode) for (var requestMode in rInfo.mode) {
			                if (!requestMode) continue;
			                var rInfoMode = rInfo.mode[requestMode];
			                if (!rInfoMode) continue;
			                if (!rInfo.enabled && !rInfoMode.enabled) continue;
			                if (rInfo.enabled && rInfoMode.enabled === false) continue;
			                var fullName = requestName + '.' + requestMode;
			                var request = {
			                    name: requestName,
			                    mode: requestMode,
			                    enabled: true,
			                    params: {}
			                };
			                for (var nn in rInfo) {
			                    if (nn == 'modeDefa') continue;
			                    if (nn == 'mode') continue;
			                    if (nn == 'captionId') {
			                        request['caption'] = g740.getMessage(rInfo['captionId']);
			                        continue;
			                    }
			                    request[nn] = rInfo[nn];
			                }
			                for (var nn in rInfoMode) {
			                    if (nn == 'modeDefa') continue;
			                    if (nn == 'captionId') {
			                        request['caption'] = g740.getMessage(rInfoMode['captionId']);
			                        continue;
			                    }
			                    request[nn] = rInfoMode[nn];
			                }
			                nt.requests[fullName] = request;
			            }
			        }
			    },

			    getRefRowSetName: function (refId, nodeType) {
			        var fields = this.getFieldsByNodeType(nodeType);
			        var fld = fields[refId];
			        if (!fld) return null;
			        var result = 'ref.' + this.name;
			        if (nodeType) result += '[' + nodeType + ']';
			        result += '.' + refId;
			        return result;
			    },
// Перерисовка экранных элементов, по умолчанию перерисовывается только текущая строка
//	objRowSet 	- набор строк
//  parentNode	- родительский узел
//	node		- узел, обычно не задан, берется из текущего
//	isFull		- полная перерисовка всех дочерних элементов
//	isRowUpdate	- изменения в строке
//	isNavigate	- сменилась текущая строка
			    doG740Repaint: function (params) {
			        var procedureName = 'g740.RowSet[' + this.name + '].doG740Repaint';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!params) g740.systemError(procedureName, 'errorValueUndefined', 'params');
					if (!params.objRowSet) params.objRowSet = this;
					if (!params.parentNode) params.parentNode = this.getFocusedParentNode();
					if (this.objForm) this.objForm.doG740Repaint(params);
			        return true;
			    },
// Режимы работы набора строк, состояния
// Заблокировать и очистить источник данных - обычно нужно, если неопределенная ситуация у родительского источника данных
			    doDisable: function () {
			        var procedureName = 'g740.RowSet[' + this.name + '].doDisable';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (this.isEnabled) {
						this.isEnabled = false;
						if (!this.isFilter) {
							this.objTreeStorage.collapseNode(this.objTreeStorage.rootNode);
							this._focusedParentNode = this.objTreeStorage.rootNode;
							this.focusedPath = [];
						}
						// Блокируем дочерние
						for (var name in this.childs) {
							var objChild = this.childs[name];
							if (!objChild) continue;
							if (objChild.isObjectDestroed) continue;
							if (objChild.isEnabled) objChild.doDisable();
						}
						// Вызываем полную отрисовку
						if (!this.isFilter) this.doG740Repaint({ isFull: true, parentNode: this.objTreeStorage.rootNode });
					}
			        return true;
			    },
// Проверка всего набора строк на ReadOnly
			    getReadOnly: function () {
			        var procedureName = 'g740.RowSet[' + this.name + '].getReadOnly';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.isEnabled) return true;
					if (this.isReadOnly) return true;
					if (this.js_readonly) return g740.js_eval(this, this.js_readonly, false);
					return false;
			    },
// Проверка текущего узла на ReadOnly
			    getRowReadOnly: function () {
			        var procedureName = 'g740.RowSet[' + this.name + '].getRowReadOnly';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (this.getReadOnly()) return true;
					var node = this.getFocusedNode();
					if (!node) return true;
					var row = node.info;
					if (!row) return true;
					if (row['row.readonly']) return true;
					return false;
			    },
// Проверить, разрешено ли выполнение запроса
			    getRequestEnabled: function (requestName, requestMode) {
			        var procedureName = 'g740.RowSet[' + this.name + '].getRequestEnabled';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.objForm) return false;
					if (this.objForm.isObjectDestroed) return false;
					if (this.objForm.getRequestEnabled(requestName, requestMode)) return true;

					// Начитка структуры доступна всем всегда
					if (requestName == 'definition') return true;
					// Справочнику доступна только операция refresh
					if (this.isRef) {
						if (requestName == 'refresh') {
							if (!this.refOwnerName) return false;
							if (!this.objForm.rowsets[this.refOwnerName]) return false;
							return true;
						}
						return false;
					}
					if (this.isFilter && requestName == 'refresh') return true;

					var node = this.getFocusedNode();
					// collapse доступно для деревьев
					if (requestName == 'collapse') {
						if (!this.isTree) return false;
						if (!node) return false;
						if (this.objTreeStorage.rootNode == node) return false;
						if (node.isFinal) return false;
						if (!node.childs) return false;
						return true;
					}
					
					var r = this.getRequest(requestName, requestMode);
					if (!r) return false;
					if (r.enabled === false) return false;
					if (!g740.js_eval(this, r.js_enabled, true)) return false;
					if (!this.isTree && r.spectree) return false;

					// Фильтрам недоступны операции с признаком specnofilter
					if (this.isFilter && r.specnofilter) return false;

					// В состоянии this.isEnabled==false доступно только refresh
					if (this.objParent && !this.objParent.isEnabled) return false;
					if (r.name == 'refresh') return true;
					if (!this.isEnabled) return false;

					// Если нет текущей строки, операции на строку недоступны
					if (!node && r.specrow) return false;
					if (!node && r.specinto) return false;
					if (!node && r.specparent) return false;

					// Если операция записи проверяем readonly
					if (r.specwrite && !r.specrow && this.getReadOnly()) return false;
					if (r.specwrite && r.specrow && this.getRowReadOnly()) return false;

					// Проверяем операции дерева
					if (r.specinto && node.isFinal) return false;
					if (r.specparent && node.isFinal) return false;
					if (node) {
						if (r.name == 'expand') {
							if (this.objTreeStorage.rootNode == node) return false;
							if (node.isFinal) return false;
							if (node.childs) return false;
						}
					}
					if (r.name == 'save' || r.name == 'undo') if (!this.getExistUnsavedChanges()) return false;
					if (r.name == 'shift') {
						if (!node || !node.parentNode) return false;
						if ((r.mode == 'first' || r.mode == 'before') && !node.prevNode) return false;
						if ((r.mode == 'last' || r.mode == 'after') && !node.nextNode) return false;
					}
					if (r.nodeTypes) {
						if (!node) return false;
						if (!r.nodeTypes[node.nodeType]) return false;
					}
			        return true;
			    },
// Проверить, поменялись ли параметры refresh с момента последней перечитки
// Наличие несохраненных изменений в строке, откат несохраненных изменений
// Проверить наличие несохраненных изменений в текущей строке
			    getExistUnsavedChanges: function () {
			        var procedureName = 'g740.RowSet[' + this.name + '].getExistUnsavedChanges';
					var result = false;
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (this.isEnabled && !this.getRowReadOnly()) {
						var row = null;
						var node = this.getFocusedNode();
						if (node) row = node.info;
						var id = row['id'];
						if (row) {
							if (row['row.new']) {
								result = true; // Признак новой строки, надо сохранять
							}
							else {
								var fields = this.getFields(node);
								for (var fieldName in fields) {
									var fld = fields[fieldName];
									if (!fld) continue;
									if (row[fieldName + '.value'] != row[fieldName + '.oldvalue']) {
										result = true;
										break;
									}
								}
							}
						}
					}
			        return result;
			    },
// Отменить несохраненные изменения в строке				
			    undoUnsavedChanges: function () {
			        var procedureName = 'g740.RowSet[' + this.name + '].undoUnsavedChanges';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.getExistUnsavedChanges()) return false;
					var row = null;
					var node = this.getFocusedNode();
					var parentNode = this.getFocusedParentNode();
					if (node) row = node.info;
					if (row) {
						if (row['row.new']) {
							var newId = null;
							var newNode = node.prevNode;
							if (!newNode) newNode = node.nextNode;
							if (newNode) newId = newNode.id;
							this.objTreeStorage.removeNode(node);
							this.setFocusedId(newId);
							// Полная отрисовка
							this.doG740Repaint({ isFull: true });
						}
						else {
							var fields = this.getFields(node);
							for (var fieldName in fields) {
								var fld = fields[fieldName];
								if (!fld) continue;
								if (row[fieldName + '.value'] != row[fieldName + '.oldvalue']) {
									var v1 = row[fieldName + '.value'];
									var v2 = row[fieldName + '.oldvalue'];
									row[fieldName + '.value'] = row[fieldName + '.oldvalue'];
								}
							}
							// Отрисовка текущей строки
							this.doG740Repaint({ isRowUpdate: true });
						}
					}
			        return true;
			    },
				execAutoRefresh: function() {
					if (!this.autoRefreshTimeout) return false;
					if (this.isObjectDestroed) return false;
					
					var isFormEnabled=true;
					var dlg=g740.application.getModalDialog();
					if (dlg && dlg.getObjForm && dlg.getObjForm()!=this.objForm) isFormEnabled=false;
					if (isFormEnabled && this.isInActivity) {
						this.exec({
							requestName: 'refresh'
						});
					}
					g740.execDelay.go({
						delay: 500,
						obj: this,
						func: this._setInActivityOn
					});
					g740.execDelay.go({
						delay: this.autoRefreshTimeout,
						obj: this,
						func: this.execAutoRefresh
					});
				},
				_setInActivityOn: function() {
					this.isInActivity=true;
				},
// Выполнить запрос по полному имени
//	requestExec = #form|#focus|#this|#parent|<имя набора строк>.name.mode(param1;...;paramN)
				execByFullName: function(requestExec, attr) {
					if (!attr) attr={};
					var requestParams='';
					var n=requestExec.indexOf('(')
					if (n>=0) {
						requestParams=requestExec.substr(n+1);
						requestExec=requestExec.substr(0,n);
						n=requestParams.lastIndexOf(')');
						if (n>=0) requestParams=requestParams.substr(0,n);
					}
					var requestName='';
					var requestMode='';
					var rowsetName = this.name;
					var p = requestExec.split('.');
					if (p.length == 1) {
						requestName = p[0];
						requestMode = '';
					}
					if (p.length == 2) {
						var name = p[0];
						if (name == '#this' || name == '#parent' || name == '#focus' || name == '#form' || this.objForm.rowsets[name]) {
							rowsetName = p[0];
							requestName = p[1];
							requestMode = '';
						}
						else {
							requestName = p[0];
							requestMode = p[1];
						}
					}
					if (p.length >= 3) {
						rowsetName = p[0];
						requestName = p[1];
						requestMode = p[2];
					}
					if (rowsetName == '#this') rowsetName = this.name;
					if (rowsetName == '#parent') rowsetName = this.parentName;
					if (rowsetName != this.name) {
						var requestExec=rowsetName + '.' + requestName;
						if (requestMode) requestExec += '.' + requestMode;
						if (requestParams) requestExec += '(' + requestParams+')';
						return this.objForm.execByFullName(requestExec, attr);
					}

					var G740params={};
					if (requestParams) G740params=this._getRequestG740params(requestParams);
					return this.exec({
						requestName: requestName,
						requestMode: requestMode,
						G740params: G740params,
						attr: attr
					});
				},
				
				
// Выполнение запроса
// Выполнить запрос, предполагающий взаимодействие с сервером
//	para.requestName
//	para.requestMode
//	para.sync - необязательный параметр, синхронность выполнения
//	para.G740params - ассоциативный массив значений параметров в формате G740
//	para.attr		- ассоциативный массив атрибутов, не передаваемый на сервер
			    exec: function (para) {
			        var result = true;
			        var fullName = '';
					if (para) {
						fullName = para.requestName;
						if (para.requestMode) fullName = para.requestName + '.' + para.requestMode;
					}
			        var procedureName = 'g740.RowSet[' + this.name + '].exec(' + fullName + ')';
					if (this.isObjectDestroed) return false;
					if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
					if (!para.attr) para.attr = {};
					if (!this.getRequestEnabled(para.requestName, para.requestMode)) return false;
					if (this.objForm.getRequestEnabled(para.requestName, para.requestMode)) {
						return this.objForm.exec(para);
					}
					if (para.requestName=='collapse') return this.collapseRow();
					if (this.isFilter && para.requestName=='refresh') return this._execRefreshFilter();
					
					this.isInActivity=false; // что-то где-то происходит (автоматическая перечитка в этом такте не нужна)
					var r = this.getRequest(para.requestName, para.requestMode);
					if (!r) g740.systemError(procedureName, 'errorIncorrectRequestName', fullName);
					if (r.speclocal) {
						if (r.name == 'undo') return this.undoUnsavedChanges();
						if (r.name == 'mark') {
							var node=this.getFocusedNode();
							if (node) {
								if (this.getIsNodeMarked(node)) {
									return this.unmarkNode(node);
								}
								else {
									return this.markNode(node);
								}
							}
							return false;
						}
						if (r.name == 'unmarkall') {
							return this.unmarkAll();
						}
						return false;
					}
					para.requestName = r.name;
					para.requestMode = r.mode;

					var G740params = {};
					var row = null;
					var node = this.getFocusedNode();
					if (node) row = node.info;

					var isSave=para.attr['save'];
					if (r.save) isSave=true;
					if (r.name == 'append' || r.name == 'expand' || r.name == 'copy' || r.name == 'move' || r.name == 'link' || r.name == 'refresh') {
						isSave=true;
					}
					if (this.isFilter) isSave=false;
					
					if (isSave && this.getExistUnsavedChanges()) {
						if (!this.exec({requestName: 'save'})) return false;
					}
					if (r.name == 'refresh') {
						if (this.isEnabled) this.doDisable();
						if (this.objParent) {
							var pNode = this.objParent.getFocusedNode();
							if (!pNode) return true;
							if (this.parentRowsetNodeType && pNode.nodeType != this.parentRowsetNodeType) return true;
							if (this.objParent.getFocusedIsNew()) return true;
						}
					}
					if (r.name == 'refreshrow') {
						if (!row) return false;
						// Очищаем несохраненные изменения
						var fields = this.getFields(node);
						for (var fieldName in fields) {
							var fld = fields[fieldName];
							if (!fld) continue;
							row[fieldName + '.oldvalue'] = row[fieldName + '.value'];
						}
					}
					if (r.name == 'save') {
						if (!row) return false;
						// Начитываем в параметры значения в изменившихся полях
						var fields = this.getFields(node);
						for (var fieldName in fields) {
							var fld = fields[fieldName];
							if (!fld) continue;
							if (row[fieldName + '.oldvalue'] == row[fieldName + '.value']) continue;
							var value = g740.convertor.toG740(row[fieldName + '.value'], fld.type);
							G740params[fieldName] = value;
						}
					}
					if (r.name == 'delete') {
					}
					if (r.name == 'move') {
					}
					if (r.name == 'change') {
					}

					var p = this._getRequestG740params(r.params);
					for (var name in p) G740params[name] = p[name];
					if (r.exec) {
						var requestParams='';
						var n=r.exec.indexOf('(')
						if (n>=0) {
							requestParams=r.exec.substr(n+1);
							n=requestParams.lastIndexOf(')');
							if (n>=0) requestParams=requestParams.substr(0,n);
						}
						if (requestParams) {
							var p = this._getRequestG740params(requestParams);
							for (var name in p) G740params[name] = p[name];
						}
					}
					var rDefault = this.getRequest('default', '');
					if (rDefault) {
						var p = this._getRequestG740params(rDefault.params);
						for (var name in p) {
							G740params[name] = p[name];
						}
					}

					if (!para.G740params) para.G740params={};
					for (var name in G740params) {
						if (para.G740params[name]) continue;
						para.G740params[name]=G740params[name];
					}
					var xmlRequest = g740.xml.createElement('request');
					xmlRequest.setAttribute('name', r.name);
					if (r.mode) xmlRequest.setAttribute('mode', r.mode);

					if (this.paginatorCount) {
						if (r.name=='refresh') {
							var paginatorCount=this.paginatorCount;
							if (para.G740params['paginator.count']) paginatorCount=parseInt(para.G740params['paginator.count']);
							xmlRequest.setAttribute('paginator.count', paginatorCount);
							var paginatorFrom=0;
							if (para.G740params['paginator.from']) paginatorFrom=para.G740params['paginator.from'];
							if (paginatorFrom) xmlRequest.setAttribute('paginator.from', paginatorFrom);
						}
					}
					delete (para.G740params['paginator.from']);
					delete (para.G740params['paginator.count']);

					if (this.objForm) xmlRequest.setAttribute('form', this.objForm.name);
					if (para.fieldName) xmlRequest.setAttribute('field', para.fieldName);
					xmlRequest.setAttribute('rowset', this.name);
					xmlRequest.setAttribute('datasource', this.datasource);
					var id = this.getFocusedId();
					if (id !== null && !r.specparent) {
						xmlRequest.setAttribute('id', id);
						para.id = id;
					}
					if (this.getFocusedIsNew()) {
						xmlRequest.setAttribute('row.new', '1');
					}
					if (this.objParent) {
						var parentId = this.objParent.getFocusedId();
						if (parentId) para.parentId = parentId;
					}

					// Проставляем параметры, специфичные для дерева
					if (this.isTree) {
						if (r.specinto) if (r.name != 'expand') this.exec({ requestName: 'expand' });
						if (node && node.nodeType) {
							xmlRequest.setAttribute('row.type', node.nodeType);
							para.nodeType = node.nodeType;
						}
						var parentNode = this.getFocusedParentNode();
						if (r.specparent) parentNode = node;
						if (parentNode) {
							xmlRequest.setAttribute('row.parentid', parentNode.id);
							para.parentNodeId = parentNode.id;
							if (parentNode.nodeType) {
								xmlRequest.setAttribute('row.parenttype', parentNode.nodeType);
								para.parentNodeType = parentNode.nodeType;
							}
						}
					}

					for (var name in para.G740params) {
						var xmlParam = g740.xml.createElement('param');
						xmlParam.setAttribute('name', name);
						var value = para.G740params[name];
						var xmlText = g740.xml.createTextNode(value);
						xmlParam.appendChild(xmlText);
						xmlRequest.appendChild(xmlParam);
					}

					para.arrayOfRequest = [xmlRequest];
					para.objOwner = this;
					if (r.timeout) para.timeout = r.timeout;
					if (typeof (para.sync) == 'undefined') para.sync = r.sync;
					result = g740.request.send(para);
					if (result && para.attr['close'] && this.objForm && this.objForm.isModal) {
						g740.execDelay.go({
							func: g740.application.closeModalForm
						});
					}
			        return result;
			    },
// Возвращаем рассчитаные параметры в контексте выполнения запроса				
			    _getRequestG740params: function (requestParams) {
			        var procedureName = 'g740.RowSet[' + this.name + ']._getRequestG740params';
					var result = {};
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!requestParams) return result;
					
					// Если параметры заданы строкой <имя>=<js_value>;...;<имя>=<js_value>
					if (typeof(requestParams)=='string') {
						if (requestParams.replaceAll(' ','').substr(0,4)=='get(') {
							requestParams=g740.js_eval(this, requestParams, '');
						}
						requestParams=requestParams.replaceAll('\u000A',';');
						requestParams=requestParams.replaceAll('\u000C','');
						var lst=requestParams.split(';');
						var lstResult={};
						for(var i in lst) {
							var item=lst[i];
							if (!item) continue;
							var lstitem=item.split('=');
							var name=lstitem[0].replaceAll(' ','');
							var p={};
							p['name']=name;
							if (lstitem.length==2) p['js_value']=lstitem[1];
							lstResult[name]=p;
						}
						requestParams=lstResult;
					}
					
					for (var paramName in requestParams) {
						var p = requestParams[paramName];
						if (!p) continue;
						if (p.enabled == false) continue;
						if (!g740.js_eval(this, p.js_enabled, true)) continue;
						var value = '';

						var objRowSet = this;
						if (this.isRef && this.objForm && this.objForm.rowsets[this.refOwnerName]) {
							objRowSet = this.objForm.rowsets[this.refOwnerName];
							if (!objRowSet) g740.systemError(procedureName, 'errorRowSetNotFoundInForm', this.refOwnerName);
						}
						// Начитываем значение в виде <имя набора строк>.<имя поля> или parent.<имя поля> или <имя поля>
						if (typeof (p.value) != 'undefined') {
							value = g740.convertor.toG740(p.value, p.type);
						}
						else {
							var v=null;
							// Начитываем значение вычисленное по js_value
							if (p.js_value) {
								v=g740.js_eval(objRowSet, p.js_value, null);
							}
							else {
								// Начитываем значение по имени поля, совпадающему с именем поля
								var fields = objRowSet.getFields(objRowSet.getFocusedNode());
								if (fields[paramName]) {
									var fld = fields[paramName];
									if (fld) {
										var v = objRowSet.getFieldProperty({ fieldName: paramName });
									}
								}
							}
							if (!v && p.def) v=p.def;
							value=g740.convertor.toG740(v, p.type);
						}
						if (p.result) {
							var name=p.result;
							if (name==1) name=paramName;
							if (this.objForm) this.objForm.modalResults[name]=value;
						} 
						else {
							result[paramName] = value;
						}
					}
			        return result;
			    },
				_execRefreshFilter: function() {
					var procedureName = 'g740.RowSet[' + this.name + ']._execRefreshFilter()';
					if (!this.isFilter) return false;
					this.isEnabled = true;
					if (!this.getFocusedId()) {
				        var parentNode = this.getFocusedParentNode();
						var node = this.objTreeStorage.getFirstChildNode(parentNode);
						if (!node) {
							var xml = g740.xml.createElement('row');
							xml.setAttribute('id', 1);
							this._doResponseRow(xml);
						}
						this.setFocusedFirst();
					}
					else {
						for (var name in this.childs) {
							var objChild = this.childs[name];
							if (!objChild) continue;
							if (objChild.isObjectDestroed) continue;
							objChild.exec({ requestName: 'refresh' });
						}
					}
				},
// Запрос на удаление строки
			    execConfirmDelete: function () {
			        var procedureName = 'g740.RowSet[' + this.name + '].execConfirmDelete';
			        if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
			        if (!this.getRequestEnabled('delete')) return false;

					var objOwner=null;
					if (this.objForm) objOwner=this.objForm.objFocusedPanel;
			        g740.showConfirm({
			            messageId: 'messageConfirmDelete',
			            onCloseOk: this.exec,
			            closePara: { requestName: 'delete' },
			            closeObj: this,
						objOwner: objOwner
			        });
			    },
// Свернуть строку (удалив подстроки)
			    collapseRow: function () {
			        var procedureName = 'g740.RowSet[' + this.name + '].collapseRow()';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					var node = this.getFocusedNode();
					if (!node) return false;
					if (!this.getRequestEnabled('collapse')) return false;
					this.objTreeStorage.collapseNode(node);
					this.isInActivity=false; // что-то где-то происходит (автоматическая перечитка в этом такте не нужна)
					this.doG740Repaint({ isFull: true, parentNode: node });
			        return true;
			    },

// Маркировка
				markNode: function(node) {
					var procedureName = 'g740.RowSet[' + this.name + '].markNode()';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!node) g740.systemError(procedureName, 'errorValueUndefined','node');
					var nt=this.getNt(node.nodeType);
					if (!nt.mark) {
						nt.mark={};
						nt.markCount=0;
					}
					if (!nt.mark[node.id]) {
						nt.mark[node.id]=true;
						nt.markCount++;
						this.doG740Repaint({
							isRowUpdate: true,
							node: node,
							parentNode: node.parentNode
						});
					}
					return true;
				},
				unmarkNode: function(node) {
					var procedureName = 'g740.RowSet[' + this.name + '].unmarkNode()';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!node) g740.systemError(procedureName, 'errorValueUndefined','node');
					var nt=this.getNt(node.nodeType);
					if (!nt.mark) {
						nt.mark={};
						nt.markCount=0;
					}
					if (nt.mark[node.id]) {
						delete nt.mark[node.id];
						nt.markCount--;
						this.doG740Repaint({
							isRowUpdate: true,
							node: node,
							parentNode: node.parentNode
						});
					}
					return true;
				},
				unmarkAll: function() {
					var procedureName = 'g740.RowSet[' + this.name + '].unmarkAll()';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (this.getMarkCount()) {
						for(var nodeType in this.nodeTypes) {
							var nt=this.getNt(nodeType);
							nt.mark={};
							nt.markCount=0;
						}
						this.doG740Repaint({
							isFull: true,
							parentNode: this.objTreeStorage.rootNode
						});
					}
					return true;
				},
				getMarkCountByNodeType: function(nodeType) {
					var procedureName = 'g740.RowSet[' + this.name + '].getMarkCountByNodeType()';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					var nt=this.getNt(nodeType);
					if (!nt.mark) nt.mark={};
					if (!nt.markCount) nt.markCount=0;
					return nt.markCount;
				},
				getMarkCount: function() {
					var procedureName = 'g740.RowSet[' + this.name + '].getMarkCount()';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					var result=0;
					for(var nodeType in this.nodeTypes) result+=this.getMarkCountByNodeType(nodeType);
					return result;
				},
				getMarkByNodeType: function(nodeType) {
					var procedureName = 'g740.RowSet[' + this.name + '].getMarkByNodeType()';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					var result=Array();
					var nt=this.getNt(nodeType);
					if (!nt.mark) {
						nt.mark={};
						nt.markCount=0;
					}
					for(var id in nt.mark) result.push({nodeType:nodeType,id:id});
					return result;
				},
				getMark: function() {
					var procedureName = 'g740.RowSet[' + this.name + '].getMark()';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					var result=Array();
					for(var nodeType in this.nodeTypes) {
						var lst=this.getMarkByNodeType(nodeType);
						for(var i=0; i<lst.length; i++) result.push(lst[i]);
					}
					return result;
				},
				getIsNodeMarked: function(node) {
					var procedureName = 'g740.RowSet[' + this.name + '].getIsNodeMarked()';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!node) return false;
					var nt=this.getNt(node.nodeType);
					if (!nt.mark) {
						nt.mark={};
						nt.markCount=0;
					}
					return (nt.mark[node.id])?true:false;
				},
				
// Обработка ответа
			    _nextFocusedNode: null,	// Сюда пойдет фокус ввода по завершению обработки
			    _g740repaint: {},
// Обработать ответ
			    doResponse: function (para) {
			        var procedureName = 'g740.RowSet[' + this.name + '].doResponse';
					if (this.isObjectDestroed) return false;
					if (!this.objTreeStorage) return false;
					
					var xmlResponse = para.xmlResponse;
					var requestName = para.requestName;
					var requestMode = para.requestMode;
					var r = this.getRequest(requestName, requestMode);
					if (!r) r={};
					var parentId = para.parentId;
					if (!g740.xml.isXmlNode(xmlResponse)) g740.systemError(procedureName, 'errorNotXml', 'xmlResponse');
					if (xmlResponse.nodeName != 'response') g740.responseError('errorXmlNodeNotFound', 'response');
					var responseName = g740.xml.getAttrValue(xmlResponse, 'name', '');
					if (responseName == '') responseName = 'ok';
					if (responseName == 'error') {
						return false;
					}
					if (requestName == 'definition') {
						var xmlRowSet = g740.xml.findFirstOfChild(xmlResponse, { nodeName: 'rowset' });
						return this.build(xmlRowSet);
					}

					var node = this.getFocusedNode();
					var parentNode = this.getFocusedParentNode();
					// Запрос возможно ассинхронный, надо убедиться, что не сдвинулся курсор в родительском наборе строк
					if (this.objParent && typeof(parentId)!='undefined' && this.objParent.getFocusedId() != parentId) return false;

					// Очищаем узел, если запрос был expand
					if (requestName == 'expand') {
						if (!node) return false;
						if (node.nodeType != para.parentNodeType) return false;
						if (node.id != para.parentNodeId) return false;
						this.objTreeStorage.collapseNode(node);
					}
					
					if (this.paginatorCount && requestName=='refresh') {
						var paginatorAll=0;
						var paginatorFrom=0;
						if (g740.xml.isAttr(xmlResponse,'paginator.all')) {
							paginatorAll=parseInt(g740.xml.getAttrValue(xmlResponse,'paginator.all',0));
						}
						if (g740.xml.isAttr(xmlResponse,'paginator.from')) {
							paginatorFrom=parseInt(g740.xml.getAttrValue(xmlResponse,'paginator.from',0));
						}
						this.paginatorAll=paginatorAll;
						this.paginatorFrom=paginatorFrom;
					}

					this._g740repaint = { isRowUpdate: true };
					this._nextFocusedNode = node;
					try {
						var row = null;
						if (this._nextFocusedNode) row = this._nextFocusedNode.info;
						// Допустимые имена строк в ответе
						var lstResponseRowNames = {};
						lstResponseRowNames['row'] = true;
						lstResponseRowNames['change'] = true;
						lstResponseRowNames['shift'] = true;
						lstResponseRowNames['delete'] = true;
						lstResponseRowNames['append'] = true;
						// Обработка ответа ok
						if (responseName == 'ok') {
							// Подсчитываем кол-во строк в ответе
							var rowCount = 0;
							for (var xml = xmlResponse.firstChild; xml; xml = xml.nextSibling) {
								if (!lstResponseRowNames[xml.nodeName]) continue;
								rowCount++;
							}
							if (para.isFirstOk && rowCount == 0) {
								if (requestName == 'append') {
									var xml = g740.xml.createElement('append');
									xmlResponse.appendChild(xml);
									rowCount = 1;
								}
								if (requestName == 'delete') {
									var xml = g740.xml.createElement('delete');
									xmlResponse.appendChild(xml);
									rowCount = 1;
								}
								if (requestName == 'shift') {
									var xml = g740.xml.createElement('shift');
									xml.setAttribute('row.destmode', requestMode);
									xmlResponse.appendChild(xml);
									rowCount = 1;
								}
							}

							var isSaveAndIsNew = false;
							if (requestName == 'save') {
								if (row) {
									var fields = this.getFields(node);
									for (var fieldName in fields) {
										var fld = fields[fieldName];
										if (!fld) continue;
										if (row[fieldName + '.oldvalue'] != row[fieldName + '.value']) row[fieldName + '.oldvalue'] = row[fieldName + '.value'];
									}
									if (row['row.new']) {
										var isSaveAndIsNew = true;
										delete row['row.new'];
									}
								}
							}

							for (var xml = xmlResponse.firstChild; xml; xml = xml.nextSibling) {
								if (!lstResponseRowNames[xml.nodeName]) continue;
								// если ответ содержит 1 строку (так обычно и бывает)
								if (rowCount == 1) {
									// проставляем значения по умолчанию для ответа append
									if (xml.nodeName == 'append') {
										if (!g740.xml.isAttr(xml, 'id')) {
											xml.setAttribute('id', '');
										}
										xml.setAttribute('row.new', '1');
										if (g740.xml.getAttrValue(xml, 'row.focus', '') != '0') {
											xml.setAttribute('row.focus', '1');
										}
									}
									else {
										if (r.specinto && requestName != 'expand') {
											if (g740.xml.getAttrValue(xml, 'row.focus', '') != '0') {
												xml.setAttribute('row.focus', '1');
											}
										}
										// проставляем id.change из id ответа
										if (requestName == 'save' && isSaveAndIsNew) {
											if (g740.xml.isAttr(xml, 'id') && !g740.xml.isAttr(xml, 'id.change')) {
												var id = this.getFocusedId();
												var newid = g740.xml.getAttrValue(xml, 'id', '');
												if (id != newid) {
													xml.setAttribute('id', id);
													xml.setAttribute('id.change', newid);
												}
											}
										}
										// проставляем значения по умолчанию для ответов на запросы, касающиеся текущей строки
										if (!g740.xml.isAttr(xml, 'id')) {
											var id = this.getFocusedId();
											if (id === null) g740.systemError(procedureName, 'errorIncorrectValue', 'id');
											xml.setAttribute('id', id);
										}
									}
								}
								var pNode = parentNode;
								if (r.specinto) pNode = node;
								this._g740repaint.parentNode = pNode;
								if (!this._doResponseItem(xml, pNode)) return false;
							}

							if (r.name == 'refresh') {
								this.isEnabled = true;
								if (!this._nextFocusedNode) this._nextFocusedNode = this.objTreeStorage.getFirstChildNode(this.objTreeStorage.rootNode);
							}
							if (r.name == 'move') {
								this._collapseParents(node);
								this.objTreeStorage.collapseNode(node);
								this._g740repaint={isFull: true, parentNode: this.objTreeStorage.rootNode};
								g740.execDelay.go({
									delay: 200,
									obj: this,
									func: this.exec,
									para: {requestName: 'expand'}
								});
							}
							if (r.name == 'link') {
								this.objTreeStorage.collapseNode(node);
								this._g740repaint={isFull: true, parentNode: this.objTreeStorage.rootNode};
								g740.execDelay.go({
									delay: 200,
									obj: this,
									func: this.exec,
									para: {requestName: 'expand'}
								});
							}

							if (requestName == 'expand') {
								if (!node.childs) {
									node.isEmpty = true;
								}
							}
							return true;
						}
						g740.responseError('errorResponseName', responseName);
					}
					finally {
						this.doG740Repaint(this._g740repaint);
						this.setFocusedNode(this._nextFocusedNode);
						// Если поменялся id, то принудительно перечитываем дочерние наборы строк
						if (this._g740repaint.isIdChanged || isSaveAndIsNew) this.doRefreshChilds(true);
						this._nextFocusedNode = null;
						this._g740repaint = {};
					}
			        return true;
			    },
				_collapseParents: function(node) {
					if (!node) return false;
					parentNode=node.parentNode;
					if (!parentNode) return false;
					for(var childNode=this.objTreeStorage.getFirstChildNode(parentNode); childNode; childNode=this.objTreeStorage.getNextNode(childNode)) {
						if (childNode!=node) this.objTreeStorage.collapseNode(childNode);
					}
					this._collapseParents(parentNode);
				},
// Обработка строки ответа: row, delete, change, append, shift
			    _doResponseItem: function (xmlItem, parentNode) {
			        var procedureName = 'g740.RowSet[' + this.name + ']._doResponseItem';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!g740.xml.isXmlNode(xmlItem)) g740.systemError(procedureName, 'errorNotXml', 'xmlItem');

					if (xmlItem.nodeName=='row') return this._doResponseRow(xmlItem, parentNode);
					if (xmlItem.nodeName=='change') {
						xmlItem.setAttribute('row.change', '1');
						return this._doResponseRow(xmlItem, parentNode);
					}
					if (xmlItem.nodeName=='append') {
						xmlItem.setAttribute('row.new', '1');
						return this._doResponseRow(xmlItem, parentNode);
					}
					// Формируем row, идентичную delete
					if (xmlItem.nodeName=='delete' || xmlItem.nodeName=='shift') {
						var xml = g740.xml.createElement('row');
						if (g740.xml.isAttr(xmlItem, 'id')) {
							xml.setAttribute('id', g740.xml.getAttrValue(xmlItem, 'id', ''));
						}
						if (g740.xml.isAttr(xmlItem, 'row.type')) {
							xml.setAttribute('row.type', g740.xml.getAttrValue(xmlItem, 'row.type', ''));
						}
						if (xmlItem.nodeName=='delete') {
							xml.setAttribute('row.delete', '1');
						}
						if (xmlItem.nodeName=='shift') {
							xml.setAttribute('row.change', '1');
							if (g740.xml.isAttr(xmlItem, 'row.destmode')) {
								xml.setAttribute('row.destmode', g740.xml.getAttrValue(xmlItem, 'row.destmode', ''));
							}
							if (g740.xml.isAttr(xmlItem, 'row.destid')) {
								xml.setAttribute('row.destid', g740.xml.getAttrValue(xmlItem, 'row.destid', ''));
							}
						}
						return this._doResponseRow(xml, parentNode);
					}
					return false;
			    },
// Обработка строки ответа row
			    _doResponseRow: function (xmlRow, parentNode) {
			        var procedureName = 'g740.RowSet[' + this.name + ']._doResponseRow';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!g740.xml.isXmlNode(xmlRow)) g740.systemError(procedureName, 'errorNotXml', 'xmlRow');

					// id строки должно быть задано обязательно
					if (!g740.xml.isAttr(xmlRow, 'id')) g740.responseError('errorValueUndefined', 'id');
					var id = g740.xml.getAttrValue(xmlRow, 'id', '');

					// Переименовываем id строки
					if (g740.xml.isAttr(xmlRow, 'id.change')) {
						var oldid = id;
						var newid = g740.xml.getAttrValue(xmlRow, 'id.change', '');
						if (oldid != newid) {
							var node = this.objTreeStorage.getNode(newid, parentNode);
							if (node) g740.responseError('errorRowIdNotUnique', newid);
							var node = this.objTreeStorage.getNode(oldid, parentNode);
							if (!node) g740.responseError('errorRowIdNotFound', oldid);
							var row = node.info;
							if (!row) g740.responseError('errorRowIdNotFound', oldid);
							var prevNode = node.prevNode;
							var nextNode = node.nextNode;
							this.objTreeStorage.cutNode(node);
							node.id = newid;
							row['id'] = newid;
							this.objTreeStorage.pasteNode(node, parentNode, prevNode, nextNode);
							if (this.focusedPath.length > 0 && this.focusedPath[this.focusedPath.length - 1] == id) {
								this.focusedPath[this.focusedPath.length - 1] = newid;
							}
							id = newid;
							this._g740repaint.isIdChanged = true;
							this._g740repaint.isFull = true;		// Смена id строки требует полной перечитки Grid
						}
					}

					// Удаляем строку
					var isDelete = false;
					if (g740.xml.isAttr(xmlRow, 'row.delete')) isDelete = g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRow, 'row.delete', '0'), 'check');
					if (isDelete) {
						var node = this.objTreeStorage.getNode(id, parentNode);
						if (!node) g740.responseError('errorRowIdNotFound', id);
						if (this._nextFocusedNode == node) {
							if (node.nextNode) {
								this._nextFocusedNode = node.nextNode;
							}
							else if (node.prevNode) {
								this._nextFocusedNode = node.prevNode;
							}
							else if (node.parentNode && node.parentNode != this.objTreeStorage.rootNode) {
								this._nextFocusedNode = node.parentNode;
							}
							else this._nextFocusedNode = null;
						}
						this.objTreeStorage.removeNode(node);
						this._g740repaint.isFull = true;
						return true;
					}


					var node = this.objTreeStorage.getNode(id, parentNode);
					if (node != this.getFocusedNode()) this._g740repaint.isFull = true;

					var isNewNode = false;
					if (!node) {
						var node = this.objTreeStorage.appendNode(id, parentNode);
						node.nodeType = g740.xml.getAttrValue(xmlRow, 'row.type', '');
						var row = {};
						row['id'] = id;
						var fields = this.getFields(node);
						for (var fieldName in fields) {
							var fld = fields[fieldName];
							if (!fld) continue;
							var def = '';
							if (fld.def) def = fld.def;
							row[fieldName + '.value'] = g740.convertor.toJavaScript(def, fld.type);
							row[fieldName + '.oldvalue'] = g740.convertor.toJavaScript('', fld.type);
							row[fieldName + '.visible'] = true;
						}
						node.info = row;
						this._g740repaint.isFull = true;
						isNewNode = true;
					}
					var row = node.info;
					if (g740.xml.isAttr(xmlRow, 'row.type')) {
						var nodeType = g740.xml.getAttrValue(xmlRow, 'row.type', node.nodeType);
						if (node.nodeType != nodeType) {
							node.nodeType = nodeType;
							row['row.type'] = nodeType;
						}
					}

					// меняем порядок строк, если надо
					var rowDestMode = g740.xml.getAttrValue(xmlRow, 'row.destmode', '');
					if (rowDestMode) {
						var prevNode = null;
						var nextNode = null;
						if (rowDestMode == 'append' || rowDestMode == 'last') prevNode = parentNode.childs.lastNode;
						if (rowDestMode == 'first') nextNode = parentNode.childs.firstNode;
						if (rowDestMode == 'after') {
							var rowDestId = g740.xml.getAttrValue(xmlRow, 'row.destid', '');
							if (rowDestId) prevNode = this.objTreeStorage.getNode(rowDestId, parentNode);
							if (!prevNode) prevNode = node.nextNode;
						}
						if (rowDestMode == 'before') {
							var rowDestId = g740.xml.getAttrValue(xmlRow, 'row.destid', '');
							if (rowDestId) nextNode = this.objTreeStorage.getNode(rowDestId, parentNode);
							if (!nextNode) nextNode = node.prevNode;
						}
						if (prevNode && prevNode == node) prevNode = null;
						if (nextNode && nextNode == node) nextNode = null;
						if (prevNode || nextNode) {
							this.objTreeStorage.cutNode(node);
							this.objTreeStorage.pasteNode(node, parentNode, prevNode, nextNode);
							this._g740repaint.isFull = true;
						}
					}

					var isRowChange=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRow, 'row.change', '0'), 'check');
					var isRowNew=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRow, 'row.new', '0'), 'check');
					if (!isRowChange && !isNewNode) {
						delete row['row.new'];
						delete row['row.readonly'];
						delete row['row.color'];
						delete row['row.icon'];
						delete node.isFinal;
						delete node.isEmpty;
						var fields=this.getFields(node);
						for (var fieldName in fields) {
							var fld=fields[fieldName];
							if (!fld) continue;
							delete row[fieldName+'.readonly'];
							delete row[fieldName+'.color'];
							row[fieldName+'.visible']=true;
						}
					}
					if (g740.xml.isAttr(xmlRow, 'row.new')) row['row.new'] = g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRow, 'row.new', '0'), 'check');
					if (g740.xml.isAttr(xmlRow, 'row.readonly')) row['row.readonly'] = g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRow, 'row.readonly', '0'), 'check');
					if (g740.xml.isAttr(xmlRow, 'row.color')) row['row.color'] = g740.xml.getAttrValue(xmlRow, 'row.color', '');
					if (g740.xml.isAttr(xmlRow, 'row.icon')) row['row.icon'] = g740.xml.getAttrValue(xmlRow, 'row.icon', '');
					if (g740.xml.isAttr(xmlRow, 'row.final')) node.isFinal = g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRow, 'row.final', '0'), 'check');
					if (g740.xml.isAttr(xmlRow, 'row.empty')) node.isEmpty = g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRow, 'row.empty', '0'), 'check');

					// Запоминаем перемещение фокуса ввода
					var isFocus = false;
					if (g740.xml.isAttr(xmlRow, 'row.focus')) isFocus = g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRow, 'row.focus', '0'), 'check');
					if (isFocus) this._nextFocusedNode = node;

					var fields=this.getFields(node);
					for (var fieldName in fields) {
						var fld=fields[fieldName];
						if (!fld) continue;
						if (g740.xml.isAttr(xmlRow, fieldName)) {
							var value=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRow, fieldName, ''), fld.type);
							row[fieldName+'.value']=value;
							if (!isRowChange && !isRowNew) row[fieldName+'.oldvalue']=value;
						}
						if (g740.xml.isAttr(xmlRow, fieldName+'.readonly')) {
							var value=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRow, fieldName+'.readonly', '0'), 'check');
							row[fieldName+'.readonly']=value;
						}
						if (g740.xml.isAttr(xmlRow, fieldName+'.visible')) {
							var value=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRow, fieldName+'.visible', '1'), 'check');
							row[fieldName+'.visible']=value;
						}
						if (g740.xml.isAttr(xmlRow, fieldName+'.color')) {
							row[fieldName+'.color']=g740.xml.getAttrValue(xmlRow, fieldName+'.color', '');
						}
					}
					var lstXmlFields=g740.xml.findArrayOfChild(xmlRow, {nodeName: 'field'});
					for (var i=0; i<lstXmlFields.length; i++) {
						var xmlField=lstXmlFields[i];
						var fieldName=g740.xml.getAttrValue(xmlField, 'name', '');
						if (!fieldName) fieldName=g740.xml.getAttrValue(xmlField, 'field', '');
						var fld = fields[fieldName];
						if (!fld) continue;
						var value='';
						if (xmlField.firstChild) value=xmlField.firstChild.nodeValue;
						var value = g740.convertor.toJavaScript(value, fld.type);
						row[fieldName+'.value']=value;
						if (!isRowChange && !isRowNew) row[fieldName+'.oldvalue']=value;
						if (g740.xml.isAttr(xmlField, 'readonly')) {
							var value=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlField, 'readonly', '0'), 'check');
							row[fieldName+'.readonly']=value;
						}
						if (g740.xml.isAttr(xmlField, 'visible')) {
							var value=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlField, 'visible', '1'), 'check');
							row[fieldName+'.visible']=value;
						}
						if (g740.xml.isAttr(xmlField, 'color')) {
							row[fieldName+'.color'] = g740.xml.getAttrValue(xmlField, 'color', '');
						}
					}
			        return true;
			    },
// Построение структуры набора строк по XML описанию
// Построить все
			    build: function (xmlRowSet) {
			        var procedureName = 'g740.RowSet[' + this.name + '].build';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'objForm');
					if (this.objForm.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objForm');
					if (!g740.xml.isXmlNode(xmlRowSet)) g740.systemError(procedureName, 'errorNotXml', 'xmlRowSet');
					if (xmlRowSet.nodeName != 'rowset') g740.systemError(procedureName, 'errorXmlNodeNotFound', 'rowset');
					this._buildRowSetProperty(xmlRowSet);
					this._buildSection(xmlRowSet);
					var lstSections = g740.xml.findArrayOfChild(xmlRowSet, { nodeName: 'section' });
					for (var i = 0; i < lstSections.length; i++) {
						var xmlSection = lstSections[i];
						this._buildSection(xmlSection);
					}
					this.isObjectDestroed = false;
			        return true;
			    },
// Начитать из XML описателя основные параметры набора строк
			    _buildRowSetProperty: function (xmlRowSet) {
			        var procedureName = 'g740.RowSet[' + this.name + ']._buildRowSetProperty';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!g740.xml.isXmlNode(xmlRowSet)) g740.systemError(procedureName, 'errorNotXml', 'xmlRowSet');
					if (xmlRowSet.nodeName != 'rowset') g740.systemError(procedureName, 'errorXmlNodeNotFound', 'rowset');
					// Читаем параметры набора строк
					if (g740.xml.isAttr(xmlRowSet, 'name')) this.name = g740.xml.getAttrValue(xmlRowSet, 'name', '');
					if (g740.xml.isAttr(xmlRowSet, 'rowset')) this.name = g740.xml.getAttrValue(xmlRowSet, 'rowset', '');
					if (g740.xml.isAttr(xmlRowSet, 'datasource')) this.datasource = g740.xml.getAttrValue(xmlRowSet, 'datasource', '');
					if (this.name == '' && this.datasource != '') this.name = this.datasource;
					if (this.datasource == '' && this.name != '') this.datasource = this.name;

					if (g740.xml.isAttr(xmlRowSet, 'parent.row.type')) this.parentRowsetNodeType = g740.xml.getAttrValue(xmlRowSet, 'parent.row.type', '');

					if (g740.xml.isAttr(xmlRowSet, 'readonly')) {
						this.isReadOnly = g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRowSet, 'readonly', '0'), 'check');
					}
					if (g740.xml.isAttr(xmlRowSet, 'js_readonly')) this.js_readonly = g740.xml.getAttrValue(xmlRowSet, 'js_readonly', '');
					if (g740.xml.isAttr(xmlRowSet, 'filter')) {
						this.isFilter = g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRowSet, 'filter', '0'), 'check');
					}
					if (g740.xml.isAttr(xmlRowSet, 'filter.autorefresh')) {
						this.isFilterAutoRefresh = g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRowSet, 'filter.autorefresh', '1'), 'check');
					}
					if (g740.xml.isAttr(xmlRowSet, 'paginator.count')) {
						this.paginatorCount=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRowSet, 'paginator.count', '500'), 'num');
					}
					if (g740.xml.isAttr(xmlRowSet, 'autorefresh')) {
						this.autoRefreshTimeout=g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlRowSet, 'autorefresh', '60'), 'num')*1000;
					}
			        return true;
			    },
			    _buildSection: function (xmlSection) {
			        var procedureName = 'g740.RowSet[' + this.name + ']._buildSection';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'objForm');
					if (this.objForm.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objForm');
					if (!g740.xml.isXmlNode(xmlSection)) g740.systemError(procedureName, 'errorNotXml', 'xmlSection');
					var nodeType = '';
					if (xmlSection.nodeName == 'section') nodeType = g740.xml.getAttrValue(xmlSection, 'row.type', '');
					var nt = this.nodeTypes[nodeType];
					if (!nt) {
						nt = {
							name: 'name',
							description: 'description',
							treemenuForm: 'form',
							treemenuParams: 'params',
							fields: {},
							requests: {},
							mark: {},
							markCount: 0
						};
						this.nodeTypes[nodeType] = nt;
					}
					if (nodeType != '') this.isTree = true;
					// Строим описание запросов
					{
						var xmlRequests = g740.xml.findFirstOfChild(xmlSection, { nodeName: 'requests' });
						if (!g740.xml.isXmlNode(xmlRequests)) xmlRequests = xmlSection;
						var xmlDefaParams = g740.xml.findFirstOfChild(xmlRequests, { nodeName: 'params' });
						if (!g740.xml.isXmlNode(xmlDefaParams)) {
							var lstDefaParam = g740.xml.findArrayOfChild(xmlRequests, { nodeName: 'param' });
							if (lstDefaParam.length > 0) {
								xmlDefaParams = xmlSection.ownerDocument.createElement('params');
								xmlRequests.appendChild(xmlDefaParams);
								for (var i = 0; i < lstDefaParam.length; i++) {
									xmlDefaParams.appendChild(lstDefaParam[i]);
								}
							}
						}
						if (g740.xml.isXmlNode(xmlDefaParams)) {
							var xmlRequestDefa = xmlSection.ownerDocument.createElement('request');
							xmlRequestDefa.setAttribute('name', 'default');
							xmlRequestDefa.appendChild(xmlDefaParams);
							xmlRequests.appendChild(xmlRequestDefa);
						}
						// Добавляем запросы
						var lstRequest = g740.xml.findArrayOfChild(xmlRequests, { nodeName: 'request' });
						for (var i = 0; i < lstRequest.length; i++) {
							var xmlItem = lstRequest[i];
							if (!g740.xml.isXmlNode(xmlItem)) continue;
							this._buildRequest(xmlItem, nodeType);
						}
						if (nt.requests['expand'] != null) this.isTree = true;
					}
					// Строим описание полей
					{
						var xmlFields = g740.xml.findFirstOfChild(xmlSection, { nodeName: 'fields' });
						if (!xmlFields) xmlFields = xmlSection;

						if (xmlFields.nodeName == 'fields') {
							nt.name = g740.xml.getAttrValue(xmlFields, 'name', nt.name);
							nt.description = g740.xml.getAttrValue(xmlFields, 'description', nt.description);
							nt.treemenuForm = g740.xml.getAttrValue(xmlFields, 'treemenu.form', nt.treemenuForm);
							nt.treemenuParams = g740.xml.getAttrValue(xmlFields, 'treemenu.params', nt.treemenuParams);
						}
						var lstField = g740.xml.findArrayOfChild(xmlFields, { nodeName: 'field' });
						var fields = {};
						// Добавляем поля
						for (var i = 0; i < lstField.length; i++) {
							var xmlItem = lstField[i];
							if (!g740.xml.isXmlNode(xmlItem)) continue;

							// Проверка имени поля на уникальность
							var name = g740.xml.getAttrValue(xmlItem, 'name', '');
							if (name == '') name = g740.xml.getAttrValue(xmlItem, 'field', '');
							if (name == 'id' || name == '') {
								g740.trace.goBuilder({
									formName: this.objForm.name,
									rowsetName: this.name,
									fieldName: name,
									messageId: 'errorIncorrectFieldName'
								});
								continue;
							}
							if (fields[name]) {
								g740.trace.goBuilder({
									formName: this.objForm.name,
									rowsetName: this.name,
									fieldName: name,
									messageId: 'errorNotUniqueFieldName'
								});
								continue;
							}
							fields[name] = xmlItem;
							this._buildField(xmlItem, nodeType);
						}
						// Строим справочники, после того как все поля построены
						for (var i = 0; i < lstField.length; i++) {
							var xmlItem = lstField[i];
							if (!g740.xml.isXmlNode(xmlItem)) continue;
							var name = g740.xml.getAttrValue(xmlItem, 'name', '');
							if (name == '') name = g740.xml.getAttrValue(xmlItem, 'field', '');

							var xmlRef = g740.xml.findFirstOfChild(xmlItem, { nodeName: 'ref' });
							if (g740.xml.isXmlNode(xmlRef)) this._buildRef(name, xmlRef, nodeType);

							var xmlChange = g740.xml.findFirstOfChild(xmlItem, { nodeName: 'change' });
							if (g740.xml.isXmlNode(xmlChange)) this._buildChange(name, xmlChange, nodeType);
						}
						// Проверяем refid
						var fields = this.getFieldsByNodeType(nodeType);
						for (var fieldName in fields) {
							var fld = fields[fieldName];
							if (!fld) continue;
							if (!fld.refid) continue;
							if (!fields[fld.refid]) {
								g740.trace.goBuilder({
									formName: this.objForm.name,
									rowsetName: this.name,
									fieldName: fld.refid,
									messageId: 'errorIncorrectFieldName'
								});
								continue;
							}
							var refRowsetName = this.getRefRowSetName(fld.refid, nodeType);
							if (!this.objForm.rowsets[refRowsetName]) {
								g740.trace.goBuilder({
									formName: this.objForm.name,
									rowsetName: this.name,
									fieldName: fld.refid,
									messageId: 'errorIncorrectRefDefinition'
								});
								continue;
							}
						}
					}
			        return true;
			    },
// Построить структуру запросов
			    _buildRequest: function (xmlRequest, nodeType) {
			        var procedureName = 'g740.RowSet[' + this.name + ']._buildRequest';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'objForm');
					if (this.objForm.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objForm');
					if (!g740.xml.isXmlNode(xmlRequest)) g740.systemError(procedureName, 'errorNotXml', 'xmlRequest');
					if (xmlRequest.nodeName != 'request') g740.systemError(procedureName, 'errorXmlNodeNotFound', 'request');

					var nt = this.nodeTypes[nodeType];
					var name = g740.xml.getAttrValue(xmlRequest, 'name', '');
					name = g740.xml.getAttrValue(xmlRequest, 'request', name);
					var mode = '';
					var rInfo = g740.rowsetRequestInfo[name];
					if (rInfo && rInfo.modeDefa) mode = rInfo.modeDefa;
					var mode = g740.xml.getAttrValue(xmlRequest, 'mode', mode);
					var fullname = name;
					if (mode) fullname = name + '.' + mode;

					var request = nt.requests[fullname];
					if (!request) {
						request = {
							name: name,
							enabled: true
						};
						if (mode) request.mode = mode;
						if (rInfo) {
							for (var nn in rInfo) {
								if (nn == 'modeDefa') continue;
								if (nn == 'mode') continue;
								if (nn == 'captionId') {
									request['caption'] = g740.getMessage(rInfo['captionId']);
									continue;
								}
								request[nn] = rInfo[nn];
							}
						}
						if (rInfo && mode && rInfo.mode && rInfo.mode[mode]) {
							var rInfoMode = rInfo.mode[mode];
							for (var nn in rInfoMode) {
								if (nn == 'modeDefa') continue;
								if (nn == 'mode') continue;
								if (nn == 'captionId') {
									request['caption'] = g740.getMessage(rInfoMode['captionId']);
									continue;
								}
								request[nn] = rInfoMode[nn];
							}
						}
					}
					g740.panels.buildRequestParams(xmlRequest, request);
					nt.requests[fullname] = request;
			        return true;
			    },
// Построить структуру полей
			    _buildField: function (xmlField, nodeType) {
			        var procedureName = 'g740.RowSet[' + this.name + ']._buildField';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.objForm) g740.systemError(procedureName, 'errorValueUndefined', 'objForm');
					if (this.objForm.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objForm');
					if (!g740.xml.isXmlNode(xmlField)) g740.systemError(procedureName, 'errorNotXml', 'xmlField');
					if (xmlField.nodeName != 'field') g740.systemError(procedureName, 'errorXmlNodeNotFound', 'field');

					var name = g740.xml.getAttrValue(xmlField, 'name', '');
					if (!name) name = g740.xml.getAttrValue(xmlField, 'field', '');

					if (!name || name == 'id' || name == 'row' || name == 'rowset') {
						g740.trace.goBuilder({
							formName: this.objForm.name,
							rowsetName: this.name,
							fieldName: name,
							messageId: 'errorIncorrectFieldName'
						});
						return false;
					}

					var nt = this.nodeTypes[nodeType];
					if (!nt) {
						nt = {};
						this.nodeTypes[nodeType] = nt;
					}
					if (!nt.fields) nt.fields = {};
					var isEnabled = g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlField, 'enabled', '1'), 'check');
					if (isEnabled) {
						var fld = nt.fields[name];
						if (!fld) {
							fld = g740.panels.buildFldDef(xmlField);
						}
						else {
							fld = g740.panels.buildFldDef(xmlField, fld);
						}

						if (!fld.type) fld.type='string';
						if (g740.xml.isAttr(xmlField, 'notnull')) {
							fld.notnull = g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlField, 'notnull', '0'), 'check');
						}
						if (g740.xml.isAttr(xmlField, 'maxlength')) {
							fld.maxlength = g740.convertor.toJavaScript(g740.xml.getAttrValue(xmlField, 'maxlength', '0'), 'num');
						}
						if (g740.xml.isAttr(xmlField, 'refid')) {
							fld.refid = g740.xml.getAttrValue(xmlField, 'refid', '');
							if (g740.xml.isAttr(xmlField,'refname')) fld.refname = g740.xml.getAttrValue(xmlField, 'refname', '');
							if (g740.xml.isAttr(xmlField,'reftext')) fld.reftext = g740.xml.getAttrValue(xmlField, 'reftext', '');
						}
						if (fld.type == 'reflist') {
							if (g740.xml.isAttr(xmlField,'refname')) fld.refname = g740.xml.getAttrValue(xmlField, 'refname', '');
						}
						if (fld.type == 'reftree') {
							if (!fld.refnodes) fld.refnodes = g740.xml.getAttrValue(xmlField, 'refnodes', '');
						}
						
						if (fld.type == 'date') {
							if (!fld.len) fld.len = 10;
						}
						if (fld.type == 'num') {
							if (!fld.len) fld.len = 12;
						}
						
						if (g740.xml.isAttr(xmlField, 'default')) fld.def=g740.xml.getAttrValue(xmlField, 'default','');
						var xmlDef=g740.xml.findFirstOfChild(xmlField,{nodeName: 'default'});
						if (xmlDef && xmlDef.firstChild) fld.def=xmlDef.firstChild.nodeValue;

						nt.fields[name] = fld;
					}
					else {
						if (nt.fields[name]) nt.fields[name] = null;
					}
			        return true;
			    },
			    _buildRef: function (fieldName, xmlRef, nodeType) {
			        var procedureName = 'g740.RowSet[' + this.name + ']._buildRef';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (xmlRef.nodeName != 'ref') g740.systemError(procedureName, 'errorXmlNodeNotFound', 'ref');

					var fields = this.getFieldsByNodeType(nodeType);
					var fldDef=fields[fieldName];
					if (!fldDef) return false;
					if (fldDef.type!='ref' && fldDef.type!='reflist' && fldDef.type!='reftree') return false;

					var datasource = g740.xml.getAttrValue(xmlRef, 'datasource', '');
					if (!datasource) {
						g740.trace.goBuilder({
							formName: this.objForm.name,
							rowsetName: this.name,
							fieldName: fieldName,
							messageId: 'errorRowSetNameEmpty'
						});
						return false;
					}
					
					var rowsetName = this.getRefRowSetName(fieldName, nodeType);
					var objRowSet = this.objForm.rowsets[rowsetName];
					if (!objRowSet) {
						var objRowSet = new g740.RowSet({
							objForm: this.objForm,
							name: rowsetName,
							datasource: datasource,
							isRef: true,
							refOwnerName: this.name
						});
						this.objForm.rowsets[rowsetName] = objRowSet;
						// Строим описания полей справочника по refid и refname
						var refFields = objRowSet.getFieldsByNodeType('');
						if (fldDef.type=='ref') {
							for (var thisFieldName in fields) {
								var thisFld = fields[thisFieldName];
								if (!thisFld) continue;
								if (thisFld.refid != fieldName) continue;
								
								var fld = {};
								var name='name';
								if (thisFld.refname) name=thisFld.refname;
								fld['name'] = name;
								fld['type'] = thisFld.type;
								fld['caption'] = thisFld.caption;
								refFields[name] = fld;
								
								if (thisFld.reftext && thisFld.reftext!=name) {
									var fld = {};
									var name=thisFld.reftext;
									fld['name'] = name;
									fld['type'] = thisFld.type;
									fld['caption'] = thisFld.caption;
									refFields[name] = fld;
								}
							}
						}
						if (fldDef.type=='reflist' || fldDef.type=='reftree') {
							var refname=fldDef.refname;
							if (!refname) refname='name';
							var fld = {};
							var name=refname;
							fld['name'] = name;
							fld['type'] = 'string';
							fld['caption'] = fldDef.caption;
							refFields[name] = fld;
						}
					}
					// Строим параметры запроса refresh.rowset
					var request = objRowSet.getRequest('refresh');
					if (!request) {
						request = {
							name: 'refresh',
							enabled: true,
							params: {}
						};
						var nt = objRowSet.getNt('');
						nt.requests['refresh'] = request;
					}
					request.sync = true;
					g740.panels.buildRequestParams(xmlRef, request);
			        return true;
			    },
			    _buildChange: function (fieldName, xmlChange, nodeType) {
			        var procedureName = 'g740.RowSet[' + this.name + ']._buildChange';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (xmlChange.nodeName != 'change') g740.systemError(procedureName, 'errorXmlNodeNotFound', 'change');

					var nt = this.nodeTypes[nodeType];
					var fullname = 'change.' + fieldName;
					if (nt.requests[fullname]) return true;
					var request = {
						sync: true,
						specwrite: true,
						specrow: true,
						name: 'change',
						mode: fieldName,
						params: {}
					};
					g740.panels.buildRequestParams(xmlChange, request);
					nt.requests[fullname] = request;
			        return true;
			    },
// Вызывается из формы после полного завершения построения набора строк
			    doAfterBuild: function () {
			        for (var nodeType in this.nodeTypes) {
			            var nt = this.nodeTypes[nodeType];
			            if (!nt.requests) continue;

			            // Чистим недопустимые запросы
			            var lstDel = {};
			            for (var requestName in nt.requests) {
			                var r = nt.requests[requestName];
			                if (!r) {
			                    lstDel[requestName] = true;
			                    continue;
			                }
			                if (this.isReadOnly && r.specwrite) lstDel[requestName] = true;
			                if (this.isFilter && r.specnofilter) lstDel[requestName] = true;
			                if (!this.isTree && r.spectree) lstDel[requestName] = true;
			            }
			            var r = nt.requests['shift'];
			            if (r) {
			                var rInfo = g740.rowsetRequestInfo['shift'];
			                for (var requestMode in rInfo.mode) {
			                    var rInfoMode = rInfo.mode[requestMode];
			                    var rMode = nt.requests['shift.' + requestMode];
			                    if (rMode) continue;
			                    rMode = {};
			                    for (var name in r) {
			                        rMode[name] = r[name];
			                    }
			                    rMode['mode'] = requestMode;
			                    rMode['caption'] = g740.getMessage(rInfoMode['captionId']);
			                    nt.requests['shift.' + requestMode] = rMode;
			                }
			                lstDel['shift'] = true;
			            }
			            for (var requestName in lstDel) delete nt.requests[requestName];
			        }
			    },
// Перечитка дочерних наборов строк
			    doRefreshChilds: function (isTimeout) {
			        var procedureName = 'g740.RowSet[' + this.name + '].doRefreshChilds';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					var result = true;
					for (var name in this.childs) {
						var objChild = this.childs[name];
						if (!objChild) continue;
						if (objChild.isEnabled) objChild.doDisable();
					}
					if (isTimeout) {
						this._indexRefreshChilds++;
						g740.execDelay.go(
							{
								delay: this.timeoutRefreshChilds,
								obj: this,
								func: this._doRefreshChildsTimeout
							}
						);
					}
					else {
						for (var name in this.childs) {
							var objChild = this.childs[name];
							if (!objChild) continue;
							if (objChild.isObjectDestroed) continue;
							if (!objChild.exec({ requestName: 'refresh' })) result = false;
						}
					}
			        return result;
			    },
			    _indexRefreshChilds: 0,
			    _doRefreshChildsTimeout: function () {
			        if (this.isObjectDestroed) return false;
			        this._indexRefreshChilds--;
			        if (this._indexRefreshChilds > 0) return false;
			        this._indexRefreshChilds = 0;
			        return this.doRefreshChilds();
			    },
// Блок навигации - посмотреть и сменить текущий узел
			    focusedPath: [],			// Цепочка id выделенных узлов (от root до выделенного узла)
			    _focusedParentNode: null,	// родитель выделенного   
// Вернуть id текущего узла
			    getFocusedId: function (nodeType) {
			        var procedureName = 'g740.RowSet[' + this.name + '].getFocusedId';
			        if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
			        if (!this.objTreeStorage) g740.systemError(procedureName, 'errorAccessToDestroedObject');
			        var result = null;
			        if (nodeType) {
			            var node = this.objTreeStorage.rootNode;
			            for (var i = 0; i < this.focusedPath.length - 1; i++) {
			                var id = this.focusedPath[i];
			                var node = this.objTreeStorage.getNode(id, node);
			                if (node.nodeType == nodeType) result = node;
			            }
			        }
			        else {
			            if (this.focusedPath.length > 0) result = this.focusedPath[this.focusedPath.length - 1];
			        }
			        return result;
			    },
// Вернуть текущей узел
			    getFocusedNode: function () {
			        var procedureName = 'g740.RowSet[' + this.name + '].getFocusedNode';
			        if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
			        if (!this.objTreeStorage) g740.systemError(procedureName, 'errorAccessToDestroedObject');
			        var id = this.getFocusedId();
			        if (id == null) return null;
			        return this.objTreeStorage.getNode(id, this.getFocusedParentNode());
			    },
// Является ли текущий узел новым, не сохраненным в базе
			    getFocusedIsNew: function () {
			        var procedureName = 'g740.RowSet[' + this.name + '].getFocusedIsNew';
			        if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
			        if (!this.objTreeStorage) g740.systemError(procedureName, 'errorAccessToDestroedObject');
			        var node = this.getFocusedNode();
			        if (!node) return false;
			        var row = node.info;
			        if (row['row.new']) return true;
			        return false;
			    },
// Вернуть родителя текущего узла
			    getFocusedParentNode: function () {
			        var procedureName = 'g740.RowSet[' + this.name + '].getFocusedParentNode';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.objTreeStorage) g740.systemError(procedureName, 'errorAccessToDestroedObject');

					// Для случая списка возвращаем root без лишних проверок					
					if (this.focusedPath.length < 2) {
						this._focusedParentNode = this.objTreeStorage.rootNode;
					}
					else {
						if (!this.objTreeStorage.isNode(this._focusedParentNode)) this._focusedParentNode = null;
						var id = this.focusedPath[this.focusedPath.length - 2];
						if (this._focusedParentNode && (this._focusedParentNode.id != id)) this._focusedParentNode = null;
						if (!this._focusedParentNode) {
							var parentNode = this.objTreeStorage.rootNode;
							for (var i = 0; i <= this.focusedPath.length - 2; i++) {
								var id = this.focusedPath[i];
								parentNode = this.objTreeStorage.getNode(id, parentNode);
								if (parentNode == null) g740.systemError(procedureName, 'errorRowIdNotFound', id);
							}
							this._focusedParentNode = parentNode;
						}
					}
			        return this._focusedParentNode;
			    },
// Основная процедура навигации по дереву или линейному набору строк
			    setFocusedPath: function (path) {
			        var procedureName = 'g740.RowSet[' + this.name + '].setFocusedPath';
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.objTreeStorage) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.isEnabled) return false;
					if (!path) path = [];

					var isPathChanged = (path.length != this.focusedPath.length);
					if (!isPathChanged) for (var i = 0; i < path.length; i++) {
						if (path[i] != this.focusedPath[i]) {
							isPathChanged = true;
							break;
						}
					}
					if (!isPathChanged) return true;

					var node = this.objTreeStorage.rootNode;
					for (var i = 0; i < path.length; i++) {
						var id = path[i];
						if ((id == null) && (i == (path.length - 1))) break;
						node = this.objTreeStorage.getNode(id, node);
						if (node == null) g740.systemError(procedureName, 'errorRowIdNotFound', id);
					}
					// Сохраняем несохраненные изменения
					if (this.getExistUnsavedChanges()) {
						if (!this.exec({ requestName: 'save' })) {
							this.doG740Repaint({});
							return false;
						}
					}

					this.isInActivity=false; // что-то где-то происходит (автоматическая перечитка в этом такте не нужна)
					this._focusedParentNode = null;
					this.focusedPath = path;			// Сдвигаем текущую строку
					this.doRefreshChilds(true);		// Перечитываем (с задержкой) дочерние наборы строк

					// Вызываем отрисовку
					this.doG740Repaint({ isNavigate: true });
			        return true;
			    },
// Дополнительные процедуры навигации				
			    setFocusedNode: function (node) {
			        var path = [];
			        if (this.objTreeStorage.isNode(node)) {
			            var p = [];
			            while (node) {
			                p.push(node.id);
			                node = node.parentNode;
			                if (node == this.objTreeStorage.rootNode) break;
			            }
			            for (var i = p.length - 1; i >= 0; i--) path.push(p[i]);
			        }
			        return this.setFocusedPath(path);
			    },
			    setFocusedId: function (id) {
			        var path = [];
			        for (var i = 0; i < this.focusedPath.length - 1; i++) path.push(this.focusedPath[i]);
			        path.push(id);
			        return this.setFocusedPath(path);
			    },
			    setFocusedFirst: function () {
			        var parentNode = this.getFocusedParentNode();
			        var id = null;
			        var node = this.objTreeStorage.getFirstChildNode(parentNode);
			        if (node) id = node.id;
			        return this.setFocusedId(id);
			    },
			    setFocusedLast: function () {
			        var parentNode = this.getFocusedParentNode();
			        var id = null;
			        var node = this.objTreeStorage.getLastChildNode(parentNode);
			        if (node) id = node.id;
			        return this.setFocusedId(id);
			    },
			    setFocusedNext: function () {
			        var id = null;
			        var node = this.getFocusedNode();
			        if (node && node.nextNode) id = node.nextNode.id;
			        return this.setFocusedId(id);
			    },
			    setFocusedPrev: function () {
			        var id = null;
			        var node = this.getFocusedNode();
			        if (node && node.prevNode) id = node.prevNode.id;
			        return this.setFocusedId(id);
			    },
			    setFocusedParent: function () {
			        var path = [];
			        for (var i = 0; i < this.focusedPath.length - 1; i++) path.push(this.focusedPath[i]);
			        return this.setFocusedPath(path);
			    },

			    doG740Get: function (name, defValue) {
			        if (!defValue) defValue = '';

			        if (this.isObjectDestroed) return defValue;
			        if (!this.objTreeStorage) return defValue;
			        var p = name.split('.');

					if (p.length==1) {
						p.unshift('#this');
						p.push('value');
					}
					if (p.length==2) {
						var isRowSet=false;
						var s=p[0];
						if (s=='#result' || s=='#this' || s=='#parent' || s=='#focus') isRowSet=true;
						if (!isRowSet && this.objForm && this.objForm.rowsets[s]) isRowSet=true;
						if (isRowSet) {
							p.push('value');
						}
						else {
							p.unshift('#this');
						}
					}
					if (p.length<3) return defValue;
					
					var rowsetName = p[0];
					var fieldName = p[1];
					var propertyName = p[2];
			        var rowType = '';
					var n0 = rowsetName.indexOf('[');
					var n1 = rowsetName.indexOf(']');
					if (n0 >= 0 && n1 > n0) {
						rowType = p[0].substr(n0 + 1, n1 - n0 - 1);
						rowsetName = p[0].substr(0, n0);
					}
					if (rowsetName == '#result') {
						return this.objForm.doG740Get(name, defValue);
					}
					if (rowsetName == '#this') rowsetName = this.name;
					if (rowsetName == '#parent' && this.objParent) rowsetName = this.objParent.name;
					if (rowsetName == '#focus') {
						var objRowSet = this.objForm.getFocusedRowSet();
						if (!objRowSet) return defValue;
						rowsetName = objRowSet.name;
					}
					var objRowSet = this.objForm.rowsets[rowsetName];
					if (!objRowSet) return defValue;
					if (objRowSet != this) {
						var name = '#this';
						if (rowType) name += '[' + rowType + ']';
						name += '.' + p[1] + '.' + p[2];
						return objRowSet.doG740Get(name);
					}

					// Это временная заглужка для совместимости
					if (fieldName == '#mark') {
						fieldName = 'rowset';
						propertyName = 'mark';
					}
					if (fieldName == '#markcount') {
						fieldName = 'rowset';
						propertyName = 'markcount';
					}
					if (fieldName == 'rowset') {
						if (propertyName == 'readonly') return this.getReadOnly();
						if (propertyName == 'mark') {
							var result = '';
							if (rowType) {
								var lst=this.getMarkByNodeType(rowType);
							}
							else {
								var lst=this.getMark();
							}
							for(var i=0; i<lst.length; i++) {
								if (result) result+=',';
								if (lst[i].nodeType && rowType && lst[i].nodeType!=rowType) result+=lst[i].nodeType+'.';
								result+=lst[i].id;
							}
							return result;
						}
						if (propertyName == 'markcount') {
							var result=0;
							if (rowType) {
								result=this.getMarkCountByNodeType(rowType);
							}
							else {
								result=this.getMarkCount();
							}
							return result;
						}
						return false;
					}
					
			        var node = null;
			        if (rowType == '@parent') {
			            node = this.getFocusedParentNode();
			        }
			        else {
			            node = this.getFocusedNode();
			            if (node && rowType) {
			                while (node && node.nodeType != rowType) node = node.parentNode;
			            }
			        }
			        if (!node) {
			            if (propertyName == 'readonly') return true;
			            return defValue;
			        }
			        if (fieldName == 'row') {
			            if (propertyName == 'readonly') {
			                if (node == this.getFocusedNode()) return this.getRowReadOnly();
			                if (this.getReadOnly()) return true;
			                if (!node.info) return true;
			                return node.info['row.readonly'];
			            }
			            if (propertyName == 'type') return node.nodeType;
			            if (propertyName == 'color') {
			                if (!node.info) return defValue;
			                return node.info['row.color'];
			            }
			            if (propertyName == 'icon') {
			                if (!node.info) return defValue;
			                return node.info['row.icon'];
			            }
			            return defValue;
			        }
			        if (fieldName == 'id') return node.id;
					
			        var fields = this.getFields(node);
			        if (!fields[fieldName]) return defValue;
			        if (!node.info) return defValue;
			        if (propertyName == 'value' || propertyName == 'oldvalue' || propertyName == 'color') {
			            return node.info[fieldName + '.' + propertyName];
			        }
			        if (propertyName == 'readonly') {
			            if (node == this.getFocusedNode()) {
			                if (this.getRowReadOnly()) return true;
			            }
			            else {
			                if (this.getReadOnly()) return true;
			            }
			            if (node.info[fieldName + '.' + propertyName]) return true;
			            return defValue;
			        }
			        return defValue;
			    },

// Прочитать значение св-ва поля
//	para.fieldName 		- имя поля (обязательный параметр)
//	para.id				- id строки (по умолчанию текущая строка)
//	para.propertyName	- value|text|oldvalue|readonly|visible|color
			    getFieldProperty: function (para) {
			        var procedureName = 'g740.RowSet[' + this.name + '].getFieldProperty';
					var result = null;
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (para == null) g740.systemError(procedureName, 'errorValueUndefined', 'para');
					if (para.fieldName == null) g740.systemError(procedureName, 'errorValueUndefined', 'fieldName');
					if (typeof (para.id) != 'string' && para.id !== null) para.id = this.getFocusedId();
					if (!para.propertyName) para.propertyName = 'value';
					var row = null;
					var node = this.objTreeStorage.getNode(para.id, this.getFocusedParentNode());
					if (node) row = node.info;
					var fields = this.getFields(node);
					var fld = fields[para.fieldName];
					var okPropertyName = false;
					if (para.propertyName == 'value') {
						okPropertyName = true;
						result = null;
						if (row && fld) result = row[para.fieldName + '.value'];
					}
					if (para.propertyName == 'text') {
						okPropertyName = true;
						result = null;
						if (row && fld) result = g740.convertor.js2text(row[para.fieldName + '.value'], fld.type);
					}
					if (para.propertyName == 'oldvalue') {
						okPropertyName = true;
						result = null;
						if (row && fld) result = row[para.fieldName + '.oldvalue'];
					}
					if (para.propertyName == 'readonly') {
						okPropertyName = true;
						result = true;
						if (row && fld) {
							result = this.getReadOnly();
							if (!result && row['row.readonly']) result = true;
							if (!result && row[para.fieldName + '.readonly']) result = true;
							if (!result && fld.readonly) result = true;
							if (!result && g740.js_eval(this, fld.js_readonly, false)) result = true;
							if (!result && fld.refid) {
								var fldRefId = fields[fld.refid];
								if (fldRefId) {
									if (!result && fldRefId.readonly) result = true;
									if (!result && row[fld.refid + '.readonly']) result = true;
									if (!result && g740.js_eval(this, fldRefId.js_readonly, false)) result = true;
								}
							}
							if (!result && !this.isFilter) {
								var r = null;
								var requests = this.getRequestsByNodeType(node.nodeType);
								if (requests) r = requests['save'];
								if (!r && node.nodeType != '') {
									var requests = this.getRequestsByNodeType('');
									if (requests) r = requests['save'];
								}
								if (!r) result = true;
							}
						}
					}
					if (para.propertyName == 'visible') {
						okPropertyName = true;
						result = true;
						if (row && fld) {
							if (result && !row[para.fieldName + '.visible']) result = false;
							if (result && !fld.visible) result = false;
							if (result && !g740.js_eval(this, fld.js_visible, true)) result = false;
						}
						else {
							result = false;
						}
					}
					if (para.propertyName == 'color') {
						okPropertyName = true;
						result = '';
						if (row && fld) {
							if (row['row.color']) result = row['row.color'];
							if (row[para.fieldName + '.color']) result = row[para.fieldName + '.color'];
						}
					}
					if (!okPropertyName) {
						g740.systemError(procedureName, 'errorIncorrectPropertyName', para.propertyName);
					}
			        return result;
			    },
// Записать значение св-ва поля, работает только для текущей строки!!!
//	para.fieldName 		- имя поля (обязательный параметр)
//	para.id				- id строки (по умолчанию текущая строка)
//	para.propertyName	- 'value'|'text'
//	value				- новое значение
			    setFieldProperty: function (para) {
			        var procedureName = 'g740.RowSet[' + this.name + '].setFieldProperty';
					var result = false;
					if (this.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
					if (!para.fieldName) g740.systemError(procedureName, 'errorValueUndefined', 'fieldName');
					if (!para.propertyName) para.propertyName = 'value';
					this.isInActivity=false; // что-то где-то происходит (автоматическая перечитка в этом такте не нужна)
					var id = this.getFocusedId();
					if (typeof (para.id) != 'string') para.id = id;
					if (para.id != id) {
						if (!this.setFocusedId(para.id)) return false;
						var id = this.getFocusedId();
					}
					if (para.id === null) return false;
					if (para.id == id) {
						var row = null;
						var node = this.objTreeStorage.getNode(para.id, this.getFocusedParentNode());
						if (node) row = node.info;
						if (row) {
							var fields = this.getFields(node);
							var fld = fields[para.fieldName];
							if (!fld) return false;
							if ((para.propertyName == 'value') || (para.propertyName == 'text')) {
								var isReadOnly = this.getFieldProperty(
									{
										fieldName: para.fieldName,
										id: id,
										propertyName: 'readonly'
									}
								);
								if (!isReadOnly) {
									var newG740Value = '';
									if (para.propertyName == 'value') {
										newG740Value = g740.convertor.toG740(para.value, fld.type);
									}
									if (para.propertyName == 'text') {
										var v = g740.convertor.text2js(para.value, fld.type);
										newG740Value = g740.convertor.toG740(v, fld.type);
									}
									var oldG740Value = g740.convertor.toG740(row[para.fieldName + '.value'], fld.type);
									if (newG740Value != oldG740Value) {
										if (fld.maxlength) {
											if (fld.type=='num' && (newG740Value+'').length>fld.maxlength) newG740Value=0;
											if (fld.type=='string' && (newG740Value+'').length>fld.maxlength) newG740Value=newG740Value.substr(0,fld.maxlength);
										}
										
										row[para.fieldName + '.value'] = g740.convertor.toJavaScript(newG740Value, fld.type);
										if (fld.save && this.getExistUnsavedChanges()) {
											if (!this.exec({ requestName: 'save' })) return false;
										}
										else if (this.getRequestEnabled('change', para.fieldName)) {
											g740.execDelay.go({
												delay: 10,
												obj: this,
												func: this.exec,
												para: { requestName: 'change', requestMode: para.fieldName }
											});
											//this.exec({ requestName: 'change', requestMode: para.fieldName });
										}
										if (this.isFilter && this.isEnabled && this.isFilterAutoRefresh) {
											// для фильтра любое изменение полей приводит к перечитке подчиненных наборов строк
											// делаем перечитку с задержкой, иначе конфликт с change - change еще не отработал и перечитка может быть некорректной
											this.doRefreshChilds(true);
											
										}
										this.doG740Repaint({ isRowUpdate: true });
									}
									result = true;
								}
							}
							else {
								g740.systemError(procedureName, 'errorIncorrectPropertyName', para.propertyName);
							}
						}
					}
			        return result;
			    }
			}
		);

	    // Древовидное хранилище, уникальность id в пределах дочерних элементов узла
	    dojo.declare(
			"g740.TreeStorage",
			null,
			{
			    g740className: 'g740.TreeStorage',

			    isTraceEnabled: false,	// трассировка внутри объекта g740.TreeStorage
			    isTraceGet: false,		// трассировка чтения данных внутри объекта g740.TreeStorage

			    rootNode: null,			// корневой узел
			    // Создание и уничтожение объекта
			    // Создание экземпляра объекта				
			    constructor: function () {
			        var procedureName = 'g740.TreeStorage.constructor';
					this.g740className = 'g740.TreeStorage';
					this.isObjectDestroed = false;
					this.rootNode = {
						g740className: 'g740.TreeStorage.Node',
						id: 'root',
						nodeType: 'root',
						info: null,
						isFinal: false,
						isEmpty: false
					};
			    },
			    // Уничтожение экземпляра объекта
			    destroy: function () {
			        var procedureName = 'g740.TreeStorage.destroy';
					this.collapseNode(this.rootNode);
					delete this.rootNode;
			    },
			    // Поиск узлов
			    // Вернуть узел по id и родительскому узлу, если родительский узел не задан, то ищем в корне
			    getNode: function (id, parentNode) {
			        var procedureName = 'g740.TreeStorage.getNode';
					if (!this.rootNode) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!parentNode) parentNode = this.rootNode;
					var result = null;
					if (this.isNode(parentNode) && parentNode.childs) {
						result = parentNode.childs.nodes[id];
					}
			        return result;
			    },
			    // Вернуть следующий узел
			    getNextNode: function (node) {
			        var procedureName = 'g740.TreeStorage.getNextNode';
					if (!this.rootNode) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.isNode(node)) g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'node');
					return node.nextNode;
			    },
			    // Вернуть предыдущий узел
			    getPrevNode: function (node) {
			        var procedureName = 'g740.TreeStorage.getPrevNode';
					if (!this.rootNode) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.isNode(node)) g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'node');
					return node.prevNode;
			    },
			    // Вернуть родительский узел
			    getParentNode: function (node) {
			        var procedureName = 'g740.TreeStorage.getParentNode';
					if (!this.rootNode) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.isNode(node)) g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'node');
					return node.parentNode;
			    },
			    // Вернуть первый дочерний узел
			    getFirstChildNode: function (node) {
			        var procedureName = 'g740.TreeStorage.getFirstChildNode';
					if (!this.rootNode) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!node) node = this.rootNode;
					if (!this.isNode(node)) g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'node');
					var result = null;
					if (node.childs) result = node.childs.firstNode;
			        return result;
			    },
			    // Вернуть последний дочерний узел
			    getLastChildNode: function (node) {
			        var procedureName = 'g740.TreeStorage.getLastChildNode';
					if (!this.rootNode) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.isNode(node)) g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'node');
					var result = null;
					if (node.childs) result = node.childs.lastNode;
			        return result;
			    },
			    // Добавление и удаление узлов
			    // Вырезать узел, не уничтожая узел и не разрушая структуру детей: для последующей вставки
			    cutNode: function (node) {
			        var procedureName = 'g740.TreeStorage.cutNode';
					if (!this.rootNode) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.isNode(node)) g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'node');
					if (node == this.rootNode) g740.systemError(procedureName, 'errorIncorrectValue', 'node=root');
					if (node.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'node');

					var parentNode = node.parentNode;
					var prevNode = node.prevNode;
					var nextNode = node.nextNode;
					if (prevNode) prevNode.nextNode = nextNode;
					if (nextNode) nextNode.prevNode = prevNode;
					if (parentNode && parentNode.childs) {
						delete parentNode.childs.nodes[node.id];
						delete parentNode.childs._ordered;
						if (parentNode.childs.firstNode == node) parentNode.childs.firstNode = nextNode;
						if (parentNode.childs.lastNode == node) parentNode.childs.lastNode = prevNode;
					}

					node.parentNode = null;
					node.prevNode = null;
					node.nextNode = null;
			        return true;
			    },
			    // Вставить или перенести узел вместе со всеми детьми в другое место
			    pasteNode: function (node, parentNode, prevNode, nextNode) {
			        var procedureName = 'g740.TreeStorage.pasteNode';
					if (!this.rootNode) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.isNode(node)) g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'node');
					if (node == this.rootNode) g740.systemError(procedureName, 'errorIncorrectValue', 'node=root');
					if (node.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'node');

					if (!parentNode) parentNode = this.rootNode;
					if (!this.isNode(parentNode)) g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'parentNode');
					if (typeof (prevNode) == 'string') prevNode = this.getNode(prevNode, parentNode);
					if (typeof (nextNode) == 'string') nextNode = this.getNode(nextNode, parentNode);
					if (!this.isNode(prevNode)) prevNode = null;
					if (!this.isNode(nextNode)) nextNode = null;
					if (prevNode && (prevNode.parentNode != parentNode)) g740.systemError(procedureName, 'errorIncorrectValue', 'prevNode');
					if (nextNode && (nextNode.parentNode != parentNode)) g740.systemError(procedureName, 'errorIncorrectValue', 'nextNode');
					if (prevNode) nextNode = prevNode.nextNode;
					if (nextNode) prevNode = nextNode.prevNode;
					if (!parentNode.childs) {
						parentNode.childs = {
							firstNode: null,
							lastNode: null,
							nodes: {}
						};
					}
					parentNode.isFinal = false;
					parentNode.isEmpty = false;
					if ((node.parentNode != parentNode) && parentNode.childs.nodes[node.id]) g740.systemError(procedureName, 'errorNotUniqueValue', node.id);
					if (node.parentNode) this.cutNode(node);
					node.parentNode = parentNode;
					node.prevNode = null;
					node.nextNode = null;
					parentNode.childs.nodes[node.id] = node;
					delete parentNode.childs._ordered;
					if (!prevNode && !nextNode) prevNode = parentNode.childs.lastNode;
					if (prevNode) {
						node.prevNode = prevNode;
						node.nextNode = prevNode.nextNode;
						prevNode.nextNode = node;
					}
					if (nextNode) {
						node.nextNode = nextNode;
						node.prevNode = nextNode.prevNode;
						nextNode.prevNode = node;
					}
					if (!node.prevNode) parentNode.childs.firstNode = node;
					if (!node.nextNode) parentNode.childs.lastNode = node;
			        return true;
			    },
			    // Добавить и вернуть добавленный узел
			    appendNode: function (id, parentNode, prevNode, nextNode) {
			        var procedureName = 'g740.TreeStorage.appendNode';
					if (!this.rootNode) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!parentNode) parentNode = this.rootNode;
					if (!this.isNode(parentNode)) g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'parentNode');

					var node = {
						g740className: 'g740.TreeStorage.Node',
						id: id,
						nodeType: ''
					};
					this.pasteNode(node, parentNode, prevNode, nextNode);
			        return node;
			    },
			    // Удалить узел, рекурсивно удалив детей
			    removeNode: function (node) {
			        var procedureName = 'g740.TreeStorage.removeNode';
					if (!this.rootNode) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.isNode(node)) g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'node');
					if (node == this.rootNode) g740.systemError(procedureName, 'errorIncorrectValue', 'node=root');
					if (!this.isNode(node.parentNode)) g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'node');
					if (node.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'node');

					this.collapseNode(node);
					this.cutNode(node);
					delete node.parentNode;
					delete node.prevNode;
					delete node.nextNode;
					delete node.childs;
					node.isObjectDestroed = true;
			        return true;
			    },
			    // Свернуть узел, рекурсивно уничтожив все его дочерние подузлы
			    collapseNode: function (node) {
			        var procedureName = 'g740.TreeStorage.collapseNode';
					if (!this.rootNode) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.isNode(node)) g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'node');
					if (node.childs) {
						for (var id in node.childs.nodes) {
							var childNode = node.childs.nodes[id];
							if (this.isNode(childNode)) {
								this.collapseNode(childNode);
								delete childNode.parentNode;
								delete childNode.prevNode;
								delete childNode.nextNode;
								delete childNode.childs;
								childNode.isObjectDestroed = true;
							}
							node.childs.nodes[id] = null;
						}
						delete node.childs.firstNode;
						delete node.childs.lastNode;
						delete node.childs._ordered;
						delete node.childs;
					}
			        return true;
			    },
			    // Получить путь до узла от корня
				getNodePath: function(node) {
					var lst=[];
					while(node) {
						lst.push(node.id);
						node=node.parentNode;
					}
					var result=[];
					for(var i=lst.length-1; i>=0; i--) {
						result.push(lst[i]);
					}
					return result;
				},
				// Нумерация узлов, необходима для работы Grid
			    // Вернуть, при необходимости пересчитав, порядковый номер узла
			    getIndex: function (node) {
			        var procedureName = 'g740.TreeStorage.getIndex';
					if (!this.rootNode) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.isNode(node)) g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'node');
					if (this.isNode(node.parentNode)) this._buildChildsOrdered(node.parentNode);
			        return node._index;
			    },
			    // Вернуть, при необходимости пересчитав, упорядоченный массив дочерних узлов
			    getChildsOrdered: function (node) {
			        var procedureName = 'g740.TreeStorage.getChildsOrdered';
					var result = [];
					if (!this.rootNode) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.isNode(node)) g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'node');
					this._buildChildsOrdered(node);
					if (node.childs) result = node.childs._ordered;
			        return result;
			    },
			    // Упорядочить дочерние узлы, если порядок следования сбился
			    _buildChildsOrdered: function (node) {
			        var procedureName = 'g740.TreeStorage._buildChildsOrdered';
					if (!this.rootNode) g740.systemError(procedureName, 'errorAccessToDestroedObject');
					if (!this.isNode(node)) g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'node');
					if (!node.childs) return true;
					if (node.childs._ordered) return true;
					var i = 0;
					var lst = [];
					for (var childNode = node.childs.firstNode; childNode != null; childNode = childNode.nextNode) {
						lst.push(childNode);
						childNode._index = i;
						i++;
					}
					node.childs._ordered = lst;
			        return true;
			    },
			    // Проверка, является ли параметр допустимым узлом дерева
			    isNode: function (node) {
			        if (typeof (node) != 'object') return false;
			        if (node == null) return false;
			        if (node.g740className != 'g740.TreeStorage.Node') return false;
			        return true;
			    }
			}
		);

	    //	Интерфейс dojo.data.api для RowSet
	    dojo.declare(
			'g740.RowSetDataApi',
			null,
			{
			    g740className: 'g740.RowSetDataApi',
			    isTraceEnabled: false,	// трассировка внутри объекта g740.RowSetDataApi

			    objRowSet: null,
			    objTreeStorage: null,

			    // Создание и уничтожение объекта
			    // Создание экземпляра объекта
			    //	para.objRowSet
			    constructor: function (para) {
			        var procedureName = 'g740.RowSetDataApi.constructor';
					this.g740className = 'g740.RowSetDataApi';
					if (!para) g740.systemError(procedureName, 'errorValueUndefined', 'para');
					if (!para.objRowSet) g740.systemError(procedureName, 'errorValueUndefined', 'para.objRowSet');
					this.set('objRowSet', para.objRowSet);
			    },
			    destroy: function () {
		            this.set('objRowSet', null);
			    },
			    set: function (name, value) {
			        var procedureName = 'g740.RowSetDataApi.set';
			        if (name == 'objRowSet') {
			            if (this.objRowSet != value) {
			                this.objTreeStorage = null;
			                if (value != null) {
			                    if (typeof (value) != 'object') g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'objRowSet');
			                    if (value.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objRowSet');
			                    if (!value.objTreeStorage) g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'objRowSet');
			                    this.objTreeStorage = value.objTreeStorage;
			                }
			                this.objRowSet = value;
			            }
			            return true;
			        }
			        return false;
			    },
			    getFeatures: function () {
			        var procedureName = 'g740.RowSetDataApi.getFeatures';
					var result = {
						'dojo.data.api.Read': true,
						'dojo.data.api.Identity': true,
						'dojo.data.api.Write': true
					};
			        return result;
			    },
			    //	Реализация dojo.data.api.Read
			    getValue: function (node, fieldName, defa) {
			        var procedureName = 'g740.RowSetDataApi.getValue';
					var result = defa;
					if (!this.objRowSet) g740.systemError(procedureName, 'errorValueUndefined', 'objRowSet');
					if (this.objRowSet.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objRowSet');
					if (!this.objTreeStorage) g740.systemError(procedureName, 'errorValueUndefined', 'objTreeStorage');
					if (!this.objTreeStorage.isNode(node)) g740.systemError(procedureName, 'errorValueUndefined', 'node');
					var item = node.info;
					if (!item) g740.systemError(procedureName, 'errorValueUndefined', 'node.info');
					if (typeof (fieldName) != 'string') g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'fieldName');
					if (fieldName == 'id') result = node.id;
					var fields = this.objRowSet.getFields(node);
					var fld = fields[fieldName];
					if (fld) result = item[fieldName + '.value'];
			        return result;
			    },
			    getValues: function (node, fieldName) {
			        var procedureName = 'g740.RowSetDataApi.getValues';
					var result = [];
					if (this.hasAttribute(node, fieldName)) result = [this.getValue(node, fieldName)];
			        return result;
			    },
			    getAttributes: function (node) {
			        var procedureName = 'g740.RowSetDataApi.getAttributes';
					var result = [];

					if (!this.objRowSet) g740.systemError(procedureName, 'errorValueUndefined', 'objRowSet');
					if (this.objRowSet.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objRowSet');
					if (!this.objTreeStorage) g740.systemError(procedureName, 'errorValueUndefined', 'objTreeStorage');
					if (!this.objTreeStorage.isNode(node)) g740.systemError(procedureName, 'errorValueUndefined', 'node');

					result.push('id');
					var fields = this.objRowSet.getFields(node);
					for (var fieldName in fields) {
						result.push(fieldName);
					}
			        return result;
			    },
			    hasAttribute: function (node, fieldName) {
			        var procedureName = 'g740.RowSetDataApi.hasAttribute';
					if (!this.objRowSet) g740.systemError(procedureName, 'errorValueUndefined', 'objRowSet');
					if (this.objRowSet.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objRowSet');
					if (!this.objTreeStorage) g740.systemError(procedureName, 'errorValueUndefined', 'objTreeStorage');
					if (!this.objTreeStorage.isNode(node)) g740.systemError(procedureName, 'errorValueUndefined', 'node');
					if (typeof (fieldName) != 'string') g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'fieldName');

					if (fieldName == 'id') return true;
					var fields = this.objRowSet.getFields(node);
					if (fields[fieldName]) return true;
			        return false;
			    },
			    containsValue: function (node, fieldName, value) {
			        var procedureName = 'g740.RowSetDataApi.containsValue';
					if (!this.objRowSet) g740.systemError(procedureName, 'errorValueUndefined', 'objRowSet');
					if (this.objRowSet.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objRowSet');
					if (!this.objTreeStorage) g740.systemError(procedureName, 'errorValueUndefined', 'objTreeStorage');
					if (typeof (fieldName) != 'string') g740.systemError(procedureName, 'errorIncorrectTypeOfValue', 'fieldName');
					if (!this.objTreeStorage.isNode(node)) return false;
					var item = node.info;
					if (!item) return false;
					if (fieldName == 'id') return true;
					if (!item.isObjectDestroed) {
						var fields = this.objRowSet.getFields(node);
						if (fields[fieldName]) return true;
					}
			        return false;
			    },
			    isItem: function (node) {
			        var procedureName = 'g740.RowSetDataApi.isItem';
					if (!this.objRowSet) g740.systemError(procedureName, 'errorValueUndefined', 'objRowSet');
					if (this.objRowSet.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objRowSet');
					if (!this.objTreeStorage) g740.systemError(procedureName, 'errorValueUndefined', 'objTreeStorage');
					if (!this.objTreeStorage.isNode(node)) return false;
					if (!node.info) return false;
			        return true;
			    },
			    isItemLoaded: function (node) {
			        return this.isItem(node);
			    },
			    loadItem: function (args) {
			        return true;
			    },
			    fetch: function (args) {
			        var procedureName = 'g740.RowSetDataApi.fetch';
					if (!this.objRowSet) g740.systemError(procedureName, 'errorValueUndefined', 'objRowSet');
					if (this.objRowSet.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objRowSet');
					if (!this.objTreeStorage) g740.systemError(procedureName, 'errorValueUndefined', 'objTreeStorage');

					if (!args) args = {};
					args.isAborted = false;
					args.abort = function () {
						this.isAborted = true;
					};
					var obj = dojo.global;
					if (args.scope) obj = args.scope;

					var parentNode = null;
					var query = args.query;
					if (!query) query = {};
					if (query.parentNode && this.objTreeStorage.isNode(query.parentNode)) parentNode = query.parentNode;
					if (!parentNode) parentNode = this.objRowSet.getFocusedParentNode();

					var nodes = this.objTreeStorage.getChildsOrdered(parentNode);
					var start = 0;
					var count = nodes.length;
					if (args.start) start = args.start;
					if (args.count && args.count < count) count = args.count;

					try {
						if (typeof (args.onBegin) == 'function') args.onBegin.call(obj, count, args);

						var items = [];
						if ((typeof (args.onItem) == 'function') || (typeof (args.onComplete) == 'function')) {
							for (var i = start; i < (start + count) ; i++) {
								if (typeof (args.onItem) == 'function') {
									args.onItem.call(obj, nodes[i], args);
									if (args.isAbort) break;
								}
								else {
									if (typeof (args.onComplete) == 'function') items.push(nodes[i]);
								}
							}
						}
						if (typeof (args.onComplete) == 'function') {
							if (typeof (args.onItem) == 'function') items = null;
							args.onComplete.call(obj, items, args);
						}
					}
					catch (e) {
						if (typeof (args.onError) == 'function') args.onError.call(obj, e, args);
					}
			        return args;
			    },
			    close: function () {
			        var procedureName = 'g740.RowSetDataApi.close';
			    },
			    getLabel: function (node) {
			        var procedureName = 'g740.RowSetDataApi.getLabel';
					var result = undefined;
					if (!this.objRowSet) g740.systemError(procedureName, 'errorValueUndefined', 'objRowSet');
					if (this.objRowSet.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objRowSet');
					if (!this.objTreeStorage) g740.systemError(procedureName, 'errorValueUndefined', 'objTreeStorage');
					if (!this.objTreeStorage.isNode(node)) g740.systemError(procedureName, 'errorValueUndefined', 'node');
					var result = node.id;
					var fields = this.objRowSet.getFields(node);
					if (fields['name'] && node.info) {
						result = node.info['name.value'];
					}
			        return result;
			    },
			    getLabelAttributes: function (node) {
			        return ['id'];
			    },
			    //	Реализация dojo.data.api.Identity
			    getIdentity: function (node) {
			        var procedureName = 'g740.RowSetDataApi.getIdentity';
					var result = null;
					if (!this.objRowSet) g740.systemError(procedureName, 'errorValueUndefined', 'objRowSet');
					if (this.objRowSet.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objRowSet');
					if (!this.objTreeStorage) g740.systemError(procedureName, 'errorValueUndefined', 'objTreeStorage');
					if (this.objTreeStorage.isNode(node)) result = node.id;
			        return result;
			    },
			    getIdentityAttributes: function (node) {
			        var procedureName = 'g740.RowSetDataApi.getIdentityAttributes';
					if (!this.objRowSet) g740.systemError(procedureName, 'errorValueUndefined', 'objRowSet');
					if (this.objRowSet.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objRowSet');
					if (!this.objTreeStorage) g740.systemError(procedureName, 'errorValueUndefined', 'objTreeStorage');
					if (!this.objTreeStorage.isNode(node)) g740.systemError(procedureName, 'errorValueUndefined', 'node');
					return ['id'];
			    },
			    fetchItemByIdentity: function (args) {
			        var procedureName = 'g740.RowSetDataApi.fetchItemByIdentity';
					if (!this.objRowSet) g740.systemError(procedureName, 'errorValueUndefined', 'objRowSet');
					if (this.objRowSet.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objRowSet');
					if (!this.objTreeStorage) g740.systemError(procedureName, 'errorValueUndefined', 'objTreeStorage');

					var result = null;
					if (!args) args = {};
					var obj = dojo.global;
					if (args.scope) obj = args.scope;

					var parentNode = null;
					var query = args.query;
					if (!query) query = {};
					if (query.parentNode && this.objTreeStorage.isNode(query.parentNode)) parentNode = query.parentNode;
					if (!parentNode) parentNode = this.objRowSet.getFocusedParentNode();

					try {
						var id = args.identity.toString();
						var result = this.objTreeStorage.getNode(id, parentNode);
						if (!result) result = null;
						if (result && (typeof (args.onItem) == 'function')) args.onItem.call(obj, result);
					}
					catch (e) {
						if (typeof (args.onError) == 'function') args.onError.call(obj, e);
					}
			        return result;
			    },
			    //	Реализация dojo.data.api.Write
			    newItem: function (args, parentNode) {
			        var procedureName = 'g740.RowSetDataApi.newItem';
					if (!this.objRowSet) g740.systemError(procedureName, 'errorValueUndefined', 'objRowSet');
					if (this.objRowSet.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objRowSet');
					if (!this.objTreeStorage) g740.systemError(procedureName, 'errorValueUndefined', 'objTreeStorage');
					if (!this.objRowSet.getRequestEnabled('append')) g740.error('errorRequestAppendIsDisabled');
					if (!this.objRowSet.exec({ requestName: 'append' })) g740.error('errorRequestAppendIsDisabled');
					return this.objRowSet.getFocusedNode();
			    },
			    deleteItem: function (node) {
			        var procedureName = 'g740.RowSetDataApi.deleteItem';
					var result = false;
					if (!this.objRowSet) g740.systemError(procedureName, 'errorValueUndefined', 'objRowSet');
					if (this.objRowSet.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objRowSet');
					if (!this.objTreeStorage) g740.systemError(procedureName, 'errorValueUndefined', 'objTreeStorage');
					if (!this.objTreeStorage.isNode(node)) return false;
					if (this.objRowSet.getRequestEnabled('delete')) {
						if (this.objRowSet.getFocusedNode() != node) {
							if (!this.objRowSet.setFocusedNode(node)) return false;
						}
						result = this.objRowSet.exec({ requestName: 'delete' });
					}
			        return result;
			    },
			    setValue: function (node, fieldName, value) {
			        var procedureName = 'g740.RowSetDataApi.setValue';
					var result = false;
					if (!this.objRowSet) g740.systemError(procedureName, 'errorValueUndefined', 'objRowSet');
					if (this.objRowSet.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objRowSet');
					if (!this.objTreeStorage) g740.systemError(procedureName, 'errorValueUndefined', 'objTreeStorage');
					if (!this.objTreeStorage.isNode(node)) return false;
					if (node != this.objRowSet.getFocusedNode()) return false;
					result = this.objRowSet.setFieldProperty(
						{
							fieldName: fieldName,
							value: value,
							propertyName: 'value'
						}
					);
			        return result;
			    },
			    setValues: function (node, fieldName, values) {
			        var result = false;
			        if ((typeof (values) == 'object') && (values.length > 0)) {
			            result = this.setValue(node, fieldName, values[0]);
			        }
			        return result;
			    },
			    unsetAttribute: function (node, fieldName) {
			        return false;
			    },
			    save: function (args) {
			        var procedureName = 'g740.RowSetDataApi.save';
					var result = false;
					if (!args) args = {};
					var obj = dojo.global;
					if (args.scope) obj = args.scope;
					try {
						if (!this.objRowSet) g740.systemError(procedureName, 'errorValueUndefined', 'objRowSet');
						if (this.objRowSet.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objRowSet');
						if (!this.objTreeStorage) g740.systemError(procedureName, 'errorValueUndefined', 'objTreeStorage');
						if (this.objRowSet.getExistUnsavedChanges() && this.objRowSet.getRequestEnabled('save')) {
							result = this.objRowSet.exec({ requestName: 'save' });
							if (result && (typeof (args.onComplete) == 'function')) args.onComplete.call(obj);
						}
					}
					catch (e) {
						if (typeof (args.onError) == 'function') args.onError.call(obj, e);
					}
			        return result;
			    },
			    revert: function () {
			        var procedureName = 'g740.RowSetDataApi.revert';
					var result = false;
					if (!this.objRowSet) g740.systemError(procedureName, 'errorValueUndefined', 'objRowSet');
					if (this.objRowSet.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objRowSet');
					if (!this.objTreeStorage) g740.systemError(procedureName, 'errorValueUndefined', 'objTreeStorage');
					if (this.objRowSet.getExistUnsavedChanges()) {
						result = this.objRowSet.undoUnsavedChanges();
					}
			        return result;
			    },
			    isDirty: function (node) {
			        var procedureName = 'g740.RowSetDataApi.isDirty';
					var result = false;
					if (!this.objRowSet) g740.systemError(procedureName, 'errorValueUndefined', 'objRowSet');
					if (this.objRowSet.isObjectDestroed) g740.systemError(procedureName, 'errorAccessToDestroedObject', 'objRowSet');
					if (!this.objTreeStorage) g740.systemError(procedureName, 'errorValueUndefined', 'objTreeStorage');
					if (node) {
						if (node == this.objRowSet.getFocusedNode()) {
							var result = this.objRowSet.getExistUnsavedChanges();
						}
					}
					else {
						var result = this.objRowSet.getExistUnsavedChanges();
					}
			        return result;
			    }
			}
		);

	    return g740;
	}
)