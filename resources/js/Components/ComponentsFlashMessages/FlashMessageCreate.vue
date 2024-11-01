<script setup>
    const props = defineProps({
        flashMessage: {
            type: String,
        }
    });
</script>
<template>
    <div v-if="props.flashMessage" class="alert bg-white text-center text-xl text-green-700 border-gray-300 border-solid border-2 p-2">
        <div class="flex flex-row justify-center items-center">
            <svg class="h-5 w-5 text-green-700" width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" />
                <circle cx="12" cy="12" r="9" />
                <path d="M9 12l2 2l4 -4" />
            </svg>
            {{ props.flashMessage }}
        </div>
    </div>
</template>