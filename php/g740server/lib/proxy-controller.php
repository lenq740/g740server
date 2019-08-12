<?php
/**
 * @file
 * G740Server, работа с http и html
 *
 * @copyright 2018-2019 Galinsky Leonid lenq740@yandex.ru
 * This project is released under the BSD license
 */
require_once('lib-base.php');


/** Класс HttpConnector - доступ по HTTP

Список имен, которые надо блокировать в proxy
- x-frame-options
- set-cookie
- cache-control
- expires
- pragma
 */
class HttpConnector {
/// Использовать cookie
	public $curl_iscookie=true;
/// режим кодирования данных
	public $curl_encoding='';
/// максимальное время ожидания ответа в секундах
	public $curl_timeout=10;
/// строка useragent
	public $curl_useragent='Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36';
/// передаваемые сайту заголовки
	public $curl_httpheader=array(
//		'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*;q=0.8',
		'Accept-Language: ru,en-us;q=0.7,en;q=0.3',
		'Accept-Charset: utf-8, windows-1251;q=0.7,*;q=0.7'
	);


/// Считанный массив заголовков
	public $headers=Array();
/// Полезная информация, вытащенная из заголовков
	public $headersInfo=Array();

/** Получить содержимое по http адресу
 * 
 * @param	string	$href url
 * @return	string содержимое
 */
	public function httpGet($href) {
		for($i=0; $i<5; $i++) {
			$result=$this->_httpGet($href);
			if ($this->headers['content-encoding'] && $this->headers['content-encoding']!=$this->curl_encoding) {
				$this->curl_encoding=$this->headers['content-encoding'];
				continue;
			}
			if ($this->headersInfo['location']) {
				$href=$this->headersInfo['location'];
				continue;
			}
			break;
		}
		return $result;
	}
/** Попытка получить содержимое по http адресу
 * 
 * @param	string	$href url
 * @return	string содержимое
 */
	protected function _httpGet($href) {
		$this->headers=Array();
		$this->headersInfo=Array();

		$ch=curl_init($href);

		// настройка прокси
		/*
		$config['www.proxy']='';			// внешний прокси
		$config['www.proxy.port']='';		// порт
		$config['www.proxy.type']='';       // CURLPROXY_HTTP (по умолчанию), либо CURLPROXY_SOCKS4, CURLPROXY_SOCKS5, CURLPROXY_SOCKS4A или CURLPROXY_SOCKS5_HOSTNAME
		$config['www.proxy.auth']='';		// CURLAUTH_BASIC и CURLAUTH_NTLM
		$config['www.proxy.serv']='';		// сервис аудентификации
		$config['www.proxy.userpwd']='';    // [username]:[password]
		$config['www.proxy.tunnel']='';		// TRUE - туннелирование
		*/

		if (getCfg('www.proxy')		    ) curl_setopt ($ch, CURLOPT_PROXY,				getCfg('www.proxy')		    );
		if (getCfg('www.proxy.port')	) curl_setopt ($ch, CURLOPT_PROXYPORT,			getCfg('www.proxy.port')	);
		if (getCfg('www.proxy.type')	) curl_setopt ($ch, CURLOPT_PROXYTYPE,			getCfg('www.proxy.type')	);
		if (getCfg('www.proxy.auth')	) curl_setopt ($ch, CURLOPT_PROXYAUTH,			getCfg('www.proxy.auth')	);
		if (getCfg('www.proxy.serv')	) curl_setopt ($ch, CURLOPT_PROXY_SERVICE_NAME, getCfg('www.proxy.serv')	);
		if (getCfg('www.proxy.userpwd') ) curl_setopt ($ch, CURLOPT_PROXYUSERPWD,		getCfg('www.proxy.userpwd') );
		if (getCfg('www.proxy.tunnel')	) curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL,	getCfg('www.proxy.tunnel')	);

