<template>
    <tbody>
        <tr v-for="user in users" :key="user.id" @click="open(user.id)">
            <td>{{user.id}}</td>
            <td>{{user.name}}</td>
            <td>{{user.email}}</td>
            <td>{{user.role}}</td>
            <!-- <td><v-btn color="success"><v-icon>mdi-eye</v-icon></v-btn></td> -->
	        <!-- <td><v-btn color="success"><v-icon>mdi-pencil</v-icon></v-btn></td> -->
        </tr>
    </tbody>
</template>
<script>
export default {
    props: ["users"],
    data: function(){
        return {
            dialog: false	
        }
    },
    mounted(){

    },
    methods: {
        openDialog: function(user_id){
            this.dialog = !this.dialog
        },
        open: function(user_id){
            window.location = "/users/"+user_id
        }
    }
}
</script>
<style scoped>
button{
    padding: 0 10px;
    margin: 0 10px;
}
tr{
    cursor: pointer;
}
</style>