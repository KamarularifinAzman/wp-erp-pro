<div class="erp-att-self-service-widget" v-cloak>
    <div class="clock-container">
	    <div class="self-service-clock">
            <p v-html="digital_clock"></p>
        </div>
    </div>

    <div class="shift-dorpdown-container text-center" v-if="shift_assigned">
        <table class="erp-att-shift-info">
            <tbody>
                <tr>
                    <td class="text-left">
                        <?php _e( 'Shift', 'erp-pro' ); ?>
                    </td>
                    <td class="text-left">
                        {{ attendance.shift_title }}
                    </td>
                </tr>
                <tr>
                    <td class="text-left">
                        <?php _e( 'Shift Time', 'erp-pro' ); ?>
                    </td>
                    <td class="text-left">
                        {{ shift_time }}
                    </td>
                </tr>
                <tr>
                    <td class="text-left">
                        <?php _e( 'Shift Duration Start', 'erp-pro' ); ?>
                    </td>
                    <td class="text-left">
                        {{ attendance.ds_start_time }}
                    </td>
                </tr>
                <tr>
                    <td class="text-left">
                        <?php _e( 'Shift Duration End', 'erp-pro' ); ?>
                    </td>
                    <td class="text-left">
                        {{ attendance.ds_end_time }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div v-else>
        <p class="text-center">{{ i18n.noShiftAssigned }}</p>
    </div>

    <div class="checkin-time-container">
        <div class="self-service-checkin-time"></div>
        <div class="self-service-checkout-time"></div>
    </div>

    <div class="checkin-button-container">
        <button
            :disabled="disable_checking_button"
            @click="saveAttendance('checkin')"
            class="button button-primary self-service-checkin-button"
        >{{ i18n.checkin }}</button>

        <button
            :disabled="!disable_checking_button"
            @click="saveAttendance('checkout')"
            class="button button-primary self-service-checkout-button"
        >{{ i18n.checkout }}</button>
    </div>

    <div class="quick-info" v-if="attendance.min_checkin !== '00:00:00' && attendance.max_checkout === '00:00:00'">
        <p><span>Checkin</span> <br> {{ timeConvFrom24(attendance.min_checkin) }}</p>
        <p><span>Working</span> <br> {{ workingTime }}</p>
    </div>
</div>
