<div class="erp-grid-container">
    <div class="row erp-request-header">
        <div class="column">
            <span class="status {{ data.status.id }}">{{ data.status.title }}</span>
        </div>

        <div class="column">
            <span class="header"><?php esc_attr_e( 'Requested by', 'erp-pro' ) ?></span>
            <span class="info">{{ data.employee.name }}</span>
        </div>
    </div>


    <# if ( data.start_date ) { #>
    <div class="row">
        <div class="col-6 column">
            <div class="header"><?php esc_attr_e( 'Start Date', 'erp-pro' ); ?></div>
            <div class="details date">
                <span class="line-icon dashicons dashicons-calendar"></span>
                <span class="info">  {{ data.start_date }}</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-6 column">
            <div class="header"><?php esc_attr_e( 'End Date', 'erp-pro' ); ?></div>
            <div class="details date-alt">
                <span class="line-icon dashicons dashicons-calendar"></span>
                <span class="info">  {{ data.end_date }}</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-6 column">
            <div class="header"><?php esc_attr_e( 'Duration', 'erp-pro' ); ?></div>
            <div class="details duration">
                <span class="line-icon dashicons dashicons-backup"></span>
                <span class="info">  {{ data.duration }}</span>
            </div>
        </div>
    </div>
    <# } #>

    <# if ( data.date ) { #>
    <div class="row">
        <div class="col-6 column">
            <div class="header"><?php esc_attr_e( 'Resign Date', 'erp-pro' ); ?></div>
            <div class="details date">
                <span class="line-icon dashicons dashicons-calendar"></span>
                <span class="info">  {{ data.date }}</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-6 column">
            <div class="header"><?php esc_attr_e( 'Request Date', 'erp-pro' ); ?></div>
            <div class="details date-alt">
                <span class="line-icon dashicons dashicons-calendar"></span>
                <span class="info">  {{ data.created }}</span>
            </div>
        </div>
    </div>
    <# } #>

    <div class="row">
        <div class="col-6 column">
            <div class="header"><?php _e( 'Reason', 'erp-pro' ); ?></div>
            <# if ( data.reason.id != 'other' ) { #>
            <div class="details reason">
                <span class="line-icon dashicons dashicons-twitch"></span>
                <span class="info">{{ data.reason.title }}</span></div>
            <# } else { #>
            <div class="details reason">
                <span class="line-icon dashicons dashicons-twitch"></span>
                <span class="info">{{ data.reason.others }}</span></div>
            </div>
            <# } #>
        </div>
    </div>

    <div class="row erp-request-footer">
        <div class="column">
            <# if ( data.showBtn ) { #> 
                <button class="button" id="erp-req-reject"><?php _e( 'Reject', 'erp-pro' ); ?></button>
                <button class="button" id="erp-req-approve"><?php _e( 'Approve', 'erp-pro' ); ?></button>
            <# } #>
        </div>
    </div>
</div>