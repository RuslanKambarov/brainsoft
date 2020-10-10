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
                    <v-card-title v-if="dial.action == 'remove'">
                        Удалить запись?
                    </v-card-title>
                    
                    <v-card-title v-if="dial.action == 'update'">
                        Редактровать запись
                    </v-card-title>
                    
                    <v-card-title v-if="dial.action == 'add'">
                        Добавить запись
                    </v-card-title>
                    
                    <v-card-text v-if="dial.load">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </v-card-text>

                    <v-card-text v-if="dial.action != 'remove'">
                        <hr>
                        <label>Марка угля</label>
                        <select outlined v-model="dial.subject.label" class="form-control mb-5" label="Марка угля">
                            <option v-for="(label, key) in labels" :value="key">{{label}}</option>
                        </select>
                        <label>Количество в тоннах</label>
                        <input v-model="dial.subject.amount" class="form-control">
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
                subject: {
                    label: "",
                    amount: ""
                }
            },
            labels: {
                1: "Марка 1",
                2: "Марка 2",
                3: "Марка 3"
            },
            rec: {},
            plan: {},
            selected: {},
            label: "",
            amount: null,
            message: {},
        }
    },
    mounted(){
        
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
            axios.post("/consumption/"+this.dial.type+"/save", {
                object_id: this.data.owen_id,
                label: this.dial.subject.label,
                amount: this.dial.subject.amount,
                date: this.data.db
            }).then((response) => {
                this.message = response.data;                                 
                var new_record = {
                    object_id: this.data.owen_id,
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
                this.dial.subject = {}                
            })
        },
        remove: function(){
            console.log(this.dial.subject.record_id)
            axios.get("/consumption/"+this.dial.type+"/delete/" + this.dial.subject.record_id).then((response) => {
                this.message = response.data;
                console.log(this.data)
                if(this.dial.type == 'fact'){
                    var index = this.data.data.indexOf(this.dial.subject)
                    this.data.data.splice(index, 1)
                }
                if(this.dial.type == 'plan'){                    
                    var index = this.data.plan.indexOf(this.dial.subject)
                    delete this.data.plan.splice(index, 1)
                }
                console.log(index)
                console.log(this.data)
                this.dial.state = false
                this.dial.subject = {}
            })
        },
        update: function(){
            axios.post("/consumption/"+this.dial.type+"/update/" + this.dial.subject.record_id, {
                label: this.dial.subject.label,
                amount: this.dial.subject.amount,
            }).then((response) => {
                this.message = response.data;                                 
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
                this.dial.state = false
                this.dial.subject = {}                
            })
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
</style>