<?php
$current_m_d            = date( 'd F', strtotime( $current_date ) );
$after_7_days_date_m_d  = date( 'd F', strtotime( $after_7_days_date ) );
$company                = new \WeDevs\ERP\Company();
$print_type             = ( 'week' == $type ) ? esc_html__( 'week', 'erp-pro' ) : esc_html__( 'month', 'erp-pro' );
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Lato:wght@300&display=swap');
    body{
        margin: 0;
        padding: 0;
        background-color: #EDF7FF;
        font-family: Lato !important;
    }
    table{
        background-color: #EDF7FF;
        font-family: Lato !important;
    }
    .digest-email-wrap{
        background-color: #EDF7FF;
        font-family: Lato !important;
    }
    .digest-email-body{
        margin: 0 auto;
        font-family: Lato !important;
    }
    .digest-email-title{
        color: #0091FF;
        font-size: 28.2px;
        font-weight: 900;
        letter-spacing: 0;
        line-height: 34px;
        font-family: Lato !important;
    }
    h3.date_range{
        color: #6F84B1;
        font-size: 16px;
        letter-spacing: 0;
        line-height: 19px;
        font-family: Lato !important;
    }
    .introduction{
        color: #515282;
        font-size: 18px;
        letter-spacing: 0;
        line-height: 30px;
        font-family: Lato !important;
    }
    #credit{
        text-align: center;
        color: #A2A3BE;
        font-size: 16px;
        letter-spacing: 0;
        line-height: 24px;
        font-family: Lato !important;
    }
    .digest-email-section{
        padding: 20px;
        background-color: #FFFFFF;
        margin: 20px auto;
        border-radius: 5px;
        font-family: Lato !important;
    }
    .digest-email-section .table{
        margin-left: 40px;
        width: 90%;
        font-family: Lato !important;
    }
    .digest-email-section table tr td{
        padding-left: 10px;
        font-family: Lato !important;
    }
    .digest-email-section table tr td.image{
        width: 2%;
    }
    .digest-email-section table tr td.image img{
        height: 30px;
        width: 30px;
        border-radius: 30px;;
    }
    .digest-email-section table tr td.name{
        width: 40%;
        color: #7D7E98;
        font-size: 16px;
        letter-spacing: 0;
        line-height: 24px;
        font-family: Lato !important;
    }
    .digest-email-section table tr td.date{
        width: 50%;
        text-align: right;
        color: #7D7E98;
        font-size: 16px;
        letter-spacing: 0;
        line-height: 24px;
        font-family: Lato !important;
    }
    span.digest-email-section-title-content{
        height: 22px;
        color: #515282;
        font-size: 18.2px;
        font-weight: bold;
        letter-spacing: 0;
        line-height: 22px;
        margin-left: 20px;
        display: inline-block !important;
        position: relative !important;
        top: -10px !important;
        font-family: Lato !important;
    }
    .digest-email-section-title-icon{
        display: inline-block !important;
    }
    .digest-email-section table{
        background-color: white;
    }
    .logo img{
        width: 100px;
        margin-bottom: 30px;
    }
    .site_login{
        text-decoration: none;
        color: #FFFFFF;
        background-color: #0091FF;
        font-size: 16px;
        letter-spacing: 0;
        line-height: 19px;
        text-align: right;
        border-radius: 35px;
        padding: 12px 29px;
    }
</style>
<div class="digest-email-wrap">
    <div class="digest-email-body">
        <div class="line">
            <img src="<?php echo ERP_PRO_PLUGIN_ASSEST  ;?>//images/email-icons/line.svg" alt="">
        </div>
        <div class="logo">
            <?php echo $company->get_logo();?>
        </div>
        <h1 class="digest-email-title">
            <?php echo  esc_html__( 'What\'s happening this ', 'erp-pro' ) . $print_type ;?>
        </h1>
        <h3 class="date_range">
             <?php echo "{$current_m_d} to {$after_7_days_date_m_d}"  ?>
        </h3>
        <div class="introduction">
            <?php echo  __( ' Hi,<br> The following things are happening this ', 'erp-pro' ) . $print_type ;?>
        </div>

        <div class="digest-email-section">
            <div>
                <span class="digest-email-section-title-icon"><img src="<?php echo ERP_PRO_PLUGIN_ASSEST  ;?>//images/email-icons/new_team_members.svg" alt=""></span><span class="digest-email-section-title-content"><?php esc_html_e( 'New Team Members', 'erp-pro' ) ?></span>
            </div>
            <?php  echo $html_for_new_member_joining ;?>
        </div>

        <div class="digest-email-section">
            <div>
                <span class="digest-email-section-title-icon"><img src="<?php echo ERP_PRO_PLUGIN_ASSEST  ;?>//images/email-icons/birthday_this_week.svg" alt=""></span><span class="digest-email-section-title-content"><?php esc_html_e( 'Birthday this ' . $type, 'erp-pro' ) ?></span>
            </div>
            <?php  echo $html_for_birth_month ;?>
        </div>

        <div class="digest-email-section">
            <div>
                <span class="digest-email-section-title-icon"><img src="<?php echo ERP_PRO_PLUGIN_ASSEST  ;?>//images/email-icons/who_is_out_this_week.svg" alt=""></span><span class="digest-email-section-title-content"><?php esc_html_e( 'Who is Out This ' . $type, 'erp-pro' ) ?></span>
            </div>
            <?php  echo $html_for_leave_request ;?>
        </div>

        <div class="digest-email-section">
            <div>
                <span class="digest-email-section-title-icon"><img src="<?php echo ERP_PRO_PLUGIN_ASSEST  ;?>//images/email-icons/contract_about_to_end.svg" alt=""></span><span class="digest-email-section-title-content"><?php esc_html_e( 'Contract About to End', 'erp-pro' ) ?></span>
            </div>
            <?php  echo $html_for_c_t_employees_contract ;?>
        </div>

        <div class="digest-email-section">
            <div>
                <span class="digest-email-section-title-icon"><img src="<?php echo ERP_PRO_PLUGIN_ASSEST  ;?>//images/email-icons/trainee_about_to_end.svg" alt=""></span><span class="digest-email-section-title-content"><?php esc_html_e( 'Trainee About to End', 'erp-pro' ) ?></span>
            </div>
            <?php  echo $html_for_c_t_employees_trainee ;?>
        </div>

        <div class="digest-email-section">
            <div>
                <span class="digest-email-section-title-icon"><img src="<?php echo ERP_PRO_PLUGIN_ASSEST  ;?>//images/email-icons/work_anniversary_this week.svg" alt=""></span><span class="digest-email-section-title-content"><?php esc_html_e( 'Work Anniversary This ' . $type, 'erp-pro' ) ?></span>
            </div>
            <?php  echo $html_for_hiring_date_anniversary ;?>
        </div>

        <div style="text-align: center; margin-top: 60px;">
            <a href="<?php echo wp_login_url() ?>" class="site_login"><?php esc_html_e( 'Login Now', 'erp-pro' ) ?></a>
        </div>

        <div style="text-align: center;margin-top: 45px;">
            <img src="<?php echo ERP_PRO_PLUGIN_ASSEST  ;?>//images/email-icons/footer_divider.svg" alt="">
        </div>
    </div>
</div>
