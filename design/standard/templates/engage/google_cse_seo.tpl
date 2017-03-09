
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