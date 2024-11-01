<script setup>
import Swal from 'sweetalert2'
import { useForm } from '@inertiajs/vue3';
import FlashMessageDelete from '@/Components/ComponentsFlashMessages/FlashMessageDelete.vue';
import { ref, onMounted } from 'vue';
const props = defineProps({
    tipoDocs: {
        type: Object,
    }
})
const flashMessage = ref('');

const formDelete = useForm({
    idtipoDoc: '',
});
onMounted(() => {
    //Manejar clic del botón con la clase BorrarTipoDoc
    $('#TablaTipoDoc').on('click', '.BorrarTipoDoc', function () {
        formDelete.idtipoDoc = $(this).data('id');
        const TipoDoc = props.tipoDocs.find(a => a.IdTipoDocumento === formDelete.idtipoDoc);
        const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
        Swal.fire({
            title: 'Confirmación necesaria',
            text: `¿Desea borrar ${TipoDoc.nombreTipoDoc}?. 
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
                    url: route('tipoDoc.borrar'),
                    method: 'POST',
                    contentType: 'application/json',
                    data: formDataJson,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        formDelete.reset();
                        flashMessage.value = 'Tipo de documento eliminado correctamente';
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                        window.location.reload();
                    },
                    error: function (xhr) {
                        console.log(xhr.responseJSON.errors)
                    }
                });
                /* formDelete.delete(route('tipoDoc.borrar'), {
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
    <!-- Muestra un flash message cuando se elimina un tipo de documento -->
    <FlashMessageDelete :flashMessage="flashMessage"></FlashMessageDelete>
</template>