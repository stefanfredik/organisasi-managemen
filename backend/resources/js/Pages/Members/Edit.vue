<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ImageUpload from '@/Components/ImageUpload.vue';
import InputError from '@/Components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

const props = defineProps({
    member: Object,
    positions: Object,
});

const form = useForm({
    full_name: props.member.full_name,
    nik: props.member.nik,
    nickname: props.member.nickname,
    gender: props.member.gender,
    religion: props.member.religion,
    email: props.member.email,
    phone: props.member.phone,
    address: props.member.address,
    domicile_address: props.member.domicile_address,
    living_status: props.member.living_status,
    marital_status: props.member.marital_status,
    occupation: props.member.occupation,
    arrival_date_bali: props.member.arrival_date_bali,
    origin_hamlet: props.member.origin_hamlet,
    origin_village: props.member.origin_village,
    origin_subdistrict: props.member.origin_subdistrict,
    origin_regency: props.member.origin_regency,
    origin_province: props.member.origin_province,
    bpjs_health_active: !!props.member.bpjs_health_active,
    bpjs_employment_active: !!props.member.bpjs_employment_active,
    date_of_birth: props.member.date_of_birth,
    join_date: props.member.join_date,
    position_id: props.member.position_id || (props.positions ? Object.keys(props.positions)[0] : ''),
    status: props.member.status,
    photo: null,
    ktp_photo: null,
    notes: props.member.notes,
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

const initializeLocationSelections = async () => {
    await loadProvinces();
    if (form.origin_province) {
        const prov = provinces.value.find(p => p.name === form.origin_province);
        if (prov) {
            originProvinceId.value = prov.code;
            await loadRegencies(prov.code);
        }
    }
    if (form.origin_regency) {
        const reg = regencies.value.find(r => r.name === form.origin_regency);
        if (reg) {
            originRegencyId.value = reg.code;
            await loadDistricts(reg.code);
        }
    }
    if (form.origin_subdistrict) {
        const dis = districts.value.find(d => d.name === form.origin_subdistrict);
        if (dis) {
            originDistrictId.value = dis.code;
            await loadVillages(dis.code);
        }
    }
    if (form.origin_village) {
        const vil = villages.value.find(v => v.name === form.origin_village);
        if (vil) {
            originVillageId.value = vil.code;
        }
    }
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(`/members/${props.member.id}`, {
        preserveScroll: true,
        forceFormData: true,
    });
};

onMounted(() => {
    initializeLocationSelections();
});
</script>

<template>
    <Head title="Edit Anggota" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-3">
                <Link href="/members" class="text-muted-foreground hover:text-foreground">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="text-xl font-semibold text-foreground">Edit Anggota</h2>
            </div>
        </template>

        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-card shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <Label for="photo">Foto Profil</Label>
                            <div class="mt-2">
                                <ImageUpload
                                    v-model="form.photo"
                                    :current-image="member.photo_url"
                                />
                            </div>
                            <InputError class="mt-2" :message="form.errors.photo" />
                        </div>

                        <div>
                            <Label for="member_code">Kode Anggota</Label>
                            <Input
                                id="member_code"
                                :model-value="member.member_code"
                                type="text"
                                class="mt-1 block w-full bg-muted"
                                disabled
                            />
                            <p class="mt-1 text-sm text-muted-foreground">Kode anggota tidak dapat diubah</p>
                        </div>

                        <div>
                            <Label for="full_name">Nama Lengkap *</Label>
                            <Input
                                id="full_name"
                                v-model="form.full_name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                            />
                            <InputError class="mt-2" :message="form.errors.full_name" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
                            <div>
                                <Label for="nik">NIK</Label>
                                <Input
                                    id="nik"
                                    v-model="form.nik"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.nik" />
                            </div>
                            <div>
                                <Label for="nickname">Nama Panggilan</Label>
                                <Input
                                    id="nickname"
                                    v-model="form.nickname"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.nickname" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
                            <div>
                                <Label for="gender">Jenis Kelamin</Label>
                                <select
                                    id="gender"
                                    v-model="form.gender"
                                    class="mt-1 block w-full border-input focus:border-ring focus:ring-ring rounded-md shadow-sm"
                                >
                                    <option value="">-</option>
                                    <option value="male">Laki-laki</option>
                                    <option value="female">Perempuan</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.gender" />
                            </div>
                            <div>
                                <Label for="religion">Agama</Label>
                                <select
                                    id="religion"
                                    v-model="form.religion"
                                    class="mt-1 block w-full border-input focus:border-ring focus:ring-ring rounded-md shadow-sm"
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

                        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
                            <div>
                                <Label for="email">Email</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div>
                                <Label for="phone">Nomor Telepon</Label>
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.phone" />
                            </div>
                        </div>

                        <div>
                            <Label for="address">Alamat</Label>
                            <textarea
                                id="address"
                                v-model="form.address"
                                rows="3"
                                class="mt-1 block w-full border-input focus:border-ring focus:ring-ring rounded-md shadow-sm"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.address" />
                        </div>

                        <div>
                            <Label for="domicile_address">Alamat Domisili</Label>
                            <textarea
                                id="domicile_address"
                                v-model="form.domicile_address"
                                rows="3"
                                class="mt-1 block w-full border-input focus:border-ring focus:ring-ring rounded-md shadow-sm"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.domicile_address" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
                            <div>
                                <Label for="living_status">Status Tempat Tinggal</Label>
                                <select
                                    id="living_status"
                                    v-model="form.living_status"
                                    class="mt-1 block w-full border-input focus:border-ring focus:ring-ring rounded-md shadow-sm"
                                >
                                    <option value="">-</option>
                                    <option value="kos">Kos</option>
                                    <option value="rumah">Rumah</option>
                                    <option value="mess">Mess</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.living_status" />
                            </div>
                            <div>
                                <Label for="marital_status">Status Pernikahan</Label>
                                <select
                                    id="marital_status"
                                    v-model="form.marital_status"
                                    class="mt-1 block w-full border-input focus:border-ring focus:ring-ring rounded-md shadow-sm"
                                >
                                    <option value="">-</option>
                                    <option value="Belum Menikah">Belum Menikah</option>
                                    <option value="Menikah">Menikah</option>
                                    <option value="Cerai">Cerai</option>
                                    <option value="Janda/Duda">Janda/Duda</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.marital_status" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
                            <div>
                                <Label for="occupation">Pekerjaan</Label>
                                <Input
                                    id="occupation"
                                    v-model="form.occupation"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.occupation" />
                            </div>
                            <div>
                                <Label for="arrival_date_bali">Waktu Tiba di Bali</Label>
                                <Input
                                    id="arrival_date_bali"
                                    v-model="form.arrival_date_bali"
                                    type="date"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.arrival_date_bali" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
                            <div>
                                <Label for="origin_hamlet">Kampung Asal</Label>
                                <Input
                                    id="origin_hamlet"
                                    v-model="form.origin_hamlet"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.origin_hamlet" />
                            </div>
                            <div>
                                <Label for="origin_province_id">Provinsi</Label>
                                <select
                                    id="origin_province_id"
                                    :value="originProvinceId"
                                    @change="onProvinceChange"
                                    class="mt-1 block w-full border-input focus:border-ring focus:ring-ring rounded-md shadow-sm"
                                >
                                    <option value="">-</option>
                                    <option v-for="prov in provinces" :key="prov.code" :value="prov.code">{{ prov.name }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.origin_province" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
                            <div>
                                <Label for="origin_regency_id">Kabupaten</Label>
                                <select
                                    id="origin_regency_id"
                                    :value="originRegencyId"
                                    @change="onRegencyChange"
                                    :disabled="!originProvinceId"
                                    class="mt-1 block w-full border-input focus:border-ring focus:ring-ring rounded-md shadow-sm disabled:bg-muted"
                                >
                                    <option value="">-</option>
                                    <option v-for="reg in regencies" :key="reg.code" :value="reg.code">{{ reg.name }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.origin_regency" />
                            </div>
                            <div>
                                <Label for="origin_subdistrict_id">Kecamatan</Label>
                                <select
                                    id="origin_subdistrict_id"
                                    :value="originDistrictId"
                                    @change="onDistrictChange"
                                    :disabled="!originRegencyId"
                                    class="mt-1 block w-full border-input focus:border-ring focus:ring-ring rounded-md shadow-sm disabled:bg-muted"
                                >
                                    <option value="">-</option>
                                    <option v-for="dis in districts" :key="dis.code" :value="dis.code">{{ dis.name }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.origin_subdistrict" />
                            </div>
                        </div>

                        <div>
                            <Label for="origin_village_id">Desa</Label>
                            <select
                                id="origin_village_id"
                                :value="originVillageId"
                                @change="onVillageChange"
                                :disabled="!originDistrictId"
                                class="mt-1 block w-full border-input focus:border-ring focus:ring-ring rounded-md shadow-sm disabled:bg-muted"
                            >
                                <option value="">-</option>
                                <option v-for="vil in villages" :key="vil.code" :value="vil.code">{{ vil.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.origin_village" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
                            <div class="flex items-center gap-2">
                                <input
                                    id="bpjs_health_active"
                                    type="checkbox"
                                    v-model="form.bpjs_health_active"
                                    class="rounded border-input text-primary shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                                />
                                <Label for="bpjs_health_active">BPJS Kesehatan Aktif</Label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input
                                    id="bpjs_employment_active"
                                    type="checkbox"
                                    v-model="form.bpjs_employment_active"
                                    class="rounded border-input text-primary shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                                />
                                <Label for="bpjs_employment_active">BPJS Tenaga Kerja Aktif</Label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
                            <div>
                                <Label for="date_of_birth">Tanggal Lahir</Label>
                                <Input
                                    id="date_of_birth"
                                    v-model="form.date_of_birth"
                                    type="date"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.date_of_birth" />
                            </div>

                            <div>
                                <Label for="join_date">Tanggal Bergabung *</Label>
                                <Input
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
                            <Label for="ktp_photo">Foto KTP</Label>
                            <div class="mt-2">
                                <ImageUpload v-model="form.ktp_photo" :current-image="member.ktp_photo_url" :maxSize="4096" />
                            </div>
                            <InputError class="mt-2" :message="form.errors.ktp_photo" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
                            <div>
                                <Label for="position_id">Posisi / Jabatan *</Label>
                                <select
                                    id="position_id"
                                    v-model="form.position_id"
                                    class="mt-1 block w-full border-input focus:border-ring focus:ring-ring rounded-md shadow-sm"
                                    required
                                >
                                    <option v-for="(label, key) in positions" :key="key" :value="key">{{ label }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.position_id" />
                            </div>
                            <div>
                                <Label for="status">Status *</Label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="mt-1 block w-full border-input focus:border-ring focus:ring-ring rounded-md shadow-sm"
                                    required
                                >
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Tidak Aktif</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.status" />
                            </div>
                        </div>

                        <div>
                            <Label for="notes">Catatan</Label>
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="3"
                                class="mt-1 block w-full border-input focus:border-ring focus:ring-ring rounded-md shadow-sm"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.notes" />
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end gap-4">
                            <Link
                                href="/members"
                                class="text-sm text-muted-foreground hover:text-foreground"
                            >
                                Batal
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                Simpan Perubahan
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
