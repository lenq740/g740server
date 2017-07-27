//-----------------------------------------------------------------------------
// Трассировка
//	включение: g740.config.isTraceEnabled
//-----------------------------------------------------------------------------
define(
	[],
	function() {
		if (typeof(g740)=='undefined') g740={};

// Ошибка
		g740.error=function(messageId, message) {
			var msg='';
			if (messageId) {
				msg+='. '+g740.getMessage(messageId);
			}
			if (message) {
				msg+='. '+message;
			}
			throw (new Error(msg));
		};
// Системные ошибки
		g740.systemError=function(procedureName, messageId, message) {
			var msg=g740.getMessage('errorSystemInProcedure')+' '+ procedureName;
			if (messageId) {
				msg+='. '+g740.getMessage(messageId);
			}
			if (message) {
				msg+='. '+message;
			}
			throw (new Error(msg));
		};
// Ошибки в серверных ответах
		g740.responseError=function(messageId, message) {
			var msg=g740.getMessage('errorInResponse');
			if (messageId) {
				msg+='. '+g740.getMessage(messageId);
			}
			if (message) {
				msg+='. '+message;
			}
			throw (new Error(msg));
		};
// Информационные сообщения
		g740.showMessage=function(message) {
			alert(message);
		};
		g740.showError=function(message, objOwner) {
			var objForm=null;
			var objFocusedPanel=null
			if (objOwner) {
				if (objOwner.g740className=='g740.RowSet' && !objOwner.isObjectDestroed) objForm=objOwner.objForm;
				if (objOwner.g740className=='g740.Form' && !objOwner.isObjectDestroed) objForm=objOwner;
			}
			if (objForm && !objForm.isObjectDestroed && objForm.objFocusedPanel) objFocusedPanel=objForm.objFocusedPanel;
			var objDialog=new g740.DialogConfirm(
				{ 
					duration: 0, 
					draggable: false,
					mode: 'error',
					messageText: message,
					objOwner: objFocusedPanel
				}
			);
			objDialog.show();
			return objDialog;
		};

// Диалог подтверждения, c ассинхронным выполнением операции по закрытии
// 	para.messageId - код вопроса
//	para.messageText - текст вопроса
// 	para.onCloseOk - процедура, выполняемая по Ok
// 	para.onClolseCancel - процедура, выполняемая по Ok
//	para.closePara
// 	para.closeObj - контекст выпонения
//	para.objOwner - куда возвращать фокус ввода по закрытии
		g740.showConfirm=function(para) {
			var objDialog=new g740.DialogConfirm(
				{ 
					duration: 0, 
					draggable: false,
					mode: 'confirm',
					messageId: para.messageId,
					messageText: para.messageText,
					onCloseOk: para.onCloseOk,
					onCloseCancel: para.onCloseCancel,
					closePara: para.closePara,
					closeObj: para.closeObj,
					objOwner: para.objOwner
				}
			);
			objDialog.show();
		};
		
		g740.trace={
			_builderErrors: [],
			goBuilderStart: function() {
				this._builderErrors=[];
			},
			goBuilderEnd: function() {
				if (this._builderErrors.length>0) {
					console.log({errors:this._builderErrors});
					g740.error('errorBuilderForm');
				}
				this._builderErrors=[];
			},
			goBuilder: function(para) {
				if (para.messageId) {
					para.message=g740.getMessage(para.messageId);
					delete para.messageId;
				}
				this._builderErrors.push(para);
			}
		};
		return g740;
	}
);
