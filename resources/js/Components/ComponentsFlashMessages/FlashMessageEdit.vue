<script setup>
const props = defineProps({
    flashMessage: {
        type: String,
    }
});
</script>
<template>
    <div v-if="props.flashMessage"
        class="alert bg-white text-center text-xl text-blue-700 border-gray-300 border-solid border-2 p-2">
        <div class=" flex flex-row justify-center items-center">
            <svg class="h-5 w-5 text-blue-700" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" />
                <path d="M9 7 h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />
                <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />
                <line x1="16" y1="5" x2="19" y2="8" />
            </svg>
            {{ props.flashMessage }}
        </div>
    </div>
</template>