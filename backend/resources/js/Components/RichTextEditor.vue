<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from "vue";

const props = defineProps({
    modelValue: { type: String, default: "" },
    placeholder: { type: String, default: "Tulis konten di sini..." },
});
const emit = defineEmits(["update:modelValue"]);

const editorRef = ref(null);
const isFocused = ref(false);
const lastRange = ref(null);

const withinEditor = (selection) => {
    const anchorNode = selection.anchorNode;
    return editorRef.value && anchorNode && editorRef.value.contains(anchorNode);
};

const saveSelection = () => {
    const sel = window.getSelection();
    if (!sel || sel.rangeCount === 0) return;
    if (!withinEditor(sel)) return;
    lastRange.value = sel.getRangeAt(0);
};

const restoreSelection = () => {
    const sel = window.getSelection();
    if (!sel || !lastRange.value) return;
    sel.removeAllRanges();
    sel.addRange(lastRange.value);
};

const exec = (cmd, value = null) => {
    restoreSelection();
    editorRef.value?.focus();
    document.execCommand(cmd, false, value);
    emit("update:modelValue", editorRef.value?.innerHTML || "");
};

const insertLink = () => {
    const url = prompt("Masukkan URL:");
    if (url) exec("createLink", url);
};

const clearFormat = () => {
    exec("removeFormat");
};

watch(
    () => props.modelValue,
    (val) => {
        if (editorRef.value && editorRef.value.innerHTML !== val) {
            editorRef.value.innerHTML = val || "";
        }
    },
    { immediate: true },
);

onMounted(() => {
    if (editorRef.value && props.modelValue) {
        editorRef.value.innerHTML = props.modelValue;
    }
    document.addEventListener("selectionchange", saveSelection);
});

onBeforeUnmount(() => {
    document.removeEventListener("selectionchange", saveSelection);
});
</script>

<template>
    <div class="border rounded-md shadow-sm bg-white">
        <div class="px-2 py-1 border-b bg-gray-50 flex items-center gap-2">
            <button type="button" class="px-2 py-1 text-xs rounded hover:bg-gray-100" @mousedown.prevent="exec('bold')">B</button>
            <button type="button" class="px-2 py-1 text-xs italic rounded hover:bg-gray-100" @mousedown.prevent="exec('italic')">I</button>
            <button type="button" class="px-2 py-1 text-xs underline rounded hover:bg-gray-100" @mousedown.prevent="exec('underline')">U</button>
            <span class="w-px h-4 bg-gray-200 mx-1"></span>
            <button type="button" class="px-2 py-1 text-xs rounded hover:bg-gray-100" @mousedown.prevent="exec('insertUnorderedList')">• List</button>
            <button type="button" class="px-2 py-1 text-xs rounded hover:bg-gray-100" @mousedown.prevent="exec('insertOrderedList')">1. List</button>
            <span class="w-px h-4 bg-gray-200 mx-1"></span>
            <button type="button" class="px-2 py-1 text-xs rounded hover:bg-gray-100" @mousedown.prevent="insertLink">Link</button>
            <button type="button" class="px-2 py-1 text-xs rounded hover:bg-gray-100" @mousedown.prevent="clearFormat">Clear</button>
        </div>
        <div
            ref="editorRef"
            class="min-h-[180px] px-3 py-2 focus:outline-none text-sm"
            contenteditable="true"
            :data-placeholder="placeholder"
            @focus="isFocused = true"
            @blur="isFocused = false"
            @input="emit('update:modelValue', editorRef?.innerHTML || '')"
        ></div>
    </div>
</template>

<style>
[contenteditable="true"][data-placeholder]:empty:before {
    content: attr(data-placeholder);
    color: #9ca3af;
}
</style>
