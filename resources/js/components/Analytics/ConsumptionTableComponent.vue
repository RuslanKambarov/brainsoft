<template>
    <div style="width: 100%">
        <div class="row grand-heading">
            <div class="col-7"><h3 class="text-center"><b>Аналитика учета топлива за {{month}}</b></h3></div>
            <div class="col-2"><date-picker v-model="date"  type="month" @change="getData()"></date-picker></div>
            <div class="col-2"><button @click="getSeasonData()" class="btn btn-small btn-primary m-4 get-excell-button">За сезон</button></div>
            <div class="col-1"><a :href="'/analytics/consumption/excell/'+this.district_id+'/'+this.date"><button class="btn btn-small btn-success m-4 get-excell-button">EXCELL</button></a></div>
        </div>
        <div class="table-wrapper">

            <table class="table table-striped table-bordered">
                <thead>
                    <tr class="table-heading-1">
                        <th style="z-index: 4" class="side-heading-1" rowspan="2">№</th>
                        <th style="z-index: 4; min-width: 110px" class="side-heading-2" rowspan="2">ФИО</th>
                        <th style="z-index: 4" class="side-heading-3" rowspan="2">Объект</th>
                        <th rowspan="2">Годовая потребность топлива</th>
                        <th colspan="6">Всего</th>
                        <th colspan="3" v-for="day in days">
                            {{day}}
                        </th>                        
                    </tr>
                    <tr class="table-heading-2">
                        <th>Логист</th>
                        <th>Расхождение</th> 
                        <th>Приход</th>                        
                        <th>Расход</th>
                        <th>Остаток</th>
                        <th>Нарушение</th>
                        <template v-for="day in days">
                            <th>Приход</th>                            
                            <th>Расход</th>
                            <th v-if="date===null">Нарушение</th>
                            <th v-else>Ввод данных</th>
                        </template>
                    </tr>
                </thead>
                <tbody v-show="loader==false">
                    <template v-for="(user_data, user_name) in data">
                        <template v-for="(object_data, object_name, index) in user_data">
                            <tr>
                                <template v-if="object_name == 'Всего'">
                                    <td colspan="3" class="side-heading-1 total">{{user_name}}</td>
                                    <td v-if="reserve[user_name] !== null">{{reserve[user_name]}}</td>
                                </template>                                                                
                                <template v-else>
                                    <td class="side-heading-1">{{++index}}</td>
                                    <td class="side-heading-2" style="width: 250px">{{user_name}}</td>
                                    <td class="side-heading-3" style="width: 250px">{{object_name}}</td>
                                    <td v-if="reserve[object_name] !== null">{{reserve[object_name]}}</td>            
                                </template>
                                <template v-for="(day_data, day_name) in object_data">
                                    <template v-if="day_name == 'Всего'">
                                        <td v-if="typeof(day_data.logist) !== 'undefined'">{{Math.round(day_data.logist * 100)/100}}</td>
                                        <td v-if="typeof(day_data.diff) !== 'undefined'">{{Math.round(day_data.diff * 100)/100}}</td>
                                        <td>{{Math.round(day_data.income * 100)/100}}</td>
                                        <td>{{Math.round(day_data.consumption * 100)/100}}</td>
                                        <td>{{Math.round(day_data.balance * 100)/100}}</td> 
                                        <td v-if="day_data.input == 1">Да</td>
                                        <td v-else>Нет</td>
                                    </template>                                    
                                    <template v-else-if="day_data.input == 1">
                                        <td class="input-true" @click="edit_consumption(day_data, day_name, object_name, user_name)">{{Math.round(day_data.income * 100)/100}}</td>                                    
                                        <td class="input-true">{{Math.round(day_data.consumption * 100)/100}}</td>                                                                            
                                        <td class="input-true">Да</td>
                                    </template>
                                    <template v-else>
                                        <td class="input-false" @click="edit_consumption(day_data, day_name, object_name, user_name)">{{Math.round(day_data.income * 100)/100}}</td>                                    
                                        <td class="input-false">{{Math.round(day_data.consumption * 100)/100}}</td>                                                                            
                                        <td class="input-false">Нет</td>
                                    </template>
                                </template>
                            </tr>
                        </template>
                    </template>
                </tbody>
            </table>
            <div class="loader" v-if="loader">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- edit consumption dialog -->
        <v-dialog v-model="dialog" max-width="500">

        <div :class="'alert alert-'+message.type">
            {{message.text}}
        </div>

        <v-card>
          <v-card-title class="headline">Изменить данные</v-card-title>
            <v-card-text>
                <hr>
                <v-text-field v-model="dialog_data.income" outlined  label="Приход в т."></v-text-field>
                <v-text-field v-model="dialog_data.consumption" outlined  label="Расход в кг."></v-text-field>
            </v-card-text>
            <v-card-actions>
                <v-btn color="green darken-1" text @click="dialog=!dialog; message = {}">Отменить</v-btn>
                <v-spacer></v-spacer>
                <v-btn  color="green darken-1" text @click="send_data()">Сохранить</v-btn>
            </v-card-actions>
        </v-card>
        </v-dialog>
    </div>
