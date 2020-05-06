<template>
<v-col cols="12">
    <v-row>
        <v-col cols="12">
            <h4>Аналитика проведенных аудитов текущий месяц</h4>
            <table class="table table-bordered">
                <thead>
                    <th>Аудит</th>
                    <th>Пользователь</th>
                    <th>Количество аудитов проведено</th>
                    <th>По плану аудитов</th>
                    <th>Дата последнего</th>
                    <th>Аудиты пользователя</th>
		</thead>
                <template v-for="(audit, auditname) in audits">
                    <tr v-for="(auditarray, username, index) in audit">
                        <td :rowspan="audit.length">{{auditname}}</td>
                        <td>{{username}}</td>
                        <td>{{auditarray.length}}</td>
                        <td>{{auditarray[0].assigned}}</td>
                        <td>{{auditarray[auditarray.length - 1].created_at}}</td>
			<td><a :href="'/audit/user/'+auditarray[0].auditor_id+'/analytics'"><button class="btn btn-primary">Просмотреть аудиты</button></a></td>
                    </tr>
                </template>
            </table>
        </v-col>
        <v-col cols="6">
            <a :href="'/audit/device/'+device_id+'/analytics/detail'"><v-btn>Подробнее</v-btn></a>
        </v-col>
        <v-col cols="6">

        </v-col>
    </v-row>
</v-col>    
</template>
<script>
export default {
    props: ["device_id"],
    data: function(){
        return {
            device: {},
            audits: []
        }
    },
    mounted(){
        axios.get("/audit/device/"+this.device_id+"/analytics").then(response => {
            this.device = response.data.device
            this.audits = response.data.audits
            console.log(this.audits)
        })
    },
    methods: {
        compare: function($audits1, $audits2){
            console.log($audits1, $audits2)
        }
    }
}
</script>
<style scoped>

</style>