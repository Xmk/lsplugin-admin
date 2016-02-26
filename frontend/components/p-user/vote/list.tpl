{**
 * Список голосов
 *}

{$component = 'p-user-vote-list'}
{component_define_params params=[ 'votes', 'pagination' ]}

{if $votes}
    <table class="table">
        <thead>
            <tr>
                {include file="{$aTemplatePathPlugin.admin}forms/sorting_cell.tpl"
                    sCellClassName='targetid'
                    mSortingOrder='target_id'
                    mLinkHtml=$aLang.plugin.admin.users.votes.table_header.target_id
                    sBaseUrl="{router page="admin/users/votes/{$oUser->getId()}"}"}

                {include file="{$aTemplatePathPlugin.admin}forms/sorting_cell.tpl"
                    sCellClassName='vote_direction'
                    mSortingOrder='vote_direction'
                    mLinkHtml=$aLang.plugin.admin.users.votes.table_header.vote_direction
                    sBaseUrl="{router page="admin/users/votes/{$oUser->getId()}"}"}

                {include file="{$aTemplatePathPlugin.admin}forms/sorting_cell.tpl"
                    sCellClassName='vote_value'
                    mSortingOrder='vote_value'
                    mLinkHtml=$aLang.plugin.admin.users.votes.table_header.vote_value
                    sBaseUrl="{router page="admin/users/votes/{$oUser->getId()}"}"}

                {include file="{$aTemplatePathPlugin.admin}forms/sorting_cell.tpl"
                    sCellClassName='vote_date'
                    mSortingOrder='vote_date'
                    mLinkHtml=$aLang.plugin.admin.users.votes.table_header.vote_date
                    sBaseUrl="{router page="admin/users/votes/{$oUser->getId()}"}"}

                {include file="{$aTemplatePathPlugin.admin}forms/sorting_cell.tpl"
                    sCellClassName='vote_ip'
                    mSortingOrder='vote_ip'
                    mLinkHtml=$aLang.plugin.admin.users.votes.table_header.vote_ip
                    sBaseUrl="{router page="admin/users/votes/{$oUser->getId()}"}"}

                <th>
                    {$aLang.plugin.admin.users.votes.table_header.target_object}
                </th>
            </tr>
        </thead>
        <tbody>
            {foreach $votes as $vote}
                <tr>
                    <td>
                        {$vote->getTargetId()}
                    </td>
                    <td>
                        {$vote->getDirection()}
                    </td>
                    <td>
                        {$vote->getValue()}
                    </td>
                    <td>
                        {$vote->getDate()}
                    </td>
                    <td>
                        {$vote->getIp()}
                    </td>
                    <td>
                        <a href="{$vote->getTargetFullUrl()}"
                           target="_blank"
                           title="{$vote->getTargetTitle()|escape}">{$vote->getTargetTitle()|escape|truncate:100:'...'}</a>
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>

    {include file="{$aTemplatePathPlugin.admin}forms/elements_on_page.tpl"
        sFormActionPath="{router page='admin/votes/ajax-on-page'}"
        iCurrentValue = Config::Get('plugin.admin.votes.per_page')}

    {component 'admin:pagination' total=+$aPaging.iCountPage current=+$aPaging.iCurrentPage url="{$aPaging.sBaseUrl}/page__page__/{$aPaging.sGetParams}"}
{else}
    {component 'admin:blankslate' text=$aLang.plugin.admin.users.votes.no_votes}
{/if}