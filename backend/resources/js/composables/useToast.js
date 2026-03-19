import { ref } from 'vue';

const toasts = ref([]);
let idCounter = 0;

export function useToast() {
    const addToast = ({ title, description, type = 'default', duration = 5000 }) => {
        const id = ++idCounter;
        toasts.value.push({ id, title, description, type, duration });

        if (duration > 0) {
            setTimeout(() => {
                removeToast(id);
            }, duration);
        }

        return id;
    };

    const removeToast = (id) => {
        const index = toasts.value.findIndex(t => t.id === id);
        if (index > -1) {
            toasts.value.splice(index, 1);
        }
    };

    const success = (description, title = 'Berhasil') => {
        return addToast({ title, description, type: 'success' });
    };

    const error = (description, title = 'Gagal') => {
        return addToast({ title, description, type: 'error', duration: 8000 });
    };

    return {
        toasts,
        addToast,
        removeToast,
        success,
        error,
    };
}
