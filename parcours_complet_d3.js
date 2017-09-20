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
/**
 * Created by antoine on 02/12/2015.
 */

initVis = function(filename) {
    var  h =1000,
        w = h,
        margin = {top: 20, bottom: 20, left:0, right: 0},
        r1 = h / 4 - margin.top - margin.bottom ,
        circleStroke = Math.round(h/100),
        r0 = r1 - circleStroke ;


    var fill = d3.scale.category20c();

    var chord = d3.layout.chord()
        .padding(.04)
        .sortSubgroups(d3.descending)
        .sortChords(d3.descending);

    var arc = d3.svg.arc()
        .innerRadius(r0)
        .outerRadius(r0 + circleStroke);

    svg = d3.select("#chart").append("svg:svg")
        .attr("width", w)
        .attr("height", h)
        .append("svg:g")
        .attr("transform", "translate(" + (w / 2 + margin.left) + "," + (h / 2 + margin.top)   + ")");


    d3.json(filename, ready)

    function ready(error,  _data) {
        if (error) throw error;

        var parcours = wrangleData(_data);
        var nbUnits = parcours.nodes.length;


        parcours.matrix = [];

        for(var i=0; i<nbUnits; i++) {
            parcours.matrix[i] = Array.apply(null, Array(nbUnits)).map(Number.prototype.valueOf,0);

        }

        parcours.links.forEach(function(d) {

                parcours.matrix[d.source_id][d.target_id] = d.value;

        })

        parcours.nodes.forEach(function(d) {
            optArray.push(d.name)
        })



        // Compute the chord layout.
        chord.matrix(parcours.matrix);

        var g = svg.selectAll("g.group")
            .data(chord.groups)
            .enter().append("svg:g")
            .attr("class", "group")
            .on("mouseover", fade(.02))
            .on("mouseout", fade(.80));



        g.append("svg:path")
            .style("stroke", function(d) { return fill(d.index); })
            .style("fill", function(d) { return fill(d.index); })
            .attr("d", arc)


        var textDy = (h / 3430)+'em',
            fontSize =  Math.round(h/100)+'px',
            textHorizontalTranslate = h / 55;


        g.append("svg:text")
            .attr('class','label')
            .each(function(d) { d.angle = (d.startAngle + d.endAngle) / 2; })
            .attr("dy", textDy)
            .attr("text-anchor", function(d) { return d.angle > Math.PI ? "end" : null; })
            .attr('font-size', fontSize)
            .style("stroke-opacity",.80)
            .attr("transform", function(d) {
                return "rotate(" + (d.angle * 180 / Math.PI - 90) + ")"
                    + "translate(" + (r0 + textHorizontalTranslate) + ")"
                    + (d.angle > Math.PI ? "rotate(180)" : "");
            })
            .text(function(d, i) {
                return (parcours.nodes[i]).name;
            })


        //console.log(g.selectAll('text'))

       // g.selectAll('text').forEach(function(d) {
        //    console.log(d[0])
       //     console.log(d[0].getComputedTextLength())
       //     console.log(getComputedStyle(d[0]))
//
       // })

        svg.selectAll("path.chord")
            .data(chord.chords)
            .enter().append("svg:path")
            .attr("class", "chord")
            .style("stroke", function(d) { return d3.rgb(fill(d.source.index)).darker(); })
            .style("fill", function(d) { return fill(d.source.index); })
            .attr("d", d3.svg.chord().radius(r0))
            .on("mouseover", fade(.02))
            .on("mouseout", fade(.80));


    }

    $(function () {
        $("#search").autocomplete({
            source: optArray
        });
    });

}


// Returns an event handler for fading a given chord group.
fade = function(opacity) {
    return function(c,i) {
        var selectedIndex

        if (typeof c.source != 'undefined') {
            selectedIndex = c.source.index
        } else {
            selectedIndex = c.index
        }

        _fade(opacity,selectedIndex);

    };
}

_fade = function(opacity, selectedIndex){
    var displayList = {};

    svg.selectAll("path.chord")
        .filter(function(d, i) {
            res = d.source.index != selectedIndex & d.target.index != selectedIndex
            if (!res) {displayList[d.source.index]=1; displayList[d.target.index]=1;}
            return res; })
        .transition()

        .style("stroke-opacity", opacity)
        .style("fill-opacity", opacity);

    displayList = d3.keys(displayList)

    svg.selectAll(".label")
        .filter(function(d,i) {

            return  displayList.indexOf(d.index+'') < 0;
        })
        .transition()
        .style("stroke-opacity", opacity)
        .style("fill-opacity", opacity);
}



updateVis = function() {

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


valueChange = function() {

}

searchNode = function() {

    //find the node

    var selectedVal = document.getElementById('search').value;
    var selectedIndex =optArray.indexOf(selectedVal);
    var displayList = {};

    var node = d3.selectAll(".label");

    console.log(selectedVal)
    if (selectedVal == "") {
        _fade(0.8)
       node.style("opacity",.80);
    } else {
        var selected = node.filter(function (d, i) {
            return d.index != selectedIndex;
        });

        _fade(0.02, selectedIndex)

    }
}

