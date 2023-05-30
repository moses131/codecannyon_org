$(function() {
    "use strict";
    
    $.fn.cms_resultsCreator = function( data ) {
        let t       = $(this);
        let options = t.find( '.options' );
        let height  = data.height;

        function init_charts( data ) {
            $.each( data.placeholders, function( k, v ) {
                if( typeof v == 'object' ) {
                    init_charts( { data, ...{ placeholders: v, data: data.data[k] } } );
                } else {
                    let el  = t.find(v);
                    let q   = el.closest( '.q' );
                    q.removeClass( 'l' );
                    populate_charts( q, { data, ...{ table: v, data: data.data[k] } } );
                }
            });
        }

        function populate_charts( q, data ) {
            google.charts.load( 'current', { 'packages': [ 'corechart' ] } );
            let table       = q.find( data.table );
            let options     = data.options != undefined ? decodeUnicode( data.options ) : [];
            let defStyle    = data.default != undefined ? data.default : '3d';
        
            google.charts.setOnLoadCallback( function() { 
                let chart   = new google.visualization.PieChart( table[0] );
                let opts    = {
                    chartArea: { left: 0, top: 10, bottom: 10, width: '100%', height: '100%' },
                    height: height,
                    colors: [ '#00A170', '#FFB347', '#D31027', '#55B4B0', '#C3447A', '#B55A30', '#926AA6', '#D2386C', '#363945', '#E0B589', '#9A8B4F' ],
                    legend: { position: 'none' },
                    backgroundColor: 'transparent',
                    sliceVisibilityThreshold: 0
                };

                let cd;
        
                function googleSetStyle( style, first ) {
                    if( !first && defStyle == style ) return ;
                    switch( style ) {
                        case 'pie':
                            opts.is3D       = false;
                            opts.pieHole    = 0;
                            chart.draw( cd, opts );
                        break;
        
                        case 'donut':
                            opts.is3D       = false;
                            opts.pieHole    = 0.4;
                            chart.draw( cd, opts );
                        break;
        
                        default:
                            opts.is3D       = true;
                            chart.draw( cd, opts );
                        break;
                    }
        
                    defStyle = style;
                }
        
                let GoogleChartsSetStyle = ( filters ) => {
                    try {
                        cd  = google.visualization.arrayToDataTable( data.data );
                        googleSetStyle( defStyle, true );
        
                        t.find( '.options select[name="chartTypes"]' ).on( 'change', function(e) {
                            googleSetStyle( $(this).val(), false );
                        });
                    }
                    catch( e ) { }
                }
        
                GoogleChartsSetStyle( options );
            } );
        }

        init_charts( data );

        let iGroup;

        // Event: click
        t.find( '.ced > span' ).on( 'click', function( e ) {
            e.preventDefault();
            iGroup  = undefined;
            let ts  = $(this);
            let p   = ts.closest( '.ced' );
            let d   = ts.prop( 'defaultText' );

            if( !d ) {
                d = ts.html();
                ts.prop( 'defaultText', d );
            }

            let group = ts.data( 'group' );

            if( group )
            iGroup = t.find( '[data-group="' + group + '"]' ).not( ts );

            if( p.hasClass( 'isPH' ) ) {
                if( ts.html() == d )
                ts.html( ' ' );

                p.prop( 'hideNull', true );
                p.removeClass( 'isPH' );
            }

            ts.attr( 'contenteditable', true );
            ts.trigger( 'focus' );

        // Event: input
        }).on( 'input', function() {
            if( iGroup )
            iGroup.text( $(this).text() )

        // Event: blur
        }).on( 'blur', function() {
            let ts      = $(this);
            let p       = ts.closest( '.ced' );
            let text    = $.trim( ts.text() );

            if( text == '' ) {
                if( p.prop( 'hideNull' ) )
                p.addClass( 'isPH' );
                ts.html( ts.prop( 'defaultText' ) );
                if( iGroup )
                iGroup.text( $(this).text() )
            }
            
            ts.attr( 'contenteditable', false );
        });

        // Change position
        options.find( 'select[name="changePos"]' ).on( 'change', function() {
            let ts      = $(this);
            let report  = ts.data( 'r' );
            let rClass  = '.report-' + report;
            t.find( rClass ).css( 'order', ts.val() );
        });

        // Change color
        options.find( 'select[name="changeCol"]' ).on( 'change', function() {
            let ts      = $(this);
            let report  = ts.data( 'r' );
            let rClass  = '.report-' + report;
            t.find( rClass ).attr( 'data-color', ts.val() );
        });
    };

});

function init_survey_chart2( data ) {
    $( document ).ready( function() {
        $( data.container ).cms_resultsCreator( data );
    });
}