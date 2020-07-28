<template>
    <div class="container-fluid">
        <div class="row kpi-control p-4">
            <date-picker v-model="date"  type="month" @change="getData()"></date-picker>
            <button @click="getExcell()" class="btn btn-small btn-success get-excell-button">EXCELL</button>
        </div>
        <template v-for="(engineer, key) in engineers">
            <table class="table table-striped table-bordered" :key="key">
                <tr>
                    <td colspan="8"><h3 class="text-center"> {{engineer.name}}</h3></td>                    
                </tr>
                <tr>
                    <td>№</td>
                    <td>Критерий</td>
                    <td>Вес критерия</td>
                    <td>Источник</td>
                    <td>Ед. Изм.</td>
                    <td>План кол-во</td>
                    <td>не выполнено, кол-во</td>
                    <td>Оценка, %</td>                    
                </tr>
                <tr>
                    <td>1</td>
                    <td>Контроль проведения влажной уборки в помещении котельной</td> 
                    <td>10</td>
                    <td>аналитика</td>
                    <td>кол-во объектов</td>
                    <td>{{engineer.audit_results["total_objects"]}}</td>
                    <td>{{engineer.audit_results["kpi1"]}}</td>
                    <td>{{engineer.audit_results["kpi1_mark"]}}</td>                    
                </tr>
                <tr>
                    <td>2</td>
                    <td>Ведение сменного журнала котельной. Наличие документации (режимная карта, график смен, телефоны ответственных лиц)</td> 
                    <td>10</td>
                    <td>аналитика</td>
                    <td>кол-во объектов</td>
                    <td>{{engineer.audit_results["total_objects"]}}</td>
                    <td>{{engineer.audit_results["kpi2"]}}</td>
                    <td>{{engineer.audit_results["kpi2_mark"]}}</td>
                </tr>
                <tr>    
                    <td>3</td>
                    <td>Обеспечение бесперебойного теплоснабжения потребителей в соответствии с утвержденным графиком, безопасную работу оборудования, соблюдение требований правил технической эксплуатации, правил охраны труда и пожарной безопасности</td> 
                    <td>40</td>
                    <td>журнал жалоб</td>
                    <td>кол-во жалоб</td>
                    <td>1</td>
                    <td><input v-model="engineer.audit_results['report']" @change="compute(key)"></td>
                    <td>{{engineer.audit_results['report_mark']}}</td>                    
                </tr>
                <tr> 
                    <td>4</td>
                    <td>Предоставление суточного расхода угля</td> 
                    <td>20</td>
                    <td>аналитика</td>
                    <td>кол-во объектов</td>
                    <td>{{engineer.consumption_data['total_objects']}}</td>
                    <td>{{engineer.consumption_data['undone']}}</td>
                    <td>{{engineer.consumption_data['consumption_mark']}}</td>
                </tr>
                <tr>
                    <td>5</td>
                    <td>Авария электродвигателя (насосы, тягодутьевые машины) в результате несоблюдения норм технического обслуживания</td> 
                    <td>20</td>
                    <td>факт нарушения</td>
                    <td>0 нет нарушений, 1 есть нарушения</td>
                    <td>0</td>
                    <td><input v-model="engineer.audit_results['crash']" @change="compute(key)"></td>
                    <td>{{engineer.audit_results["crash_mark"]}}</td>
                </tr>
                <tr>
                    <td></td>
                    <td><b>Итоговая оценка/результативность</b></td> 
                    <td>100</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>{{engineer.audit_results["result"]}}</td>
                </tr>
            </table>
        </template>
        <table class="table table-bordered table-striped">
            <tr>
                <td colspan="8"><h3 class="text-center mt-10 mb-10">Оценочный лист эффективности деятельности главного инженера {{manager.name}}</h3></td>
            </tr>
            <tr>
            <td>№</td>
            <td>Критерий</td>
            <td>Вес критерия</td>
            <td>Источник</td>
            <td>Ед. Изм.</td>
            <td>План кол-во</td>
            <td>не выполнено, кол-во</td>
            <td>Оценка, %</td>
            </tr>
            <tr>
            <td>1</td>
            <td>Контроль и проведение аудитов согласно плана</td> 
            <td>30</td>
            <td>аналитика</td>
            <td>кол-во аудитов</td>
            <td>{{manager["total_assigned"]}}</td>
            <td>{{manager["total_undone"]}}</td>
            <td>{{manager["kpi1_mark"]}}</td>
            </tr>
            <tr>
            <td>2</td>
            <td>Своевременное и качественное исполнение поставленных задач</td> 
            <td>30</td>
            <td>Докуметооборот/ IQ300</td>
            <td>кол-во задач</td>
            <td><input v-model="manager['tasks']" @change="computeManager()"></td>
            <td><input v-model="manager['undone']" @change="computeManager()"></td>
            <td>{{manager["tasks_mark"]}}</td>
            </tr>
            <tr>
            <td>3</td>
            <td>Контроль за исполнительской дисциплиной ИТР и рем.бригады</td> 
            <td>40</td>
            <td>Оценочные листы</td>
            <td>Средний бал оценочных листов</td>
            <td>100</td>
            <td>{{manager["average"]}}</td>
            <td>{{manager["average_mark"]}}</td>
            </tr>
            <tr>
            <td></td>
            <td><b>Итоговая оценка/результативность</b></td> 
            <td>100</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{manager["result"]}}</td>
            </tr>
        </table>
    </div>
