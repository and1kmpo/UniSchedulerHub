<template>

    <!-- Isotipo Oficial TARRAYA - Centrado Óptico y Anclaje Estructural Certificado -->
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" role="img"
        aria-label="TARRAYA Isotype - Optical Balance Master" class="block">
        <!-- CAPA 1: INFRAESTRUCTURA DE RED (Anillos y ejes concéntricos matemáticamente perfectos) -->
        <g id="layer-network-backbone">
            <!-- Anillo Perimetral Operativo Principal (#2563EB) -->
            <circle cx="250" cy="250" r="150" fill="none" stroke="#2563EB" stroke-width="4.5" />

            <!-- Anillo Secundario de Comunicación Central -->
            <circle cx="250" cy="250" r="125" fill="none" stroke="#5C6B73" stroke-width="1.5" opacity="0.35" />

            <!-- Ejes de Sincronización de Datos (Mueren exactamente en el radio de los nodos perimetrales R=150) -->
            <!-- Eje Vertical (Norte - Sur) -->
            <line x1="250" y1="100" x2="250" y2="174" stroke="#5C6B73" stroke-width="1.2" stroke-linecap="round"
                opacity="0.25" />
            <line x1="250" y1="326" x2="250" y2="400" stroke="#5C6B73" stroke-width="1.2" stroke-linecap="round"
                opacity="0.25" />

            <!-- Eje Horizontal (Este - Oeste) -->
            <line x1="100" y1="250" x2="174" y2="250" stroke="#5C6B73" stroke-width="1.2" stroke-linecap="round"
                opacity="0.25" />
            <line x1="326" y1="250" x2="400" y2="250" stroke="#5C6B73" stroke-width="1.2" stroke-linecap="round"
                opacity="0.25" />

            <!-- Ejes Diagonales con Intersecciones Angulares a 45° Reales -->
            <line x1="144" y1="144" x2="196" y2="196" stroke="#5C6B73" stroke-width="1.2" stroke-linecap="round"
                opacity="0.25" />
            <line x1="304" y1="304" x2="356" y2="356" stroke="#5C6B73" stroke-width="1.2" stroke-linecap="round"
                opacity="0.25" />
            <line x1="144" y1="356" x2="196" y2="304" stroke="#5C6B73" stroke-width="1.2" stroke-linecap="round"
                opacity="0.25" />
            <line x1="304" y1="196" x2="356" y2="144" stroke="#5C6B73" stroke-width="1.2" stroke-linecap="round"
                opacity="0.25" />
        </g>

        <!-- CAPA 2: CAPA OPERATIVA (Nodos periféricos ordenados simétricamente) -->
        <g id="layer-system-nodes">
            <!-- Eje Central Vertical (Norte: Cyber Cyan #06B6D4 / Sur: Slate Ink #0F172A) -->
            <circle cx="250" cy="100" r="14" fill="#06B6D4" />
            <circle cx="250" cy="100" r="5" fill="#0F172A" />
            <circle cx="250" cy="400" r="14" fill="#0F172A" />
            <circle cx="250" cy="400" r="5" fill="#FFFFFF" />

            <!-- Eje Central Horizontal (Este / Oeste: Tarraya Cobalt #2563EB) -->
            <circle cx="400" cy="250" r="14" fill="#2563EB" />
            <circle cx="400" cy="250" r="5" fill="#FFFFFF" />
            <circle cx="100" cy="250" r="14" fill="#2563EB" />
            <circle cx="100" cy="250" r="5" fill="#FFFFFF" />

            <!-- Nodos Diagonales Secundarios (Módulos del Sistema en Graphite Gray #5C6B73) -->
            <circle cx="356" cy="144" r="11" fill="#5C6B73" />
            <circle cx="356" cy="144" r="4.5" fill="#FFFFFF" />
            <circle cx="144" cy="144" r="11" fill="#5C6B73" />
            <circle cx="144" cy="144" r="4.5" fill="#FFFFFF" />
            <circle cx="356" cy="356" r="11" fill="#5C6B73" />
            <circle cx="356" cy="356" r="4.5" fill="#FFFFFF" />
            <circle cx="144" cy="356" r="11" fill="#5C6B73" />
            <circle cx="144" cy="356" r="4.5" fill="#FFFFFF" />
        </g>

        <!-- CAPA 3: THE ACADEMIC NETWORK CORE (Eje central con T en centrado óptico y base extendida) -->
        <g id="layer-brand-core-axis">
            <!-- Escudo Circular Perceptivo en Slate Ink (Radio 106px) -->
            <circle cx="250" cy="250" r="106" fill="#0F172A" />
            <!-- Núcleo de Identidad Principal en Tarraya Cobalt (Radio 96px) -->
            <circle cx="250" cy="250" r="96" fill="#2563EB" />

            <!-- Letra 'T' Calibrada: Techo en Y=180, Grosor de barra: 26px | Poste centrado en X=250, Grosor: 38px | Base extendida a Y=334 -->
            <path d="M185 180H315V206H269V334H231V206H185Z" fill="#FFFFFF" />
        </g>
    </svg>


</template>
