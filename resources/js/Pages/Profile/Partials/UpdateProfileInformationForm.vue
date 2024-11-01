<script setup>
import FlashMessageEdit from '@/Components/ComponentsFlashMessages/FlashMessageEdit.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import vSelect from 'vue-select';
const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    user: {
        type: Object,
    },
    personal: {
        type: Object,
        default: ({}),
    },
    departamentos: {
        type: Array,
    }
});

const user = usePage().props.auth.user;
const personal = usePage().props.personal;
const form = useForm({
    name: personal.Nombre,
    lastname: personal.Apellidos,
    email: user.email,
    Departamento: props.departamentos.find(b => b.IdDepartamento === personal.IdDepartamento),
    Sexo: personal.Sexo,
});
const flashMessage = ref('');
const disableButtonForm = ref(false);
const updateProfileInformation = () => {
    form.clearErrors();
    const formDataJson = JSON.stringify(form); // Convertimos a JSON
    $.ajax({
        url: route('profile.update'),
        method: 'PATCH',
        contentType: 'application/json',
        data: formDataJson,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function(){
            disableButtonForm.value = true;
        },
        success: function (response) {
            flashMessage.value = 'Datos de perfil actualizados'
            setTimeout(() => {
                flashMessage.value = '';
            }, 3000);
            disableButtonForm.value = false;
        },
        error: function (xhr) {
            console.log(xhr.responseJSON);
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $.each(errors, function (field, messages) {
                    form.setError({
                        [field]: messages[0] || {}
                    });
                });
            }
            disableButtonForm.value = false;
        }
    });
    //form.post(route('reenviar.verificacionemail'));
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Información de perfil</h2>

            <p class="mt-1 text-sm text-gray-600">
                Actualiza tu información de perfil y tu correo electrónico.
            </p>
        </header>

        <form @submit.prevent="updateProfileInformation" class="mt-6 space-y-6">
            <div>
                <InputLabel for="name" value="Nombre" />

                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus
                    autocomplete="name" />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="lastname" value="Apellido(s)" />

                <TextInput id="lastname" type="text" class="mt-1 block w-full" v-model="form.lastname" required
                    autocomplete="lastname" />

                <InputError class="mt-2" :message="form.errors.lastname" />
            </div>
            <div>
                <InputLabel for="Estatus" value="Sexo" class="" />
                <div class="flex flex-auto justify-evenly">
                    <div class="space-x-2">
                        <label for="Hombre">Hombre</label>
                        <input type="radio" id="Hombre" value="Hombre" v-model="form.Sexo" />
                    </div>
                    <div class="space-x-2">
                        <label for="Mujer">Mujer</label>
                        <input type="radio" id="entregado" value="Mujer" v-model="form.Sexo" />
                    </div>
                    
                </div>
                <div class="text-end block font-medium text-sm text-gray-700">Seleccionó: {{
            form.Sexo }}</div>
            </div>
            <div>
                <InputLabel for="Departamento" value="Departamento" class="pt-2" />
                <v-select type="text" id="Departamento" label="nombreDepartamento"
                    placeholder="Introduce el departamento del que proviene" :options="departamentos" :filterable="true"
                    v-model="form.Departamento" class="border-white" />
                <InputError class="mt-2" :message="form.errors.Departamento" />
            </div>
            <div>
                <InputLabel for="email" value="Correo electrónico" />

                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required
                    autocomplete="username" />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm mt-2 text-gray-800">
                    Tu correo electrónico no esta verificado.
                    <Link :href="route('verification.send')" method="post" as="button"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Clic aquí para re-enviar el correo de verificacion.
                    </Link>
                </p>

                <div v-show="status === 'verification-link-sent'" class="mt-2 font-medium text-sm text-green-600">
                    Un nuevo link de verificación ha sido enviado a tu correo electrónico.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :class="{ 'opacity-25': disableButtonForm }"
                :disabled="disableButtonForm">Guardar</PrimaryButton>

                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="flashMessage" class="text-sm text-gray-600">{{ flashMessage }}.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
<style>
@import "vue-select/dist/vue-select.css";
</style>