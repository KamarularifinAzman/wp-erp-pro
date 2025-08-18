;
/**********************************
 ***********************************
 * WPERP People Fields Main JS
 ***********************************
 ***********************************/


/**
 * Receives the saved from data from Server
 * If no data it will be a blank array
 */


    var serverData = wpErpRec.qcollection || [];

    // unquotes unserialized boolean data from wordpress that was quoted
    for (var i in serverData) {
        if ('true' == serverData[i].required) {
            serverData[i].required = true;
        } else {
            serverData[i].required = false;
        }
    }

    // Should be removed in production
    //console.log(serverData);

    // Vue Component for Single Field
    Vue.component('questions-field', {

        // Component will get only these data from outside
        props: ['model', 'count' ],

        // The component template
        template: '#single-field-template',

        // Temporary data to be used within this component
        data: function () {

            return {

                edit: true,

                editButton: false,

                hover: false,

                fields: {
                    text: { value: 'text', text: 'Text', childOptions: false },
                    textarea: { value: 'textarea', text: 'Textarea', childOptions: false },
                    select: { value: 'select', text: 'Dropdown', childOptions: true },
                    radio: { value: 'radio', text: 'Radio', childOptions: true },
                    checkbox: { value: 'checkbox', text: 'Checkbox', childOptions: true }
                }
            }
        },

        // Automatically computed data
        computed: {

            // On field change checks if this field has child options
            hasChildOptions: function () {
                if (this.model.type) {
                    return this.fields[this.model.type].childOptions;
                }
            }

        },

        watch: {

            // While changing field deletes options if not required
            'model.type': function () {

                if (false == this.fields[this.model.type].childOptions) {
                    this.model['options'] = [
                        { text: '', value: '' }
                    ];
                }

            }

        },

        // Component Methods
        methods: {

            // adds a new child option
            addNewOption: function (model) {
                this.model.options.push({ text: '', value: '' });
            },

            // removes a child option
            removeOption: function (option) {
                this.model.options.$remove(option);
            },

            // will trigger when click save button
            saveData: function () {
                this.edit = false;
                this.sendToServer();
            },

            // will trigger when click delete button
            deleteModel: function () {

                var confirmed = confirm('Do you want to delete ' + this.model.label + ' ?');

                if (confirmed) {
                    var index = serverData.indexOf(this.model);
                    serverData.splice(index, 1);

                    this.sendToServer();

                    //this.edit = false;
                }
            },

            // sends data to server
            sendToServer: function () {
                var post_id = jQuery('#post_ID').val();
                var field_data = {
                    action: 'recruitment_form_builder',
                    postid: post_id,
                    qcollection: wperprec.qcollection,
                    nonce: wperprec.nonce
                };

                // Ajax call to ajaxurl
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: field_data,
                    dataType: "json",
                    success: function (response) {

                    }
                });
            }
        }
    });

if ( jQuery('#questions-field-main').length > 0 ) {

    var wperprec = new Vue({

        // Vue object's view will append here
        el: '#questions-field-main',

        // Vue object data
        data: {
            // assings server data to base qcollection
            qcollection: serverData,
            nonce: wpErpRec.nonce,
            ptitle: wpErpRec.post_title
        },

        // Vue object methods
        methods: {

            /**
             * Will add a default model to qcollection
             * while Add new Field
             */
            addNewField: function () {
                var current_length = this.qcollection.length;

                this.qcollection.push({
                    label: '',
                    name: this.ptitle + '_' + current_length,
                    type: 'text',
                    options: [
                        { text: '', value: '' }
                    ]
                });
            }
        }
    });

}