		if ($this->curl_iscookie) {
			$cookieFileName=realpath(pathConcat(
				getCfg('path.root'),
				getCfg('path.root.log'),
				'cookie.txt'
			));
			curl_setopt ($ch, CURLOPT_COOKIEFILE, $cookieFileName);
			curl_setopt ($ch, CURLOPT_COOKIEJAR, $cookieFileName);
		}
		if ($this->curl_useragent) curl_setopt($ch, CURLOPT_USERAGENT, $this->curl_useragent);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HEADER, true);
		if ($this->curl_httpheader) curl_setopt($ch, CURLOPT_HTTPHEADER, $this->curl_httpheader);
		if ($this->curl_encoding) curl_setopt($ch, CURLOPT_ENCODING , $this->curl_encoding);
		if ($this->curl_timeout) curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
		$response = curl_exec($ch);

		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		curl_close($ch);
		$header = substr($response, 0, $header_size);
		$result = substr($response, $header_size);

		$data=explode("\n",$header);
		array_shift($data);
		foreach($data as $part){
			$middle=explode(":",$part);
			$name=$middle[0];
			$value=trim(substr($part,strlen($name)+1,strlen($part)));
		}
		foreach($data as $item){
			$item=trim($item);
			if (!$item) continue;
			$n=strpos($item,':');
			if ($n===false) continue;
			$name=trim(strtolower(substr($item, 0, $n)));
			$value=trim(substr($item, $n+1, 9999));
			if ($name=='location') $this->headersInfo[$name]=$value;
			if ($name=='content-encoding') $this->headersInfo[$name]=strtolower($value);
			if ($name=='content-type') {
				$lst=explode(";",$value);
				$this->headersInfo['content-type']=$lst[0];
				foreach($lst as $lstItem) {
					if (mb_substr(trim(mb_strtolower($lstItem)),0,7)=='charset') {
						$lstCharSet=explode('=',$lstItem);
						$charset=strtolower(trim($lstCharSet[1]));
						$charset=str_replace('"','',$charset);
						$charset=str_replace("'",'',$charset);
						$this->headersInfo['content-charset']=trim($charset);
					}
				}
			}
			$this->headers[$name]=$item;
		}
		return $result;
	}
}

/** Класс HtmlParser
 */
