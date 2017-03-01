{def $pagedataEngage = ezpagedata()}

{if is_set( $pagedataEngage.persistent_variable.engage_data )}
    {def $engageData = $pagedataEngage.persistent_variable.engage_data}
    <meta id="engage-page" name="engage-page" content="TRUE" {foreach $engageData as $engageInfoAttribute => $engageinfo} data-{$engageInfoAttribute}="{$engageinfo}"{/foreach} />
{elseif and( is_set($module_result), is_set($module_result.content_info) )}
    <meta id="engage-page" name="engage-page" content="TRUE" data-contentId="{$module_result.content_info.object_id}" data-content-type="{$module_result.content_info.class_identifier}" />
{else}
    <meta id="engage-page" name="engage-page" content="TRUE" data-contentId="-1" data-classIdentifier="none" />
{/if}