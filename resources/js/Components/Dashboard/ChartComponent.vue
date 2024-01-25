<template>
    <div class="container mt-5">
        <h2 class="mb-4 text-center">Distribution of subjects by knowledge area</h2>
        <div v-if="loaded">
            <Bar id="my-chart-id" :options="chartOptions" :data="chartData" />
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
            chartData: {
                labels: [],
                datasets: [
                    {
                        label: 'Number of subjects',
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
        // Inicializa una variable para almacenar todas las asignaturas
        let allSubjects = [];

        // Realiza una petición inicial para obtener la primera página de asignaturas
        let response = await axios.get('/subjects');

        // Agrega los datos de la primera página a la variable allSubjects
        allSubjects = [...allSubjects, ...response.data.data];

        // Itera sobre las páginas restantes
        while (response.data.next_page_url) {
            // Realiza una nueva petición para obtener la siguiente página de asignaturas
            response = await axios.get(response.data.next_page_url);

            // Agrega los datos de la página actual a la variable allSubjects
            allSubjects = [...allSubjects, ...response.data.data];
        }

        const areasCount = {};
        allSubjects.forEach((subjects) => {
            const area = subjects.knowledge_area;
            areasCount[area] = (areasCount[area] || 0) + 1;
        })

        this.chartData.labels = Object.keys(areasCount);
        this.chartData.datasets[0].data = Object.values(areasCount);
        this.chartData.datasets[0].backgroundColor = this.chartData.labels.map(() => getRandomHexColor());

        this.loaded = true;

    }
}
</script>
