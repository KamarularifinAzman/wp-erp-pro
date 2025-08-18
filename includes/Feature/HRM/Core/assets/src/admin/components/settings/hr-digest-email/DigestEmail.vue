<template>
    <base-layout
        section_id="erp-email"
        sub_section_id="hrm_digest_email"
        :enable_content="true"
    ></base-layout>
</template>

<script>
const BaseLayout = window.settings.libs['BaseLayout'];

export default {
    name: 'HRDigestEmail',

    components: {
        BaseLayout,
    },
}
</script>
