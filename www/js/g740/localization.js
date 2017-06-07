//-----------------------------------------------------------------------------
//	Локализация, в настоящий момент поддержка английского и русского языка
//-----------------------------------------------------------------------------
define(
	[],
	function() {
		if (typeof(g740)=='undefined') g740={};
		
		g740._messages=dojo.i18n.getLocalization('g740', 'messages');
// Локализация сообщения по Id
		g740.getMessage=function(messageId) {
			var result=g740._messages[messageId];
			if (!result) result=g740._messages['messageUnknown'];
			return result;
		}
// Локализация региональных настроек
		g740.getRegionConfig=function(name) {
			var result=g740._messages['regionConfig'];
			if (!result) result={};
			if (name) result=result[name];
			return result;
		}
		return g740;
	}
);