<template>
    <div class="container mt-5">
        <h2 class="mb-4 text-center">Students per semester</h2>
        <div v-if="loaded">
            <Doughnut id="my-chart-id" :options="chartOptions" :data="chartData" />
        </div>
        <div v-else>
            <p>Loading...</p>
        </div>
    </div>
</template>

<script>
import { Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
ChartJS.register(ArcElement, Tooltip, Legend)
import axios from 'axios'

function getRandomHexColor() {
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

export default {
    name: 'DoughnutChart',
    components: { Doughnut },
    data() {
        return {
            loaded: false,
            chartData: {
                labels: [],
                datasets: [
                    {
                        label: 'Number of students',
                        backgroundColor: '',
                        data: []
                    }
                ],
            },
            chartOptions: {
                responsive: true
            }
        }
    },
    async mounted() {
        try {
            let response = await axios.get('/students-semester-report');
            console.log(response);

            const chartLabels = response.data.map(entry => 'Semester ' + entry.semester);
            const chartData = response.data.map(entry => entry.student_count || 0);

            this.chartData.labels = chartLabels;
            this.chartData.datasets[0].data = chartData;
            this.chartData.datasets[0].backgroundColor = chartLabels.map(() => getRandomHexColor());

            // Verifica si hay programas sin estudiantes
            this.hasProgramsWithoutStudents = chartData.some(count => count === 0);

            this.loaded = true;
        } catch (error) {
            console.error('Error fetching data:', error);
        }
    }
}
</script>
