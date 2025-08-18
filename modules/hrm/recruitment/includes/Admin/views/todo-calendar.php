<div class="wrap erp-calendar-detail">
    <h1>
        <?php _e('Calendar','erp-pro');?>
        <a id="add-todo" class="page-title-action" href="#"><?php _e('Add To-Do','erp-pro');?></a>
        <span class="spinner"></span>
    </h1>
    <div id="dashboard-widgets-wrap" class="erp-grid-container">
        <div class="row">
            <div class="col-6">
                <div class="postbox">
                    <div class="inside" style="overflow-y:hidden;padding-left:0;margin-top:0;padding-bottom:0;margin-bottom:0;">
                        <div id="left-fixed-menu">
                            <ul>
                                <li><span id="section-overview"><?php _e('Overview', 'erp-pro');?></span></li>
                                <li><span id="section-overdue"><?php _e('Overdue', 'erp-pro');?></span></li>
                                <li><span id="section-today"><?php _e('Today', 'erp-pro');?></span></li>
                                <li><span id="section-later"><?php _e('Later', 'erp-pro');?></span></li>
                                <li><span id="section-no-due-date"><?php _e('No Due Date', 'erp-pro');?></span></li>
                                <li><span id="section-this-month"><?php _e('This Month', 'erp-pro');?></span></li>
                            </ul>
                        </div>

                        <div class="single-information-container">
                            <div id="todo-calendar-overview" class="show-cal-item"></div>
                            <div id="todo-calendar-overdue"></div>
                            <div id="todo-calendar-today"></div>
                            <div id="todo-calendar-later"></div>
                            <div id="todo-calendar-no-date" style="display: none">
                                <h3><?php _e('No Date', 'erp-pro');?></h3>
                                <ul></ul>
                            </div>
                            <div id="todo-calendar-this-month"></div>
                        </div>
                    </div><!-- inside -->
                </div><!-- postbox -->
            </div><!-- col-6 -->
        </div><!-- row -->
    </div><!-- erp-grid-container -->
</div>
