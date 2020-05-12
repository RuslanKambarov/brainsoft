<template>
    <div class="row device">
        <div class="col-3"><input class="form-control" type='text' v-model="device.name"></div>
        <div class="col-1"><input class="form-control" type='checkbox' v-model="device.controller"></div>
        <div class="col-2"><input class="form-control" type='text' v-model="device.owen_id"></div>
        <div class="col-2"><input class="form-control" type='text' v-model="device.district_id"></div>
        <div class="col-1"><input class="form-control" type='text' v-model="device.required_t"></div>
        <div class="col-1"><input class="form-control" type='text' v-model="device.required_p"></div>
        <div class="col-2"><button class="btn btn-primary btn-sm" @click="saveParams()">Сохранить</button></div>
    </div>   
</template>
<script>
export default {
    props: ['device'],
    methods:{
        saveParams: function(){
            axios.post("/settings/update/"+this.device.owen_id, {
                token: $('meta[name="csrf-token"]').attr('content'),
		target: "device",
                device: this.device
            }).then(Response => {
                console.log(Response.data)
            })
        }
    }
}
</script>
<style scoped>
input{
    width: 100%;
}
</style>