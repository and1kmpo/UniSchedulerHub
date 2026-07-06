function createPrintFrame() {
    const iframe = document.createElement("iframe");

    iframe.style.position = "fixed";
    iframe.style.right = "0";
    iframe.style.bottom = "0";
    iframe.style.width = "0";
    iframe.style.height = "0";
    iframe.style.border = "0";

    document.body.appendChild(iframe);

    return iframe;
}

function printFrame(iframe) {
    setTimeout(() => {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        setTimeout(() => document.body.removeChild(iframe), 500);
    }, 100);
}

export function printHtml(html) {
    const iframe = createPrintFrame();
    const printDocument = iframe.contentWindow.document;

    printDocument.open();
    printDocument.write(html);
    printDocument.close();

    iframe.onload = () => printFrame(iframe);
}

export function printDocument(title, buildDocument) {
    const iframe = createPrintFrame();
    const printDocument = iframe.contentWindow.document;

    printDocument.open();
    printDocument.close();
    printDocument.title = title;

    buildDocument(printDocument);
    printFrame(iframe);
}

function escapeHtml(value) {
    return String(value ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

function renderFilters(filters) {
    const activeFilters = filters.filter((filter) => filter.value !== "" && filter.value !== null && filter.value !== undefined);

    if (!activeFilters.length) {
        return '<span class="filter-pill">No filters applied</span>';
    }

    return activeFilters
        .map((filter) => `<span class="filter-pill"><strong>${escapeHtml(filter.label)}:</strong> ${escapeHtml(filter.value)}</span>`)
        .join("");
}

function renderMetrics(metrics) {
    return metrics
        .map((metric) => `
            <div class="metric">
                <span>${escapeHtml(metric.label)}</span>
                <strong>${escapeHtml(metric.value)}</strong>
            </div>
        `)
        .join("");
}

function renderRows(columns, rows) {
    if (!rows.length) {
        return `<tr><td colspan="${columns.length}">No records found.</td></tr>`;
    }

    return rows
        .map((row) => `
            <tr>
                ${columns.map((column) => `<td>${escapeHtml(row[column.key])}</td>`).join("")}
            </tr>
        `)
        .join("");
}

const printTokens = {
    brand: "#2563EB",
    brandSoft: "#EFF6FF",
    ink: "#0F172A",
    graphite: "#5C6B73",
    surface: "#FFFFFF",
    surfaceMuted: "#F8FAFC",
    border: "#E2E8F0",
    borderStrong: "#CBD5E1",
};

function generatedAt() {
    return new Intl.DateTimeFormat("en-US", {
        year: "numeric",
        month: "short",
        day: "2-digit",
        hour: "numeric",
        minute: "2-digit",
        hour12: true,
    }).format(new Date());
}

export function printTableReport({
    title,
    subtitle,
    filters = [],
    metrics = [],
    columns = [],
    rows = [],
    orientation = "landscape",
}) {
    printHtml(`
        <!doctype html>
        <html>
            <head>
                <title>${escapeHtml(title)}</title>
                <style>
                    * { box-sizing: border-box; }
                    body {
                        background: ${printTokens.surface};
                        color: ${printTokens.ink};
                        font-family: "Geist", "Inter", Arial, sans-serif;
                        margin: 28px;
                        padding-bottom: 36px;
                    }
                    header {
                        border-bottom: 2px solid ${printTokens.ink};
                        display: flex;
                        justify-content: space-between;
                        gap: 24px;
                        margin-bottom: 18px;
                        padding-bottom: 16px;
                    }
                    .brand {
                        color: ${printTokens.brand};
                        font-size: 12px;
                        font-weight: 700;
                        letter-spacing: 0.08em;
                        margin-bottom: 6px;
                        text-transform: uppercase;
                    }
                    .brand-descriptor {
                        color: ${printTokens.graphite};
                        font-size: 10px;
                        font-weight: 700;
                        letter-spacing: 0.12em;
                        text-transform: uppercase;
                    }
                    h1 { font-size: 24px; margin: 0; }
                    p { color: ${printTokens.graphite}; margin: 6px 0 0; }
                    .generated {
                        color: ${printTokens.graphite};
                        font-family: "Geist Mono", "JetBrains Mono", monospace;
                        font-size: 12px;
                        min-width: 180px;
                        text-align: right;
                    }
                    .filters {
                        background: ${printTokens.surfaceMuted};
                        border: 1px solid ${printTokens.border};
                        border-radius: 8px;
                        margin-bottom: 18px;
                        padding: 12px;
                    }
                    .section-label {
                        color: ${printTokens.graphite};
                        display: block;
                        font-size: 11px;
                        font-weight: 700;
                        margin-bottom: 8px;
                        text-transform: uppercase;
                    }
                    .filter-pill {
                        background: ${printTokens.surface};
                        border: 1px solid ${printTokens.border};
                        border-radius: 999px;
                        display: inline-block;
                        font-size: 11px;
                        margin: 0 6px 6px 0;
                        padding: 6px 10px;
                    }
                    .summary {
                        display: grid;
                        gap: 10px;
                        grid-template-columns: repeat(4, 1fr);
                        margin-bottom: 20px;
                    }
                    .metric {
                        background: ${printTokens.surface};
                        border: 1px solid ${printTokens.border};
                        border-radius: 8px;
                        min-height: 70px;
                        padding: 10px;
                    }
                    .metric span {
                        color: ${printTokens.graphite};
                        display: block;
                        font-size: 11px;
                        font-weight: 700;
                        text-transform: uppercase;
                    }
                    .metric strong {
                        display: block;
                        font-size: 22px;
                        margin-top: 6px;
                    }
                    table {
                        border-collapse: collapse;
                        font-size: 11px;
                        width: 100%;
                    }
                    th {
                        background: ${printTokens.brandSoft};
                        color: ${printTokens.ink};
                        text-align: left;
                    }
                    th, td {
                        border: 1px solid ${printTokens.borderStrong};
                        padding: 7px;
                        vertical-align: top;
                    }
                    tr { break-inside: avoid; }
                    footer {
                        border-top: 1px solid ${printTokens.border};
                        bottom: 0;
                        color: ${printTokens.graphite};
                        display: flex;
                        font-size: 10px;
                        justify-content: space-between;
                        left: 28px;
                        padding-top: 8px;
                        position: fixed;
                        right: 28px;
                    }
                    .page-number::after { content: counter(page); }
                    @page { margin: 16mm; size: ${orientation}; }
                </style>
            </head>
            <body>
                <header>
                    <div>
                        <div class="brand">TARRAYA</div>
                        <div class="brand-descriptor">Academic Operating System</div>
                        <h1>${escapeHtml(title)}</h1>
                        <p>${escapeHtml(subtitle)}</p>
                    </div>
                    <div class="generated">
                        Generated<br>
                        <strong>${escapeHtml(generatedAt())}</strong>
                    </div>
                </header>

                <section class="filters">
                    <span class="section-label">Applied filters</span>
                    ${renderFilters(filters)}
                </section>

                <section class="summary">
                    ${renderMetrics(metrics)}
                </section>

                <table>
                    <thead>
                        <tr>
                            ${columns.map((column) => `<th>${escapeHtml(column.label)}</th>`).join("")}
                        </tr>
                    </thead>
                    <tbody>
                        ${renderRows(columns, rows)}
                    </tbody>
                </table>

                <footer>
                    <span>TARRAYA Academic Operations</span>
                    <span>Page <span class="page-number"></span></span>
                </footer>
            </body>
        </html>
    `);
}
