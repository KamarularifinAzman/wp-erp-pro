<div id="basic-payroll-info-wrapper" class="wrap basic-payroll-info not-loaded">

    <div class="basic-info-col-50">

        <div class="postbox leads-actions">
            <div class="postbox-header">
                <h3 class="hndle"><?php esc_html_e( 'Detail Information At A glance', 'erp-pro' ); ?></h3>

                <div class="handle-actions hide-if-no-js">
                    <button type="button" class="handlediv" aria-expanded="true">
                        <span class="screen-reader-text"><?php _e( 'Click to toggle', 'erp-pro' ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
            
            <div class="inside">
                <h3><?php _e( 'Fixed Allowance', 'erp-pro' );?></h3>
                <ul class="paylist">
                    <li>
                        <label><?php _e( 'Pay Item', 'erp-pro' );?></label>
                        <label><?php _e( 'Pay Item Amount', 'erp-pro' );?></label>
                        <label>&nbsp;</label>
                    </li>
                    <li v-for="plist in paylist">
                        <label>{{ plist.payitem }}</label>
                        <label>{{ plist.pay_item_amount }}</label>
                        <label @click="removePayitem(plist.id)"><i class="fa fa-trash"></i></label>
                    </li>
                </ul>

                <h3><?php _e( 'Fixed Deduction', 'erp-pro' );?></h3>
                <ul class="paylist">
                    <li>
                        <label><?php _e( 'Pay Item', 'erp-pro' );?></label>
                        <label><?php _e( 'Pay Item Amount', 'erp-pro' );?></label>
                        <label>&nbsp;</label>
                    </li>
                    <li v-for="plist in deductionlist">
                        <label>{{ plist.payitem }}</label>
                        <label>{{ plist.pay_item_amount }}</label>
                        <label @click="removePayitem(plist.id)"><i class="fa fa-trash"></i></label>
                    </li>
                </ul>

                <h3><?php _e( 'Fixed Tax', 'erp-pro' );?></h3>
                <ul class="paylist">
                    <li>
                        <label><?php _e( 'Tax Caption', 'erp-pro' );?></label>
                        <label><?php _e( 'Tax Amount', 'erp-pro' );?></label>
                        <label>&nbsp;</label>
                    </li>
                    <li v-for="tlist in taxlist">
                        <label>{{ tlist.payitem }}</label>
                        <label>{{ tlist.pay_item_amount }}</label>
                        <label @click="removePayitem(tlist.id)"><i class="fa fa-trash"></i></label>
                    </li>
                </ul>

            </div>
        </div>
    </div>

    <div class="basic-info-col-50">
        <div class="postbox leads-actions">
            <div class="postbox-header">
                <h3 class="hndle"><?php esc_html_e( 'Bank and Tax Info', 'erp-pro' ); ?></h3>

                <div class="handle-actions hide-if-no-js">
                    <button type="button" class="handlediv" aria-expanded="true">
                        <span class="screen-reader-text"><?php _e( 'Click to toggle', 'erp-pro' ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                </div>
            </div>

            <div class="inside edit_payroll">
                <ul class="erp-list separated">
                    <li>
                        <label><?php _e( 'Employee Tax Number', 'erp-pro' );?></label>
                        <span class="sep"> : </span>
                        <span class="value"><input type="text" v-model="employee_tax_number" name="employee_tax_number" value="" /></span>
                    </li>
                    <!-- <li>
                        <label><?php _e( 'Basic Pay', 'erp-pro' );?></label>
                        <span class="sep"> : </span>
                        <span class="value">
                            <input type="text" v-model="ordinary_rate" name="ordinary_rate" value="" />
                        </span>
                    </li> -->
                    <li>
                        <label><?php _e( 'Bank Account Number', 'erp-pro');?></label>
                        <span class="sep"> : </span>
                        <span class="value"><input type="text" v-model="bank_acc_number" name="bank_acc_number" value="" /></span>
                    </li>
                    <li>
                        <label><?php _e( 'Bank Account Name', 'erp-pro');?></label>
                        <span class="sep"> : </span>
                        <span class="value"><input type="text" v-model="bank_acc_name" name="bank_acc_name" value="" /></span>
                    </li>
                    <li>
                        <label><?php _e( 'Bank Name', 'erp-pro');?></label>
                        <span class="sep"> : </span>
                        <span class="value"><input type="text" v-model="bank_name" name="bank_name" value="" /></span>
                    </li>
                </ul>
                <input type="button" class="button button-primary alignright" value="Submit" v-on:click="addBasicInfo">
                <input type="button" class="button button-primary alignright view_basic_info_btn" value="View" v-on:click="viewBasicInfo">
            </div>
            <div class="inside view_payroll">
                <ul class="erp-list separated">
                    <li>
                        <label><?php _e( 'Employee Tax Number', 'erp-pro' );?></label>
                        <span class="sep"> : </span>
                        <span class="value">{{ employee_tax_number }}</span>
                    </li>
                    <!-- <li>
                        <label><?php _e( 'Basic Pay', 'erp-pro' );?></label>
                        <span class="sep"> : </span>
                        <span class="value">
                            {{ ordinary_rate }}
                        </span>
                    </li> -->
                    <li>
                        <label><?php _e( 'Bank Account Number', 'erp-pro');?></label>
                        <span class="sep"> : </span>
                        <span class="value">{{ bank_acc_number }} </span>
                    </li>
                    <li>
                        <label><?php _e( 'Bank Account Name', 'erp-pro');?></label>
                        <span class="sep"> : </span>
                        <span class="value">{{ bank_acc_name }}</span>
                    </li>
                    <li>
                        <label><?php _e( 'Bank Name', 'erp-pro');?></label>
                        <span class="sep"> : </span>
                        <span class="value">{{ bank_name }}</span>
                    </li>
                </ul>
                <input type="button" class="button button-primary alignright" value="Edit" v-on:click="editBasicInfo">
            </div>

        </div>

        <div class="postbox leads-actions fixed_allowance_deduction not-loaded">
            <div class="postbox-header">
                <h3 class="hndle"><?php esc_html_e( 'Fixed Allowance Payments', 'erp-pro' ); ?></h3>

                <div class="handle-actions hide-if-no-js">
                    <button type="button" class="handlediv" aria-expanded="true">
                        <span class="screen-reader-text"><?php _e( 'Click to toggle', 'erp-pro' ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
            
            <div class="inside">
                <ul class="erp-list separated">
                    <li>
                        <label><?php _e( 'Payitem', 'erp-pro' );?></label>
                        <span class="sep"> : </span>
                        <span class="value">
                            <select v-model="pay_allowance_title">
                                <option v-for="payitem in payAllowanceItemList" value="{{ payitem.id }}">
                                    {{ payitem.payitem }}
                                </option>
                            </select>
                        </span>
                    </li>
                    <li>
                        <label><?php _e( 'Payitem payment', 'erp-pro' );?></label>
                        <span class="sep"> : </span>
                        <span class="value">
                            <input type="text" v-model="pay_allowance_amount" @focus="VanishZero" @blur="ReturnZero"/>
                        </span>
                    </li>
                </ul>
                <button class="button button-primary alignright" @click="savePayitem"><?php _e( 'Save', 'erp-pro');?></button>
            </div>
        </div>

        <div class="postbox leads-actions fixed_allowance_deduction">
            <div class="postbox-header">
                <h3 class="hndle"><?php esc_html_e( 'Fixed Deduction Payments', 'erp-pro' ); ?></h3>

                <div class="handle-actions hide-if-no-js">
                    <button type="button" class="handlediv" aria-expanded="true">
                        <span class="screen-reader-text"><?php _e( 'Click to toggle', 'erp-pro' ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
            
            <div class="inside">
                <ul class="erp-list separated">
                    <li>
                        <label><?php _e( 'Deduction item', 'erp-pro' );?></label>
                        <span class="sep"> : </span>
                        <span class="value">
                            <select v-model="pay_deduction_title">
                                <option v-for="payitem in payDeductionItemList" value="{{ payitem.id }}">
                                    {{ payitem.payitem }}
                                </option>
                            </select>
                        </span>
                    </li>
                    <li>
                        <label><?php _e( 'Deduction payment', 'erp-pro' );?></label>
                        <span class="sep"> : </span>
                        <span class="value">
                            <input type="text" v-model="pay_deduction_amount" @focus="VanishZeroDeduct" @blur="ReturnZeroDeduct"/>
                        </span>
                    </li>
                </ul>
                <button class="button button-primary alignright" @click="saveDeductItem"><?php _e( 'Save', 'erp-pro');?></button>
            </div>
        </div>

        <div class="postbox leads-actions fixed_allowance_deduction">
            <div class="postbox-header">
                <h3 class="hndle"><?php esc_html_e( 'Tax', 'erp-pro' ); ?></h3>

                <div class="handle-actions hide-if-no-js">
                    <button type="button" class="handlediv" aria-expanded="true">
                        <span class="screen-reader-text"><?php _e( 'Click to toggle', 'erp-pro' ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
            
            <div class="inside">
                <ul class="erp-list separated">
                    <li>
                        <label><?php _e( 'Tax', 'erp-pro' );?></label>
                        <span class="sep"> : </span>
                        <span class="value">
                            <select v-model="pay_tax_title">
                                <option v-for="payitem in payTaxItemList" value="{{ payitem.id }}">
                                    {{ payitem.payitem }}
                                </option>
                            </select>
                        </span>
                    </li>
                    <li>
                        <label><?php _e( 'Tax amount', 'erp-pro' );?></label>
                        <span class="sep"> : </span>
                        <span class="value">
                            <input type="text" v-model="pay_tax_amount" @focus="VanishZeroTax" @blur="ReturnZeroTax"/>
                        </span>
                    </li>
                </ul>
                <button class="button button-primary alignright" @click="saveTaxItem"><?php _e( 'Save', 'erp-pro');?></button>
            </div>
        </div>

        <div class="postbox leads-actions">
            <div class="postbox-header">
                <h3 class="hndle"><?php esc_html_e( 'Payment Details', 'erp-pro' ); ?></h3>

                <div class="handle-actions hide-if-no-js">
                    <button type="button" class="handlediv" aria-expanded="true">
                        <span class="screen-reader-text"><?php _e( 'Click to toggle', 'erp-pro' ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
            
            <div class="inside">
                <ul class="erp-list separated">
                    <li>
                        <label><?php _e( 'Payment Method', 'erp-pro' );?></label>
                        <span class="sep"> : </span>
                        <span class="value">
                            <select v-model="payment_method">
                                <option value="Bank"><?php _e( 'Bank', 'erp-pro' );?></option>
                                <option value="Cheque"><?php _e( 'Cheque', 'erp-pro' );?></option>
                                <option value="Cash"><?php _e( 'Cash', 'erp-pro' );?></option>
                            </select>
                        </span>
                    </li>
                </ul>
                <input type="button" class="button button-primary alignright" value="<?php _e( 'Submit Payment Info', 'erp-pro' );?>" v-on:click="addPaymentMethodInfo">
            </div>
        </div>
    </div>

</div>
