<template>
    <div class="row p-2">
        <div class="col-4"><b>Название</b></div>
        <div class="col-2"><b>OWEN ID</b></div>
        <div class="col-2"><b>OWEN PARENT ID</b></div>
        <div class="col-2"><button class="btn btn-success" @click="dialog=!dialog"><b>Добавить район</b></button></div>

        <district-settings-component v-for="district in tree" :district="district" :key="district.id"></district-settings-component>
        <v-dialog v-model="dialog" max-width="300">
            <v-card>
            <v-card-title>Добавить район</v-card-title>
            <v-card-text>
                <hr>
                <v-text-field outlined v-model="district.name" label="Название"></v-text-field>
                <v-text-field outlined v-model="district.parent_id" label="OWEN PARENT"></v-text-field>
                <v-text-field outlined v-model="district.owen_id" label="OWEN ID"></v-text-field>
            </v-card-text>
            <v-card-actions>
                    <v-btn color="green darken-1" text @click="dialog=!dialog">Отменить</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn  color="green darken-1" @click="create()" text>Сохранить</v-btn>
            </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>    
<script>
export default {
    props: ['tree'],
    data: function(){
        return {
            dialog: false,
            district: {
                name:   "",
                parent_id: 0,
                owen_id: 0
            }
        }
    },
    methods:{
        create: function(){
            axios.post("/settings/create", {
                token:  $('meta[name="csrf-token"]').attr('content'),
                target: "district",
                name:   this.district.name,
                owen_id: this.district.owen_id,
                parent: this.district.parent_id
            }).then(response => { 
                this.dialog = !this.dialog
                this.tree.push(this.district)
            })
        }
    }
}
</script>
<style scoped>
input{
    width: 100%;
}
</style>