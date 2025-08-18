<template>
    <base-layout
        section_id="erp-crm"
        sub_section_id="crm_life_stages"
        :enable_content="false"
        :enableSubSectionTitle="false"
    >
        <form action="" class="wperp-form" method="post">
            <div class="sub-section-title pull-left">
                <h3> {{ erplifeStage.i18n.lifeStages }} </h3>
                <p><small>{{erplifeStage.lsDescription}}</small></p>
            </div>
            <button v-if="lifeStagesData.length < 10" type="button" class="wperp-btn btn--primary settings-button header-right-button" @click="popupModal({}, 'create')">
                <i class="fa fa-plus"></i> {{ erplifeStage.i18n.addMore }}
            </button>
            <div class="clearfix"></div>

            <div v-if="lifeStagesData.length">
                <table class="form-table">
                    <tbody>
                        <tr class="erp-grid-container erp-life-stage-settings-page" id="erp-life-stage-settings-page">
                            <div class="erp-life-stages">
                                <ul class="erp-life-stage-list" id="sortable">
                                    <draggable v-model="lifeStagesData" @end="sortList" @change="sortList">
                                        <li class="clearfix" v-for="(lifeStage, index) in lifeStagesData" :key="index">
                                            <div class="stage-title" style="padding: 5px;" id="title-1">
                                                <a href="#" @click="popupModal(lifeStage, 'edit')"><strong>{{ lifeStage.title }}</strong></a>
                                            </div>

                                            <input type="hidden" id="title-plural-1" :value="lifeStage.title_plural">

                                            <div class="stage-buttons" style="padding: 5px;">
                                                <button type="button" class="button button-small button-link edit-life-stage-button" @click="popupModal(lifeStage, 'edit')">
                                                    <span class="fa fa-edit"></span> {{ erplifeStage.i18n.edit }}
                                                </button>
                                                <button type="button" class="button button-small button-link delete-life-stage-button" @click="onDeletePopup(lifeStage)">
                                                    <span class="fa fa-trash"></span> {{ erplifeStage.i18n.delete }}
                                                </button>
                                            </div>
                                        </li>
                                    </draggable>
                                </ul>
                            </div>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else>
                <div class="empty-life-stage">
                    {{ erplifeStage.i18n.noLifeStage }}
                </div>
            </div>

        </form>

        <modal
            v-show="isVisibleModal"
            :title="modalMode === 'create' ? erplifeStage.i18n.addLifeStage : erplifeStage.i18n.updateLifeStage"
            @close="popupModal({}, modalMode)"
            :header="true"
            :footer="true"
            :hasForm="true"
            size="sm"
        >

            <template v-slot:body>
                <form class="wperp-form" method="post" @submit.prevent="onFormSubmit">
                    <div class="wperp-form-group">
                        <label>{{ erplifeStage.i18n.title }}</label>
                        <input v-model="singleLifeStage.title" class="wperp-form-field" />
                    </div>

                    <div class="wperp-form-group">
                        <label>{{ erplifeStage.i18n.titlePlural }}</label>
                        <input v-model="singleLifeStage.title_plural" class="wperp-form-field" />
                    </div>

                    <div class="wperp-form-group" v-if="modalMode === 'create'">
                        <label>{{ erplifeStage.i18n.slug }}</label>
                        <input v-model="singleLifeStage.slug" class="wperp-form-field" />
                    </div>
                </form>
            </template>

            <template v-slot:footer>
                <span @click="onFormSubmit">
                    <submit-button :text="erplifeStage.i18n.save" customClass="pull-right" style="margin-left: 10px" />
                </span>

                <span @click="popupModal({}, modalMode)">
                    <submit-button :text="erplifeStage.i18n.cancel" customClass="wperp-btn-cancel pull-right" />
                </span>

            </template>
        </modal>

    </base-layout>
</template>

<script>
const Modal                      = window.settings.libs['Modal'];
const SubmitButton               = window.settings.libs['SubmitButton'];
const BaseLayout                 = window.settings.libs['BaseLayout'];
const Draggable                  = window.settings.libs['Draggable'];
const generateFormDataFromObject = window.settings.libs['generateFormDataFromObject'];
const $ = jQuery;

