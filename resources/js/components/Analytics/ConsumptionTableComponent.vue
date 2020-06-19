<template>
    <div style="width: 100%">
        <div class="table-wrapper">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr class="table-heading-1">
                        <th style="z-index: 4" class="side-heading-1" rowspan="2">№</th>
                        <th style="z-index: 4" class="side-heading-2" rowspan="2">ФИО</th>
                        <th style="z-index: 4" class="side-heading-3" rowspan="2">Объект</th>
                        <th colspan="3" v-for="day in days">
                            {{day}}
                        </th>
                        <th colspan="3">Всего</th>
                    </tr>
                    <tr class="table-heading-2">
                        <template v-for="day in days">
                            <th>Приход</th>
                            <th>Аналитика</th>
                            <th>Расход</th>
                        </template>
                        <th>Приход</th>
                        <th>Аналитика</th>
                        <th>Расход</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="(user_data, user_name) in data">
                        <template v-for="(object_data, object_name, index) in user_data">
                            <tr>                                
                                <td v-if="object_name == 'Всего'" colspan="3" class="side-heading-1">Итого по направлению</td>
                                <template v-else>
                                <td class="side-heading-1">{{++index}}</td>
                                <td class="side-heading-2" style="width: 250px">{{user_name}}</td>
                                <td class="side-heading-3" style="width: 250px">{{object_name}}</td>
                                </template>
                                <template v-for="(day_data, day_name) in object_data">
                                    <td>{{day_data.income}}</td>
                                    <td>{{day_data.input}}</td>
                                    <td>{{day_data.consumption}}</td>
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
            loader: false
        }
    },
    computed: {
        month: function(){
            return this.date.toLocaleString("ru", {month: 'long'})
        }
    },    
    mounted(){
        var date = new Date()
        axios.get("/analytics/consumption/analytics/"+this.district_id).then((response) => {this.data = response.data.consumption_analytics; this.days = response.data.period});
    }
}
</script>
<style scoped>
table, tr, td, th{
    font-size: 12px;
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
    background-color: coral;
    position: sticky;
    left: 0;
}
.side-heading-2{
    background-color: coral;
    position: sticky;
    min-width: 40px;
    left: 40px;
}
.side-heading-3{
    background-color: coral;    
    position: sticky;
    min-width: 110px;
    left: 150px;
}
</style>