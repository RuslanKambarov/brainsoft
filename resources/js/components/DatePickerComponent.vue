<template>
    <div class="date-picker p-4">
        <date-picker v-model="date" type="datetime" range></date-picker>
        <button class="btn btn-primary" @click="filter()">Применить</button>        
        <div class="alert">{{error}}</div>
    </div>
</template>
<script>
import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';
export default {
    components: {
        DatePicker
    },
    props: ['base_url'],
    data: function(){
        return {
            date:   0,
            error: ""
        }
    },
    mounted(){
        $(".v-toolbar__title").after(this.$el)
    },
    methods:{
        filter: function(){
            if(this.date === 0){
                this.error="Выберите дату"
            }else{
                window.location = this.base_url+"/"+this.date[0]+"/"+this.date[1]
            }                        
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
button{
    margin: 0 5px;
    height: 34px;
}
</style>