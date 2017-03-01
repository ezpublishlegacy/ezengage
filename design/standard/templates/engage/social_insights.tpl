{foreach $socialInsights as $index1 => $insightClass}
    <h2>{$index1}</h2>
    {foreach $insightClass as $index2 => $comments}
        <h3>{$index2}</h3>
        <table class="list">
            <tr>
                <th>name</th>
                <th>username</th>
                <th>comment</th>
                <th>scores</th>
                <th></th>
            </tr>
            {foreach $comments as $comment}
            <tr>
                <td>{$comment['name']}</td>
                <td>{$comment['username']}</td>
                <td>{$comment['comment']}</td>
                <td>
                    {foreach $comment['scores'] as $index3 => $score}
                        {$index3}={$score}{delimiter}, {/delimiter}
                    {/foreach}
                </td>
                <td><a href="{$comment['link']}">Link</a></td>
            </tr>
            {/foreach}
        </table>
    {/foreach}
{/foreach}