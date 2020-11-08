<template>
    <td>
        <div class="day-box">
            <div class="plan border border-success">
                <div><h5 class="text-center">План</h5></div>
                <div class="add-button">
                    <button class="form-control add-button" @click="invoke_dial('plan', 'add', null)">
                        <v-icon>mdi-plus</v-icon>
                    </button>
                </div>
                <div v-for="plan in data.plan" class="data-element">                    
                    <div class="">{{labels[plan.label] + ': '}}</div>
                    <div class="">{{plan.amount + 'т.'}}</div>
                    <button class="data-control edit-data" @click="invoke_dial('plan', 'update', plan)">
                        <v-icon>mdi-pencil</v-icon>
                    </button>
                    <button class="delete-data" @click="invoke_dial('plan', 'remove', plan)">
                        <v-icon>mdi-delete</v-icon>
                    </button>
                </div>
            </div>
            <div class="fact border border-danger">
                <div><h5 class="text-center">Факт</h5></div>
                <div class="add-button">
                    <button class="form-control add-button" @click="invoke_dial('fact', 'add')">
                        <v-icon>mdi-plus</v-icon>
                    </button>
                </div>
                <div v-for="rec in data.data" class="data-element">                    
                    <div class="">{{labels[rec.label] + ': '}}</div>
                    <div class="">{{rec.amount + 'т.'}}</div>
                    <button class="data-control edit-data" @click="invoke_dial('fact', 'update', rec)">
                        <v-icon>mdi-pencil</v-icon>
                    </button>
                    <button class="delete-data" @click="invoke_dial('fact', 'remove', rec)">
                        <v-icon>mdi-delete</v-icon>
                    </button>
                </div>
            </div>
            <v-dialog max-width="300" v-model="dial.state">
                <v-card>
                    <div class="modal-head">
                        <v-card-title v-if="dial.action == 'remove'">
                            Удалить запись?
                        </v-card-title>
                        
                        <v-card-title v-if="dial.action == 'update'">
                            Редактровать запись
                        </v-card-title>
                        
                        <v-card-title v-if="dial.action == 'add'">
                            Добавить запись
                        </v-card-title>
                        
                        <div :class="'alert alert-'+dial.message.type">
                            {{dial.message.text}}
                        </div>

                        <v-card-text v-if="dial.load">
                            <div class="d-flex justify-content-center">
                                <div class="spinner-border" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                        </v-card-text>
                    </div>
                    <v-card-text v-if="dial.action != 'remove'">
                        <hr>
                        <label>Марка угля</label>
                        <select outlined v-model="dial.subject.label" class="form-control mb-5" label="Марка угля">
                            <option v-for="(label, key) in labels" :value="key">{{label}}</option>
                        </select>
                        <label>Количество в тоннах</label>
                        <input v-model="dial.subject.amount" class="form-control">
                        
                        <template v-if="isMix">

                            <div v-for="(item, key, index) in dial.subject.mix" :key="key">
                                <hr>
                                <label>Марка угля {{index}}</label>
                                <select outlined v-model="item.label" class="form-control mb-5" label="Марка угля">
                                    <option v-for="(label, key) in labels" :value="key">{{label}}</option>
                                </select>
                                <label>Количество в процентах {{index}}</label>
                                <input v-model="item.amount" class="form-control">
                            </div>

                            <button 
                                @click="dial.subject.mix.push({label: '', amount: ''})" 
                                class="form-control btn btn-primary"
                            >Добавить</button>                        
                        
                        </template>

                            
                    </v-card-text>
                    
                    <v-card-actions>
                        <v-btn color="blue darken-1" text @click="dial.state=false; dial.subject={}">Отменить</v-btn>
                        <v-spacer></v-spacer>
                        <v-btn  v-if="dial.action == 'remove'" color="red darken-1" @click="remove()" text>Удалить</v-btn>                    
                        <v-btn  v-if="dial.action == 'update'" color="green darken-1" @click="update()" text>Сохранить</v-btn>
                        <v-btn  v-if="dial.action == 'add'" color="green darken-1" @click="send()" text>Сохранить</v-btn>
                    </v-card-actions>

                </v-card>
            </v-dialog>
            
        </div>
    </td>
