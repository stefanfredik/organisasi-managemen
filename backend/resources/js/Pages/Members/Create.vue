<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ImageUpload from '@/Components/ImageUpload.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    full_name: '',
    nik: '',
    nickname: '',
    gender: '',
    religion: '',
    email: '',
    phone: '',
    address: '',
    domicile_address: '',
    living_status: '',
    marital_status: '',
    occupation: '',
    arrival_date_bali: '',
    origin_hamlet: '',
    origin_village: '',
    origin_subdistrict: '',
    origin_regency: '',
    origin_province: '',
    bpjs_health_active: false,
    bpjs_employment_active: false,
    date_of_birth: '',
    join_date: new Date().toISOString().split('T')[0],
    status: 'active',
    photo: null,
    ktp_photo: null,
    notes: '',
});

const provinces = ref([]);
const regencies = ref([]);
const districts = ref([]);
const villages = ref([]);

const originProvinceId = ref('');
const originRegencyId = ref('');
const originDistrictId = ref('');
const originVillageId = ref('');

const loadProvinces = async () => {
    const { data } = await axios.get('/api/areas/provinces');
    provinces.value = data.data || [];
};

const loadRegencies = async (provinceId) => {
    regencies.value = [];
    districts.value = [];
    villages.value = [];
    originRegencyId.value = '';
    originDistrictId.value = '';
    originVillageId.value = '';
    if (!provinceId) return;
    const { data } = await axios.get(`/api/areas/regencies/${provinceId}`);
    regencies.value = data.data || [];
};

const loadDistricts = async (regencyId) => {
    districts.value = [];
    villages.value = [];
    originDistrictId.value = '';
    originVillageId.value = '';
    if (!regencyId) return;
    const { data } = await axios.get(`/api/areas/districts/${regencyId}`);
    districts.value = data.data || [];
};

const loadVillages = async (districtId) => {
    villages.value = [];
    originVillageId.value = '';
    if (!districtId) return;
    const { data } = await axios.get(`/api/areas/villages/${districtId}`);
    villages.value = data.data || [];
};

const onProvinceChange = (e) => {
    const id = e.target.value;
    originProvinceId.value = id;
    const selected = provinces.value.find(p => String(p.code) === String(id));
    form.origin_province = selected ? selected.name : '';
    loadRegencies(id);
};

const onRegencyChange = (e) => {
    const id = e.target.value;
    originRegencyId.value = id;
    const selected = regencies.value.find(r => String(r.code) === String(id));
    form.origin_regency = selected ? selected.name : '';
    loadDistricts(id);
};

const onDistrictChange = (e) => {
    const id = e.target.value;
    originDistrictId.value = id;
    const selected = districts.value.find(d => String(d.code) === String(id));
    form.origin_subdistrict = selected ? selected.name : '';
    loadVillages(id);
};

const onVillageChange = (e) => {
    const id = e.target.value;
    originVillageId.value = id;
    const selected = villages.value.find(v => String(v.code) === String(id));
    form.origin_village = selected ? selected.name : '';
};

const submit = () => {
    form.post('/members', {
        preserveScroll: true,
        forceFormData: true,
    });
};

onMounted(() => {
    loadProvinces();
});
</script>

