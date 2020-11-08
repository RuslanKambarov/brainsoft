<template>
    <v-row class="district-box">
        <div class="col-12 expander" @click="expand = !expand">
            <h3 class="text-center m-4">{{district.name}}</h3>
        </div>
        <monitor-table-component v-if="expand==true" :district_id="district.id"></monitor-table-component>
    </v-row>
</template>
<script>
export default {
    props: ['district'],
    data: function(){
        return {
            expand: false
        }
    }
}
</script>
<style scoped>
.district-box{
    margin: 15px 15px;
    border: 1px solid;
    box-shadow: 0 0 4px black;
    cursor: pointer;
    padding: 5px;
}
.district-box:hover{
    border: 2px solid green;
}
</style>