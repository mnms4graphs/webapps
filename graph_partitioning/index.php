<!DOCTYPE html>
<html>

<head>
<title></title>

<meta charset="UTF-8" />

<script type="text/javascript" charset="utf-8" src="d3.min.js"></script>
<script type="text/javascript" charset="utf-8" src="jquery.min.js"></script>

<style type="text/css">

body { padding: 0; margin: 0; background: #000; }

circle.node {
  cursor: pointer;
  stroke: #000;
  stroke-width: .5px;
}

line.link {
  fill: none;
  stroke: #9ecae1;
  stroke-width: 1.5px;
}

#control {
    width: 300px;
    margin: 15px auto;
}

#files {
    display: block;
    margin: 15 auto;
    padding: 5px;
    width: 100%;
}

#graph {
    position: absolute;
    background: #333;
    top: 50px;
    bottom: 0;
    left: 0;
    right: 0;
}

</style>

<script type="text/javascript">

$(document).ready(function(){

    var w = $('#graph').width(),
        h = $('#graph').height(),
        node,
        link,
        root;
    
    var force = d3.layout.force()
        .on("tick", tick)
        .charge(function(d) { return (d.children) ? -Math.sqrt(d.size) : -d.size; })
        .linkDistance(function(d) { return (d.target.children) ? 80 : Math.sqrt(d.target.size); })
        .size([w, h]);
    
    var vis = d3.select("#graph").append("svg:svg")
        .attr("width", w)
        .attr("height", h);
        
    
    $('#files').change(function(){
        var f = $(this).find('option:selected').attr('value');
        if (!f) return;
        d3.json(f+'?id='+Math.random(100), function(json){
          root = json;
          root.fixed = true;
          root.x = w / 2;
          root.y = h / 2 - 80;
          update();
        });
    });
    
    function update() {
      var nodes = flatten(root)
          links = d3.layout.tree().links(nodes);
    
      // Restart the force layout.
      force
          .nodes(nodes)
          .links(links)
          .start();
    
      // Update the links…
      link = vis.selectAll("line.link")
          .data(links, function(d) { return d.target.id; });
    
      // Enter any new links.
      link.enter().insert("svg:line", ".node")
          .attr("class", "link")
          .attr("x1", function(d) { return d.source.x; })
          .attr("y1", function(d) { return d.source.y; })
          .attr("x2", function(d) { return d.target.x; })
          .attr("y2", function(d) { return d.target.y; });
    
      // Exit any old links.
      link.exit().remove();
    
      // Update the nodes…
      node = vis.selectAll("circle.node")
          .data(nodes, function(d) { return d.id; })
          .style("fill", color);
      
      node.transition()
          .attr("r", function(d) { return d.children ? 5 : Math.sqrt(d.size); });
    
      // Enter any new nodes.
      node.enter().append("svg:circle")
          .attr("class", "node")
          .attr("cx", function(d) { return d.x; })
          .attr("cy", function(d) { return d.y; })
          .attr("r", function(d) { return d.children ? 5 : Math.sqrt(d.size); })
          .style("fill", color)
          .on("click", click)
          .call(force.drag);
    
      // Exit any old nodes.
      node.exit().remove();
    }
    
    function tick() {
      link.attr("x1", function(d) { return d.source.x; })
          .attr("y1", function(d) { return d.source.y; })
          .attr("x2", function(d) { return d.target.x; })
          .attr("y2", function(d) { return d.target.y; });
    
      node.attr("cx", function(d) { return d.x; })
          .attr("cy", function(d) { return d.y; });
    }
    
    // Color leaf nodes orange, and packages white or blue.
    function color(d) {
      return d._children ? "#3182bd" : d.children ? "#c6dbef" : "#fd8d3c";
    }
    
    // Toggle children on click.
    function click(d) {
      if (d.children) {
        d._children = d.children;
        d.children = null;
      } else {
        d.children = d._children;
        d._children = null;
      }
      update();
    }
    
    // Returns a list of all nodes under the root.
    function flatten(root) {
      var nodes = [], i = 0, min, max;
    
      function recurse(node) {
        if (node.children) node.size = node.children.reduce(function(p, v) { return p + recurse(v); }, 0);
        if (!node.id) node.id = ++i;
        nodes.push(node);
        return node.size;
      }
    
      root.size = recurse(root);
      return nodes;
    }  

});

</script>

</head>
<body>

<div id="control">
<select id="files">
    <option value="">Select a file ...</option>
    <?php
    $list = glob('data/*.json');
    sort($list);
    ?>
    <?php foreach ($list as $f) : ?>
        <option value="<?=$f?>"><?=strtoupper(str_replace('_',' ',str_replace('.json','',basename($f))))?></option>
    <?php endforeach ?>
</select>
</div>
<div id="graph"></div>

</body>
</html>
