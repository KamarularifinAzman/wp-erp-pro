<template>
    <base-layout
        section_id="erp-hr"
        sub_section_id="remote_work"
        :enable_content="true"
    />
</template>

<script>
const BaseLayout = window.settings.libs['BaseLayout'];

export default {
    name: "HRRemoteWork",

    components: {
        BaseLayout,
    },
};
</script>
