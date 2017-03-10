<div class="block">
    <fieldset>
        <legend>SEO Tools</legend>
        <form method="get" action="{'/ezengage/google_cse_seo'|ezurl('no')}">
            <div class="block">
                <label for="get_site_meta_overview">Query</label>
                <input type="text" name="get_site_meta_overview" id="get_site_meta_overview" />
                <input type="submit" value="Get Sites Results Meta Overview" />
            </div>
        </form>
    </fieldset>
</div>
{if is_set( $seo_results )}
    <h3>Overview</h3>
    {foreach $seo_results as $result}
        <ul>
        {if and( is_set( $result.description ), $result.description|ne('') )}
            <li><strong>Description:</strong> {$result.description}</li>
        {/if}
        {if and( is_set( $result.keywords ), $result.keywords|ne('') )}
            <li><strong>Keywords:</strong> {$result.keywords}</li>
        {/if}
        </ul>
    {/foreach}
    <h3>Totals</h3>
    <ul>
    {foreach $totals as $index => $total}
        <li><strong>{$index}:</strong> {$total}</li>
    {/foreach}
    </ul>
{/if}