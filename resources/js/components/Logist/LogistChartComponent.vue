<template>
<div class="container">
    <canvas id="objectCanvas" width="400" height="200"></canvas>    
</div>
</template>
<script>
import { Chart } from "chart.js";
export default {
    props: ['plan_data', 'fact_data'],
    mounted(){
        var ctx = document.getElementById('objectCanvas');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: Object.keys(this.plan_data),
                datasets: [{
                    label: "План отгрузки (тонн)",
                    data: Object.values(this.plan_data),
                    borderColor: [
                        'rgba(11, 95, 112, 1)'
                    ],
                    borderWidth: 1
                },
                {
                    label: "Фактически отгружено (тонн)",
                    data: Object.values(this.fact_data),
                    borderColor: [
                        'rgba(15, 109, 12, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            stepSize: 30
                        }
                    }]
                }
            }
        });
    }    
}
</script>
<style scoped>

</style>