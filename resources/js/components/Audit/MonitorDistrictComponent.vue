<template>
    <v-row class="district-box" @click="expand=true">
        <h3 class="text-center m-4">{{district.name}}</h3>
        <v-spacer></v-spacer>
        <div class="control-buttons" v-show="expand">
            <a :href="'/audit/monitor/excell/'+district.owen_id"><button class="btn btn-small btn-success m-4 get-excell-button">Excell</button></a>
            <button class="btn btn-small btn-success" @click="expand=false">Скрыть</button>
        </div>
        <monitor-table-component v-if="expand==true" :district_id="district.owen_id"></monitor-table-component>
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