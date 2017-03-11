<h4>eZEngage</h4>

<ul>
    {if fetch( 'user', 'has_access_to', hash( 'module', 'ezengage', 'function', 'dashboard' ) )}
    <li>
        <div><a href={"/ezengage/dashboard"|ezurl}>eZEngage Dashboard</a></div>
    </li>
    {/if}
    {if fetch( 'user', 'has_access_to', hash( 'module', 'ezengage', 'function', 'social_insights' ) )}
    <li>
        <div><a href={"/ezengage/social_insights"|ezurl}>Social Insights</a></div>
    </li>
    {/if}
    {if fetch( 'user', 'has_access_to', hash( 'module', 'ezengage', 'function', 'social_media_query' ) )}
    <li>
        <div><a href={"/ezengage/youtube_query"|ezurl}>Youtube Query</a></div>
    </li>
    {/if}
    {if fetch( 'user', 'has_access_to', hash( 'module', 'ezengage', 'function', 'google_cse_seo' ) )}
    <li>
        <div><a href={"/ezengage/google_cse_seo"|ezurl}>Google CSE SEO</a></div>
    </li>
    {/if}
    {if fetch( 'user', 'has_access_to', hash( 'module', 'ezengage', 'function', 'dashboard' ) )}
    <li>
        <div><a href={"/ezengage/chart_js_test"|ezurl}>ChartJS sample</a></div>
    </li>
    {/if}
</ul>