</template>
<script>
export default {
    props: ["data"],
    data: function(){
        return {
            dial: {
                state: false,
                type: "",
                action: "",
                load: false,
                message:{
                    type: "",
                    text: ""
                },
                subject: {
                    label: "",
                    amount: "",
                    isMix: false,
                    mix: []
                }
            },
            labels: {
                1: "Б3 гамма",
                2: "ГЖО",
                3: "ГЖ-5 метровый",
                4: "ксн сорт",
                5: "Шубар. ряд",
                6: "Шубар. сорт",
                7: "Микс"
            },        
        }
    },
    mounted(){
        
    },
    computed:{
        isMix: function(){
            if(this.dial.subject.label == 7){
                return true
            }else{
                false
            }
        }
    },
    methods:{
        invoke_dial: function(type, action, subject = null){

            this.dial.type = type
            this.dial.action = action
            if(subject){
                this.dial.subject = subject
            }
            this.dial.state = true
        },
        send: function(){            
            var send_data = {
                object_id: this.data.object_id,
                label: this.dial.subject.label,
                amount: this.dial.subject.amount,
                date: this.data.db
            }

            if(this.isMix){
                if(_.sumBy(this.dial.subject.mix, function(item){ return +item.amount }) != 100){
                    this.dial.message.type = "warning"
                    this.dial.message.text = "Сумма процентов должна быть 100"
                    return
                }else{
                    send_data.isMix = true
                    send_data.mix = this.dial.subject.mix
                }
            }
            this.dial.load = true
            axios.post("/consumption/"+this.dial.type+"/save", send_data).then((response) => {
                this.message = response.data;
                this.dial.message.type = response.data.type
                this.dial.message.text = response.data.text 
                this.dial.load = true                                
                var new_record = {
                    object_id: this.data.object_id,
                    label: this.dial.subject.label,
                    amount: this.dial.subject.amount,
                    date: this.data.db,
                    record_id: response.data.record
                }
                if(this.dial.type == 'fact'){
                    this.data.data.push(new_record)
                }
                if(this.dial.type == 'plan'){
                    this.data.plan.push(new_record)
                } 
                this.dial.state = false
                this.dial.load = false
                this.dial.message = {
                    text: "",
                    type: ""
                }
                this.dial.subject = {
                    label: "",
                    amount: "",
                    isMix: false,
                    mix: []
                }               
            })
        },
        remove: function(){
            this.dial.load = true
            axios.get("/consumption/"+this.dial.type+"/delete/" + this.dial.subject.record_id).then((response) => {
                this.message = response.data;
                this.dial.message.type = response.data.type
                this.dial.message.text = response.data.text                                 
                if(this.dial.type == 'fact'){
                    var index = this.data.data.indexOf(this.dial.subject)
                    this.data.data.splice(index, 1)
                }
                if(this.dial.type == 'plan'){                    
                    var index = this.data.plan.indexOf(this.dial.subject)
                    delete this.data.plan.splice(index, 1)
                }

                this.dial.state = false
                this.dial.load = false
                this.dial.message = {
                    text: "",
                    type: ""
                }
                this.dial.subject = {
                    label: "",
                    amount: "",
                    isMix: false,
                    mix: []                    
                }
            })
        },
        update: function(){
            this.dial.load = true
            axios.post("/consumption/"+this.dial.type+"/update/" + this.dial.subject.record_id, {
                label: this.dial.subject.label,
                amount: this.dial.subject.amount,
            }).then((response) => {
                this.message = response.data;                                 
                this.dial.message.type = response.data.type
                this.dial.message.text = response.data.text
                if(this.dial.type == 'fact'){
                    var index = this.data.data.indexOf(this.dial.subject)
                    this.data.data[index].label = this.dial.subject.label
                    this.data.data[index].amount = this.dial.subject.amount
                }
                if(this.dial.type == 'plan'){
                    var index = this.data.plan.indexOf(this.dial.subject)
                    this.data.plan[index].label = this.dial.subject.label
                    this.data.plan[index].amount = this.dial.subject.amount
                }

                this.dial.message.type = response.data.type
                this.dial.message.text = response.data.text                  
                this.dial.state = false
                this.dial.load = false
                this.dial.subject = {
                    label: "",
                    amount: "",
                    isMix: false,
                    mix: []                    
                }                
            })
        },
        validate(data){
            for (var prop in data){
                if(data[prop] == "")  {
                    this.dial.message.type = "warning"
                    this.dial.message.text = "Заполните все поля"
                    return false
                }
            }
            return true
        }
    }
}
</script>
<style scoped>
.add-data, .data-element{
    display: flex;
}
.add-button{
    min-width: 150px;
    height: 1.3rem;
    margin: 0;
    padding: 0;
    line-height: 0;
    background-color: #afddaf;
}
.data-control{
    margin-left: auto;
}
.edit-data{
    background-color:#7d96cc;
}
.delete-data{
    background-color:#c55e5e;
}
button{
    border: 0.5px solid;
}
.day-box{
    display: flex;
}
.modal-head{
    position: sticky;
    top: 0;
    background-color: gainsboro;
}
</style>