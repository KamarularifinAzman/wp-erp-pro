;( function($) {

    'use strict';

    var erpProOrgc = {
        zoom: 0,

        init: function() {
            
            var self    = this,
                scale   = 1;

            self.renderOrgChart();
            self.initActions();
            self.select2Action( 'erp-hrm-select2' );

            $( '#erp-orgc-filter' ).on( 'change', function(e) {
                var deptId = $(this).val();
                self.renderOrgChart( deptId );
            });

            $( document ).on( 'click', '#erp-orgc-zoom-out', function(e) {
                if (scale > 0.95) {
                    scale -= 0.05;
                }
                
                erpProOrgc.zoom -= 5;
                
                self.scaleChart(scale);
                $( '#erp-orgc-zoom' ).html( erpProOrgc.zoom + '%' );
                $( '#erp-orgc-zoom-in' ).removeAttr( 'disabled' );

                if ( erpProOrgc.zoom == 0 ) {
                    $( '#erp-orgc-zoom' ).html('');
                }

                if ( erpProOrgc.zoom <= -100 ) {
                    $(this).attr( 'disabled', 'disabled' );
                } else {
                    $(this).removeAttr( 'disabled' );

                    if ( erpProOrgc.zoom == 100 ) {
                        $( '#erp-orgc-zoom-in' ).attr( 'disabled', 'disabled' );
                    }
                }
            });

            $( document ).on( 'click', '#erp-orgc-zoom-in', function(e) {
                if (scale < 1.05) {
                    scale += 0.05;
                }
                
                erpProOrgc.zoom += 5;
                
                self.scaleChart(scale);
                $( '#erp-orgc-zoom' ).html( erpProOrgc.zoom + '%' );
                $( '#erp-orgc-zoom-out' ).removeAttr( 'disabled' );

                if ( erpProOrgc.zoom == 0 ) {
                    $( '#erp-orgc-zoom' ).html('');
                }

                if ( erpProOrgc.zoom >= 100 ) {
                    $(this).attr( 'disabled', 'disabled' );
                } else {
                    $(this).removeAttr('disabled');

                    if ( erpProOrgc.zoom == -100 ) {
                        $( '#erp-orgc-zoom-out' ).attr('disabled', 'disabled');
                    }
                }
            });
        },

        initActions: function() {
            $( document ).on( 'click', '.erp-orgc-node-actions', function(e) {
                e.preventDefault();
                var id = $(this).data('id');

                $( `#erp-orgc-node-actions-${id}` ).toggleClass( 'show' );
                $( `#erp-orgc-popper-arrow-${id}` ).toggleClass( 'show' );
            });

            $( document ).on( 'click', function(e) {
                Array.prototype.forEach.call( $( '.erp-orgc-node-actions' ), function(row, index) {
                    if ( typeof row !== 'undefined' && ! row.contains( e.target ) ) {
                        $( '.dropdown-content' )[index].classList.remove('show');
                        $( '.popper-arrow' )[index].classList.remove('show');
                    }
                });
            });

            $( document ).on( 'click', '.erp-multi-org-chart .hierarchy.no-parent.root', function(e) {
                var deptId = $(this).data( 'dept' );
                $( '#erp-orgc-filter' ).val( deptId );
                $( '#erp-orgc-filter' ).trigger( 'change' );
            });

            $( document ).click( function(e) {
                Array.prototype.forEach.call( $( '.orgchart .node' ), function(row, index) {
                    if ( typeof row !== 'undefined' && ! row.contains( e.target ) ) {
                        row.classList.remove('focused');
                    }
                });
            });

            $( document ).on( 'click', '.dropdown-content', function() {
                var email = $( this ).find( 'a.erp-orgc-email' ).data( 'email' );
                window.location.href = 'mailto:' + email;
            });
        },

        select2Action: function(element) {
            $('.'+element).select2({
                width: 'element',
            });
        },

        scaleChart: function(scale, fixTop) {
            var $chart = $( '#erp-hr-org-chart .orgchart' ),
                lastTf = $chart.css( 'transform' );
            
            if (lastTf === 'none') {
                if (fixTop) {
                    $chart.css( 'transform', 'matrix(' + scale + ', 0, 0, ' + scale + ', 0,' + (1 - scale) );
                } else {
                    $chart.css( 'transform', 'scale(' + scale + ',' + scale + ')' );
                }
            } else {
                if (lastTf.indexOf('3d') === -1) {
                    $chart.css( 'transform', lastTf + ' scale(' + scale + ',' + scale + ')' );
                } else {
                    $chart.css( 'transform', lastTf + ' scale3d(' + scale + ',' + scale + ', 1)' );
                }
            }
        },

        renderOrgChart: function(deptId = '') {
            $( '.erp-ajax-loader-bg' ).show();
            $( '.erp-ajax-loader' ).show();

            wp.ajax.send({
                data: {
                    action: 'erp_hr_get_orgchart',
                    dept_id: deptId,
                    _wpnonce: erpOrgChart.nonce
                },
                success: function(response) {
                    $( '#erp-hr-org-chart' ).html('');
                    
                    response.id        = 'rootNode';
                    response.collapsed = true;
                    response.className = 'top-level';
                    erpProOrgc.zoom    = 0;

                    $( '#erp-orgc-zoom' ).html( erpProOrgc.zoom + '%' );

                    var data = {
                        data             : response,
                        depth            : 2,
                        pan              : true,
                        nodeTitle        : 'avatar',
                        nodeContent      : '',
                        exportFilename   : 'ERP_Org_Chart',
                        chartClass       : 'edit-state',
                        parentNodeSymbol : ''
                    }

                    var screen      = $(window).width(),
                        numChild    = data.children ? data.children.length : 0;

                    if ( ( screen > 1200 && numChild > 10 ) || ( screen <= 1200 && screen > 767 && numChild > 5 ) ) {
                        data.verticalLevel = 3;
                    } else if ( screen <= 767 ) {
                        data.direction = 'l2r';
                        $( '#erp-hr-org-chart' ).addClass( 'mobile-view' );
                    }
        
                    if (response.is_array) {
                        $( '#erp-hr-org-chart' ).addClass( 'erp-multi-org-chart' );

                        data.createNode  = function($node, data) {
                            $node.children( 'ul.nodes' ).attr( 'data-dept', data.dept_id );
                        }
                    } else {
                        $( '#erp-hr-org-chart' ).removeClass( 'erp-multi-org-chart' );

                        data.createNode  = function($node, data) {
                            var name  = data.name  ? data.name  : '',
                                title = data.title ? data.title : '',
                                email = data.email ? data.email : '';

                            if ( screen > 767 ) {
                                $node.children( '.title' ).append(
                                    `<div class="erp-row-action-dropdown erp-orgc-node-actions" data-id="id${data.id}l${data.lead}">
                                        <span class="dashicons dashicons-ellipsis dropdown-btn"></span>
    
                                        <div x-arrow class="popper-arrow arrow-left" id="erp-orgc-popper-arrow-id${data.id}l${data.lead}"></div>
    
                                        <div class="dropdown-content" id="erp-orgc-node-actions-id${data.id}l${data.lead}">
                                            <a target="_top" href="mailto:${email}" class="erp-orgc-email" data-email="${email}">
                                                <span class="dashicons dashicons-email"></span>
                                                 Mail
                                            </a>
                                        </div>
                                    </div>`
                                );
                            }

                            $node.children( '.content' ).append(
                                `<div class='name'>${name}</div>
                                <div class='designation'>${title}</div>`
                            );
                        }
                    }
        
                    $( '#erp-hr-org-chart' ).orgchart( data );
        
                    $( '#erp-hr-org-chart .orgchart .nodes .hierarchy' ).removeClass( 'isChildrenCollapsed' );
                    $( '#erp-hr-org-chart .orgchart .nodes .nodes' ).removeClass( 'hidden' );

                    $( '.erp-ajax-loader-bg' ).hide();
                    $( '.erp-ajax-loader' ).hide();

                    $( '#erp-hr-org-chart' ).find( '.hierarchy ul.nodes' ).each((index, element) => {
                        if ( $(element).find( '.node' ).hasClass( 'no-parent' ) ) {
                            $(element).addClass( 'no-parent' );
                            $(element).find( 'li.hierarchy' ).addClass( 'no-parent' );

                            if ( ! $(element).find( '.nodes' ).length ) {
                                $(element).parent().addClass( 'multi-children' );
                            } else if ( response.is_array ) {
                                $(element).removeClass( 'no-parent' );
                                $(element).addClass( 'has-child' );
                            }
                        }
                    });

                    if ( $( '#rootNode' ).parent().find( '.node' ).hasClass( 'no-parent' ) ) {
                        $( '#rootNode' ).addClass( 'no-content' );
                    }

                    $( '#rootNode' ).parent().find( '.node' ).each((index, element) => {
                        if ( $(element).data( 'parent' ) == 'rootNode' ) {
                            $(element).addClass( 'root' );
                            $(element).parent().addClass( 'root' );
                        }
                    });

                    if ( response.is_array ) {
                        $( document ).find( '.hierarchy.no-parent.root' ).each((index, element) => {
                            var dept = response.children[index].dept_id != 0 ? response.children[index].dept_id : -1;
                            $( element ).attr( 'data-dept', dept );
                        });
                    }

                    /**
                     * Disabling it because it is unable to do its job while the datasize is big.
                     * It needs to be optimized so that it can perfectly fit the chart with the screen.
                     */
                    // if (
                    //     $( '#erp-hr-org-chart' ).height() < $( '#erp-hr-org-chart .orgchart' ).height() ||
                    //     $( '#erp-hr-org-chart' ).width() < $( '#erp-hr-org-chart .orgchart' ).width()
                    // ) {
                    //     var heightRatio = $( '#erp-hr-org-chart' ).height() / $( '#erp-hr-org-chart .orgchart' ).height();
                    //     var widthRatio  = $( '#erp-hr-org-chart' ).width() / $( '#erp-hr-org-chart .orgchart' ).width();
    
                    //     if (heightRatio < 1 && widthRatio < 1) {
                    //         erpProOrgc.scaleChart( Math.max( heightRatio, widthRatio ) + 0.05 );
                    //     } else {
                    //         erpProOrgc.scaleChart( Math.min( heightRatio, widthRatio ) - 0.05 );
                    //     }
                    // }
                },
                error: function(error) {
                    swal( '', error, 'error' );
                    $( '.erp-ajax-loader-bg' ).hide();
                    $( '.erp-ajax-loader' ).hide();
                }
            });
        }
    };

    $(function() {
        erpProOrgc.init();
    });

} ) ( jQuery );