class HtmlParser {
/// объект парсера
	protected $objDom=null;
/// все подчистить за собой
	function __destruct() {
		if ($this->objDom) {
			$this->objDom->clear();
			$this->objDom=null;
		}
	}
/** выполнить парсинг
 *
 * @param	string	$html url
 */
	public function load($html) {
		includeExtLibSimpleHtmlDom();
		if ($this->objDom) {
			$this->objDom->clear();
			$this->objDom=null;
		}
		$this->objDom=str_get_html($html);
		if (!$this->objDom) throw new Exception('Не удалось загрузить HTML в HtmlParser!!!');
	}

/** вернуть базовый путь до ссылок (тэг base в head)
 *
 * @param	string	$default значение по умолчанию
 * @return	string базовый путь до ссылок (тэг base в head)
 */
	public function getBaseHref($default='') {
		$result=$default;
		if (!$this->objDom) throw new Exception('HTML не загружен ...');
		foreach($this->objDom->find('head base') as $objBase) {
			$result=$objBase->href;
		}
		return $result;
	}

/// вернуть charset по содержимому html
	public function getCharSet() {
		$result='';
		if (!$this->objDom) throw new Exception('HTML не загружен ...');
		foreach($this->objDom->find('meta') as $objMeta) {
			if ($objMeta->charset!='') {
				$result=$objMeta->charset;
				break;
			}
			$txt=strtolower($objMeta->outertext);
			if (strpos($txt,'http-equiv')!==false) {
				$content=$objMeta->content;
				$lst=explode(";",$content);
				foreach($lst as $lstItem) {
					if (substr(trim(strtolower($lstItem)),0,7)=='charset') {
						$lstCharSet=explode('=',$lstItem);
						$charset=strtolower(trim($lstCharSet[1]));
						$charset=str_replace('"','',$charset);
						$charset=str_replace("'",'',$charset);
						$result=trim($charset);
						break;
					}
				}
			}
		}
		return $result;
	}
/** получить список ссылок из html
 *
 * @param	Array	$params
 * @return	Array список ссылок
 */
	public function getListOfHref($params) {
		$result=Array();
		if (!$this->objDom) throw new Exception('HTML не загружен ...');
		if ($params['link']) foreach($this->objDom->find('link') as $objLink) {
			if ($objLink->href) $result[$objLink->href]=$objLink->href;
		}
		if ($params['linkcss']) foreach($this->objDom->find('link') as $objLink) {
			if ($objLink->href) {
				$isCSS=false;
				if (strtolower($objLink->type)=='text/css') $isCSS=true;
				if (strtolower($objLink->rel)=='stylesheet') $isCSS=true;
				if (!$isCSS) {
					$data=explode("?",$objLink->href);
					$info=pathinfo($data[0]);
					$ext=strtolower($info['extension']);
					if ($ext=='css') $isCSS=true;
				}
				if ($isCSS) $result[$objLink->href]=$objLink->href;
			}
		}
		if ($params['img']) foreach($this->objDom->find('img') as $objImg) {
			if ($objImg->src) {
				if (substr(strtolower($objImg->src),0,5)=='data:') continue;
				$result[$objImg->src]=$objImg->src;
			}
		}
		if ($params['a']) foreach($this->objDom->find('a') as $objA) {
			if ($objA->href) $result[$objA->href]=$objA->href;
		}
		if ($params['iframe']) foreach($this->objDom->find('iframe') as $objIframe) {
			if ($objIframe->src) $result[$objIframe->src]=$objIframe->src;
		}
		if ($params['script']) foreach($this->objDom->find('script') as $objScript) {
			if ($objScript->src) $result[$objScript->src]=$objScript->src;
		}
		if ($params['style']) foreach($this->objDom->find('[style]') as $objDiv) {
			$style=$objDiv->style;
			if (!$style) continue;
			if (strpos($style,'background')===false) continue;
			$lst=explode(";",$style);
			foreach($lst as $lstItem) {
				if (!$lstItem) continue;
				$resItem=$lstItem;
				$n=strpos($lstItem,':');
				$name=strtolower(trim(substr($lstItem,0,$n)));
				$value=substr($lstItem,$n+1,9999);
				if ($name=='background-image' || $name=='background') {
					$n=strpos($value,'url');
					$valueBeforeUrl='';
					$valueUrl='';
					$valueAfterUrl='';
					if ($n!==false) {
						$valueBeforeUrl=substr($value,0,$n);
						$valueAfter=substr($value,$n,999);
						$n1=strpos($valueAfter,'(');
						$n2=strpos($valueAfter,')');
						if ($n2!==false) $valueAfterUrl=substr($valueAfter,$n2+1,999);
						if ($n1!==false && $n2!==false && $n2>$n1) {
							$valueUrl=substr($valueAfter,$n1+1,$n2-$n1-1);
						}
					}
					$valueUrl=str_replace("'",' ',$valueUrl);
					$valueUrl=str_replace(")",' ',$valueUrl);
					$valueUrl=str_replace("(",' ',$valueUrl);
					$valueUrl=trim($valueUrl);

					if ($valueUrl && substr(strtolower($valueUrl),0,5)!='data:') $result[$valueUrl]=$valueUrl;
				}
			}
		}
		return $result;
	}
/** подменить ссылки в html
 *
 * @param	Array	$lstHref
 * @param	Array	$params
 */
	public function replaceHref($lstHref, $params) {
		if (!$this->objDom) throw new Exception('HTML не загружен ...');
		if ($params['link']) foreach($this->objDom->find('link') as $objLink) {
			if ($objLink->href && $lstHref[$objLink->href]) $objLink->href=$lstHref[$objLink->href];
		}
		if ($params['img']) foreach($this->objDom->find('img') as $objImg) {
			if ($objImg->src && $lstHref[$objImg->src]) $objImg->src=$lstHref[$objImg->src];
		}
		if ($params['a']) foreach($this->objDom->find('a') as $objA) {
			if ($objA->href && $lstHref[$objA->href]) $objA->href=$lstHref[$objA->href];
			if ($objA->href && $params['a.target']) $objA->target=$params['a.target'];
		}
		if ($params['iframe']) foreach($this->objDom->find('iframe') as $objIframe) {
			if ($objIframe->src && $lstHref[$objIframe->src]) $objIframe->src=$lstHref[$objIframe->src];
		}
		if ($params['script']) foreach($this->objDom->find('script') as $objScript) {
			if ($objScript->src && $lstHref[$objScript->src]) $objScript->src=$lstHref[$objScript->src];
		}
		if ($params['style']) foreach($this->objDom->find('[style]') as $objDiv) {
			$style=$objDiv->style;
			if (!$style) continue;
			if (strpos($style,'background')===false) continue;
			$lst=explode(";",$style);
			$resStyle='';
			foreach($lst as $lstItem) {
				if (!$lstItem) continue;
				$resItem=$lstItem;
				$n=strpos($lstItem,':');
				$name=strtolower(trim(substr($lstItem,0,$n)));
				$value=substr($lstItem,$n+1,9999);
				if ($name=='background-image' || $name=='background') {
					$n=strpos($value,'url');
					$valueBeforeUrl='';
					$valueUrl='';
					$valueAfterUrl='';
					if ($n!==false) {
						$valueBeforeUrl=substr($value,0,$n);
						$valueAfter=substr($value,$n,999);
						$n1=strpos($valueAfter,'(');
						$n2=strpos($valueAfter,')');
						if ($n2!==false) $valueAfterUrl=substr($valueAfter,$n2+1,999);
						if ($n1!==false && $n2!==false && $n2>$n1) {
							$valueUrl=substr($valueAfter,$n1+1,$n2-$n1-1);
						}
					}

					$valueUrl=str_replace("'",' ',$valueUrl);
					$valueUrl=str_replace(")",' ',$valueUrl);
					$valueUrl=str_replace("(",' ',$valueUrl);
					$valueUrl=trim($valueUrl);
					if ($valueUrl) {
						$value=$valueUrl;
						if ($lstHref[$valueUrl]) $value=$lstHref[$valueUrl];
						if ($name=='background-image') {
							$resItem="background-image:url('{$value}')";
						}
						if ($name=='background') {
							$resItem="{$valueBeforeUrl} background:url('{$value}') {$valueAfterUrl}";
						}
					}
				}
				if ($resStyle) $resStyle.=';';
				$resStyle.=$resItem;
			}
			$objDiv->style=$resStyle;
		}
		return true;
	}
/// прочитать текст из html
	public function getText() {
		if (!$this->objDom) throw new Exception('HTML не загружен ...');
		$text=$this->objDom->plaintext;
		$text=str_replace("\n",' ',$text);
		$text=str_replace("\r",' ',$text);
		$text=str_replace("\t",' ',$text);
		$text=str_replace("&nbsp;",' ',$text);
		for ($i=0; $i<5; $i++) {
			$text=str_replace('       ',' ',$text);
			$text=str_replace('    ',' ',$text);
			$text=str_replace('  ',' ',$text);
		}
		if (!mb_check_encoding($text,'utf-8')) $text='';
		return trim($text);
	}
/// прочитать html
	public function getHtml() {
		if (!$this->objDom) throw new Exception('HTML не загружен ...');
		$result=$this->objDom->outertext;
		return $result;
	}
}

