/*
    Dr Warehouse is a document oriented data warehouse for clinicians. 
    Copyright (C) 2017  Antoine Neuraz

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

    Contact : Nicolas Garcelon - nicolas.garcelon@institutimagine.org
    Institut Imagine
    24 boulevard du Montparnasse
    75015 Paris
    France
*/
function initVis(filename,data_json, nbMin, particles, firstView) {
    var w = 1400;
    var h = 1000;
    var flexibility = 100;

    var     nodeWidth = 30,
        nodePadding = 10,

        frequencyRange = [5,100],
        sizeRange = [5,5]



    nbMin = parseInt(nbMin);
    var particlesOn = particles;
    var firstView = firstView;


    var margin = {top: Math.round(w*8/100), right: Math.round(w*10/100), bottom: Math.round(w*15/100), left: Math.round(w*10/100)};
    var width = w - margin.left - margin.right;
    var height = h - margin.top - margin.bottom;
    var color = d3.scale.category20();

    var chartPosition = document.getElementById('chart').getBoundingClientRect()

    var canvas= d3.select("canvas")
        .attr({
            height:h,
            width: w
        })
        .style({
            top:Math.round(w*8/100 + chartPosition.top)+'px',
            left: Math.round(w*10/100 + chartPosition.left)+'px'
        })

// SVG (group) to draw in.
    var svg = d3.select("#chartSvg")
        .attr({
            width: width + margin.left + margin.right,
            height: height + margin.top + margin.bottom
        })
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    var linksGroup = svg.append("g").attr('class', 'links');
    var nodesGroup = svg.append("g").attr('class', 'nodes');

// Set up Sankey object.
    sankey = d3.sankey()
        .nodeWidth(nodeWidth)
        .nodePadding(nodePadding)
        .size([width, (height-flexibility)]);

// Get path data generator
    var path = sankey.link();


    var freqCounter = frequencyRange[0];

	if (filename!='') {
		d3.json(filename, sankeyDraw);
	}
	if (data_json!='') {
		data=JSON.parse(data_json);
		sankeyDraw(data);
	}

// Callback to draw on a data set.
    function sankeyDraw(_data) {

        var data = wrangleData(_data, rangeValue);

        var minValue = d3.min(data.links,function(d) {return d.value});
        var maxValue = d3.max(data.links,function(d) {return d.value});
        var meanValue = Math.round(d3.mean(data.links,function(d) {return d.value}))
        var totalValue = d3.sum(data.links,function(d) {return d.value});


        if (firstView) {nbMin = meanValue}

        d3.select('#minValue').text(minValue)
        d3.select('#maxValue').text(maxValue)
        d3.select('#selectedValue').text(' = ' + nbMin )
        d3.select('#nbMin').attr({
            'value':nbMin,
            'min': minValue,
            'max': maxValue -1
        })

        data = filterData(data, nbMin)

        sankey.nodes(data.nodes)
            .links(data.links)
            .sinksRight('sinksRight' in data ? data.sinksRight : true)
            .layout(32);

        // Draw the links.
        var links = linksGroup.selectAll('.link').data(data.links);

        // Enter
        links.enter()
            .append("path")
            .attr('class', 'link')
            .attr("id", function(d,i){
                d.id = i;
                return "link-"+i;
            });
        // Enter + Update
        links.attr('d', path)
            .style("stroke-width", function (d) {
                return Math.max(1, d.dy);

            })
        if (!particlesOn) {
            links.style("stroke", function (d) {
                return color(d.source.name.replace(/ +/g, "_"));
            });
        }

        links.classed('backwards', function (d) { return d.target.x <= d.source.x; });

        links.append("title")
            .text(function (d) {
                return d.source.name + " vers " + d.target.name + " = " + d.value + " ( " + Math.round((100* d.value/ d.source.value)) +'% )';
            });
        // Exit
        links.exit().remove();

        // Draw the nodes.
        var nodes = nodesGroup.selectAll('.node').data(data.nodes);
        // Enter
        var nodesEnterSelection = nodes.enter()
            .append("g")
            .attr('class', 'node')
            .on("mouseover",highlight_node_links)
            .on("mouseout",highlight_none)
            .call(d3.behavior.drag()
                .origin(function(d) { return d; })
                .on("dragstart", function() { this.parentNode.appendChild(this); })
                .on("drag", dragmove));
        nodesEnterSelection.append("rect")
            .attr('width', sankey.nodeWidth())
            .append("title")
        nodesEnterSelection.append("text")
            .attr('x', sankey.nodeWidth() / 2)
            .attr('dy', ".35em")
            .attr("text-anchor", "middle")
            .attr('transform', null)
            .attr('pointer-events','none');

        // Enter + Update
        nodes
            .attr('transform', function (d) {
                return "translate(" + d.x + "," + d.y + ")";
            });
        nodes.select('rect')
            .attr('height', function (d) {
                return d.dy;
            })
            .style('fill', function (d) {
                return d.color = color(d.name.replace(/ +/g, "_"));
            })
            .style('stroke', function (d) {
                return d3.rgb(d.color).darker(2);
            });
        nodes.select('rect').select('title')
            .text(function (d) {
                if (typeof d.dms != 'undefined') {
                    if (d.dms > 1) {
                        return 'DMS = '+ d.dms + ' jours';
                    } else {
                        return 'DMS = '+ d.dms + ' jour';
                    }
                } else {
                    return 'DMS = NA'
                }

            });
        nodes.select('text')
            .attr('y', function (d) {
                return d.dy / 2;
            })
            .text(function (d) {
                    return d.name;
            });

        // Exit
        nodes.exit().remove();

        function dragmove(d) {
            d3.select(this).attr("transform", "translate(" + (d.x = Math.max(0, Math.min(width - d.dx, d3.event.x))) + "," + (d.y = Math.max(0, Math.min((height+flexibility) - d.dy, d3.event.y))) + ")");
            sankey.relayout();
            links.attr("d", path);
        }


        if (particlesOn) {

            var linkExtent = d3.extent(data.links, function (d) {return d.value});
            var frequencyScale = d3.scale.linear().domain(linkExtent).range(frequencyRange);
            var particleSize = d3.scale.linear().domain(linkExtent).range(sizeRange);


            data.links.forEach(function (link) {
                link.freq = frequencyScale(link.value);
                link.particleSize = particleSize(link.value);
                link.particleColor = d3.scale.linear().domain([1,1000]).range([link.source.color, link.target.color]);
            })

            var particles = [];

            function tick(elapsed, time) {

                particles = particles.filter(function (d) {return d.time > (elapsed - 1000)});

                if (freqCounter > frequencyRange[1]) {
                    freqCounter = frequencyRange[0];
                }

                d3.selectAll("path.link")
                    .each(
                        function (d) {
                            if (d.freq >= freqCounter) {
                                var offset = (Math.random() -.5) * (d.dy);
                                particles.push({link: d, time: elapsed, offset: offset, path: this})
                            }
                        });

                particleEdgeCanvasPath(elapsed);
                freqCounter++;

            }

            function particleEdgeCanvasPath(elapsed) {
                var context = d3.select("canvas").node().getContext("2d")

                context.clearRect(0,0,w,h);

                context.fillStyle = "gray";
                context.lineWidth = "1px";
                for (var x in particles) {
                    var currentTime = elapsed - particles[x].time;
                    var currentPercent = currentTime / 1000 * particles[x].path.getTotalLength();
                    var currentPos = particles[x].path.getPointAtLength(currentPercent)
                    context.beginPath();
                    context.fillStyle = particles[x].link.particleColor(currentTime);
                    context.arc(currentPos.x,currentPos.y + particles[x].offset,particles[x].link.particleSize,0,2*Math.PI);
                    context.fill();
                }
            }

            var t = d3.timer(tick, 1000);

        }



        function highlight_node_links(node,i){

            d3.selectAll(".link").style("stroke-opacity", 0);

            var remainingNodes=[],
                nextNodes=[],
                traversedNodes = {};

            var nbNodes = nodes.length;

            var stroke_opacity = 0;
            if( d3.select(this).attr("data-clicked") == "1" ){
                d3.select(this).attr("data-clicked","0");
                stroke_opacity = 0.2;
            }else{
                d3.select(this).attr("data-clicked","1");
                stroke_opacity = 0.5;
            }

            var traverse = [{
                linkType : "sourceLinks",
                nodeType : "target"
            },{
                linkType : "targetLinks",
                nodeType : "source"
            }];

            traversedNodes[node.name] = 1;

            traverse.forEach(function(step){
                node[step.linkType].forEach(function(link) {
                    remainingNodes.push(link[step.nodeType]);

                    highlight_link(link.id, stroke_opacity);
                });

                while (remainingNodes.length & nbNodes > 0) {

                    nextNodes = [];
                    remainingNodes.forEach(function(node) {
                        traversedNodes[node.name] = 1;
                        if (d3.keys(traversedNodes).indexOf(node.name) < 0 ) {
                            node[step.linkType].forEach(function(link) {
                                nextNodes.push(link[step.nodeType]);
                                highlight_link(link.id, stroke_opacity);
                            });
                        }

                    });
                    remainingNodes = nextNodes;
                    nbNodes --;
                }
            });
            //d3.selectAll(".link").style("stroke-opacity", 0.2)

        }



        function highlight_link(id,opacity){
            d3.select("#link-"+id).style("stroke-opacity", opacity);
        }

        function highlight_none(){
            //
            //
            d3.selectAll(".link")
                .style("stroke-opacity", 0.2)
            d3.selectAll(".node").attr("data-clicked" ,0)
        }



    }

}

