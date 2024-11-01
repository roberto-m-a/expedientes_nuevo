<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: '',
    OTPassword: '',
});

const submit = () => {
    form.clearErrors();
    const formDataJson = JSON.stringify(form); // Convertimos a JSON
    $.ajax({
        url: route('login.otp'),
        method: 'POST',
        contentType: 'application/json',
        data: formDataJson,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            window.location.href = route('dashboard');
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $.each(errors, function (field, messages) {
                    form.setError({
                        [field]: messages[0] || {}
                    });
                });
            }
        }
    });
    //form.post(route('login.otp'));
};
</script>
<template>
    <GuestLayout>
        <Head title="Login" />
        <div class="mb-4 text-sm text-gray-600">
            Ingresa el correo con el que registraste y la contraseña de uso único que te enviamos al correo para
            loguearte por primera vez.
        </div>
        <form @submit.prevent="submit">
            <div class="mt-4">
                <InputLabel for="email" value="Correo electrónico" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autofocus/>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>
            <div class="mt-4">
                <InputLabel for="OTPassword" value="Contraseña de uso único" />
                <TextInput id="OTPassword" type="password" class="mt-1 block w-full" v-model="form.OTPassword" required/>
                <InputError class="mt-2" :message="form.errors.OTPassword" />
            </div>
            <div class="flex items-center justify-end mt-4">
                <PrimaryButton>
                    Iniciar sesión
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>