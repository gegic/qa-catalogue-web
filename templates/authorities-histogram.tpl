<link rel="stylesheet" href="styles/histogram.css">
<h3>histogram</h3>

<svg class="histogram-chart" width="960" height="300"></svg>
<ul>
  <li>y: number of records</li>
  <li>x: number of authority names in one record</li>
</ul>
<script src="js/histogram.js" type="text/javascript"></script>
<script>
var db = '{$db}';
var count = {$count};
{literal}
var units = 'authorities';
var histogramDataUrl = '?tab=histogram&file=authorities-histogram';
var histogramSvgClass = 'histogram-chart';

var tooltip = d3.select("body")
    .append("div")
    .style("opacity", 0)
    .attr("class", "tooltip")
    .attr("id", "tooltip")
displayHistogram(histogramDataUrl, histogramSvgClass);
{/literal}
</script>