/**
* Similar to the Date (dd/mm/YY) data sorting plug-in, this plug-in offers
* additional flexibility with support for spaces between the values and
* either . or / notation for the separators.
*
* @name Date (dd . mm[ . YYYY])
* @summary Sort dates in the format `dd/mm/YY[YY]` (with optional spaces)
* @author [Robert Sedovšek](http://galjot.si/)
*
* @example
* $('#example').dataTable( {
* columnDefs: [
* { type: 'date-eu', targets: 0 }
* ]
* } );
*/
jQuery.extend( jQuery.fn.dataTableExt.oSort, {
	"date-eu-pre": function ( date ) {
	date = date.replace(" ", "");
	var eu_date, year;
	if (date == '') {
	return 0;
	}
	if (date.indexOf('.') > 0) {
	/*date a, format dd.mn.(yyyy) ; (year is optional)*/
	eu_date = date.split('.');
	} else {
	/*date a, format dd/mn/(yyyy) ; (year is optional)*/
	eu_date = date.split('/');
	}
	/*year (optional)*/
	if (eu_date[2]) {
	year = eu_date[2];
	} else {
	year = 0;
	}
	/*month*/
	var month = eu_date[1];
	if (month.length == 1) {
	month = 0+month;
	}
	/*day*/
	var day = eu_date[0];
	if (day.length == 1) {
	day = 0+day;
	}
	return (year + month + day) * 1;
	},
	"date-eu-asc": function ( a, b ) {
	return ((a < b) ? -1 : ((a > b) ? 1 : 0));
	},
	"date-eu-desc": function ( a, b ) {
	return ((a < b) ? 1 : ((a > b) ? -1 : 0));
	}
} );

jQuery.fn.dataTableExt.oApi.fnAddTr = function ( oSettings, nTr, bRedraw ) {
    if ( typeof bRedraw == 'undefined' )
    {
        bRedraw = true;
    }
 
    var nTds = nTr.getElementsByTagName('td');
    if ( nTds.length != oSettings.aoColumns.length )
    {
        alert( 'Warning: not adding new TR - columns and TD elements must match' );
        return;
    }
 
    var aData = [];
    var aInvisible = [];
    var i;
    for ( i=0 ; i<nTds.length ; i++ )
    {
        aData.push( nTds[i].innerHTML );
        if (!oSettings.aoColumns[i].bVisible)
        {
            aInvisible.push( i );
        }
    }
 
    /* Add the data and then replace DataTable's generated TR with ours */
    var iIndex = this.oApi._fnAddData( oSettings, aData );
    nTr._DT_RowIndex = iIndex;
    oSettings.aoData[ iIndex ].nTr = nTr;
 
    oSettings.aiDisplay = oSettings.aiDisplayMaster.slice();
 
    // Hidding invisible columns
    for ( i = (aInvisible.length - 1) ; i >= 0 ; i-- )
    {
        oSettings.aoData[iIndex]._anHidden[ i ] = nTds[aInvisible[i]];
        nTr.removeChild( nTds[aInvisible[i]] );
    }
 
    // Redraw
    if ( bRedraw )
    {
        this.oApi._fnReDraw( oSettings );
    }
};