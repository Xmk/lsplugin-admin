<?php
/*-------------------------------------------------------
*
*	 LiveStreet Engine Social Networking
*	 Copyright © 2008 Mzhelskiy Maxim
*
*--------------------------------------------------------
*
*	 Official site: www.livestreet.ru
*	 Contact e-mail: rus.engine@gmail.com
*
*	 GNU General Public License, version 2:
*	 http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*
---------------------------------------------------------
*/

/*
 *	Модуль работы с шаблонами движка
 *
 *	by PSNet
 *	http://psnet.lookformp3.net
 *
*/

class PluginAdmin_ModuleSkin extends Module {
	
	const SKIN_PREVIEW_FILE = 'template_preview.png';
	const SKIN_XML_FILE = 'template_info.xml';
	
	
	protected $sSkinPath = null;
	protected $sLang = null;
	
	
	public function Init() {
		$this -> sSkinPath = Config::Get ('path.root.server') . '/templates/skin/';
		$this -> sLang = $this -> Lang_GetLang ();
	}
	
	
	protected function GetSkinXmlFile ($sSkinName) {
		return $this -> sSkinPath . $sSkinName . '/' . self::SKIN_XML_FILE;
	}
	
	
	protected function GetSkinPreviewFile ($sSkinName) {
		return $this -> sSkinPath . $sSkinName . '/' . self::SKIN_PREVIEW_FILE;
	}
	
	
	protected function GetSkinNames () {
		return array_map ('basename', glob ($this -> sSkinPath . '*', GLOB_ONLYDIR));
	}
	
	
	protected function GetSkinXmlData ($sSkinXmlFile) {
		if ($oXml = @simplexml_load_file ($sSkinXmlFile)) {
			$this -> Xlang ($oXml, 'name', $this -> sLang);
			$this -> Xlang ($oXml, 'author', $this -> sLang);
			$this -> Xlang ($oXml, 'description', $this -> sLang);
			
			$oXml -> homepage = $this -> Text_Parser ((string) $oXml -> homepage);
			return $oXml;
		}
		return null;
	}
	
	
	public function GetSkinList ($aFilter = array ()) {
		$aSkins = array ();
		foreach ($this -> GetSkinNames () as $sSkinName) {
			$aSkinInfo = array ();
			// имя шаблона
			$aSkinInfo ['name'] = $sSkinName;
			// информация о шаблоне
			$sSkinXmlFile = $this -> GetSkinXmlFile ($sSkinName);
			if (file_exists ($sSkinXmlFile)) {
				$aSkinInfo ['info'] = $this -> GetSkinXmlData ($sSkinXmlFile);
			}
			// превью шаблона
			$sSkinPreviewFile = $this -> GetSkinPreviewFile ($sSkinName);
			if (file_exists ($sSkinPreviewFile)) {
				$aSkinInfo ['preview'] = $this -> GetWebPath ($sSkinPreviewFile);
			}
			$aSkins [$sSkinName] = Engine::GetEntity ('PluginAdmin_Skin', $aSkinInfo);
		}
		
		if (isset ($aFilter ['order']) and $aFilter ['order'] == 'name') {
			natsort ($aSkins);
		}
		return $aSkins;
	}
	
	
	/**
	 * Получает значение параметра из XML на основе языковой разметки
	 *
	 * @param SimpleXMLElement $oXml	XML узел
	 * @param string           $sProperty	Свойство, которое нужно вернуть
	 * @param string           $sLang	Название языка
	 */
	protected function Xlang ($oXml, $sProperty, $sLang) {														// todo: copy from plugin module, todo: reuse from plugin?
		$sProperty=trim($sProperty);

		if (!count($data=$oXml->xpath("{$sProperty}/lang[@name='{$sLang}']"))) {
			$data=$oXml->xpath("{$sProperty}/lang[@name='default']");
		}
		$oXml->$sProperty->data=$this->Text_Parser(trim((string)array_shift($data)));
	}
	
	
	protected function GetWebPath ($sPath) {																					// todo: in engine export this funcs into tools module
		return $this -> Image_GetWebPath ($sPath);
	}
	

}

?>