/** Класс HttpCopy - копирование сайта
 */
class HttpCopy {
/** Заменить недопустимые символы в href
 *
 * @param	string	$href url
 * @return	string преобразованный url
 */
	protected function prepareHref($href='') {
		$from=Array('&amp;','&quot;','&apos;','&lt;','&gt;');
		$to=Array('&','"',"'", '<','>');
		$result=str_replace($from, $to, $href);
		return $result;
	}
/** Копировать страницу сайта
 *
 * @param	string	$href url откуда копировать
 * @param	string	$path куда копировать
 * @return	string content-type
 */
	public function copyToPath($href, $path) {
		$objHttpConnector=new HttpConnector();
		$result=$objHttpConnector->httpGet($href);
		$headersInfo=$objHttpConnector->headersInfo;
		$headers=$objHttpConnector->headers;
		unset($objHttpConnector);

		$this->lstLink=Array();
		$this->lstLinkFull=Array();
		$this->hrefRoot=$href;
		if (!is_dir($path)) mkdir($path,0777, true);

		if ($headersInfo['content-type']=='text/html') {
			$charset=$headersInfo['content-charset'];
			if ($charset && $charset!='utf-8') $result=mb_convert_encoding($result, 'utf-8', $charset);
			$objHtmlParser=new HtmlParser();
			$objHtmlParser->load($result);
			if (!$charset) {
				$charset=$objHtmlParser->getCharSet();
				if ($charset && $charset!='utf-8') {
					$result=mb_convert_encoding($result, 'utf-8', $charset);
					$objHtmlParser->load($result);
				}
			}

			$linkCSSParams=Array(
				'linkcss'=>true
			);
			$htCSS=Array();
			$lst=$objHtmlParser->getListOfHref($linkCSSParams);
			foreach($lst as $href) {
				$htCSS[$href]=true;
				if (isset($this->lstLink[$href])) continue;
				$this->lstLink[$href]=$this->getHrefFileName($href,'css');
			}
			$linkParams=Array(
				'link'=>true
			);
			$lst=$objHtmlParser->getListOfHref($linkParams);
			foreach($lst as $href) {
				if (isset($htCSS[$href])) continue;
				if (isset($this->lstLink[$href])) continue;
				$this->lstLink[$href]='empty.html';
			}

			$linkParams=Array(
				'img'=>true,
				'style'=>true
			);
			$lst=$objHtmlParser->getListOfHref($linkParams);
			foreach($lst as $href) {
				if (isset($this->lstLink[$href])) continue;
				$data=explode("?",$href);
				$info=pathinfo($data[0]);
				$ext=strtolower($info['extension']);
				$this->lstLink[$href]='empty.jpg';
				if ($ext=='jpg' || $ext=='jpeg' || $ext=='png' || $ext=='gif' || $ext=='svg') {
					$this->lstLink[$href]=$this->getHrefFileName($href,$ext);
				}
			}

			$linkParams=Array(
				'a'=>true
			);
			$lst=$objHtmlParser->getListOfHref($linkParams);
			foreach($lst as $href) {
				if (isset($this->lstLink[$href])) continue;
				$this->lstLink[$href]='index.html';
			}
			$linkParams=Array(
				'iframe'=>true
			);
			$lst=$objHtmlParser->getListOfHref($linkParams);
			foreach($lst as $href) {
				if (isset($this->lstLink[$href])) continue;
				$this->lstLink[$href]='empty.html';
			}

			$linkParams=Array(
				'script'=>true
			);
			$lst=$objHtmlParser->getListOfHref($linkParams);
			foreach($lst as $href) {
				if (isset($this->lstLink[$href])) continue;
				$this->lstLink[$href]='empty.js';
			}

			$linkParams=Array(
				'img'=>true,
				'style'=>true,
				'link'=>true,
				'a'=>true,
				'iframe'=>true,
				'script'=>true
			);
			$objHtmlParser->replaceHref($this->lstLink, $linkParams);
			$result=$objHtmlParser->getHtml();
			if ($charset && $charset!='utf-8') {
				$result=mb_convert_encoding($result, $charset, 'utf-8');
			}

			file_put_contents(pathConcat($path,'empty.js'), '');
			file_put_contents(pathConcat($path,'empty.html'), '');
			file_put_contents(pathConcat($path,'empty.jpg'), '');

			$fileName=pathConcat($path,'index.html');
			file_put_contents($fileName, $result);
			unset($objHtmlParser);

			$objHttpConnector=new HttpConnector();
			foreach($this->lstLinkFull as $href=>$fileName) {
				if (!$href) continue;
				if (!$fileName) continue;

				$info=pathinfo($fileName);
				if ($info['extension']!='css') {
					$preparedHref=$this->prepareHref($href);
					$value=$objHttpConnector->httpGet($preparedHref);
					file_put_contents(pathConcat($path,$fileName), $value);
				}
			}

			for($i=0; $i<5; $i++) {
				$isProcess=false;
				$lstLinkFull=$this->lstLinkFull;
				foreach($lstLinkFull as $href=>$fileName) {
					if (!$href) continue;
					if (!$fileName) continue;
					if (is_file(pathConcat($path,$fileName))) continue;
					$preparedHref=$this->prepareHref($href);
					$value=$objHttpConnector->httpGet($preparedHref);
					$info=pathinfo($fileName);
					if ($info['extension']=='css') {
						$value=$this->prepareCSS($href, $value);
					}
					file_put_contents(pathConcat($path,$fileName), $value);
					$isProcess=true;
				}
				if (!$isProcess) break;
			}
			unset($objHttpConnector);
		}
		else {
			$lstContentType=Array(
				'application/msword'=>'doc',
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx',
				'application/vnd.ms-excel'=>'xls',
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'xlsx'
			);
			$ext=$lstContentType[$headersInfo['content-type']];
			if (!$ext) throw new Exception("Неизвестный тип файла '{$headersInfo['content-type']}'");
			$fileName=pathConcat($path,'index'.'.'.$ext);
			file_put_contents($fileName, $result);
		}
		return $headersInfo['content-type'];
	}
/** Обработать CSS для выявления подгружаемых элементов
 *
 * @param	string	$hrefCSS
 * @param	string	$valueCSS
 * @return	string обработанный CSS
 */
	protected function prepareCSS($hrefCSS, $valueCSS) {
		global $_objHttpCopy;
		$oldHrefRoot=$this->hrefRoot;
		$_objHttpCopy=$this;
		try {
			$this->hrefRoot=$hrefCSS;
			$pregExpr='/url\s*\(([^)]*)\)/m';
			$result=preg_replace_callback(
				$pregExpr,
				function ($matches) {
					global $_objHttpCopy;
					return $_objHttpCopy->prepareCSSMatches($matches);
				},
				$valueCSS
			);
		}
		finally {
			$this->hrefRoot=$oldHrefRoot;
			$_objHttpCopy=null;
		}
		return $result;
	}
/** Вспомогательная функция для обработки CSS
 *
 * @param	Array	$matches
 * @return	string обработанная строка
 */
	protected function prepareCSSMatches($matches) {
		$result=$matches[0];
		$href=$matches[1];
		$href=str_replace('"','',$href);
		$href=str_replace("'",'',$href);
		if (substr($href,0,5)=='data:') return $result;

		$fileName=$href;
		$ext='';
		$n=strpos($fileName,'?');
		if ($n!==false) $fileName=substr($fileName,0,$n);
		$info=pathinfo($fileName);
		$ext=$info['extension'];

		if ($ext=='svg' || $ext=='png' || $ext=='jpg' || $ext=='jpeg' || $ext=='css') {
			$fileName=$this->getHrefFileName($href, $ext);
			$result="url('{$fileName}')";
		}
		else {
			$result="url('empty.jpg')";
		}
		return $result;
	}
/** Преобразовать ссылку в полный адрес в интернете
 *
 * @param	string	$href url
 * @return	string полный адрес в интернете
 */
	protected function getHrefFull($href) {
		$info=parse_url($this->hrefRoot);

		$hrefScheme=$info['scheme'];
		$hrefHost=$info['host'];
		if ($info['port']) $hrefHost.=':'.$info['port'];

		$hrefPath=$info['path'];
		if (substr(strtolower($href),0,7)=='http://') return $href;
		else if (substr(strtolower($href),0,8)=='https://') return $href;
		else if (substr(strtolower($href),0,2)=='//') return "{$hrefScheme}:{$href}";
		else if (substr(strtolower($href),0,1)=='/') return "{$hrefScheme}://{$hrefHost}{$href}";
		return "{$hrefScheme}://{$hrefHost}/{$hrefPath}/{$href}";
	}
/** Преобразовать ссылку в имя файла в папке на диске
 *
 * @param	string	$href url
 * @param	string	$ext расширение файла
 * @return	string имя файла в папке на диске
 */
	protected function getHrefFileName($href, $ext) {
		$hrefFull=$this->getHrefFull($href);
		if (!$this->lstLinkFull[$hrefFull]) $this->lstLinkFull[$hrefFull]=getGUID().'.'.$ext;
		return $this->lstLinkFull[$hrefFull];
	}
/// Корень, относительно которого идет закачка
	protected $hrefRoot='';
/// Массив соответствий ссылок и файлов
	protected $lstLink=null;
/// Массив соответствий полных адресов интернета и имен файлов
	protected $lstLinkFull=null;
}
/// Вспомогательная глобальная переменная для передачи данных в preg_replace_callbac
$_objHttpCopy=null;

