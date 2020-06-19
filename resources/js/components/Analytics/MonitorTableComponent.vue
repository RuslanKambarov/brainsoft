<template>
    <div style = "width: 100%" class="">
        <div class="table-wrapper">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <td colspan="12"><h3>Аналитика отклонений температурного режима отапливаемых объектов</h3></td>
                    <td colspan="3">
                        <a :href="'/analytics/monitor/excell/'+district_id">
                            <button class="btn btn-small btn-success m-4 get-excell-button">Excell</button>
                        </a>
                    </td>
                </tr>
                <tr class="table-heading">
                <th>№</th>
                <th colspan="2">Наименование объекта</th>
                <th colspan="2">Фио инженера</th>
                <th>За весь период</th>
                <th>сентябрь</th>
                <th>октябрь</th>
                <th>ноябрь</th>
                <th>декабрь</th>
                <th>январь</th>
                <th>февраль</th>
                <th>март</th>
                <th>апрель</th>
                <th>май</th>
                </tr>                  
            </thead>
            <tbody>
                <tr v-for="(row, index) in data" :key="row.id">
                    <td>{{index+1}}</td>
                    <td colspan="2">{{row.name}}</td>
                    <td colspan="2">{{row.engineer}}</td>
                    <td>{{row.total}}</td>
                    <td @click="showMore('sep', row.id)">{{row.sep}}</td>
                    <td @click="showMore('oct', row.id)">{{row.oct}}</td>
                    <td @click="showMore('nov', row.id)">{{row.nov}}</td>
                    <td @click="showMore('dec', row.id)">{{row.dec}}</td>
                    <td @click="showMore('jan', row.id)">{{row.jan}}</td>
                    <td @click="showMore('feb', row.id)">{{row.feb}}</td>
                    <td @click="showMore('mar', row.id)">{{row.mar}}</td>
                    <td @click="showMore('apr', row.id)">{{row.apr}}</td>
                    <td @click="showMore('may', row.id)">{{row.may}}</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>    
</template>
<script>
export default {
    props: ['district_id'],
    data: function(){
        return {
            data: []
        }
    },
    mounted(){
        axios.get("/analytics/monitor/"+this.district_id).then(response =>  this.data = response.data);
    },
    methods: {
        showMore: function(month, object_id){
            window.location = window.location+"/"+month+"/"+object_id;
        }
    }
}
</script>
<style scoped>
table td{
    width: 20px;
    height: 20px;
    text-align: center;
}
.table-wrapper{
    max-height: 750px;
    overflow-y: scroll;
}
table thead th { 
    position: sticky; 
    top: 0;
    background-color: #737278;
    color: azure; 
}
td button{
    margin: 0 !important;
}
</style>