const DATE_FORMATTER = new Intl.DateTimeFormat("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
});

const TIME_FORMATTER = new Intl.DateTimeFormat("en-US", {
    hour: "numeric",
    minute: "2-digit",
    hour12: true,
});

function parseDate(value, useUtcForDateOnly = true) {
    if (!value) {
        return null;
    }

    if (value instanceof Date) {
        return Number.isNaN(value.getTime()) ? null : value;
    }

    const normalized = String(value);

    if (useUtcForDateOnly && /^\d{4}-\d{2}-\d{2}$/.test(normalized)) {
        const [year, month, day] = normalized.split("-").map(Number);

        return new Date(Date.UTC(year, month - 1, day));
    }

    const parsed = new Date(normalized);

    return Number.isNaN(parsed.getTime()) ? null : parsed;
}

export function formatDate(value, fallback = "-") {
    const date = parseDate(value);

    return date ? DATE_FORMATTER.format(date) : fallback;
}

export function formatDateTime(value, fallback = "-") {
    const date = parseDate(value, false);

    return date ? `${DATE_FORMATTER.format(date)}, ${TIME_FORMATTER.format(date)}` : fallback;
}

export function formatTime(value, fallback = "-") {
    if (!value) {
        return fallback;
    }

    const [hours = "0", minutes = "0"] = String(value).split(":");
    const date = new Date(2026, 0, 5, Number(hours), Number(minutes), 0, 0);

    return Number.isNaN(date.getTime()) ? fallback : TIME_FORMATTER.format(date);
}

export function toDateInput(value) {
    return value ? String(value).slice(0, 10) : "";
}
