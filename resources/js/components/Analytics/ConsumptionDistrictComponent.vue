<template>
    <v-row class="district-box">
        <div class="col-12 expander" @click="expand = !expand">
            <h3 class="text-center m-4">{{district.name}}</h3>
        </div>
        <consumption-table-component :district_id="district.id" v-if="expand"></consumption-table-component>
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
    padding: 5px;
    cursor: pointer;
}
.expander:hover{
    border: 2px solid green;
}
</style>