particlesSwitch = function() {
    particles = this.checked;
    rangeValue = d3.select('#nbMin').node().value;

    updateVis(filename,data_json, rangeValue, particles, false);
}

valueChange = function() {
    rangeValue = this.value;
    updateVis(filename,data_json, rangeValue, particles, firstView);
}

wrangleData = function(data) {


    data.nodes.map(function(d, i) {
        d.id = i;

        if (typeof d.dms != 'undefined') {
            d.dms = parseFloat(d.dms.replace(',','.'))

        }

    })

    data.links.map(function(d, i) {
        d.source_id = d.source
        d.target_id = d.target

        d.source = data.nodes.filter(function(node) {
            return node.id == d.source
        })[0]
        d.target = data.nodes.filter(function(node) {
            return node.id == d.target
        })[0]
    })

    return data;
}

filterData = function(data, nbMin){

    var nodesList = {};
    // filter autolinks
    data.links = data.links.filter(function(d) {
        res = d.source_id != d.target_id & d.value > nbMin  ;
        if (res) nodesList[d.source_id] = 1
        if (res) nodesList[d.target_id] = 1
        return res;
    })

    var nodesKeyList = Object.keys(nodesList)
    var nodesIdList = []

    nodesKeyList.forEach(function(d) {
        nodesIdList.push(parseInt(d))
    })

    data.nodes = data.nodes.filter(function(d) {
        return nodesIdList.indexOf(d.id) >=0;
    })

    return data
}

updateVis = function(filename,data_json, rangeValue, particles, firstView) {
    d3.select('svg').selectAll('*').remove();
    initVis(filename,data_json, rangeValue, particles, firstView);
};