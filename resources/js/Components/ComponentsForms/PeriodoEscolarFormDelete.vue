<script setup>
import FlashMessageDelete from '@/Components/ComponentsFlashMessages/FlashMessageDelete.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
const props = defineProps({
    periodosEscolares: {
        type: Array,
    },
});
//formulario para borrar un periodo escolar
const formDelete = useForm({
    IdPeriodoEscolar: '',
})
const flashMessage = ref('');
onMounted(() => {
    $('#TablaPeriodos').on('click', '.BorrarPeriodo', function () {
        formDelete.IdPeriodoEscolar = $(this).data('id');
        const periodoEscolarD = props.periodosEscolares.find(a => a.IdPeriodoEscolar === formDelete.IdPeriodoEscolar);
        const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
        Swal.fire({
            title: 'Confirmación necesaria',
            text: `¿Desea borrar ${periodoEscolarD.nombre_corto}?. 
            Para continuar, ingresa el código de confirmación: ${randomCode}`,
            input: 'number',
            footer: 'Esta acción se puede realizar ya que el registro no tiene relaciones con otros registros.',
            inputAttributes: {
                maxlength: 4,
                autocapitalize: 'off',
                autocorrect: 'off'
            },
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar',
            preConfirm: (inputValue) => {
                return new Promise((resolve) => {
                    if (inputValue === randomCode.toString()) {
                        resolve(true);
                    } else {
                        Swal.showValidationMessage('Código incorrecto');
                        resolve(false);
                    }
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const formDataJson = JSON.stringify(formDelete); // Convertimos a JSON
                $.ajax({
                    url: route('periodoEscolar.borrar'),
                    method: 'POST',
                    contentType: 'application/json',
                    data: formDataJson,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        formDelete.reset();
                        flashMessage.value = 'Período escolar borrado correctamente';
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                        window.location.reload();
                    },
                    error: function (xhr) {
                        console.log(xhr.responseJSON.errors);
                    }
                });
                /* formDelete.delete(route('periodoEscolar.borrar'), {
                    preserveScroll: true,
                    onSuccess: () => {
                        formDelete.reset()
                    },
                    onError: () => {
                        console.log(formEdit.errors);
                    },
                }); */
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire('Acción cancelada', 'No se realizó ninguna acción.', 'error');
            }
        });
    });
});
</script>
<template>
    <!-- FlashMessage que se muestra al borrar un departamento -->
    <FlashMessageDelete :flashMessage="flashMessage"></FlashMessageDelete>
</template>