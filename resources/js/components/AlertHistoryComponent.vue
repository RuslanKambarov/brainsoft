<template>
    <a target="_blank" :href="'/events/device/'+object_id+'/'+formated_start+'/'+formated_end"><button class="btn btn-primary">История</button></a>
</template>

<script>
export default {
    props: ["start", "end", "object_id"],
    data: function(){
        return {
            formated_start: "",
            formated_end: ""
        }
    },
    mounted(){
        this.formated_start = new Date(this.start) 
        this.formated_end = new Date(this.end)
    }
}
</script>