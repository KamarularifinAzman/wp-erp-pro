<div id="payroll-acc-settings-wrapper" class="settings-area">
    <?php $assets_head = get_option('erp_payroll_account_head_assets');?>
    <?php $salary_head = get_option('erp_payroll_account_head_salary');?>
    <?php $salary_tax_head = get_option('erp_payroll_account_head_salary_tax');?>
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="gen_financial_month"><?php _e( 'Account head for Assets', 'erp-pro' ); ?></label>
            </th>
            <td>
                <div class="row">
                    <?php
                    $dropdown      = erp_ac_get_chart_dropdown( [
                        'exclude' => [ 3, 2, 4, 5 ]
                    ] );
                    ?>
                    <select name="account_head_assets">
                        <?php if ( is_array($dropdown) && count($dropdown) > 0 ) : ?>
                            <?php foreach ( $dropdown as $key => $value ) : ?>
                                <?php if ( is_array($value) && count($value) > 0 ) : ?>
                                    <?php foreach ( $value as $inner_key => $inner_value ) : ?>
                                        <?php if ( is_array($inner_value) && count($inner_value) > 0 ) : ?>
                                            <?php foreach ( $inner_value as $ik => $iv ) : ?>
                                                <?php if ( $salary_head == $iv->id ) : ?>
                                                    <option value="<?php echo $iv->id;?>" selected="selected">
                                                        <?php echo $iv->name;?>
                                                    </option>
                                                <?php else :?>
                                                    <option value="<?php echo $iv->id;?>">
                                                        <?php echo $iv->name;?>
                                                    </option>
                                                <?php endif;?>
                                            <?php endforeach;?>
                                        <?php endif;?>
                                    <?php endforeach;?>
                                <?php endif;?>
                            <?php endforeach;?>
                        <?php endif;?>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="gen_financial_month"><?php _e( 'Account head for salary reporting', 'erp-pro' ); ?></label>
            </th>
            <td>
                <div class="row">
                    <?php
                    $dropdown      = erp_ac_get_chart_dropdown( [
                        'exclude' => [ 1, 2, 4, 5 ]
                    ] );
                    ?>
                    <select name="account_head_salary">
                        <?php if ( is_array($dropdown) && count($dropdown) > 0 ) : ?>
                            <?php foreach ( $dropdown as $key => $value ) : ?>
                                <?php if ( is_array($value) && count($value) > 0 ) : ?>
                                    <?php foreach ( $value as $inner_key => $inner_value ) : ?>
                                        <?php if ( is_array($inner_value) && count($inner_value) > 0 ) : ?>
                                            <?php foreach ( $inner_value as $ik => $iv ) : ?>
                                                <?php if ( $salary_head == $iv->id ) : ?>
                                                    <option value="<?php echo $iv->id;?>" selected="selected">
                                                        <?php echo $iv->name;?>
                                                    </option>
                                                <?php else :?>
                                                    <option value="<?php echo $iv->id;?>">
                                                        <?php echo $iv->name;?>
                                                    </option>
                                                <?php endif;?>
                                            <?php endforeach;?>
                                        <?php endif;?>
                                    <?php endforeach;?>
                                <?php endif;?>
                            <?php endforeach;?>
                        <?php endif;?>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="gen_com_start"><?php _e( 'Account head for tax reporting', 'erp-pro' ); ?></label>
            </th>
            <td>
                <div class="row">
                    <?php
                    $dropdown      = erp_ac_get_chart_dropdown( [
                        'exclude' => [ 1, 2, 4, 5 ]
                    ] );
                    ?>
                    <select name="account_head_salary_tax">
                        <?php foreach ( $dropdown as $key => $value ) : ?>
                            <?php if ( is_array($value) && count($value) > 0 ) : ?>
                                <?php foreach ( $value as $inner_key => $inner_value ) : ?>
                                    <?php if ( is_array($inner_value) && count($inner_value) > 0 ) : ?>
                                        <?php foreach ( $inner_value as $ik => $iv ) : ?>
                                            <?php if ( $salary_tax_head == $iv->id ) : ?>
                                                <option value="<?php echo $iv->id;?>" selected="selected">
                                                    <?php echo $iv->name;?>
                                                </option>
                                            <?php else : ?>
                                                <option value="<?php echo $iv->id;?>">
                                                    <?php echo $iv->name;?>
                                                </option>
                                            <?php endif;?>
                                        <?php endforeach;?>
                                    <?php endif;?>
                                <?php endforeach;?>
                            <?php endif;?>
                        <?php endforeach;?>
                    </select>
                </div>
            </td>
        </tr>
    </table>
</div>
