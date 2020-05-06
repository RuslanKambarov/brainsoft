<template>
    <tbody>        
        <tr v-for="item in parameters" :key="item.id">
            <td>{{item.name}}</td>
            <td>{{item.value}}</td>
            <td>
                <v-btn color="success" @click.stop="dialog = true; fetch(item)"><v-icon>mdi-tag-plus</v-icon></v-btn>
            </td>
        </tr>
        <v-dialog v-model="dialog" max-width="500">
        <v-card>
          <v-card-title class="headline">Создать новое событие для "{{name}}"</v-card-title>
  
          <v-card-text>
            Результат: {{expression}}
            <v-simple-table>
              <template v-slot:default>
                <tbody>
                  <tr>
                    <td>Параметр</td>
                    <td>{{name}}</td>
                  </tr>
                  <tr>
                    <td>Знак</td>
                    <td><v-text-field prepend label="Знак" v-model="sign"></v-text-field></td>
                  </tr>
                  <tr>
                    <td>Значение</td>
                    <td><v-text-field prepend label="Значение" v-model="value"></v-text-field></td>
                  </tr>
                </tbody>
                <v-text-field prepend label="Сообщение" v-model="message"></v-text-field>
                <v-text-field prepend label="Время задержки" v-model="condition_time"></v-text-field>
              </template>
            </v-simple-table>
          </v-card-text>
  
          <v-card-actions>
            <v-spacer></v-spacer>

            <v-btn color="green darken-1" text @click="dialog = false">
              Отменить
            </v-btn>
  
            <v-btn color="green darken-1" text  @click="sendEvent()">
              Сохранить
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </tbody>
</template>
<script>
export default {
    props: {
        device_id: Number,
        parameters: Array
    },
    data: function(){
        return {
            dialog: false,
            name: String,
            message: "",
            condition_time: 0,
            code: String,
            sign: "=",
            value: 0
        }
    },
    computed: {
      expression: function(){
        return "'"+this.code+"'" + this.sign + this.value
      }
    },
    methods:{
      fetch(param){
        console.log(param)
        this.name = param.name
        this.code = param.code
      },
      sendEvent(){
        axios.get("/set-event", { 
          params: {
            expression: this.expression,
            device_id: this.device_id,
            message: this.message,
            condition_time: this.condition_time
         }
        }).then((response) => { console.log(response.data)})
      }
    }
}
</script>