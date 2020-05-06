<template>
    <tr>
        <th>Температура наружного воздуха</th>
        <th>Температура прямой сетевой воды</th>
        <th>Температура обратной сетевой воды</th>
        <th>
            <v-btn small color="success" @click="dialog=!dialog"><v-icon>mdi-plus</v-icon></v-btn>
        </th>
        <v-dialog v-model="dialog" max-width="300">
            <v-card>
            <v-card-title>Добавить запись</v-card-title>
            <v-card-text>
                <hr>
                <v-text-field outlined v-model="card.outside_t" label="Наружняя температура"></v-text-field>
                <v-text-field outlined v-model="card.direct_t" label="Температура подачи"></v-text-field>
                <v-text-field outlined v-model="card.back_t" label="Температура обратки"></v-text-field>
            </v-card-text>
            <v-card-actions>
                    <v-btn color="green darken-1" text @click="dialog=!dialog">Отменить</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn  color="green darken-1" @click="create()" text>Сохранить</v-btn>
            </v-card-actions>
            </v-card>
        </v-dialog>
    </tr>
</template>
<script>
export default {
    props: ["id"],
    data: function(){
        return {
            dialog: false,
            card: {}
        } 
    },
    methods:{
        create: function(){
            this.card.id = this.id
            console.log(this.card)
            axios.post("/tempcard/create", {
                token: $('meta[name="csrf-token"]').attr('content'),
                parameters: this.card
            }).then((response)=> {
                this.dialog = !this.dialog
                $(".container--fluid").prepend("<div class='alert alert-success'>"+response.data+"</div>");
            })
        }
    }
}
</script>