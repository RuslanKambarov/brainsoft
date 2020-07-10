<template>
    <v-col>
     <div class="row">   
    <h3>Необходимые параметры</h3>
    <v-spacer></v-spacer>
    <v-btn color="grey lighten-2" small @click="modal=!modal"><v-icon>mdi-pencil</v-icon></v-btn>
    </div>
    <table class="table table bordered">
        <thead>
        </thead>
        <tr>
            <td>Необходимая температура</td>
            <td>{{required_t}}</td>
        </tr>
        <tr>
            <td>Необходимое давление</td>
            <td>{{required_p}}</td>
        </tr>
    </table>
    <v-dialog v-model="modal" scrollable persistent max-width="300">
    <v-card color="#f5f0e7" raised>
        <v-card-title class="headline">Изменить значения</v-card-title>
        <v-card-text>
            <hr>
            <v-text-field outlined v-model="required_t" label="Необходимая температура"></v-text-field>
            <v-text-field outlined v-model="required_p" label="Необходимое давление"></v-text-field>
        </v-card-text>
        <v-card-actions>
            <v-btn color="green darken-1" text @click="modal=!modal">Отменить</v-btn>
            <v-spacer></v-spacer>
            <v-btn  color="green darken-1" text @click="saveParams()">Сохранить</v-btn>
        </v-card-actions>
    </v-card>
    </v-dialog>
    </v-col>
</template>
<script>
export default {
    props: ['device_id', 'required_t', 'required_p'],
    data: function(){
        return{
            modal: false
        }
    },
    methods: {
        saveParams: function(){
            axios.post("/device/"+this.device_id+"/update", {
                token: $('meta[name="csrf-token"]').attr('content'),
                parameters: {
                    required_t:     this.required_t,
                    required_p:     this.required_p
                }     
            }).then((response)=> {
                this.modal = !this.modal
                $(".v-content__wrap").prepend("<div class='alert alert-success'>"+response.data+"</div>");
            })
        }

    }
}
</script>