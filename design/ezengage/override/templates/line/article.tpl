{* Article - Line view *}
{def $content_size = '8'}

<div class="content-view-line engage-list-item">
    <article class="class-article row"
             data-engage-key="id" 
             data-engage-type="value" 
             data-engage-value="{$node.contentobject_id}">

    {if $node.data_map.image.has_content}
    <div class="span2">
        <div class="attribute-image"
             data-engage-key="img" 
             data-engage-type="image">
            {attribute_view_gui image_class=articlethumbnail href=$node.url_alias|ezurl attribute=$node.data_map.image}
        </div>
    </div>
        {set $content_size = '6'}
    {/if}

    <div class="span{$content_size}">
        <div class="attribute-header">
            <h2
                data-engage-key="link" 
                data-engage-type="link">
                <a href="{$node.url_alias|ezurl( 'no' )}" class="teaser-link"
                    data-engage-key="title" 
                    data-engage-type="text">{$node.data_map.title.content|wash()}</a>
            </h2>
        </div>

        <div class="attribute-byline with-comments"
                    data-engage-key="byline" 
                    data-engage-type="text">
            <span class="date">
                {$node.object.published|l10n(shortdatetime)}
            </span>
        {if $node.data_map.author.content.is_empty|not()}
            <span class="author">
                {attribute_view_gui attribute=$node.data_map.author}
            </span>
        {/if}
        </div>

        {if $node.data_map.intro.content.is_empty|not}
        <div class="attribute-short"
                    data-engage-key="summary" 
                    data-engage-type="text">
            {attribute_view_gui attribute=$node.data_map.intro}
        </div>
        {/if}
    </div>

    </article>
</div>

{undef $content_size}
