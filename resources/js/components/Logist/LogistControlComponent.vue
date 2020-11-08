<template>
    <div class="date-picker p-4">
        <date-picker v-model="date" type="month"></date-picker>
        <select class="form-control" v-model="district">
            <option v-for="district in districts" :value="district.id">{{district.name}}</option>
        </select>
        <button class="btn btn-primary" @click="filter()">Применить</button>                
    </div>
</template>
<script>
import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';
export default {
    components: {
        DatePicker
    },
    props: ['base_url', 'districts'],
    data: function(){
        return {
            date:   0,
            district: 0,
            error: ""
        }
    },
    mounted(){
        $(".v-toolbar__title").after(this.$el)
    },
    methods:{
        filter: function(){
            if(this.district !== 0){                
                if(this.district !== 0){
                    window.location = this.base_url+"/"+this.district+"/"+this.date
                }else{
                    window.location = this.base_url+"/"+this.district
                }
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
select{
    margin-left: 5px;
    height: calc(1.6em + 0.75rem);
}
</style>