export default {
    name: 'LifeStage',

    components: {
        BaseLayout,
        SubmitButton,
        Modal,
        Draggable
    },

    data() {
        return {
            erplifeStage    : {},
            lifeStagesData  : [],
            isVisibleModal  : false,
            singleLifeStage : {},
            modalMode       : 'create' // 'create' or 'edit'
        }
    },

    created() {
        this.$store.dispatch("spinner/setSpinner", true);
        this.getLifeStagesData();
    },

    methods: {

        getLifeStagesData() {
            const self        = this;
            self.erplifeStage = window.erpLifeStages;

            let requestData = window.settings.hooks.applyFilters(
                "requestData",
                {
                    _wpnonce: self.erplifeStage.nonce,
                    action  : "erp_crm_list_life_stages",
                }
            );

            const postData = generateFormDataFromObject(requestData);

            $.ajax({
                url        : erp_settings_var.ajax_url,
                type       : "POST",
                data       : postData,
                processData: false,
                contentType: false,
                success    : function (response) {
                    self.$store.dispatch("spinner/setSpinner", false);

                    if (response.success) {
                        self.lifeStagesData = response.data;
                    }
                },
            });
        },

        popupModal( lifeStage, modalMode) {
            if ( this.isVisibleModal ) {
                this.isVisibleModal = false;
            } else {
                this.isVisibleModal = true;
            }

            this.singleLifeStage = modalMode === 'create' ? {} : lifeStage;
            this.modalMode       = modalMode;
        },

        buildErrorMessage(message) {
            if(typeof message === 'object') {
                let msg = '';
                for (let key in message) {
                    if (key === 'data'){
                        continue;
                    }
                    msg += this.buildErrorMessage(message[key]) + '\n';
                }

                return msg;
            }

            return message;
        },

        onFormSubmit() {
            const self     = this;
            const isUpdate = self.modalMode === 'edit' ? true : false;
            self.$store.dispatch("spinner/setSpinner", true);

            let requestData = {
                ...self.singleLifeStage,
                stage_id : self.modalMode === 'edit' ? self.singleLifeStage.id : 0,
                action   : ! isUpdate ? 'erp_crm_add_life_stage' : 'erp_crm_update_life_stage',
                _wpnonce : self.erplifeStage.nonce,
            };

            requestData    = window.settings.hooks.applyFilters( "requestData", requestData );
            const postData = generateFormDataFromObject(requestData);

            $.ajax({
                url        : erp_settings_var.ajax_url,
                type       : "POST",
                data       : postData,
                processData: false,
                contentType: false,
                success: function (response) {
                    self.$store.dispatch("spinner/setSpinner", false);

                    if (response.success) {
                        if ( isUpdate ) {
                            self.singleLifeStage = {};
                            self.popupModal({}, 'edit');
                        } else {
                            self.popupModal({}, 'create');
                        }

                        self.getLifeStagesData();
                        self.showAlert("success", response.data.message);
                    } else {
                        self.showAlert("error", self.buildErrorMessage(response.data).trim());
                    }
                },
            });
        },

        onDeletePopup( lifeStage ) {
            const self = this;

            swal({
                title             : self.erplifeStage.i18n.deleteLifeStage,
                text              : self.erplifeStage.i18n.confirmDelete,
                type              : "warning",
                showCancelButton  : true,
                cancelButtonText  : self.erplifeStage.i18n.cancel,
                confirmButtonColor: "#DD6B55",
                confirmButtonText : self.erplifeStage.i18n.delete,
                closeOnConfirm    : false
            },
            function() {
                $.ajax({
                    type    : "POST",
                    url     : erp_settings_var.ajax_url,
                    dataType: 'json',
                    data    : {
                        stage_id: lifeStage.id,
                        slug    : lifeStage.slug,
                        _wpnonce: self.erplifeStage.nonce,
                        action  : 'erp_crm_delete_life_stage'
                    },
                } )
                .fail( function( xhr ) {
                    self.showAlert('error', xhr);
                } )
                .done( function( response ) {
                    swal.close();

                    if ( response.success ) {
                        self.getLifeStagesData();
                        self.showAlert('success', response.data.message);
                    } else {
                        self.showAlert('error', response.data);
                    }
                });
            });
        },

        sortList( data ) {
            const self                   = this;
            let orders                   = [];
            const { oldIndex, newIndex } = data;

            self.lifeStagesData.forEach( ( stage, index ) => {
                if ( typeof index !== 'undefined' ) {
                    orders.push( [ stage.id, index + 1 ] );
                }
            } );

            if ( oldIndex !== newIndex ) {
                $.ajax({
                    type    : "POST",
                    url     : erp_settings_var.ajax_url,
                    dataType: 'text',
                    data    : {
                        update  : 1,
                        orders  : orders,
                        _wpnonce: self.erplifeStage.nonce,
                        action  : 'erp_crm_update_life_stage_order'
                    },
                })
                .fail(function(xhr) {
                    this.showAlert('error', xhr);
                });
            }
        },
    },

}
</script>