<template>
    <Head title="Tambah Anggota" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-3">
                <Link href="/members" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="text-xl font-semibold text-gray-800">Tambah Anggota</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <InputLabel for="photo" value="Foto Profil" />
                            <div class="mt-2">
                                <ImageUpload v-model="form.photo" />
                            </div>
                            <InputError class="mt-2" :message="form.errors.photo" />
                        </div>

                        <div>
                            <InputLabel for="full_name" value="Nama Lengkap *" />
                            <TextInput
                                id="full_name"
                                v-model="form.full_name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                            />
                            <InputError class="mt-2" :message="form.errors.full_name" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="nik" value="NIK" />
                                <TextInput
                                    id="nik"
                                    v-model="form.nik"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.nik" />
                            </div>
                            <div>
                                <InputLabel for="nickname" value="Nama Panggilan" />
                                <TextInput
                                    id="nickname"
                                    v-model="form.nickname"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.nickname" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="gender" value="Jenis Kelamin" />
                                <select
                                    id="gender"
                                    v-model="form.gender"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">-</option>
                                    <option value="male">Laki-laki</option>
                                    <option value="female">Perempuan</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.gender" />
                            </div>
                            <div>
                                <InputLabel for="religion" value="Agama" />
                                <select
                                    id="religion"
                                    v-model="form.religion"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">-</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Kristen">Kristen</option>
                                    <option value="Katolik">Katolik</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Buddha">Buddha</option>
                                    <option value="Konghucu">Konghucu</option>
                                    <option value="Kepercayaan">Kepercayaan</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.religion" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="email" value="Email" />
                                <TextInput
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div>
                                <InputLabel for="phone" value="Nomor Telepon" />
                                <TextInput
                                    id="phone"
                                    v-model="form.phone"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.phone" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="address" value="Alamat" />
                            <textarea
                                id="address"
                                v-model="form.address"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.address" />
                        </div>

                        <div>
                            <InputLabel for="domicile_address" value="Alamat Domisili" />
                            <textarea
                                id="domicile_address"
                                v-model="form.domicile_address"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.domicile_address" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="living_status" value="Status Tempat Tinggal" />
                                <select
                                    id="living_status"
                                    v-model="form.living_status"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">-</option>
                                    <option value="kos">Kos</option>
                                    <option value="rumah">Rumah</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.living_status" />
                            </div>
                            <div>
                                <InputLabel for="marital_status" value="Status Pernikahan" />
                                <TextInput
                                    id="marital_status"
                                    v-model="form.marital_status"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.marital_status" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="occupation" value="Pekerjaan" />
                                <TextInput
                                    id="occupation"
                                    v-model="form.occupation"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.occupation" />
                            </div>
                            <div>
                                <InputLabel for="arrival_date_bali" value="Waktu Tiba di Bali" />
                                <TextInput
                                    id="arrival_date_bali"
                                    v-model="form.arrival_date_bali"
                                    type="date"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.arrival_date_bali" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="origin_hamlet" value="Kampung Asal" />
                                <TextInput
                                    id="origin_hamlet"
                                    v-model="form.origin_hamlet"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.origin_hamlet" />
                            </div>
                            <div>
                                <InputLabel for="origin_province_id" value="Provinsi" />
                                <select
                                    id="origin_province_id"
                                    :value="originProvinceId"
                                    @change="onProvinceChange"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">-</option>
                                    <option v-for="prov in provinces" :key="prov.code" :value="prov.code">{{ prov.name }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.origin_province" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="origin_regency_id" value="Kabupaten" />
                                <select
                                    id="origin_regency_id"
                                    :value="originRegencyId"
                                    @change="onRegencyChange"
                                    :disabled="!originProvinceId"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm disabled:bg-gray-100"
                                >
                                    <option value="">-</option>
                                    <option v-for="reg in regencies" :key="reg.code" :value="reg.code">{{ reg.name }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.origin_regency" />
                            </div>
                            <div>
                                <InputLabel for="origin_subdistrict_id" value="Kecamatan" />
                                <select
                                    id="origin_subdistrict_id"
                                    :value="originDistrictId"
                                    @change="onDistrictChange"
                                    :disabled="!originRegencyId"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm disabled:bg-gray-100"
                                >
                                    <option value="">-</option>
                                    <option v-for="dis in districts" :key="dis.code" :value="dis.code">{{ dis.name }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.origin_subdistrict" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="origin_village_id" value="Desa" />
                            <select
                                id="origin_village_id"
                                :value="originVillageId"
                                @change="onVillageChange"
                                :disabled="!originDistrictId"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm disabled:bg-gray-100"
                            >
                                <option value="">-</option>
                                <option v-for="vil in villages" :key="vil.code" :value="vil.code">{{ vil.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.origin_village" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-center gap-2">
                                <input
                                    id="bpjs_health_active"
                                    type="checkbox"
                                    v-model="form.bpjs_health_active"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                />
                                <InputLabel for="bpjs_health_active" value="BPJS Kesehatan Aktif" />
                            </div>
                            <div class="flex items-center gap-2">
                                <input
                                    id="bpjs_employment_active"
                                    type="checkbox"
                                    v-model="form.bpjs_employment_active"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                />
                                <InputLabel for="bpjs_employment_active" value="BPJS Tenaga Kerja Aktif" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="date_of_birth" value="Tanggal Lahir" />
                                <TextInput
                                    id="date_of_birth"
                                    v-model="form.date_of_birth"
                                    type="date"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.date_of_birth" />
                            </div>

                            <div>
                                <InputLabel for="join_date" value="Tanggal Bergabung *" />
                                <TextInput
                                    id="join_date"
                                    v-model="form.join_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.join_date" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="ktp_photo" value="Foto KTP" />
                            <div class="mt-2">
                                <ImageUpload v-model="form.ktp_photo" :maxSize="4096" />
                            </div>
                            <InputError class="mt-2" :message="form.errors.ktp_photo" />
                        </div>

                        <div>
                            <InputLabel for="status" value="Status *" />
                            <select
                                id="status"
                                v-model="form.status"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.status" />
                        </div>

                        <div>
                            <InputLabel for="notes" value="Catatan" />
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.notes" />
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end gap-4">
                            <Link
                                href="/members"
                                class="text-sm text-gray-600 hover:text-gray-900"
                            >
                                Batal
                            </Link>
                            <PrimaryButton :disabled="form.processing">
                                Simpan
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
