<?php
/**
 * LiveStreet CMS
 * Copyright © 2013 OOO "ЛС-СОФТ"
 * 
 * ------------------------------------------------------
 * 
 * Official site: www.livestreetcms.com
 * Contact e-mail: office@livestreetcms.com
 * 
 * GNU General Public License, version 2:
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * 
 * ------------------------------------------------------
 * 
 * @link http://www.livestreetcms.com
 * @copyright 2013 OOO "ЛС-СОФТ"
 * @author Serge Pustovit (PSNet) <light.feel@gmail.com>
 * 
 */

/*
 *	Работа с настройками плагинов
 */

class PluginAdmin_ActionAdmin_EventSettings extends Event {

	/**
	 * Показать настройки плагина
	 *
	 * @return bool
	 */
	public function EventShow() {
		/*
		 * корректно ли имя конфига
		 */
		if (!$sConfigName = $this->getParam(1) or !is_string($sConfigName)) {
			$this->Message_AddError($this->Lang_Get('plugin.admin.errors.wrong_config_name'), $this->Lang_Get('error'));
			return false;
		}
		/*
		 * активирован ли этот плагин
		 */
		if (!$this->PluginAdmin_Settings_CheckPluginNameIsActive($sConfigName)) {
			$this->Message_AddError($this->Lang_Get('plugin.admin.errors.plugin_need_to_be_activated'), $this->Lang_Get('error'));
			return false;
		}
		/*
		 * получить набор настроек
		 */
		$aSettingsAll = $this->PluginAdmin_Settings_GetConfigSettings($sConfigName);
		
		$this->Viewer_Assign('aSettingsAll', $aSettingsAll);
		$this->Viewer_Assign('sConfigName', $sConfigName);
		$this->Lang_AddLangJs(array('plugin.admin.errors.some_fields_are_incorrect'));
	}


	/**
	 * Сохранить настройки (запрос может быть выполнен обычным способом так и через аякс, в зависимости от настройки соответствующего параметра в конфиге плагина)
	 *
	 * @return mixed
	 */
	public function EventSaveConfig() {
		/*
		 * получить тип ответа на запрос сохранения настроек
		 */
		if ($bAjax = isAjaxRequest()) {
			$this->Viewer_SetResponseAjax('json');
		}
		
		$this->Security_ValidateSendForm();
		/*
		 * если была нажата кнопка
		 */
		if (isPost('submit_save_settings')) {
			/*
			 * успешно ли сохранение настроек
			 */
			$bResult = $this->SaveSettings();
			/*
			 * если успешно и это обычный запрос - написать "ок"
			 */
			if ($bResult and !$bAjax) {
				$this->Message_AddNotice('Ok', '', true);
			}
			/*
			 * если это аякс - загрузить весь набор ошибок для показа на форме
			 */
			if ($bAjax) {
				/*
				 * через специальный метод админки
				 */
				$this->Viewer_AssignAjax('aParamErrors', $this->Message_GetParamsErrors());
			}
		}
		/*
		 * если это обычный запрос - сделать редирект
		 */
		if (!$bAjax) {
			return $this->RedirectToReferer();
		}
	}


	/**
	 * Выполнить сохранение настроек
	 *
	 * @return bool
	 */
	protected function SaveSettings() {
		/*
		 * корректно ли имя конфига
		 */
		if (!$sConfigName = $this->getParam(1) or !is_string($sConfigName)) {
			$this->Message_AddError($this->Lang_Get('plugin.admin.errors.wrong_config_name'), $this->Lang_Get('error'));
			return false;
		}
		/*
		 * является ли набор настроек настройками движка или это активированный плагин
		 */
		if ($sConfigName != ModuleStorage::DEFAULT_KEY_NAME and !$this->PluginAdmin_Settings_CheckPluginNameIsActive($sConfigName)) {
			$this->Message_AddError($this->Lang_Get('plugin.admin.errors.plugin_need_to_be_activated'), $this->Lang_Get('error'));
			return false;
		}
		/*
		 * получение всех параметров, их валидация и сверка с описанием структуры и запись в отдельную инстанцию конфига
		 */
		if (!$this->PluginAdmin_Settings_ParsePOSTDataIntoSeparateConfigInstance($sConfigName)) {
			/*
			 * список ошибок уже создан с помощью специального метода модуля Message при проверке и будет передан пользователю вызывающим методом
			 */
			return false;
		}
		/*
		 * сохранить все настройки плагина в БД
		 */
		$this->PluginAdmin_Settings_SaveConfigByKey($sConfigName);
		return true;
	}


	/**
	 * Получение настроек ядра по группе
	 *
	 * @param array $aKeysToShow			ключи группы для показа (множество)
	 * @param array $aKeysToExcludeFromList	ключи, которые необходимо исключить (подмножество)
	 * @return bool
	 */
	protected function ShowSystemSettings($aKeysToShow = array(), $aKeysToExcludeFromList = array()) {
		$sConfigName = ModuleStorage::DEFAULT_KEY_NAME;
		$aSettingsAll = $this->PluginAdmin_Settings_GetConfigSettings($sConfigName, $aKeysToShow, $aKeysToExcludeFromList);

		$this->Viewer_Assign('aSettingsAll', $aSettingsAll);
		$this->Viewer_Assign('sConfigName', $sConfigName);
		$this->Viewer_Assign('aKeysToShow', $aKeysToShow);
		return true;
	}


	/**
	 * Получить ключи для показа и исключенные по группе
	 *
	 * @param $sGroupName	имя группы, как она записана в конфиге админки
	 * @return bool
	 */
	protected function GetGroupsListAndShowSettings($sGroupName) {
		return $this->ShowSystemSettings(
			$this->aCoreSettingsGroups[$sGroupName]['allowed'],
			$this->aCoreSettingsGroups[$sGroupName]['exclude']
		);
	}


	/**
	 * Этот магический метод показывает настройки для каждой, заданной в конфиге админки, группы
	 *
	 * @param $sName
	 * @param $aArgs
	 * @return bool|mixed
	 * @throws Exception
	 */
	public function __call($sName, $aArgs) {
		/*
		 * если это вызов для показа системных настроек ядра
		 */
		if (strpos($sName, $this->sCallbackMethodToShowSystemSettings) !== false) {
			/*
			 * пробуем получить имя группы настроек как оно должно быть записано в конфиге
			 */
			$sGroupName = strtolower(str_replace($this->sCallbackMethodToShowSystemSettings, '', $sName));
			/*
			 * если такая группа настроек существует
			 */
			if (isset($this->aCoreSettingsGroups[$sGroupName])) {
				return $this->GetGroupsListAndShowSettings($sGroupName);
			}
			/*
			 * это сообщение не будет никогда показано при текущих настройках, но пусть будет для отладки
			 */
			throw new Exception('Admin: error: there is no settings group name as "' . $sGroupName . '"');
		}
		/*
		 * это обращение к ядру
		 */
		return parent::__call($sName, $aArgs);
	}

}

?>