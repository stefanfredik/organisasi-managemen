<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    options: {
        type: Array,
        required: true,
        // Expected format: [{ value: 'key', label: 'Display Text', subLabel: 'Optional Sub' }]
    },
    placeholder: {
        type: String,
        default: 'Pilih opsi...',
    },
    searchPlaceholder: {
        type: String,
        default: 'Cari...',
    },
    label: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const searchQuery = ref('');
const containerRef = ref(null);
const searchInputRef = ref(null);

const selectedOption = computed(() => {
    return props.options.find(opt => opt.value === props.modelValue);
});

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    const query = searchQuery.value.toLowerCase();
    return props.options.filter(opt => 
        opt.label.toLowerCase().includes(query) || 
        (opt.subLabel && opt.subLabel.toLowerCase().includes(query))
    );
});

const toggleDropdown = () => {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        searchQuery.value = '';
        nextTick(() => {
            if (searchInputRef.value) searchInputRef.value.focus();
        });
    }
};

const selectOption = (option) => {
    emit('update:modelValue', option.value);
    emit('change', option);
    isOpen.value = false;
    searchQuery.value = '';
};

const closeDropdown = (e) => {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});
</script>

<template>
    <div class="relative" ref="containerRef">
        <label v-if="label" class="block text-sm font-medium text-gray-700 mb-1">
            {{ label }}
        </label>
        
        <!-- Trigger Button -->
        <button
            type="button"
            @click="toggleDropdown"
            class="relative w-full bg-white border rounded-lg shadow-sm pl-3 pr-10 py-2 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors"
            :class="[
                error ? 'border-red-300' : 'border-gray-300',
                disabled ? 'bg-gray-100 cursor-not-allowed opacity-75' : 'hover:bg-gray-50'
            ]"
            :disabled="disabled"
        >
            <span v-if="selectedOption" class="block truncate text-gray-900">
                {{ selectedOption.label }}
                <span v-if="selectedOption.subLabel" class="text-gray-400 text-xs ml-1">
                    ({{ selectedOption.subLabel }})
                </span>
            </span>
            <span v-else class="block truncate text-gray-400">
                {{ placeholder }}
            </span>
            
            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                </svg>
            </span>
        </button>

        <!-- Dropdown Menu -->
        <div
            v-if="isOpen"
            class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
        >
            <!-- Search Input -->
            <div class="sticky top-0 z-10 bg-white px-2 py-2 border-b border-gray-100">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        ref="searchInputRef"
                        type="text"
                        v-model="searchQuery"
                        class="block w-full pl-9 pr-3 py-1.5 border border-gray-200 rounded-md leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-xs"
                        :placeholder="searchPlaceholder"
                    />
                </div>
            </div>

            <!-- Options List -->
            <ul class="max-h-48 overflow-y-auto">
                <li
                    v-for="option in filteredOptions"
                    :key="option.value"
                    @click="selectOption(option)"
                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50 text-gray-900"
                    :class="{ 'bg-indigo-50/50': option.value === modelValue }"
                >
                    <div class="flex items-center">
                        <span class="block truncate font-normal" :class="{ 'font-semibold': option.value === modelValue }">
                            {{ option.label }}
                        </span>
                        <span v-if="option.subLabel" class="ml-2 truncate text-gray-400 text-xs">
                            {{ option.subLabel }}
                        </span>
                    </div>

                    <span
                        v-if="option.value === modelValue"
                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </li>
                
                <!-- No Results -->
                <li v-if="filteredOptions.length === 0" class="cursor-default select-none relative py-2 pl-3 pr-9 text-gray-500 italic text-center text-xs">
                    Tidak ada hasil ditemukan
                </li>
            </ul>
        </div>

        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    </div>
</template>
