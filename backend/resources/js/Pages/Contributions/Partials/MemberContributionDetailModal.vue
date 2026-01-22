<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    type: Object,
});

const emit = defineEmits(['close', 'success']);

const page = usePage();
const user = computed(() => page.props.auth.user);
const member = computed(() => user.value.role === 'anggota' ? user.value.member : null);

const loading = ref(false);
const statusData = ref(null);
const selectedPeriods = ref([]);
const paymentMethod = ref('transfer');
const receiptFile = ref(null);

const form = useForm({
    member_ids: [],
    contribution_type_id: '',
    amount: 0,
    payment_date: new Date().toISOString().split('T')[0],
    periods: [],
    payment_method: 'transfer',
    notes: '',
    receipt: null,
});

const totalAmount = computed(() => {
    if (!props.type) return 0;
    return selectedPeriods.value.length * props.type.amount;
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(val);
};

const fetchStatus = async () => {
    if (!props.type) return;
    loading.value = true;
    try {
        const res = await axios.get(route('contributions.my-status', props.type.id));
        statusData.value = res.data;
        // Reset selection
        selectedPeriods.value = [];
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

watch(() => props.show, (val) => {
    if (val) {
        fetchStatus();
        form.reset();
        receiptFile.value = null;
    }
});

const togglePeriod = (periodKey) => {
    if (selectedPeriods.value.includes(periodKey)) {
        selectedPeriods.value = selectedPeriods.value.filter(k => k !== periodKey);
    } else {
        selectedPeriods.value.push(periodKey);
    }
};

const submit = () => {
    if (!member.value) return; // safety
    
    // Setup form
    form.member_ids = [member.value.id];
    form.contribution_type_id = props.type.id;
    form.amount = totalAmount.value;
    form.periods = selectedPeriods.value;
    form.payment_method = paymentMethod.value;
    
    // For manual handling of file in useForm
    if (receiptFile.value) {
        form.receipt = receiptFile.value;
    }

    form.post(route('contributions.bulk-store'), {
        onSuccess: () => {
            emit('success');
            emit('close');
        },
    });
};

const handleFileChange = (e) => {
    receiptFile.value = e.target.files[0];
};

const selectAllUnpaid = () => {
    if (!statusData.value) return;
    statusData.value.periods.forEach(p => {
        if (p.status === 'unpaid' && !selectedPeriods.value.includes(p.period)) {
            selectedPeriods.value.push(p.period);
        }
    });
};
</script>

<template>
    <Modal :show="show" @close="$emit('close')" maxWidth="2xl">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight">
                    Rincian Pembayaran
                </h2>
                <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div v-if="loading" class="py-12 text-center text-gray-500 text-sm">
                Memuat data...
            </div>

            <div v-else-if="statusData">
                <!-- Header Info -->
                <div class="mb-6 bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold uppercase text-gray-500">Jenis Iuran</span>
                        <span class="text-sm font-black text-gray-900">{{ type?.name }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold uppercase text-gray-500">Nominal per Periode</span>
                        <span class="text-indigo-600 font-bold">{{ formatCurrency(type?.amount) }}</span>
                    </div>
                </div>

                <!-- Periodic Section -->
                <div v-if="statusData.type !== 'once'" class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest">Daftar Periode</h3>
                        <button @click="selectAllUnpaid" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 uppercase">Pilih Semua Belum Bayar</button>
                    </div>
                    
                    <div class="flex items-center gap-4 mb-4" v-if="statusData.summary">
                        <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-indigo-500 h-full" :style="{ width: statusData.summary.percentage + '%' }"></div>
                        </div>
                        <span class="text-xs font-bold text-gray-600">{{ statusData.summary.paid }} / {{ statusData.summary.total }} ({{ statusData.summary.percentage }}%)</span>
                    </div>

                    <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-xl">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase text-gray-500">Periode</th>
                                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase text-gray-500">Jatuh Tempo</th>
                                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase text-gray-500">Status</th>
                                    <th class="px-4 py-3 text-center text-[10px] font-bold uppercase text-gray-500">Pilih</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100 text-sm">
                                <tr v-for="p in statusData.periods" :key="p.period" :class="{'bg-indigo-50/50': selectedPeriods.includes(p.period)}">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ p.label }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ p.due_date || '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span v-if="p.status === 'paid'" class="text-xs font-bold text-green-600 uppercase">Lunas</span>
                                        <span v-else-if="p.status === 'pending'" class="text-xs font-bold text-yellow-600 uppercase">Menunggu</span>
                                        <span v-else class="text-xs font-bold text-red-500 uppercase">Belum Bayar</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input 
                                            v-if="p.status === 'unpaid'" 
                                            type="checkbox" 
                                            :checked="selectedPeriods.includes(p.period)"
                                            @change="togglePeriod(p.period)"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                        <span v-else class="text-gray-300">-</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- One Time Section -->
                <div v-else class="mb-6">
                    <div v-if="statusData.status === 'unpaid'" class="p-4 bg-red-50 text-red-700 rounded-xl text-center text-sm font-bold">
                        Anda belum membayar iuran ini.
                    </div>
                     <div v-else class="p-4 bg-green-50 text-green-700 rounded-xl text-center text-sm font-bold">
                        Status: {{ statusData.status === 'paid' ? 'LUNAS' : 'MENUNGGU VERIFIKASI' }}
                    </div>
                </div>

                <!-- Payment Form -->
                <div v-if="(statusData.type !== 'once' && selectedPeriods.length > 0) || (statusData.type === 'once' && statusData.status === 'unpaid')" class="mt-8 border-t border-gray-100 pt-6">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-4">Konfirmasi Pembayaran</h3>
                    
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Total Periode</span>
                            <span class="font-bold tex-gray-900">{{ statusData.type === 'once' ? '1' : selectedPeriods.length }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Total Pembayaran</span>
                            <span class="font-black text-xl text-indigo-600">{{ formatCurrency(statusData.type === 'once' ? type.amount : totalAmount) }}</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                             <InputLabel value="Metode Pembayaran" class="text-[10px] uppercase text-gray-400 mb-1" />
                             <select v-model="paymentMethod" class="w-full border-gray-100 rounded-xl font-bold text-sm text-gray-700">
                                 <option value="transfer">Transfer Bank</option>
                                 <option value="cash">Tunai (Cash)</option>
                             </select>
                        </div>
                        
                        <div v-if="paymentMethod === 'transfer'">
                             <InputLabel value="Bukti Transfer (Gambar)" class="text-[10px] uppercase text-gray-400 mb-1" />
                             <input type="file" @change="handleFileChange" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                        </div>
                        
                         <div>
                             <InputLabel value="Catatan (Opsional)" class="text-[10px] uppercase text-gray-400 mb-1" />
                             <textarea v-model="form.notes" rows="2" class="w-full border-gray-100 rounded-xl text-sm"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <SecondaryButton @click="$emit('close')">Batal</SecondaryButton>
                        <PrimaryButton 
                            :disabled="form.processing || (paymentMethod === 'transfer' && !receiptFile)" 
                            :class="{'opacity-50': form.processing || (paymentMethod === 'transfer' && !receiptFile)}"
                            @click="submit"
                        >
                            {{ form.processing ? 'Memproses...' : 'Kirim Pembayaran' }}
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </div>
    </Modal>
</template>
