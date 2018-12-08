<div class="dashboard-graph">
    <a href="graphs/{{ $param }}/type={{ $graph_type }}/from={{ $from }}">
        <img class="minigraph-image"
             width="{{ $dimensions['x'] }}"
             height="{{ $dimensions['y'] }}"
             src="graph.php?{{ $param }}&from={{ $from }}&to={{ $to }}&width={{ $dimensions['x'] }}&height={{ $dimensions['y'] }}&type={{ $graph_type }}&legend={{ $graph_legend }}&absolute=1"
        />
    </a>
</div>
