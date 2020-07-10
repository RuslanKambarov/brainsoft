<template>
    <div class="row district-wrapper">
        <div class="col-4"><input class="form-control" type='text' v-model="district.name"></div>
        <div class="col-2"><input class="form-control" type='text' v-model="district.owen_id"></div>
        <div class="col-2"><input class="form-control" type='text' v-model="district.parent_id"></div>
        <div class="col-2"><button class="btn btn-primary btn-sm" @click="showObjects = !showObjects">Показать объекты</button></div>
        <div class="col-2"><button class="btn btn-primary btn-sm" @click="saveParams()">Сохранить</button></div>
        <div class="col-12 ml-2" v-if="showObjects">
            <div class="row pl-2">
                <div class="col-6"><b>Объекты района: {{district.name}}</b></div>
                <div class="col-6"><button class="btn btn-success" @click="dialog=!dialog">Добавить объекты</button></div>
            </div>

            <div class="devices-wrapper">
                <div class="row"> 
                    <div class="col-3"><b>Название</b></div>
                    <div class="col-1"><b>Контр</b></div>
                    <div class="col-1"><b>Аббревиатура</b></div>
                    <div class="col-1"><b>OWEN ID</b></div>
                    <div class="col-2"><b>DISTRICT ID</b></div>
                    <div class="col-1"><b>Температура</b></div>
                    <div class="col-1"><b>Давление</b></div>
                </div>
                <device-settings-component v-for="device in district.devices" :device="device" :key="device.id"></device-settings-component>
            </div>
        </div>
        <!-- Dialog window -->
        <v-dialog v-model="dialog" max-width="900">
            <v-card>
            <v-card-title>Добавить объект</v-card-title>
            <v-card-text>
            <v-row v-for="(device, index) in add_devices" :key="index">
                
                <hr>
                <v-col cols='4'>
                    <v-text-field outlined v-model="device.name" label="Название"></v-text-field>
                </v-col>
                <v-col cols='4'>
                    <v-text-field outlined v-model="device.identifier" label="Идентификатор"></v-text-field>
                </v-col>
            
            </v-row>
            <v-row>
                <v-col cols='12'><v-btn color='success' @click='addFields()'>+</v-btn></v-col>
            </v-row>

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
    props: ['district'],
    data: function(){
        return {
            dialog: false,
            showObjects: false,
            add_devices: [
                {
                    name: '',
                    identifier: 0,
                    district_id: this.district.owen_id
                }
            ]
        }
    },
    methods:{
        create: function(){
            axios.post("/settings/create", {
                token: $('meta[name="csrf-token"]').attr('content'),
        		target: "device",
                devices: this.add_devices
            }).then(Response => {
                console.log(Response.data)
            })
        },
        addFields: function(){
            this.add_devices.push(                
                {
                    name: '',
                    identifier: 0,
                    district_id: this.district.owen_id
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