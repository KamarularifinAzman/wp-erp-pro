<div class="wrap workflow-wrap" id="workflow-app" v-cloak>
    <?php
        if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) {
            echo '<h2>' . __( 'Edit Workflow', 'erp-pro' ) . '</h2>';
        } else {
            echo '<h2>' . __( 'Create Workflow', 'erp-pro' ) . '</h2>';
        }
    ?>
    <br />

    <form method="post" style="background: #fff;" id="workflow-form">
        <section id="workflow-basic-info">
            <div class="wf-grid-container-fluid">
                <div class="row row-padding">
                    <div class="col-md-2">
                        <strong><label for="workflow_name"><?php _e( 'Workflow Name', 'erp-pro' ); ?></label></strong>
                    </div>
                    <div class="col-md-4">
                        <input type="text" v-model="workflow_name" class="full-width {{ errors.workflow_name ? 'v-error' : '' }}" placeholder="<?php _e( 'Enter Workflow Name', 'erp-pro' ); ?>">
                        <label class="v-warn" v-if="errors.workflow_name"><?php _e( 'This field is required', 'erp-pro' ); ?></label>
                    </div>
                </div>
                <div class="row row-padding">
                    <div class="col-md-2">
                        <strong><label for="delay_time"><?php _e( 'Delay', 'erp-pro' ); ?></label></strong>
                    </div>
                    <div class="col-md-2">
                        <input type="number" v-model="delay_time" class="full-width">
                    </div>
                    <div class="col-md-2">
                        <singleselect :selected.sync="delay_period" :options="[{key: 'minute', label: 'In Minute(s)'}, {key: 'hour', label: 'In Hour(s)'}, {key: 'day', label: 'In Day(s)'}]"></singleselect>
                    </div>
                </div>

                <hr />
            </div>
        </section>

        <section id="workflow-conditions">

            <div class="wf-grid-container-fluid trigger-left-icon" id="trigger">
                <div class="row">
                    <div class="col-md-12">
                        <h2><a href="#" @click.prevent="scrollTo"><?php _e( 'Trigger', 'erp-pro' ); ?></a></h2>
                    </div>
                </div>
                <div class="row row-padding">
                    <div class="col-md-1" style="text-align: right;">
                        <label><strong><?php _e( 'Module', 'erp-pro' ); ?></strong></label>
                    </div>
                    <div class="col-md-3 col-xl-2">
                        <singleselect :selected.sync="events_group" :options="event_groups_list" placeholder="<?php _e( 'Select Module', 'erp-pro' ); ?>"></singleselect>
                    </div>
                    <div class="col-md-1" style="text-align: right;">
                        <label><strong><?php _e( 'Event', 'erp-pro' ); ?></strong></label>
                    </div>
                    <div class="col-md-3 col-xl-2">
                        <singleselect :selected.sync="event" :options="events_list" placeholder="<?php _e( 'Select Event', 'erp-pro' ); ?>"></singleselect>
                        <label class="v-warn" v-if="errors.event"><?php _e( 'This field is required', 'erp-pro' ); ?></label>
                    </div>
                </div>
                <hr v-if="event" />
            </div>

            <div class="wf-grid-container-fluid conditions-left-icon" id="conditions" v-if="event">
                <div class="row">
                    <div class="col-md-12">
                        <h2><a href="#" @click.prevent="scrollTo"><?php _e( 'Conditions', 'erp-pro' ); ?></a></h2>
                    </div>
                </div>
                <div class="row row-padding">
                    <div class="col-md-2" style="width: 130px;">
                        <singleselect :selected.sync="conditions_group" :options="[{key: 'and', label: 'For All'}, {key: 'or', label: 'For Any'}]"></singleselect>
                    </div>
                    <div class="col-md-3">
                        <label><?php _e( 'of the following conditions', 'erp-pro' ); ?></label>
                    </div>
                </div>
                <div class="condition-list">
                    <div class="row condition {{ type == 'manual' ? 'bottom-border' : '' }}" v-for="(index, condition) in conditions">
                        <condition :id="index" :conditions_group.sync="conditions_group" :condition="condition"></condition>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <button class="add-condition button button-secondary" @click.prevent="addNewCondition"><?php _e( '+ Add Condition', 'erp-pro' ); ?></button>
                        </div>
                    </div>
                </div>

                <hr />
            </div>
        </section>

        <section id="workflow-actions" v-if="is_display_actions">
            <div id="actions" class="wf-grid-container-fluid actions-left-icon">
                <div class="row">
                    <div class="col-md-12">
                        <h2><a href="#" @click.prevent="scrollTo"><?php _e( 'Actions', 'erp-pro' ); ?></a></h2>
                    </div>
                </div>
                <action-container :actions.sync="actions" :action_edit_mode="action_edit_mode"></action-container>
            </div>
        </section>

        <div class="wf-grid-container-fluid">
            <div class="row row-padding">
                <div class="col-md-12">
                    <?php
                        if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) {
                    ?>
                        <input type="hidden" name="workflow_id" id="workflow_id" value="<?php echo $_GET['id']; ?>" />
                    <?php
                        }
                    ?>
                    <input v-if="workflow_edit_mode" type="hidden" v-model="nonce" value="<?php echo wp_create_nonce( 'erp-wf-edit-workflow' ); ?>" />
                    <input v-else type="hidden" v-model="nonce" value="<?php echo wp_create_nonce( 'erp-wf-new-workflow' ); ?>" />

                    <?php
                        if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) {
                    ?>
                        <p class="submit">
                            <input type="submit" class="button-primary" value="<?php esc_attr_e( 'Update', 'erp-pro' ); ?>" @click.prevent="saveToDatabase(true)">
                        </p>
                    <?php
                        } else {
                    ?>
                        <p class="submit">
                            <input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save &amp; Activate', 'erp-pro' ); ?>" @click.prevent="saveToDatabase(true)">
                            <input type="submit" class="button-secondary" value="<?php esc_attr_e( 'Save Only', 'erp-pro' ); ?>" @click.prevent="saveToDatabase(false)">
                        </p>
                    <?php
                        }
                    ?>
                </div>
            </div>
        </div>
    </form>
</div>
