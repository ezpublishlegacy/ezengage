{if is_set( $setup_youtube )}
    <h2>Set up your youtube data access</h2>
    <p>You must set up your youtube data access</p>
    <ul>
        <li>Go to <a href="https://console.developers.google.com/apis/credentials">google console apis credentials</a></li>
        <li>Create 2 OAuth Client ID credentials, one <b>"web"</b> and another <b>"other"</b></li>
        <li>For the <b>"web"</b> one, add <b>{$youtube_redirect}</b> to the <b>"Authorized redirect URIs"</b> list.
            <br>If you are using your localhost machine you must change your host settings (and probably the siteaccesses settings) so you make your admin siteaccess accessible by the http://localhost url.</li>
        <li>Copy the <b>"Client ID"</b> and <b>"Client secret"</b> from the <b>"other"</b> one to the ezengage.ini.append.php file ( SocialInsights/YoutubeClientId and SocialInsights/YoutubeClientSecret )</li>
        <li>Go to the <a href="{'/ezengage/youtube_query'|ezurl('no')}">"Youtube Query"</a> page and complete the install giving the app the necessary permissions.</li>
    </ul>
{/if}

{if is_set( $setup_disqus )}
    <h2>Set up your disqus data access</h2>
    <p>You must set up your disqus data access</p>
    <ul>
        <li>Go to <a href="https://disqus.com/api/applications/">disqus applications</a></li>
        <li>Create a new app ( if you have not done yet ).</li>
        <li>Copy the <b>"Secret Key"</b> from the <b>"app" info</b> to the ezengage.ini.append.php file ( SocialInsights/DisqusKey ).
        <li>You must update ezengage.ini.append.php file ( SocialInsights/DisqusForum ), for example, for https://testforum.disqus.com  the value would be "testforum".</li>

    </ul>
{/if}


{if is_set( $setup_twitter )}
    <h2>Set up your twitter data access</h2>
    <p>You must set up your twitter data access</p>
    <ul>
        <li>Go to <a href="https://apps.twitter.com/">twitter applications</a></li>
        <li>Create a new app ( if you have not done yet ).</li>
        <li>Go to "Keys and Access Tokens",  create the "Access Token" ( if you have not done yet ).</li>
        <li>Copy the app keys ( Consumer Key (API Key), Consumer Secret (API Secret), Access Token,  Access Token Secret ) to the ezengage.ini.append.php file ( TwitterKey, TwitterSecret, TwitterTokenKey, TwitterTokenSecret ).</li>
    </ul>
{/if}