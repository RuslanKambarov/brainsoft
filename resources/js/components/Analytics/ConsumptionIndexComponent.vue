<template>
    <div>
        <consumption-district-component v-for="district in districts" :key="district.id" :district="district"></consumption-district-component>
    </div>
</template>
<script>
export default {
    props: ['districts'],
    mounted(){
        
    }
}
</script>