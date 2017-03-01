<h4>eZEngage</h4>

<ul>
    {if fetch( 'user', 'has_access_to', hash( 'module', 'ezengage', 'function', 'social_insights' ) )}
    <li>
        <div><a href={"/ezengage/social_insights"|ezurl}>Social Insights</a></div>
    </li>
    {/if}
</ul>

