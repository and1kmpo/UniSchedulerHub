import Swal from "sweetalert2";

export function useAlert() {
    // 🔔 Reusable basic toast
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener("mouseenter", Swal.stopTimer);
            toast.addEventListener("mouseleave", Swal.resumeTimer);
        },
    });

    // ✅ Success modal alert
    const success = (message = "Operation successful", title = "Success") => {
        Swal.fire({
            icon: "success",
            title,
            text: message,
            timer: 2000,
            showConfirmButton: false,
        });
    };

    // ❌ Error modal alert
    const error = (message = "An error occurred", title = "Error") => {
        Swal.fire({
            icon: "error",
            title,
            text: message,
        });
    };

    // ℹ️ Informational alert (with confirmation option)
    const info = (message = "Information", title = "Attention") => {
        Swal.fire({
            icon: "info",
            title,
            text: message,
        });
    };

    // ⚠️ Yes/No confirmation alert
    // ⚠️ Yes/No confirmation alert (with optional HTML)
    const confirm = async (
        message = "Are you sure?",
        title = "Confirm",
        useHtml = false
    ) => {
        const result = await Swal.fire({
            title,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#2563eb", // Indigo
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes",
            cancelButtonText: "Cancel",
            ...(useHtml ? { html: message } : { text: message }), // 🔄 usar html o text
        });
        return result.isConfirmed;
    };

    // ✅ Floating success toast
    const toastSuccess = (message = "Saved successfully") => {
        Toast.fire({
            icon: "success",
            title: message,
        });
    };

    // ❌ Floating error toast
    const toastError = (message = "Error saving") => {
        Toast.fire({
            icon: "error",
            title: message,
        });
    };

    // Custom alert
    const custom = (options = {}) => {
        Swal.fire(options);
    };

    //Alert Confirm but with preconfirm
    const confirmWithPreConfirm = async (title, text, preConfirm) => {
        if (!title || !text || !preConfirm) {
            console.error(
                "confirmWithPreConfirm requiere title, text y preConfirm"
            );
            return;
        }

        const result = await Swal.fire({
            title,
            text,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete",
            cancelButtonText: "Cancel",
            reverseButtons: true,
            focusCancel: true,
            showLoaderOnConfirm: true,
            preConfirm,
        });

        return result;
    };

    return {
        success,
        error,
        info,
        confirm,
        confirmWithPreConfirm,
        toastSuccess,
        toastError,
        custom,
    };
}
