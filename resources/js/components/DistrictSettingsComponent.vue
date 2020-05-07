<template>
    <div class="row">
        <div class="col-4"><input class="form-control" type='text' v-model="district.name"></div>
        <div class="col-3"><input class="form-control" type='text' v-model="district.owen_id"></div>
        <div class="col-3"><input class="form-control" type='text' v-model="district.parent_id"></div>
        <div class="col-2"><button class="btn btn-primary btn-sm" @click="saveParams()">Сохранить</button></div>
    </div>   
</template>
<script>
export default {
    props: ['district'],
    methods:{
        saveParams: function(){
            axios.post("/settings/update/"+this.district.owen_id, {
                token: $('meta[name="csrf-token"]').attr('content'),
		target: "district",
                district: this.district
            }).then(Response => {
                console.log(Response.data)
            })
        }
    }
}
</script>
<style scoped>
div{
    border-bottom: 0.1px solid #032903
}
input{
    width: 100%;
}
</style>