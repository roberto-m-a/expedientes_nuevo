<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import FlashMessageCreate from '@/Components/ComponentsFlashMessages/FlashMessageCreate.vue';
import { ref } from 'vue';

const props = defineProps({
    tokenExpirado: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.tokenExpirado,
    email: '',
    OTPassword: '',
});
const flashMessage = ref('');
const submit = () => {
    form.clearErrors();
    const formDataJson = JSON.stringify(form); // Convertimos a JSON
    $.ajax({
        url: route('reenviar.otp'),
        method: 'POST',
        contentType: 'application/json',
        data: formDataJson,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            form.reset();
            flashMessage.value = 'Se te ha enviado una nueva contraseña de uso único a tu correo'
            setTimeout(() => {
                window.location.href = '/';
            }, 3000);
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
        }
    });
    //form.post(route('login.otp'));
};
</script>
<template>
    <GuestLayout>
        <Head title="Login" />
        <div class="mb-4 text-sm text-gray-600">
            <p>Al parecer tu token y tu contraseña de uso único han expirado.</p>
            Ingresa el correo con el que registraste y la contraseña de uso único que te enviamos al correo para
            generarla nuevamente.
        </div>
        <FlashMessageCreate :flashMessage="flashMessage"></FlashMessageCreate>
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
                    Reenviar
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>