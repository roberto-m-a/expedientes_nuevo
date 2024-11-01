<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});
const disableButtonForm = ref(false);
const verifyEmailSend = ref(false);
const submit = () => {
    form.clearErrors();
    const formDataJson = JSON.stringify(form);
    $.ajax({
        url: route('verification.send'),
        method: 'POST',
        contentType: 'application/json',
        data: formDataJson,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function(){
            disableButtonForm.value = true;
        },
        success: function (response) {
            console.log('nuevo correo enviado')
            verifyEmailSend.value = true;
            setTimeout(() => {
                verifyEmailSend.value = false;
            }, 5000);
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
    //form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div class="mb-4 text-sm text-gray-600">
            Gracias por registrarte. Antes de poder acceder al sistema, asegúrese de verificar su correo electrónico haciendo clic en el enlace que le hemos mandado por correo.
             Si aún no te ha llegado, puedes solicitar un reenvío
        </div>

        <div class="mb-4 font-medium text-sm text-green-600" v-if="verifyEmailSend">
            Un nuevo enlace de verificación ha sido enviado al correo que proporcionaste durante tu registro.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton :class="{ 'opacity-25': disableButtonForm }" :disabled="disableButtonForm">
                    Reenviar correo de verificación
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
