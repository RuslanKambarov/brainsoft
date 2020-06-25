<template>
    <div class="container">
        <div class="row" v-for="(engineer, key) in engineers" :key="engineer.id">
            <div class="col-12"><h3 class="text-center"> {{engineer.name}}</h3></div>
            <div class="col-1">№</div>
            <div class="col-4" style="max-widdiv: 200px">Критерий</div>
            <div class="col-1">Вес критерия</div>
            <div class="col-1">Источник</div>
            <div class="col-2">Ед. Изм.</div>
            <div class="col-1">План кол-во</div>
            <div class="col-1">не выполнено, кол-во</div>
            <div class="col-1">Оценка, %</div>

            <div class="col-1">1</div>
            <div class="col-4">Контроль проведения влажной уборки в помещении котельной</div> 
            <div class="col-1">10</div>
            <div class="col-1">аналитика</div>
            <div class="col-2">кол-во объектов</div>
            <div class="col-1">{{engineer.audit_results["total_objects"]}}</div>
            <div class="col-1">{{engineer.audit_results["kpi1"]}}</div>
            <div class="col-1">{{engineer.audit_results["kpi1_mark"]}}</div>

            <div class="col-1">2</div>
            <div class="col-4">Ведение сменного журнала котельной. Наличие документации (режимная карта, график смен, телефоны ответственных лиц)</div> 
            <div class="col-1">10</div>
            <div class="col-1">аналитика</div>
            <div class="col-2">кол-во объектов</div>
            <div class="col-1">{{engineer.audit_results["total_objects"]}}</div>
            <div class="col-1">{{engineer.audit_results["kpi2"]}}</div>
            <div class="col-1">{{engineer.audit_results["kpi2_mark"]}}</div>

            <div class="col-1">3</div>
            <div class="col-4">Обеспечение бесперебойного теплоснабжения потребителей в соответствии с утвержденным графиком, безопасную работу оборудования, соблюдение требований правил технической эксплуатации, правил охраны труда и пожарной безопасности</div> 
            <div class="col-1">40</div>
            <div class="col-1">журнал жалоб</div>
            <div class="col-2">кол-во жалоб</div>
            <div class="col-1">1</div>
            <div class="col-1"><input v-model="engineer.audit_results['report']" @change="compute(key)"></div>
            <div class="col-1">{{engineer.audit_results['report_mark']}}</div>

            <div class="col-1">4</div>
            <div class="col-4">Предоставление суточного расхода угля</div> 
            <div class="col-1">20</div>
            <div class="col-1">аналитика</div>
            <div class="col-2">кол-во объектов</div>
            <div class="col-1"></div>
            <div class="col-1"></div>
            <div class="col-1"></div>

            <div class="col-1">5</div>
            <div class="col-4">Авария электродвигателя (насосы, тягодутьевые машины) в результате несоблюдения норм технического обслуживания</div> 
            <div class="col-1">20</div>
            <div class="col-1">факт нарушения</div>
            <div class="col-2">0 нет нарушений, 1 есть нарушения</div>
            <div class="col-1">0</div>
            <div class="col-1"><input v-model="engineer.audit_results['crash']" @change="compute(key)"></div>
            <div class="col-1">{{engineer.audit_results["crash_mark"]}}</div>

            <div class="col-1"></div>
            <div class="col-4"><b>Итоговая оценка/результативность</b></div> 
            <div class="col-1">100</div>
            <div class="col-1"></div>
            <div class="col-2"></div>
            <div class="col-1"></div>
            <div class="col-1"></div>
            <div class="col-1">{{engineer.audit_results["result"]}}</div>
        </div>
        <h3 class="text-center mt-10 mb-10">Оценочный лист эффективности деятельности главного инженера ТОО "КТРК" {{manager.name}}</h3>
        <div class="row">
            <div class="col-1">№</div>
            <div class="col-4" style="max-widdiv: 200px">Критерий</div>
            <div class="col-1">Вес критерия</div>
            <div class="col-1">Источник</div>
            <div class="col-2">Ед. Изм.</div>
            <div class="col-1">План кол-во</div>
            <div class="col-1">не выполнено, кол-во</div>
            <div class="col-1">Оценка, %</div>

            <div class="col-1">1</div>
            <div class="col-4">Контроль и проведение аудитов согласно плана</div> 
            <div class="col-1">30</div>
            <div class="col-1">аналитика</div>
            <div class="col-2">кол-во аудитов</div>
            <div class="col-1">{{manager["total_assigned"]}}</div>
            <div class="col-1">{{manager["total_undone"]}}</div>
            <div class="col-1">{{manager["kpi1_mark"]}}</div>

            <div class="col-1">2</div>
            <div class="col-4">Своевременное и качественное исполнение поставленных задач</div> 
            <div class="col-1">30</div>
            <div class="col-1">Докуметооборот/ IQ300</div>
            <div class="col-2">кол-во задач</div>
            <div class="col-1"><input v-model="manager['tasks']" @change="computeManager()"></div>
            <div class="col-1"><input v-model="manager['undone']" @change="computeManager()"></div>
            <div class="col-1">{{manager["tasks_mark"]}}</div>

            <div class="col-1">3</div>
            <div class="col-4">Контроль за исполнительской дисциплиной ИТР и рем.бригады</div> 
            <div class="col-1">40</div>
            <div class="col-1">Оценочные листы</div>
            <div class="col-2">Средний бал оценочных листов</div>
            <div class="col-1">100</div>
            <div class="col-1">{{manager["average"]}}</div>
            <div class="col-1">{{manager["average_mark"]}}</div>

            <div class="col-1"></div>
            <div class="col-4"><b>Итоговая оценка/результативность</b></div> 
            <div class="col-1">100</div>
            <div class="col-1"></div>
            <div class="col-2"></div>
            <div class="col-1"></div>
            <div class="col-1"></div>
            <div class="col-1">{{manager["result"]}}</div>
    
            <button @click="getExcell()" class="btn btn-small btn-success get-excell-button">EXCELL</button>
    
        </div>
    </div>
</template>
<script>
export default {
    props: ["engineers", "manager"],
    data: function(){
        return {

        }
    },
    mounted(){
        
    },
    computed:{
    },
    methods:{
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
</style>