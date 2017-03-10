
<form method="get" action="{'/ezengage/youtube_query'|ezurl('no')}">
    <div class="block">
        <input type="submit" name="run_test" value="Run test" />
    </div>
</form>
<div class="block">
    <fieldset>
        <legend>SEO Tools</legend>
        <form method="get" action="{'/ezengage/youtube_query'|ezurl('no')}">
            <div class="block">
                <label for="get_channel_keywords_id">Channel ID</label>
                <input type="text" name="get_channel_keywords_id" id="get_channel_keywords_id" />
                <input type="submit" name="get_channel_keywords" value="Get Channel Keywords" />
            </div>
        </form>
        <form method="get" action="{'/ezengage/youtube_query'|ezurl('no')}">
            <div class="block">
                <label for="get_video_tags_id">Video ID</label>
                <input type="text" name="get_video_tags_id" id="get_video_tags_id" />
                <input type="submit" name="get_video_tags" value="Get Video Tags" />
            </div>
        </form>
    </fieldset>
    <fieldset>
        <legend>Check Subscriber</legend>
        <form method="get" action="{'/ezengage/youtube_query'|ezurl('no')}">
            <div class="block">
                <label for="subscriber_check_from_id">Is the Channel ID</label>
                <input type="text" name="subscriber_check_from_id" id="subscriber_check_from_id" />
            </div>
            <div class="block">
                <label for="subscriber_check_target_id">Subscribed to Channel ID?</label>
                <input type="text" name="subscriber_check_target_id" id="subscriber_check_target_id" />
            </div>
            <input type="submit" name="check_subscriber" value="Check Subscriber" />
        </form>
    </fieldset>
    <fieldset>
        <legend>Get Channel ID</legend>
        <form method="get" action="{'/ezengage/youtube_query'|ezurl('no')}">
            <div class="block">
                <label for="get_channel_id">Channel Custom Name</label>
                <input type="text" name="get_channel_id" id="get_channel_id" />
                <input type="submit" value="Get Channel ID" />
            </div>
        </form>
    </fieldset>
</div>

{if is_set($run_test)}
    <h2>Run test results</h2>
    <h3>Activities list test</h3>
    {$activities|dump("show",10)}
    <h3>Videos list test</h3>
    {$videos|dump("show",10)}
    <h3>CommentThreads list test</h3>
    {$commentThreads|dump("show",10)}
    <h3>Subscriptions list test</h3>
    {$subscriptions|dump("show",10)}
{/if}

{if is_set( $get_channel_keywords_result )}
    <h2>Get Channel Keywords Results</h2>
    {if not( $get_channel_keywords_result )}
        <h3>Error</h3>
    {else}
        {if is_set( $get_channel_keywords_result.brandingSettings.channel.title )}
            <h3>Channel Title</h3>
            <p>{$get_channel_keywords_result.brandingSettings.channel.title}</p>
        {/if}
        {if is_set( $get_channel_keywords_result.brandingSettings.channel.description )}
            <h3>Channel Description</h3>
            <p>{$get_channel_keywords_result.brandingSettings.channel.description}</p>
        {/if}
        {if is_set( $get_channel_keywords_result.brandingSettings.channel.keywords )}
            <h3>Keywords</h3>
            <p>{$get_channel_keywords_result.brandingSettings.channel.keywords}</p>
        {else}
            <h3>No Keywords</h3>
        {/if}
    {/if}
{/if}

{if is_set( $get_channel_id_result )}
    <h2>Get Channel Keywords Results</h2>
    {if not( $get_channel_id_result )}
        <h3>Error</h3>
    {else}
        <p>Channel ID: {$get_channel_id_result.id}</p>
    {/if}
{/if}

{if is_set( $check_subscriber_result )}
    <p>Channel {$check_subscriber_result.from_channel} <b>is{if $check_subscriber_result.subscribed|not()} NOT{/if}</b> subscribed to Channel {$check_subscriber_result.to_channel}</p>
{/if}

{if is_set( $get_video_tags_result )}
    <h2>Get Video Keywords Results</h2>
    {if not( $get_video_tags_result )}
        <h3>Error</h3>
    {else}
        {if is_set( $get_video_tags_result.snippet.title )}
            <h3>Video Title</h3>
            <p>{$get_video_tags_result.snippet.title}</p>
        {/if}
        {if is_set( $get_video_tags_result.snippet.description )}
            <h3>Video Description</h3>
            <p>{$get_video_tags_result.snippet.description}</p>
        {/if}
        {if is_set( $get_video_tags_result.snippet.tags )}
            <h3>Tags</h3>
            <p>{$get_video_tags_result.snippet.tags|implode(', ')}</p>
        {else}
            <h3>No Tags</h3>
        {/if}
    {/if}
{/if}