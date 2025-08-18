<div class="request-halfday-form">
    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => esc_html__( 'Half-day Leave', 'erp' ),
            'name'     => 'halfday',
            'value'    =>  'off',
            'type'     => 'checkbox'
        ) ); ?>
    </div>

    <div class="row halfday-leave-period">
        <?php erp_html_form_input( array(
            'label'    => esc_html__( 'Leave Period', 'erp' ),
            'name'     => 'leave-period',
            'value'    =>  'morning',
            'type'     => 'select',
            'options' => array(
                '2' => esc_html__('Morning', 'erp'),
                '3' => esc_html__('Afternoon', 'erp')
            )
        ) ); ?>
    </div>
</div>
