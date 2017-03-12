{ezscript_require( 'backend/Chart.bundle.min.js' )}
    <div id="canvas-holder" style="width:100%">
        <canvas id="chart-area" />
    </div>
{def $current_year = currentdate()|datetime( 'custom', '%Y' )
     $current_month = currentdate()|datetime( 'custom', '%n' )
     $past_month = 0
     $past_year = 0
     $date_timestamps = array()
     $end_date = 0
     $count = 0
     $total = 0}
    <script>
        var chart_data = [];
        var chart_labels = [];
{for 12 to 0 as $past_months}
    {set $past_month = sub( $current_month, $past_months )
         $past_year = $current_year}
    
    {if $past_month|le(0)}
        {set $past_month = sum(12,$past_month)}
        {set $past_year = sub($past_year,1)}
    {/if}
    {set $date_timestamps = $date_timestamps|append( maketime( 0, 0, 0, $past_month, 1, $past_year ) )}
{/for}
{foreach $date_timestamps as $index => $date_timestamp}
            {if $index|lt(12)}
                {set $end_date = $date_timestamps[$index|inc]|dec}
            {else}
                {set $end_date = currentdate()}
            {/if}
            /* {$date_timestamp|datetime( 'custom', '%h:%i %a %d %F %Y' )} - {$end_date|datetime( 'custom', '%h:%i %a %d %F %Y' )} */
            {set $count = fetch('content', 'tree_count',
                                        hash( 
                                                'parent_node_id', 2
                                                , 'main_node_only', true()
                                                , 'class_filter_type', 'include'
                                                , 'class_filter_array', array( 'article' )
                                                , 'attribute_filter', array( 'and', array( 'published', 'between', array( $date_timestamp, $end_date ) ) )
                                        )
                         )
                  $total = sum( $total, $count )}
            chart_data.push({$count});
            chart_labels.push("{$date_timestamp|datetime( 'custom', '%F %Y' )}");
{/foreach}

{literal}

        window.chartColors = {
                red: 'rgb(255, 99, 132)',
                orange: 'rgb(255, 159, 64)',
                yellow: 'rgb(255, 205, 86)',
                green: 'rgb(75, 192, 192)',
                blue: 'rgb(54, 162, 235)',
                purple: 'rgb(153, 102, 255)',
                grey: 'rgb(231,233,237)'
        };

        window.randomScalingFactor = function() {
                return (Math.random() > 0.5 ? 1.0 : -1.0) * Math.round(Math.random() * 100);
        }
        
        var config = {
            type: 'bar',
            data: {
                datasets: [{
                    data: chart_data,
                    label: 'Articles published per month',
                    backgroundColor: 'rgb(54, 162, 235)',
                }],
                labels: chart_labels
            },
            options: {
                responsive: true
            }
        };

        window.onload = function() {
            var ctx = document.getElementById("chart-area").getContext("2d");
            window.myPie = new Chart(ctx, config);
        };
        
    </script>
{/literal}

<h3>Total: {$total}</h3>