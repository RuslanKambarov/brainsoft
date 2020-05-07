<template>
    <div>
        <v-row v-show="visible" class="device-box" @click="analytics=!analytics">
            <v-col cols="12"><b>{{device.name}}</b></v-col>
            <device-analytics-component v-if="analytics" :device_id="device.owen_id"></device-analytics-component>
        </v-row>
    </div>
</template>
<script>
export default {
    props: ["device"],
    data: function(){
        return {
            visible: true,
            analytics: false
        }
    }
}
</script>    
<style scoped>
.device-box{
    margin: 15px 15px;
    border: 1px solid;
    box-shadow: 0 0 4px black;
    cursor: pointer;
}
.device-box:hover{
    border: 2px solid green;
}
</style>