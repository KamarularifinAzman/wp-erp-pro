<?php defined( 'ABSPATH' ) or exit; ?>
<?php do_action('erp_helpscout_before_widget_start', $customer_details['id']); ?>
<div class="toggleGroup open">
    <h4><i class="icon-person"></i><strong><?php _e('Profile', 'erp-pro');?></strong></h4>
    <div class="toggle indent">
        <ul class="unstyled">
            <li><strong><?php echo $customer_details['name']; ?></strong></li>
            <li>Company  <span class="muted">-</span> <?php echo $customer_details['company']; ?></li>
            <li>Phone <span class="muted">-</span> <?php echo $customer_details['phone']; ?></span></li>
            <li>Website <span class="muted">-</span> <?php echo $customer_details['website']; ?></li>
            <li>Location <span class="muted">-</span> <?php echo $customer_details['location']; ?></li>
        </ul>
    </div>
</div>
<div class="divider"></div>
<div class="toggleGroup1 open1">
    <h4><a href="<?php echo admin_url('admin.php?page=erp-sales-customers&action=view&id='.$customer_details['id']); ?>" class="toggleBtn1"><i class="icon-flag"></i><?php _e('Recent Activity', 'erp-pro');?></a></h4>
    <div class="toggle">
        <?php
        if(empty($customer_details['recent_activity'])){
            _e('No activity found', 'erp-pro');
        }else{
            echo '<ul>';
            foreach ($customer_details['recent_activity'] as $activity){
                echo "<li>{$activity}</li>";
            }
            echo '</ul>';
        }
        ?>
    </div>
</div>
<?php do_action('erp_helpscout_before_widget_end', $customer_details['id']); ?>

