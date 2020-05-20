<template>
    <div class="date-picker p-4">
        <input v-model="start" type="date" class="form-control" name="start" id="">
        <input v-model="end"   type="date" class="form-control" name="end" id="">
        <button class="btn btn-primary" @click="filter()">Применить</button>
        <div class="alert">{{error}}</div>
    </div>
</template>
<script>
export default {
    props: ['base_url'],
    data: function(){
        return {
            start: 0,
            end:   0,
            error: ""
        }
    },
    mounted(){
        $(".v-toolbar__title").after(this.$el)
    },
    methods:{
        filter: function(){
            if(this.end < this.start){
                this.error = "Дата начала должна быть раньше даты окончания"
                return
            }
            if((this.end == 0) || (this.start == 0)){
                this.error = "Установите дату начала и дату окончания"
                return
            }
            
            window.location = this.base_url+"/"+this.start+"/"+this.end
            
        }
    }

}
</script>
<style scoped>
.date-picker{
    display: flex;
}
.date-picker input{
    margin: 0 5px;
    max-width: 160px;
}
.alert{
    margin: 5px;
    padding: 5px;
    color: red;
    font-family: 'Times New Roman', Times, serif;
    font-size: 14;
}
</style>