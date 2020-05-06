<template>
        <tr>
            <td>{{card.outside_t}}</td>
            <td>{{card.direct_t}}</td>
            <td>{{card.back_t}}</td>
            <td>
                <v-btn small color="primary" @click="editDialog = !editDialog"><v-icon>mdi-pencil</v-icon></v-btn> 
                <v-btn small color="error" @click="rmDialog = !rmDialog"><v-icon>mdi-delete</v-icon></v-btn>
            </td>
            <v-dialog v-model="editDialog" scrollable persistent max-width="300">
                <v-card color="#f5f0e7" raised>
                    <v-card-title class="headline">Изменить значения</v-card-title>
                    <v-card-text>
                        <hr>
                        <v-text-field outlined v-model="card.outside_t" label="Наружняя температура"></v-text-field>
                        <v-text-field outlined v-model="card.direct_t" label="Температура подачи"></v-text-field>
                        <v-text-field outlined v-model="card.back_t" label="Температура обратки"></v-text-field>
                    </v-card-text>
                    <v-card-actions>
                        <v-btn color="green darken-1" text @click="editDialog=!editDialog">Отменить</v-btn>
                        <v-spacer></v-spacer>
                        <v-btn  color="green darken-1" text @click="save()">Сохранить</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
            <v-dialog v-model="rmDialog" max-width="300">
                <v-card>
                <v-card-title>Подтвердите удаление</v-card-title>
                <v-card-actions>
                        <v-btn color="green darken-1" text @click="rmDialog=!rmDialog">Отменить</v-btn>
                        <v-spacer></v-spacer>
                        <v-btn  color="green darken-1" @click="remove()" text>Удалить</v-btn>
                </v-card-actions>
                </v-card>
            </v-dialog>
        </tr>
</template>
<script>
export default {
    props: ['card'],
    data: function(){
        return {
            editDialog: false,
            rmDialog: false
        }
    },
    mounted(){

    },
    methods:{
        save: function(){
            axios.post("/tempcard/update/"+this.card.id, {
                token: $('meta[name="csrf-token"]').attr('content'),
                parameters: this.card                
            }).then((response)=> {
                this.editDialog = !this.editDialog
                $(".container--fluid").prepend("<div class='alert alert-success'>"+response.data+"</div>");
            })
        },
        remove: function(){
            axios.get("/tempcard/remove/"+this.card.id, {

            }).then((response)=> {
                this.rmDialog = !this.rmDialog
                $(".container--fluid").prepend("<div class='alert alert-success'>"+response.data+"</div>");
            })
        }
    }
}
</script>