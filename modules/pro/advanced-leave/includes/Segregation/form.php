<?php
$jan = !empty( $segregation ) ? $segregation['jan'] : 0;
$feb = !empty( $segregation ) ? $segregation['feb'] : 0;
$mar = !empty( $segregation ) ? $segregation['mar'] : 0;
$apr = !empty( $segregation ) ? $segregation['apr'] : 0;
$may = !empty( $segregation ) ? $segregation['may'] : 0;
$jun = !empty( $segregation ) ? $segregation['jun'] : 0;
$jul = !empty( $segregation ) ? $segregation['jul'] : 0;
$aug = !empty( $segregation ) ? $segregation['aug'] : 0;
$sep = !empty( $segregation ) ? $segregation['sep'] : 0;
$oct = !empty( $segregation ) ? $segregation['oct'] : 0;
$nov = !empty( $segregation ) ? $segregation['nov'] : 0;
$dec = !empty( $segregation ) ? $segregation['decem'] : 0;
?>

<div class="form-group">

    <div class="policy-leave-segregation">

        <div class="segregation-label"><?php
            esc_attr_e( 'Segregation', 'erp-pro' );
            echo ' ' . erp_help_tip( esc_attr__( "Segregation value will be applied based on an employee's joining date in addition to the policy's applicable after days.", 'erp-pro' ), false, 'title' );
        ?></div>

        <div>
            <table class="segre-table-part1">
                <thead>
                    <tr>
                        <th><?php esc_html_e('January', 'erp-pro'); ?></th>
                        <th><?php esc_html_e('February', 'erp-pro'); ?></th>
                        <th><?php esc_html_e('March', 'erp-pro'); ?></th>
                        <th><?php esc_html_e('April', 'erp-pro'); ?></th>
                        <th><?php esc_html_e('May', 'erp-pro'); ?></th>
                        <th><?php esc_html_e('June', 'erp-pro'); ?></th>
                    </tr>
                </thead>
                <tbody class="segre-date">
                    <tr>
                        <td><input name="segre[jan]" class="segre" type="number" value="<?php echo $jan; ?>"></td>
                        <td><input name="segre[feb]" class="segre" type="number" value="<?php echo $feb; ?>"></td>
                        <td><input name="segre[mar]" class="segre" type="number" value="<?php echo $mar; ?>"></td>
                        <td><input name="segre[apr]" class="segre" type="number" value="<?php echo $apr; ?>"></td>
                        <td><input name="segre[may]" class="segre" type="number" value="<?php echo $may; ?>"></td>
                        <td><input name="segre[jun]" class="segre" type="number" value="<?php echo $jun; ?>"></td>
                    </tr>
                </tbody>
            </table>

            <table>
                <thead>
                    <tr>
                        <th><?php esc_html_e('July', 'erp-pro'); ?></th>
                        <th><?php esc_html_e('August', 'erp-pro'); ?></th>
                        <th><?php esc_html_e('September', 'erp-pro'); ?></th>
                        <th><?php esc_html_e('October', 'erp-pro'); ?></th>
                        <th><?php esc_html_e('November', 'erp-pro'); ?></th>
                        <th><?php esc_html_e('Decemeber', 'erp-pro'); ?></th>
                    </tr>
                </thead>
                <tbody class="segre-date">
                    <tr>
                        <td><input name="segre[jul]" class="segre" type="number" value="<?php echo $jul; ?>"></td>
                        <td><input name="segre[aug]" class="segre" type="number" value="<?php echo $aug; ?>"></td>
                        <td><input name="segre[sep]" class="segre" type="number" value="<?php echo $sep; ?>"></td>
                        <td><input name="segre[oct]" class="segre" type="number" value="<?php echo $oct; ?>"></td>
                        <td><input name="segre[nov]" class="segre" type="number" value="<?php echo $nov; ?>"></td>
                        <td><input name="segre[decem]" class="segre" type="number" value="<?php echo $dec; ?>"></td>
                    </tr>
                </tbody>
            </table>
            <p class="description margin-left-0"><?php esc_attr_e( '0 (zero) values will be ignored and policy days will be applied.' ); ?></p>
        </div>
    </div>
</div>
