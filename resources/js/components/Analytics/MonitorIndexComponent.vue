<template>
    <div>
        <monitor-district-component v-for="district in districts" :key="district.id" :district="district"></monitor-district-component>
    </div>
</template>
<script>
export default {
    props: ['districts'],
    data: function(){
        return {

        }
    }
}
</script>
<style scoped>

</style>