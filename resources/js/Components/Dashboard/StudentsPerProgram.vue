<template>
    <div class="container mt-5">
        <h2 class="mb-4 text-center">Total students per program</h2>
        <div v-if="loaded">
            <Bar id="my-chart-id" :options="chartOptions" :data="chartData" />
            <div v-if="hasProgramsWithoutStudents" class="text-center mt-3">
                <p class="text-red-500 text-xs flex items-center">
                    <i class="fas fa-exclamation-circle mr-2 text-lg"></i>
                    Some programs have no students enrolled. Please note that the corresponding bar may be very small and
                    difficult to visualize on the chart.
                </p>
            </div>

        </div>
        <div v-else>
            <p>Loading...</p>
        </div>
    </div>
</template>

<script>
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
import axios from 'axios'
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

function getRandomHexColor() {
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

export default {
    name: 'BarChart',
    components: { Bar },
    data() {
        return {
            loaded: false,
            hasProgramsWithoutStudents: false,
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
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        grace: '2%',
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        }
    },
    async mounted() {
        try {
            let response = await axios.get('/students-program-report');

            const chartLabels = response.data.map(program => program.name);
            const chartData = response.data.map(program => program.student_count || 0);

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