</template>
<script>
export default {
    props: ["district_id"],
    data: function(){
        return {
            date: new Date(),
            data: {},
            engineers: {},
            manager: {}
        }
    },
    mounted(){
        var panel = $('.kpi-control')
        $('.v-toolbar__title').after(panel)

        var date = new Date()
        axios.get("/analytics/kpi/"+this.date).then((response) => {this.engineers = response.data.engineers; this.manager = response.data.manager});
        console.log(this.date)
    },
    computed:{
        month: function(){
            return this.date.toLocaleString("ru", {month: 'long'})
        }
    },
    methods:{
        getData: function(){
            axios.get("/analytics/kpi/"+this.date).then((response) => {this.engineers = response.data.engineers; this.manager = response.data.manager; this.loader = false});
        },
        compute: function(key){
            this.engineers[key].audit_results['report_mark'] = 40*(1-this.engineers[key].audit_results['report'])    

            if(this.engineers[key].audit_results['crash'] == 1){
                this.engineers[key].audit_results['crash_mark'] = 0
            }
            if(this.engineers[key].audit_results['crash'] == 0){
                this.engineers[key].audit_results['crash_mark'] = 20
            } 
            this.engineers[key].audit_results["result"] = this.engineers[key].audit_results['report_mark'] + this.engineers[key].audit_results['kpi1_mark'] + this.engineers[key].audit_results['kpi2_mark'] + this.engineers[key].audit_results['crash_mark']
            this.computeManager();
            this.$forceUpdate();
        },
        computeManager: function(){
            var i = 0
            this.engineers.forEach(element => i += element.audit_results.result);
            this.manager.average = 100 - i/this.engineers.length
            this.manager.tasks_mark = parseInt(30*(this.manager.tasks - this.manager.undone)*1/this.manager.tasks)
            this.manager.average_mark = 40*(100 - this.manager.average)*1/100
            this.manager.result = this.manager.average_mark + this.manager.tasks_mark + this.manager.kpi1_mark
            this.$forceUpdate();
        },
        getExcell: function(){
            axios.post("/analytics/kpi/excell", {
                token:      $("meta[name=csrf-token]").attr('content'),
                engineers:  this.engineers,
                manager:    this.manager
            }).then(()=>window.location = "http://brainsoft.com/Аналитика мониторинга Оценочный лист.xlsx")
        }
    }
}
</script>
<style scoped>
.row div{
    border: 0.5px solid;
    font-size: 1.1em;
    color: black;
    font-weight: 700;
}
.kpi-control *{
    margin: 5px;
}
</style>