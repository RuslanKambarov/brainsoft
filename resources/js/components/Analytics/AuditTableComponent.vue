<template>
    <div>
    <div class="table-wrapper">
        <table class="table table-striped table-bordered">
            <thead class="head">
                <tr>
                    <td colspan="18" class="text-center"><h3><b>Аналитика проведения аудитов за месяц</b></h3></td>
                    <td colspan="3"><date-picker v-model="date"  type="month" @change="getData()"></date-picker></td>
                    <td>
                        <a :href="'/analytics/audit/excell/'+this.district_id+'/'+this.date"><button class="btn btn-small btn-success m-4 get-excell-button">EXCELL</button></a>
                    </td>
                </tr>
                <tr class="table-heading-1">
                    <th rowspan="3">№</th>
                    <th rowspan="3" colspan="2">Наименование объекта</th>
                    <th rowspan="3" colspan="2">Фио инженера</th>
                    <th rowspan="2" colspan="2">Главный инженер</th>
                    <th rowspan="2" colspan="2">Инженер</th>
                    <th rowspan="2">Всего аудитов</th>
                    <th colspan="2">Показатели для КПИ</th>
                    <th colspan="10">Фактическое кол-во нарушений по проведенным аудитам за месяц</th>
                </tr>
                <tr class="table-heading-2">
                    <th>Чистота в котельной</th>
                    <th>Целостность ограждающих конструкций</th>
                    <th>Чистота в котельной</th>
                    <th>Целостность ограждающих конструкций</th>
                    <th>Проверка растяжек дымовой трубы на провис</th>
                    <th>Наличие топлива согласно отчета</th>
                    <th>Комплектность оборудования котельной</th>
                    <th>Исправность оборудования</th>
                    <th>Целостность и работоспособность электрического оборудования и проводки</th>
                    <th>Наличие воды в системе отопления</th>
                    <th>Наличие инвентаря согласно перечня</th>
                    <th>Отсутствие оголенной теплотрассы</th>
                </tr>
                <tr class="table-heading-3">
                    <th>план</th>
                    <th>факт</th>
                    <th>план</th>
                    <th>факт</th>
                    <th>факт</th>
                    <th>NOK</th>
                    <th>NOK</th>
                    <th>NOK</th>
                    <th>NOK</th>
                    <th>NOK</th>
                    <th>NOK</th>
                    <th>NOK</th>
                    <th>NOK</th>
                    <th>NOK</th>
                    <th>NOK</th>
                    <th>NOK</th>
                    <th>NOK</th>
                </tr>                  
            </thead>
            <tbody class="data">                
                <tr v-for="(row, index) in data" :key="row.id">
                    <template v-if="Object.keys(row).length == 1">
                        <td colspan="7">Итого по инженеру</td>
                        <template v-for="item in row">
                            <td>{{item.engineer_assigned}}</td>
                            <td>{{item.engineer_conducted}}</td>
                            <td>{{item.total_conducted}}</td>
                            <td>{{item.kpi1}}</td>
                            <td>{{item.kpi2}}</td>
                            <template v-for="answer in item.NOK">
                                <td>{{answer}}</td>
                            </template>
                        </template>
                    </template>
                    <template v-else-if="row.object_name ==='Итого по району'">
                        <td colspan="5"><b>{{row.object_name}}</b></td>
                        <td><b>{{row.manager_assigned}}</b></td>
                        <td><b>{{row.manager_conducted}}</b></td>
                        <td><b>{{row.engineer_assigned}}</b></td>
                        <td><b>{{row.engineer_conducted}}</b></td>
                        <td><b>{{row.total_conducted}}</b></td>
                        <td><b>{{row.kpi1}}</b></td>
                        <td><b>{{row.kpi2}}</b></td>
                        <template v-for="(td, index) in row.NOK">
                            <td :key="index"><b>{{td}}</b></td>
                        </template>                        
                    </template>
                    <template v-else>
                        <td>{{index}}</td>
                        <td colspan="2">{{row.object_name}}</td>
                        <td colspan="2">{{row.engineer}}</td>
                        <td>{{row.manager_assigned}}</td>
                        <td>{{row.manager_conducted}}</td>
                        <td>{{row.engineer_assigned}}</td>
                        <td>{{row.engineer_conducted}}</td>
                        <td>{{row.total_conducted}}</td>
                        <td>{{row.kpi1}}</td>
                        <td>{{row.kpi2}}</td>
                        <template v-for="(td, index) in row.NOK">
                            <td :key="index">{{td}}</td>
                        </template>
                    </template>
                </tr>
            </tbody>
        </table>
    </div>
    </div>
</template>
<script>  
import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';
export default {
    components: {
        DatePicker
    },
    props: ['district_id'],
    data: function(){
        return {
            date: new Date(),
            data: {},
            number: 1
        }
    },
    mounted(){
        var date = new Date()
        axios.get("/analytics/audit/analytics/"+this.district_id+"/"+this.date).then(response => this.data = response.data);
    },
    methods: {
        getData: function(){
            axios.get("/analytics/audit/analytics/"+this.district_id+"/"+this.date).then(response => this.data = response.data);            
        }
    }

}
</script>
<style scoped>
table, tr, td, th{
    font-size: 12px;
}
.table-wrapper{
    max-height: 750px;
    overflow-y: scroll;
}
table thead th{
    color: black; 
    position: sticky; 
    background-color: azure;
}
.table-heading-1 th{  
    top: 0;
    
}
.table-heading-2 th{ 
    top: 41px;
}
.table-heading-3 th{  
    top: 120px;
}
td button{
    margin: 0 !important;
}
</style>