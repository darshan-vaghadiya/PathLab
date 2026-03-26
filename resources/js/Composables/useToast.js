import { toast } from 'vue-sonner';
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useFlashToast() {
    const page = usePage();

    watch(
        () => page.props.flash,
        (flash) => {
            if (flash?.success) {
                toast.success(flash.success);
            }
            if (flash?.error) {
                toast.error(flash.error);
            }
        },
        { immediate: true, deep: true }
    );
}

export { toast };
