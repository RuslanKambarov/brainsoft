<template>
    <div>
        <v-btn color="success" @click="attachObject()" v-if="user_role != 3">Прикрепить объект<v-icon>mdi-plus</v-icon></v-btn>
        <v-btn color="teal lighten-3" @click="dialog1 = true">Изменить роль<v-icon>mdi-pencil</v-icon></v-btn>
        <a href="/users"><v-btn color="primary">Назад</v-btn></a>
        <v-dialog v-model="dialog1" scrollable persistent max-width="500">
            <v-card color="" raised>
                <v-card-title class="headline">Изменить роль пользователя</v-card-title>
                <v-card-text>
                    <div class="card-body" v-for="role in roles" :key = "role.id">
                        <input  type = "checkbox" v-model="user_role" :value = "role.id">
                        <label>{{role.name}}</label>
                    </div>
                </v-card-text>
                <v-card-actions>
                    <v-btn color="success" @click="changeRole()">Сохранить</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn color="warning" @click="dialog1=false">Отмена</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <v-dialog v-model="dialog2" scrollable persistent max-width="500">
            <v-card color="#f5fdef" raised>
                <v-card-title class="headline">Прикрепить пользователю</v-card-title>
                <v-card-text>
                    <div v-if="list_loaded == true">
                    <table class="table table-striped">
                        <th>Прикрепить район пользователю</th>
                        <tr v-for="object in objects.districts" :key="object.id">
                            <td>{{object.name}}</td>
                            <td>
                                <input type="checkbox" v-model="selected_districts" :value="object.id">
                            </td>
                        </tr>
                    </table>

                    <table class="table table-striped">
                        <th>Прикрепить устройство пользователю</th>
                        <tr v-for="object in objects.devices" :key="object.id">
                            <td>{{object.name}}</td>
                            <td>
                                <input type="checkbox" v-model="selected_devices" :value="object.id">
                            </td>
                        </tr>
                    </table>

                    </div>
                    <div v-else>
                        <v-progress-circular
                        :width="3"
                        color="green"
                        indeterminate
                        ></v-progress-circular>
                        <b>loading</b>
                    </div>
                </v-card-text>
                <v-card-actions>
                    <v-btn color="success" @click="saveAttachedObjects()">Сохранить</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn color="warning" @click="dialog2=false">Отмена</v-btn>                    
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>
<script>
export default {
    data: function(){
        return {
            dialog1: false,
            dialog2: false,
            objects: {},
            list_loaded: false,
            selected_districts: [],
            selected_devices: [],
        }
    },
    props: ["user_id", "user_role", "roles"],
    mounted(){
        console.log('user_role' + this.user_role)
        console.log('roles' + this.roles)
        console.log('user_id' + this.user_id)
    },
    methods:{

        changeRole: function(){
            axios.post("/users/" + this.user_id + "/changerole", {
                token: $('meta[name="csrf-token"]').attr('content'),
                roles: this.user_role
            }).then(response => {
                location.reload()
            })       
        },
        attachObject: function(){
            this.dialog2 = true
            axios.get("/users/" + this.user_id + "/notattached").then(response => {
                console.log(response.data)
                this.objects = response.data
                this.list_loaded = true
            })
        },
        saveAttachedObjects: function(){
            axios.post("/users/" + this.user_id + "/attach", {
                token: $('meta[name="csrf-token"]').attr('content'),
                districts: this.selected_districts,
                devices: this.selected_devices
            }).then(response => {
                location.reload()
            })
        }
    }
}
</script>