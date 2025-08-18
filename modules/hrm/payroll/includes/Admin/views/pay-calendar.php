<div id="pay-calendar-wrapper" class="wrap erp-payroll-pay-calendar not-loaded">
    <h1>
        <?php _e( 'Pay Calendar', 'erp-pro' );?>
        <?php $add_new_pay_cal_page_link = add_query_arg( ['subpage' => 'add-cal-form'], erp_payroll_get_admin_link('calendar') );?>
        <a href="<?php echo $add_new_pay_cal_page_link;?>" class="button button-primary">
            <?php _e( 'Add New Pay Calendar', 'erp-pro' );?>
        </a>
    </h1>

    <div id="dashboard-widgets-wrap" class="erp-grid-container payitem-container">
        <div class="col-6">
            <div class="calendar-loader error" v-if="loading_calendar">
              <p><?php _e( 'Loading Pay Calendar ! Please wait.', 'erp-pro' );?></p>
            </div>
            <div  v-else>
                <div class="error" v-if="calendarData.length < 1 ">
                <p><?php _e( 'No Pay Calendar Found!', 'erp-pro' );?></p>
                </div>
            </div>
            <div class="postbox metabox-holder" v-for="caldata in calendarData">
                <h2 class="hndle">{{ caldata.pay_calendar_name }}</h2>
                <div class="inside">
                    <ul class="pay-cal-list">
                        <li>
                            <label><strong><?php _e( 'Calendar Name :', 'erp-pro' );?></strong>&nbsp;{{ caldata.pay_calendar_name }}</label>
                            <label><strong><?php _e( 'Calendar Type :', 'erp-pro' );?></strong>&nbsp;{{ caldata.pay_calendar_type | ucFirst }}</label>
                            <label><strong><?php _e( 'Total Employees :', 'erp-pro' );?></strong>&nbsp;{{ caldata.cal_emp_number }}</label>
                        </li>
                    </ul>
                </div><!-- inside -->
                <div class="action-col">
                <?php $add_new_pay_cal_page_link = add_query_arg( ['subpage' => 'add-cal-form', 'cal_id' => '{{ caldata.id }}'], erp_payroll_get_admin_link( 'calendar' ) );?>
                    <a href="<?php echo $add_new_pay_cal_page_link;?>" class="button">
                        <i class="fa fa-pencil"></i>
                    </a>
                    <span class="button alignleft" @click="removeCal(caldata)">
                        <i class="fa fa-trash"></i>
                    </span>
                    <span class="button alignright" @click="runPayrun(caldata)">
                        <?php _e( 'Start Payrun', 'erp-pro' );?>
                    </span>
                </div>
            </div><!-- postbox -->
        </div>
    </div><!-- erp-grid-container -->

</div>
