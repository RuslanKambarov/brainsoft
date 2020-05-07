<template>
    <div>
        <div class="panel">
            <v-text-field v-model="filterString" @input="filterDevices()" placeholder="Поиск" solo outlined dense>
            </v-text-field>
        </div>
        <audit-device-component v-for="device in devices" :device="device" :key="device.owen_id" :ref="device.name"></audit-device-component>
    </div>
</template>
<script>
export default {
    props: ['devices'],
    data: function(){
        return {
            filterString: ""
        }
    },
    mounted(){

},
    methods:{
        filterDevices: function(){
            Object.entries(this.$refs).forEach(([key, value]) => {
                if(this.filterString === ""){
                    value[0].$data.visible = true
                }else{
                    if(key.indexOf(this.filterString) >= 0){
                        value[0].$data.visible = true
                    }else{
                        value[0].$data.visible = false
                    }
                }
            })            
        }
    }
}
</script>