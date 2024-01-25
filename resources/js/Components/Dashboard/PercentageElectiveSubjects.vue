<template>
    <div class="container mt-5">
        <h2 class="mb-4 text-center">Percentage of Elective Subjects</h2>
        <div v-if="loaded">
            <Pie id="my-chart-id" :options="chartOptions" :data="chartData" />
        </div>
        <div v-else>
            <p>Loading...</p>
        </div>
    </div>
</template>

<script>
import { Pie } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
import axios from 'axios'
ChartJS.register(ArcElement, Tooltip, Legend)

function getRandomHexColor() {
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

export default {
    name: 'PieChart',
    components: { Pie },
    data() {
        return {
            loaded: false,
            chartData: {
                labels: [],
                datasets: [
                    {
                        label: 'Percentage of Elective Subjects',
                        backgroundColor: '',
                        data: []
                    }
                ],
            },
            chartOptions: {
                responsive: true,
            }
        }
    },
    async mounted() {
        try {
            let response = await axios.get('/elective-subjects-report');

            const percentageElective = parseFloat(response.data.percentageElective);
            const percentageMandatory = parseFloat(response.data.percentageMandatory);

            const chartLabels = ['Percentage of elective subjects', 'Percentage of mandatory subjects'];
            const chartData = [percentageElective, percentageMandatory];

            this.chartData.labels = chartLabels;
            this.chartData.datasets[0].data = chartData;
            this.chartData.datasets[0].backgroundColor = chartLabels.map(() => getRandomHexColor());

            this.loaded = true;
        } catch (error) {
            console.error('Error fetching data:', error);
        }
    }
}
</script>
