<!DOCTYPE html>
<!--[if lt IE 9 ]><html class="unsupported-ie ie" lang="{$site.http_equiv.Content-language|wash}"><![endif]-->
<!--[if IE 9 ]><html class="ie ie9" lang="{$site.http_equiv.Content-language|wash}"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html lang="{$site.http_equiv.Content-language|wash}"><!--<![endif]-->
<head>
{def $basket_is_empty   = cond( $current_user.is_logged_in, fetch( shop, basket ).is_empty, 1 )
     $user_hash         = concat( $current_user.role_id_list|implode( ',' ), ',', $current_user.limited_assignment_value_list|implode( ',' ) )}

{include uri='design:engage/meta.tpl' module_result=$module_result}
{include uri='design:page_head_displaystyles.tpl'}

{if is_set( $extra_cache_key )|not}
    {def $extra_cache_key = ''}
{/if}

{def $pagedata        = ezpagedata()
     $inner_column_size = $pagedata.inner_column_size
     $outer_column_size = $pagedata.outer_column_size}

{cache-block keys=array( $module_result.uri, $basket_is_empty, $current_user.contentobject_id, $extra_cache_key )}
{def $pagestyle        = $pagedata.css_classes
     $locales          = fetch( 'content', 'translation_list' )
     $current_node_id  = $pagedata.node_id}

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

{include uri='design:page_head.tpl'}
{include uri='design:page_head_style.tpl'}
{include uri='design:page_head_script.tpl'}

</head>
<body>
<!-- Complete page area: START -->

<div id="page" class="{$pagestyle}">

    {if and( is_set( $pagedata.persistent_variable.extra_template_list ),
             $pagedata.persistent_variable.extra_template_list|count() )}
    {foreach $pagedata.persistent_variable.extra_template_list as $extra_template}
        {include uri=concat('design:extra/', $extra_template)}
    {/foreach}
    {/if}

    <!-- Header area: START -->
    {include uri='design:page_header.tpl'}
    <!-- Header area: END -->

    {cache-block keys=array( $module_result.uri, $user_hash, $extra_cache_key )}

    <div class="navbar main-navi">
        <!-- Top menu area: START -->
        {if $pagedata.top_menu}
            {include uri='design:page_topmenu.tpl'}
        {/if}
        <!-- Top menu area: END -->

        <!-- Path area: START -->
        {if $pagedata.show_path}
            {include uri='design:page_toppath.tpl'}
        {/if}
        <!-- Path area: END -->
    </div>

    <!-- Toolbar area: START -->
    {if and( $pagedata.website_toolbar, $pagedata.is_edit|not)}
        {include uri='design:page_toolbar.tpl'}
    {/if}
    <!-- Toolbar area: END -->

    <!-- Columns area: START -->
    <div class="container">
        <div class="row">
            <!-- Side menu area: START -->
            {if $pagedata.left_menu}
                {include uri='design:page_leftmenu.tpl'}
            {/if}
            <!-- Side menu area: END -->
    {/cache-block}
    {/cache-block}
            <!-- Main area: START -->
            {include uri='design:page_mainarea.tpl'}
            <!-- Main area: END -->
            {cache-block keys=array( $module_result.uri, $user_hash, $access_type.name, $extra_cache_key )}

            <!-- Extra area: START -->
            {if $pagedata.extra_menu}
                {include uri='design:page_extramenu.tpl'}
            {/if}
            <!-- Extra area: END -->
        </div>
    </div>
    <!-- Columns area: END -->

    <!-- Footer area: START -->
    {include uri='design:page_footer.tpl'}
    <!-- Footer area: END -->
</div>
<!-- Complete page area: END -->

<!-- Footer script area: START -->
{include uri='design:page_footer_script.tpl'}
<!-- Footer script area: END -->

{/cache-block}

{* This comment will be replaced with actual debug report (if debug is on). *}
<!--DEBUG_REPORT-->
<div class="engage-suggestion hide alert alert-info"></div>
{literal}
<script id="ezengage_test_html" type="text/x-handlebars-template">
<div class="block-type-read-more">
    <div class="attribute-header">
        <h2>More Articles</h2>
    </div>
    <article class="selected-products">
        {{#each items}}
        <div class="clearfix" data-contentId="{{id}}">
            <div class="attribute-image">
                <a href="{{link}}">
                    <img src="{{img}}" alt="{{title}}" class="img-responsive">
                </a>
            </div>
            <div class="product-info">
                <div class="attribute-header">
                    <h3><a href="{{link}}">{{title}}</a></h3>
                    <p>{{ summary }}</p>
                    <p class="kicker">{{ byline }}</p>
                </div>
            </div>
        </div>
        {{/each}}
    </article>
</div>
</script>
<script id="ezengage_notification_html" type="text/x-handlebars-template">
    <div class="clearfix">
        <button class="close pull-right" onclick="$( '.engage-suggestion' ).slideUp();">x</button>
        <h4>Read more</h4>
    </div>
    <div class="items">
        {{#each items}}
        <div class="item">
            <a href="{{link}}" class="image">
                    <img src="{{img}}" alt="{{title}}" class="img-responsive">
            </a>
            <a href="{{link}}" class="alert-link">{{title}}</a>
        </div>
        {{/each}}
    </div>
</script>
{/literal}
</body>
</html>