/// Подключение внешней библиотеки simplehtmldom
function includeExtLibSimpleHtmlDom() {
	includeExtLib('simplehtmldom/simple_html_dom.php');
}

/** Вызов proxy - читает, выполняет замены, выдает через echo
 * 
 * @param	string	$href url
 */
function proxyGoEcho($href) {
	$objHttpConnector=new HttpConnector();
	$result=$objHttpConnector->httpGet($href);
	$headersInfo=$objHttpConnector->headersInfo;
	$headers=$objHttpConnector->headers;
	unset($objHttpConnector);

	if ($headersInfo['content-type']=='text/html') {
		$charset=$headersInfo['content-charset'];
		if ($charset && $charset!='utf-8') $result=mb_convert_encoding($result, 'utf-8', $charset);
		$objHtmlParser=new HtmlParser();
		$objHtmlParser->load($result);
		if (!$charset) {
			$charset=$objHtmlParser->getCharSet();
			if ($charset && $charset!='utf-8') {
				$headersInfo['content-charset']=$charset;
				$result=mb_convert_encoding($result, 'utf-8', $charset);
				$objHtmlParser->load($result);
			}
		}

		$linkParams=Array(
			'link'=>true,
			'img'=>true,
			'a'=>true,
			'a.target'=>'_blank',
			'iframe'=>true,
			'script'=>true,
			'style'=>true
		);
		$lstHref=$objHtmlParser->getListOfHref($linkParams);
		$info=parse_url($href);
		$hrefScheme=$info['scheme'];
		$hrefHost=$info['host'];
		$hrefPath=$info['path'];
		$lstHrefResult=Array();
		foreach($lstHref as $href) {
			if (substr(strtolower($href),0,5)=='data:') continue;
			else if (substr(strtolower($href),0,7)=='http://') continue;
			else if (substr(strtolower($href),0,8)=='https://') continue;
			else if (substr(strtolower($href),0,2)=='//') $lstHrefResult[$href]="{$hrefScheme}:{$href}";
			else if (substr(strtolower($href),0,1)=='/') $lstHrefResult[$href]="{$hrefScheme}://{$hrefHost}{$href}";
			else $lstHrefResult[$href]="{$hrefScheme}://{$hrefHost}/{$hrefPath}/{$href}";
		}


		if (strtolower($_SERVER['REQUEST_SCHEME'])=='https') {
			$linkCssParams=Array(
				'linkcss'=>true
			);
			$lstCss=$objHtmlParser->getListOfHref($linkCssParams);
			$hrefProxy='https://'.$_SERVER['SERVER_NAME'];
			if ($_SERVER['SERVER_PORT']!='443') $hrefProxy.=':'.$_SERVER['SERVER_PORT'];
			$hrefProxy.=$_SERVER['SCRIPT_NAME'].'?css=1&amp;href=';

			foreach($lstHref as $href) {
				if (!$lstCss[$href]) continue;
				if (substr(strtolower($href),0,5)=='data:') continue;
				if (substr(strtolower($href),0,8)=='https://') continue;
				$hrefTo=$lstHrefResult[$href];
				if (!$hrefTo) $hrefTo=$href;
				$lstHrefResult[$href]=$hrefProxy.$hrefTo;
			}
		}

		$objHtmlParser->replaceHref($lstHrefResult, $linkParams);
		$result=$objHtmlParser->getHtml();
		if ($charset && $charset!='utf-8') {
			$result=mb_convert_encoding($result, $charset, 'utf-8');
		}
		unset($objHtmlParser);
	}
	else if ($headersInfo['content-type']=='text/css') {
// Сюда мы попадаем, если грузим css через наш proxy - используется при работе системы по https
// на всякий случай вычищаем непонятные внешние ссылки, на которые ссылается такой css
		if ($_REQUEST['css']==1) {
			global $_proxyCssInfo;
			$_proxyCssInfo=Array();
			$hrefProxy='https://'.$_SERVER['SERVER_NAME'];
			if ($_SERVER['SERVER_PORT']!='443') $hrefProxy.=':'.$_SERVER['SERVER_PORT'];
			$hrefProxy.='/proxy';
			$_proxyCssInfo['hrefProxy']=$hrefProxy;
			$_proxyCssInfo['hrefRoot']=$_REQUEST['href'];

			$pregExpr='/url\s*\(([^)]*)\)/m';
			$result=preg_replace_callback(
				$pregExpr,
				function($matches) {
					$result=$matches[0];
					$href=$matches[1];
					$href=str_replace('"','',$href);
					$href=str_replace("'",'',$href);
					if (substr($href,0,5)=='data:') return $result;

					global $_proxyCssInfo;
					$hrefProxy=$_proxyCssInfo['hrefProxy'];
					$hrefRoot=$_proxyCssInfo['hrefRoot'];

/*
					$fileName=$href;
					$ext='';
					$n=strpos($fileName,'?');
					if ($n!==false) $fileName=substr($fileName,0,$n);
					$info=pathinfo($fileName);
					$ext=$info['extension'];

					if (strtolower($ext)=='css') {
						$info=parse_url($hrefRoot);
						$hrefScheme=$info['scheme'];
						$hrefHost=$info['host'];
						$hrefPath=$info['path'];

						$hrefFull="{$hrefScheme}://{$hrefHost}/{$hrefPath}/{$href}";
						if (substr(strtolower($href),0,7)=='http://') $hrefFull=$href;
						else if (substr(strtolower($href),0,8)=='https://') $hrefFull=$href;
						else if (substr(strtolower($href),0,2)=='//') $hrefFull="{$hrefScheme}:{$href}";
						else if (substr(strtolower($href),0,1)=='/') $hrefFull="{$hrefScheme}://{$hrefHost}{$href}";

						return "url({$hrefProxy}/index.php?css=1&href={$hrefFull})";
					}
					else {
*/
						return "url({$hrefProxy}/empty.jpg)";
/*
					}
*/
				},
				$result
			);
		}
	}
	else {
		$lstContentType=Array(
			'application/msword'=>'doc',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'doc',
			'application/vnd.ms-excel'=>'xls',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'xls',
			'application/pdf'=>'pdf',

			'application/rtf'=>'doc',
			'application/x-rtf'=>'doc',
			'text/richtext'=>'doc',

			'application/x-compressed'=>'zip',
			'application/x-zip-compressed'=>'zip',
			'application/zip'=>'zip',
			'multipart/x-zip'=>'zip',

			'application/vnd.ms-powerpoint'=>'ppt',
			'application/mspowerpoint'=>'ppt',
			'application/powerpoint'=>'ppt',
			'application/x-mspowerpoint'=>'ppt',

			'image/gif'=>'*',
			'text'=>'*',
			'text/plain'=>'*',
			'application/plain'=>'*',
			'image/jpeg'=>'*',
			'image/pjpeg'=>'*',
			'image/png'=>'*',
			'image/tiff'=>'*',
			'image/x-tiff'=>'*'
		);
		$lstTarget=Array(
			'pdf'=>'_blank'
		);

		if ($headersInfo['content-type']) {
			$ext=$lstContentType[$headersInfo['content-type']];
		}
		else {
			$ext='empty';
		}
		if (!$ext) $ext='question';
		if ($ext!='*') {
			$htmlTarget='';
			if ($lstTarget[$ext]) $htmlTarget='target="_blank"';

			$attrHref=str2Attr($href);
			$result=<<<HTML
<!DOCTYPE HTML>
<html style="height:100%;margin:0px;padding:0px;">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<meta http-equiv="Cache-Control" content="no-cache">
	<meta http-equiv="Content-Language" content="ru"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body style="height:100%;margin:0px;padding:0px;">
<a href="{$attrHref}" {$htmlTarget}><div style="height:100%;background-repeat:no-repeat;background-size:contain;background-position:center;background-image:url('img-{$ext}.svg')"></div></a>
</body >
</html>
HTML;
			$headers=Array();
		}
	}

	$headers['cache-control']='Cache-Control: no-cache,no-store,max-age=0,must-revalidate';
	if ($headersInfo['content-type']=='text/html') {
		if ($headersInfo['content-charset']) $headers['content-type']='Content-Type: text/html; charset='.$headersInfo['content-charset'];
		unset($headers['content-length']);
	}

	foreach($headers as $name=>$header) {
		if (!$name) continue;
		if ($name=='x-frame-options') continue;
		if ($name=='set-cookie') continue;
		if ($name=='cache-control') continue;
		if ($name=='expires') continue;
		if ($name=='pragma') continue;
		if ($name=='transfer-encoding') continue;
		if ($name=='connection') continue;
		if ($name=='content-encoding') continue;
		if ($name=='date') continue;
		if ($name=='last-modified') continue;
		if ($name=='content-disposition') continue;
		if ($name=='strict-transport-security') continue;
		header($header);
	}
	echo $result;
}
/// Вспомогательная глобальная переменная для передачи данных в preg_replace_callbac
$_proxyCssInfo=Array();
