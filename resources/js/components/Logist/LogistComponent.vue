<template>
    <div class="logist">    
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="left-heading">Объект</th>
                    <th class="left-heading">Итого</th>
                    <th v-for="day in data[0].data">{{day.iso}}</th>
                </tr>
            </thead>
            <tr v-for="(item, key) in data" :key="key">
                <th class="left-heading">{{item.name}}</th>
                <td>
                    <div class="day-box">
                        <div class="plan border border-success">
                            <div><h5 class="text-center">План</h5></div>
                            <div v-for="(row, key) in item.total.plan"><b>{{labels[key]}}: {{row}}т.</b></div>
                        </div>
                        <div class="fact border border-danger">
                            <div><h5 class="text-center">Факт</h5></div>
                            <div v-for="(row, key) in item.total.fact"><b>{{labels[key]}}: {{row}}т.</b></div>
                        </div>
                    </div>
                </td>
                <template v-for="day in item.data">
                    <logist-day-component :data="day"></logist-day-component>
                </template>
            </tr>                        
        </table>       
    </div>    
</template>
<script>
  export default {
    props: ["data"],
    data () {
      return {
            labels: {
                1: "Б3 гамма",
                2: "ГЖО",
                3: "ГЖ-5 метровый",
                4: "ксн сорт",
                5: "Шубар. ряд",
                6: "Шубар. сорт"
            },
        }
    },
    mounted(){

    },
    methods:{

    }
  }
</script>    
<style scoped>
td{
    padding: 0;
}
tr{
    border: 2px solid;
}
.logist{
    overflow-x: scroll;
    overflow-y: scroll;
    width: 100%;
    height: calc(100vh - 150px);
}
table tr th{
    position: sticky;
    background-color: #dee2e6;
    top: 0;    
    z-index: 2;
}
th, td{
    border: 1px solid;
}
.left-heading{
    position: sticky;
    left: 0;    
}
.day-box{
    display: flex;
}
.day-box div{
    min-width: 150px;
}
</style>