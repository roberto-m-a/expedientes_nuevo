<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import AuthenticatedLayout_secretaria from '@/Layouts/AuthenticatedLayout_secretaria.vue';
import DepartamentoFormCreate from '@/Components/ComponentsForms/DepartamentoFormCreate.vue';
import DepartamentoFormEdit from '@/Components/ComponentsForms/DepartamentoFormEdit.vue';
import DepartamentoTable from '@/Components/ComponentsTables/DepartamentoTable.vue';
const props = defineProps({
    user: {
        type: Object,
    },
    personal: {
        type: Object,
    },
    departamentos: {
        type: Array,
    }
});
const abrir = ref(false); //Variable para manejar el componente de DepartamentoFormCreate
const abrirEdit = ref(false); //Variable para manejar el componente de DepartamentoFormEdit
const departamento = ref(null); //Variable para manejar el departamento del componente DepartamentoFormEdit

const formDelete = useForm({
    idDepartamento: '',
})
onMounted(() => {
    // Manejar clic en del botón con la clase EditarDepartamento
    $('#TablaDepartamentos').on('click', '.EditarDepartamento', function () {
        abrirEdit.value = true;
        const id = $(this).data('id');
        departamento.value = props.departamentos.find(a => a.IdDepartamento === id);
    });
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
                formDelete.delete(route('departamento.borrar'), {
                    preserveScroll: true,
                    onSuccess: () => {
                        formDelete.reset()
                    },
                    onError: () => {
                        console.log(formEdit.errors);
                    },
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire('Acción cancelada', 'No se realizó ninguna acción.', 'error');
            }
        });
    });
});
</script>

<template>

    <Head title="Departamento" />

    <AuthenticatedLayout_secretaria :user="user" :personal="personal">
        <template #header>
            <div class="flex flex-row items-end space-x-4">
                <h2 class="font-semibold text-2xl text-black leading-tight">Departamentos</h2>
                <h2 class="text-gray-500 font-semibold">Agrega, edita y borra departamentos</h2>
            </div>
        </template>
        <!-- Modal para editar departamento -->
        <DepartamentoFormEdit v-model:abrirModal="abrirEdit" v-model:departamento="departamento"></DepartamentoFormEdit>
        <!-- Modal para agregar un nuevo departamento -->
        <DepartamentoFormCreate v-model:abrirModal="abrir"></DepartamentoFormCreate>
        <DepartamentoTable v-model:abrir="abrir" :departamentos="departamentos"></DepartamentoTable>
    </AuthenticatedLayout_secretaria>
</template>
