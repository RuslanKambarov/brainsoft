<template>
    <div class="col-6">
    <div class="row">
        <div class="col-4"><label for="setting.id">{{setting.name}}</label></div>
        <div class="col-4"><input class="form-control" type='text' v-model="setting.value"></div>
        <div class="col-4"><button class="btn btn-primary btn-sm" @click="saveParams()">Сохранить</button></div>
    </div>
    </div>   
</template>
<script>
export default {
    props: ['setting'],
    methods:{
        saveParams: function(){
            axios.post("/settings/update/"+this.setting.id, {
                token: $('meta[name="csrf-token"]').attr('content'),
		target: "setting",
                setting: this.setting
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