<template>
    <div>
        <audit-district-component v-for="district in districts" :key="district.id" :district="district"></audit-district-component>
    </div>
</template>
<script>
export default {
    props: ['districts'],
    mounted(){
        
    }
}
</script>