</template>
<script>
export default {
    props: ['district_id'],
    data: function(){
        return {
            date: new Date(),
            days: {},
            data: {},
            dialog_data: {},
            reserve: [],
            dialog: false,
            loader: false,
            message: {}
        }
    },
    computed: {
        month: function(){
            if(this.date == null) return "сезон";
            return this.date.toLocaleString("ru", {month: 'long'})
        }
    },    
    mounted(){
        var date = new Date()
        axios.get("/analytics/consumption/analytics/"+this.district_id+'/'+this.date).then((response) => {this.data = response.data.consumption_analytics; this.days = response.data.period; this.reserve = response.data.reserve});
    },
    methods: {        
        getData: function(){
            this.loader = true
            axios.get("/analytics/consumption/analytics/"+this.district_id+"/"+this.date).then((response) => {this.data = response.data.consumption_analytics; this.days = response.data.period; this.reserve = response.data.reserve; this.loader = false});            
        },
        getSeasonData: function(){
            this.loader = true
            axios.get("/analytics/consumption/season/"+this.district_id).then((response) => {this.data = response.data.consumption_analytics; this.days = response.data.period; this.loader = false, this.date = null});                        
        },
        edit_consumption: function(day_data, day_name, object_name, user_name){
            this.dialog_data.record_id = day_data.record_id
            this.dialog_data.income = day_data.income
            this.dialog_data.consumption = day_data.consumption            
            this.dialog_data.object_id = day_data.object_id
            this.dialog_data.user_name = user_name
            this.dialog_data.object_name = object_name
            this.dialog_data.day_name = day_name

            this.dialog = true
        },
        send_data: function(){
            axios.post("/analytics/consumption/edit/"+this.district_id, {
                token:  $('meta[name="csrf-token"]').attr('content'),
                parameters: this.dialog_data
            }).then((response)=> { 
                if(response.data.type == "success"){
                    this.data[this.dialog_data.user_name][this.dialog_data.object_name][this.dialog_data.day_name].income = response.data.record.income
                    this.data[this.dialog_data.user_name][this.dialog_data.object_name][this.dialog_data.day_name].consumption = response.data.record.consumption
                    this.data[this.dialog_data.user_name][this.dialog_data.object_name][this.dialog_data.day_name].input = 1
                    this.data[this.dialog_data.user_name][this.dialog_data.object_name][this.dialog_data.day_name].record_id = response.data.record.id
                    this.dialog = false
                }else{
                    this.message = {
                        text: response.data.text,
                        type: response.data.type
                    } 
                }                
            }).catch((error) => {
                this.message = {
                    type: "danger",
                    text: "Произошла ошибка! Попробуйте позднее" 
                }
            })
        }
    }
}
</script>
<style scoped>
table, tr, td, th{
    font-size: 12px;
    text-align: center;
    vertical-align: middle !important;
}
.table-wrapper{
    max-height: 700px;
    overflow-y: scroll;
    width: 100%;
    overflow-x: scroll;
}
table thead th{
    color: black; 
    position: sticky; 
    background-color: #6c757d;
}
.grand-heading{
    position: sticky;
    top: 0;
    left: 0;
}
.grand-heading button{
    margin: 0 !important;
}
.table-heading-1 th{  
    top: 0;
    z-index: 3;
    
}
.table-heading-2 th{ 
    z-index: 3;
    top: 41px;
}
.table-heading-3 th{  
    z-index: 3;
    top: 120px;
}
td button{
    margin: 0 !important;
}
.loader{
    width: 100%;
}
.wide{
    width: 250px;
}
.side-heading-1{
    background-color: #a7a19e;
    position: sticky;
    left: 0;
}
.side-heading-2{
    background-color: #a7a19e;
    position: sticky;
    min-width: 40px;
    left: 40px;
}
.side-heading-3{
    background-color: #a7a19e;    
    position: sticky;
    min-width: 110px;
    left: 150px;
}
.total, .total ~ td{
    background: #f1f2f3;
    border-top: 2px solid;
    border-bottom: 2px solid;
}
.input-true{
    background-color: #A9F16C;
}
.input-false{
    background-color: #FF7373;
}
</style>