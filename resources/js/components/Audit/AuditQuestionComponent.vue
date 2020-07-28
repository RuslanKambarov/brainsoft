<template>
    <table class="table table-bordered table-striped">
        <thead>
            <th>Вопрос</th>
            <th>Снимок</th>
            <th>Действия</th>
        </thead>
        <tbody>
            <template v-for="question in questions">
                <tr :key="question.id">
                    <td>
                        <input class="form-control" type="text" v-model="question.question" @change="updateQuestion(question.id, question.question)">
                    </td>
                    <td v-if="question.photo">Требуется</td>
                    <td v-else>Не требуется</td>
                    <td>
                        <v-btn color="error" @click="showAlert(question.id)"><v-icon>mdi-delete</v-icon></v-btn>
                    </td>
                </tr>
            </template>
        </tbody>
        <v-dialog v-model="dialog" max-width="300">
            <v-card>
            <v-card-title>Подтвердите удаление</v-card-title>
            <v-card-actions>
                    <v-btn color="green darken-1" text @click="dialog=!dialog">Отменить</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn  color="red darken-1" @click="deleteQuestion()" text>Удалить</v-btn>
            </v-card-actions>
            </v-card>
        </v-dialog>
        <v-dialog v-model="response" max-width="300">
            <div class="alert alert-success">
                {{message}}
            </div>
        </v-dialog>
    </table>
</template>
<script>
export default {
    props: ['questions'],
    data: function(){
        return {
            dialog: false,
            id: 0,
            response: false,
            message: "" 
        }
    },
    mounted(){
        
    },
    methods:{
        showAlert: function(question_id){
            this.dialog = true
            this.id = question_id
        },
        deleteQuestion: function(){
            axios.get("/audit/types/removequestion/"+this.id)
            .then((response) => {
                this.dialog = false
                this.response = true
                this.message = response.data
                this.questions = this.questions.filter(question => question.id != this.id)
                setTimeout(() => {
                    this.response = false                    
                }, 500)
            })
        },
        updateQuestion: function(question_id, question_text){
            axios.get("/audit/types/updatequestion/"+question_id, {
                params: {
                    question_id: question_id, 
                    question_text: question_text
                }
            }).then((response) => {
                this.response = true
                this.message = response.data                
                setTimeout(() => {
                    this.response = false                    
                }, 500)
            })
        }
    }
}
</script>