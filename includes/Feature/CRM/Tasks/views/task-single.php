<div class="erp-modal midium erp-crm-task-single" id="erp-crm-new-contact">
    <div class="close" id="close-task-modal"><span class="dashicons dashicons-no-alt"></span></div>

    <div class="content-container modal-footer">
        <div class="content">
            <div class="erp-grid-container">
                <div class="row" id="task-info">
                    <div class="col-3 alignleft">
                        <button class="button" id="status-btn"></button>
                    </div>

                    <div class="col-3 text-right">
                        <span class="header-lite"><?php esc_attr_e( 'Created by ', 'erp-pro' ) ?></span><span id="task-created-by" class="header-val"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6" id="task-title"></div>
                </div>

                <div class="row">
                    <div class="col-3 alignleft">
                        <div class="header"><?php esc_attr_e( 'Create Date', 'erp-pro' ); ?></div>
                        <div class="header-val"><span class="dashicons dashicons-calendar"></span>  <span id="task-created-at"></span></div>
                    </div>

                    <div class="col-3 alignright">
                        <div class="header"><?php esc_attr_e( 'Due Date', 'erp-pro' ); ?></div>
                        <div class="header-val"><span class="dashicons dashicons-calendar"></span>  <span id="task-due-at"></span></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="header"><?php esc_attr_e( 'Description', 'erp-pro' ); ?></div>
                        <div class="header-val" id="task-des"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><!-- .single task modal -->

<div class="erp-modal-backdrop erp-crm-new-contact"></div><!-- .backdrop modal -->
