<template>
    <div>
        <div class = "card">
            <div class="card-body">
                <h2>Температура объекта</h2>
            </div>
        </div>
        <canvas id="objectCanvas" width="400" height="200"></canvas>
        <hr>
        <div class = "card">
            <div class="card-body">
                <h2>Температура наружнего воздуха</h2>
            </div>
        </div>        
        <canvas id="outsideCanvas" width="400" height="200"></canvas>
        <hr>
        <div class = "card">
            <div class="card-body">
                <h2>Температура подачи</h2>
            </div>
        </div>           
        <canvas id="directCanvas" width="400" height="200"></canvas>
        <hr>
        <div class = "card">
            <div class="card-body">
                <h2>Температура обратки</h2>
            </div>
        </div>        
        <canvas id="backCanvas" width="400" height="200"></canvas>
        <hr>
        <canvas id="pressureCanvas" width="400" height="200"></canvas>
    </div>
</template>
<script>
import { Chart } from "chart.js";
export default {
    props: ['time_marks', 'object_t', 'outside_t', 'direct_t', 'back_t'],
    data: function(){
        return {
        }
    },
    mounted(){
        var ctx = document.getElementById('objectCanvas');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.time_marks,
                datasets: [{
                    label: "Температура объекта",
                    data: this.object_t,
                    backgroundColor: [
                        'rgba(55, 99, 132, 0.5)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
        var ctx = document.getElementById('outsideCanvas');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.time_marks,
                datasets: [{
                    label: "Температура наружнего воздуха",
                    data: this.outside_t,
                    backgroundColor: [
                        'rgba(55, 19, 132, 0.5)'
                    ],
                    borderColor: [
                        'rgba(25, 109, 12, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
        var ctx = document.getElementById('directCanvas');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.time_marks,
                datasets: [{
                    label: "Температура подачи",
                    data: this.direct_t,
                    backgroundColor: [
                        'rgba(204, 60, 60, 0.5)'
                    ],
                    borderColor: [
                        'rgba(201, 13, 182, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
        var ctx = document.getElementById('backCanvas');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.time_marks,
                datasets: [{
                    label: "Температура обратки",
                    data: this.back_t,
                    backgroundColor: [
                        'rgba(30, 120, 102, 0.5)'
                    ],
                    borderColor: [
                        'rgba(51, 212, 143, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
    },
    methods: {

    }
}
</script>