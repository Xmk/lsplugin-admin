{**
 * Сброс данных
 *}

{extends file="{$aTemplatePathPlugin.admin}layouts/layout.base.tpl"}


{block name='layout_page_title'}
	{$aLang.plugin.admin.utils.reset_n_clear.title}
{/block}


{block name='layout_content'}
	{**
	 * Сброс данных
	 *}
	{* todo: добавить иконки *}
	<h2 class="page-header">{$aLang.plugin.admin.utils.reset_n_clear.datareset.title}</h2>
	
	<div class="mb-20">
		{$aLang.plugin.admin.utils.reset_n_clear.datareset.info}
	</div>

	<div class="mb-10">
		<a class="js-question" href="{router page='admin/utils/reset_n_clear/resetallbansstats'}?security_ls_key={$LIVESTREET_SECURITY_KEY}"
				>{$aLang.plugin.admin.utils.reset_n_clear.datareset.resetallbansstats}</a>
	</div>

	{hook run='admin_utils_reset_n_clear_datareset_item'}

{/block}