<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from "vue";
import Quill from "quill";
import "quill/dist/quill.snow.css";

const props = defineProps({
    modelValue: { type: String, default: "" },
    placeholder: { type: String, default: "Tulis konten di sini..." },
});
const emit = defineEmits(["update:modelValue"]);

const containerRef = ref(null);
let quill = null;

onMounted(() => {
    quill = new Quill(containerRef.value, {
        theme: "snow",
        placeholder: props.placeholder,
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ["bold", "italic", "underline", "strike"],
                [{ list: "ordered" }, { list: "bullet" }],
                ["link", "blockquote", "code-block"],
                [{ align: [] }],
                ["clean"],
            ],
        },
    });

    if (props.modelValue) {
        quill.clipboard.dangerouslyPasteHTML(props.modelValue);
    }

    quill.on("text-change", () => {
        emit("update:modelValue", quill.root.innerHTML || "");
    });
});

watch(
    () => props.modelValue,
    (val) => {
        if (!quill) return;
        const current = quill.root.innerHTML;
        if ((val || "") !== current) {
            const sel = quill.getSelection();
            quill.clipboard.dangerouslyPasteHTML(val || "");
            if (sel) quill.setSelection(sel);
        }
    },
);

onBeforeUnmount(() => {
    quill = null;
});
</script>

<template>
    <div class="quill-editor">
        <div ref="containerRef" class="min-h-[200px]"></div>
    </div>
</template>

