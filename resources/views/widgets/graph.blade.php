<div class="dashboard-graph">
    <a href="graphs/{{ implode($params, '/') }}/type={{ $graph_type }}/from={{ $from }}/to={{ $to }}">
        <img class="minigraph-image"
             width="{{ $dimensions['x'] }}"
             height="{{ $dimensions['y'] }}"
             src="graph.php?{{ implode($params, '&') }}&from={{ $from }}&to={{ $to }}&width={{ $dimensions['x'] }}&height={{ $dimensions['y'] }}&type={{ $graph_type }}&legend={{ $graph_legend }}&absolute=1"
        />
    </a>
</div>
