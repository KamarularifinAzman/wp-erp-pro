<?php

namespace WeDevs\HelpScout;

use WeDevs\Deals\Models\Activity;

class Deals {

    public function __construct() {
        add_action( 'erp_helpscout_before_widget_end', [ $this, 'print_deals_activity' ], 10, 1 );
    }

    /**
     * Output a list of recent deal with customer
     *
     * @since 1.0.0
     *
     * @param $contact_id
     */
    public function print_deals_activity( $contact_id ) {
        $deals = Activity::where( 'contact_id', $contact_id )->take( 5 )->get();
        ?>
        <div class="divider"></div>
        <div class="toggleGroup open">
            <h4><a href="#" class="toggleBtn"><i class="icon-case"></i>Deals</a></h4>
            <div class="toggle">
                <?php

                if ( count( $deals ) == 0 ) {
                    _e( 'No deal activity found', 'erp-pro' );
                } else {
                    echo '<ul>';
                    foreach ( $deals as $deal ) {
                        $date_time = date( 'jS M y, h:i A', strtotime( $deal->start ) );
                        echo "<li><span class='muted'>{$date_time}</span> - {$deal->title}</li>";
                    }
                    echo '</ul>';
                }
                ?>
            </div>
        </div>
        <?php
    }
}
