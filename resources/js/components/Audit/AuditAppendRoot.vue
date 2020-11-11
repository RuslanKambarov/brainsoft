<template>
    <div class="card-body">
        <h3 class="text-center">{{district.name}}</h3>
        <table class="table table-dark table-bordered table-striped">
            <thead>
                <tr>
                    <th>Объект</th>
                    <th v-for="audit in audits" style="max-width: 50px">{{audit.name}}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="object in district.devices">
                    <td>{{object.name}}</td>
                    <td v-for="audit in audits">
                        <input @change="attachAudits(object.id, object.audit_ids)" v-model="object.audit_ids" :value="audit.id" type="checkbox">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
export default {
    props: ["district", "audits"],
    data: function(){
        return {
            token : $('meta[name="csrf-token"]').attr("content")
        }
    },
    mounted(){

    },
    methods:{
        attachAudits: function(object_id, audit_ids){
            axios.post("/audit/control/attach", {
                token: this.token,
                object: object_id,
                audits: audit_ids
            }).then(response => {
                console.log(response.data)
            })
        }
    }
}
</script>