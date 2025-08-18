/**
 * Email Note Component
 *
 * @param  {[object]} feedData
 * @param  {Boolean} isValid
 *
 * @return {[void]}
 */
Vue.component( 'sms-note', {
    props : ['feed'],

    template: '#erp-crm-new-sms-note-template',

    data: function() {
        return {
            feedData: {
                message: '',
                sms_number: []
            },
            isValid: false,
            charLimit: 160,
            nextCharLimit: 160,
        }
    },

    methods: {
        notify: function () {
            this.$dispatch('bindFeedData', this.feedData );
        },

        cancelUpdateFeed: function() {
            this.$parent.$data.isEditable = false;
            this.$parent.$data.editfeedData = {};
        },

        initializeSelectTags: function() {
            jQuery('select#erp-crm-customer-sms-phone-number-tags').select2({
                tags: true,
                tokenSeparators: [' ', ','],
                placeholder: "Add phone number (Type & press enter to add)",
                dropdownCssClass: 'erp-crm-sms-number-select2-suggession-hidden'
            });
        }
    },

    events: {
        'bindEditFeedData': function (feed ) {
            this.feedData.message = feed.message;
            this.feedData.sms_number = feed.extra.sms_number;
        }
    },

    computed: {

        validation: function() {
            return {
                message : !!this.feedData.message,
                sms_number : ( this.feedData.sms_number ) && this.feedData.sms_number.length > 0 ? true : false,
            }
        },

        isValid: function() {
            var validation = this.validation

            if ( jQuery.isEmptyObject( validation ) ) return;

            return Object.keys( validation ).every(function(key){
                return validation[key]
            });
        },

        messageCount: function () {
            if ( this.feedData.message.length <= this.nextCharLimit ) {
                this.nextCharLimit;
            } else {
                this.nextCharLimit += this.charLimit;
            }

            if ( this.feedData.message.length <= (this.nextCharLimit - this.charLimit) ) {
                this.nextCharLimit -= this.charLimit;
            }

            if ( this.nextCharLimit <= 0 ) {
                this.nextCharLimit = this.charLimit;
            }

            return parseInt( this.nextCharLimit / this.charLimit );
        },
    },

    watch: {
        feedData: {
            deep: true,
            immediate: true,
            handler: function () {
                this.notify();
            }
        }
    },

    ready: function() {
        var self = this;

        self.initializeSelectTags()
        jQuery('select#erp-crm-customer-sms-phone-number-tags').on('change', function() {
            self.feedData.sms_number = jQuery(this).val();
        });
    }
});

Vue.component( 'sms-component', {
    props: [ 'i18n', 'feed' ],

    data: function() {
        return {
            headerText: '',
        }
    },

    template: '#erp-crm-timeline-feed-sms-note',

    computed: {
        headerText: function() {
            return this.i18n.smsHeaderText
                    .replace( '{{createdUserName}}', this.createdUserName )
                    .replace( '{{createdForUser}}', this.createdForUser )
                    .replace( '{{smsNumber}}', this.smsNumber );
        },

        createdUserImg: function() {
            return this.feed.created_by.avatar;
        },

        createdUserName: function() {
            return ( this.feed.created_by.ID == wpCRMvue.current_user_id ) ? this.i18n.you : this.feed.created_by.display_name;
        },

        createdForUser: function() {
            return _.contains( this.feed.contact.types, 'company' ) ? this.feed.contact.company : this.feed.contact.first_name + ' ' + this.feed.contact.last_name;
        },

        smsNumber: function() {
            return ( this.feed.type == 'sms' ) ? this.feed.extra.sms_number : '';
        }

    }

} )