<div class="wrap erp erp-crm-task">
    <h2><?php esc_html_e( 'Tasks', 'erp-pro' ); ?></h2>
    <?php do_action( 'erp_crm_task_menu', 'tasks' ); ?>

    <h2 class="nav-tab-wrapper erp-nav-tab-wrapper" id="crm-task-tab">
        <div class="nav-tab crm-tasks-tab nav-tab-active" id="own-tab"><?php esc_attr_e( 'My Tasks', 'erp-pro' ); ?></div>
        <?php if ( current_user_can( erp_crm_get_manager_role() ) || current_user_can( 'manage_options' ) ) : ?>
        <div class="nav-tab crm-tasks-tab" id="all-tab"><?php esc_attr_e( 'All Tasks', 'erp-pro' ); ?></div>
        <?php endif; ?>
    </h2><!-- .task nav tab -->

    <div class="tablenav top">
		<div class="alignleft actions">
            <select name="filter_status" id="filter-status"></select>

            <select name="filter_contact" id="filter-contact" data-types="contact,company"></select>

            <select name="filter_user" id="filter-user"></select>

            <i class="fa fa-calendar" aria-hidden="true"></i>
            <input type="text" name="filter_date" id="filter-date" placeholder="<?php esc_attr_e( 'Select Date Range', 'erp-pro' ); ?>" autocomplete="off">

            <i class="fa fa-search" aria-hidden="true"></i>
            <input type="search" name="search_task" id="search-task" placeholder="<?php esc_attr_e( 'Search Task', 'erp-pro' ); ?>">
        </div><!-- .action filters -->

        <div class="tablenav-pages">
            <span class="displaying-num"></span>
            <span class="pagination-links">
                <span class="tablenav-pages-navspan button" id="crm-task-prev" aria-hidden="true">‹</span>
                <span class="paging-input">
                    <label for="current-page-selector" class="screen-reader-text"><?php esc_attr_e( 'Current Page', 'erp-pro' ); ?></label>
                    <input class="current-page" id="current-page-selector" type="text" name="paged" size="1">
                    <span class="tablenav-paging-text"> of <span class="total-pages"></span></span>
                </span>
                <span class="tablenav-pages-navspan button" id="crm-task-next" aria-hidden="true">›</span>
            </span><!-- .pagination links -->
        </div><!-- .talenav-pages -->
	</div><!-- .talenav -->

    <div id="erp-task-table-wrap" >
        <div class="list-table-inner">
            <table class="wp-list-table widefat fixed striped tasks">
                <form method="get">
                    <thead></thead>
                    <tbody id="crm-task-list"></tbody>
                    <tfoot></tfoot>
                </form>
            </table>
        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->
</div><!-- .erp-crm-task wrap -->
