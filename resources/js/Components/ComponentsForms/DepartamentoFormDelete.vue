<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import FlashMessageDelete from '@/Components/ComponentsFlashMessages/FlashMessageDelete.vue';
const props = defineProps({
    departamentos: {
        type: Array,
    }
});

const formDelete = useForm({
    idDepartamento: '',
})
const flashMessage = ref('');
onMounted(() => {
    //Manejar el clic del botón con la clase BorrarDepartamento
    $('#TablaDepartamentos').on('click', '.BorrarDepartamento', function () {
        formDelete.idDepartamento = $(this).data('id');
        const departamentoD = props.departamentos.find(a => a.IdDepartamento === formDelete.idDepartamento);
        const randomCode = Math.floor(1000 + Math.random() * 9000); // Genera un código aleatorio de 4 dígitos
        Swal.fire({
            title: 'Confirmación necesaria',
            text: `¿Desea borrar ${departamentoD.nombreDepartamento}?. 
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
                    url: route('departamento.borrar'),
                    method: 'POST',
                    contentType: 'application/json',
                    data: formDataJson,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        formDelete.reset();
                        flashMessage.value = 'Departamento borrado correctamente';
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
                /* formDelete.delete(route('departamento.borrar'), {
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